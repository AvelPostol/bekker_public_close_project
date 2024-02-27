<?php
require_once ('Workspace/Tools/CurlManager.php');
require_once ('Workspace/Bitrix/Common.php');


class UpdListElem {

    public function __construct($metod) {
        $apiKey = '02f532d7052b495dadb503a5c54d09f4';
        $this->CurlManager = new \BazisCM\Workspace\Tools\CurlManager($apiKey, $metod);
    
       // $this->Base = new \BazisCM\Workspace\Tools\Base();
        $this->BitrixCommon = new \BazisCM\Workspace\Bitrix\Common();
      }

      public function GClients($p){
        $BitrixCommon = $this->Checks();
      }
      
      public function Checks($p)
      {

        if (\Bitrix\Main\Loader::IncludeModule("crm")) {

            $DealsInfo = \Bitrix\Crm\DealTable::GetList([
              'select' => ['ID','UF_CRM_1694018792723', '*', 'CATEGORY_ID', 'STAGE_ID'], 
            ]);

            $DealsInfoData = $DealsInfo->fetchAll();
            if ($DealsInfoData !== false) {
                return $DealsInfoData;
            }
            else{
                return NULL;
            }

        }

        return NULL;
      }

      public function Check($numer)
      {

        if (\Bitrix\Main\Loader::IncludeModule("crm")) {


            $DealsInfo = \Bitrix\Crm\DealTable::GetList([
              'select' => ['ID','UF_CRM_1694018792723', '*', 'CATEGORY_ID', 'STAGE_ID'], 
              'filter' => ['UF_CRM_1694018792723' => $numer], //$p['client']['data']['number']] //'123321'
              'limit' => 1
            ]);

            $DealsInfoData = $DealsInfo->fetchAll();
            if ($DealsInfoData !== false) {
                return $DealsInfoData[0];
            }
            else{
                return NULL;
            }

        }

        return NULL;
      }
            
      // поиск документа/элемента списка в битрикс по номеру договора
      public function GetIblokList($data){

        

        if(!isset($data['UF_CRM_1694018792723']) || empty($data['UF_CRM_1694018792723'])){
            return;
        }

        $arFilter = [
            "IBLOCK_ID" => 5,
            "ACTIVE" => "Y",
            'PROPERTY_93' => $data['UF_CRM_1694018792723']
        ];
        $arSort = [
            "ID" => "ASC", 
        ];
        $arSelect = array(
            "ID",
            'PROPERTY_97', // связь с CRM -> сделка
            'PROPERTY_93', // номер договора
        );

        

        // проверяем фиьлтром есть ли элементы списка с таким ID ДОГОВОРА, если несколько то собираем файлы
        $rsElements = \CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
        $lr = 0;

        while ($arElement = $rsElements->Fetch()){

            $crmDeal = '';
           
            if(isset($arElement['PROPERTY_93_VALUE']) && !empty($arElement['PROPERTY_93_VALUE'])){
                $crmDeal = $this->Check($arElement['PROPERTY_93_VALUE']);
                if($crmDeal){
                    
                    $lr = $lr + 1;
                    $arrIblockItem[] = $arElement;
                }
            }
        }
        

        $update = [];

        print_r(['$arrIblockItem' => $arrIblockItem]);

        if(isset($arrIblockItem) && !empty($arrIblockItem)){
            foreach($arrIblockItem as $item){
                $update = $this->GDeals($item, $update);
             }
     
             if(isset($update) && !empty($update)){
                 foreach($update as $key_u => $val_u){
                     $this->IblokItemUpdate(['idListElem' => $key_u, 'svaz_val' => $val_u]);
                 }
             }
     
        }

        print_r(['$update' => $update]);

      }

      public function GDeals($item, $update){
       // получаем ID сделки
       $crmDeal = $this->Check($item['PROPERTY_93_VALUE']);
       
       if($crmDeal){
           $CRM_DEAL_ID = 'D_'.$crmDeal['ID'];
           if(isset($CRM_DEAL_ID)){
            if($CRM_DEAL_ID !== $item['PROPERTY_97_VALUE']){
                $update[$item['ID']] = $CRM_DEAL_ID;
            }
           }
       }

       
       return $update;
      }

      public function IblokItemUpdate($update){
        $el_id = $update['idListElem'];
        $iblock_id = 5;
        $prop[97] = ['VALUE'=>$update['svaz_val']];
        $item = CIBlockElement::SetPropertyValuesEx($el_id, $iblock_id, $prop);

        print_r([$el_id, $iblock_id, $prop]);

      }
      
}
