<?php

class App_Controller_Plugin_ControlActiveUsers extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    { 
        if($request->getActionName() !='showemail'){
            if(($request->getControllerName() == 'recommendation') && ($request->getActionName() == 'index') && $request->getParam('link_id')){
                if(!empty($_SESSION['user_id']))
                    $request->setModuleName('default')->setControllerName('recommendation')->setActionName('index')->setParam('link_id', $request->getParam('link_id'));
                else
                    $request->setModuleName('default')->setControllerName('recommendation')->setActionName('indexguest')->setParam('link_id', $request->getParam('link_id'));
            }else{
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
                        $request->setControllerName('index')->setActionName('invite')->setModuleName('default');
                    }
                }
            }
        }
        if(!empty($_SESSION['user_id'])) {
            $qM = new Models_QuestManager();
            $qM->setLastUserActivity($_SESSION['user_id']);
            $temp_array = array();
            $notifications = $qM->getUserNotifications($_SESSION['user_id']);
//            $userNotifications = json_decode('[{"thumbnail":"https:\/\/fbcdn-profile-a.akamaihd.net\/hprofile-ak-snc6\/c24.33.296.296\/s200x200\/190764_1923014522041_7145508_n.jpg","user_id":"60","user_name":"Nickolas Kapravchuk","title":"HP \u0423\u043a\u0440\u0430\u0457\u043d\u0430","url":"http:\/\/sphotos-b.xx.fbcdn.net\/hphotos-snc6\/c26.0.403.403\/p403x403\/285216_549123208440417_1566938986_n.jpg"},{"thumbnail":"https:\/\/fbcdn-profile-a.akamaihd.net\/hprofile-ak-snc6\/c24.33.296.296\/s200x200\/190764_1923014522041_7145508_n.jpg","user_id":"60","user_name":"Nickolas Kapravchuk","title":"Cleveland casino, Caesars loyalty program up the ante","url":"http:\/\/www.cleveland.com\/metro\/index.ssf\/2013\/02\/cleveland_casino_caesars_loyal.html"}]',true);
//            print_r($notifications);die();
            $userNotifications = array("new_messages"=>0, "messages"=>array());
            if(!empty($notifications)){
                foreach ($notifications as $notification_item) {
                    $userNotifications['new_messages'] = $notification_item['new_message'] + $userNotifications['new_messages'];
                    $notification = json_decode($notification_item['data'], true);
                    if(!empty($notification)){
                        if($notification_item['type_id'] == 4){
                            foreach($notification as $key=>$item){
                                if(!array_key_exists($item['user_id'], $temp_array)) {
                                    $temp_array[$item['user_id']] = $item;
                                }else{
                                    if(!empty($userNotifications['new_messages']))
                                        $userNotifications['new_messages'] = $userNotifications['new_messages'] - 1;
                                }
                            }
                            $notification = $temp_array;
                        }
                        $userNotifications['messages'] = array_merge($userNotifications['messages'], $notification);
                    }
                }
                usort($userNotifications['messages'], function($first, $second) {
                    if(!empty($first['time']) && !empty($second['time'])){
                        if($first['time'] < $second['time']) return 1;
                        else return -1;
                    }
                });
            }
            Zend_Registry::set('userNotifications',$userNotifications);
            $cache = Zend_Registry::get('cache');
            if(!$cache->test('colaboration_' . $_SESSION['user_id'])||
                !$cache->test('noncolaboration_' . $_SESSION['user_id'])||
                !$cache->test('colaboration_count_' . $_SESSION['user_id'])||
                !$cache->test('noncolaboration_count_' . $_SESSION['user_id'])){
                exec("/usr/bin/php -f /var/www/splyst/cron/createfriedscache.php ".$_SESSION['user_id']);
            }
        }
    }
}

