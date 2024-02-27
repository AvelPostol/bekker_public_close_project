<?php

class BotTelegram {

    
    public function LetBotBase($entityId) {

        $Deals = $this->GetPullDeal(
            [
                'select' => ['ID', 'DATE_CREATE', 'UF_CRM_1694160451992', 'UF_CRM_1696584638945', 'UF_CRM_1693988021524', 'UF_CRM_1693585313113', 'UF_CRM_1694018792723', '*', 'STAGE_ID', 'CATEGORY_ID', 'ASSIGNED_BY_ID', 'UF_CRM_1693485339146'],
                 'filter' => ['ID' => $entityId] 
            ]
        );

        $rsUsers = \Bitrix\Main\UserTable::GetList([
            'select' => ['UF_TELEGRAM_ID'],
            'filter' => ['ID' => $Deals['ASSIGNED_BY_ID']]
        ])->fetch();
                
        $id_tg = $rsUsers['UF_TELEGRAM_ID'];

        $this->LetBot($Deals, $id_tg);
       
    }

    public function LetBot($pool, $id_tg) {

        \CModule::IncludeModule('crm'); 

        $dealId = $pool['ID'];

        $datetimeVisit = $pool['UF_CRM_1693988021524'];

        if(isset($pool['UF_CRM_1693585313113']) && !empty($pool['UF_CRM_1693585313113'])){
            $adr = json_decode($pool['UF_CRM_1693585313113']);
            $fullAddress = $adr->address;
            $adres = $fullAddress;
        }

        $data = [
            'chat_id' => $id_tg,
            'text' => $message_text,
        ];

        $message_text = "
        Встреча ОТМЕНЕНА оператором по сделке ".$dealId." 
        Дата и время встречи: ".$datetimeVisit."
        Адрес клиента: ".$adres;

        $data = [
            'chat_id' => $id_tg,
            'text' => $message_text,
        ];

        $this->SendmessHelper($data);
        
    }

    public function SendmessHelper($data) {
        $botToken = '6489328913:AAFJ5biTuinVmedStG2DBjqmDYWlAQfMdoU';
        $apiUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        if ($response === false) {
            $ery = 'Ошибка отправки запроса' . curl_error($ch);
            print_r([$ery]);

        }
        else{
            print_r(['LetBotBase' => $data]);
        }

    }

    public function GetPullDeal($ParamForSearch)
      {

        \CModule::IncludeModule('crm'); 

        $arDeals=\Bitrix\Crm\DealTable::getList($ParamForSearch)->fetchAll();

        
        $deals=[];
        foreach($arDeals as $deal){
            $deals=$deal;
        }
        if (isset($deals)) {
            return $deals;
        }
        else{
             return NULL;
        }

        return NULL;
    }

}



