<?php
/**
 *  Sample Foo Resource
 */
class Api_LoginController extends REST_Controller
{
    /**
     * The index action handles index/list requests; it should respond with a
     * list of the requested resources.
     */
    protected $uM;

    public function init(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->uM = new Models_ApiUserManager();
    }

    public function indexAction(){
        $this->view->message = 'indexAction has been called.';
        $this->_response->ok();
    }

    /**
     * The head action handles HEAD requests; it should respond with an
     * identical response to the one that would correspond to a GET request,
     * but without the response body.
     */
    public function headAction(){
        $this->view->message = 'headAction has been called';
        $this->_response->ok();
    }

    /**
     * The get action handles GET requests and receives an 'id' parameter; it
     * should respond with the server resource state of the resource identified
     * by the 'id' value.
     */
    public function getAction(){
        $this->view->message = 'get';
    }

    /**
     * The post action handles POST requests; it should accept and digest a
     * POSTed resource representation and persist the resource state.
     */
    public function postAction(){
        $all_params = $this->_request->getParams();
        $errors = array();
        if(!empty($all_params)){
            if( !empty($all_params['fb_user_id']) && is_numeric($all_params['fb_user_id']) ){
                $fb_user_id = $all_params['fb_user_id'];
            }else $errors['fb_user_id'] = 'User id not correct.';

            if( !empty($all_params['fb_access_token']) && is_string($all_params['fb_access_token']) ){
                $fb_access_token = $all_params['fb_access_token'];
            }else $errors['fb_access_token'] = 'Access token not correct';

            if( empty($all_params['v']) && $all_params['v'] != '1' )
                $errors['v'] = 'Version API not correct';

            if(empty($errors)){
                $user_data = $this->uM->getUser($fb_user_id, $fb_access_token);
                if($user_data) $this->view->response = json_encode($user_data);
                else $this->view->response = 'User not found';
            }else
                $this->view->error = $errors;

            $this->_response->ok();
        }
    }

    /**
     * The put action handles PUT requests and receives an 'id' parameter; it
     * should update the server resource state of the resource identified by
     * the 'id' value.
     */
    public function putAction(){
        $id = $this->_getParam('id', 0);

        $this->view->id = $id;
        $this->view->params = $this->_request->getParams();
        $this->view->message = sprintf('Resource #%s Updated', $id);
        $this->_response->ok();
    }

    /**
     * The delete action handles DELETE requests and receives an 'id'
     * parameter; it should update the server resource state of the resource
     * identified by the 'id' value.
     */
    public function deleteAction(){
        $id = $this->_getParam('id', 0);

        $this->view->id = $id;
        $this->view->message = sprintf('Resource #%s Deleted', $id);
        $this->_response->ok();
    }
}