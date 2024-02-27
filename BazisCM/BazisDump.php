<?php
namespace BazisCM;

error_reporting(E_ERROR);
ini_set('display_errors', 1);

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS',true);

$_SERVER["DOCUMENT_ROOT"] = "/mnt/data/bitrix";
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");



require_once ('/mnt/data/bitrix/local/php_interface/classes/WCommon/Context.php');
require_once ('/mnt/data/bitrix/local/php_interface/classes/WCommon/LogPush.php');

require_once ('Workspace/Tools/CurlManager.php');
require_once ('Workspace/Tools/Base.php');
require_once ('Workspace/Bitrix/Common.php');
require_once ('Workspace/Tools/MatchStadyWitdthDB.php');

require_once ('Workspace/Bitrix/Crud.php');


ob_end_flush();
class ChatManager {
  private $CurlManager;

  public function __construct($metod) {
    $apiKey = '02f532d7052b495dadb503a5c54d09f4';
    $this->CurlManager = new \BazisCM\Workspace\Tools\CurlManager($apiKey, $metod);

    $currentDateTime = new \DateTime();
    $this->to = $currentDateTime->format('Y-m-d\TH:i:s.u\Z');
    $oneWeekAgo = new \DateTime('-2 hour'); //  $oneWeekAgo = new \DateTime('-1 hour');
    //$oneWeekAgo = new \DateTime('-7 days');
    $this->from = $oneWeekAgo->format('Y-m-d\TH:i:s.u\Z');

    $this->Base = new \BazisCM\Workspace\Tools\Base();
    $this->BitrixCommon = new \BazisCM\Workspace\Bitrix\Common();

    $this->MatchStadyWitdthDB = new \BazisCM\Workspace\Tools\MatchStadyWitdthDB(); 

    $this->Crud = new \BazisCM\Workspace\Bitrix\Crud();
  }

  // запрос на получение заказов с фильтром, получаем самые новые за некоторый период
  public function getOrders(){
    $response = $this->CurlManager->Get(['from' => $this->from,'to' => $this->to, 'CheckContentName' => 'orders', 'ContentPars' => true]);
    return $response;
  }

  // отдельный запрос на получение информации о конкретном заказе
  public function getOrderInfo($p){
    $response = $this->CurlManager->Get(['CheckContentName' => 'order', 'ContentPars' => false]);
    return $response;
  }

  // хелпер для поиска соответсвий стадий и статусов в битрикс и базис
  public function findValueInArray($inputValue, $array) {
    if (array_key_exists($inputValue, $array)) {
        return $array[$inputValue];
    } else {
        return null;
    }
  }


  // основная функция для обновления информации о клиенте в карточку клиента из данных базиса
  public function getClient($p){
    
    $client = $p['client']['data']['client'];
    $number = $p['client']['data']['number'];


    print_r(['id заказа' => $p['client']['data']['id']]);
    print_r(['проверка номера договора' => $number]);

    $BitrixCommon = $this->BitrixCommon->Check($p);

    if(!$BitrixCommon['CONTACT_ID'] || $BitrixCommon['CONTACT_ID'] == 0 || !isset($BitrixCommon['CONTACT_ID'])){
      // создание нового контакта исключено, так как они создаются вручную/при формировании сделки
      print_r(['Warning: сделка с номер договора не найдена (getClient)']);
    }
    else{
      //проверяем нужно ли обновлять контакт
      $ContactGet = $this->BitrixCommon->ContactGet_check(['filter' => ['ID' => $BitrixCommon['CONTACT_ID']], 'check' => $client]);

      // обновляем контакт
      if($ContactGet == 'update'){
        $upd = $this->BitrixCommon->ContactUpdate(['ID' => $BitrixCommon['CONTACT_ID'], 'updateField' => $client]);
        if($upd){
          print_r(['обновили контакт']);
        }
        else{
          print_r(['Warning: не получилось обновить контакт']);
        }
      }
      else{
        print_r(['не требуется обновлять контакт']);
      }
    }
    return $BitrixCommon['CONTACT_ID'];
  }


