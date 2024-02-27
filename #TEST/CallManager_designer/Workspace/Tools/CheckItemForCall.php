<?php
namespace CallCustom\Workspace\Tools;

class CheckItemForCall {
    
    public function __construct() {
        $this->DB = new \CallCustom\Workspace\DateBaseORM\Crud(); // CREATE / READ / UPDATE / DELETE -> CRUD
        $this->CRM = new \CallCustom\Workspace\Bitrix\CRM();
    }

    public function CanPush($item,$historyItem,$HDealDX) {

        if($item['stage_id'] == 'Встреча принята'){

            if($item['STATE'] == '>>1hour'){
                $data['status_mess_ten_min'] = 'yet';
            }

            if($item['STATE'] == '>15'){
                $data['status_mess_moment_prinata'] = 'yet';
            }

            if(($item['STATE'] == '15') || ($item['STATE'] == '<15')){
                $data['minutes_mess'] = 'yet';
            }

            if(($item['STATE'] == '10') || ($item['STATE'] == '<10')){
                $data['status_mess_ten_min_wait'] = 'yet';
            }

        }
        if($item['stage_id'] == 'Встреча подтверждена'){
            $data['status_mess_moment_podtver'] = 'yet';
        }

        foreach($data as $key => $data_item){
            foreach($historyItem as $historyItem_id){

                if ($historyItem_id['uf_time'] !== $item['uf_time_visit']) {
                    
                    if((isset($historyItem_id['uf_time']) && !empty($historyItem_id['uf_time'])) || (isset($item['uf_time_visit']) && !empty($item['uf_time_visit']))){

                        echo '<PRE>';
                        print_r(['меняем время', ['стало' => $item['uf_time_visit']], ['было' => $historyItem_id['uf_time']]]);
                        echo '</PRE>';

                        $this->DB->ChangeDateDeal($item);
                        return 'change';
                    }
                }

                if($historyItem_id['id_responsible'] !== $item['id_responsible']){
                    $this->DB->ChangeDateDeal($item);
                    return 'change';
                }

                if($item['STATE'] == '>>1hour'){
                    if($historyItem_id['status_mess_ten_min_wait'] !== 'yet'){
                        return NULL;
                    }
                }

                if(isset($data['status_mess_ten_min']) && !empty($data['status_mess_ten_min'])){
                    if($data['status_mess_ten_min'] !== $historyItem_id['status_mess_ten_min']){
                        return 'validated';
                    }
                }

            

                if(isset($data['status_mess_ten_min_wait']) && !empty($data['status_mess_ten_min_wait'])){
                    if($data['status_mess_ten_min_wait'] !== $historyItem_id['status_mess_ten_min_wait']){
                        return 'validated';
                    }
                }

                if($historyItem_id['change_date'] == 0){
                    if($historyItem_id[$key] == $data_item){
                        return NULL;
                    }
                }

               
                
            }

            foreach($HDealDX as $HDealDX_item){
                if($HDealDX_item['id_responsible'] !== $item['id_responsible']){
                    $this->DB->ChangeDateDeal($item);
                    return 'change';
                }
            }
            
        }

        return 'validated';
    }

    public function GetPull($pool) {

        $items = $pool['items'];
        $ThisTime = $pool['ThisTime'];
        $PullRespUser = [];
        $pio = 0;

        $pulrec = [];
        foreach($items as $keyDeal => $item){

         if(isset($pool['force'])){
            $result = $this->WidthOutHistory(['item' => $item, 'timeStateVisit' => $timeStateVisit, 'force' => 'force']);
            die();
         }

          $historyItem = NULL;
          $HDealDX = NULL;

          // история по сделке - возвращает записи по id сделки
          $historyItem = $this->DB->GetHistoryDeal($item);
          $HDealDX = $this->DB->GetHDealDX($item);

          $timeStateVisit = $this->TimeCallEmet($item);

          echo '<PRE>';
          print_r(['проверяем сделку' => $item['id_crm_deal']]);
          echo '</PRE>';

          echo '<PRE>';
          print_r(['timeStateVisit' => $timeStateVisit]);
          echo '</PRE>';

          if(isset($timeStateVisit['state']) && !empty($timeStateVisit)){

            if($timeStateVisit['state'] == 'past'){

                if(((int)$timeStateVisit['minut'] > 60) || ((int)$timeStateVisit['minut'] == 60)){
                    $item['STATE'] = '>>1hour';
                    $timeStateVisit = '>>1hour';
                }
                else{
                    $item['STATE'] = 'past';
                    $timeStateVisit = 'past';
                }
               
            }
          }
          else{
            $item['STATE'] = $timeStateVisit;
          }


          $dealCan = $this->CanPush($item,$historyItem,$HDealDX);

          if($dealCan){

            $pulrec[] = $item['STATE'];
           
           if($historyItem){
                $result = $this->WidthHistory(['item' => $item, 'historyItem' => $historyItem, 'timeStateVisit' => $timeStateVisit, '$dealCan' => $dealCan]);
            } else{
                $result = $this->WidthOutHistory(['item' => $item, 'timeStateVisit' => $timeStateVisit]);
            }

          }
          else{
            
            print_r(['cant push']);
          }
      
        }
    }

