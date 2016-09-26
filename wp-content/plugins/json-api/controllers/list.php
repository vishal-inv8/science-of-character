<?php
/*
Controller name: List Controller
Controller description: JSON API List custom controller for list module in soc app
*/

class JSON_API_List_Controller
{
    private $_ErrorCode = array();
    public function __construct()
    {
	global $json_api;
        //if(strtolower($_SERVER["REQUEST_METHOD"]) == "get")
	//$json_api->error($this->post_msg_error,$this->error_code);
	require_once("errorcode.php");	
	$JSON_API_Errorcode_Controller = new JSON_API_Errorcode_Controller();
	$this->_ErrorCode = $JSON_API_Errorcode_Controller->_ErrorCode;
    }
    
    public function info()
    {
	global $json_api;
        return array(
            'version' => '1.0',
	    'code'=>200,
	    'msg'=>$this->_ErrorCode[200],
	    'desc'=>"This controller is created for list module in soc app"
        );
    }

  
    /* This method create list for each user */
    public function addlist()
    {
	global $json_api;
	global $wpdb;		
	
	$table_list = $wpdb->prefix.'list';
	$table_list_item = $wpdb->prefix.'list_item';
	
	$login_id = (int)$json_api->query->login_id;
	$cookie = $json_api->query->cookie;
	$name = $json_api->query->name;
	$privacy = (int)$json_api->query->privacy;
	
	if(!$login_id)
	{
	    $json_api->error($this->_ErrorCode[223],223);			
	}
	elseif( get_userdata( $login_id ) === false)
	{
	  $json_api->error($this->_ErrorCode[208],208);  
	}
	elseif(!$cookie)
	{
	    $json_api->error($this->_ErrorCode[221],221);
	}
	elseif( wp_validate_auth_cookie($cookie, 'logged_in') != $login_id)
	{
	  $json_api->error($this->_ErrorCode[222],222);  
	}
	elseif( empty($name))
	{
	  $json_api->error($this->_ErrorCode[238],238);  
	}
	else
	{
	    $name = sanitize_text_field($name);
	    
	    $result = $wpdb->get_results ("SELECT id FROM ".$table_list." WHERE name = '".$name."' AND user_id='".$login_id."'");
	    if( count($result) > 0)
	    {
		$response['msg'] = $this->_ErrorCode[239];
		$response["code"] = 239;
	    }
	    else
	    {
		$listTableData = array("id"=>'',"name"=>$name,"privacy"=>$privacy,"user_id"=>$login_id,"datetime"=>date("Y-m-d h:m:i",time()));
		$wpdb->insert($table_list,$listTableData);
		if($wpdb->insert_id)
		{
		    $response['msg'] = $this->_ErrorCode[200];
		    $response["code"] = 200;
		}
		else
		{
		    $json_api->error($this->_ErrorCode[240],240);
		}
	    }
	}		
	return $response;
    }
    
