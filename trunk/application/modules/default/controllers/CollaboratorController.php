<?php

class Default_CollaboratorController extends Zend_Controller_Action {

	protected $fb;
	protected $qM;
	protected $countC;
	protected $countNC;
        protected $cache;

        public function init() {
		$this->qM = new Models_QuestManager();
		$this->fb = new App_Fb_Token();
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
               !$this->cache->test('noncolaboration_count_' . $_SESSION['user_id'])){
                $friends = $this->fb->_getCacheFriends($_SESSION['user_id']);
                $colaboratorsArray = array();
                $nonColaboratorsArray = array();
                if (!empty($friends['data'])) {
                    foreach ($friends['data'] as $friend){
                        if(!empty($friend['installed']))
                            $colaboratorsArray[] = $friend;
                        else
                            $nonColaboratorsArray[] = $friend;
                    }
                }
                $this->cache->save($colaboratorsArray, 'colaboration_' . $_SESSION['user_id'], array(), 7200);
                $this->cache->save($nonColaboratorsArray, 'noncolaboration_' . $_SESSION['user_id'], array(), 7200);
                $this->cache->save($this->countC, 'colaboration_count_' . $_SESSION['user_id'], array(), 7200);
                $this->cache->save($this->countNC, 'noncolaboration_count_' . $_SESSION['user_id'], array(), 7200);
            }else{
                $colaboratorsArray = $this->cache->load('colaboration_' . $_SESSION['user_id']);
                $nonColaboratorsArray = $this->cache->load('noncolaboration_' . $_SESSION['user_id']);
            }

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
                    if($params['search_val'] == 'all'){
                        if(!$this->cache->test('all_sr_count_'.$_SESSION['user_id'])){
                            $all_search_result = 0;
                            $this->cache->save($all_search_result, 'all_sr_count_'.$_SESSION['user_id'], array(), 7200);
                        }else{
                            $all_search_result = $this->cache->load('all_sr_count_'.$_SESSION['user_id']);
                            if(!empty($params['scroll']))
                                $all_search_result = $this->countAC+$all_search_result;
                            else $all_search_result = 0;

                            $this->cache->save($all_search_result, 'all_sr_count_'.$_SESSION['user_id'], array(), 7200);
                        }
                        $search_result = array_splice($nonColaboratorsArray, $all_search_result, $this->countAC);
                    }else{
                        $search_val = str_replace(" ", "_", $params['search_val']);
                        $search_count = 0;
                        $search_result = array();
                        if(preg_match('/^[a-zA-Z0-9]+$/', $search_val)){
                            if(!$this->cache->test('colaboration_search_'.$search_val.'_'.$_SESSION['user_id'])){
                                foreach($nonColaboratorsArray as $item){
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
                                    $search_count = $this->cache->load('colaboration_search_count_'.$search_val.'_'.$_SESSION['user_id']);
                                    $search_count = $this->countAC+$search_count;
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
                $friends = $this->fb->_getCacheFriends($params['id']);
                    $colaboratorsArray = array();
                    $nonColaboratorsArray = array();
                    if (!empty($friends['data'])) {
                        foreach ($friends['data'] as $friend){
                            if(!empty($friend['installed']))
                                $colaboratorsArray[] = $friend;
                            else
                                $nonColaboratorsArray[] = $friend;
                        }
                    }
                    $this->cache->save($colaboratorsArray, 'colaboration_' . $params['id'], array(), 7200);
                    $this->cache->save($nonColaboratorsArray, 'noncolaboration_' . $params['id'], array(), 7200);
                    $this->cache->save($this->countC, 'colaboration_count_' . $params['id'], array(), 7200);
                    $this->cache->save($this->countNC, 'noncolaboration_count_' . $params['id'], array(), 7200);
            }
        }
        
}
