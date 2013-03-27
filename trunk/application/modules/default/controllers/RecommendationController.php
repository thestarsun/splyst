<?php

class Default_RecommendationController extends Zend_Controller_Action{

    protected $eM;
    protected $rM;
    protected $fb;
    protected $google;
    protected $lM;
    protected $cache;
    protected $qM;
    protected $grab;


    public function init(){
        $this->eM = new Models_ExperienceManager();
        $this->qM = new Models_QuestManager();
        $this->lM = new Models_LinkManager();
        $this->rM = new Models_RecommendationManager();
        $this->fb = new App_Fb_Token();
        $this->google = new App_Google_SearchAPI();
        $this->alchemy = new App_Alchemy();
        $this->cache = Zend_Registry::get('cache');
        $this->grab = new App_Google_Bildcontent();
    }

    public function indexAction() {
//        $_SESSION['user_id'] = 86;
//        $_SESSION['token'] = 'AAAGXRNrZARo8BAHU1NAfcskvjMZALQLB8JbGohx2B9TZBPdehrBoUZBsdHXiL3xisT2LUgp66jhnliwOvxR9lx51IzJdy9Mp6i7zlWozjgZDZD';
//        $fl = fopen('cache_test',"a+");
        $this->_helper->layout->setLayout('revommendations');
        if (!empty($_SESSION['user_id'])){
            if(!$this->cache->test('exp_'.$_SESSION['user_id'])){
                $this->view->experience = $this->eM->getUserExperience($_SESSION['user_id']);
                $this->cache->save($this->view->experience, 'exp_' . $_SESSION['user_id'], array(), 172800);
            }else{
                $this->view->experience = $this->cache->load('exp_' . $_SESSION['user_id']);
            }
//            $this->cache->clean();
            if(!$this->cache->test('recommendation_ids_'.$_SESSION['user_id'])||$this->cache->test('recommendation_ids_clean_'.$_SESSION['user_id'])){
                $recID = $this->rM->getRecID($_SESSION['user_id']);
                if(!empty($recID)){
                    $recMaxID = $this->rM->getRecMAxIDforShuffle($_SESSION['user_id']);
                    if(empty($recMaxID)){
                        $recMaxID = $recID[0];
                        $this->rM->setRecMAxIDforShuffle($_SESSION['user_id'], $recMaxID);
                    }
                    $arr_for_shuffle = array();
                    $arr_not_shuffle = array();
                    foreach ($recID as $ids){
                        if($ids <= $recMaxID)
                            $arr_for_shuffle[] = $ids;
                        else
                            $arr_not_shuffle[] = $ids;
                    }
                    shuffle($arr_for_shuffle);
                    $recID = array_merge($arr_not_shuffle, $arr_for_shuffle);
                   
                }
//                fwrite($fl, '1 save '.implode(',', $recID));
                $this->cache->save($recID, 'recommendation_ids_' . $_SESSION['user_id'], array(), 7200);
                $this->cache->remove('recommendation_ids_clean_'.$_SESSION['user_id']);
            }else{
                $recID = $this->cache->load('recommendation_ids_' . $_SESSION['user_id']);
               // fwrite($fl, '2 load '.implode(',', $recID));
            }
            $this->cache->save(30, 'rec_count_'.$_SESSION['user_id'], array(), 7200);
            $ids10 = array_splice($recID, 0, 30);
//            fwrite($fl, '3 ids10 '.implode(',', $ids10));
            $recomm = array();
            if(!empty($ids10)) {
                $recomm = $this->rM->getRecommendationsByIDs(implode(', ', $ids10));
            }
            $this->view->recomendation_array = $recomm;
        }else{
            $this->_helper->redirector->gotoSimple('index','index','default');
        }
//        fclose($fl);
    }

    public function newrecommendationsAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $users = $this->qM->getActiveUsers();
        if(!empty($users)) {
            foreach ($users as $user) {
                $this->grab->newrecommendationbyfb($user['id_tbl_user']);
            }
        }
    }
    public function newrecfor1userAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $this->grab->newrecommendationbyfb(100);
    }

    public function recforsignupuserAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $params = $this->getAllParams();
        if(!empty($params['token'])&& $params['token'] == 'bnmsdferw678ghjhewr2347611' && !empty($params['id'])){
            $this->grab->newrecommendationbyfb((int)$params['id']);
            $friendList = $this->fb->getFriendsForShare($params['id']);
            $user_data = $this->qM->getUserBySplystId($params['id']);
            if(!empty($friendList))
            {
                foreach($friendList as $friend){
                    if(!empty($friend['installed']))
                   {
                        $fr_arr = $this->qM->getUserById($friend['id']);
                        $notificationData = array();
                        $notificationData['thumbnail'] = $user_data['pic'];
                        $notificationData['user_id'] = $params['id'];
                        $notificationData['user_name'] = $user_data['name'];
                        $notificationData['title'] = "You have new friend!!!";
                        $notificationData['url'] = "";
                        $notificationData['time'] = time();
                        $this->qM->updateUserNotification($fr_arr['id_tbl_user'],3,$notificationData,true);
                        $this->cache->remove('noncolaboration_'.$fr_arr['id_tbl_user']);
                        $this->cache->remove('noncolaboration_count_'.$fr_arr['id_tbl_user']);
                        $this->cache->remove('colaboration_'.$fr_arr['id_tbl_user']);
                        $this->cache->remove('colaboration_count_'.$fr_arr['id_tbl_user']);
                        $this->cache->remove('allcolaboration_count_'.$fr_arr['id_tbl_user']);
                    }
                }
            }
        }
    }
    
    public function updatecounterAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if(!empty($_SESSION['user_id'])) {
            $this->_helper->json(array('count'=>$this->lM->getCounter($_SESSION['user_id'])));
        }
    }
    
    public function refreshcounterAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if(!empty($_SESSION['user_id'])) {
            $this->lM->breakCounterRec($_SESSION['user_id']);
        }
    }
    
    public function updaterecomendationsAction(){
        $this->_helper->layout->disableLayout();
        if(!empty($_SESSION['user_id'])) {
            $last_id = $_COOKIE['lastRecID'];
            $this->_helper->layout->disableLayout();
            $recomm = $this->rM->getNewRec($_SESSION['user_id'], $last_id);
            if(!empty($recomm[0]['id'])) {
                setcookie("lastRecID", $recomm[0]['id']);
            }else
                $recomm = array();

            $this->view->recomendation_array = $recomm;
        }
    }
    
    public function checkrecnumberAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if(!empty($_SESSION['user_id'])) {
            $r = $this->rM->maxRecNumber($_SESSION['user_id']);
            setcookie("lastRecID", $r);
        }
        
    }
    
    }
