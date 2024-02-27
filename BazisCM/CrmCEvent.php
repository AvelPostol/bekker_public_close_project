<?php
namespace BazisCM;

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS',true);

$_SERVER["DOCUMENT_ROOT"] = "/mnt/data/bitrix";
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
require_once ('/mnt/data/bitrix/local/php_interface/classes/BazisCM/UpdListElem.php');

// подключение классов
require_once ('head.php');

use Bitrix\Main\Loader;
use Bitrix\Main\Type;

/**
  * Получаем сделку -> проверяем условия, получаем ID ответственного пользователя
  * Получение ID салона пользователя  
  *
  */

  class CrmCEvent
  {
      private $Base;
      private $Main;
  
      public function __construct() {
        $this->Main = new \BazisCM\Main();
       // $this->Base = new \BazisCM\Workspace\Tools\Base();
        $this->Crud = new \BazisCM\Workspace\Bitrix\Crud();
      }

      // Функция для проверки наличия цифр в строке
      public function hasDigit($str) {
        return preg_match('/\d/', $str);
      }

      public function GetManager($data) {
          if (\Bitrix\Main\Loader::IncludeModule("main")) {

              \Bitrix\Main\Loader::includeModule("crm");

              $deal = \Bitrix\Crm\DealTable::GetList([
                'select' => ['*', 'UF_*'],
                'filter' => ['ID' => $data['deal']['ID']] 
              ])->fetch();

              $data['deal'] = $deal;

              $stage = $data['deal']['STAGE_ID'];
              $category = $data['deal']['CATEGORY_ID'];
              $ManagerID = $data['deal']['ASSIGNED_BY_ID'];

              $manager = \Bitrix\Main\UserTable::GetList([
                  'select' => ['UF_BASIS_SALON'],
                  'filter' => ['ID' => $ManagerID] 
              ])->fetch();

              if(!isset($manager['UF_BASIS_SALON']) || empty($manager['UF_BASIS_SALON'])){
                return;
              }

              $numeD = '';

              if(isset($data['deal']['UF_CRM_1694018792723']) && !empty($data['deal']['UF_CRM_1694018792723'])){
                $numeD = $data['deal']['UF_CRM_1694018792723'];
              }
              
                    
              $item['deal'] = $data['deal']['ID'];
              $item['user'] = $data['deal']['ASSIGNED_BY_ID'];
              $item['groupy'] = $manager['UF_BASIS_SALON'];
              
              $HDealitem = $this->GetHistoryDeal($data['deal']['ID']);
              
              if(isset($HDealitem) && !empty($HDealitem)){
                $HDeal = $HDealitem[0];
              }

              if(isset($HDeal) && !empty($HDeal)){ // если по сделке уже есть записи

                
                
                if(intval($ManagerID) !== intval($HDeal['user'])){ // если сменился ответственный 
                  $this->Main->Conroller(['manager' => $manager, 'deal' => $data['deal'], 'test' => $item, 'stop' => 'stop', 'oldNume' => $numeD]);
                }else{
                  if($HDeal['groupy'] !== $manager['UF_BASIS_SALON']){ // если сменилась группа
                    if (!$this->hasDigit($HDeal['groupy'])) {
                      $this->Main->Conroller(['manager' => $manager, 'deal' => $data['deal'], 'test' => $item, 'stop' => 'stop', 'oldNume' => $numeD]);
                    }
                  } else{
                    if($data['deal']['UF_CRM_1694018792723'] == NULL){ // номер договора пуст
                      $this->Main->Conroller(['manager' => $manager, 'deal' => $data['deal'], 'test' => $item, 'stop' => 'stop']);
                    }  
                  }
                }
                
              }
              else{ // истории еще нет

                // если в нужной стадии
                if ((($category == '11') && ($stage == 'C11:NEW')) || ($category == '12') && ($stage == 'C12:PREPAYMENT_INVOIC')) {

                  if( !isset($data['deal']['UF_CRM_1694018792723']) || empty($data['deal']['UF_CRM_1694018792723'])){ // номер договора пуст
                    $this->Main->Conroller(['manager' => $manager, 'deal' => $data['deal'], 'test' => $item, 'stop' => 'stop']);
                  } else{ 
                    if (strpos($data['deal']['UF_CRM_1694018792723'], $manager['UF_BASIS_SALON']) === false) { // если сменился ответственный
                      if (!preg_match('/^[0-9]*[a-zA-Z]{1}[0-9]*$/', $data['deal']['UF_CRM_1694018792723'])) { // проверка для новых записей, чтобы в старом формате не переписывать номер договора
                        $this->Main->Conroller(['manager' => $manager, 'deal' => $data['deal'], 'test' => $item, 'stop' => 'stop', 'oldNume' => $numeD]); // если это не номер договора в старом формате
                      }
                    }
                  }

                } else { // проверяем была ли смена ответственного
                  if( isset($data['deal']['UF_CRM_1694018792723']) && !empty($data['deal']['UF_CRM_1694018792723'])){
                    if (strpos($data['deal']['UF_CRM_1694018792723'], $manager['UF_BASIS_SALON']) === false) { // если сменился ответственный
                      if (!preg_match('/^[0-9]*[a-zA-Z]{1}[0-9]*$/', $data['deal']['UF_CRM_1694018792723'])) {
                        $this->Main->Conroller(['manager' => $manager, 'deal' => $data['deal'], 'test' => $item, 'stop' => 'stop', 'oldNume' => $numeD]);
                       }
                    }
                  }
                }

              }
          }
      }

      public function GetHistoryDeal($deal) {

        try{
          
          $prefix = $p['id_crm_deal'];
          $prefix_resp = $p['id_responsible'];

          // id	deal	user!	numer	groupy!	timest	

          $managerQuery = "SELECT * FROM history_deal_numer_test WHERE deal='$deal' ORDER BY timest DESC LIMIT 1";
          $managerResult = $this->Crud->Get(['request' => $managerQuery]);
    
          if (!$managerResult) {
              return null;
          }
    
          return $managerResult;
    
        } catch (\Exception $e) { 
            return null; 
        }
          
      }
    

      public function Check($arFields){
        $instance = new CrmCEvent();
        $manager = $instance->GetManager([
          'deal' => $arFields,
          ]);
      }

      public static function Controller(&$arFields)
      {

        $instance = new CrmCEvent();

        try{
          $manager = $instance->Check($arFields);
        }catch (Exception $e) {
          
        }

        $metod = '/api-orders-exchange-public/orders';
        $UpdListElem = new \UpdListElem($metod);

        try{
          $UpdListElem->GetIblokList($arFields);
        }catch (Exception $e) {
          
        }
    
      }
  }


