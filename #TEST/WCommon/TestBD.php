<?php


/*
$tui = new mysqli("10.178.2.96", 'root1', '2gqmxE83CmhWWFdnPUVF', 'bd1', 3306);

if ($tui->connect_error) {
    die("Ошибка подключения: " . $tui->connect_error);
}*/
/*
mysql -h 10.178.200.16 -u dbbb1 -p dbbb1
mysql -h localhost -u dbbb1 -p dbbb1

hE0pVa4ec3ZNUaxaHJvR*/

class Test {
    
    public function __construct() {
         
        
        $this->db = 'dbbb1';
        $this->user = 'dbbb1';
        $this->pass = 'hE0pVa4ec3ZNUaxaHJvR';

        $this->mysqli = new \mysqli("localhost", $this->user, $this->pass, $this->db);
        
        if ($this->mysqli->connect_error) {
            die("Ошибка подключения: " . $this->mysqli->connect_error);
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
        
        print_r(['$result' => $result]);

        return $data;
    }
}

$perem = "

SHOW * FROM history_deal_numer_test

";
 
$test = new Test();
$fi = $test->Get(['request' => $perem]);



/*


CREATE TABLE bb_log_doknumer_deff (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    ARTICLE VARCHAR(255),
    TIMEUPDATE VARCHAR(255),
    ID_ORDER VARCHAR(255),
    NUMER_DOGOVOR VARCHAR(255),
    DATE_CREATE VARCHAR(255),
    FIO VARCHAR(255),
    ADRESS VARCHAR(255),
    PHONE VARCHAR(255),
    TIMEST TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);




*/