    /* This method update list privacy for logged-in user */
    public function editlist()
    {
	global $json_api;
	global $wpdb;	
	$tbl_list = $wpdb->prefix.'list';	
	$login_id = (int)$json_api->query->login_id;
	$cookie = $json_api->query->cookie;
	$list_id = (int)$json_api->query->list_id;
	$name = $json_api->query->name;
	$privacy = (int)$json_api->query->privacy;	
	if(!$login_id)
	{
	    $json_api->error($this->_ErrorCode[223],223);			
	}
	elseif( get_userdata( $login_id ) === false)
	{
	  $json_api->error($this->_ErrorCode[208],208);  
	}
	elseif(!$cookie)
	{
	    $json_api->error($this->_ErrorCode[221],221);
	}
	elseif( wp_validate_auth_cookie($cookie, 'logged_in') != $login_id)
	{
	  $json_api->error($this->_ErrorCode[222],222);  
	}
	elseif(!$list_id)
	{
	    $json_api->error($this->_ErrorCode[241],241);
	}
	else
	{
	    $updateDataArray = array('privacy' => $privacy);
	    if(!empty($name))
	    {
		$updateDataArray["name"] = $name;		
	    }
	    
	    $updateRes = $wpdb->update
	    ( 
		$tbl_list, 
		$updateDataArray, 
		array( 'id' => $list_id,'user_id'=>$login_id)
	    );  
	    $response['msg'] = $this->_ErrorCode[200];
	    $response["code"] = 200;
	}		
	return $response;
    }
    
    
    /* Item method remove list from already created list by user*/
    public function removelist()
    {
	global $json_api;
	global $wpdb;		
	
	$tbl_list = $wpdb->prefix.'list';
	$tbl_list_item = $wpdb->prefix.'list_item';
	
	$login_id = (int)$json_api->query->login_id;
	$cookie = $json_api->query->cookie;
	$list_id = $json_api->query->list_id;
	
	if(!$login_id)
	{
	    $json_api->error($this->_ErrorCode[223],223);			
	}
	elseif( get_userdata( $login_id ) === false)
	{
	  $json_api->error($this->_ErrorCode[208],208);  
	}
	elseif(!$cookie)
	{
	    $json_api->error($this->_ErrorCode[221],221);
	}
	elseif( wp_validate_auth_cookie($cookie, 'logged_in') != $login_id)
	{
	  $json_api->error($this->_ErrorCode[222],222);  
	}
	elseif(!$list_id)
	{
	  $json_api->error($this->_ErrorCode[241],241);  
	}
	else
	{
	    $listItemTableData = array("list_id"=>$list_id,"user_id"=>$login_id);
	    $wpdb->delete($tbl_list_item,$listItemTableData);

	    $listTableData = array("id"=>$list_id,"user_id"=>$login_id);
	    if($wpdb->delete($tbl_list,$listTableData))
	    {
		$response['msg'] = $this->_ErrorCode[200];
		$response["code"] = 200;
	    }
	    else
	    {
		$json_api->error($this->_ErrorCode[240],240);
	    }
	}		
	return $response;
    }
    
    /* This method add item into already created list by user*/
    public function additem()
    {
	global $json_api;
	global $wpdb;		
	
	$tbl_list = $wpdb->prefix.'list';
	$tbl_list_item = $wpdb->prefix.'list_item';
	
	$login_id = (int)$json_api->query->login_id;
	$cookie = $json_api->query->cookie;
	$post_id = (int)$json_api->query->post_id;	
	$type = isset($json_api->query->type)?$json_api->query->type:'s'; # s - single , m - multiple	
	
	if($type == 's')
	{
	    $list_id = (int)$json_api->query->list_id;
	}
	else
	{
	    $list_id = $json_api->query->list_id;
	}
	
	if(!$login_id)
	{
	    $json_api->error($this->_ErrorCode[223],223);			
	}
	elseif( get_userdata( $login_id ) === false)
	{
	  $json_api->error($this->_ErrorCode[208],208);  
	}
	elseif(!$cookie)
	{
	    $json_api->error($this->_ErrorCode[221],221);
	}
	elseif( wp_validate_auth_cookie($cookie, 'logged_in') != $login_id)
	{
	  $json_api->error($this->_ErrorCode[222],222);  
	}
	elseif(!$list_id)
	{
	  $json_api->error($this->_ErrorCode[241],241); 
	}
	elseif(!$post_id)
	{
	  $json_api->error($this->_ErrorCode[237],237);  
	}
	else
	{
	    if($type == 'm')
	    {
		$list_ids = explode(",",$list_id);
		for($i = 0; $i < count($list_ids); $i++)
		{
		    if($list_ids[$i] > 0)
		    {
			$result = $wpdb->get_results("SELECT id FROM ".$tbl_list_item." WHERE list_id = '".$list_ids[$i]."' AND post_id='".$post_id."' AND user_id='".$login_id."'");
			if(count($result) == 0)
			{
			    $listItemTableData = array("id"=>'',"list_id"=>$list_ids[$i],"post_id"=>$post_id,"user_id"=>$login_id);
			    $wpdb->insert($tbl_list_item,$listItemTableData);
			}
		    }
		}
		
		if(count($list_ids))
		{
		    $response['msg'] = $this->_ErrorCode[200];
		    $response["code"] = 200;
		}
		else
		{
		    $response['msg'] = $this->_ErrorCode[242];
		    $response["code"] = 242;
		}		
	    }
	    else
	    {
		$result = $wpdb->get_results ("SELECT id FROM ".$tbl_list_item." WHERE list_id = '".$list_id."' AND post_id='".$post_id."' AND user_id='".$login_id."'");
		if(count($result) > 0)
		{
		    $response['msg'] = $this->_ErrorCode[242];
		    $response["code"] = 242;
		}
		else
		{
		    $listItemTableData = array("id"=>'',"list_id"=>$list_id,"post_id"=>$post_id,"user_id"=>$login_id);
		    $wpdb->insert($tbl_list_item,$listItemTableData);
		    if($wpdb->insert_id)
		    {
			$response['msg'] = $this->_ErrorCode[200];
			$response["code"] = 200;
		    }
		    else
		    {
			$json_api->error($this->_ErrorCode[240],240);
		    }
		}
	    }
	}		
	return $response;
    }
  
  
    /* Item method remove list from already created list by user*/
    public function removeitem()
    {
	global $json_api;
	global $wpdb;		
	
	$tbl_list = $wpdb->prefix.'list';
	$tbl_list_item = $wpdb->prefix.'list_item';
	
	$login_id = (int)$json_api->query->login_id;
	$cookie = $json_api->query->cookie;
	$list_id = $json_api->query->list_id;
	$post_id = $json_api->query->post_id;
	
	if(!$login_id)
	{
	    $json_api->error($this->_ErrorCode[223],223);			
	}
	elseif( get_userdata( $login_id ) === false)
	{
	  $json_api->error($this->_ErrorCode[208],208);  
	}
	elseif(!$cookie)
	{
	    $json_api->error($this->_ErrorCode[221],221);
	}
	elseif( wp_validate_auth_cookie($cookie, 'logged_in') != $login_id)
	{
	  $json_api->error($this->_ErrorCode[222],222);  
	}
	elseif(!$list_id)
	{
	  $json_api->error($this->_ErrorCode[241],241);  
	}
	elseif(!$post_id)
	{
	  $json_api->error($this->_ErrorCode[237],237);  
	}
	else
	{
	    $listItemTableData = array("list_id"=>$list_id,"post_id"=>$post_id,"user_id"=>$login_id);
	    if($wpdb->delete($tbl_list_item,$listItemTableData))
	    {
		$response['msg'] = $this->_ErrorCode[200];
		$response["code"] = 200;
	    }
	    else
	    {
		$json_api->error($this->_ErrorCode[240],240);
	    }
	}		
	return $response;
    }
    
