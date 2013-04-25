<?php
class App_Fb_Token{
    
    private $options;
    private $qM;
    private $cache;


    public function __construct() {
        $front = \Zend_Controller_Front::getInstance();
        $bootstrap = $front->getParam("bootstrap");
        $this->options = $bootstrap->getOption('fb');
        $this->qM = new Models_QuestManager();
        $this->cache = Zend_Registry::get('cache');
    }
    
    public function getAppToken(){
        $data = array('grant_type'=>'fb_exchange_token',
              'client_id'=>$this->options['appID'],
              'client_secret'=>$this->options['appSecret'],
              'grant_type'=>'client_credentials');
        $query = $this->options['basePath'].http_build_query($data);
        $request = file_get_contents($query);
        parse_str($request, $token);
        return $token['access_token'];
    }
    
    public function checkToken($appToken, $tokenForCheck){
        $data = array('grant_type'=>'fb_exchange_token',
              'input_token'=>$tokenForCheck,
              'access_token'=>$appToken);
        $query = $this->options['debugPath'].http_build_query($data);
        $response = json_decode(file_get_contents($query));
        return $response->data->is_valid;
    }
    
    public function getLongLiveToken($shortLiveToken){
        $data = array('grant_type'=>'fb_exchange_token',
              'client_id'=>$this->options['appID'],
              'client_secret'=>$this->options['appSecret'],
              'fb_exchange_token'=>$shortLiveToken);
        $query = $this->options['basePath'].http_build_query($data);
        $request = file_get_contents($query);
        parse_str($request, $token);
        return $token['access_token'];
    }
    
    public function getInfoMe($interest, $token){
        $data = array('access_token'=>$token);
        $query = $this->options['mePath'].$interest.'?'.http_build_query($data);
        $response = json_decode(file_get_contents($query));
        return $response;
    }
    
    public function getLikes($token){
        $data = array('access_token'=>$token, 'fields'=>'likes');
        $query = $this->options['mePath'].'?'.http_build_query($data);
        $response = json_decode(file_get_contents($query));
        return $response;
    }
    
    public function getPosts($token){
        $data = array('access_token'=>$token, 'fields'=>'posts');
        $query = $this->options['mePath'].'?'.http_build_query($data);
        $response = json_decode(file_get_contents($query));
        return $response;
    }
    
    public function getProfilePic($token){
        $data = array('access_token'=>$token);
        $query = $this->options['profilepicPath'].http_build_query($data);
        preg_match('@{.*}@',file_get_contents($query), $maches);
        return json_decode($maches[0])->data->url;
    }
	
    public function getFriends($token) {
        try {
            $data = array('access_token' => $token);
            $query = $this->options['friendsPath'] . http_build_query($data);
            $friends = json_decode(file_get_contents($query));
        } catch (Exception $exc) {
                $friends = array();
                $exc->getTraceAsString();
        }
        return $friends;
    }
	
    public function sendMessage($token, $to, $message) {
		try {
			$parameters = array(
//				'access_token' => $token,
				'app_id' => $this->options['appID'],
				'to' => $to,
				'link' => 'http://195.177.237.145/',
				'redirect_uri' => 'http://195.177.237.145/',
//    'method' => 'send',
//				'message' => $message,
			);
			$url = 'https://www.facebook.com/dialog/send?' . http_build_query($parameters);
			print_r($url);
			$response = json_decode(file_get_contents($url));
			print_r($response); die;
		} catch (Exception $exc) {
			$response = false;
			$exc->getTraceAsString();
		}
		return $response;
	}
    
    private function _getFriends($token) {
        $data = array('access_token' => $token,
            'fields' => 'installed,name,picture.width(320).height(320)');
        $query = $this->options['mePath'].'friends?'.http_build_query($data);
        $friends = json_decode(file_get_contents($query),true);
        if(!empty($friends))
            return $friends;
        return false;
    }

    public function _getCacheFriends($user_id){
         if(!$this->cache->test('user_friends_'.$user_id)){
             if(empty($_SESSION['token'])){
                 $user_p = $this->qM->getUserBySplystId($user_id);
             }else
                 $user_p['fb_access_token'] = $_SESSION['token'];
            $friends = $this->_getFriends($user_p['fb_access_token']);
            if(!empty($friends)){
                $friends = addslashes(json_encode($friends));
                $this->qM->setFriendsForShare($user_id, $friends);
                }
            $friends_data = json_decode($this->qM->getFriendsForShare($user_id),true);
            $this->cache->save($friends_data, 'user_friends_'.$user_id, array(), 7200);
         }else
             $friends_data = $this->cache->load('user_friends_'.$user_id);
        return $friends_data;
    }
    
    public function getFriendsForShare($user_id){
         if(!$this->cache->test('user_friends_for_share_'.$user_id)){
            $friends = $this->_getCacheFriends($user_id);
            foreach ($friends['data'] as $key => &$friend){
                unset ($friend['picture']);
            }
            $this->cache->save($friends['data'], 'user_friends_for_share__'.$user_id, array(), 7200);
         }else
             $friends['data'] = $this->cache->load('user_friends_for_share__'.$user_id);
        return $friends['data'];
    }

    public function getUserWorkAndEducation($token){
        $data = array('access_token'=>$token, 'fields'=>'work,education');
        $query = $this->options['mePath'].'?'.http_build_query($data);
        $response = json_decode(file_get_contents($query));
        return $response;
        
    }
    
    /* Select new user likes based on FB likes, posts, school and job. 
     * Input array user data from users table.
     * Return text for forming new tags
     */
    public function takeUserLikes($user){
        $likes = $this->getLikes($user['fb_access_token']);
        $posts = $this->getPosts($user['fb_access_token']);
        $userprofile = $this->getUserWorkAndEducation($user['fb_access_token']);
        if(empty($user['new_user']))
            $fb_data = array('likes'=>array(), 'posts'=>array(), 'work'=>array(), 'education'=>array());
        else {
            $fb_data = json_decode($this->qM->getUserLikes($user['id_tbl_user']), true);
        }
        $user_tags = array();
        if(!empty($likes->likes->data)) {
            foreach($likes->likes->data as $like){
                if(!in_array($like->id, $fb_data['likes'])){
                    $fb_data['likes'][] = $like->id;
                    $user_tags[] = $like->name;
                }
            }
        }
        if(!empty($posts->posts->data)) {
            foreach($posts->posts->data as $post){
                if(!empty($post->description) && !in_array($post->id, $fb_data['posts'])){
                    $fb_data['posts'][] = $post->id;
                    $user_tags[] = $post->description;
                }
            }
        }
        if(!empty($userprofile->work)) {
            foreach($userprofile->work as $work){
                if(!in_array($work->employer->id, $fb_data['work'])){
                    $fb_data['work'][] = $work->employer->id;
                    $user_tags[] = $work->employer->name;
                }
            }
        }
        if(!empty($userprofile->education)) {
            foreach($userprofile->education as $educ){
                if(!in_array($educ->school->id, $fb_data['education'])){
                    $fb_data['education'][] = $educ->school->id;
                    $user_tags[] = $educ->school->name;
                }
            }
        }
        $this->qM->setUserLikes($user['id_tbl_user'], json_encode($fb_data));
        return $user_tags;
    }
}

?>
