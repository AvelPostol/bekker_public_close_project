<?php
$token = "6489328913:AAFJ5biTuinVmedStG2DBjqmDYWlAQfMdoU";

$getQuery = array(
     "url" => "https://bx24.kitchenbecker.ru/local/php_interface/classes/PushManager/PushReplay.php",
);
$ch = curl_init("https://api.telegram.org/bot". $token ."/setWebhook?" . http_build_query($getQuery));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);

$resultQuery = curl_exec($ch);
curl_close($ch);

echo $resultQuery;

?>