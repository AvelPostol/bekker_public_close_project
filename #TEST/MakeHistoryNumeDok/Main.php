<?php
namespace BazisCM;

error_reporting(E_ERROR);
ini_set('display_errors', 1);

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS',true);

$_SERVER["DOCUMENT_ROOT"] = "/mnt/data/bitrix";
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");


require_once ('/mnt/data/bitrix/local/php_interface/classes/BazisCM/Workspace/Tools/CurlManager.php');
require_once ('/mnt/data/bitrix/local/php_interface/classes/BazisCM/Workspace/Tools/Base.php');
require_once ('/mnt/data/bitrix/local/php_interface/classes/BazisCM/Workspace/Bitrix/Common.php');
require_once ('/mnt/data/bitrix/local/php_interface/classes/BazisCM/Workspace/Tools/MatchStadyWitdthDB.php');

require_once ('/mnt/data/bitrix/local/php_interface/classes/BazisCM/Workspace/Bitrix/Crud.php');

class ChatManagerGo {
  private $CurlManager;

  public function __construct($metod) {
    $apiKey = '02f532d7052b495dadb503a5c54d09f4';
    $this->CurlManager = new \BazisCM\Workspace\Tools\CurlManager($apiKey, $metod);

    $currentDateTime = new \DateTime();
    $this->to = $currentDateTime->format('Y-m-d\TH:i:s.u\Z');
    $oneWeekAgo = new \DateTime('-4 hour'); //  $oneWeekAgo = new \DateTime('-1 hour');
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

  public function Controller() {

    $metod = '/api-orders-exchange-public/orders';

    // для начала получим все заказы
    $chatManager = new ChatManagerGo($metod);
    $orders = $chatManager->getOrders();

    $orders = $this->ControllLog($orders);

    /*
    //получаем инфо о каждом заказе
    foreach($orders['data'] as $key => $order){
      print_r([$order]);
      $metod = '/api-orders-exchange-public/orders/' . $order['id'];
      $chatManager = new ChatManager($metod);
      $OrderInfo = $chatManager->getOrderInfo($order);
    }*/

  }

  public function fillEmptyValues(array &$array) {
    foreach ($array as $key => &$value) {
        // Если значение - массив, рекурсивно вызываем эту функцию
        if (is_array($value)) {
            $this->fillEmptyValues($value);
        } else {
            // Если значение пустое, заменяем его на ''
            if ($value === null || $value === '') {
                $value = '';
            }
        }
    }
    
    // Возвращаем измененный массив
    return $array;
}


  public function writeLog($data) {
    $logFile = '/mnt/data/bitrix/local/php_interface/classes/MakeHistoryNumeDok/log_'.time().'.txt';
    $formattedData = var_export($data, true);
    file_put_contents($logFile, '<?php $array = ' . $formattedData . ';', FILE_APPEND);
  }

  public function GetDeal($nume)
  {

    \CModule::IncludeModule('crm'); 
    $arDeals=\Bitrix\Crm\DealTable::getList([
      'select' => ['ID', 'UF_CRM_1694018792723'],
      'filter' => ['UF_CRM_1694018792723' => $nume]
    ])->fetchAll();
    
    $deals=[];
    foreach($arDeals as $deal){
        $deals = $deal;
    }
    if (isset($deals)) {
        return $deals;
    }
    else{
         return NULL;
    }

    return NULL;
  }

  public function GoySheets($values){

    $values = $this->fillEmptyValues($values);
    // Выполняем HTTP POST запрос
    $url = 'https://bx24.kitchenbecker.ru/local/php_interface/classes/MakeHistoryNumeDok/DumpSheets.php';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($values));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    print_r($response);

  }


  public function ControllLog($orders) {
    foreach($orders['data'] as $key => $OrderInfo){
      $metod = '/api-orders-exchange-public/orders/' . $OrderInfo['id'];

     /* $query_z = "SELECT * FROM bb_log_doknumer_deff";
      $Result_z = $this->Crud->Get(['request' => $query_z]);
      $this->GoySheets($Result_z);
      die();*/

      $this->ControllLog_w($metod);
    }
  }

  public function ControllLog_w($metod) {
    $chatManager = new ChatManagerGo($metod);
    $orderD = $chatManager->getOrderInfo($OrderInfo);
    $order = $orderD['data'];

    $compareDate = new \DateTime("2023-12-12 00:00:00");
    $dateObject = new \DateTime($order['dateCreate']);

    if((isset($order) && !empty($order['dateCreate'])) && ($dateObject > $compareDate)){ // если есть заказ и дата создания

      $deal = $this->GetDeal($order['number']);

      if((!isset($deal) || empty($deal)) && (isset($order['client']) && !empty($order['client']))){ // если нет такой сделки и есть данные о клиенте
        $TIME	= $order['dateCreate'];

        $query = "SELECT * FROM bb_log_doknumer_deff WHERE DATE_CREATE='$TIME' AND ID_ORDER=".$order['id'];
        $Result = '';
        $Result = $this->Crud->Get(['request' => $query]);
  
          if(!isset($Result) || empty($Result)){


            if(isset($order['client']['microdistrict'])){
              $city = $order['client']['microdistrict'];
            } else{
              $city = $order['client']['city'];
            }

            if($order['client']['house']){
              $order['client']['house'] = 'д. '.$order['client']['house'];
            }
            
            if($order['client']['housing']){
              $order['client']['housing'] = 'корпус '.$order['client']['housing'];
            }
    
            if($order['client']['apartment']){
              $order['client']['apartment'] = 'квартира '.$order['client']['apartment'];
            }

            $adres = '';
            $adres = trim($city.' '.$order['client']['street'].' '.$order['client']['house'].' '.$order['client']['housing'].' '.$order['client']['apartment']);

            $phone = '';
            if(isset($order['client']['phone']) && !empty($order['client']['phone'])){
              $phone = $order['client']['phone'];
            }
            if(isset($order['client']['phone2']) && !empty($order['client']['phone2'])){
              $phone = $phone . ', ' .$order['client']['phone2'];
            }

            $data[0] = [
              'TIMEUPDATE' => $order['dateLastEdit'],
              'ID_ORDER	' => $order['id'],
              'ARTICLE' => $order['salon']['article'],
              'NUMER_DOGOVOR' => $order['number'],
              'DATE_CREATE' => $order['dateCreate'],
              'FIO' => $order['client']['companyName'], 
              'ADRESS' => $adres,
              'PHONE' => $phone,
            ];
            $this->Crud->syncDataWithDatabase(['table_name' => 'bb_log_doknumer_deff', 'data' => $data]);

            $query_z = "SELECT * FROM bb_log_doknumer_deff";
            $Result_z = $this->Crud->Get(['request' => $query_z]);
            $this->GoySheets($Result_z);

          }
      }
      else{
  
      }
    }
  }
}




$metod = '/api-orders-exchange-public/orders';

// для начала получим все заказы
$chatManager = new ChatManagerGo($metod);
$orders = $chatManager->Controller();


/*
CREATE TABLE bb_log_doknumer_deff (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    ARTICLE VARCHAR(255),
    TIMEUPDATE VARCHAR(255),
    ID_ORDER VARCHAR(255),
    NUMER_DOGOVOR VARCHAR(255),
    DATE_CREATE VARCHAR(255),
    FIO VARCHAR(255),
    ADRESS VARCHAR(255),
    PHONE VARCHAR(255),
    TIMEST TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


id заказа, 
номер договора,
дата создания сделки в Базисе,
ФИО клиента,
адрес,
номер телефона клиента.*/