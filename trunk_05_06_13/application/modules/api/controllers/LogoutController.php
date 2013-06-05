<?php
/**
 *  Sample Logout Resource
 */
class Api_LogoutController extends REST_Controller
{
    
    protected $uM;  
    
    /**
     * The index action handles index/list requests; it should respond with a
     * list of the requested resources.
     */
    public function init(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->uM = new Models_ApiUserManager();
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
        $response = array('logout'=>'false');
        $request_token = $this->_getParam('request_token', 0);
        if(!empty($request_token)){
            $result = $this->uM->logout($request_token);
            if($result) $response['logout'] = 'success';
            else $response['error'] = '404 - Not found';
        } else $response['error'] = '400 - Bad request';
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
