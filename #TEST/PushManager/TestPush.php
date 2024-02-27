<?php

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS',true);

$_SERVER["DOCUMENT_ROOT"] = "/mnt/data/bitrix";
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

if (\Bitrix\Main\Loader::IncludeModule("crm")) {
    $CCrmEvent = new \CCrmEvent();
    $rep = $CCrmEvent->Add(
        array(
            'USER_ID' => 37,
            'ENTITY_TYPE'=> 'DEAL',
            'ENTITY_ID' => 366917,
            'EVENT_ID' => 'INFO',
            'EVENT_TEXT_1' => '$event',
            )
        );
    print_r(['test' => $rep]);
}
