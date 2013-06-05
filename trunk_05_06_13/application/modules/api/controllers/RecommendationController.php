<?php
/**
 *  Sample Logout Resource
 */
class Api_RecommendationController extends REST_Controller
{
    
    protected $rM;  
    
    /**
     * The index action handles index/list requests; it should respond with a
     * list of the requested resources.
     */
    public function init(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->rM = new Models_ApiRecommendationManager();
    }
    public function indexAction()
    {
        $this->view->message = 'indexAction has been called.';
        $this->_response->ok();
    }

    /**
     * The head action handles HEAD requests; it should respond with an
     * identical response to the one that would correspond to a GET request,
     * but without the response body.
     */
    public function headAction()
    {
        $this->view->message = 'headAction has been called';
        $this->_response->ok();
    }

    /**
     * The get action handles GET requests and receives an 'id' parameter; it
     * should respond with the server resource state of the resource identified
     * by the 'id' value.
     */
    public function getAction()
    {
        $id = $this->_getParam('id', 0);

        $this->view->id = $id;
        $this->view->message = sprintf('Resource #%s', $id);
        $this->_response->ok();
    }

    /**
     * The post action handles POST requests; it should accept and digest a
     * POSTed resource representation and persist the resource state.
     */
    public function postAction()
    {       
        $response = array();     
        $data = $this->_getAllParams();
        if(!empty($data['request_token']) && !empty($data['limit']) && isset($data['offset']) && !empty($data['source_type']) && !empty($data['version'])){
            $user = $this->rM->checkUserByToken($data['request_token']);
            if(empty($user['id_tbl_user']))
                $response['error'] = '401 - Unauthorized(Incorrect value of "request_token")';
            else{
                if($data['source_type'] == 1){
                    if(!empty($data['user_experience_id']))
                        $response['error'] = '400 - Bad request (Unused parameter "user_experience_id")';
                    else{
                        if(!empty($data['type'])) 
                            $response['result'] = $this->rM->getRecommendation($data['request_token'], $data['limit'], $data['offset'], $data['source_type'], FALSE, $data['type']);
                        else
                            $response['result'] = $this->rM->getRecommendation($data['request_token'], $data['limit'], $data['offset'], $data['source_type']);
                    }
                }else if($data['source_type'] == 2){
                    if(!empty($data['user_experience_id'])){
                        if(!empty($data['type']))
                            $response['result'] = $this->rM->getRecommendation($data['request_token'], $data['limit'], $data['offset'], $data['source_type'], $data['user_experience_id'], $data['type']);
                        else
                            $response['result'] = $this->rM->getRecommendation($data['request_token'], $data['limit'], $data['offset'], $data['source_type'], $data['user_experience_id']);
                    }else $response['error'] = '400 - Bad request (Empty value of "user_experience_id")';
                }else $response['error'] = '400 - Bad request (Incorrect value of "source_type")';
            }
           
   
        }else $response['error'] = '400 - Bad request';
        
        
        $this->view->response = json_encode($response);
        $this->_response->ok();
    }

    /**
     * The put action handles PUT requests and receives an 'id' parameter; it
     * should update the server resource state of the resource identified by
     * the 'id' value.
     */
    public function putAction()
    {
        $id = $this->_getParam('id', 0);

        $this->view->id = $id;
        $this->view->params = $this->_request->getParams();
        $this->view->message = sprintf('Resource #%s Updated', $id);
        $this->_response->ok();
    }

    /**
     * The delete action handles DELETE requests and receives an 'token'
     * parameter; it should update the server resource state of the resource
     * identified by the 'token' value.
     */
    public function deleteAction()
    {           
        
    }
}
