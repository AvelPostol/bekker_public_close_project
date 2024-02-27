<?php
require 'vendor/autoload.php';
$googleAccountKeyFilePath = '/mnt/data/bitrix/local/php_interface/classes/MakeHistoryNumeDok/bekkernumer-6d54e3d07b1d.json';
$io = putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $googleAccountKeyFilePath);

$originalArray = $_POST;
/*
$values[] = [
    'ID', 'номер базиса дизайнера', 'Дата обновления','Номер заказа','Номер договора','Дата создания','ФИО','Адресс','Номер телефона','Время записи'
];*/

    foreach($originalArray as $item){
        $item['PHONE'] = "'".$item['PHONE'];
        $values[] = array_values($item);
    }


  $client = new Google_Client();
  $client->useApplicationDefaultCredentials();
  $client->addScope('https://www.googleapis.com/auth/spreadsheets');
  $client->setApprovalPrompt('force');
  $service = new Google_Service_Sheets($client);
  $spreadsheetId = '1ypBvWR_ziOaM5LEBCMyTj7_9hkX0Z2I6sPaUOFd4XzM';
  $response = $service->spreadsheets->get($spreadsheetId);
  
  $requests = [
   new Google_Service_Sheets_Request( [
     'deleteRange' => [
       'range'          => [
         'sheetId' => '0',
         'startRowIndex' => 1, // начиная со второй строки
       ],
       'shiftDimension' => 'ROWS'
     ]
   ] )
  ];
  
  $batchUpdateRequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest( [
   'requests' => $requests
  ] );
  $service->spreadsheets->batchUpdate( $spreadsheetId, $batchUpdateRequest );

  $ValueRange = new Google_Service_Sheets_ValueRange();
  $ValueRange->setValues($values);
  $options = ['valueInputOption' => 'USER_ENTERED'];
  $sd = $service->spreadsheets_values->update($spreadsheetId, 'nubmer!A2', $ValueRange, $options);
  
    print_r(['$sd']);

// AIzaSyBYrNyUN5QYHupA87qAx0lHs-3Sm6AmJq0