  // основная функция для обновления информации о стадиях из данных базиса
  public function getState($p){

    $BitrixCommon = $this->BitrixCommon->Check($p);

    $ArrStageCategory_1 = [
      '05. Договор проверен РОП' => 'C12:WON',
      '02. Договор заключен' => 'C12:UC_CM88W2',
      '01. Повторная встреча' => 'C12:UC_YFU93G',
      '03. Договор не заключен' => 'C12:UC_MN8HQK',
    ];

    $ArrStageCategory_2 = [
      '05. Договор проверен РОП' => 'C11:WON',
      '02. Договор заключен' => 'C11:EXECUTING',
      '01. Повторная встреча' => 'C11:UC_2V1ONJ',
      '03. Договор не заключен' => 'C11:UC_6N89KF',
    ];

    $ArrStageCategory_allert = ['02. Договор заключен', '01. Повторная встреча', '03. Договор не заключен', '05. Договор проверен РОП'];

    if(in_array($p['stateBl']['currentState'],$ArrStageCategory_allert)){

      $result = '';

      if(($BitrixCommon['CATEGORY_ID'] == 12) || ($BitrixCommon['CATEGORY_ID'] == 11)){

        if($BitrixCommon['CATEGORY_ID'] == 12){
          
          $result = $this->findValueInArray($p['stateBl']['currentState'], $ArrStageCategory_1);
        }
        if($BitrixCommon['CATEGORY_ID'] == 11){

          $result = $this->findValueInArray($p['stateBl']['currentState'], $ArrStageCategory_2);
        }
        
  
        if($BitrixCommon['STAGE_ID'] !== $result){
  
          print_r(['стадия начальная' => $BitrixCommon['STAGE_ID']]);
          print_r(['стадия новая' => $result]);
  
          print_r(['требуется изменить стадию']);
          
          if($BitrixCommon['ID']){

            $stahgeState = $this->MatchStadyWitdthDB->GetHistoryStady($p['stateBl']['number'], $BitrixCommon['ID']);

            if(!$stahgeState){
              if($BitrixCommon['CATEGORY_ID'] == 11){
                $UpdateCrmDeal = $this->BitrixCommon->UpdateCrmDeal(['ID' => $BitrixCommon['ID'], 'updateFields' => ['STAGE_ID' => 'C11:PREPAYMENT_INVOIC']]);
              }
              if($BitrixCommon['CATEGORY_ID'] == 12){
                $UpdateCrmDeal = $this->BitrixCommon->UpdateCrmDeal(['ID' => $BitrixCommon['ID'], 'updateFields' => ['STAGE_ID' => 'C12:EXECUTING']]);
              }
            }
            
            $UpdateCrmDeal = $this->BitrixCommon->UpdateCrmDeal(['ID' => $BitrixCommon['ID'], 'updateFields' => ['STAGE_ID' => $result]]);
  
            if($UpdateCrmDeal){
              print_r(['стадия изменена успешно']);
            }
            else{
              print_r(['Warning: ошибка изменении стадии']);
            }
          }
          else{
            print_r(['сделка не найдена']);
          }
        }
      }
      else{
        print_r(['сделка вне воронок' => $BitrixCommon['CATEGORY_ID']]);
      }

    }
    else{
      print_r(['нет совпадений по стадиям воронок']);
    }
  
  }

  public function getFileBazis($p){

    foreach($p['files'] as $keyFl => $file) {
      print_r(['файл' => $file['name']]);
      $metod = '/api-orders-exchange-public/orders/' . $p['orderId'] . '/file';
      $chatManager = new ChatManager($metod);
      $files[$file['name']] = $this->CurlManager->Get(['filename' => $file['name'],'CheckContentName' => 'file', 'ContentPars' => false]);
    }

    return $files;
  }

