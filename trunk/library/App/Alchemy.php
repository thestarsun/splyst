<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Alchemy
 *
 * @author thestarsun
 */
class App_Alchemy {
  
    public function __construct() {
        $front = \Zend_Controller_Front::getInstance();
        $bootstrap = $front->getParam("bootstrap");
        $this->options = $bootstrap->getOption('alchemy'); 
    }
    
    public function getConseptTag($url){
        $data = array(
            'url' => $url,
            'apikey' =>$this->options['key'],
            'maxRetrieve' =>1,
            'outputMode'=> 'json'
        );
        $query = $this->options['path'].'?'.http_build_query($data);
        $response = json_decode(file_get_contents($query), true);
        $tag = null;
        if(!empty($response['concepts'][0]['text']))
            $tag = $response['concepts'][0]['text'];
        return $tag;
    }
    
    public function getTagByText($text){
        $data = array(
            'text' => $text,
            'apikey' =>$this->options['key'],
            'maxRetrieve' =>1,
            'outputMode'=> 'json'
        );
        $query = $this->options['textPath'].'?'.http_build_query($data);
        $response = json_decode(file_get_contents($query), true);
        $tag = null;
        if(!empty($response['entities'][0]['text']))
            $tag = $response['entities'][0]['text'];
        return $tag;
    }
}

?>
