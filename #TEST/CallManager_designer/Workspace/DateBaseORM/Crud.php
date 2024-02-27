<?php
namespace CallCustom\Workspace\DateBaseORM;

class Crud {

    private $db;
    private $user;
    private $pass;
    private $mysqli;
    
    public function __construct() {

        $this->db = 'dbbb1';
        $this->user = 'dbbb1';
        $this->pass = 'hE0pVa4ec3ZNUaxaHJvR';

        $this->mysqli = new \mysqli("localhost", $this->user, $this->pass, $this->db);
        
        if ($this->mysqli->connect_error) {
            die("Ошибка подключения: " . $this->mysqli->connect_error);
        }
    }
    
    public function __destruct() {
        // Закрываем соединение при уничтожении объекта
        $this->mysqli->close();
    }


    public function WGet($p) {
        $query = $p['request'];

        $result = $this->mysqli->query($query);

        if (!$result) {
            echo "Ошибка выполнения запроса: (" . $this->mysqli->errno . ") " . $this->mysqli->error;
            return false;
        }

        if($result == 1){
            return 1;
        }

        if(isset($p['one']) && !empty($p['one'])){
            while ($row = $result->fetch_assoc()) {
                $data = $row;
            }
        }
        else{
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }

      

        $result->free();

        return $data;
    }

    public function Get($p) {
        $query = $p['request'];

        $result = $this->mysqli->query($query);

        if (!$result) {
            echo "Ошибка выполнения запроса: (" . $this->mysqli->errno . ") " . $this->mysqli->error;
            return false;
        }

        if(isset($p['one']) && !empty($p['one'])){
            while ($row = $result->fetch_assoc()) {
                $data = $row;
            }
        }
        else{
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }

      

        $result->free();

        return $data;
    }

    public function closeConnection() {
        if ($this->mysqli) {
            $this->mysqli->close();
        }
    }

    
    public function LetBotBase($pool, $subtext, $subtype) {

        $rsUsers = \Bitrix\Main\UserTable::GetList([
            'select' => ['UF_TELEGRAM_ID'],
            'filter' => ['ID' => $pool['id_responsible']]
        ])->fetch();

                
        $id_tg = $rsUsers['UF_TELEGRAM_ID'];
        $this->LetBot($pool, $subtext, $id_tg);
       
    }

