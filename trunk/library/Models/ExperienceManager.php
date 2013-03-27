<?php

class Models_ExperienceManager extends App_Zenddb {
    
    public function getDefaultExperience($checked = false) {
        if(empty($checked)){
            $select = $this->db->select()
                               ->from(array('t' => 'experience'), array('exp_id', 'exp_title','checked'))
                               ->where('t.exp_id >= 1 and  t.exp_id <= 63');
            $result = $this->db->fetchAll($select);
        }else{
            $select = $this->db->select()
                               ->from(array('t' => 'experience'), array('exp_id', 'exp_title','checked'))
                               ->where('t.exp_id >= 1 and  t.exp_id <= 63 and checked =1');
            $result = $this->db->fetchAll($select);
        }
        return $result;
    }
    
    public function saveUserDefaultExperience($data){
        $this->db->query('Insert into user_experience (`user_id`, `exp_id`) values '.$data);
    }

    public function getUserExperience($userId){
        $query = $this->db->query("SELECT count(user_exp_link.link_id) as count, experience.exp_id,
            experience.exp_url, user_experience.exp_thumbnail, experience.exp_title, user_experience.id as id
            FROM user_experience
            Left Join  experience  
            on user_experience.exp_id = experience.exp_id 
            Left JOIN user_exp_link  
            on user_exp_link.user_exp_id = user_experience.id 
            where user_experience.user_id = ".$userId." and user_experience.exp_id != 64
            group by IFNULL(user_exp_link.user_exp_id, experience.exp_id) order by experience.exp_id desc;");
        $query1 = $this->db->query("SELECT count(user_exp_link.link_id) as count, experience.exp_id,
            experience.exp_url, user_experience.exp_thumbnail, experience.exp_title, user_experience.id as id
            FROM user_experience
            Left Join  experience  
            on user_experience.exp_id = experience.exp_id 
            Left JOIN user_exp_link  
            on user_exp_link.user_exp_id = user_experience.id 
            where user_experience.user_id = ".$userId." and user_experience.exp_id = 64
            group by IFNULL(user_exp_link.user_exp_id, experience.exp_id) order by experience.exp_id desc;");
        $exp = $query->fetchAll();
        $exp1 = $query1->fetchAll();

        return array_merge($exp1, $exp);
    }

    public function getLinks($exp_id){
        $select2 = $this->db->select()
                            ->from(array('ue' => 'user_exp_link'), array('ue.link_id'))
                            ->where('ue.user_exp_id = '.$exp_id);
        $select3 = $this->db->select()
                                ->from(array('l' => 'links'), array('l.*'))
                                ->joinLeft(array('t' =>'tags'), 'l.tags = t.id', array('tags_name'=>'t.name'))
                                ->where('l.id in ('.$select2.')')
                                ->order('l.id DESC');
        $result = $this->db->fetchAll($select3);
        return $result;

    }
    
//    public function getNews($link_id){
//        $select = $this->db->select()
//                ->from(array('c' => 'news_content'))
//                ->join(array('n' =>'news'), 'c.news_id = n.news_cont_id')
//                ->where('n.link_id ='.$link_id);
//        $result = $this->db->fetchAll($select);
//        return $result;
//    }
//    
//    public function getWikis($wiki_id){
//        $select = $this->db->select()
//                ->from(array('c' => 'wikis_content'))
//                ->join(array('n' =>'wiki'), 'c.wiki_id = n.wiki_cont_id')
//                ->where('c.wiki_id ='.$wiki_id);
//        $result = $this->db->fetchAll($select);
//        return $result;
//    }
//    
//    public function getImages($img_id){
//        $select = $this->db->select()
//                ->from(array('c' => 'images_content'))
//                ->join(array('n' =>'images'), 'c.img_id = n.img_cont_id')
//                ->where('c.img_id ='.$img_id);
//        $result = $this->db->fetchAll($select);
//        return $result;
//    }
//    
//    public function getVideos($videos_id){
//        $select = $this->db->select()
//                ->from(array('c' => 'videos_content'))
//                ->join(array('n' =>'videos'), 'c.video_id = n.video_cont_id')
//                ->where('c.video_id ='.$videos_id);
//        $result = $this->db->fetchAll($select);
//        return $result;
//    }
    
    public function checkExistExpWithSuchTitle($title){
        $select = $this->db->select()
                ->from(array('c' => 'experience'))
                ->where("c.exp_title ='".$title."'");
        $result = $this->db->fetchRow($select);
        if(!empty($result)) {
           return $result['exp_id'];
        }
        return false;
    }
    public function saveExperience($user_id, $exp_id){
        $data = array('user_id'=> $user_id, 'exp_id' =>$exp_id);
        $this->db->insert('user_experience', $data);
        return $this->db->lastInsertId();
    }
    
    public function saveNewExperience($user_id, $title){
        $data = array('exp_title'=> $title);
        $this->db->insert('experience', $data);
        $id = $this->db->lastInsertId();
        $data2 = array('user_id'=> $user_id, 'exp_id' =>$id);
        $this->db->insert('user_experience', $data2);
		return $this->db->lastInsertId();
    }

    public function checkAlreadyExistExpSuchUser($id, $user_id){
        $select = $this->db->select()
                ->from(array('c' => 'user_experience'))
                ->where("c.exp_id =".$id." and c.user_id =".$user_id);
        $result = $this->db->fetchRow($select);
        if(empty($result)) {
           return true;
        }
        return false;
    }
    
    public function saveLink($data){
        if(!empty($data['comment'])){
            $comments[] = array('date'=>date('Y/m/d H:i:s'), 
                'name'=> $_SESSION['user_name'], 
                'data'=>$data['comment'], 
                'user_pic'=>$_SESSION['user_pic'],
                'fb_user_id' =>$_SESSION['fb_user_id']);
            $link = array('url'=> $data['url'], 'title'=>$data['title'], 'thumbnail'=> $data['img'] , 'tags'=> $data['tags'], 'comments' =>json_encode($comments));
        }else{
            $link = array('url'=> $data['url'], 'title'=>$data['title'], 'thumbnail'=> $data['img'] , 'tags'=> $data['tags']);
        }
        $this->db->insert('links', $link);
        $link_id = $this->db->lastInsertId();
        $data2 = array('user_exp_id' =>$data['id'], 'link_id'=> $link_id);
        $this->db->insert('user_exp_link', $data2);
        $select = $this->db->select()
                ->from(array('c' => 'user_exp_link'), array('count(*)'))
                ->where('c.user_exp_id ='.$data['id']);
        $count = $this->db->fetchOne($select);
        $select2 = $this->db->select()
                ->from(array('c' => 'user_experience'), array('exp_thumbnail'))
                ->where('c.id ='.$data['id']);
        $img = $this->db->fetchOne($select2);
        if($count == 1 && $img == null){
            $this->db->query('UPDATE user_experience SET exp_thumbnail = '.$data['img'].' WHERE id = '.$data['id']);
        }

        return array("id"=>$data['id'], "exp_thumbnail"=>$data['img'], "exp_title"=>$data['title']);
    }
    
    public function rec2link($data){
        $select_link = $this->db->select()
                ->from(array('links'), array('id', 'comments'))
                ->where('url = "'.$data['url'].'"');
        $link = $this->db->fetchRow($select_link);
        if(!empty($link['id'])){
            $comments = json_decode($link['comments'],true);
            if(!empty($comments))
                array_push($comments, json_decode($data['comments'], true));
            else
                $comments[] = json_decode($data['comments'], true);
            $this->db->update('links', array('comments'=> json_encode($comments)), 'id ='.$link['id']);
            $link_id = $link['id'];
        }else{
            $comments[] = json_decode($data['comments'], true);
            $data['comments'] = json_encode($comments);
            $this->db->insert('links', $data);
            $link_id = $this->db->lastInsertId();
        }
        return $link_id;
    }
    public function createLinkOnRec($user_id, $data, $lock, $link_id){
//            $link_id = $this->rec2link($data);
            $select1 = $this->db->select()
                    ->from('user_experience', array('id'))
                    ->where('user_id = '.$user_id.' and exp_id = 64');
            $user_exp_id = $this->db->fetchOne($select1);
//            echo $select1->__toString(); die;
            
            $select5 = $this->db->select()
                    ->from(array('user_exp_link'), array('id'))
                    ->where('user_exp_id ='.$user_exp_id.' and link_id = '.$link_id);
//            echo $select5->__toString(); die;
            $exist_link_id = $this->db->fetchOne($select5);
            if(empty($exist_link_id)){
                $notif_type = 1;//content
                $data2 = array('user_exp_id' =>$user_exp_id, 'link_id'=> $link_id, 'lock' =>$lock);
                $this->db->insert('user_exp_link', $data2);
            }else
                $notif_type = 2;//comment
            if(!empty($data['img'])){
                $select2 = $this->db->select()
                        ->from(array('c' => 'user_experience'), array('exp_thumbnail'))
                        ->where('c.id ='.$user_exp_id);
                $img = $this->db->fetchOne($select2);
                if($img == null){
                    $this->db->query('UPDATE user_experience SET exp_thumbnail = '.$data['img'].' WHERE id = '.$user_exp_id);
//                    $select = $this->db->select()
//                            ->from(array('c' => 'user_exp_link'), array('count(*)'))
//                            ->where('c.user_exp_id ='.$user_exp_id);
//                    $count = $this->db->fetchOne($select);
                }
            }
//            if($count == 1 && $img == null && !empty($data['img'])){}
            return array('user_exp_id'=>$user_exp_id, 'notif_type' =>$notif_type);
    }

    public function getExperience($id){
        $select = $this->db->select()
            ->from(array('ue' => 'user_experience'))
            ->join(array('e' =>'experience'), 'e.exp_id = ue.exp_id')
            ->where("ue.id ='".$id."'");
        $result = $this->db->fetchRow($select);

        return $result;
    }
    
    public function createDefExp($user_id){
        $data = array('user_id'=> $user_id, 'exp_id' => 64);
        $this->db->insert('user_experience', $data);
    }
    public function getExperienceByLink($user_id, $link_id) {
        $query = $this->db->query('Select
                                        e.user_exp_id
                                    From
                                        user_experience as u
                                            Join
                                        user_exp_link as e ON e.user_exp_id = u.id
                                    Where
                                        link_id = '.$link_id.' and user_id = '.$user_id.';');
        return $query->fetchAll();
    }
}
