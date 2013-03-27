<?php

class App_Browser {

    private $trans = array( "а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d","е"=>"e", "ё"=>"yo","ж"=>"j","з"=>"z","и"=>"i","й"=>"i","к"=>"k","л"=>"l", "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r","с"=>"s","т"=>"t", "у"=>"y","ф"=>"f","х"=>"h","ц"=>"c","ч"=>"ch", "ш"=>"sh","щ"=>"sh","ы"=>"i","э"=>"e","ю"=>"u","я"=>"ya",
                            "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G","Д"=>"D","Е"=>"E", "Ё"=>"Yo","Ж"=>"J","З"=>"Z","И"=>"I","Й"=>"I","К"=>"K", "Л"=>"L","М"=>"M","Н"=>"N","О"=>"O","П"=>"P", "Р"=>"R","С"=>"S","Т"=>"T","У"=>"Y","Ф"=>"F", "Х"=>"H","Ц"=>"C","Ч"=>"Ch","Ш"=>"Sh","Щ"=>"Sh", "Ы"=>"I","Э"=>"E","Ю"=>"U","Я"=>"Ya",
                            "ь"=>"","Ь"=>"","ъ"=>"","Ъ"=>"");
    private $user_agents = array(
        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.91 Safari/534.30',
        'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.2.17) Gecko/20110420 Firefox/3.6.17',
        'Opera/9.80 (Windows NT 5.1; U; Distribution 00; ru) Presto/2.8.131 Version/11.11',
        'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; GTB7.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729)',
        'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/534.24 (KHTML, like Gecko) Chrome/11.0.696.68 Safari/534.24',
        'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.10) Gecko/20101005 Fedora/3.6.10-1.fc14 Firefox/3.6.10'
    );
    
    private $refer = array('http://www.google.com.ua/url?sa=t&source=web&cd=1&ved=0CBcQFjAA&url=http%3A%2F%2Fwww.autosite.com.ua%2F&rct=j&q=http%3A%2F%2Fwww.autosite.com.ua%2F&ei=Nw0bTtmoGsuc-wbIzOXXBw&usg=AFQjCNEkbVmNzR9d08hH2yNwQPCIRZBJ7w&sig2=042FS4L7lJfw7-jrRh1p_w',
        'http://go.mail.ru/search?mailru=1&drch=e&mg=1&q=http%3A%2F%2Fwww.autosite.com.ua%2F&rch=e','http://nova.rambler.ru/search?btnG=%D0%9D%D0%B0%D0%B9%D1%82%D0%B8%21&amp;query=http%3A%2F%2Fwww.autosite.com.ua%2F',
        'http://www.autosite.com.ua/','http://www.autosite.com.ua/used_results.html?Type=1&Region=0&Make=0&Model=&YearFrom=0&YearTo=0&PriceFrom=&PriceTo=','http://www.bing.com/search?q=http%3A%2F%2Fwww.autosite.com.ua%2F&go=&qs=bs&form=QBLH');
    
    /**
     * Page charset
     * 
     * @var string 
     */
    private $charset = "UTF-8";
    private $imagesTypes = array('image/gif', 'image/png', 'image/jpeg');
    /**
     * Singleton instance
     *
     * @var Analytics_Browser
     */
    protected static $_instance = null;

    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
    protected function __construct() {
        
    }

    /**
     * Singleton pattern implementation makes "clone" unavailable
     *
     * @return void
     */
    protected function __clone() {
        
    }

    /**
     * Returns an instance of Analytics_Browser
     *
     * Singleton pattern implementation
     *
     * @return Analytics_Browser Provides a fluent interface
     */
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * @return Analytics_Browser Provides a fluent interface
     */
    public function setCharset($charset) {
        $this->charset = $charset;
        return $this;
    }

    public function makeRequest($url, $useCache = false) {
        if ($useCache) {
            $_manager = new Managers\Cache();
            if ($cacheContent = $_manager->getOne($url)) {
                return $cacheContent;
            }
        }
        
        $content = $this->_request($url);

        if ($useCache) {
            $_manager->save($url, $content);
        }
        return $content;
    }

    private function getUserAgent() {
        $key = array_rand($this->user_agents);
        return $this->user_agents[$key];
    }

    private function getRefer() {
        $key = array_rand($this->refer);
        return $this->refer[$key];
    }

    public function loadFile($url) {
        return $this->_request($url);
    }

    private function _request($url) {
        
        
        $url=strtr($url, $this->trans);
//        try{
//            $client = new Zend_Http_Client($url);
//        }catch (Exception $exc){
//            $exc->getMessage();
//            var_dump($exc->getMessage());
//            die('^_^');
//            return false;
//        }
//       
//        $client->setConfig(array(
//            'useragent' => $this->getUserAgent(),
//            'timeout' => 30,
//        ));
//        
//        $client->setHeaders(array(
//            'ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
//        ));
//        
//        
//        if($client->request()->getStatus()==200){  
//            
//            $header = $client->request()->getHeader('Content-type');
//            if (!in_array($header, $this->imagesTypes)) {                                   
//                if ($this->charset != 'UTF-8') {
//                    return iconv($this->charset, 'UTF-8', $client->request()->getBody());
//                }
//            }    
//            
//            return $client->request()->getBody();
//        
//        
//            
//        }
//        else {
//            $proxy = "127.0.0.1:9050";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//            curl_setopt($ch, CURLOPT_PROXY, $proxy);
//            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            curl_setopt($ch, CURLOPT_USERAGENT, $this->getUserAgent());
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_REFERER, $this->getRefer()); 
            $result = curl_exec($ch);
            curl_close($ch);
            if($result == false)
                return false;
            if ($this->charset != 'UTF-8') {
                    return iconv($this->charset, 'UTF-8', $result);
                }
            else return $result;
            // для того щоб змінити IP
//            $this->tor_new_identity();
//            }
        
             
    }


//    public function tor_new_identity($tor_ip='127.0.0.1', $control_port='9050', $auth_code='') {
//        $fp = fsockopen($tor_ip, $control_port, $errno, $errstr, 30);
//        if (!$fp)
//            return false; //can't connect to the control port
//
//        fputs($fp, "AUTHENTICATE $auth_code\r\n");
//        $response = fread($fp, 1024);
//        list($code, $text) = explode(' ', $response, 2);
//        if ($code != '250')
//            return false; //authentication failed
//        
//        //send the request to for new identity
//        fputs($fp, "signal NEWNYM\r\n");
//        $response = fread($fp, 1024);
//        list($code, $text) = explode(' ', $response, 2);
//        if ($code != '250')
//            return false; //signal failed
//
//        fclose($fp);
//        return true;
//    
//
//    }
}