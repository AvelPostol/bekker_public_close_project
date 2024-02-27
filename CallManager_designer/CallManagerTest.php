<?php
namespace CallCustom;

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS',true);

$_SERVER["DOCUMENT_ROOT"] = "/mnt/data/bitrix";
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

require_once ('head.php');

error_reporting(E_ERROR);
ini_set('display_errors', 1);

class CallManagerTest {

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

	$Deals = $this->CRM->GetPullDeal(
		[
			'select' => ['ID', 'DATE_CREATE', 'UF_CRM_1694160451992', 'UF_CRM_1696584638945', 'UF_CRM_1693988021524', 'UF_CRM_1693585313113', 'UF_CRM_1694018792723', '*', 'STAGE_ID', 'CATEGORY_ID', 'ASSIGNED_BY_ID', 'UF_CRM_1693485339146'],
		  	'filter' => ['ID' => '534555'],
		]
	); 

	$ItemsDeal = $this->CreateItems->GetPull(['deals' => $Deals]);

	$checkCall_list = $this->CheckItemForCall->GetPull(['items' => $ItemsDeal]);

    }

}

$CallManager = new CallManagerTest();
$orders = $CallManager->Main();