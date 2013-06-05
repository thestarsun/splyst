<?php

class Default_AuthController extends Zend_Controller_Action
{

    protected $qM;
    protected $eM;
    protected $fb;
    protected $basePath;
    
    public function init()
    {
        
        $this->qM = new Models_QuestManager();
        $this->eM = new Models_ExperienceManager();
        $this->fb = new App_Fb_Token();
        $front = Zend_Controller_Front::getInstance();
        $bootstrap = $front->getParam("bootstrap");
        $this->basePath = $bootstrap->getOption('basePath');
    }
    public function indexAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }
    
    public function checkregistrationAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        // Get FB userID and short live token
        $request = $this->getAllParams();
        // Check existing user in DB
        $user = $this->qM->getUserById($request['userID']);
        if(empty($user)|| empty($request['userID'])){
            //Save fb user data and redirect to registration
            $token = $this->fb->getLongLiveToken($request['accessToken']);
            $fb_data = array('fb_user_id' => $request['userID'],
                          'fb_access_token'=> $token,
                          'create_access_token' => date('Y-m-d H:i:s'));
            $full_user_info = (array)$this->fb->getInfoMe('', $token);
            $userpic = $this->fb->getProfilePic($token);

            if($full_user_info['gender'] == 'male') $full_user_info['gender'] = 1;
            else $full_user_info['gender'] = 0;

            $user_info = array(
                "name" => $full_user_info['name'],
                "email" => $full_user_info['email'],
                "birthday" => date("Y-m-d", strtotime($full_user_info['birthday'])),
                "gender" => $full_user_info['gender'],
                "pic" => $userpic);
            $data = array_merge($fb_data, $user_info);
            $userID = $this->qM->setUser($data);

            $this->qM->addUserNotification($userID);
            $return_data = array(
                'registration' => 'yes',
                'id' => $userID,
                'name' => $data['name'],
                'email' => $data['email'],
                'birthday' => $data['birthday'],
                'gender' => $data['gender'],
                'fb_user_id' => $data['fb_user_id']
            );
            $_SESSION['user_id'] = $userID;
            $_SESSION['user_name'] = $data['name'];
            $_SESSION['user_pic'] = $userpic;
            $_SESSION['token'] = $token;
            $_SESSION['fb_user_id'] = $request['userID'];
            $_SESSION['email'] = $data['email'];
            $this->_helper->json($return_data);
        }else{
            //Check date of last taking FB user likes, if it old -> check FB token, and updata data
//            if(!$this->_checkExpiresData($user['upload_data_time'])){
//                $freshToken = $this->fb->checkToken($this->fb->getAppToken(), $user['fb_access_token']);
//                if(!$freshToken){
                    $freshToken = $this->fb->getLongLiveToken($request['accessToken']);
//                }
////                $freshData = $this->fb->getInfoMe('', $freshToken);
////                $this->qM->updateData($freshData);
//            }
            $_SESSION['user_id'] = $user['id_tbl_user'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_pic'] = $user['pic'];
            $_SESSION['token'] = $user['fb_access_token'];
            $_SESSION['token'] = $user['fb_access_token'];
            $_SESSION['fb_user_id'] = $user['fb_user_id'];
            $_SESSION['email'] = $user['email'];
            

            $this->_helper->json(array("registration" => "no"));
        }
    }
    
    protected function _checkExpiresData($date){
        if(strtotime($date)< time())
            return false;
        return true;
    }
    
