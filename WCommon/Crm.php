<?php
namespace WCommon;
/*
    Класс для обращения к CRM битрикс
*/
require_once ('/mnt/data/bitrix/local/php_interface/classes/WCommon/LogPush.php');

class CRM
    {

      public function GetPullDeal($ParamForSearch)
      {
        \CModule::IncludeModule('crm');

        $deals=[];
        $arDeals = \Bitrix\Crm\DealTable::getList($ParamForSearch)->fetchAll();

        foreach($arDeals as $deal){
            $deals[$deal['ID']]=$deal;
        }
       
        if (isset($deals)) {
            return $deals;
        }
        else{
             return NULL;
        }
      }


      public function GetPhoneContact($contact_id)
      {
        // получаем мульти поля
        $multiFields = \Bitrix\Crm\FieldMultiTable::getList([
            'filter' => [
            'ELEMENT_ID' => $contact_id,
            'ENTITY_ID' => 'CONTACT',
            ],
        ]);

        $stateph = false;
        // adapt мульти поля
        while ($fieldItem = $multiFields->fetch())
        {
            if($fieldItem['TYPE_ID'] == 'PHONE'){
                return $fieldItem['VALUE'];
            }
        }

        return NULL;
      }

    /*
      обновляет сделку, делает запись в историю
    */
    public static function CrmUpdate($resuest)
    {

      $LogPush = new \WCommon\LogPush();

      $fields = $resuest['fields'];
      $user = $resuest['user'];
      $entityId = $resuest['entityId'];

      \Bitrix\Main\Loader::IncludeModule("crm");

      if(!isset($user) || empty($user)){
        // Если пользователь не указан, используем значение по умолчанию '37'
        $user = '37';
      }

      $currentFields = ['$currentFields' => ''];

      $starter = new \Bitrix\Crm\Automation\Starter(\CCrmOwnerType::Deal, $entityId);
      $starter->runOnUpdate($currentFields, $fields);

      $entityTypeId = \CCrmOwnerType::Deal;
      $factory = \Bitrix\Crm\Service\Container::getInstance()->getFactory($entityTypeId);
      $item = $factory->getItem($entityId);
      $item->setFromCompatibleData($fields);

      $operation = $factory->getUpdateOperation($item);

      $operation->disableAllChecks();
      $result = $operation->launch();

      if (!$result->isSuccess())
      {
        $LogPush = new \WCommon\LogPush();
        $LogPush->Push($result->getErrorMessages());
        $LogPush->Push('ошибка в wcommon\CrmUpdate');
      }

      
      // Переменная для хранения события
      $event = 'Пользователь ' . $user . ' обновил сделку: ';
      // Цикл по массиву для конкатенации данных
      foreach ($fields as $key => $value) {
         $event .= $key . ': ' . $value . ', ';
      }

      // Удаляем последнюю запятую и пробел
      $event = rtrim($event, ', ');

      $CCrmEvent = new \CCrmEvent();
      $rep = $CCrmEvent->Add(
         array(
            'USER_ID' => 37,
            'ENTITY_TYPE'=> 'DEAL',
            'ENTITY_ID' => $entityId,
            'EVENT_ID' => 'INFO',
            'EVENT_TEXT_1' => $event,
         ), false
      );

      return $rep;
     
   }

   public function GetChatID($id_resp) {

    $rsUsers = \Bitrix\Main\UserTable::GetList([
        'select' => ['UF_TELEGRAM_ID'],
        'filter' => ['ID' => $id_resp]
    ])->fetch();
            
    $id_tg = $rsUsers['UF_TELEGRAM_ID'];
    return $id_tg;
  }

}