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
            'maxRetrieve' =>3,
            'outputMode'=> 'json'
        );
        $query = $this->options['path'].'?'.http_build_query($data);
        $response = json_decode(file_get_contents($query), true);      
        $tag = array();
        foreach ($response['concepts'] as $key => $value) {
            if(!empty($value['text']))
                $tag[$key] =  $value['text'];
        }        
        return $tag;
    }
    
    public function getTagType($url){
        $data = array(
            'url' => $url,
            'apikey' =>$this->options['key'],
            'maxRetrieve' =>10,
            'outputMode'=> 'json',
            'linkedData' => 1,
            'showSourceText' => 1,
            'sourceText' => 'raw'
        );
        $query = $this->options['path_forTypes'].'?'.http_build_query($data);
        $response = json_decode(file_get_contents($query), true);
        $tag = array();
        foreach ($response['entities'] as $key => $value) {
            if(!empty($value['text']))
                $tag[$key] = array('text' => $value['text'], 'type' => $value['type']);
        }

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
