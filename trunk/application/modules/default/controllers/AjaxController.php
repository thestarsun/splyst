<?php

class Default_AjaxController extends Zend_Controller_Action {

    protected $qM;
    protected $eM;
    protected $rM;
    protected $fb;
//    protected $mail;
    protected $lM;
    protected $alchemy;
    protected $cache;

    public function init() {
        /* @var $this Models_QuestManager */
        $this->qM = new Models_QuestManager();
        $this->eM = new Models_ExperienceManager();
        $this->lM = new Models_LinkManager();
        $this->rM = new Models_RecommendationManager();
        $this->fb = new App_Fb_Token();
        $this->alchemy = new App_Alchemy();
        $this->cache = Zend_Registry::get('cache');
//        $this->mail = new App_Notifier_EmailSender();
        /* Initialize action controller here */
    }

    public function indexAction() {

    }

    public function experienceAction($data = array()) {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $title = $this->getParam('title');
        $id = $this->eM->checkExistExpWithSuchTitle($title);
        $new_exp = false;
        if ($id) {
            if ($this->eM->checkAlreadyExistExpSuchUser($id, $_SESSION['user_id']))
                $id = $this->eM->saveExperience($_SESSION['user_id'], $id);
        }else {
            $id = $this->eM->saveNewExperience($_SESSION['user_id'], $title);
        }
        $cache = Zend_Registry::get('cache');
        $cache->remove('exp_' . $_SESSION['user_id']);
        if (!empty($data)) {
            $data['id'] = $id;
            $new_exp = $this->savelinkAction($data);
        }
        $this->_helper->json(array('success' => 'true', 'new_exp' => $new_exp));
    }

    public function scanimgAction() {
        $this->_helper->layout()->disableLayout();
        $url = $this->getParam('url');
        $html = App_Browser::getInstance()->makeRequest($url);
        //get page title
        $title = $this->get_page_title($html);
        if(!$title) $title = '';
        if ($html == false) {
            $this->getResponse()->setHttpResponseCode(400);
            $this->_helper->viewRenderer->setNoRender(true);
        } else {
            $matches = $this->get_images($html);
            $dir = './tmp/' . $_SESSION['user_id'] . '/';
            $this->view->dir = '/tmp/' . $_SESSION['user_id'] . '/';
            if (!is_dir($dir)) {
                $oldmask = umask(0);
                mkdir($dir, 0777);
                umask($oldmask);
            }
            $i = 0;
            foreach ($matches as $key => $element) {
                if ($element[0] == '/' && $element[1] == '/')
                    $element = substr($element, 2);
                if ($i < 10) {
                    $picture_file = './tmp/' . $_SESSION['user_id'] . '/' . $key . 'picture.gif';
                    $picture = App_Browser::getInstance()->loadFile($element);
                    if ($picture == false) {
                        preg_match('@http://.*?/@', $url, $matches);
                        if (!empty($matches[0]))
                            $picture = App_Browser::getInstance()->loadFile($matches[0] . $element);
                    }
                    if ($picture == false) {
                        preg_match('@.*?/@', $url, $matches);
                        if (!empty($matches[0]))
                            $picture = App_Browser::getInstance()->loadFile($matches[0] . $element);
                    }
                    if ($picture != false) {
                        file_put_contents($picture_file, $picture);
                        $size = getimagesize($picture_file);
                        if ($size) {
                            if ($size[0] > $size[1]) {
                                $l_size = $size[0];
                                $s_size = $size[1];
                            } else {
                                $l_size = $size[1];
                                $s_size = $size[0];
                            }
                            if ($s_size < 170 || $l_size / $s_size > 3)
                                unlink($picture_file);
                            else
                                $i++;
                        }else {
                            unlink($picture_file);
                        }
                    }
                }else
                    break;
            }

            if (is_dir($dir)) {
                if ($dh = opendir($dir)) {
                    $img_name = array();
                    while (($file = readdir($dh)) !== false) {
                        if ($file != "." && $file != ".." && $file != ".svn")
                            $img_name[] = $file;
                    }
                    closedir($dh);
                }
            }

            //create screenshot
            $screenshot = $this->create_screenshot($url);
            if ($screenshot)
                $this->view->screenshot = $screenshot;

            $this->view->img_arr = $img_name;
            $this->view->title = $title;
        }
    }