    public function LetBot($pool, $subtext, $id_tg) {

        \CModule::IncludeModule('crm'); 

        $ContactInfo = \Bitrix\Crm\ContactTable::GetList([
            'select' => ['*', 'PHONE', 'ADDRESS'], 
            'filter' => [ 'ID' => $pool['contact_id'] ]
        ]);
        
        $Contact = $ContactInfo->fetch();
        $CID = $this->getFIO($Contact);
       
        $url = "https://bx24.kitchenbecker.ru/crm/deal/details/".$pool['id_crm_deal']."/";

        if(isset($pool['force'])){

            $message_text = 'Примите новую встречу в работу по сделке '.$pool['id_crm_deal'].' 
Ссылка на сделку: '.$url;
$data = [
    'chat_id' => $id_tg,
    'text' => $message_text,
];
$this->Sendmess($pool, $data);
            /*\PushManager\PushReplay::send([
                'id_crm' => $pool['id_crm_deal'],
                'chat_id' => $id_tg,
                'pool' => $pool,
                'message_text' => $message_text
            ]);*/


            $message_text = "Встреча принята в работу по сделке " .$pool['id_crm_deal'] . " ФИО клиента: " . $CID;

            if (isset($pool['uf_time_visit'])) {
                $message_text .= ' Дата и время встречи: ' . $pool['uf_time_visit'];
            }

            if (isset($pool['adress'])) {
                $message_text .= ' Адрес клиента: ' . $pool['adress'];
            }

            if (isset($pool['link'])) {
                $message_text .= ' Ссылка на маршрут: ' . $pool['link'];
            }
            
            $message_text .= ' ' . $url;

            $data = [
                'chat_id' => $id_tg,
                'text' => $message_text,
            ];

            $this->Sendmess($pool, $data);
    
            $message_text = 'Дополнительная информация по сделке "'.$pool['id_crm_deal'].'"
Ссылка на сделку: '.$url.'
Номер договора: '.$pool['numerDogovor'].'
Номер квартиры:'.$pool['numerKravt'];

            $data = [
                'chat_id' => $id_tg,
                'text' => $message_text,
            ];

            $this->Sendmess($pool, $data);

            die();

        }

        if($pool['STATE'] == 'moment_podtver'){

$message_text = 'Примите новую встречу в работу по сделке '.$pool['id_crm_deal'].' 
Ссылка на сделку: '.$url.'
Для принятия встречи нажмите на кнопку "Да"';

            $pushRetp = \PushManager\PushReplay::send([
                'id_crm' => $pool['id_crm_deal'],
                'chat_id' => $id_tg,
                'pool' => $pool,
                'message_text' => $message_text
            ]);

            $LogPush = new \WCommon\LogPush();

            if($pushRetp){

                $response = 'успешно';
                $this->SendBossmess($pool, $response);
            } else {

                $err_or = 'ошибка #001 по сделке: '.$pool['id_crm_deal'].'. Не отправлено сообщение на стадии сделка подтверждена';
                $LogPush->Push($err_or);

                $response = 'не успешно1';
                $this->SendBossmess($pool, $response);
            }

            print_r(['$response_1' => $response]);

            return;

        }

        if($pool['STATE'] == '>>1hour'){

            $tasksArr_Activity_ITEM['ownerId'] = $pool['id_crm_deal'];
            $tasksArr_Activity_ITEM['description'] = 'Проконтролировать встречу и позвонить клиенту для уточнения деталей';
            $entityItemIdentifier = new \Bitrix\Crm\ItemIdentifier(\CCrmOwnerType::Deal, $tasksArr_Activity_ITEM['ownerId']);

            $todo = (new \Bitrix\Crm\Activity\Entity\ToDo($entityItemIdentifier))
            ->setDescription($tasksArr_Activity_ITEM['description']);
            $todo->setCheckPermissions(false);
            $todo->setDeadline( \Bitrix\Main\Type\DateTime::createFromTimestamp(strtotime("0 hour")));
        
            $responsibleId = $pool['UF_CRM_1694343013']; // ответственный МП UF_CRM_1694343013

            if(!isset($responsibleId) || empty($responsibleId)){
                $responsibleId = 37;
            }

            $todo->setResponsibleId($responsibleId);
            $saveResult = $todo->save();
            $saveResult = $saveResult->getData();

            if(!$saveResult){
                $ki = $saveResult->LAST_ERROR;
                print_r(['ERROR_TASK' => $ki]);
            }

            return;
        }

        if($pool['STATE'] == '>15'){
           /* $message_text = "Встреча принята в работу по сделке " .$pool['id_crm_deal'] . " ФИО клиента: " . $CID;

            if (isset($pool['uf_time_visit'])) {
                $message_text .= ' Дата и время встречи: ' . $pool['uf_time_visit'];
            }

            if (isset($pool['adress'])) {
                $message_text .= ' Адрес клиента: ' . $pool['adress'];
            }

            if (isset($pool['link'])) {
                $message_text .= ' Ссылка на маршрут: ' . $pool['link'];
            }
            $message_text .= ' ' . $url;*/
            
            return;
        }

        if(($pool['STATE'] == '15') || $pool['STATE'] == '<15'){
            $message_text = 'Дополнительная информация по сделке "'.$pool['id_crm_deal'].'"
Ссылка на сделку: '.$url.'
Номер договора: '.$pool['numerDogovor'].'
Номер квартиры:'.$pool['numerKravt'];
        }

        
        
        
        if(($pool['STATE'] == '10') || $pool['STATE'] == '<10'){

            //$id_tg = '437532761';
            $text = 'встреча по сделке '.$pool['id_crm_deal'].' началась?';
            $data = [
                'chat_id' => $id_tg,
                'text' => $text,
                'reply_markup' => json_encode(array(
                    'inline_keyboard' => array(
                        array(
                            array(
                                'text' => 'Да',
                                'callback_data' => 'approve_10time',
                            ),
                            array(
                                'text' => 'Отменилась',
                                'callback_data' => 'not_approve_10time',
                            ),
                        )
                    ),
                )),
            ];

            \PushManager\PushReplay::sendM([
                'id_crm' => $pool['id_crm_deal'],
                'chat_id' => $id_tg,
                'pool' => $pool
            ], $data);

            return;
        }

        $data = [
            'chat_id' => $id_tg,
            'text' => $message_text
        ];

        $this->Sendmess($pool, $data);

        
    }

