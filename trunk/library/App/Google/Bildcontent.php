<?php

class App_Google_Bildcontent {
    
    protected $lM;
    protected $rM;
    protected $qM;
    protected $fM;
    protected $alchemy;
    protected $img;
    protected $vd;
    protected $nw;
    
    function __construct() {
        $this->lM = new Models_LinkManager();
        $this->rM = new Models_RecommendationManager();
        $this->qM = new Models_QuestManager();
        $this->fb = new App_Fb_Token();
        $this->alchemy = new App_Alchemy();
        $this->img = App_Google_BaseContent::factory('images');
        $this->vd = App_Google_BaseContent::factory('videos');
        $this->nw = App_Google_BaseContent::factory('news');
    }

    public function bild($params){
        $cache = Zend_Registry::get('cache');
        if(!$cache->test('getLinkTag_'.$params['id'])){
            $tag_id = $this->lM->getLinkTag($params['id']);
            $cache->save($tag_id, 'getLinkTag_'.$params['id'], array(), 172800);
        }else{
            $tag_id = $cache->load('getLinkTag_'.$params['id']);
        }
        if (!$cache->test('link_'.$params['type'].'_'.$params['amount'].'_'.$tag_id)){
            $tag_name = $this->lM->getTagName($tag_id);
            $content = App_Google_BaseContent::factory($params['type']);
            $link = ($params['amount'] == 2)?$content->getContent($tag_id):$content->getMaxContent($tag_id);
            if(strtotime('2 days ago') > strtotime($link['date'])){
                $contData = $content->parseData($tag_name, $params['amount']);
                if(!empty($contData)){
                    $data_to_write = json_encode($contData);
                    if ($params['amount'] == 2){
                        $content->setContent($tag_id, $data_to_write);
                        $this->_updateUserRec($tag_id, $params['type']);
                        $link = $content->getContent($tag_id);
                    } else {
                        $content->setMaxContent($tag_id, $data_to_write);
                        $link = $content->getMaxContent($tag_id);
                    }
                }
            }
                $link_data = json_decode($link['data']);
                $cache->save($link_data,'link_'.$params['type'].'_'.$params['amount'].'_'.$tag_id, array(), 172800);
        }else{
            $link_data = $cache->load('link_'.$params['type'].'_'.$params['amount'].'_'.$tag_id);
        }
        return $link_data;
    }
    
    public function addRec2User($tagID, $userID, $type = null, $usr_arr = null) {
         if(empty($type) || $type == 'news'){
            $data_news = $this->lM->getNews($tagID);
            $news = json_decode($data_news['data'], true);
            if (!empty($news)) {
                foreach ($news as $new) {
                    $insert_data = array();
                    $insert_data['type'] = 1;
                    $insert_data['thumbnail'] = (!empty($new['image'])) ? $new['image'] : '';
                    $insert_data['description'] = $new['content'];
                    $insert_data['url'] = $new['url'];
                    $insert_data['title'] = strip_tags($new['title']);
                    $this->lM->addRec2User($userID, $insert_data, $usr_arr);
                }
            }
        }
        if(empty($type) || $type == 'images'){
            $data_img = $this->lM->getImages($tagID);
            $imgs = json_decode($data_img['data'], true);
            if (!empty($imgs)) {
                foreach ($imgs as $img) {
                    $insert_data = array();
                    $insert_data['type'] = 2;
                    $insert_data['thumbnail'] = $img['tbUrl'];
                    $insert_data['description'] = '';
                    $insert_data['url'] = $img['url'];
                    $insert_data['title'] = strip_tags($img['content']);
                    $this->lM->addRec2User($userID, $insert_data, $usr_arr);
                }
            }
        }
        if(empty($type) || $type == 'videos'){
            $data_vd = $this->lM->getVideos($tagID);
            $videos = json_decode($data_vd['data'], true);
            if (!empty($videos)) {
                foreach ($videos as $vd) {
                    $insert_data = array();
                    $insert_data['type'] = 3;
                    $insert_data['thumbnail'] = $vd['thumbnail'];
                    $insert_data['description'] = $vd['description'];
                    $insert_data['url'] = $vd['id'];
                    $insert_data['title'] = strip_tags($vd['title']);
                    $this->lM->addRec2User($userID, $insert_data, $usr_arr);
                }
            }
        }
    }
    