    function get_images($html) {
        $images = array();
        preg_match_all('/(img|src)\=(\"|\')[^\"\'\>]+/i', $html, $media);
        unset($html);
        $html = preg_replace('/(img|src)(\"|\'|\=\"|\=\')(.*)/i', "$3", $media[0]);
        foreach ($html as $url) {
            $info = pathinfo($url);
            if (isset($info['extension'])) {
                if (($info['extension'] == 'jpg') || ($info['extension'] == 'jpeg') || ($info['extension'] == 'gif') || ($info['extension'] == 'png'))
                    array_push($images, $url);
            }
        }
        return $images;
    }

    function get_page_title($html){
        preg_match("/<title>([^>]*)<\/title>/si",$html, $title);
        if(!empty($title[1])) return $title[1];
        else return false;
    }

    public function updatepassAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $email = $this->_getParam('email');
        $success = false;
        if (!empty($email)) {
            $user = $this->qM->getUserByEmail($email);
            if (!empty($user)) {
                $arr_letter = str_split('ABCDEFGHIJKLMNOPVXWQqwertyuiopasdfghjklzxcvbnm'); // get all the characters into an array
                $arr_number = str_split('1234567890');
                $arr_character = str_split('!@#$%^&*?_~-()');
                shuffle($arr_letter); // randomize the array
                shuffle($arr_number);
                shuffle($arr_character);
                $merge_pass = array_merge($arr_letter, $arr_number, $arr_character);
                shuffle($merge_pass);
                $temp_pass = array_slice($merge_pass, 0, 13); // get the first six (random) characters out
                $pass = implode('', $temp_pass);
                $this->qM->updatePass($user['id_tbl_user'], md5($pass));
                $message = "You new password  " . $pass;
                $subject = "Change password";
                $send_email = $this->mail->send($email, $message, $subject);

                if ($send_email) {
                    $success = true;
                    $result = "Password has been sent to the specified email.";
                } else
                    $result = "An error occurred, please try again.";
            }else
                $result = "Your email was not found in the database.";
        }else
            $result = "You have not entered email address.";

