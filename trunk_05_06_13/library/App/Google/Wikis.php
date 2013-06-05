<?php

class App_Google_Wikis extends App_Google_Content{
    
    public function __construct() {
        parent::__construct();
    }

    public function getContent($tag_id){
        return $this->lM->getWikis($tag_id);
    }     
    
    public function getMaxContent($tag_id){
        return $this->lM->getMaxWikis($tag_id);
    }     
    
   public function setContent($tag_id, $data){
       $data = json_decode($data, true);
       foreach($data as &$dt){
           $dt['tag_id'] = $tag_id;
       }
        $this->lM->setContent($tag_id,json_encode($data), 'wiki', '', 'wiki');
    }   
    
    public function setMaxContent($tag_id, $data){
        $data = json_decode($data, true);
        foreach($data as &$dt){
           $dt['tag_id'] = $tag_id;
        }
        $this->lM->setContent($tag_id, json_encode($data), 'wiki', '_max', 'wiki');
    } 
    
    public function parseData($tag_name, $amount){
        $wiki = new App_Wiki();
        return json_decode($wiki->wiki_search($tag_name, $amount));
    }
   
}