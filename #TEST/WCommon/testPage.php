<?php

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS',true);

$_SERVER["DOCUMENT_ROOT"] = "/mnt/data/bitrix";
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
require_once('Context.php');
require_once('LogPush.php');

$ret = \ContextCust::GetUrl();

$LogPush = new \WCommon\LogPush();
$LogPush->Push($ret);

//print_r(['$ret' => $ret]);