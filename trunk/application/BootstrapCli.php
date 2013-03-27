<?php

class BootstrapCli extends Zend_Application_Bootstrap_Bootstrap {

    protected $_getopt = null;
    protected $_getOptRules = array(
        'environment|e-s' => 'Application environment switch (optional)',
        'module|m-s' => 'Module name (optional)',
        'controller|c=s' => 'Controller name (required)',
        'action|a=s' => 'Action name (required)'
    );

    public function __construct($application) {
        parent::__construct($application);
        Zend_Session::start(true);
    }
    protected function _initPartialsPath()
    {
        $options = $this->getOption('partials');
        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->addScriptPath($options['path']);
        return $view;
    }
    protected function _initConfig()
    {
        $options = $this->getOption('fb');
        Zend_Registry::set('fb_appID', $options['appID']);
        Zend_Registry::set('fb_appSecret',$options['appSecret']);
//        $config = new Zend_Config($this->getOptions());
//        Zend_Registry::set('config', $config);
//        return $config;
    }
    protected function _initHelpers(){
        Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH.'/../library/Helpers/Action', 'Helpers_Action');
    }
    
    protected function _initAutoload()
    {
         $moduleLoader = new Zend_Application_Module_Autoloader(array(
             'basePath'  =>APPLICATION_PATH.'/',
             'namespace' => '',
         ));
         return $moduleLoader;
     }
     
     protected function _initMemcache(){
        $oBackend = new Zend_Cache_Backend_Memcached(
            array(
                'servers' => array( array(
                    'host' => '127.0.0.1',
                    'port' => '11211'
                ) ),
                'compression' => true
        ) );

        // настраиваем логер кэширования
        $oCacheLog =  new Zend_Log();
        $oCacheLog->addWriter( new Zend_Log_Writer_Stream( 'file:///var/pr-memcache.log' ) );


        // настраиваем стратегию frontend кэширования
        $oFrontend = new Zend_Cache_Core(
            array(
                'caching' => true,
                'cache_id_prefix' => 'splyst',
                'logging' => true,
                'logger'  => $oCacheLog,
                'write_control' => true,
                'automatic_serialization' => true,
                'ignore_user_abort' => true
            ) );

        // составляем объект кэширования
        $oCache = Zend_Cache::factory( $oFrontend, $oBackend );
        Zend_Registry::set('cache', $oCache);
        
     }

    protected function _initCliFrontController() {
        $this->bootstrap('FrontController');
        $front = $this->getResource('FrontController');
        $getopt = new Zend_Console_Getopt($this->getOptionRules(),
                        $this->_isolateMvcArgs());
        $request = new ZFExt_Controller_Request_Cli($getopt);
        $front->setResponse(new Zend_Controller_Response_Cli)
                ->setRequest($request)
                ->setRouter(new ZFExt_Controller_Router_Cli)
                ->setParam('noViewRenderer', true);
    }  // CLI specific methods for option management

    public function setGetOpt(Zend_Console_Getopt $getopt) {
        $this->_getopt = $getopt;
    }

    public function getGetOpt() {
        if (is_null($this->_getopt)) {
            $this->_getopt = new Zend_Console_Getopt($this->getOptionRules());
        }
        return $this->_getopt;
    }

    public function addOptionRules(array $rules) {
        $this->_getOptRules = $this->_getOptRules + $rules;
    }

    public function getOptionRules() {
        return $this->_getOptRules;
    }

    // get MVC related args only (allows later uses of Getopt class
    // to be configured for cli arguments)
    protected function _isolateMvcArgs() {
        $options = array($_SERVER['argv'][0]);
        foreach ($_SERVER['argv'] as $key => $value) {
            if (in_array($value, array(
                        '--action', '-a', '--controller', '-c', '--module', '-m', '--environment', '-e'
                    ))) {
                $options[] = $value;
                $options[] = $_SERVER['argv'][$key + 1];
            }
        }
        return $options;
    }


    
//

}