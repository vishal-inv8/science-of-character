<?php
/*
Controller name: Error Codes
Controller description: This controller defines error codes and their messages for api response
*/

class JSON_API_Errorcode_Controller
{
    public $_ErrorCode = array();    
    public function __construct()
    {
	$this->_ErrorCode[200] = "success";	
	$this->_ErrorCode[201] = "email address is required";
	$this->_ErrorCode[202] = "you must include nonce var in your request";
	$this->_ErrorCode[203] = "invalid nonce value";
	$this->_ErrorCode[204] = "username is invalid";
	$this->_ErrorCode[205] = "Username already exists";
	$this->_ErrorCode[206] = "invalid email address";
	$this->_ErrorCode[207] = "email address is already in use";
	$this->_ErrorCode[208] = "user does not exists";
	$this->_ErrorCode[209] = "empty list";
	$this->_ErrorCode[210] = "you must include a username var in your request";
	$this->_ErrorCode[211] = "you must include a password var in your request";
	$this->_ErrorCode[212] = "invalid username or password";
	$this->_ErrorCode[213] = "current password is required";
	$this->_ErrorCode[214] = "current password does not match";
	$this->_ErrorCode[215] = "new password is required";
	$this->_ErrorCode[216] = "confirm password is required";
	$this->_ErrorCode[217] = "new password and confirm password should match";
	$this->_ErrorCode[218] = "you must include a user_id var in your request";
	$this->_ErrorCode[219] = "you must include a access_token var in your request from facebook API";
	$this->_ErrorCode[220] = "invalid access_token";
	$this->_ErrorCode[221] = "you must include a cookie authentication cookie";
	$this->_ErrorCode[222] = "invalid authentication cookie";
	$this->_ErrorCode[223] = "you must include login userid var in your request";
	$this->_ErrorCode[224] = "you must include profile userid var in your request";
	$this->_ErrorCode[230] = "please include follow_to var in request";
	$this->_ErrorCode[231] = "please include follow_by var in request";
	$this->_ErrorCode[232] = "You can not follow yourself";
	$this->_ErrorCode[233] = "please include unfollow_to var in request";
	$this->_ErrorCode[234] = "please include unfollow_by var in request";
	$this->_ErrorCode[235] = "You can not unfollow yourself";
	$this->_ErrorCode[236] = "already performed";
	$this->_ErrorCode[237] = "you must include post ID in your request";
	$this->_ErrorCode[238] = "list name is empty";
	$this->_ErrorCode[239] = "list name already exist";
	$this->_ErrorCode[240] = "database error";
	$this->_ErrorCode[241] = "you must include list ID in your request";
	$this->_ErrorCode[242] = "item already added in list";
	$this->_ErrorCode[243] = "record found";
	$this->_ErrorCode[244] = "no record found";
	$this->_ErrorCode[245] = "invalid image";
	$this->_ErrorCode[246] = "password has been updated successfully";
	$this->_ErrorCode[247] = "We have emailed you a link to reset your password. The link will be expire in one hour";
	$this->_ErrorCode[301] = "unknown controller";
	$this->_ErrorCode[302] = "unknown method";
	$this->_ErrorCode[303] = "include controller and method vars in your request";	
	$this->_ErrorCode[404] = "not found";
	$this->_ErrorCode[405] = "only post method is accepted";
    }
    
    public function info()
    {
	global $json_api;
        return array(
            'version' => '1.0',
	    'code'=>200,
	    'msg'=>$this->_ErrorCode[200],	    
	    'desc'=>"This method describe information about error code controller used in science of character project"
	);
    }
    
    
    public function listing()
    {
	global $json_api;
	$response = array(
	    'code'=>200,
	    'msg'=>$this->_ErrorCode[200],	    
	    'desc'=>"This method display all the error codes defined at server side in api response",
	    'errorcode_list'=>$this->_ErrorCode
        );	
	if($json_api->query->format!='json')
	{
	    echo "<pre>";print_r($response);die;
	}
	return $response;    	
    }    
}

function add_errorcode_controller($controllers){
    $controllers[] = 'errorcode';
    return $controllers;
}
add_filter('json_api_controllers', 'add_errorcode_controller');
function set_errorcode_controller_path(){
    return get_stylesheet_directory() . "/errorcode.php";
}
add_filter('json_api_errorcode_controller_path', 'set_errorcode_controller_path');