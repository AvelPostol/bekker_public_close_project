<?php
namespace PushManager;
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS',true);

$_SERVER["DOCUMENT_ROOT"] = "/mnt/data/bitrix";
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

require_once ('/mnt/data/bitrix/local/php_interface/classes/PushManager/Workspace/DateBaseORM/Crud.php');

require_once ('/mnt/data/bitrix/local/php_interface/classes/WCommon/Crud.php');
require_once ('/mnt/data/bitrix/local/php_interface/classes/WCommon/LogPush.php');
require_once ('/mnt/data/bitrix/local/php_interface/classes/WCommon/Crm.php');
require_once ('/mnt/data/bitrix/local/php_interface/classes/WCommon/Context.php');

class PushReplay {

    public static function sendM($p, $data) {
        
       // $id_tg = '437532761';

        $Crud = new \PushManager\Workspace\DateBaseORM\Crud;
        $response = $Crud->SendMess($data);

        $Crud->Create([
            'id_crm' => $p['id_crm'],
            'message_id' => $response['result']['message_id'],
            'chat_id' => $response['result']['chat']['id'],
            'status' => 'create'
        ]);

    }



    public static function send($p) {
        
        /*$crm = new \WCommon\Crm();
        $GetChatID = GetChatID($id_resp);*/

       // $id_tg = '437532761';
       // 352326656

        $Crud = new \PushManager\Workspace\DateBaseORM\Crud;
        $response = $Crud->SendMess([
            'chat_id' => $p['chat_id'],
            'text' => $p['message_text'],
            'reply_markup' => json_encode(array(
                'inline_keyboard' => array(
                    array(
                        array(
                            'text' => 'Да',
                            'callback_data' => 'approve',
                        ),
                    )
                ),
            )),
        ]);

        if($response['ok'] === true){
            if($response['result']['reply_markup']['inline_keyboard'][0][0]['callback_data'] == 'approve'){
                $Crud->Create([
                    'id_crm' => $p['id_crm'],
                    'message_id' => $response['result']['message_id'],
                    'chat_id' => $response['result']['chat']['id'],
                    'status' => 'create'
                ]);
                return 'true';
            } else{
                return NULL;
            }
            
        } else{
            $LogPush = new \WCommon\LogPush();
            $LogPush->Push('Ошибка '.$response['description'].' для PushManager\PushReplay:send');
            return NULL;
        }
    }

