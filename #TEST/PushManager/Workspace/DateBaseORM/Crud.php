<?php
namespace PushManager\Workspace\DateBaseORM;

require_once ('/mnt/data/bitrix/local/php_interface/classes/WCommon/Crud.php');

use WCommon\Crud as ParentCrud;

class Crud extends ParentCrud {

    public function GetHistory($message_id,$chat_id) {

        $host = \ContextCust::GetUrl();
        if($host){
            $table_name = 'tg_approve_btn_main';
        } else{
            $table_name = 'tg_approve_btn_main';
        }

        $data = self::GetHistoryItem([
            'table_name' => $table_name,
            'where' => [
                'message_id' => $message_id,
                'chat_id' => $chat_id,
                'live' => 'yep'
            ],
            'order' => 'created_at',
        ]);
  
        return $data;
    }

    public function Create($data) {

        $host = \ContextCust::GetUrl();
        if($host){
            $table_name = 'tg_approve_btn_main';
        } else{
            $table_name = 'tg_approve_btn_main';
        }

        $Create = parent::syncDataWithDatabase(['data' => [$data], 'table_name' => $table_name]);
        
        if(isset($Create) && !empty($Create)){
            return $Create;
        } else{
            return false;
        }
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


    public function Upd($data) {

        $Create = parent::UpdDatabase($data);
        
        if(isset($Create) && !empty($Create)){
            return $Create;
        } else{
            return false;
        }
    }


    
    public function Change($item) {
        $LogPush = new \WCommon\LogPush();
        //$LogPush->Push($item['status']);
        $mysqli = $this->mysqli;
        $message_id = $item['message_id'];
        $chat_id = $item['chat_id'];
        $callback_query_id = $item['callback_query_id'];

        $st = 'approved';

        if(isset($item['status']) && !empty($item['status'])){
            $st = $item['status'];
        }

        $host = \ContextCust::GetUrl();
        if($host){
            $table_name = 'tg_approve_btn_main';
        } else{
            $table_name = 'tg_approve_btn_main';
        }

        $ifyet = "SELECT * FROM $table_name WHERE message_id='$message_id' AND chat_id='$chat_id' AND live='yep'";
        $resultifyet = parent::Get(['request' => $ifyet]);

        if($resultifyet[0]['callback_query_id'] == $item['callback_query_id']){
            return [
                'return' => 'DUBLE_ZAPROS',
            ];
        }

        if(($resultifyet[0]['status'] == '10minN') || ($resultifyet[0]['status'] == '10minY')){
            return [
                'return' => 'YET_REFLECT',
            ]; 
        }


        if($resultifyet[0]['status'] !== $item['status']){

            $managerQuery = "UPDATE $table_name SET status='$st', callback_query_id='$callback_query_id' WHERE message_id=$message_id AND chat_id=$chat_id AND live='yep'";
            $wget = parent::WGet(['request' => $managerQuery]);
            
            $managerQuery = "SELECT * FROM $table_name WHERE message_id='$message_id' AND chat_id='$chat_id' AND live='yep'";
            $result = parent::Get(['request' => $managerQuery]);


            if (!$result) {
                return false;
            }
            else{
                return [
                    'return' => 'yep',
                    'relust' => $result[0]
                ];
            }
            
        } else{
            return [
                'return' => 'yet yep',
                'relust' => $result[0]
            ];
        }
   
    }
}


/*

$CallManager = new \PushManager\Workspace\DateBaseORM\Crud();
$orders = $CallManager->Change([
    'message_id' => 754,
    'chat_id' => 437532761
]);
*/