        $this->_helper->json(array("success" => $success, "result" => $result));
    }

    public function ajaxloginAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $success = false;
        $error = true;
        if ($this->getRequest()->isPost()) {
            $params = $this->getAllParams();
            if (empty($params['email']) || empty($params['password'])) {
                $error = 'The fields is required.';
            } else {
                $params['password'] = md5($params['password']);
                $data = $this->qM->loginUser($params);
                if (!empty($data)) {
                    $_SESSION['user_id'] = $data['id_tbl_user'];
                    $_SESSION['user_name'] = $data['name'];
                    $_SESSION['user_pic'] = $data['pic'];
                    $_SESSION['token'] = $data['fb_access_token'];
                    $_SESSION['fb_user_id'] = $data['fb_user_id'];
                    $_SESSION['email'] = $data['email'];
                    $success = true;
                }else
                    $error = 'Login or password is not correct.';
            }

            $this->_helper->json(array("success" => $success, "error" => $error));
        }
    }

    public function ajaxregisterAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $success = false;
        if ($this->getRequest()->isPost()) {
            $params = $this->getAllParams();
            $temp_data = explode("&", $params['data']);
            $data = array();
            foreach ($temp_data as $item) {
                $temp_item = explode("=", $item);
                $data[$temp_item[0]] = $temp_item[1];
            }
            $pass = trim($data['password']);
            $pass_control = trim($data['confirm_password']);
            $error_count = 0;
            $error_text['password'] = array();
            if (empty($pass) || empty($pass_control)) {
                $error_text['password'][] = "The field is required";
                $error_count++;
            }
            if (strcmp($pass, $pass_control) == !0) {
                $error_text['password'][] = "The passwords entered are not the same.";
                $error_count++;
            }
            if (strlen($pass) < 6) {
                $error_text['password'][] = "Password length at least 6 characters.";
                $error_count++;
            }
            if (!(preg_match('@[A-z]+@', $pass) && preg_match('@[0-9]+@', $pass))) {
                $error_text['password'][] = "Password must have an alpha and numeric character and can accept special characters.";
                $error_count++;
            }
            $error = $error_text['password'];
//            if(empty($data['password']) || empty($data['confirm_password'])){
//                $error = 'Please enter a password.';
//            }elseif($data['password'] != $data['confirm_password']){
//                $error = 'Passwords are not equal.';
//            }else{
            if (empty($error_count)) {
                $this->qM->updatePass($data['user_id'], md5($data['password']));
                $success = true;
                $error = false;
                $_SESSION['user_name'] = $params['user_name'];
            }
            try {
//                exec("/usr/bin/php -f /var/www/splyst/trunk/cron/rec4newuser.php ".$_SESSION['user_id']);
                exec("/usr/bin/php -f /var/www/splyst/cron/rec4newuser.php ".$_SESSION['user_id']);
            } catch (Exception $e) {
//                echo $e->getMessage();
            }
            $this->_helper->json(array("success" => $success, "error" => $error));
        }
    }

    public function ajaxexperienceAction() {
        $cache = Zend_Registry::get('cache');
        $this->_helper->layout->disableLayout();
        if (!$cache->test('defexp')) {
            $this->view->experience = $this->eM->getDefaultExperience();
            $cache->save($this->view->experience, 'defexp');
        } else {
            $this->view->experience = $cache->load('defexp');
        }
    }

    public function savelinkAction($params = array()) {
        if (!empty($params)) $data = $params;
        else $data = $this->getAllParams();
        $cache = Zend_Registry::get('cache');
        $front = Zend_Controller_Front::getInstance();
        $bootstrap = $front->getParam("bootstrap");
        $temp_dir = $bootstrap->getOption('temp_dir');
        //$temp_dir = '.'.$bootstrap->getOption('temp_dir');

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $img_name = $_SESSION['user_id'] . time();
        $dir = $temp_dir . $_SESSION['user_id'] . '/';
        if (!is_dir($dir)) {
            $oldmask = umask(0);
            try {
                mkdir($dir, 0777);
            } catch (Exception $e) {
                echo $e->getMessage();
            }

            umask($oldmask);
        }
        if (empty($data['img'])) {
            $data['img'] = $this->create_screenshot($data['url']);
        }
        if (preg_match('@^/tmp/@', $data['img'])) {
            try {
                copy('./' . $data['img'], $temp_dir . $_SESSION['user_id'] . '/' . $img_name);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        } else {
            try {
                copy($data['img'], $temp_dir . $_SESSION['user_id'] . '/' . $img_name);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
        $data['img'] = $img_name;
        if (!empty($data['url'])) {
            $res = $this->alchemy->getConseptTag($data['url']);
            $tag = (!empty($res)) ? $res : $data['title'];
        } elseif (!empty($data['text'])) {
            $res = $this->alchemy->getTagByText(trim($data['text']));
            $tag = (!empty($res)) ? $res : $data['title'];
        } else {
            $res = $this->alchemy->getConseptTag($data['url']);
            $tag = (!empty($res)) ? $res : $data['title'];
        }
        if (empty($tag))
            $tag = $data['title'];
//        if (!$cache->test('tag_'.$tag)){
        $tag_id = $this->lM->getTag($tag);
        if (empty($tag_id))
            $tag_id = $this->lM->setTag($tag);
//                $cache->save($tag_id,'tag_'.$tag, array(), 172800);
//        } else {
//            $tag_id = $cache->load('tag_'.$tag);
//        }
        //Add link tags to user recommendations
        $this->lM->addUserRecTag($_SESSION['user_id'], $tag_id);
        $data['tags'] = $tag_id;
        $new_experience = $this->eM->saveLink($data);
        if(!empty($data['this_link']))
            $new_experience['link'] = true;
        $cache->remove('exp_links_' . $data['id']);
        $cache->remove('exp_' . $_SESSION['user_id']);
        if(!empty($new_experience))
            $this->_helper->json(array('success' => 'true', "new_experience"=>$new_experience));
        else
            $this->_helper->json(array('success' => 'false'));
    }

    public function savesplystbuttonlinkAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $data = $this->getAllParams();
        $data['id'] = $data['experience_name'];
        if ($data['experience_name'] == 'new') {
            $result = $this->experienceAction($data);
        } else {
            $data['this_link'] = true;
            $result = $this->savelinkAction($data);
        }
        if($result['success'])
            $this->_helper->json(array('success' => 'true', 'result'=>$result));
        else
            $this->_helper->json(array('success' => 'false'));
    }

    public function cleandirAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $dir = './tmp/' . $_SESSION['user_id'] . '/';
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != "." && $file != ".." && $file != ".svn")
                        unlink($dir . $file);
                }
                closedir($dh);
            }
        }
    }

    public function ajaxrecommendationAction() {
        //$fl = fopen('cache_test',"a+");
        $this->_helper->layout->disableLayout();
        if(!$this->cache->test('recommendation_ids_'.$_SESSION['user_id'])){
                $recID = $this->rM->getRecID($_SESSION['user_id']);
                shuffle($recID);
                $this->cache->save($recID, 'recommendation_ids_' . $_SESSION['user_id'], array(), 7200);
        }else{
            $recID = $this->cache->load('recommendation_ids_' . $_SESSION['user_id']);
        }
        if(!$this->cache->test('rec_count_'.$_SESSION['user_id'])){
            $this->cache->save(10, 'rec_count_'.$_SESSION['user_id'], array(), 7200);
        }
        $count = $this->cache->load('rec_count_'.$_SESSION['user_id']);
       // fwrite($fl, '4 count '.$count);
        $recommendation = array();
        if(count($recID)> $count){
            $ids10 = array_splice($recID, $count, 10);
            //fwrite($fl, '5 ids10 '.implode(',', $ids10));
            $this->cache->save($count+10, 'rec_count_'.$_SESSION['user_id'], array(), 7200);
            $recommendation = $this->rM->getRecommendationsByIDs(implode(', ', $ids10));
        }
        $this->view->recomendation_array = $recommendation;
       // fclose($fl);
    }

    private function create_screenshot($url) {
        $screenshot_url = 'http://www.uglymongrel.com/takeScreenshot.php';
        $temp_screenshot = App_Browser::getInstance()->makeRequest($screenshot_url . '?url="' . $url . '"');
        $temp_screenshot2 = str_replace(array("(", ");"), "", $temp_screenshot);
        $screenshot_img = json_decode($temp_screenshot2, true);

        return $screenshot_img['fileUrl'];
    }
    
    public function getnotificationsdataAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $result = array();
        $result['success'] = false;
        if(!empty($_SESSION['user_id'])){
            $notifications = $this->qM->getUserNotifications($_SESSION['user_id']);
            $userNotifications = array("new_messages"=>0, "messages"=>array());
            if(!empty($notifications)){
                foreach ($notifications as $notification) {
                    $userNotifications['new_messages'] = $notification['new_message']+ $userNotifications['new_messages'];
                    $notification = json_decode($notification['data'], true);
                    if(!empty($notification)){
                        $userNotifications['messages'] = array_merge($userNotifications['messages'],$notification);
                    }
                }
                usort($userNotifications['messages'], function($first, $second) {
                    if($first['time'] < $second['time']) return 1;
                    else return -1;
                });
            }
            if(!empty($userNotifications)){
                $result['data'] = $userNotifications['messages'];
                $result['success'] = true;
                $result['counter'] = count($userNotifications['messages']);
                $result['new_messages'] = $userNotifications['new_messages'];
            }
        }
        
        $this->_helper->json($result);
    }
    
    public function viewnotificationsAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $result = array();
        $result['success'] = true;
        if(!empty($_SESSION['user_id'])){
            for($typeId=1;$typeId<6;$typeId++){
                $this->qM->updateUserNotification($_SESSION['user_id'], $typeId, "", false);
            }
            $_SESSION['new_notifications'] = 0;
        }

        $this->_helper->json($result);
    }
    
    public function addcommentAction(){
        $this->_helper->layout->disableLayout();
        $data = $this->getAllParams();
        $this->lM->updateLink($data);
        $this->view->comments = $this->lM->getLinkComments($data['link_id']);
        //notification
        //select all users that have this link_id and friend of user
        $users = $this->lM->getUserBylink($data['link_id']);
        $users_with_link = array();
        if(!empty($users)){
            foreach ($users as $user){
                $users_with_link[] = $user['user_id'];
            }
        }
        $colaboratorsArray =  $this->cache->load('colaboration_' . $_SESSION['user_id']);
        $collobSlystID = array();
        if(!empty($colaboratorsArray)){
            foreach ($colaboratorsArray as $col){
                $collobSlystID[] = $this->qM->getUserIDByFBID($col['id']); 
            }
        }
        $user_for_notif = array_intersect($users_with_link, $collobSlystID);
        if(!empty($user_for_notif)){
            $link = $this->lM->getLinkByID($data['link_id']);
            $notificationData = array();
            $notificationData['thumbnail'] = $_SESSION['user_pic'];
            $notificationData['user_id'] = $_SESSION['user_id'];
            $notificationData['user_name'] = $_SESSION['user_name'];
            $notificationData['title'] = $link['title'];
            $notificationData['url'] = $link['url'];
            $notificationData['time'] = time();
            foreach ($user_for_notif as $usr_for_notif){
                $exp_id = $this->eM->getExperienceByLink($usr_for_notif, $data['link_id']);
                $notificationData['link_id'] = '/default/dashboard/experience?id='.$exp_id[0]['user_exp_id'].'&link='.$data['link_id'];
                $this->qM->updateUserNotification($usr_for_notif, 2, $notificationData, true);
            }
        }
        //add notific to each user
        $this->view->fb_fr_arr = $this->getfbfr();
        $this->renderScript('ajax/getcomments.phtml');
    }
    
    public function getcommentsAction(){
        $this->_helper->layout->disableLayout();
        $data = $this->getAllParams();
        $this->view->comments = $this->lM->getLinkComments($data['link_id']);
        $this->view->fb_fr_arr = $this->getfbfr();
    }
    
    public function getfbfr(){
        $this->cache->clean();
        if(!$this->cache->test('js_ar_fr_'.$_SESSION['user_id'])){
            $friends = $this->fb->getFriendsForShare($_SESSION['user_id']);
            if(!empty($friends)){
                $js_ar_fr = [$_SESSION['user_name'] => $_SESSION['fb_user_id']];
                foreach($friends as $fr){
                    $js_ar_fr[$fr['name']] = $fr['id']; 
                }
                $this->cache->save($js_ar_fr, 'js_ar_fr_'.$_SESSION['user_id'], array(), 7200);
            }
        }else
        $js_ar_fr = $this->cache->load('js_ar_fr_'.$_SESSION['user_id']);
        return $js_ar_fr;
    }

    public function inviteemailAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $data = $this->getAllParams();
        $result = array();
        $result['success'] = false;
        $result['exist'] = false;
        if(!empty($data['email'])){
            $check_email = $this->qM->check_inv_email($data['email']);
            if(!$check_email){
                $save_email = $this->qM->save_inv_email($data['email']);
                if($save_email) $result['success'] = true;
            }else
                $result['exist'] = true;
        }

        $this->_helper->json($result);
    }
    
    public function deletebookmarkAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $data = $this->getAllParams();
        $this->cache->remove('exp_links_' . $data["id_book"]);
        $this->cache->remove('exp_' . $_SESSION['user_id']);
        if(!empty($data["id_book"])){
            $this->lM->deleteBookmark($_SESSION['user_id'], $data["id_book"]);
            $this->_helper->json(array('success'=>1));
        }elseif (!empty($data["id_exp"])) {
            $this->lM->deleteExperience($data["id_exp"]);
            $this->_helper->json(array('success'=>2));
        }
    }
}

