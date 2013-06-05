<?php

class Default_MailController extends Zend_Controller_Action{

    protected $mail;


    public function init(){
        $this->mail = new App_Emailsender();
    }

    public function inviteAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $email = $this->getParam('email');
        $message = 'Please go to the site http://splyst.com';
        $subject = 'Invite to splyst';
        $this->mail->send($email, $message, $subject);
    }
    
}