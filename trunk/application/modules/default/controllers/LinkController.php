<?php

class Default_LinkController extends Zend_Controller_Action
{
    protected $eM;
    protected $fb;
    protected $google;
    protected $google_news;
    protected $lM;
    protected $wiki;
    protected $rM;
    
    public function init(){
        $this->eM = new Models_ExperienceManager();
        $this->lM = new Models_LinkManager();
        $this->fb = new App_Fb_Token();
        $this->google = new App_Google_SearchAPI();
        $this->google_news = new App_Google_News();
		$this->wiki = new App_Wiki();
        $this->rM = new Models_RecommendationManager();
        /* Initialize action controller here */
    }
    public function indexAction(){

    }
    public function contentAction(){
        $this->_helper->layout()->disableLayout();
        $cache = Zend_Registry::get('cache');
//        $cache->clean();
        $params = $this->_getAllParams();
        if(!$cache->test('getLinkTag_'.$params['id'])){
            $tag_id = $this->lM->getLinkTag($params['id']);
            $cache->save($tag_id, 'getLinkTag_'.$params['id'], array(), 172800);
        }else{
            $tag_id = $cache->load('getLinkTag_'.$params['id']);
        }
        $content = new App_Google_Bildcontent();
        $this->view->link_data = $content->bild($params);
        $this->view->amount = $params['amount'];
        $this->view->id = $params['id'];
        $this->renderScript('/link/'.$params['type'].'.phtml');
    }
     
    public function wikipageAction() {
            $this->_helper->layout->setLayout('experiences');
            $cache = Zend_Registry::get('cache');
            $params = $this->_getAllParams();
            $this->view->headLink()->setStylesheet('/css/wiki.css');
            if (!$cache->test('getLinkTag_' . $params['id'])) {
                    $tag_id = $this->lM->getLinkTag($params['id']);
                    $cache->save($tag_id, 'getLinkTag_' . $params['id'], array(), 172800);
            } else {
                    $tag_id = $cache->load('getLinkTag_' . $params['id']);
            }
            if (!$cache->test('link_wiki_'.$params['amount'] . '_' . $tag_id)) {
                    if($params['amount'] ==2)
                        $all_wiki_temp = $this->lM->getWikis($tag_id);
                    else
                        $all_wiki_temp = $this->lM->getMaxWikis($tag_id);
                    $all_wiki = json_decode($all_wiki_temp['data'], true);
            } else {
                    $all_wiki_temp = $cache->load('link_wiki_' . $params['amount'] . '_' . $tag_id);
                    $all_wiki = json_decode($all_wiki_temp, true);
            }
            $wiki = $all_wiki[$params['wiki_key']];
            $wiki_title = $wiki['title'];
            $wiki_content = $wiki['text'];

            $this->view->exp_id = $this->lM->getExperience($params['id']);
            $this->view->title = $wiki_title;
            $this->view->content = $wiki_content;
    }

    public function newspageAction() {
        $this->_helper->layout->setLayout('news_page');
        $params = $this->_getAllParams();
        if (!empty($params['id'])){
            $full_news = $this->rM->getRecommendationsByIDs($params['id']);
            if(!empty($full_news)){
                $full_news = $full_news[0];
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
    public function newspagefromlinkAction() {
        $this->_helper->layout->setLayout('news_page');
        $params = $this->_getAllParams();
        $this->view->title = $params['title'];
        $this->view->content = false;
        $this->view->url = $params['url'];
        $this->view->image  = (!empty($params['thumbnail']))? $params['thumbnail']:false;
        $this->view->frame = false;
        $iframe = $this->check_iframe($params['url']);
        if($iframe){
            $this->view->frame = true;
            $this->view->frame_content = '<iframe id="news_iframe_page" src="'.$params['url'].'" class="news_iframe_page"></iframe>';
        }
        $temp_news_page = file_get_contents('http://boilerpipe-web.appspot.com/extract?url='.$params['url'].'&extractor=ArticleExtractor&output=json');
        $news_page = json_decode($temp_news_page, true);
        if($news_page['status'] == 'success'){
            $this->view->content = $this->multiexplode($news_page['response']['content']);
        }
        $this->renderScript('link/news-page.phtml');
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