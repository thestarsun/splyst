<?php

class Default_IndexController extends Zend_Controller_Action
{
    protected $eM;
    protected $qM;
    protected $lM;
    protected $rM;
    protected $fb;
    protected $google;

    public function init()
    {
        $this->eM = new Models_ExperienceManager();
        $this->google = new App_Google_SearchAPI();
        $this->lM = new Models_LinkManager();
        $this->qM = new Models_QuestManager();
        $this->fb = new App_Fb_Token();
        $this->rM = new Models_RecommendationManager();
        $this->alchemy = new App_Alchemy();
        $this->cache = Zend_Registry::get('cache');
        /* Initialize action controller here */
    }

    public function indexAction(){
        if(empty($_SESSION['user_id']))
           $this->_helper->layout->setLayout('invite_page');
    }

    public function loginAction(){
        $this->_helper->layout->setLayout('landing_page');
    }

    public function inviteAction(){
        $this->_helper->layout->setLayout('invite_page');
    }

    public function aboutAction(){
        $this->_helper->layout->setLayout('guest');
    }

    public function privacyAction(){
        $this->_helper->layout->setLayout('guest');
    }

    public function termsAction(){
        $this->_helper->layout->setLayout('guest');
    }
        
    public function deleteuserAction(){
        if(!empty($_SESSION['user_id'])){
            $this->qM->deteleUser($_SESSION['user_id']);
            unset($_SESSION['user_id']);
            unset($_SESSION['user_name']);
            unset($_SESSION['user_pic']);
            unset($_SESSION['token']);
            unset($_SESSION['fb_user_id']);
            unset($_SESSION['email']);
            echo "You delete your user. You can sign-up again";
        }else{
            echo "Please login before delete your account";
        }
    }
    
    public function testloadAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if(!$this->cache->test('nick')){
                $a = $this->rM->getUserRecommendationsUnlimit(86);
                $this->cache->save($a, 'nick', array(), 172800);
        }else{
            $a = $this->cache->load('nick');
        }
//        $a = $this->rM->getUserRecommendationsUnlimit(86);
//        echo "<pre>";
//        var_dump($a);
//        echo "</pre>";
//        die('++');
    }
}
    