    public function SendBossmess($pool, $response) {    
        if($pool['STATE'] == 'moment_podtver'){
            if(isset($pool['boss'][0]) && !empty($pool['boss'][0])){

                if($pool['boss'][0] !== 31){
                    $rsUsers = \Bitrix\Main\UserTable::GetList([
                        'select' => ['UF_TELEGRAM_ID','NAME','SECOND_NAME','LAST_NAME'],
                        'filter' => ['ID' => $pool['boss']]
                    ])->fetch();
    
    
                    $ContactInfo = \Bitrix\Main\UserTable::GetList([
                        'select' => ['UF_TELEGRAM_ID','NAME','SECOND_NAME','LAST_NAME'],
                        'filter' => ['ID' => $pool['id_responsible']]
                    ])->fetch();
                    
                    $url = "https://bx24.kitchenbecker.ru/crm/deal/details/".$pool['id_crm_deal']."/";
        
                    $fio = $this->getFIO($ContactInfo);
                    $id_tg = $rsUsers['UF_TELEGRAM_ID'];
        
                    $mess = 'На вашего дизайнера '.$fio.' пришло уведомление на подтверждение по сделке '.$url;
        
                    $data = [
                        'chat_id' => $id_tg,
                        'text' => $mess,
                    ];
        
                    $pool['ITS'] = 'BOSS';
                    $this->Sendmess($pool, $data);
                }
                else{
                    print_r(['ему не отправляем']);
                }

            }

        }

    }

    
    public function Sendmess($pool, $data) {
        $botToken = '6489328913:AAFJ5biTuinVmedStG2DBjqmDYWlAQfMdoU';
        $apiUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        
        if($pool['ITS'] == 'BOSS'){
            if ($response === false) {
                $ery = 'Ошибка отправки запроса' . curl_error($ch);
                print_r([$ery]);
                curl_close($ch);

                $mess = 'Сообщение "'.$mess.'" не было доставлено руководителю с ID = '. $pool['boss'];
                $data = [
                    'chat_id' => '2074706005',
                    'text' => $mess,
                ];

                $this->SendmessHelper($data);
            }
            else{
                print_r($data);
                echo 'Сообщение успешно отправлено!2';
                curl_close($ch);
            }
        }
        else{
            if($response === false) {
                $ery = 'Ошибка отправки запроса' . curl_error($ch);
                print_r([$ery]);
                curl_close($ch);

                $mess = 'Сообщение "'.$data['text'].'" не было доставлено дизайнеру с ID = '. $pool['id_responsible'];
                $data = [
                    'chat_id' => '2074706005',
                    'text' => $mess,
                ];

                $this->SendmessHelper($data);
            }
            else{
                print_r($data);
                echo 'Сообщение успешно отправлено!1';
                curl_close($ch);
                $ery = 'успешно';
                $this->SendBossmess($pool, $ery);
            }
        }

        
    }


    public function SendmessHelper($data) {
        $botToken = '6489328913:AAFJ5biTuinVmedStG2DBjqmDYWlAQfMdoU';
        $apiUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    }
        
