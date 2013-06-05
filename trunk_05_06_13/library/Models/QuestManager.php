<?php

class Models_QuestManager extends App_Zenddb {
    
     public function getUserById($id) {
        $select = $this->db->select()
                           ->from(array('t' => 'users'))
                           ->where('t.fb_user_id = ?', $id);
        $result = $this->db->fetchRow($select);
        return $result;
    }

    public function setUser($data){
        $this->db->insert('users', $data);
        return $this->db->lastInsertId();
    }
    
    public function getUserBySplystId($id) {
        $select = $this->db->select()
                           ->from(array('t' => 'users'))
                           ->where('t.id_tbl_user = ?', $id);
        $result = $this->db->fetchRow($select);
        return $result;
    }
	
    public function getExistUsersByFbId($idArray) {
        $select = $this->db->select()
                           ->from(array('t' => 'users'),array('fb_user_id'))
                           ->where('t.fb_user_id IN (' . $idArray.')');
        $result = $this->db->fetchAll($select);
        return $result;
    }
    
    public function addProfileData($data){
        $save = array('password' => $data['password'],
              'email' => $data['email'],
              'birthday' => $data['birthday'],
              'name' => $data['user_name'],
              'pic'=> $data['userpic']);
        $where = $this->db->quoteInto('fb_user_id = ?', $data['fb_id']);
        $this->db->update('users', $save, $where);
    }
    
    public function getUserByEmail($email){
        $select = $this->db->select()
                           ->from(array('t' => 'users'))
                           ->where('t.email = ?', $email);
        $result = $this->db->fetchRow($select);
        return $result;
    }
    
    public function getUserIDByFBID($fbID){
        $select = $this->db->select()
                           ->from(array('t' => 'users'), array('id_tbl_user'))
                           ->where('t.fb_user_id  = ?', $fbID);
        return $this->db->fetchOne($select);
        
    }
    
    public function loginUser($params){
        $select = $this->db->select()
                           ->from(array('t' => 'users'))
                           ->where('t.email = ?', $params['email'])
                           ->where('t.password = ?', $params['password']);
        $result = $this->db->fetchRow($select);
        return $result;
    }

    public function updatePass($user_id, $pass){
        $save = array('password' => $pass);
        $where = 'id_tbl_user = '.$user_id;
        $result = $this->db->update('users', $save, $where);
        return $result;
    }
    
    public function deteleUser($id){
        $this->db->query('DELETE FROM users WHERE id_tbl_user ='.$id);
    }
    
    public function getUserLikes($id){
        $select = $this->db->select()
                ->from(array('t' => 'user_likes'), array('t.data'))
                ->where('t.user_id ='. $id);
        $result = $this->db->fetchOne($select);
        return $result;
    }
    
    public function setUserLikes($id, $data){
        $this->db->query("INSERT INTO user_likes (user_id, data, date) VALUES ('".$id."','".$data."','".date('Y/m/d H:i:s')."') ON DUPLICATE KEY UPDATE data = '".$data."', date = '".date('Y/m/d H:i:s')."'");
    }
    
    public function setUserOld($id){
        $this->db->update('users', array('new_user' => 1), 'id_tbl_user ='.$id);
    }
    
    public function setLastUserActivity($user_id){
        $this->db->update('users', array('last_login'=> time()), 'id_tbl_user = '.$user_id);
    }
    
    public function getLastUserActivity($user_id){
        $select = $this->db->select()
                ->from(array('t' => 'users'), array('t.last_login'))
                ->where('t.id_tbl_user = '.$user_id);   
        $result = $this->db->fetchOne($select);
        return $result;
    }
    
    public function getActiveUsers($active_time = null){
        if(empty($active_time))
            $active_time = 2400;
        $time = time()-$active_time;
        $select = $this->db->select()
                ->from(array('t' => 'users'), array('t.id_tbl_user'))
                ->where('last_login >'.$time);
        $result = $this->db->fetchAll($select);
        return $result;
    }
    
    public function setFriendsForShare($id, $data){
        $front = \Zend_Controller_Front::getInstance();
        $bootstrap = $front->getParam("bootstrap");
        $options = $bootstrap->getOption('db');
        $connect = mysql_connect($options['host'], $options['login'], $options['pass']);//or die("no connect with data base");
        $db = mysql_select_db($options['schema'], $connect);
        $sql ="INSERT INTO user_likes (user_id, friends, friends_update_date) VALUES ('".$id."','".$data."','".date('Y/m/d H:i:s')."') ON DUPLICATE KEY UPDATE friends = '".$data."', friends_update_date = '".date('Y/m/d H:i:s')."'";
        mysql_query($sql);
//        $this->db->query("INSERT INTO user_likes (user_id, friends, friends_update_date) VALUES ('".$id."','".
//                $data."','".date('Y/m/d H:i:s')."') ON DUPLICATE KEY UPDATE friends = '".$data."', friends_update_date = '".date('Y/m/d H:i:s')."'");
    }
    
    public function getFriendsForShare($id){
        $select = $this->db->select()
                ->from(array('t' => 'user_likes'), array('t.friends'))
                ->where('t.user_id ='. $id);
        $result = $this->db->fetchOne($select);
        return $result;
        
    }
    
    public function addUserNotification($userId){

//        type_id = 1 - sharing button - content share
//        type_id = 2 - add comment
//        type_id = 3 - new friend
        for($i=1;$i<6;$i++){
            $data = array();
            $data['user_id'] = $userId;
            $data['type_id'] = $i;
            $data['count'] = 0;
            $this->db->insert('user_notification', $data);
        }
    }
    
//    public function getAllUsers(){
//        $select = $this->db->select()
//                ->from(array('t' => 'users'));
//        $result = $this->db->fetchAll($select);
//        return $result;
//    }
    