    public function WidthHistory($pool) {
        $item = $pool['item'];
        $historyItem = $pool['historyItem'];
        $timeState = $pool['timeStateVisit'];
        $return = [];
    
        if (
            isset($historyItem['uf_time_visit']) && !empty($historyItem['uf_time_visit']) &&
            isset($Item['uf_time_visit']) && !empty($Item['uf_time_visit'])
        ) {
            if ($historyItem['uf_time_visit'] !== $item['uf_time_visit']) {
                print_r(['меняем время']);
                $this->DB->ChangeDateDeal($item);
                return 'CANGEDATE';
            }
        }        

        if($pool['$dealCan'] == 'change'){
            $historyItem[0]['stateChange'] = 'change';
        }

        

        return $this->processTimeStateVisit($item, $timeState, $historyItem);
    }
    

    public function WidthOutHistory($pool) {

        $item = $pool['item'];
        $timeStateVisit = $pool['timeStateVisit'];
        $return = [];
        $historyItem = NULL;
        if(isset($pool['force'])){
            $item['force'] = 'force';
        }
        return $this->processTimeStateVisit($item, $timeStateVisit, $historyItem);
    }

    public function processTimeStateVisit($item, $timeState, $historyItems) {

        $item['STATE'] = $timeState;
        $subtype = NULL;

        if(isset($item['force'])){
            echo '<PRE>';
            print_r(['принудительная отправка боту в телеграмм']);
            echo '</PRE>';
            $this->DB->LetBotBase($item, $subtext, $subtype);
        }
       
        if($item['stage_id'] == 'Встреча подтверждена'){
            print_r(['отправка боту Встреча подтверждена']);
            $this->DB->LetBotBase($item, $subtext, $subtype);
        }
        if($item['stage_id'] == 'Встреча принята'){

           
            if($timeState == '>15'){
                print_r(['отправка боту Встреча принята, но до 15 мин еще далеко']);
                $this->DB->LetBotBase($item, $subtext, $subtype);
            }
            if(($timeState == '<15') || ($timeState == '15')){
                print_r(['отправка боту Встреча принята, за 15 мин']);
                if(($timeState == '15') || ($timeState == '<15')){
                    $subtext = '15';
                    $subtype = 15;
                }
                if($subtype){
                    $this->DB->LetBotBase($item, $subtext, $subtype);
                }
            }
            if(($timeState == '<10') || ($timeState == '10')){
                print_r(['отправка боту, за 10 мин']);
                if(($timeState == '10') || ($timeState == '<10')){
                    $subtext = '10';
                    $subtype = 10;
                }
                if($subtype){
                    $this->DB->LetBotBase($item, $subtext, $subtype);
                }
            }
            if($timeState == '>>1hour'){
                print_r(['этап >>1hour с момента встречи']);
                print_r(['если нет в tg_approve_btn метки 10minY или 10minN']);
                $this->DB->LetBotBase($item, $subtext, $subtype);
            }
        }

        $this->DB->CreateAnEntry(['item' => $item, 'historyItem' => $historyItems[0]]);
        
    }
    
    public function CheckCallTable($USER) {
        \CModule::IncludeModule('voximplant');
        $dataUserCall = \Bitrix\Voximplant\Model\CallTable::GetList([
          'select' => ['STATUS'],
          'filter' => ['USER_ID' => $USER]
        ]);
      
        foreach($dataUserCall as $dataUserCall_item){
          if(($dataUserCall_item['STATUS'] !== 'finished') && (isset($dataUserCall_item['STATUS']) || !empty($dataUserCall_item['STATUS']))){
            return NULL;
          }
        }
        
        return 'NE_ZANAT';
    
    }

    public function TimeCallEmet($item) {

        $result = 'NONE';

        if($item['stage_id'] == 'Встреча подтверждена'){
            $result = 'moment_podtver';
        }

        if ($item['stage_id'] == 'Встреча принята') {
            if (isset($item['uf_time_visit']) && !empty($item['uf_time_visit'])) {
                $dateTimeString = $item['uf_time_visit'];
                $dateTimeObject = new \DateTime($dateTimeString);
                $currentDateTime = new \DateTime();
        
                // Отсекаем секунды и миллисекунды
                $dateTimeObject->setTime($dateTimeObject->format('H'), $dateTimeObject->format('i'), 0);
                $currentDateTime->setTime($currentDateTime->format('H'), $currentDateTime->format('i'), 0);
                
                // Вычисляем разницу в минутах
                $interval = $currentDateTime->diff($dateTimeObject);
                $minutesDifference = $interval->days * 24 * 60 + $interval->h * 60 + $interval->i;
        
                // Проверяем, прошла ли дата
                if ($dateTimeObject < $currentDateTime) {
                    return [
                        'state' => 'past',
                        'minut' => $minutesDifference
                    ]; // Дата прошла
                }

                if ($minutesDifference > 15) {
                    $result = '>15';
                } elseif ($minutesDifference < 15) {
                    $result = '<15';
                } else {
                    $result = '15';
                }

                if($minutesDifference == 10) {
                    $result = '10';
                }

                if($minutesDifference < 10) {
                    $result = '<10';
                }

            }
        }
        
    
        return $result;
    }


    public function GetContactNumer($p) {

         $cont = $p['item']['contact_id'];

          if (\Bitrix\Main\Loader::IncludeModule("crm")) {
            $ContactInfo = \Bitrix\Crm\ContactTable::GetList([
                'select' => ['PHONE'], 
                'filter' => ['ID' => $cont ]
              ]);
          }
          $Contact = $ContactInfo->fetch();
      
          $PHONE = $Contact['PHONE'];
      
          return $PHONE;
        
    }
    
}