    public function getFIO($Contact) {
        if(isset($Contact) && !empty($Contact)){
            
            
            $CID = '';
    
            if (!empty($Contact['NAME'])) {
                $CID .= $Contact['NAME'];
            }
            
            if (!empty($Contact['LAST_NAME'])) {
                if (!empty($CID)) {
                    $CID .= ' ';
                }
                $CID .= $Contact['LAST_NAME'];
            }
            
            if (!empty($Contact['SECOND_NAME'])) {
                if (!empty($CID)) {
                    $CID .= ' ';
                }
                $CID .= $Contact['SECOND_NAME'];
            }
            return $CID;
        }
        else{
            return 'ФИО не найдено';
        }
    }

    
    public function CreateAnEntry($p) {
        
        $item = $p['item'];
        
        if(isset($p['historyItem']) && !empty($p['historyItem']) && ($p['historyItem']['stateChange'] !== 'change')){

            $historyItem = $p['historyItem'];
            $data = $p['historyItem'];

            unset($data['id']);
            unset($data['created_at']);
            unset($data['contact_id']);

            $data['contact_id'] = $item['contact_id'];
            if (isset($item['id_responsible']) && !empty($item['id_responsible'])) {
                $data['id_responsible'] = $item['id_responsible'];
            } else {
                $data['id_responsible'] = 'none';
            }
            if(isset($item['uf_time_visit']) && !empty($item['uf_time_visit'])){
                $data['uf_time'] = $item['uf_time_visit'];
            }

        } else{
            if (isset($item['id_responsible']) && !empty($item['id_responsible'])) {
                $data['id_responsible'] = $item['id_responsible'];
            } else {
                $data['id_responsible'] = 'none';
            }
        
            $data['contact_id'] = $item['contact_id'];
            $data['uf_time'] = '';
            
            if(isset($item['uf_time_visit']) && !empty($item['uf_time_visit'])){
                $data['uf_time'] = $item['uf_time_visit'];
            }
    
            $data['id_crm_deal'] = $item['id_crm_deal'];
            // Устанавливаем значения по умолчанию
            $data['status_mess_moment_podtver'] = 'not yet';
            $data['status_mess_moment_prinata'] = 'not yet';
            $data['minutes_mess'] = 'not yet';
        }

        $data['change_date'] = '0';

        if($item['stage_id'] == 'Встреча принята'){
            if($item['STATE'] == '>15'){
                $data['status_mess_moment_prinata'] = 'yet';
            }
            if(($item['STATE'] == '15') || $item['STATE'] == '<15'){
                $data['minutes_mess'] = 'yet';
            }
            if(($item['STATE'] == '10') || ($item['STATE'] == '<10')){
                $data['status_mess_ten_min_wait'] = 'yet';
            }
            if($item['STATE'] == '>>1hour'){
                $data['status_mess_ten_min'] = 'yet';
            } 
        }

        if($item['stage_id'] == 'Встреча подтверждена'){
            $data['status_mess_moment_podtver'] = 'yet';
        }

        $LogPush = new \WCommon\LogPush();

        /*$LogPush->Push($data['status_mess_moment_prinata']);
        $LogPush->writeLogSimple($data);*/

        $host = \ContextCust::GetUrl();
        if($host){
            $table_name = 'cm_dis_mess_history';
        } else{
            $table_name = 'cm_dis_mess_history';
        }


        $this->syncDataWithDatabase(['data' => [$data], 'table_name' => $table_name]);

        

    }
    
    public function ChangeDateDeal($item) {
        $mysqli = $this->mysqli;
        $id_crm_deal = $item['id_crm_deal'];

        $host = \ContextCust::GetUrl();
        if($host){
            $table_name = 'cm_dis_mess_history';
        } else{
            $table_name = 'cm_dis_mess_history';
        }

        $query = "UPDATE $table_name SET change_date = '1' WHERE id_crm_deal = $id_crm_deal";
       
        $result = $this->mysqli->query($query);

        if($host){
            $table_name = 'tg_approve_btn_main';
        } else{
            $table_name = 'tg_approve_btn_main';
        }

        $query = "UPDATE $table_name SET live = 'no' WHERE id_crm = $id_crm_deal";
        $result = $this->mysqli->query($query);
    }

