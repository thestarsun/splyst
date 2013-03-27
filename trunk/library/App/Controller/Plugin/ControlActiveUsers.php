<?php

class App_Controller_Plugin_ControlActiveUsers extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        if($request->getActionName() !='showemail'){
            if(!empty($_SESSION['email'])){
                $mM = new Models_MailManager();
                $dt = $mM->getUserByEmail($_SESSION['email']);
                if(empty($dt)){
                    unset($_SESSION['user_id']);
                    unset($_SESSION['user_name']);
                    unset($_SESSION['user_pic']);
                    unset($_SESSION['token']);
                    unset($_SESSION['fb_user_id']);
                    unset($_SESSION['email']);
                    $request->setControllerName('index')->setActionName('index')->setModuleName('default');
                }
            }
        }
        if(!empty($_SESSION['user_id'])) {
            $qM = new Models_QuestManager();
            $qM->setLastUserActivity($_SESSION['user_id']);
            $notifications = $qM->getUserNotifications($_SESSION['user_id']);
//            $userNotifications = json_decode('[{"thumbnail":"https:\/\/fbcdn-profile-a.akamaihd.net\/hprofile-ak-snc6\/c24.33.296.296\/s200x200\/190764_1923014522041_7145508_n.jpg","user_id":"60","user_name":"Nickolas Kapravchuk","title":"HP \u0423\u043a\u0440\u0430\u0457\u043d\u0430","url":"http:\/\/sphotos-b.xx.fbcdn.net\/hphotos-snc6\/c26.0.403.403\/p403x403\/285216_549123208440417_1566938986_n.jpg"},{"thumbnail":"https:\/\/fbcdn-profile-a.akamaihd.net\/hprofile-ak-snc6\/c24.33.296.296\/s200x200\/190764_1923014522041_7145508_n.jpg","user_id":"60","user_name":"Nickolas Kapravchuk","title":"Cleveland casino, Caesars loyalty program up the ante","url":"http:\/\/www.cleveland.com\/metro\/index.ssf\/2013\/02\/cleveland_casino_caesars_loyal.html"}]',true);
//            print_r($notifications);die();
            $userNotifications = array("new_messages"=>0, "messages"=>array());
            if(!empty($notifications)){
                foreach ($notifications as $notification) {
                    $userNotifications['new_messages'] = $notification['new_message'] + $userNotifications['new_messages'];
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
            Zend_Registry::set('userNotifications',$userNotifications);
            $cache = Zend_Registry::get('cache');
           if(!$cache->test('colaboration_' . $_SESSION['user_id'])||
               !$cache->test('noncolaboration_' . $_SESSION['user_id'])||
               !$cache->test('colaboration_count_' . $_SESSION['user_id'])||
               !$cache->test('noncolaboration_count_' . $_SESSION['user_id'])){
               exec("/usr/bin/php -f /var/www/splyst/trunk/cron/createfriedscache.php ".$_SESSION['user_id']);
            }
        }
    }
}

