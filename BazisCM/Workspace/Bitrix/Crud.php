<?php
namespace BazisCM\Workspace\Bitrix;

class Crud {

    private $db;
    private $user;
    private $pass;
    private $mysqli;
    
    public function __construct() {

        /*$this->db = 'call_manager';
        $this->user = 'python';
        $this->pass = 'Deep1993';
        $this->port = '30305';
        $this->mysqli = new \mysqli("10.178.200.13", $this->user, $this->pass, $this->db, $this->port);*/
        
        $this->db = 'dbbb1';
        $this->user = 'dbbb2';
        $this->pass = 'hE0pVa4ec3ZNUaxaHJvR';
        $this->mysqli = new \mysqli("localhost", $this->user, $this->pass, $this->db);
        
        if ($this->mysqli->connect_error) {
            error_log("Ошибка подключения: " . $this->mysqli->connect_error . ' '. date('Y-m-d H:i:s'));
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
    public function writeLogSimple($data) {
        $logFile = '/mnt/data/bitrix/local/php_interface/classes/WCommon/Log/log_Simple_o'.time().'.txt';
        $formattedData = var_export($data, true);
        file_put_contents($logFile, '<?php $array = ' . $formattedData . ';', FILE_APPEND);
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

        $this->writeLogSimple([$query]);

        if (!$stmt) {
            $error = "Ошибка подготовки запроса: (" . $mysqli->errno . ") " . $mysqli->error;
            $this->writeLogSimple([$error]);
            echo "Ошибка подготовки запроса: (" . $mysqli->errno . ") " . $mysqli->error;
            exit();
        }
    
        $stmt->execute();
    }

    public function GetHistoryDeal($p) {

        try{
  
          $prefix = $p['id_crm_deal'];
          $prefix_resp = $p['id_responsible'];

          $managerQuery = "SELECT * FROM cm_dis_mess_history WHERE id_crm_deal='$prefix' AND id_responsible='$prefix_resp' ORDER BY created_at DESC";
          $managerResult = $this->Get(['request' => $managerQuery]);
    
          if (!$managerResult) {
              return null;
          }
    
          return $managerResult;
    
        } catch (\Exception $e) { 
            return null; 
        }
          
    }
}