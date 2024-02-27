<?php
namespace WCommon;

require_once('/mnt/data/bitrix/local/php_interface/classes/WCommon/LogPush.php');

class Crud {

    private $db;
    private $user;
    private $pass;
    private $mysqli;
    
    public function __construct() {

       /* $this->db = 'call_manager';
        $this->user = 'python';
        $this->pass = 'Deep1993';
        $this->port = '30305';
        $this->mysqli = new \mysqli("10.178.200.13", $this->user, $this->pass, $this->db, $this->port);*/
        $this->db = 'dbbb1';
        $this->user = 'dbbb1';
        $this->pass = 'hE0pVa4ec3ZNUaxaHJvR';

        $this->mysqli = new \mysqli("localhost", $this->user, $this->pass, $this->db);

        $this->LogPush = new \WCommon\LogPush();
        
        if ($this->mysqli->connect_error) {
            die("Ошибка подключения 2: " . $this->mysqli->connect_error);
        }
    }
    
    public function __destruct() {
        // Закрываем соединение при уничтожении объекта
        $this->mysqli->close();
    }

    public function Get($p) {
        $query = $p['request'];

        $result = $this->mysqli->query($query);

        if (!$result) {
            echo "Ошибка выполнения запроса: (" . $this->mysqli->errno . ") " . $this->mysqli->error;
            return false;
        }

        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $result->free();

        return $data;
    }

    public function UpdDatabase($p) {

        $mysqli = $this->mysqli;
        $table_name = $p['table_name'];
        $key_column = $p['key_column'];

        $values = [];
      
        foreach ($p['data'] as $item) {
            $escapedValues = array_map(function ($value) use ($mysqli) {
                return "'" . $mysqli->real_escape_string($value) . "'";
            }, $item);

            $values[] = '(' . implode(',', $escapedValues) . ')';
        }

        // Собираем запрос для обновления
        $update_query = "UPDATE $table_name SET " . implode(',', array_map(function ($field, $value) use ($mysqli) {
            return $field . "='" . $mysqli->real_escape_string($value) . "'";
        }, array_keys($p['data'][0]), $p['data'][0])) . " WHERE $key_column = ?";

        $stmt = $mysqli->prepare($update_query);

        if (!$stmt) {
            $er_r = "Ошибка подготовки запроса: (" . $mysqli->errno . ") " . $mysqli->error;
            $this->LogPush->Push($er_r);
            return false;
        }
        
        // Приведение значения ключа к строке для bind_param
        $key_value = $p['data'][0][$key_column];
        $stmt->bind_param('s', $key_value);

        $stmt->execute();

        return $stmt;
    }


    public function syncDataWithDatabase($p) {
      
        $mysqli = $this->mysqli;
        $table_name = $p['table_name'];
    
        $values = [];

        
    
        foreach ($p['data'] as $item) {
            $escapedValues = array_map(function ($value) use ($mysqli) {
               
                return "'" . $mysqli->real_escape_string($value) . "'";
            }, $item);
    
            $values[] = '(' . implode(',', $escapedValues) . ')';
        }
    
        $fields = array_keys($p['data'][0]);
        $query = "INSERT INTO $table_name (" . implode(',', $fields) . ") VALUES " . implode(',', $values);
        $stmt = $mysqli->prepare($query);
    
        if (!$stmt) {
            $er_r = "Ошибка подготовки запроса: (" . $mysqli->errno . ") " . $mysqli->error;
            $this->LogPush->Push($er_r);
            return false;
        }
    
        $stmt->execute();

        return $stmt;
    }

    public function GetHistoryItem($p) {

      try {
          $table_name = $p['table_name'];
          $WHERE_conditions = $p['where'];
          $ORDER_BY = $p['order'];

          $query = "SELECT * FROM $table_name";

          if (!empty($WHERE_conditions)) {
              $whereClause = $this->buildWhereClause($WHERE_conditions);
              $query .= " WHERE $whereClause";
          }

          if (!empty($ORDER_BY)) {
              $query .= " ORDER BY $ORDER_BY DESC";
          }

          $managerResult = $this->Get(['request' => $query]);

          if (!$managerResult) {
              return null;
          }

          return $managerResult;

      } catch (\Exception $e) {
          return null;
      }
          
    }

    private function buildWhereClause($conditions) {
      $whereParts = [];
  
      foreach ($conditions as $key => $value) {
          $whereParts[] = "$key = '$value'";
      }
  
      return implode(" AND ", $whereParts);
    }

    public function WGet($p) {
      $query = $p['request'];

      $result = $this->mysqli->query($query);

      if (!$result) {
          echo "Ошибка выполнения запроса: (" . $this->mysqli->errno . ") " . $this->mysqli->error;
          return false;
      }

      if($result == 1){
          return 1;
      }

      if(isset($p['one']) && !empty($p['one'])){
          while ($row = $result->fetch_assoc()) {
              $data = $row;
          }
      }
      else{
          $data = [];
          while ($row = $result->fetch_assoc()) {
              $data[] = $row;
          }
      }

      $result->free();

      return $data;
    }

    public function LetBotBase($pool, $subtext) {

      $rsUsers = \Bitrix\Main\UserTable::GetList([
          'select' => ['UF_TELEGRAM_ID'],
          'filter' => ['ID' => $pool['id_responsible']]
      ])->fetch();

      if(isset($rsUsers['UF_TELEGRAM_ID']) && !empty($rsUsers['UF_TELEGRAM_ID'])){
        return $rsUsers['UF_TELEGRAM_ID'];
      } else{
        return false;
      }

    }

    public function SendMess($data) {
      $botToken = '6489328913:AAFJ5biTuinVmedStG2DBjqmDYWlAQfMdoU';
      $apiUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";

      $ch = curl_init($apiUrl);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $response = curl_exec($ch);
      $response = json_decode($response ,true);

      if ($response === false) {
        $ery = 'Ошибка отправки запроса' . curl_error($ch);
        curl_close($ch);
        $this->LogPush->Push($ery);
        return false;
      } else{
        curl_close($ch);
        return $response;
      }
  }
}




