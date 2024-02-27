<?php
require_once (__DIR__.'/crest.php');

$resulti = CRest::call(
	'placement.unbind',
	[
		 'PLACEMENT' => 'CRM_DEAL_DETAIL_TOOLBAR',
		// 'HANDLER' => 'https://bx24.kitchenbecker.ru/local/php_interface/classes/CallManager_designer/placement/placement.php',
	]
);

echo '<PRE>';
print_r(['$resulti' => $resulti]);
echo '</PRE>';