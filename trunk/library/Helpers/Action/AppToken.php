<?
 class Helpers_Action_AppToken extends Zend_Controller_Action_Helper_Abstract
{
    public function __construct(){
   
    }

    public function direct(){
        $front = \Zend_Controller_Front::getInstance();
        $bootstrap = $front->getParam("bootstrap");
        $options = $bootstrap->getOption('fb'); 
        $data = array('grant_type'=>'fb_exchange_token',
              'client_id'=>$options['appID'],
              'client_secret'=>$options['appSecret'],
              'grant_type'=>'client_credentials');
        $query = $options['basePath'].http_build_query($data);
        $request = file_get_contents($query);
        parse_str($request, $token);
        return $token['access_token'];
        
    }
}