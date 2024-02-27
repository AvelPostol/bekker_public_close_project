<?php
namespace BazisCM;
error_reporting(E_ERROR);
ini_set('display_errors', 1);

  /**
  * Контроллер связи базиса и битрикс
  *
  *  1) отработка события перехода на стадию сделки в CRM
  *  -----------------------------------------------------
  *  2) поиск, старшего по номеру, договора в базисе
  *  3) увеличиваем значение договора на 1
  *  4) ищем в битрикс24 этот номер договора, если находим, то меняем на + 1 и проверяем еще раз
  *  5) после выполнения условий записываем сгенерированый номер в карточку сделки
  */

  /**
  * НОМЕР ДОГОВОРА
  * ----------------
  * ID ПОЛЬЗОВАТЕЛЯ + ID САЛОНА + СТАРШЕЕ ЧИСЛО
  */

class Main {

  public function __construct() {
    //$this->BazisController = new Workspace\Bazis\Controller;
    $this->BitrixController = new Workspace\Bitrix\Controller;
    //$this->Base = new \BazisCM\Workspace\Tools\Base();
    $this->Crud = new \BazisCM\Workspace\Bitrix\Crud();
  }

  public function Conroller($p){
 
  if(isset($p['showtest']) && !empty($p['showtest'])){

    $NewNumberContract = $this->BitrixController->Check(['UF_BASIS_SALON' => $p['manager']['UF_BASIS_SALON'], 'sh' => 't']);

    $p['test']['numer'] = str_replace($p['test']['groupy'], "", $NewNumberContract);
    $data[0] = $p['test'];
    print_r([
      'manager' => $p['manager'],
      'NewNumberContract' => $NewNumberContract,
      'deal' => $p['deal'],
      'oldNume' => $p['oldNume']
    ]);

    //$this->Crud->syncDataWithDatabase(['data' => $data, 'table_name' => 'history_deal_numer_test']);
    //$this->BitrixController->AddContractNumber(['manager' => $p['manager'], 'NewNumberContract' => $NewNumberContract, 'deal' => $p['deal'], 'oldNume' => $p['oldNume']]);

    die();
  }
  

  
  if(isset($p['stop']) && !empty($p['stop'])){

    $NewNumberContract = $this->BitrixController->Check(['UF_BASIS_SALON' => $p['manager']['UF_BASIS_SALON']]);

    print_r(['_1']);

    $p['test']['numer'] = str_replace($p['test']['groupy'], "", $NewNumberContract);
    print_r(['_2']);
    $data[0] = $p['test'];

    print_r([
      '$data' => $data
    ]);
    $this->Crud->syncDataWithDatabase(['data' => $data, 'table_name' => 'history_deal_numer_test']);
    print_r(['_3']);
    $this->BitrixController->AddContractNumber(['manager' => $p['manager'], 'NewNumberContract' => $NewNumberContract, 'deal' => $p['deal'], 'oldNume' => $p['oldNume']]);
    print_r(['_4']);
    print_r(['$BazisMaxNumer' => $BazisMaxNumer, '$data' => $data]);
    
  }
  
/*
  if(!empty($BazisMaxNumer['maxNumber']) && isset($BazisMaxNumer['maxNumber'])){

    $NewNumberContract = $this->BitrixController->Check(['ManagerUserField' => $p['manager']['UF_BASIS_SALON'], 'maxNumber' => $BazisMaxNumer['maxNumber']]);

    $p['test']['numer'] = str_replace($p['test']['groupy'], "", $NewNumberContract);
    $data[0] = $p['test'];
    
    $this->Crud->syncDataWithDatabase(['data' => $data, 'table_name' => 'history_deal_numer']);
    $this->BitrixController->AddContractNumber(['manager' => $p['manager'], 'NewNumberContract' => $NewNumberContract, 'deal' => $p['deal']]);

  } else{
    $NewNumberContract = $this->BitrixController->Check(['ManagerUserField' => $p['manager']['UF_BASIS_SALON'], 'maxNumber' => 100]);

    $p['test']['numer'] = str_replace($p['test']['groupy'], "", $NewNumberContract);
    $data[0] = $p['test'];

    $this->Crud->syncDataWithDatabase(['data' => $data, 'table_name' => 'history_deal_numer']);
    $this->BitrixController->AddContractNumber(['manager' => $p['manager'], 'NewNumberContract' => $NewNumberContract, 'deal' => $p['deal']]);
  }
  */
  }
  
}
