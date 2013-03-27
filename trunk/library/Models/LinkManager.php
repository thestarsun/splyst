<?php

class Models_LinkManager extends App_Zenddb {
    
    public function getImages($tag){
        $select1 = $this->db->select()
                ->from('tags', array('img_id'))
                ->where('id ='.$tag);
        $select2 = $this->db->select()
                ->from('images',array('*'))
                ->where('id = ('. $select1.')');
        return $this->db->fetchRow($select2);
    }
    public function getMaxImages($tag){
        $select1 = $this->db->select()
                ->from('tags', array('img_max_id'))
                ->where('id ='.$tag);
        $select2 = $this->db->select()
                ->from('images',array('*'))
                ->where('id = ('. $select1.')');
        return $this->db->fetchRow($select2);
    }
    public function getLinkTitleById($id){
        $select = $this->db->select()
                ->from('links', array('title'))
                ->where('id ='.$id);
        return $this->db->fetchOne($select);
    }
    public function getLinkTag($id){
        $select = $this->db->select()
                ->from('links', array('tags'))
                ->where('id ='.$id);
        return $this->db->fetchOne($select);
    }
    public function getVideos($tag){
        $select1 = $this->db->select()
                ->from('tags', array('video_id'))
                ->where('id ='.$tag);
        $select2 = $this->db->select()
                ->from('videos',array('*'))
                ->where('id = ('. $select1.')');
        return $this->db->fetchRow($select2);
    }
    public function getMaxVideos($link_id){
        $select1 = $this->db->select()
                ->from('tags', array('video_max_id'))
                ->where('id ='.$link_id);
        $select2 = $this->db->select()
                ->from('videos',array('*'))
                ->where('id = ('. $select1.')');
        return $this->db->fetchRow($select2);
    }
    public function getTag($name){
        $select = $this->db->select()
                ->from('tags', array('id'))
                ->where('name = "'.$name.'"');
        return $this->db->fetchOne($select);
    }
    public function getTagInfo($id){
        $select = $this->db->select()
                ->from('tags', array('*'))
                ->where('id = "'.$id.'"');
        return $this->db->fetchRow($select);
    }
    
    public function setTag($name){
        $data = array('name' => $name);
        $this->db->insert('tags', $data);
        return $this->db->lastInsertId();
    }
    
    public function getTagName($id){
        $select = $this->db->select()
                ->from('tags', array('name'))
                ->where('id = '.$id);
        return $this->db->fetchOne($select);
    }
    public function setContent($tag, $data, $type, $amount, $table){
        
        $content = array('data'=> $data, 'date'=>date('Y/m/d H:i:s'));
        $select = $this->db->select()
                ->from(array('t' => 'tags'), array('t.'.$type.$amount.'_id'))
                ->where('id ='.$tag );
        $content_id = $this->db->fetchOne($select);
        if(empty($content_id)){
            $this->db->insert($table, $content);
            $id = $this->db->lastInsertId();
        }else{
            $this->db->update($table, $content, 'id ='.$content_id);
            $id = $content_id;
        }
        $this->db->query('UPDATE tags SET '.$type.$amount.'_id = '.$id.' WHERE id = '.$tag);
    }

    public function getNews($id){
        $select = $this->db->select()
            ->from(array('t' => 'tags'))
            ->join(array('n' =>'news'), 'n.id = t.news_id')
            ->where("t.id ='".$id."'");
        $result = $this->db->fetchRow($select);

        return $result;
    }
    
    public function getMaxNews($id){
        $select = $this->db->select()
            ->from(array('t' => 'tags'))
            ->join(array('n' =>'news'), 'n.id = t.news_max_id')
            ->where("t.id ='".$id."'");
        $result = $this->db->fetchRow($select);

        return $result;
    }
    
    public function getWikis($id){
        $select = $this->db->select()
            ->from(array('t' => 'tags'))
            ->join(array('n' =>'wiki'), 'n.id = wiki_id')
            ->where("t.id ='".$id."'");
        $result = $this->db->fetchRow($select);
        return $result;
    }
    public function getMaxWikis($id){
        $select = $this->db->select()
            ->from(array('t' => 'tags'))
            ->join(array('n' =>'wiki'), 'n.id = t.wiki_max_id')
            ->where("t.id ='".$id."'");
        $result = $this->db->fetchRow($select);
        return $result;
    }

    public function getExperience($id){
        $select = $this->db->select()
            ->from('user_exp_link', array('user_exp_id'))
            ->where('link_id = '.$id);
        return $this->db->fetchOne($select);
    }
    
    public function addUserRecTag($user_id, $tag){
        $data = array('user_id'=>$user_id, 'tag_id' =>$tag);
        $this->db->insert('user_rec_tags', $data);
    }
    
    public function addRec2User($user_id, $rec, $usr_arr = null){
        $data = array('date'=>date('Y/m/d H:i:s'), 
            'type'=>$rec['type'],
            'thumbnail'=>$rec['thumbnail'],
            'description'=>$rec['description'],
            'title'=>$rec['title'],
            'url'=>$rec['url'],
            );
        $select = $this->db->select()
                ->from(array('t' => 'recommendations'), array('t.id'))
                ->where("t.url = '".$rec['url']."'");
        $rec_id = $this->db->fetchOne($select);
        if(!empty($rec_id)){
            if(!empty($usr_arr)){
                foreach ($user_id as $user_id_one)
                    $this->_getUserRecId($rec_id, $user_id_one);
            }else
                $this->_getUserRecId($rec_id, $user_id);
        }else{
            $this->db->insert('recommendations', $data);
            $rec_id = $this->db->lastInsertId();
            $this->_addRec($rec_id, $user_id, $usr_arr);
        }
    }
    private function _getUserRecId($rec_id, $user_id_one){
        $select2 = $this->db->select()
            ->from(array('t' => 'user_rec'), array('t.id'))
            ->where('t.rec_id ='.$rec_id.' and t.user_id ='.$user_id_one);
        $rec_user_id = $this->db->fetchOne($select2);
        if(empty($rec_user_id)){
            $this->_addRec($rec_id, $user_id_one);
        }
    }