    public function syncDataWithDatabase($p) {
      

        $mysqli = $this->mysqli;
        $table_name = $p['table_name'];
    
        $values = [];
    
        foreach ($p['data'] as $item) {
            $escapedValues = array_map(function ($value) use ($mysqli) {
               
                return "'" . $mysqli->real_escape_string($value) . "'";
            }, $item);
    
            $values[] = '(' . implode(',', $escapedValues) . ')';
        }
    
        $fields = array_keys($p['data'][0]);
        $query = "INSERT INTO $table_name (" . implode(',', $fields) . ") VALUES " . implode(',', $values);
        $stmt = $mysqli->prepare($query);
    
        if (!$stmt) {
            echo "Ошибка подготовки запроса: (" . $mysqli->errno . ") " . $mysqli->error;
            exit();
        }
    
        $stmt->execute();
    }

    public function GetDoubleDeal($p) {

        try{

          $id_crm_deal = $p['id_crm_deal'];
          $id_responsible = $p['id_responsible'];
          $contact_id = $p['contact_id'];
          $type_call = $p['type_call'];
          $category_call = $p['category_call'];
          $twenty_four_hour = $p['twenty_four_hour'];
          $three_hour = $p['three_hour'];
          $breaktime = $p['breaktime'];
          $stoptime = $p['stoptime'];
          $status_called = $p['status_called'];


          $host = \ContextCust::GetUrl();
          if($host){
              $table_name = 'cm_dis_mess_history';
          } else{
              $table_name = 'cm_dis_mess_history';
          }

          // Запрос для выбора менеджера
          $managerQuery = "SELECT * FROM $table_name
            WHERE id_crm_deal='$id_crm_deal' 
            AND id_responsible='$id_responsible' 
            AND contact_id='$contact_id' 
            AND type_call='$type_call' 
            AND category_call='$category_call' 
            AND twenty_four_hour='$twenty_four_hour' 
            AND status_called='$status_called'
            AND three_hour='$three_hour' 
            AND breaktime='$breaktime' 
            ORDER BY created_at DESC 
            LIMIT 1";

          $managerResult = $this->Get(['request' => $managerQuery]);

          if (!$managerResult) {
              return null;
          }

          return $managerResult;
    
        } catch (\Exception $e) { 
            return null; 
        }
          
    }

    public function GetHistoryDeal($p) {

        try{
  
          $prefix = $p['id_crm_deal'];
          $prefix_resp = $p['id_responsible'];

          $host = \ContextCust::GetUrl();
          if($host){
              $table_name = 'cm_dis_mess_history';
          } else{
              $table_name = 'cm_dis_mess_history';
          }

          $managerQuery = "SELECT * FROM $table_name WHERE id_crm_deal='$prefix' AND id_responsible='$prefix_resp' AND change_date='0' ORDER BY id DESC";
          $managerResult = $this->Get(['request' => $managerQuery]);
    
          if (!$managerResult) {
              return null;
          }
    
          return $managerResult;
    
        } catch (\Exception $e) { 
            return null; 
        }
          
    }

    
    public function GetHDealDX($p) {

        try{
  
          $prefix = $p['id_crm_deal'];
          $prefix_resp = $p['id_responsible'];

          $host = \ContextCust::GetUrl();
          if($host){
              $table_name = 'cm_dis_mess_history';
          } else{
              $table_name = 'cm_dis_mess_history';
          }

          $managerQuery = "SELECT * FROM $table_name WHERE id_crm_deal='$prefix' AND change_date='0' ORDER BY id DESC";
          $managerResult = $this->Get(['request' => $managerQuery]);
    
          if (!$managerResult) {
              return null;
          }
    
          return $managerResult;
    
        } catch (\Exception $e) { 
            return null; 
        }
          
    }
}