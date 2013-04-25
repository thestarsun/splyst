<?php

class Admin_IndexController extends Zend_Controller_Action
{

    protected $mM;
    protected $mail;
    
    public function init()
    {
        $this->mM = new Models_MailManager();
        $this->_helper->layout->setLayout('clear');
        $this->mail = new App_Emailsender();
    }

    public function indexAction()
    {
        if($this->getRequest()->isPost()){
            $par = $this->getAllParams();
            if($par['login'] == 'admin'&& $par['pass']=="SPLyst1010"){
                $_SESSION['admin_login'] = 1;
                $this->_helper->redirector->gotoUrl('/admin/index/showemail/');
            }else
                $this->view->message = "Not correct login or password";
        }
    }
    public function showemailAction(){
        if(!empty($_SESSION['admin_login'])){
            if($this->getRequest()->isPost()){
                $post_mail = $this->getParam("send");
                if(!empty($post_mail)){
                    echo 'We sent mail to:</br>'; 
                    foreach ($this->mM->getEmails() as $mail){
                        if(array_key_exists($mail['id'], $post_mail)){
                            echo $mail['email'].'</br>';
                            $this->sendemails($mail['email']);
                            $this->mM->setSendMail($mail['id']);
                        }
                    }
                }
            }
            $this->view->emails = $this->mM->getEmails();
        }else{
            $this->_helper->redirector->gotoUrl('/admin/index/index/');
        }
    }
    
    private function sendemails($email){
        $message = 'Your are invited to site http://splyst.com';
        $subject = 'Invite to splyst';
        $this->mail->send($email, $message, $subject);
    }

    public function deleteemailAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $id = $this->getParam('id');
        $this->mM->deleteEmail($id);
    }
    
    public function accessAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $id = $this->getParam('id');
        $val = $this->getParam('val');
        $this->mM->updateEmail($id, $val);
        
    }
    
    public function newemailAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $val = $this->getParam('val');
        if (preg_match('/^[\._A-Za-z0-9-]+@[A-Za-z0-9-]+\.[a-z]{2,3}\.?[a-z]*$/', $val)){
            $email = $this->mM->getEmail($val);
            if(!empty($email))
                $this->_helper->json(Array('error' =>'This email already exist!'));
            else
                $this->mM->addEmail($val);
        }else
            $this->_helper->json(Array('error' =>'Not valid mail adress!'));
        
    }
    
    public function logoutAction(){
        unset($_SESSION['admin_login']);
         $this->_helper->redirector->gotoUrl('/admin/index/index/');
    }
    
    public function showlogAction(){
        $path = 'http://'.$_SERVER['SERVER_NAME'].'/delete_tags.log';
        $file = file_get_contents($path);
        $file_content = explode("\n", $file);
        if(!empty($file_content)){
            foreach ($file_content as &$line){
                if(!empty($line)){
                    $temp_line = substr($line, 36);
                    $temp_time = substr($line, 0, 25);
                    $line = json_decode($temp_line, true);
                    $line['time'] = date('m/d/Y h:i A', strtotime($temp_time));
                }
            }
        }else
            $file_content = array();
        $this->view->logs = $file_content;
    }
}