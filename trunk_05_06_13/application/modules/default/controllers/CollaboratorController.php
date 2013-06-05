<?php

class Default_CollaboratorController extends Zend_Controller_Action {

	protected $fb;
	protected $qM;
	protected $fM;
	protected $countC;
	protected $countNC;
        protected $cache;

        public function init() {
		$this->qM = new Models_QuestManager();
		$this->fb = new App_Fb_Token();
		$this->fM = new Models_FriendManager();
                $this->countC = 24;
                $this->countNC = 22;
                $this->countAC = 100;
                $this->cache = Zend_Registry::get('cache');
	}

	public function indexAction() {
            $this->_helper->layout->setLayout('collaborator');
            $params = $this->getAllParams();
//            $_SESSION['user_id'] = 86;
//            $_SESSION['user_id'] = 85;
//            $_SESSION['token'] = 'AAAGXRNrZARo8BAHU1NAfcskvjMZALQLB8JbGohx2B9TZBPdehrBoUZBsdHXiL3xisT2LUgp66jhnliwOvxR9lx51IzJdy9Mp6i7zlWozjgZDZD';
//            $_SESSION['token'] = 'AAAGXRNrZARo8BAMmb1IxZCFfxcuEjQKv6HZCH9kguddsMq3pc8FNWM7R5rlQb2q3ro0KV28LIWewrOJClbIrmeT7gRGCSE1DJa7aDTZB5gZDZD';
//            $this->cache->clean();
            if(!$this->cache->test('colaboration_' . $_SESSION['user_id'])||
               !$this->cache->test('noncolaboration_' . $_SESSION['user_id'])||
               !$this->cache->test('colaboration_count_' . $_SESSION['user_id'])||
               !$this->cache->test('search_colaboration_' . $_SESSION['user_id'])||
               !$this->cache->test('noncolaboration_count_' . $_SESSION['user_id'])){
                $this->createCache($_SESSION['user_id']);
            }
                $colaboratorsArray = $this->cache->load('colaboration_' . $_SESSION['user_id']);
                $nonColaboratorsArray = $this->cache->load('noncolaboration_' . $_SESSION['user_id']);
                $searchArray = $this->cache->load('search_colaboration_' . $_SESSION['user_id']);

            if(empty($params['all'])){
                $this->view->colaborators_real_count = count($colaboratorsArray);
                $this->view->otherFriends_real_count = count($nonColaboratorsArray);
                $this->view->colaborators = array_splice($colaboratorsArray, 0, $this->countC);
                $this->view->otherFriends = array_splice($nonColaboratorsArray, 0, $this->countNC);
                $this->view->allcolaborators = false;
            }else{
                $this->_helper->layout->disableLayout();
                $this->view->allcolaborators_count = $this->countAC;
                $this->cache->save($this->countAC, 'allcolaboration_count_' . $_SESSION['user_id'], array(), 7200);
                $this->view->allcolaborators = array_splice($nonColaboratorsArray, 0, $this->countAC);
            }
	}
        