    public function updateUserNotification($userId, $typeId, $data, $increase = null){
        if(!empty($userId) && !empty($typeId) && $typeId>0 && $typeId<6){
            if(!empty($increase)){
                $currNotify = $this->getUserNotifications($userId, $typeId);
                $new_message = $currNotify['new_message']+1;
                $notifData = json_decode($currNotify['data'], true);
                if(empty($notifData))
                    $notifData = array();
                array_unshift($notifData, $data);
                if(count($notifData) > 15 && $typeId != 4){
                    array_splice($notifData, 15);
                }
                $currNotify['count'] = count($notifData);
                $this->db->update('user_notification', array('count'=> $currNotify['count'], 'data' => json_encode($notifData), 'new_message'=>$new_message), 'user_id = '.$userId.' AND type_id = '.$typeId);
            }
            else{
                $this->db->update('user_notification', array('new_message'=> 0), 'user_id = '.$userId.' AND type_id = '.$typeId);
            }
        }
    }
    
    public function getUserNotifications($userId, $typeId = null){
        $result = null;
        if(!empty($userId)){
            $select = $this->db->select()
                ->from(array('t' => 'user_notification'))
                ->where('t.user_id ='. $userId);
            
            if(!empty($typeId)){
                $select->where('t.type_id = '.$typeId);
                $result = $this->db->fetchRow($select);
            }else
                $result = $this->db->fetchAll($select);
        }
        return $result;
    }

    public function updateTypeNotifications($userId, $typeId, $data){
        if(empty($data)) $notifications = '';
        else $notifications = json_encode($data);
        $update = $this->db->update('user_notification', array('count' => count($data), 'data' => $notifications, 'new_message' => 0), 'user_id = '.$userId.' AND type_id = '.$typeId);
        if($update) return true;
        else return false;
    }
    
    public function getRegistrationEnd($user_id){
        $select = $this->db->select()
                ->from(array('t' => 'user_experience'), array('id'))
                ->where('user_id ='.$user_id.' and exp_id = 64' );
        return $this->db->fetchOne($select);
    }
    
    public function getAllUsers(){
        $select = $this->db->select()
                ->from(array('t' => 'users'), array('name', 'fb_user_id as id','pic as picture'));
        return $this->db->fetchAll($select);
    }

    public function getRecommendationThumbs($recom_id){
        $select = $this->db->select()
            ->from(array('t' => 'recommendations'), array('t.likes', 't.dislikes', 't.splyse'))
            ->where('t.id ='.$recom_id);
        $result = $this->db->fetchRow($select);

        return $result;
    }
    
    public function get_tags_thumbs($tag_id, $user_id){
        $select = $this->db->select()
            ->from(array('t' => 'user_rec_tags'), array('t.likes'))
            ->where('t.tag_id ='.$tag_id.' and user_id = '.$user_id);
        $result = $this->db->fetchRow($select);

        return $result['likes'];
    }

    public function getUserRecommendationThumbs($user_id, $recom_id){
        $select = $this->db->select()
            ->from(array('t' => 'user_recom_likes'), array('t.likes'))
            ->where('t.user_id = '.$user_id.' AND recom_id = '.$recom_id);
        $result = $this->db->fetchRow($select);

        return $result['likes'];
    }

    public function update_recommendation_thumbs($action, $user_id, $recom_id, $tag_id){
        $increase = ' + 1';
        $increase2 = '1';
        $rec_likes = 'likes';
        if($action == 'down'){
            $increase = ' - 1';
            $increase2 = '-1';
            $rec_likes = 'dislikes';
            $select = $this->db->select()
                               ->from(array('r' => 'recommendations'), array('id', 'title', 'likes'))
                                ->join(array('t' => 'tags'), 't.id = r.tags', array('t.name'))
                               ->where('r.id = '.$recom_id);
            $result_data = $this->db->fetchRow($select);
            $recom_data = '('.$result_data['id'].') - '.$result_data['title'];

            $log_message_data = array('user_id'=>$user_id, 'user_name'=>$_SESSION['user_name'], 'tag_id'=>$tag_id, 'tag_name'=>$result_data['name'], 'dislike_recommendations'=>$recom_data);
            $log_message = json_encode($log_message_data);
            $logger = Zend_Registry::get('logger');
            $logger->log($log_message, Zend_Log::INFO);
        }
        
        $sql_query = 'UPDATE user_rec_tags SET likes = likes'.$increase.' WHERE tag_id='.$tag_id.' and user_id='.$user_id;
        $sql_query2 = 'UPDATE recommendations SET '.$rec_likes.' = '.$rec_likes.$increase.' WHERE id='.$recom_id.' and tags='.$tag_id;
        $sql_query3 = 'UPDATE user_recom_likes SET likes = '.$increase2.' WHERE recom_id='.$recom_id.' and user_id='.$user_id;

        $sql = $this->db->query($sql_query);
        $sql2 = $this->db->query($sql_query2);
        $sql3 = $this->db->query($sql_query3);
        
        if($sql && $sql2 && $sql3) return $this->getRecommendationThumbs($recom_id);
        else return false;
    }

    public function recommendationSplyse($action, $recom_id){
        if($action == 'update'){
            $this->db->query('UPDATE recommendations SET splyse = splyse+1 WHERE id='.$recom_id);
        }elseif($action == 'get'){
            $select = $this->db->select()
                ->from(array('r' => 'recommendations'), array('r.likes','r.splyse'))
                ->where('r.id ='.$recom_id);
            $result = $this->db->fetchRow($select);
            
            return $result;
        }
    }
}
