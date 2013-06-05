<?php

class App_Google_Videos extends App_Google_Content{
    
    public function __construct() {
        parent::__construct();
    }

    public function getContent($tag_id){
        return $this->lM->getVideos($tag_id);
    }     
    
    public function getMaxContent($tag_id){
        return $this->lM->getMaxVideos($tag_id);
    }     
    
    public function setContent($tag_id, $data){
        $this->lM->setContent($tag_id, $data, 'video', '', 'videos');
    }   
    
    public function setMaxContent($tag_id, $data){
        $this->lM->setContent($tag_id, $data, 'video', '_max', 'videos');
    }
    
    public function parseData($tag_name, $amount) {
        $amount = 2;
        $videos = $this->google->getVideo($tag_name, $amount);
        if(!empty($videos)){
            $data = array();
            foreach ($videos as $key => $video){
                $data[$key]['description']=$video["media\$group"]["media\$description"]["\$t"];
                $data[$key]['thumbnail']=$video["media\$group"]["media\$thumbnail"][2]["url"];
                $data[$key]['title']=$video["media\$group"]["media\$title"]["\$t"];
                $data[$key]['id']=$video["media\$group"]["yt\$videoid"]["\$t"];
            }
            return $data;
        } else {
            return false;
        }
    }
}