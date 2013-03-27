<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vstymkovskyi
 * Date: 06.02.13
 * Time: 12:49
 * To change this template use File | Settings | File Templates.
 */

class Models_RecommendationManager extends App_Zenddb {

    public function getUserRecommendations($user_id, $page=null){
        $select = $this->db->select()
            ->from(array('ur' => 'user_rec'))
            ->join(array('r' =>'recommendations'), 'r.id = ur.rec_id')
            ->where("ur.user_id = '".$user_id."'")
            ->limit(10, $page);
        $result = $this->db->fetchAll($select);

        return $result;
    }
    
    public function getUserRecommendationsUnlimit($user_id){
        $select = $this->db->select()
            ->from(array('ur' => 'user_rec'))
            ->join(array('r' =>'recommendations'), 'r.id = ur.rec_id')
            ->where("ur.user_id = '".$user_id."'");
        $result = $this->db->fetchAll($select);

        return $result;
    }
    
    public function getImgRecommendations(){
        $select = $this->db->select()
                ->from(array('t' => 'recommendations'), array('t.id', 't.url'))
                ->where('t.type = 2');
        return $this->db->fetchAll($select);
    }
    
    public function unsetbrokenRec($ids_str){
        $this->db->query('DELETE FROM recommendations WHERE id in ('.$ids_str.')');
        $this->db->query('DELETE FROM user_rec WHERE rec_id in ('.$ids_str.')');
        
    }

    public function getRecID($user_id){
        $select = $this->db->select()
                ->from(array('ur' => 'user_rec'), array())
                ->join(array('r' =>'recommendations'), 'r.id = ur.rec_id', array('r.id'))
                ->where("ur.user_id = '".$user_id."'")
                ->order('ur.id desc');
        $result = $this->db->fetchAll($select);
        $ids_arr = array();
        foreach ($result as $res){
            $ids_arr[] = $res['id'];
        }
        return $ids_arr;
    }
    
    public function getRecMAxIDforShuffle($user_id){
        $select = $this->db->select()
                ->from(array('u' => 'users'), array('max_rec_id'))
                ->where('u.id_tbl_user = '. $user_id);
        return $this->db->fetchOne($select);
    }
    
    public function setRecMAxIDforShuffle($user_id, $ids){
        $this->db->update('users', array('max_rec_id'=> $ids), 'id_tbl_user = '.$user_id);
    }

    public function getRecommendationsByIDs($ids){
        $qv = $this->db->query('SELECT t.* FROM recommendations AS t WHERE t.id in ('.$ids.') ORDER BY FIND_IN_SET(t.id,"'.str_replace(' ', '',$ids).'")');
        $result = $qv->fetchAll();
//        $select = $this->db->select()
//                ->from(array('t' => 'recommendations'), array("*"))
//                ->where('t.id in ('.$ids.') FIND_IN_SET(t.id,'.str_replace(' ', '',$ids).')');
////                ->order('FIND_IN_SET(t.id,'.str_replace(' ', '',$ids).')');
//        echo $select->__toString();
//        die('^_^');
//        $result = $this->db->fetchAll($select);
        
        return $result;
    }
    public function getRecommendationsByID($id){
        $select = $this->db->select()
                ->from(array('t' => 'recommendations'), array("*"))
                ->where('t.id ='.$id);
        $result = $this->db->fetchRow($select);
        return $result;
    }

    public function getUserByRecommendation($id){
        $select = $this->db->select()
                ->from('user_rec_tags', array('user_id'))
                ->where('tag_id ='.$id);
        $result = $this->db->fetchAll($select);
        if(!empty($result))
            return $result; 
        else 
            return false;
    }
    
    public function getNewRec($user_id, $id){
        $select = $this->db->select()
            ->from(array('ur' => 'user_rec'))
            ->join(array('r' =>'recommendations'), 'r.id = ur.rec_id')
            ->where("ur.user_id = '".$user_id."' and ur.id >".$id)
            ->order('ur.id DESC');
        $result = $this->db->fetchAll($select);

        return $result;
    }

    public function getNews($id){
        $select = $this->db->select()
            ->from(array('r' => 'recommendations'))
            ->where("r.id = ".$id);
        $result = $this->db->fetchAll($select);

        return $result;
    }

    public function maxRecNumber($user_id){
        $select = $this->db->select()
                ->from(array('t' => 'user_rec'), array('max(t.id)'))
                ->where('t.user_id = '.$user_id);
        $result = $this->db->fetchOne($select);

        return $result;
    }
    
    public function getTagsForUsers($user_arr){
        $user_str = [];
        if(!empty($user_arr)){
            foreach($user_arr as $user){
                $user_str[] = $user['id_tbl_user'];
            }
            $select = $this->db->select()
                    ->from(array('u' => 'user_rec_tags'), array(''))
                    ->join(array('r' =>'tags'), 'u.tag_id = r.id', array('r.id', 'r.name'))
                    ->where('user_id in ('.implode(', ',$user_str).')')
                    ->group('r.id');
            return $this->db->fetchAll($select);
        }
    }
    
    public function getUsersByTag($tag_id){
       if(!empty($tag_id)){
           $select = $this->db->select()
                   ->from(array('t' => 'user_rec_tags'), array('user_id'))
                   ->where('tag_id = '.$tag_id);
           $tmp_arr = $this->db->fetchAll($select);
           $result = []; 
           foreach ($tmp_arr as $usr){
               $result[] = $usr['user_id'];
           }
           return $result;
       }
    }

}