        public function ajaxcoloboratorsAction(){
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $params = $this->getAllParams();
            $success = false;
            switch ($params['type']) {
                case 'all':
                    $nonColaboratorsArray = $this->cache->load('noncolaboration_' . $_SESSION['user_id']);
                    $allColaboratorsCount = $this->cache->load('allcolaboration_count_' . $_SESSION['user_id']);
                    $view = array_splice($nonColaboratorsArray, $allColaboratorsCount, $this->countAC);
                    $this->cache->save($this->countAC+$allColaboratorsCount, 'allcolaboration_count_' . $_SESSION['user_id'], array(), 7200);
                    break;
                case 'col':
                    $colaboratorsArray = $this->cache->load('colaboration_' . $_SESSION['user_id']);
                    $colaboratorsCount = $this->cache->load('colaboration_count_' . $_SESSION['user_id']);
                    $view = array_splice($colaboratorsArray, $colaboratorsCount, $this->countC);
                    $this->cache->save($this->countC+$colaboratorsCount, 'colaboration_count_' . $_SESSION['user_id'], array(), 7200);
                    break;
                case 'nocol':
                    $nonColaboratorsArray = $this->cache->load('noncolaboration_' . $_SESSION['user_id']);
                    $nonColaboratorsCount = $this->cache->load('noncolaboration_count_' . $_SESSION['user_id']);
                    $view = array_splice($nonColaboratorsArray, $nonColaboratorsCount, $this->countNC);
                    $this->cache->save($this->countNC+$nonColaboratorsCount, 'noncolaboration_count_' . $_SESSION['user_id'], array(), 7200);
                    break;
                case 'search':
                    $nonColaboratorsArray = $this->cache->load('noncolaboration_' . $_SESSION['user_id']);
                    $searchArray = $this->cache->load('search_colaboration_' . $_SESSION['user_id']);
                    if($params['search_val'] == 'all'){
                        if(!$this->cache->test('all_sr_count_'.$_SESSION['user_id'])){
                            $all_search_result = 0;
                            $this->cache->save($all_search_result, 'all_sr_count_'.$_SESSION['user_id'], array(), 7200);
                        }else{
                            $all_search_result =(!empty($params['scroll']))?$this->countAC+$this->cache->load('all_sr_count_'.$_SESSION['user_id']):0;
                            $this->cache->save($all_search_result, 'all_sr_count_'.$_SESSION['user_id'], array(), 7200);
                        }
                        $search_result = array_splice($nonColaboratorsArray, $all_search_result, $this->countAC);
                    }else{
                        $search_val = str_replace(" ", "_", $params['search_val']);
                        $search_count = 0;
                        $search_result = array();
                        if(preg_match('/^[a-zA-Z0-9]+$/', $search_val)){
                            if(!$this->cache->test('colaboration_search_'.$search_val.'_'.$_SESSION['user_id'])){
                                foreach($searchArray as $item){
                                    if (preg_match("/".$search_val."/i", $item['name'])) {
                                        $search_result[] = $item;
                                    }
                                }
                                $search_temp = $search_result;
                                $this->cache->save($search_temp, 'colaboration_search_'.$search_val.'_'.$_SESSION['user_id'], array(), 7200);
                                $this->cache->save($search_count, 'colaboration_search_count_'.$search_val.'_'.$_SESSION['user_id'], array(), 7200);
                            }else{
                                $search_temp = $this->cache->load('colaboration_search_'.$search_val.'_'.$_SESSION['user_id']);
                                if(!empty($params['scroll'])){
                                    $search_count = $this->countAC+$this->cache->load('colaboration_search_count_'.$search_val.'_'.$_SESSION['user_id']);
                                    $this->cache->save($search_count, 'colaboration_search_count_'.$search_val.'_'.$_SESSION['user_id'], array(), 7200);
                                }else{
                                    $this->cache->save($search_count, 'colaboration_search_count_'.$search_val.'_'.$_SESSION['user_id'], array(), 7200);
                                }
                            }
                            $search_result = array_splice($search_temp, $search_count, $this->countAC);
                        }
                    }
                    $view = $search_result;
                    break;
                default:
                    break;
            }
            if(!empty($view)){
                $success = true;
            }
            $this->_helper->json(array('data'=>json_encode($view), 'success' => $success));
        }
        
        public function createcacheAction(){
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender();
            $params = $this->getAllParams();
            if(!empty($params['token'])&& $params['token'] == 'bnmsdferw678ghjhewr2347611' &&!empty($params['id'])){
                $this->createCache($params['id']);
            }
        }
        
        public function sendinviteAction(){
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender();
            $fr_arr = $this->qM->getUserById($this->getParam('to'));
            $notificationData = array();
            $notificationData['thumbnail'] = $_SESSION['user_pic'];
            $notificationData['user_id'] = $_SESSION['user_id'];
            $notificationData['user_name'] =  $_SESSION['user_name'];
            $notificationData['title'] = $_SESSION['user_name']." want to be your friend!";
            //$notificationData['type'] = "invite";
            $notificationData['time'] = time();
            $notificationData['type'] = 4;
            $check_if_friend = $this->fM->getFriends($_SESSION['user_id']);
            if(empty($check_if_friend)){
                $this->qM->updateUserNotification($fr_arr['id_tbl_user'], 4, $notificationData, true);
            }
            //add notification to invited user
        }
        
