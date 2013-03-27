<?php

class Default_CronController extends Zend_Controller_Action{

    protected $rM;
    protected $qM;
    protected $grab;


    public function init(){
        $this->rM = new Models_RecommendationManager();
        $this->qM = new Models_QuestManager();
        $this->grab = new App_Google_Bildcontent();
    }

    public function deletepbokenimagesAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $images = $this->rM->getImgRecommendations();
        $unset_ids = array();
        foreach($images as $img){
            $cont = $this->remoteFileExists($img['url']);
            if(!$cont){
                echo $img['url'];
                $unset_ids[] = $img['id'];
            }
        }
       $this->rM->unsetbrokenRec(implode(', ', $unset_ids));
    }
    
    
    
    protected function remoteFileExists($url) {
        $curl = curl_init($url);

        //don't fetch the actual page, you only want to check the connection is ok
        curl_setopt($curl, CURLOPT_NOBODY, true);

        //do request
        $result = curl_exec($curl);

        $ret = false;

        //if request did not fail
        if ($result !== false) {
            //if request was ok, check response code
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);  

            if ($statusCode == 200) {
                $ret = true;   
            }
        }

        curl_close($curl);

        return $ret;
    }
    public function updrecevdayAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $users = $this->qM->getActiveUsers(86400);
        if(!empty($users)){
            $tags = $this->rM->getTagsForUsers($users);
            if(!empty($tags)){
                $this->grab->freeRec($tags);
            }
        }
    }
    
    public function updrecevdayoneuserAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if(!empty($_SESSION['user_id'])){
            echo "Start";
            $users = array(array('id_tbl_user'=>$_SESSION['user_id']));
            if(!empty($users)){
                $tags = $this->rM->getTagsForUsers($users);
                if(!empty($tags)){
                    $this->grab->freeRec($tags);
                }
            }
            echo "Stop";
        }else{
            echo "Please login to start script";
        }
    }
}