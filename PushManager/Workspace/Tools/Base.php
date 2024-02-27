<?php
namespace PushManager\Workspace\Tools;
class Base
{
   public static function writeLog($data) {
      $logFile = '/mnt/data/bitrix/local/php_interface/classes/PushManager/log_'.time().'.txt';
      $formattedData = var_export($data, true);
      file_put_contents($logFile, '<?php $array = ' . $formattedData . ';', FILE_APPEND);
  }
}

