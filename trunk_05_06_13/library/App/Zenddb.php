<?php

class App_Zenddb extends Zend_Controller_Plugin_Abstract {

    public function __construct() {
       
        $params_db = array(
//            'host' => '207.198.114.205',
//            'host' => '195.177.237.145',
            'host' => 'localhost',
            'username' => 'root',
            'password' => ',j,th',
            'dbname' => 'splyst',
            'charset' => 'UTF8');
//        $params_db = array('host' => 'localhost',
//            'username' => 'root',
//            'password' => '',
//            'dbname' => 'splyst',
//            'charset' => 'UTF8');

        $this->db = Zend_Db::factory('Pdo_Mysql', $params_db);
        $this->db->getConnection();
    }

}
