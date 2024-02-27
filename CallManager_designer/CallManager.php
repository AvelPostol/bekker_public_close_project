<?php
namespace CallCustom;

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS',true);

$_SERVER["DOCUMENT_ROOT"] = "/mnt/data/bitrix";
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

require_once ('head.php');
ob_end_flush();
error_reporting(E_ERROR);
ini_set('display_errors', 1);

class CallManager {

    public function __construct() {

		$currentDateTime = new \DateTime();
		$this->time = $currentDateTime->format('Y-m-d\TH:i:s.u\Z');

		$this->CurlManagerCall = new \CallCustom\Workspace\Tools\Call();
		$this->CRM = new \CallCustom\Workspace\Bitrix\CRM();
		$this->Base = new \CallCustom\Workspace\Tools\Base();
		$this->CreateItems = new \CallCustom\Workspace\Tools\CreateItemsForCheck();
		$this->CheckItemForCall = new \CallCustom\Workspace\Tools\CheckItemForCall();

    }
  
    public function Main(){

	// дава время встречи - UF_CRM_1693988021524
	// номер договора - UF_CRM_1694018792723
	// UF_CRM_1693485339146 - принять встречу
	// UF_CRM_1693585313113 - адресс

	   
	$idfilter = 0;

	while(true){

		$Deals = $this->CRM->GetPullDeal(
			[
				'select' => ['ID', 'DATE_CREATE', 'UF_CRM_1694343013', 'UF_CRM_1694160451992', 'CONTACT_ID', 'UF_CRM_1696584638945', 'UF_CRM_1693988021524', 'UF_CRM_1693585313113', 'UF_CRM_1694018792723', 'STAGE_ID', 'CATEGORY_ID', 'ASSIGNED_BY_ID', 'UF_CRM_1693485339146'],
				'limit' => '100000',
				'order' => [
					'ID' => 'ASC'
				],
				'filter' => [
					//">=DATE_MODIFY" => \Bitrix\Main\Type\DateTime::createFromTimestamp(strtotime("2023-12-26 17:00:00")),
					'>ID' => $idfilter
				]
				
			]
		);

		
		if(isset($Deals) && !empty($Deals)){
			$idfilter = max(array_keys($Deals));
			$ItemsDeal = $this->CreateItems->GetPull(['deals' => $Deals]);
			$checkCall_list = $this->CheckItemForCall->GetPull(['items' => $ItemsDeal]);
		}
		else{
			die();
		}
		unset($Deals);

	}

	
	


    }

}

$CallManager = new CallManager();
$orders = $CallManager->Main();

/*
CREATE TABLE cm_field_config (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,      
	typefield VARCHAR(255) NOT NULL,   
	entity_in_b24 VARCHAR(255) NOT NULL,  
	require_field VARCHAR(255) NOT NULL
)*/

/*
CREATE TABLE cm_type_call (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,      
	type_call VARCHAR(255) NOT NULL,   
	entity_in_b24 VARCHAR(255) NOT NULL,  
	value_entity VARCHAR(255) NOT NULL,
	priority VARCHAR(255) NOT NULL,
)
*/


/*
CREATE TABLE cm_history (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,               
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,     
    id_responsible VARCHAR(255) NOT NULL,                    
	contact_id VARCHAR(255) NOT NULL,                   
    uf_time VARCHAR(255),                     
    id_crm_deal INT NOT NULL,     

	type_call VARCHAR(255),                    
	category_call VARCHAR(255), 

	is_active TINYINT(1) DEFAULT 1,                    

	change_date TINYINT(1) DEFAULT 0,                  
	
	twenty_four_hour VARCHAR(255) NOT NULL DEFAULT 'not yet',     
	three_hour  VARCHAR(255) NOT NULL DEFAULT 'not yet',         

	status_called VARCHAR(255) NOT NULL DEFAULT 'not yet',       
	breaktime VARCHAR(255),                              
	start_day VARCHAR(255),                            
    stoptime VARCHAR(255) NOT NULL DEFAULT 'not'    
);*/




/*

// 1)
// возвращаем статус онлайн/не онлайн
GLOBAL $USER;
$online = \CUser::IsOnLine($id_responsible);
if(!$online){
 	echo 'юзер не в сети';
}
else{
	echo 'юзер в сети';
}
return $online;

// 2)
// возвращаем статус занятости для конкретного юзера
\CModule::IncludeModule('voximplant');
$dataUserCall = \Bitrix\Voximplant\Model\CallTable::GetList([
  'select' => ['STATUS'],
  'filter' => ['USER_ID' => $USER]
]);

foreach($dataUserCall as $dataUserCall_item){
  if($dataUserCall_item['STATUS'] !== 'finished'){
	echo 'юзер сейчас разговаривает по телефону';
	return $dataUserCall_item;
  }
}

// 3)
// возвращаем статус перерыв/в работе
$obUser = new \CTimeManUser($ResponsibleID);
return $obUser->State();

// 4)
// возвращаем статусы начала рабочего дня
$obUser = new \CTimeManUser($ResponsibleID);
$arInfo = $obUser->GetCurrentInfo(); 
return $arInfo;*/

/*
CREATE TABLE cm_telegram_mess (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,      
	id_crm_deal VARCHAR(255) NOT NULL,   
	state_send_mess VARCHAR(255) NOT NULL,  
	type_mess VARCHAR(255) NOT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
	id_responsible VARCHAR(255) NOT NULL
)*/

/*
CREATE TABLE cm_di----s_mess_history_clone (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,               
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,     
    id_responsible VARCHAR(255) NOT NULL,     
	id_crm_deal INT NOT NULL,                  
	contact_id VARCHAR(255) NOT NULL,                   
    uf_time VARCHAR(255),                     

	change_date TINYINT(1) DEFAULT 0,                  
	
	status_mess_moment_podtver VARCHAR(255) DEFAULT 'not yet',  
	minutes_mess VARCHAR(255) DEFAULT 'not yet',  
	status_mess_moment_prinata VARCHAR(255) DEFAULT 'not yet',
	status_mess_ten_min_wait VARCHAR(255) DEFAULT 'not yet',
	status_mess_ten_min VARCHAR(255) DEFAULT 'not yet'    
);*/