        public function confirmfriendsAction(){
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender();
//            $this->fM->setFriend($_SESSION['user_id'], $this->getParam('fr_sp_id'));
            $temp_array = array();
            $params = $this->getAllParams();
            $data = explode(",", $params['data']);
            $this->fM->setFriend($_SESSION['user_id'], $data[3]);

            $all_data_notifications = $this->qM->getUserNotifications($_SESSION['user_id'], $data[0]);
            $notifications = json_decode($all_data_notifications['data'], true);
            foreach($notifications as $key=>$notif){
                if(!array_key_exists($notif['user_id'], $temp_array))
                    $temp_array[$notif['user_id']] = $notif;
                else
                    unset($notifications[$key]);
            }
            foreach($temp_array as $key=>$item){
                if($item['time'] == $data[1])
                    unset($temp_array[$key]);
            }
            $this->qM->updateTypeNotifications($_SESSION['user_id'], $data[0], $temp_array);
            $this->createCache($_SESSION['user_id']);
            $this->createCache($data[3]);
        }

        public function rejectfriendsAction(){
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender();
            $temp_array = array();
            $params = $this->getAllParams();
            $data = explode(",", $params['data']);

            $all_data_notifications = $this->qM->getUserNotifications($_SESSION['user_id'], $data[0]);
            $notifications = json_decode($all_data_notifications['data'], true);
            foreach($notifications as $key=>$notif){
                if(!array_key_exists($notif['user_id'], $temp_array))
                    $temp_array[$notif['user_id']] = $notif;
                else
                    unset($notifications[$key]);
            }
            foreach($temp_array as $key=>$item){
                if($item['time'] == $data[1])
                    unset($temp_array[$key]);
            }
            $this->qM->updateTypeNotifications($_SESSION['user_id'], $data[0], $temp_array);
            $this->createCache($_SESSION['user_id']);
            $this->createCache($data[3]);
        }

        private function createCache($user_id){
            $friends = $this->fb->_getCacheFriends($user_id);
            $colaboratorsArray = array();
            $nonColaboratorsArray = array();
            if (!empty($friends['data'])) {
                foreach ($friends['data'] as  &$friend){
                    $friend['picture'] = $friend['picture']['data']['url'];
                    if(!empty($friend['installed'])){
                        $colaboratorsArray[$friend['id']] = $friend;
                        $colaboratorsArray[$friend['id']]['type'] = 'fb';
                    }else{
                        $nonColaboratorsArray[$friend['id']]= $friend;
                        $nonColaboratorsArray[$friend['id']]['type'] = 'fb';
                    }
                }
            }
            $friends_DB = $this->fM->getFriends($_SESSION['user_id']);
            $friends_DB_moderate = [];
            if(!empty($friends_DB)){
                foreach($friends_DB as $fr_DB){
                    $friends_DB_moderate[$fr_DB['id']] =$fr_DB;
                }
            }
            $users = $this->qM->getAllUsers();
            $user_ids = array();
            foreach ($users as $user){
                $user_ids[$user['id']] = $user; 
                $user_ids[$user['id']]['type'] = 'sp'; 
            }
            unset($user_ids[$_SESSION['fb_user_id']]);
//            $searchArray = $user_ids + $nonColaboratorsArray;
            $colaboratorsArray += $friends_DB_moderate;
            $searchArray = array_diff_key($user_ids + $nonColaboratorsArray, $colaboratorsArray);
            $this->cache->save($searchArray, 'search_colaboration_' . $user_id, array(), 7200);
            $this->cache->save($colaboratorsArray, 'colaboration_' . $user_id, array(), 7200);
            $this->cache->save($nonColaboratorsArray, 'noncolaboration_' . $user_id, array(), 7200);
            $this->cache->save($this->countC, 'colaboration_count_' . $user_id, array(), 7200);
            $this->cache->save($this->countNC, 'noncolaboration_count_' . $user_id, array(), 7200);
        }
        
}
