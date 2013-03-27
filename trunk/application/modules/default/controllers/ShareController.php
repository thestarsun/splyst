<?php

class Default_ShareController extends Zend_Controller_Action{

    protected $fb;
    protected $rM;
    protected $eM;
    protected $cache;
    protected $optionsFB;
    protected $qM;
    protected $base_path;


    public function init(){
        $this->fb = new App_Fb_Token();
        $this->rM = new Models_RecommendationManager();
        $this->eM = new Models_ExperienceManager();
        $this->qM = new Models_QuestManager();
        $this->cache = Zend_Registry::get('cache');
        $front = \Zend_Controller_Front::getInstance();
        $bootstrap = $front->getParam("bootstrap");
        $this->optionsFB = $bootstrap->getOption('fb');
        $this->base_path = $bootstrap->getOption('base_path');
    }

    public function getpopupAction(){
        $this->_helper->layout->disableLayout();
        $content_id = $this->getParam('id');
        if(!$this->cache->test('rec_sh_recid_'.$content_id.'_userid_'.$_SESSION['user_id'])){
            $recommendation = $this->rM->getRecommendationsByID($content_id);
            $this->cache->save($recommendation, 'rec_sh_recid_'.$content_id.'_userid_'.$_SESSION['user_id'], array(), 7200);
        }else{
            $recommendation = $this->cache->load('rec_sh_recid_'.$content_id.'_userid_'.$_SESSION['user_id']);
        }
        $this->view->thumbnail = $recommendation['thumbnail']; 
        $this->view->title = $recommendation['title'];
        $this->view->description = (!empty($recommendation['description']))?mb_substr(strip_tags($recommendation['description']), 0, 200, 'utf-8')."...":'';
        $friends = $this->fb->getFriendsForShare($_SESSION['user_id']);
        $fr_name = array();
        foreach ($friends as $friend)
            array_push ($fr_name, $friend['name']);
        $this->view->friends_name = strip_tags(json_encode($fr_name));
        $this->view->id = $content_id;
        
    }
    
    public function execshareAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $params = $this->_getAllParams();
        if(!$this->cache->test('rec_sh_recid_'.$this->getParam('rec_id').'_userid_'.$_SESSION['user_id'])){
            $recommendation = $this->rM->getRecommendationsByID($this->getParam('rec_id'));
            $this->cache->save($recommendation, 'rec_sh_recid_'.$this->getParam('rec_id').'_userid_'.$_SESSION['user_id'], array(), 7200);
        }else{
            $recommendation = $this->cache->load('rec_sh_recid_'.$this->getParam('rec_id').'_userid_'.$_SESSION['user_id']);
        }
        $com ='';
        if(!empty($params['comment'])){
            $comment = array('date'=>date('Y/m/d H:i:s'), 
                'name'=> $_SESSION['user_name'], 
                'data'=>$params['comment'], 
                'user_pic'=>$_SESSION['user_pic'],
                'fb_user_id' =>$_SESSION['fb_user_id']);
            $com = json_encode($comment);
        }
        $data = array(
            'url' => $recommendation['url'],
            'title' => $recommendation['title'],
            'thumbnail' => $recommendation['thumbnail'],
            'description' => $recommendation['description'],
            'old_rec' => $recommendation['type'], 
            'comments' =>$com
        );
        
        
        $friends = $this->fb->getFriendsForShare($_SESSION['user_id']);
        if(!$this->cache->test('js_ar_fr_'.$_SESSION['user_id'])){
            if(!empty($friends)){
                $js_ar_fr = [];
                foreach($friends as $fr){
                    $js_ar_fr[$fr['name']] = $fr['id']; 
                }
                $this->cache->save($js_ar_fr, 'js_ar_fr_'.$_SESSION['user_id'], array(), 7200);
            }
        }else
            $js_ar_fr = $this->cache->load('js_ar_fr_'.$_SESSION['user_id']);
        
        $lock = (empty($params['exp_lock']))? 0:1;
        $link_id = $this->eM->rec2link($data);
        $notificationData = array();
        $notificationData['thumbnail'] = $_SESSION['user_pic'];
        $notificationData['user_id'] = $_SESSION['user_id'];
        $notificationData['user_name'] = $_SESSION['user_name'];
        $notificationData['title'] = $recommendation['title'];
        $notificationData['url'] = $recommendation['url'];
        $notificationData['time'] = time();
        if(!empty($params["friends"])&& !empty($friends)){
            foreach ($friends as $friend){
                if(in_array($friend['name'], $params["friends"])){
                    if(!empty($friend['installed']) && $friend['installed'] == true){
                        $fr_arr = $this->qM->getUserById($friend['id']);
                        $return = $this->eM->createLinkOnRec($fr_arr['id_tbl_user'], $data, $lock, $link_id);
                        $exp_id = $this->eM->getExperienceByLink($fr_arr['id_tbl_user'], $link_id);
                        $notificationData['link_id'] = '/default/dashboard/experience?id='.$exp_id[0]['user_exp_id'].'&link='.$link_id;
                        $this->qM->updateUserNotification($fr_arr['id_tbl_user'], $return['notif_type'], $notificationData, true);
                        if(!empty($return['user_exp_id'])){
                            $this->cache->remove('exp_links_' . $return['user_exp_id']);
                            $this->cache->remove('exp_' . $fr_arr['id_tbl_user']);
                        }
                    }
                }
            }
        }
        switch ($recommendation['type']) {
            case 1:
                $type = 'news';
                break;
            case 2:
                $type = 'image';
                break;
            case 3:
                $type = 'video';
                break;
            default:
                break;
        }
        $this->_helper->json(array('link' =>'http://'.$this->base_path.'/default/fblinks/'.$type.'?id='.$link_id , 'js_ar_fr' => json_encode($js_ar_fr)));
    }

}