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
    
    public function init(){
        $this->eM = new Models_ExperienceManager();
        $this->lM = new Models_LinkManager();
        $this->fb = new App_Fb_Token();
        $this->qM = new Models_QuestManager();
        $this->google = new App_Google_SearchAPI();
        $this->google_news = new App_Google_News();
        $this->cache =
                 Zend_Registry::get('cache');
        /* Initialize action controller here */
    }

    public function indexAction(){
        $cache = Zend_Registry::get('cache');
        $this->view->temp_dir = '/static/'.$_SESSION['user_id'].'/';
//        $this->view->temp_dir = $bootstrap->getOption('temp_dir').'/'.$_SESSION['user_id'].'/';
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
            $this->_helper->redirector->gotoUrl($this->basePath . '/default/index/index');
        }
    }

    public function experienceAction() {
//        $_SESSION['user_id'] = 98;
        $this->view->temp_dir = '/static/'.$_SESSION['user_id'].'/';
//        $this->view->temp_dir = $bootstrap->getOption('temp_dir').$_SESSION['user_id'].'/';
        $this->_helper->layout->setLayout('experiences');
        if(!empty($_SESSION['user_id'])) {
            $exp_id = $_GET['id'];
            $experience = $this->eM->getExperience($exp_id);
            if($experience["exp_id"]== 64)
                $this->view->blockAddLink = 1;
//            if($experience['user_id'] == $_SESSION['user_id']){
                $this->view->exp_title = $experience['exp_title'];
                if(!$this->cache->test('exp_links_'.$exp_id)){
                    $links_arr = $this->eM->getLinks($exp_id);
                    $content = new App_Google_Bildcontent();
                    foreach($links_arr as &$link){
                        if(!empty($link['tags'])){
                            $params = array('id'=>$link['id'], 'type'=>'news', 'amount'=> '2');
                            $link['news'] = $content->bild($params);
                        }
                    }
                    $this->cache->save($links_arr, 'exp_links_'.$exp_id, array(), 172800);
                }else{
                    $links_arr =$this->cache->load('exp_links_'.$exp_id);
                }
                $this->view->links_arr = $links_arr;
//            }else{
//                $this->view->error = 1;
//            }
        }else{
            $this->_helper->redirector->gotoUrl($this->basePath . '/default/index/index');
        }
    }

    
}
