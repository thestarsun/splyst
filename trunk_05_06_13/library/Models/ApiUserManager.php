<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vstymkovskyi
 * Date: 26.04.13
 * Time: 9:58
 * To change this template use File | Settings | File Templates.
 */
class Models_ApiUserManager extends App_Zenddb {

    public function getUser($fb_user_id, $token){
        $select = $this->db->select()
            ->from(array('u' => 'users'), array('u.id_tbl_user', 'u.name', 'u.pic', 'u.counter_rec', 'u.max_rec_id'))
            ->where('u.fb_user_id = '.$fb_user_id)
            ->where('u.fb_access_token = "'.$token.'"');
        $result = $this->db->fetchRow($select);

        if(!empty($result)){
            $result['request_token'] = md5($result['id_tbl_user'].$token.time());
            $this->db->update('users', array('request_token' => $result['request_token']), 'fb_user_id = '.$fb_user_id);
            return $result;
        }else return false;
    }
    
    public function logout($request_token){
        $result = FALSE;        
        $select = $this->db->select()
            ->from(array('u' => 'users'), array('u.id_tbl_user'))
            ->where('u.request_token = "'.$request_token.'"');     
        $res = $this->db->fetchAll($select);
        if(!empty($res)){
            $this->db->query('UPDATE users SET request_token = "" WHERE request_token = "'.$request_token.'"');
            $result= TRUE;
        }
        return $result;
    } 

}