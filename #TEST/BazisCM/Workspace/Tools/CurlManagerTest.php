<?php
namespace BazisCM\Workspace\Tools;

class CurlManagerTest {
  private $token;
  private $authHeader;
  private $baseUrl;
  private $requestUrl;
  private $top;
  private $ch;

  public function __construct($apiKey, $metod) {
    $this->apiKey = $apiKey;
    $this->authHeader = 'apiKey: ' . $this->apiKey;
    $this->baseUrl = 'https://cloud.bazissoft.ru';
    $this->requestUrl = $this->baseUrl . $metod;
    $this->top = 50;
    $this->ch = curl_init();
  } 

  /*
  T70135 ***
  P93110 ***


  125235 R37176
  47124 R38240
  17598  ---
  164187 R38242
  41097  R51101
  192332 R25378
  165939 ---
  45949  R37182
  52151  R51101
  191875 R25375
  44562  R38249
  144738 R37176
  191701 R37192
  56894  R49122
  17906  N99111
  118825 N99118
  134418 L14518
  112279 L14511
  118835 K37493
  195373 T13118




170151  L36173
51096   P88216
151089  P88216
170994  P88223 -
125518  R37172 //
170091  G07168
36767   K43305
50622   K27427
90172   K73275
88778   K43309
101757  K37486 //
94240   K37489
115591  K27907
124994  K43315
127003  ---
167552  K37501 ---
165604  K43323
47824   K02195
125458  K02196 /
173199  KA0013 --
191814  K05185
191835  K02204 /
191789  K32506
247309  K45349
198678  K37520
184365  K37520
45544   T13105
197761  T70142
80171   W03146



134418  L14518
112279  L14511
118835  K37493
195373  T13118
179364  L46105
50286   K27931
50225   T70135
95056   W01805
225900  E69329
94812   E69333
93320   E69334
191390  E69332
268464  E69331
90149   L14487
56818   R37171
43693   K37487
51131   K37490
70030   K97242
170140  K02194
192123  K43262
1085    T90223

  */

  public function CreateQueryOrders($p,$ProcesParam) {

    if($p['CheckContentName'] == 'orders'){
      $queryParams = http_build_query([
        'pageIndex' => $ProcesParam['pageIndex'],
        'pageSize' => $ProcesParam['pageSize'],
        'from' => $p['from'],
        'to' => $p['to'],
        //'filterByNumber' => 'KD0015'
      ]);
    }

    if($p['CheckContentName'] == 'order'){
      $queryParams = http_build_query([]);
    }

    if($p['CheckContentName'] == 'file'){
      $queryParams = http_build_query([
        'filename' => $p['filename']
      ]);
    }
    
    return $queryParams;
  }

  public function Get($p) {
    
    $continue = true;
    $accumulatedMessages = array();

    $ProcesParam['pageIndex'] = 0;
    $ProcesParam['pageSize'] = 50;

    if($p['CheckContentName'] == 'file'){
      if (strpos($this->requestUrl, '/file') !== false) {
        
      }
      else{
        $this->requestUrl = $this->requestUrl . '/file';
      }
    }

    while ($continue) {

      // Создание cURL-запроса
      curl_setopt($this->ch, CURLOPT_URL, $this->requestUrl . '?' . $this->CreateQueryOrders($p, $ProcesParam));
      curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($this->ch, CURLOPT_HTTPHEADER, [
          $this->authHeader
      ]);

      $jsonResponse = curl_exec($this->ch);
      $response = json_decode($jsonResponse, true);
     // print_r(['$response' => $response]);

    //  die();
    
      if ($jsonResponse === false) {
        echo 'cURL Error: ' . curl_error($this->ch);
        $continue = false; // Останавливаем цикл в случае ошибки
      } else {

      if($p['ContentPars']){
        if (isset($response[$p['CheckContentName']]) && !empty($response[$p['CheckContentName']])) {
          foreach ($response[$p['CheckContentName']] as $message) {
            if(isset($message)){
              $accumulated[] = $message;
            }
          }
        }
        else{
          $continue = false;
        }
      }
      else{
        if($p['CheckContentName'] == 'file'){
          $accumulated = $jsonResponse;
          $continue = false;
        }
        else{
          $accumulated = $response;
          $continue = false;
        }  
      }
    
      $ProcesParam['pageIndex']++;

      }
    }

    curl_close($this->ch);
    return ['data' => $accumulated];
  }
}