    public static function getReplay($data) {

        $callback_query = $data->callback_query;
        $callback_query_id = $callback_query->id; 
        $callback_data = $callback_query->data;
    
        $from = $callback_query->from;
        $user_id = $from->id; // ID чата / юзера
        
        $message = $callback_query->message;
        $message_id = $message->message_id; // mess id

        $LogPush = new \WCommon\LogPush();
        /*$LogPush->writeLogSimple($data);
        die();*/

        // если пришло подтверждение
        if($callback_data == 'approve'){

            $Crud = new \PushManager\Workspace\DateBaseORM\Crud;

            // approved
            $apr = $Crud->Change([
                'message_id' => $message_id,
                'chat_id' => $user_id,
                'callback_query_id' => $callback_query_id,
                'status' => 'approved'
            ]);

            if($apr['return'] == 'DUBLE_ZAPROS'){
                return;
            }

            if($apr){

                if($apr['return'] === 'yet yep'){

                    // уже была до этого подтверждена сделка
                    $send = $Crud->SendMess([
                        'chat_id' => $user_id,
                        'text' => 'Встреча уже принята по этой сделке'
                    ]);
                    return;
                }

                $crm = new \WCommon\CRM();
                $Deals = $crm->GetPullDeal(
                    [
                        'select' => ['ID', 'DATE_CREATE', 'UF_CRM_1694160451992', 'UF_CRM_1696584638945', 'CONTACT_ID', 'UF_CRM_1693988021524', 'UF_CRM_1693585313113', 'UF_CRM_1694018792723', 'STAGE_ID', 'CATEGORY_ID', 'ASSIGNED_BY_ID', 'UF_CRM_1693485339146'],
                        'limit' => '1',
                        'filter' => [
                            'ID' => $apr['relust']['id_crm']
                        ]
                        
                    ]
                );

                

                $deal = $Deals[$apr['relust']['id_crm']];

                //$LogPush->Push($apr['relust']['id_crm']);

                $fullAddress = '';

                if(isset($deal['UF_CRM_1693585313113']) && !empty($deal['UF_CRM_1693585313113'])){
                    $adr = json_decode($deal['UF_CRM_1693585313113']);
                    $fullAddress = $adr->address;
                }

                $fio = '';

                if(isset($deal['CONTACT_ID']) && !empty($deal['CONTACT_ID'])){
                    $ContactInfo = \Bitrix\Crm\ContactTable::GetList([
                        'select' => ['*', 'PHONE', 'ADDRESS'], 
                        'filter' => [ 'ID' => $deal['CONTACT_ID'] ]
                    ]);
                    
                    $Contact = $ContactInfo->fetch();
                    $fio = $Crud->getFIO($Contact);
                }

$text = 'Встреча по сделке '.$apr['relust']['id_crm'].' принята в работу
Встреча подтверждена -> Встреча принята
ФИО клиента: '.$fio.'
Дата и время встречи: '.$deal['UF_CRM_1693988021524'].'
Адрес встречи: '.$fullAddress.'
Ссылка на маршрут: '.$deal['UF_CRM_1694160451992'];

                //  $text = 'Встреча подтверждена -> Встреча принята по сделке '.$apr['relust']['id_crm'];
                // отправляем отмашку что сделка принята
                $send = $Crud->SendMess([
                    'chat_id' => $user_id,
                    'text' => $text
                ]);

                
                // меняем стадию сделке
                $apr = $crm->CrmUpdate([
                    'entityId' => $apr['relust']['id_crm'],
                    'fields' => [
                        'UF_CRM_1693485339146' => 455
                    ]
                ]);
            }
        }


        if(($callback_data == 'approve_10time') || ($callback_data == 'not_approve_10time')){

            if($callback_data == 'not_approve_10time'){
                $status = '10minN';
                $fields = [
                    'UF_CRM_1693643868590' => 2243
                ];

            } else {
                $status = '10minY';
                $fields = [
                    'UF_CRM_1693643868590' => 2242
                ];
            }


            $Crud = new \PushManager\Workspace\DateBaseORM\Crud;
            $crm = new \WCommon\CRM();
            
            

            $host = \ContextCust::GetUrl();
            if($host){
                $table_name = 'tg_approve_btn_main';
            } else{
                $table_name = 'tg_approve_btn_main';
            }

            $ifyet = "SELECT * FROM $table_name WHERE message_id='$message_id' AND chat_id='$user_id' AND live='yep'";
            $resultifyet = $Crud->Get(['request' => $ifyet]);

            if(($resultifyet[0]['status'] !== '10minN') && ($resultifyet[0]['status'] !== '10minY')){

                $prefix = $resultifyet[0]['id_crm'];

                if($host){
                    $table_name = 'cm_dis_mess_history';
                } else{
                    $table_name = 'cm_dis_mess_history';
                }

                $ify = "SELECT * FROM $table_name WHERE id_crm_deal='$prefix' ORDER BY id DESC";
    
                
                $ifyet = $Crud->Get(['request' => $ify]);
    
                if($ifyet[0]['status_mess_ten_min'] == 'yet'){
                    $send = $Crud->SendMess([
                        'chat_id' => $user_id,
                        'text' => 'Ваше время истекло'
                    ]);
                    return;
                }
            }
            
            // status_mess_ten_min
    

            $apr = $Crud->Change([
                'message_id' => $message_id,
                'chat_id' => $user_id,
                'status' => $status,
                'callback_query_id' => $callback_query_id
            ]);


            if($apr['return'] == 'DUBLE_ZAPROS'){
                return;
            }

            if($apr['return'] == 'YET_REFLECT'){
                $send = $Crud->SendMess([
                    'chat_id' => $user_id,
                    'text' => 'Статус уже установлен'
                ]);
                return;
            }

            $crm = new \WCommon\CRM();


                $LogPush->writeLogSimple([
                    'меняем стадию на ' => 
                    [
                        'entityId' => $apr['relust']['id_crm'],
                        'fields' => $fields
                    ]
                    ]);


            // меняем стадию сделке
            $crm->CrmUpdate([
                'entityId' => $apr['relust']['id_crm'],
                'fields' => $fields
            ]);

            $host = \ContextCust::GetUrl();
            if($host){
                $table_name = 'cm_dis_mess_history';
            } else{
                $table_name = 'cm_dis_mess_history';
            }
            
            $Crud->UpdDatabase([
                'table_name' => $table_name,
                'key_column' => 'id_crm_deal',
                'data' => [
                    ['id_crm_deal' => $apr['relust']['id_crm'], 'status_mess_ten_min' => 'yet']
                ]
            ]);


            if(($apr['relust']['status'] !== '10minN') || ($apr['relust']['status'] !== '10minY')){
                if($apr['relust']['status'] == '10minY'){
                    $send = $Crud->SendMess([
                        'chat_id' => $user_id,
                        'text' => 'Статус установлен Встреча началась? -> ДА'
                    ]);
                }
                if($apr['relust']['status'] == '10minN'){
                    $send = $Crud->SendMess([
                        'chat_id' => $user_id,
                        'text' => 'Статус установлен Встреча началась? -> НЕТ'
                    ]);
                }
            }

        }
    }

}



$data = json_decode(file_get_contents('php://input'));

if(isset($data) && !empty($data)){
    PushReplay::getReplay($data);
    return true;
}

//PushReplay::getReplay('test');


/*
    отправляем сообщение -> создаем запись

CREATE TABLE tg_approve_btn_main (
    id INT AUTO_INCREMENT PRIMARY KEY,     
    id_crm INT NOT NULL,  
	message_id INT NOT NULL,   
	chat_id INT NOT NULL,  
	status VARCHAR(255) NOT NULL,
    callback_query_id VARCHAR(255),
    live VARCHAR(255) DEFAULT 'yep',
    timeemet timestamp CURRENT_TIMESTAMP
)

*/