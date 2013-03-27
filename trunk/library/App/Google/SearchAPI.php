<?php

class App_Google_SearchAPI {
    
    private $options;
    
    public function __construct() {
        $front = \Zend_Controller_Front::getInstance();
        $bootstrap = $front->getParam("bootstrap");
        $this->options = $bootstrap->getOption('google'); 
    }
    
    public function getImages($text, $count){
       $data = array('key'=>$this->options['apiKey'],
//              'rsz'=>'large',
              'num'=>$count,
//              'hl'=>'en',
//              'prettyPrint'=>'false',
//              'source'=>'gcsc',
//              'gss'=>'.17',
              'imgSize'=>'large',
              'sig'=>'d369d8abd543e83f65f7875493a3cf13',
              'searchtype'=>'image',
              'cx'=>$this->options['cx1'].':'.$this->options['cx2'],
              'q'=>$text,
               'alt'=>'json',
//              'googlehost'=>'www.google.com',
//              'oq'=>$text,
            );
        $query = $this->options['path'].http_build_query($data);
        $request = file_get_contents($query);
        $images = json_decode($request);
        return $images->results;
    }
    
    public function getVideo($text, $count){
        $data= array(
            'key'=>$this->options['apiKey'],
            'q'=>$text,
            'max-results'=>$count,
            'v'=>'2',
            'orderby'=>'published',
            'format'=>'5',
            'alt'=>'json',
        );
        $query = $this->options['pathVideo'].http_build_query($data);
        $request = file_get_contents($query);
        $video = json_decode($request, true);
        return (!empty($video['feed']['entry']))?$video['feed']['entry']:false;
    }

    public function getNews($text, $count){
        $ip = (!empty($_SERVER['REMOTE_ADDR']))?$_SERVER['REMOTE_ADDR']:'127.0.0.17';
        $url = 'https://ajax.googleapis.com/ajax/services/search/news?v=1.0&q='.$text.'&userip='.$ip.'&rsz='.$count;
        $request = file_get_contents($url);

        $result = json_decode($request, true);

        return $result['responseData']['results'];
    }
}

?>
