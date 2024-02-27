<?php
namespace BazisCM\Workspace\Bitrix;

  class Controller
  {
    public static function Check($p)
    {

       //   RU => ANGL
       $alfa = [
        'Т'=>'T',
        'К'=>'K',
        'Е'=>'E',
        'А'=>'A',
        'О'=>'O',
        'Р'=>'P',
        'Х'=>'X',
        'М'=>'M',
        'Н'=>'H',
        'С'=>'C',
        'В'=>'B'
      ];

      $modifiedText = NULL;
      $ContractUID = NULL;

      \Bitrix\Main\Loader::IncludeModule("crm");

      foreach($alfa as $key_i => $val_i){
        $text = $p['UF_BASIS_SALON'];
        $modifiedText = str_replace($key_i, $val_i, $text);
        if($modifiedText !== NULL){
          $p['UF_BASIS_SALON'] = $modifiedText;
        }
      }

        $found = true;
        // Поиск подстроки "K01"
        $substring = $p['UF_BASIS_SALON'];
    
        $arrNUm = [];
        
        while ($found) {

          $get = $substring.'%';

          $DealInfo = \Bitrix\Crm\DealTable::GetList([
            'select' => ['ID', 'UF_CRM_1694018792723'],
            'filter' => ['%=UF_CRM_1694018792723' => $get]
          ]);
          $R = false;

          foreach($DealInfo as $key => $rec){
            $R = 1;
            $pos = strpos($rec['UF_CRM_1694018792723'], $substring);
            // Если подстрока найдена
            if ($pos !== false) {
              $result = str_replace($substring, "", $rec['UF_CRM_1694018792723']);
              $arrNUm[$rec['ID']] = $result;
            }
          }

            // Если это не первая такая
            if ($R !== false) {
              
              if(!empty($arrNUm)){
                // Преобразование каждого элемента массива в число
                $numericArray = array_map('intval', $arrNUm);
                // Нахождение самого большого числа
                $maxValue = max($numericArray);
              }
                
                $numerics = ltrim($maxValue, '0');
                $numerics = intval($numerics) + 1;
    

                if(strlen($p['UF_BASIS_SALON']) == 2){
                  $numerics = sprintf("%04d", $numerics);
                }
                if(strlen($p['UF_BASIS_SALON']) == 3){
                  $numerics = sprintf("%03d", $numerics);
                }
                $found = false;
                $ContractUID = $p['UF_BASIS_SALON'].$numerics;
            }
            else{
                if(strlen($p['UF_BASIS_SALON']) == 2){
                  $numerics_n = '0001';
                }
                if(strlen($p['UF_BASIS_SALON']) == 3){
                  $numerics_n = '001';
                }
                $ContractUID = $p['UF_BASIS_SALON'].$numerics_n;
                $found = false;
            }   
        }

      return $ContractUID;
    }
    
    public function Push($data) {
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

    public function AddContractNumber($p){

      if (\Bitrix\Main\Loader::IncludeModule("crm")) {

          $entityId = $p['deal']['ID'];
          $entityFields = [
            'UF_CRM_1694018792723' => $p['NewNumberContract'],
            'UF_BUSINESSPROCESS_MOVE' => 'marker'
          ];


          $bCheckRight = false;
          $entityObject = new \CCrmDeal( $bCheckRight );
          $isUpdateSuccess = $entityObject->Update(
          $entityId,
          $entityFields,
          $bCompare = true,
          $arOptions = [
              /**
               * ID пользователя, от лица которого выполняется действие
               * в том числе проверка прав
               * @var integer
               */
              'CURRENT_USER' => \CCrmSecurityHelper::GetCurrentUserID(5005),

              /**
               * Флаг системного действия. В случае true у элемента не будут
               * занесены данные о пользователе который производит действие
               * и дата изменения элемента не изменится.
               * @var boolean
               */
              'IS_SYSTEM_ACTION' => false,

              /**
               * В случае true, битрикс создаст сообщение в ленту о изменении
               * @var boolean
               */
              'REGISTER_SONET_EVENT' => true,
              
              /**
               * Флаг обозначающий запрет на создании записи в timeline элемента
               * о создании.
               * @var char
               */
              //'DISABLE_TIMELINE_CREATION' => 'Y'
              //
              /**
               * Флаг для вызова системных событий.
               * При установке в false не будут срабатывать событие
               * @var boolean
               */
              'ENABLE_SYSTEM_EVENTS' => true,
          ]
      );

      if($isUpdateSuccess){

        if(isset($p['oldNume']) && !empty($p['oldNume'])){
          $event = 'Сгенерирован номер сделки '.$p['oldNume'].'->'.$p['NewNumberContract'];
        }
        else{
          $event = 'Сгенерирован номер сделки '.$p['NewNumberContract'];
        }
 
       
       $CCrmEvent = new \CCrmEvent();
       $CCrmEvent->Add(
          array(
             'USER_ID' => 5005,
             'ENTITY_TYPE'=> 'DEAL',
             'ENTITY_ID' => $entityId,
             'EVENT_ID' => 'INFO',
             'EVENT_TEXT_1' => $event,
          ), false
       );

          return true;
      }
      else{
        $this->Push($entityObject->LAST_ERROR.' $entityId: '.$entityId.' во время генерации номера сделки '.$p['NewNumberContract'].' AddContractNumber');
        return NULL;
      }
      }

    }

      
  }