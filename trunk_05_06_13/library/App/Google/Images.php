<?php

class App_Google_Images extends App_Google_Content{
    
    public function __construct() {
        parent::__construct();
    }

    public function getContent($tag_id){
        return $this->lM->getImages($tag_id);
    }     
    
    public function getMaxContent($tag_id){
        return $this->lM->getMaxImages($tag_id);
    }     
    
    public function setContent($tag_id, $data){
        $this->lM->setContent($tag_id, $data, 'img', '', 'images');
    }   
    
    public function setMaxContent($tag_id, $data){
        $this->lM->setContent($tag_id, $data, 'img', '_max', 'images');
    } 
    
    public function parseData($tag_name, $amount) {
        $images = $this->google->getImages($tag_name, $amount);
        if(!empty($images)){
            $img_to_write = array();
            foreach ($images as $key => $image){
                $img_to_write[$key]['content'] = strip_tags($image->content);
                $img_to_write[$key]['url'] = $image->url;
                $img_to_write[$key]['tbUrl'] = $image->tbUrl;
            }
            return $img_to_write;
        } else {
            return false;
        }
    }
}