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
//                ->from('links', array('tags'))
//                ->where('id ='.$id);
//       return $this->db->fetchOne($select);
                ->from('user_link_tags', array('tag_id'))
                ->where('tag_type_id = 0')
                ->where('link_id ='.$id);
        $tags = $this->db->fetchAll($select);
        $res_tags = array();
        foreach($tags as $tag){
            $res_tags[] = $tag['tag_id']; 
        }
        return $res_tags;
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
    
    public function getTagType($name){
        $select = $this->db->select()
                ->from('tag_types', array('id'))
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
    
    public function setTagType($name){
        $data = array('name' => $name);
        $this->db->insert('tag_types', $data);
        return $this->db->lastInsertId();
    }
    
    public function addTagType(){
        $this->db->query('Insert ignore into user_link_tags (user_id, tag_id) values ("'.$user_id.'","'.$tag.'")');
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
    
    public function setUserLinkTag($link_id, $tag_id, $tagType_id = FALSE){
        $data = array('link_id' => $link_id, 'tag_id' => $tag_id);        
        if($tagType_id) 
            $data['tag_type_id'] = $tagType_id;
        $this->db->insert('user_link_tags', $data);
    }
    
    public function addUserRecTag($user_id, $tag){
        $this->db->query('Insert ignore into user_rec_tags (user_id, tag_id) values ("'.$user_id.'","'.$tag.'")');
    }
    
    public function addRec2User($user_id, $rec, $usr_arr = null){
        $data = array('date'=>date('Y/m/d H:i:s'), 
            'type'=>$rec['type'],
            'thumbnail'=>$rec['thumbnail'],
            'description'=>$rec['description'],
            'title'=>$rec['title'],
            'url'=>$rec['url'],
            'tags'=>$rec['tags'],
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
        $this->db->query("INSERT IGNORE INTO user_rec (user_id, rec_id) VALUES ('".$user_id."','".$id."')");
        $this->db->insert('user_recom_likes', array("user_id"=>$user_id, "recom_id"=>$id,"likes"=>0));
        $login = $this->db->select()->from('users', array('last_login'))->where('id_tbl_user = '.$user_id);
        $login_date = $this->db->fetchOne($login);
        $time = time() - 2400;
        if($login_date > $time){
            $get_count_tags = $this->db->query('SELECT r.id
                        FROM user_experience AS ue, user_exp_link AS uel, user_link_tags AS ult, recommendations AS r
                        WHERE ue.user_id = '.$user_id.'
                        AND uel.user_exp_id = ue.id
                        AND ult.link_id = uel.link_id
                        AND ult.tag_type_id = 0
                        AND ult.count_tags > 0
                        AND r.tags = ult.tag_id
                        AND r.id = '.$id.'
                        GROUP BY ult.tag_id');
            $count_tags = $this->db->fetchOne($get_count_tags);
            if(empty($count_tags)){
                $get_count_tags = $this->db->query('SELECT ur.id FROM recommendations AS r, user_rec_tags AS ur where ur.tag_id = r.tags AND ur.user_id = '.$user_id.' AND r.id = '.$id);
                $count_tags = $this->db->fetchOne($get_count_tags);
            }
            if(!empty($count_tags)){
                    $select = $this->db->select()->from('users', array('counter_rec'))->where('id_tbl_user = '.$user_id);
                    $counter = $this->db->fetchOne($select);
                    $this->db->update('users',array('counter_rec'=> ++$counter), 'id_tbl_user = '.$user_id);
            }
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
         return $user_exp_id;
    }
    
    public function deleteExperience($exp_id){
        $this->db->delete('user_exp_link','user_exp_id = '.$exp_id);
        $this->db->delete('user_experience','id = '.$exp_id);
    }

    public function deleteUserTag($recom_id, $tag_id, $user_id){
        $select = $this->db->select()
                           ->from(array('r' => 'recommendations'), array('id', 'title', 'likes'))
                           ->where('tags = '.$tag_id);
        $recom_data = $this->db->fetchAssoc($select);
        foreach ($recom_data as &$item){
            $item = '('.$item['id'].') - '.$item['title'];
        }
        $recom_ids = array_keys($recom_data);
        $select_dislike_recom = $this->db->select()
                                         ->from(array('url' => 'user_recom_likes'), array('url.recom_id'))
                                         ->joinLeft(array('r' =>'recommendations'), 'r.id = url.recom_id', array('r.id','r.title'))
                                         ->where('url.likes < 0')
                                         ->where("url.recom_id in (".implode(',',$recom_ids).")");
        $recom_dislike = $this->db->fetchAssoc($select_dislike_recom);
        foreach ($recom_dislike as &$item){
            $item = '('.$item['id'].') - '.$item['title'];
        }

        $tags_name = $this->getTagName($tag_id);
        $user_name = $_SESSION['user_name'];
        $this->db->query('UPDATE recommendations SET likes = likes-1 WHERE id='.$recom_id.' and tags='.$tag_id);
        $this->db->delete('user_rec','rec_id IN ('.implode(",", $recom_ids).') and user_id ='.$user_id);
        $this->db->delete('user_recom_likes','recom_id = '.$recom_id.' and user_id ='.$user_id);
        $this->db->delete('user_rec_tags','tag_id = '.$tag_id.' and user_id ='.$user_id);

        $log_message_data = array('user_id'=>$user_id, 'user_name'=>$user_name, 'tag_id'=>$tag_id, 'tag_name'=>$tags_name, 'recommendation_info'=>$recom_data, 'dislike_recommendations'=>$recom_dislike);
        $log_message = json_encode($log_message_data);
        $logger = Zend_Registry::get('logger');
        $logger->log($log_message, Zend_Log::INFO);
    }
    
    public function updateUserLinkTag($link_id, $tag_id){
        $this->db->query('UPDATE user_link_tags SET count_tags = count_tags+1 WHERE link_id='.$link_id.' AND tag_id='.$tag_id);
    }
}