    private function _addRec($id, $user_id, $usr_arr = null){
        if(!empty($usr_arr)){
            foreach ($user_id as $one_user_id){
                $this->_addSinglRec($id, $one_user_id);
            }
        }  else {
            $this->_addSinglRec($id, $user_id);
        }
    }
//        $select1 = $this->db->select()
//                ->from(array('t' => 'user_rec'), array('count(*)'))
//                ->where('user_id ='.$user_id);
//        $rec_count= $this->db->fetchOne($select1);
//        if($rec_count > 200){
//            $select2 = $this->db->select()
//                    ->from(array('user_rec'), array('id'))
//                    ->where('user_id ='.$user_id)
//                    ->order('id', 'asc')
//                    ->limit(5);
//            $ids_arr = $this->db->fetchAll($select2);
//            $ids_arr_tmp = array();
//            foreach($ids_arr as $id_){
//                $ids_arr_tmp[] =$id_['id'];     
//            }
//            $ids_str = implode(', ', $ids_arr_tmp);
//            $recID = $cache->load('recommendation_ids_' .$user_id);
//            $cache->save(array_diff($recID, $ids_arr_tmp),'recommendation_ids_' . $user_id, array(), 7200); 
//            $this->db->query('DELETE FROM recommendations WHERE id in ('.$ids_str.')');
//            $this->db->query('DELETE FROM user_rec WHERE rec_id in ('.$ids_str.')');
//            
//        }
    private function _addSinglRec($id, $user_id){
        $cache = Zend_Registry::get('cache');
        $this->db->insert('user_rec', array('user_id'=>$user_id, 'rec_id'=>$id));
        $login = $this->db->select()->from('users', array('last_login'))->where('id_tbl_user = '.$user_id);
        $login_date = $this->db->fetchOne($login);
        $time = time() - 2400;
        if($login_date > $time){
            $select = $this->db->select()->from('users', array('counter_rec'))->where('id_tbl_user = '.$user_id);
            $counter = $this->db->fetchOne($select);
            $this->db->update('users',array('counter_rec'=> ++$counter), 'id_tbl_user = '.$user_id);
        }
        $cache->save('1','recommendation_ids_clean_'.$user_id, array(), 7200);
    }

    public function breakCounterRec($user_id){
        $this->db->update('users',array('counter_rec'=> 0), 'id_tbl_user = '.$user_id);
    }
    
    public function getCounter($user_id){
        $select = $this->db->select()->from('users', array('counter_rec'))->where('id_tbl_user = '.$user_id);
        return $this->db->fetchOne($select);
    }
    
    public function getLinkByID($link_id){
        $select = $this->db->select()
                ->from(array('t' => 'links'), array('*'))
                ->where('id ='. $link_id);
        $result = $this->db->fetchRow($select);
        return $result;
    }
    
    public function updateLink($data){
        $select = $this->db->select()
                ->from(array('links'), array('comments'))
                ->where('id = "'.$data['link_id'].'"');
        $link = $this->db->fetchOne($select);
        $comments = json_decode($link, true);
        $data_comments = array('date'=>date('Y/m/d H:i:s'), 
                'name'=> $_SESSION['user_name'], 
                'data'=>$data['text'], 
                'user_pic'=>$_SESSION['user_pic'],
                'fb_user_id' =>$_SESSION['fb_user_id']);
        if(!empty($comments))
            array_push($comments, $data_comments);
        else
            $comments[] = $data_comments;
        $this->db->update('links', array('comments'=> json_encode($comments)), 'id ='.$data['link_id']);
        
    }
    
    public function getLinkComments($link_id){
        $select = $this->db->select()
                ->from(array('links'), array('comments'))
                ->where('id = "'.$link_id.'"');
        return $this->db->fetchOne($select);
    }
    
    public function getUserBylink($link_id){
        //SELECT user_id FROM user_experience Where id in(SELECT user_exp_id FROM splyst.user_exp_link where link_id = 140);
        $select1 = $this->db->select()
                ->from(array('t' => 'user_exp_link'), array('user_exp_id'))
                ->where('link_id = '.$link_id);
        $select2 = $this->db->select()
                ->from('user_experience',array('user_id'))
                ->where('id in ('. $select1.')');
        $result = $this->db->fetchAll($select2);
        return $result;
    }
    
    public function deleteBookmark($user_id, $link_id){
         $select = $this->db->select()
            ->from(array('l' => 'user_exp_link'), array('l.user_exp_id'))
            ->join(array('e' =>'user_experience'), 'e.id =l.user_exp_id')
            ->where("l.link_id ='".$link_id."' and e.user_id =".$user_id);
         $user_exp_id = $this->db->fetchOne($select);
         if(!empty($user_exp_id))
             $this->db->delete('user_exp_link','user_exp_id = '.$user_exp_id.' and link_id  = '.$link_id);
    }
    
    public function deleteExperience($exp_id){
        $this->db->delete('user_exp_link','user_exp_id = '.$exp_id);
        $this->db->delete('user_experience','id = '.$exp_id);
    }
}
