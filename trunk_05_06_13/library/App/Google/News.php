<?php

class App_Google_News extends App_Google_Content{
    
    public function __construct() {
        parent::__construct();
    }

    public function getContent($tag_id){
        return $this->lM->getNews($tag_id);
    }     
    
    public function getMaxContent($tag_id){
        return $this->lM->getMaxNews($tag_id);
    }     
    
    public function setContent($tag_id, $data){
        $this->lM->setContent($tag_id, $data, 'news', '', 'news');
    }   
    
    public function setMaxContent($tag_id, $data){
        $this->lM->setContent($tag_id, $data, 'news', '_max', 'news');
    } 
    
    public function parseData($tag_name, $amount){
        $amount = 4;
        $news_temp = $this->google->getNews(urlencode($tag_name), $amount);
        if(!empty($news_temp)){
            $news = array();
            foreach($news_temp as $key=>$one_news){
                $news[$key]['title'] = strip_tags($one_news['title']);
                $news[$key]['publish_date'] = $one_news['publishedDate'];
                if(isset($one_news['image']['url']))
                    $news[$key]['image'] = $one_news['image']['url'];
                $news[$key]['url'] = $one_news['unescapedUrl'];
                $news[$key]['content'] = strip_tags($one_news['content']);
            }
            return $news;
        } else {
            return false;
        }
    }
   
}