    private function _updateUserRec($tagID, $type){
        $users_ids = $this->rM->getUserByRecommendation($tagID);
        if(!empty($users_ids)){
            foreach ($users_ids as $userID){
                $this->addRec2User($tagID, $userID['user_id'], $type);
            }
        }
    }
    
    public function newrecommendationbyfb($user_id) {
        if (!empty($user_id)) {
            $user = $this->qM->getUserBySplystId($user_id);
            $likes = $this->fb->takeUserLikes($user);
            //if user have new info from FB
            if (!empty($likes)) {
                $tag = array();
                // if it first visit to recommendation page after registration
                if (!empty($user['new_user'])) {
                    foreach ($likes as $like) {
//                        $tag_tmp = $this->alchemy->getTagByText($like);
//                        $tag = (!empty($tag_tmp)) ? $tag_tmp : str_replace(array('@', '#', '"', '`', "'"), '', $like);
                        $tag = str_replace(array('@', '#', '"', '`', "'"), '', $like);
                        $db_tag_id = $this->lM->getTag($tag);
                        if (!empty($db_tag_id))
                            $this->addRec2User($db_tag_id, $user['id_tbl_user']);
                        else{
                             if($this->_checkTagByWiki($tag))
                                 $db_tag_id = $this->lM->setTag($tag);
                        }
                        if (!empty($db_tag_id))
                        $this->lM->addUserRecTag($user['id_tbl_user'], $db_tag_id);
                        }
                }else {
                    foreach ($likes as $like) {
//                        $tag_tmp = $this->alchemy->getTagByText($like);
//                        $tag = (!empty($tag_tmp)) ? $tag_tmp : str_replace(array('@', '#', '"', '`', "'"), '', $like);
                        $tag = str_replace(array('@', '#', '"', '`', "'"), '', $like);
                        if($this->_checkTagByWiki($tag)){
                            $db_tag_id = $this->lM->getTag($tag);
                            if (empty($db_tag_id)) {
                                $db_tag_id = $this->lM->setTag($tag);
                                $this->_insertContent($tag, $db_tag_id);
                            }
                            $this->lM->addUserRecTag($user['id_tbl_user'], $db_tag_id);
                            $this->addRec2User($db_tag_id, $user['id_tbl_user']);
                        }
                    }
                }
            }
            $this->qM->setUserOld($user['id_tbl_user']);
        }
    }
    
    private function _insertContent($tag, $db_tag_id){
        $imd_data = $this->img->parseData($tag, 2);
        if (!empty($imd_data)) {
            $img_to_write = json_encode($imd_data);
            $this->img->setContent($db_tag_id, $img_to_write);
         }
        $vd_data = $this->vd->parseData($tag, 2);
        if (!empty($vd_data)) {
            $vd_to_write = json_encode($vd_data);
            $this->vd->setContent($db_tag_id, $vd_to_write);
        }
        $nw_data = $this->nw->parseData($tag, 2);
        if (!empty($nw_data)) {
            $nw_to_write = json_encode($nw_data);
            $this->nw->setContent($db_tag_id, $nw_to_write);
        }   
    }

    private function _checkTagByWiki($tag){
        $ch = curl_init();
        $data = array(
            'action' => 'query',
            'list' => 'allpages',
            'format' => 'json',
            'apprefix' => $tag,
            'apimit' => 2
        );
        $base_path = 'http://en.wikipedia.org/w/api.php?';
        $url = $base_path.http_build_query($data);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.91 Safari/534.30');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.google.com/'); 
        $result = curl_exec($ch);
        curl_close($ch);
        $resp = json_decode($result, true);
        if(!empty($resp["query"]['allpages']))
            if(count($resp["query"]['allpages'])!= 0)
                return true;
        return false;
    }
    
    public function freeRec($tags){
        foreach($tags as $tag){
            $this->_insertContent($tag['name'], $tag['id']);
            $users = $this->rM->getUsersByTag($tag['id']);
            if(!empty($users)){
                $this->addRec2User($tag['id'], $users, 0, 1);
            }
        }
    }
}

?>