  // основная функция для обновления информации о элементах списка/документах из данных базиса по номеру договора
  public function getFiles($p){

    $IblokItems = $this->BitrixCommon->GetIblokList($p); //  возвращает элементы списка с номером документа ('PROPERTY_93' => $p['data']['number'])

    // если найден соответствующий элемент списка
    if($IblokItems){

      $files = $this->getFileBazis($p);

      //проверяем нужно ли обновлять элемент списка/документа
      $IblokItemCheckBazis = $this->BitrixCommon->IblokItemCheck($p,$IblokItems,$files);

      if(isset($IblokItemCheckBazis['orderIsChecked'])){

        $CheckUpdate = $this->BitrixCommon->IblokItemUpdate($IblokItemCheckBazis['orderIsChecked'], $IblokItems, $IblokItemCheckBazis['updFiles'], $p['contact']);

        if(!$CheckUpdate){
          $Base = new \BazisCM\Workspace\Tools\Base();
          $error = "не получилось обновить документ: ".$p['data']['number'];
          $Base->GoWide($error);
        }

      } 
    }
    else{
      $files = $this->getFileBazis($p);
      $CheckAdd = $this->BitrixCommon->AdaptForAddIblockElem($p, $files);
      $CheckAdd = $this->BitrixCommon->IblokItemAdd($CheckAdd['ord']['data'], $CheckAdd['files'], $p['contact']);

      if(!$CheckAdd){
        $Base = new \BazisCM\Workspace\Tools\Base();
        $error = "не получилось сгенерировать документ: ".$p['data']['number'];
        $Base->GoWide($error);
      }
    }

    //далее в списках добавляем или обновляем файлы
  }

  public function Controller() {

    $metod = '/api-orders-exchange-public/orders';

    // для начала получим все заказы
    $chatManager = new ChatManager($metod);
    $orders = $chatManager->getOrders();

    $orders = $this->ControllLog($orders);


    $alfa = [
      'Т'=>'T',
      'К'=>'K',
      'Е'=>'E',
      'А'=>'A',
      'О'=>'O',
      'Р'=>'P',
      'Х'=>'X',
      'М'=>'M',
      'Н'=>'H',
      'С'=>'C',
      'В'=>'B'
    ];
    
    //получаем инфо о каждом заказе
    foreach($orders['data'] as $key => $order){

      $metod = '/api-orders-exchange-public/orders/' . $order['id'];
      $chatManager = new ChatManager($metod);

      $OrderInfo = $chatManager->getOrderInfo($order);

      $OrderInfo['data']['number'] = strtr($OrderInfo['data']['number'], $alfa);

      $contact = $chatManager->getClient(['client' => $OrderInfo]);

      $chatManager->getState(['stateBl' => $OrderInfo['data']]);

      $chatManager->getFiles(['files' => $OrderInfo['data']['files'], 'orderId' => $order['id'], 'data' => $OrderInfo['data'], 'contact' => $contact]);

    }
  }


  public function ControllLog($orders) {
    foreach($orders['data'] as $key => $order){

      if(isset($order) && !empty($order['dateLastEdit'])){
  
        $TIMEUPDATE	= $order['dateLastEdit'];
  
        $query = "SELECT * FROM bb_log WHERE TIMEUPDATE='$TIMEUPDATE' AND ID_ORDER=".$order['id'];
        $Result = '';
        $Result = $this->Crud->Get(['request' => $query]);

          if(!isset($Result) || empty($Result)){
  
            $data[0] = [
              'TIMEUPDATE' => $order['dateLastEdit'],
              'ID_ORDER' => $order['id'],
            ];

            $this->Crud->syncDataWithDatabase(['table_name' => 'bb_log', 'data' => $data]);
    
          }
          else{
            unset($data[0]);
            unset($orders['data'][$key]);
          }
  
      }
      
    }
    return $orders;
  }
}


$metod = '/api-orders-exchange-public/orders';

// для начала получим все заказы
$chatManager = new ChatManager($metod);
$orders = $chatManager->Controller();