//    public function registrationAction(){
//        $request = $this->getAllParams();
//        $user = $this->qM->getUserBySplystId($request['id']);
//        $userview = (array)$this->fb->getInfoMe('',$user['fb_access_token']);
//        $userview['gender']  = ($userview['gender']== 'male')?'0':'1';
//        $this->view->user = $userview;
//        
//        $error = array('email'=>'', 'password'=>'');
//        $error_count = 0;
//        if ($this->getRequest()->isPost()) {
//            // Field validation
//            $email = $this->_checkEmail($request['email'], $error, $error_count);
//            $pass = $this->_checkPass($request['password'], $request['confirm_password'] ,$error, $error_count);
//            echo "<pre>";
//            var_dump($error_count);
//            echo "</pre>";
//            die('++');
//            if($error_count == 0){
//                $request['password'] = $pass;
//                $userpic = $this->fb->getProfilePic($user['fb_access_token']);
//                $request['userpic'] = $userpic;
//                $this->qM->addProfileData($request);
//                $_SESSION['user_id'] = $request['id'];
//                $_SESSION['user_name'] = $request['user_name'];
//                $_SESSION['user_pic'] = $request['userpic'];
//                $this->_helper->redirector->gotoUrl($this->basePath.'/default/auth/defaultexperience');
//            }else{
//                $this->view->error = $error;
//                $this->view->user['name']= $request['user_name'];
//                $this->view->user['email']= $email;
//                $this->view->user['birthday']= $request['birthday'];
//                $this->view->user['gender']= $request['gender'];
//            }
//        }
//    }
//    private function _checkPass($pass, $pass_control, &$error_text, &$error_count ){
//        $pass = trim($pass);
//        $pass_control = trim($pass_control);
//        if (empty($pass) || empty($pass_control)) {
//            $error_text['password'][] = "The field is required";
//            $error_count++;
//        }
//        if (strcmp($pass, $pass_control) == !0) {
//            $error_text['password'][] = "The passwords entered are not the same";
//            $error_count++;
//        }
//        if (strlen($pass) < 6) {
//            $error_text['password'][] = "Password length at least 6 characters";
//            $error_count++;
//        }
//        if (!(preg_match('@[A-z]+@', $pass) && preg_match('@[0-9]+@', $pass))){
//            $error_text['password'][] = "Password must have an alpha and numeric character and can accept special characters";
//            $error_count++;
//        }
//        if(empty($error_text['password'])) 
//            $pass = md5($_POST['password']);
//        else
//            $error_text['password'] = $this->error_html($error_text['password']);
//        return $pass;
//    }
    
    public function _checkEmail($email, &$error_text, &$error_count){
            $email = trim($email);
            if($this->qM->getUserByEmail($email)!= null){
                $error_text['email'] = "A user with the same email already exists.";
                $error_count++;
            }
            if (empty($email)) {
                $error_text['email'] = "The field is required.";
                $error_count++;
            } else {
                if (preg_match('/^[\._A-Za-z0-9-]+@[A-Za-z0-9-]+\.[a-z]{2,3}\.?[a-z]*$/', $email)) {
                    $domain = explode("@", $email);
                    if (!getmxrr($domain[1], $mxhosts)) {
                        $error_text['email'] = "Send mail to the introduction of address not available.";
                        $error_count++;
                    }
                } else {
                    $error_text['email'] = "The address you entered is not the e-mail address.";
                    $error_count++;
                }
            }
            if(!empty($error_text['email']))
                $error_text['email'] = $this->error_html($error_text['email']);

            return $email;
    }
    
    public function loginAction(){
        $error = array('email'=> '', 'password'=>'');
        $error_count = 0;
        if ($this->getRequest()->isPost()){
            $params = $this->getAllParams();
            if(empty($params['email'])||empty($params['password'])){
                $error['text'] = 'The fields is required.';
                $error_count++;
            }else{
                $params['password'] = md5($params['password']);
                $data = $this->qM->loginUser($params);
                if(!empty($data)){
                    $_SESSION['user_id'] = $data['id_tbl_user'];
                    $_SESSION['user_name'] = $data['name'];
                    $_SESSION['user_pic'] = $data['pic'];
                    $_SESSION['token'] = $data['fb_access_token'];
                    $_SESSION['fb_user_id'] = $data['fb_user_id'];
                    $_SESSION['email'] = $data['email'];
                    $this->_helper->redirector->gotoUrl($this->basePath.'/default/recommendation/index');
                }else{
                    $error['text'] = 'Login or password is not correct.';
                    $error_count++;
                }
            }
            $this->view->error = $error;
        }
    }
    
    public function logoutAction(){
        unset($_SESSION['user_id']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_pic']);
        unset($_SESSION['token']);
        unset($_SESSION['fb_user_id']);
        unset($_SESSION['email']);
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->json(array('success'=>'true'));
    }

    public function error_html($errors){
        $output= '<div class="error_block_wrap show_error">';
            $output .= '<div class="error_block_content">';
            $output .= '<h4>Error:</h4>';
                $output .= '<ul>';
                    if(is_array($errors)):
                        foreach($errors as $error):
                            $output .= '<li class="invalid">'.$error.'</li>';
                        endforeach;
                    else:
                        $output .= '<li class="invalid">'.$errors.'</li>';
                    endif;
                $output .= '</ul>';
            $output .= '</div>';
        $output .= '</div>';

        return $output;
    }
    public function defaultexperienceAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $this->view->experience = $this->eM->getDefaultExperience();
         if ($this->getRequest()->isPost()){
            $params = $this->getAllParams();
            $tmp_arr = array('("'.$_SESSION['user_id'].'", "64")');
            foreach($params['category'] as $cat_id){
                $tmp_arr[] = '("'.$_SESSION['user_id'].'", "'.$cat_id.'")';
            }
            $this->eM->saveUserDefaultExperience(implode(',', $tmp_arr));
             if(!empty($_COOKIE['outsidecontentid']) && !empty($_COOKIE['outsidecontentlink'])){
                $link =  $_COOKIE['outsidecontentlink'];
                $id =  $_COOKIE['outsidecontentid'];
                setcookie ("outsidecontentlink", "", time() - 3600);
                setcookie ("outsidecontentid", "", time() - 3600);
                $this->_helper->redirector->gotoUrl($this->basePath.'/default/fblinks/'.$link.'?id='.$id);
            }else
            $this->_helper->redirector->gotoUrl($this->basePath.'/default/recommendation/index');
         }
        
    }
    
    public function collaboratorsAction(){
        
    }
    
}
    

