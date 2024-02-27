<?php
namespace CallCustom\Workspace\Bitrix;

/*
    Класс для обращения к CRM битрикс
*/

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

    }

        