    /* Method : follower_list()  give you listing of all users who are following you on soc app */
    public function user_list()
    {
	global $json_api;
	global $wpdb;	  
	
	$tbl_list = $wpdb->prefix.'list';
	$tbl_list_item = $wpdb->prefix.'list_item';
	
	$login_id = (int)$json_api->query->login_id;
	$profile_id = (int)$json_api->query->profile_id;
	$cookie = $json_api->query->cookie;      	 
	
	if(!$login_id)
	{
	    $json_api->error($this->_ErrorCode[223],223);			
	}
	elseif( get_userdata( $login_id ) === false)
	{
	  $json_api->error($this->_ErrorCode[208],208);  
	}
	elseif(!$cookie)
	{
	    $json_api->error($this->_ErrorCode[221],221);
	}
	elseif( wp_validate_auth_cookie($cookie, 'logged_in') != $login_id)
	{
	  $json_api->error($this->_ErrorCode[222],222);  
	}
	if(!$profile_id)
	{
	    $json_api->error($this->_ErrorCode[224],224);			
	}
	elseif( get_userdata( $profile_id ) === false)
	{
	  $json_api->error($this->_ErrorCode[208],208);  
	}	  
	else
	{
	    $AndSql = "";
	    if($login_id != $profile_id)
	    {
		$AndSql = " AND privacy='0'";
	    }
	    
	    $list_sql = "
	    SELECT
	    L.id,L.name,L.privacy,L.user_id,count(LI.list_id) as total_item
	    FROM ".$tbl_list." AS L
	    LEFT JOIN ".$tbl_list_item." as LI ON LI.list_id = L.id
	    WHERE L.user_id = ".$profile_id." $AndSql GROUP BY L.id ";
	    
	    $list_results = (array)$wpdb->get_results($list_sql,ARRAY_A);
	    if($list_results)
	    {
		$result_Set = array();
		foreach($list_results as $list_row)
		{
		    $list_row["name"] = stripslashes($list_row['name']);
		    $all_item_sql = "SELECT post_id FROM ".$tbl_list_item." WHERE user_id = '".$list_row['user_id']."' AND list_id='".$list_row['id']."'";
		    $all_item_results = (array)$wpdb->get_results($all_item_sql,ARRAY_A);
		    $list_row["user_items"] = array();
		    foreach($all_item_results as $all_item_row)
		    {
			$list_row["user_items"][] = array("post_id"=>$all_item_row['post_id'],"post_weburl"=>(string)get_permalink( $all_item_row['post_id'] ));
		    }
		    $result_Set[] = $list_row;
		}
	    }
	    
	    $response["results"] = $result_Set;
	    if($result_Set)
	    {
	      $response["code"] = 200;
	      $response["msg"] = $this->_ErrorCode[200];  
	    }
	    else
	    {
	      $response["code"] = 209;
	      $response["msg"] = $this->_ErrorCode[209];
	    }
	  }
	  return $response;			
    }
    
    
    /* Method : follower_list()  give you listing of all users who are following you on soc app */
    public function list_items()
    {
	global $json_api;
	global $wpdb;	  
	
	$tbl_list = $wpdb->prefix.'list';
	$tbl_list_item = $wpdb->prefix.'list_item';
	
	$login_id = (int)$json_api->query->login_id;
	$cookie = $json_api->query->cookie;    	 
	$list_id = (int)$json_api->query->list_id;
	
	if(!$login_id)
	{
	    $json_api->error($this->_ErrorCode[223],223);			
	}
	elseif( get_userdata( $login_id ) === false)
	{
	  $json_api->error($this->_ErrorCode[208],208);  
	}
	elseif(!$cookie)
	{
	    $json_api->error($this->_ErrorCode[221],221);
	}
	elseif( wp_validate_auth_cookie($cookie, 'logged_in') != $login_id)
	{
	  $json_api->error($this->_ErrorCode[222],222);  
	}
	elseif(!$list_id)
	{
	  $json_api->error($this->_ErrorCode[241],241);  
	}
	else
	{
	    $response["user_list_items"] = array();
	    $sql = "
	    SELECT
	    post_id
	    FROM ".$tbl_list_item."
	    WHERE list_id = ".$list_id;
	    
	    $list_item_results = (array)$wpdb->get_results($sql,ARRAY_A);
	    	    
	    if($list_item_results)
	    {		
		require_once("education.php");
		$EducationHub = new JSON_API_Education_Controller();
		foreach($list_item_results as $list_item_row)
		{
		    $post_detail = array();
		    $post_detail = get_post($list_item_row["post_id"]);
		    
		    if($post_detail)
		    {
			$postRow = array();
			$postRow["post_id"] =  $post_detail->ID;
			$postRow["post_title"] =  $post_detail->post_title;    
			$postRow["post_content"] =  $post_detail->post_content;
			$postRow["post_thumbnail"] = (string)get_the_post_thumbnail_url( $post_detail->ID, "thumbnail", '' );			
			$postRow["post_weburl"] =  (string)get_permalink( $post_detail->ID);

			$get_post_taxonomy = $EducationHub->get_post_taxonomy($post_detail->ID);
			
			$postRow["ages_list"] = $get_post_taxonomy['ages_list'];
			$postRow["strengths_list"] = $get_post_taxonomy['strengths_list'];
			$postRow["resource_list"] = $get_post_taxonomy['resource_list'];
			$postRow["purpose_list"] = $get_post_taxonomy['purpose_list'];
			
			$response["user_list_items"][] = $postRow;
		    }
		}
	    }
	    else
	    {
		$response["code"] = 244;
		$response["msg"] = $this->_ErrorCode[244];
	    }
	    
	    if($response["user_list_items"])
	    {
	      $response["code"] = 200;
	      $response["msg"] = $this->_ErrorCode[200];  
	    }
	    else
	    {
	      $response["code"] = 209;
	      $response["msg"] = $this->_ErrorCode[209];
	    }
	}
	return $response;			
    }
    
    
    public function testUpload()
    {
	global $json_api;
	global $wpdb;
	
	if (!$json_api->query->username)
	{
	    $json_api->error($this->_ErrorCode[210],210);
	}
	elseif (!$json_api->query->password)
	{
	    $json_api->error($this->_ErrorCode[213],213);
	}
	
	$password = $json_api->query->password;
	
	$user     = get_user_by('login', $json_api->query->username);
	print_r($user);
	
	$response = wp_check_password($password, $user->data->user_pass, $user->ID);
	
	return $response;
	
    }
}

function add_list_controller($controllers){
    $controllers[] = 'list';
    return $controllers;
}
add_filter('json_api_controllers', 'add_list_controller');
function set_list_controller_path(){
    return get_stylesheet_directory() . "/list.php";
}
add_filter('json_api_list_controller_path', 'set_list_controller_path');