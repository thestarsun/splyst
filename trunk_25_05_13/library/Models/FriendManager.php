<?php

class Models_FriendManager extends App_Zenddb {
    
    public function setFriend($user_id, $fr_id){
        $data = array('user_id'=>$user_id, 'fr_id'=>$fr_id);
        $this->db->insert('friends', $data);
        $data = array('user_id'=>$fr_id, 'fr_id'=>$user_id);
        $this->db->insert('friends', $data);
    }
    public function getFriends($user_id){
        $select = $this->db->select()
                ->from(array('f' => 'friends'), array())
                ->join(array('u'=>'users'), 'u.id_tbl_user = f.fr_id', array('name','pic as picture', 'fb_user_id as id'))
                ->where('f.user_id = '.$user_id);
        return $this->db->fetchAll($select);
    }
    
}
