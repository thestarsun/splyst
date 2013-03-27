<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Content
 *
 * @author thestarsun
 */
abstract class App_Google_Content {
    
    public $lM;
    public $google;
    
    public function __construct() {
        $this->lM = new Models_LinkManager();
        $this->google = new App_Google_SearchAPI();
    }

    abstract function getContent($tag_id);     
    
    abstract function getMaxContent($tag_id);     
    
    abstract function setContent($tag_id, $data);     
    
    abstract function setMaxContent($tag_id, $data); 
    
    abstract function parseData($tag_name, $amount);
}

?>
