<?php
namespace BazisCM\Workspace\Tools;

class Base {

    public function writeLog($data) {
        $logFile = '/mnt/data/bitrix/local/php_interface/classes/BazisCM/log/log_'.$data['meta'].time().'.txt';
        //$logFile = __DIR__.'/log_'.$data['meta'].time().'.txt';
        $formattedData = var_export($data['body'], true);
        file_put_contents($logFile, '<?php $array = ' . $formattedData . ';', FILE_APPEND);
    }

    
    public function GoWide($data) {
        $id_tg = '437532761';
        $message_text = 'ошибка:'.$data;

        $data = [
            'chat_id' => $id_tg,
            'text' => $message_text,
        ];

        $botToken = '6667463040:AAHVa_ZkV32Ko7PSrI7qENYmAJqxxKpcxHE';
        $apiUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);


    }
    
    
}