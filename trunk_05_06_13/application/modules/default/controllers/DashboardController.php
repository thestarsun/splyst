<?php

class Default_DashboardController extends Zend_Controller_Action
{
    protected $eM;
    protected $fb;
    protected $qM;
    protected $google;
    protected $google_news;
    protected $lM;
    protected $cache;
    protected $content;


    public function init(){
        $this->eM = new Models_ExperienceManager();
        $this->lM = new Models_LinkManager();
        $this->fb = new App_Fb_Token();
        $this->qM = new Models_QuestManager();
        $this->google = new App_Google_SearchAPI();
        $this->google_news = new App_Google_News();
        $this->cache = Zend_Registry::get('cache');
        $this->content = new App_Google_Bildcontent();
    }

    public function indexAction(){
        $cache = Zend_Registry::get('cache');
        $this->view->temp_dir = '/static/'.$_SESSION['user_id'].'/';
//        $this->view->temp_dir = '/tmp/'.$_SESSION['user_id'].'/';
        if(!empty($_SESSION['user_id'])) {
            $defExp = $this->qM->getRegistrationEnd($_SESSION['user_id']);
            if(empty($defExp)){
//                $this->eM->createDefExp($_SESSION['user_id']);
                $experience = $this->eM->getDefaultExperience(true);
                $tmp_arr = array('("'.$_SESSION['user_id'].'", "64")');
                foreach($experience as $cat_id){
                    $tmp_arr[] = '("'.$_SESSION['user_id'].'", "'.$cat_id['exp_id'].'")';
                }
                $this->eM->saveUserDefaultExperience(implode(',', $tmp_arr));
                $cache->remove('exp_'.$_SESSION['user_id']);
            }
            if (!$cache->test('exp_'.$_SESSION['user_id'])){
                $this->view->experience = $this->eM->getUserExperience($_SESSION['user_id']);
                $cache->save($this->view->experience, 'exp_'.$_SESSION['user_id'], array(), 172800);
            } else {
                $this->view->experience = $cache->load('exp_'.$_SESSION['user_id']);
            }
        }else{
            $this->_helper->redirector->gotoUrl($this->basePath . '/default/index/invite');
        }
    }

    public function experienceAction() {
//        $_SESSION['user_id'] = 98;
        $this->view->temp_dir = '/static/'.$_SESSION['user_id'].'/';
//        $this->view->temp_dir = '/tmp/'.$_SESSION['user_id'].'/';
        $this->_helper->layout->setLayout('experiences');
//        $this->cache->clean();
        if(!empty($_SESSION['user_id'])) {
//            $this->cache->clean();
            $exp_id = $_GET['id'];
            $experience = $this->eM->getExperience($exp_id);
            if($experience["exp_id"]== 64)
                $this->view->blockAddLink = 1;
//            if($experience['user_id'] == $_SESSION['user_id']){
            $this->view->exp_title = $experience['exp_title'];
            $this->view->exp_id = $exp_id;
            if(!$this->cache->test('exp_links_'.$exp_id)){
                $links_arr = $this->eM->getLinks($exp_id);
                $this->cache->save($links_arr, 'exp_links_'.$exp_id, array(), 172800);
            }else{
                $links_arr =$this->cache->load('exp_links_'.$exp_id);
            }
            if(!empty($links_arr)){
//                if(count($links_arr) > 3){
                    $all_links_arr = array('bookmarks' => $links_arr);
                    $this->view->all_links_arr = $all_links_arr;                
//                    $this->renderScript('dashboard/experience_big.phtml');
//                }else{
//                    foreach($links_arr as &$link){
//                        $params = array('id'=>$link['id'], 'type'=>'news', 'amount'=> '2');
//                        $link['news'] = $this->content->bild($params);
//                    }
//                    $this->cache->save($links_arr, 'exp_links_'.$exp_id, array(), 172800);
//                    $this->view->links_arr = $links_arr;
//                }
            }
        }else{
            $this->_helper->redirector->gotoUrl($this->basePath . '/default/index/invite');
        }
    }
    
    public function ajaxcontentAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
//        $this->cache->clean();
        $params = $this->getAllParams();
        if(!empty($params['type'])){
            $type = $params['type'];
            if(!$this->cache->test('exp_links_'.$params['exp_id'])){
                $links_arr = $this->eM->getLinks($params['exp_id']);
                $this->cache->save($links_arr, 'exp_links_'.$params['exp_id'], array(), 172800);
            }else{
                $links_arr =$this->cache->load('exp_links_'.$params['exp_id']);
            }
            if(!empty($links_arr)){
                $link_arr_data = array();
                foreach($links_arr as &$link){
                    if(!$this->cache->test('exp_links_content'.$params['exp_id'].'_'.$link['id'].'_'.$type.'_'.$_SESSION['user_id'])){
                        $link_arr = $this->content->bild(array('id'=>$link['id'], 'type'=>$type, 'amount'=> '2'));
                        $this->cache->save($link_arr, 'exp_links_content'.$params['exp_id'].'_'.$link['id'].'_'.$type.'_'.$_SESSION['user_id'], array(), 172800);
                    }else{
                        $link_arr = $this->cache->load('exp_links_content'.$params['exp_id'].'_'.$link['id'].'_'.$type.'_'.$_SESSION['user_id']);
                    }
                    if(!empty($link_arr)){
                        if(!empty($link_arr_data)) $link_arr_data = array_merge($link_arr_data, $link_arr);
                        else $link_arr_data = $link_arr;
                    }
                }
            }
            $tmp = array();
            $res_arr = array();
            foreach ($link_arr_data as &$one_link){
                if((($type =="wikis" ||$type== "videos") && !in_array($one_link['title'], $tmp))||(($type =="images" ||$type== "news") && !in_array($one_link['url'], $tmp))){
                    if($type =="news"){
                        $one_link['title']= str_replace(array("'", '"', '&#39;'),"",$one_link['title']);
                        $one_link['url']= str_replace(array("'", '"', '&#39;'),"",$one_link['url']);
                        if(!empty($one_link['image']))
                            $one_link['image']= str_replace(array("'", '"', '&#39;'),"",$one_link['image']);
                    }
                    $res_arr[]=$one_link;
                    if($type == "images" || $type== "news")
                        array_push($tmp, $one_link['url']);
                    else
                        array_push($tmp, $one_link['title']);
                }
            }
            $this->_helper->json(array('type'=>$type, 'data'=>  json_encode($res_arr)));
        }
    }

    
}
