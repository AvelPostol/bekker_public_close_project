<?php


class Crud {

    private $db;
    private $user;
    private $pass;
    private $mysqli;
    
    public function __construct() {
        
        $this->db = 'dbbb1';
        $this->user = 'dbbb2';
        $this->pass = 'hE0pVa4ec3ZNUaxaHJvR';
        $this->mysqli = new \mysqli("localhost", $this->user, $this->pass, $this->db);
        
        if ($this->mysqli->connect_error) {
            error_log("Ошибка подключения: " . $this->mysqli->connect_error . ' '. date('Y-m-d H:i:s'));
        } else {
           print_r([
            'Успех'
           ]);
        }
    }
    
    public function __destruct() {
        // Закрываем соединение при уничтожении объекта
        $this->mysqli->close();
    }

  /*  public function Get($p) {
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
    }*/

    public function TestMes($id_tg) {
        $message_text = 'тестовое сообщение';
    
        $data = [
            'chat_id' => $id_tg,
            'text' => $message_text,
        ];
    
        $botToken = '6275540383:AAFGqM2s37wMwNsAoBCn0BF6h61k57Q_A6A';   // менеджеры
       // $botToken = '6275540383:AAFGqM2s37wMwNsAoBCn0BF6h61k57Q_A6A'; // дизайнеры
       // $botToken = '6667463040:AAHVa_ZkV32Ko7PSrI7qENYmAJqxxKpcxHE'; // лог разработчику
        $apiUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
    
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $response = curl_exec($ch);

        print_r([
            '$response' => $response
        ]);
    
        curl_close($ch);
      }
}


$Crud = new Crud();
$id_tg = '1678838404'; // разработчик 
$id_tg = '1865551642';

$Crud->TestMes($id_tg);