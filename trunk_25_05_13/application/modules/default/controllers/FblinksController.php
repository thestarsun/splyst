<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vstymkovskyi
 * Date: 21.02.13
 * Time: 11:55
 * To change this template use File | Settings | File Templates.
 */
class Default_FblinksController extends Zend_Controller_Action{

    private $lM;
    
    public function init(){
        $this->_helper->layout->setLayout('experiences');
        $this->lM = new Models_LinkManager();
    }

    public function imageAction(){
        $params = $this->_getAllParams();
        if (!empty($params['id'])){
            if(empty($_SESSION['user_id']))
                $this->_helper->layout->setLayout('guest');
//                $this->_helper->redirector->gotoUrl('/default/fblinks/error?adr=image&id='.$params['id']);
                $full_news = $this->lM->getLinkByID($params['id']);
                $this->view->url = $full_news['url'];
                $this->view->title = $full_news['title'];
        }
    }
    
    public function videoAction(){
        $params = $this->_getAllParams();
        if (!empty($params['id'])) {
            if (empty($_SESSION['user_id']))
                $this->_helper->layout->setLayout('guest');
//                $this->_helper->redirector->gotoUrl('/default/fblinks/error?adr=video&id='.$params['id']);
                $full_news = $this->lM->getLinkByID($params['id']);
                $this->view->url = $full_news['url'];
                $this->view->title = $full_news['title'];
        }
    }
    
    public function newsAction(){
        $this->_helper->layout->setLayout('news_page');
        $params = $this->_getAllParams();
        if (!empty($params['id'])) {
            if (empty($_SESSION['user_id']))
               $this->_helper->layout->setLayout('guest');
//                $this->_helper->redirector->gotoUrl('/default/fblinks/error?adr=news&id='.$params['id']);
            $full_news = $this->lM->getLinkByID($params['id']);
            if(!empty($full_news)){
                $this->view->link_id = $params['id'];
                $this->view->title = $full_news['title'];
                $this->view->image = false;
                $this->view->content = false;
                $this->view->url = $full_news['url'];
                if($full_news['thumbnail']) $this->view->image = $full_news['thumbnail'];

                $this->view->frame = false;
                $iframe = $this->check_iframe($full_news['url']);
                if($iframe){
                    $this->view->frame = true;
                    $this->view->frame_content = '<iframe id="news_iframe_page" src="'.$full_news['url'].'" class="news_iframe_page"></iframe>';
                }
                $temp_news_page = file_get_contents('http://boilerpipe-web.appspot.com/extract?url='.$full_news['url'].'&extractor=ArticleExtractor&output=json');
                $news_page = json_decode($temp_news_page, true);
                if($news_page['status'] == 'success'){
                    $this->view->content = $this->multiexplode($news_page['response']['content']);
                }
            }else
                $this->view->error = 1;
        }else
            $this->view->error = 1;
    }
    

    public function errorAction(){
        $params = $this->_getAllParams();
        setcookie('outsidecontentid', $params['id'], time()+360000, "/");
        setcookie('outsidecontentlink', $params['adr'], time()+360000, "/");
//        $this->_helper->redirector->gotoUrl('/');
    }

    private function check_iframe($url){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        $response = curl_exec($ch);
        curl_close($ch);
        if(!preg_match("/X-Frame-Options/i", $response))
            return true;
        else
            return false;
    }

    private function multiexplode ($string) {
        $string = str_replace('."', '".', $string);
        $launch = explode('.', $string);
        $full_text = '<p>';
        foreach($launch as $key=>&$item){
            if(!empty($item)){
                $item = $item.".";
                if($key % 7 == 0 && $key != 0){
                    $item = $item.'</p><p>';
                }
                $full_text .= $item;
            }
        }
        $full_text .= '</p>';

        return $full_text;
    }

}