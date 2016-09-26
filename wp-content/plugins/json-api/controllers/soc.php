<?php
/*
Controller name: Soc Controller
Controller description: JSON API Soc custom controller
*/

class JSON_API_Soc_Controller
{
  private $_ErrorCode = array();    
  public function __construct()
  {
      global $json_api;
      require_once("errorcode.php");
      $JSON_API_Errorcode_Controller = new JSON_API_Errorcode_Controller();
      $this->_ErrorCode = $JSON_API_Errorcode_Controller->_ErrorCode;
      
      //if(strtolower($_SERVER["REQUEST_METHOD"]) == "get")
      //$json_api->error($this->_ErrorCode[405],405);	  
  }
  
  /* Method : info() show the information about controller */
  public function info()
  {
      global $json_api;
      return array(
	  'version' => '1.0',
	  'code'=>200,
	  'msg'=>"This is custom controller defined for custom api response modification"
      );
  }
    
  /* Method : reset_password() reset user password */
  public function reset_password()
  {
      global $json_api;
      global $wpdb;
      
      if (!$json_api->query->cookie) {
	 $json_api->error($this->_ErrorCode[221],221);
      } else {
	  $valid = wp_validate_auth_cookie($json_api->query->cookie, 'logged_in') ? true : false;
	  if (!$valid) {
	      $json_api->error($this->_ErrorCode[222],222);
	  }
      }
      
      if (!$json_api->query->username)
      {
	  $json_api->error($this->_ErrorCode[210],210);
      }
      elseif (!$json_api->query->password) {
	  $json_api->error($this->_ErrorCode[213],213);
      }
      elseif (!$json_api->query->n_password) {
	  $json_api->error($this->_ErrorCode[215],215);
      }
      elseif (!$json_api->query->c_password) {
	  $json_api->error($this->_ErrorCode[216],216);
      }
      elseif ($json_api->query->n_password !== $json_api->query->c_password) {
	  $json_api->error($this->_ErrorCode[217],217);
      }
      else {
	  $password = $json_api->query->password;
	  $user     = get_user_by('login', $json_api->query->username);
	  if ($user && wp_check_password($password, $user->data->user_pass, $user->ID))
	  {
	      wp_set_password($json_api->query->n_password, $user->ID);
	      $msg = $this->_ErrorCode[200];
	      $code = 200;
	  }
	  else {
	      $json_api->error($this->_ErrorCode[214],214);
	  }
      }
      
      return array(
	  'msg' => $msg,
	  "code"=>$code
      );
  }
    
  /* Method : register() register user on soc app */
  public function register()
  {
      global $json_api;
      
      if (!$json_api->query->email) {
	  $json_api->error($this->_ErrorCode[201],201);
      }
      else
      {
	  $email = sanitize_email($json_api->query->email);
      }
	  
      if(!$json_api->query->username)
      {
	  $username = $email;
      }
      else
      {
	  $username = sanitize_user($json_api->query->username);
      }
      
      if (!$json_api->query->nonce)
      {
	  $json_api->error($this->_ErrorCode[202],202);
	 
      }
      else
      {
	  $nonce = sanitize_text_field($json_api->query->nonce);
      } 
      
      if (!$json_api->query->user_pass)
      {
	  $json_api->error($this->_ErrorCode[211],211);
	 
      }
      else
      {
	  $user_pass = sanitize_text_field($json_api->query->user_pass);
      }   
      
      if (!$json_api->query->display_name)
      {
	   $display_name = $email;    
      }
      else
      {
	  $display_name = sanitize_text_field($json_api->query->display_name);
      }
      
      if ($json_api->query->seconds)
      {
	  $seconds = (int) $json_api->query->seconds;
      }        
      else
      {
	  $seconds = 1209600; //14 days
      }
      
      //Add usernames we don't want used
      $invalid_usernames = array(
	  'admin'
      );
      
      //Do username validation
      $nonce_id = $json_api->get_nonce_id('soc', 'register');
      
      if (!wp_verify_nonce($json_api->query->nonce, $nonce_id))
      {            
	    $json_api->error($this->_ErrorCode[203],203);
      }
      else 	
      {            
	  if (!validate_username($username) || in_array($username, $invalid_usernames))
	  {                
	      $json_api->error($this->_ErrorCode[204],204);   
	  }            
	  elseif (username_exists($username))
	  {                
	      $json_api->error($this->_ErrorCode[205],205);   
	  }            
	  else
	  {                             
	      if (!is_email($email)) {
		  $json_api->error($this->_ErrorCode[206],206);
	      }
	      elseif (email_exists($email)) {    
		  $json_api->error($this->_ErrorCode[207],207);
	      }                
	      else
	      {                    
		  //Everything has been validated, proceed with creating the user
		  
		  //Create the user
		  
		  if (!isset($user_pass)) {
		      $user_pass             = wp_generate_password();
		      $_REQUEST['user_pass'] = $user_pass;
		  }
		  
		  $_REQUEST['user_login'] = $username;
		  $_REQUEST['user_email'] = $email;
		  
		  $allowed_params = array(
		      'user_login',
		      'user_email',
		      'user_pass',
		      'display_name',
		      'user_nicename',
		      'user_url',
		      'nickname',
		      'first_name',
		      'last_name',
		      'description',
		      'rich_editing',
		      'user_registered',
		      'role',
		      'jabber',
		      'aim',
		      'yim',
		      'comment_shortcuts',
		      'admin_color',
		      'use_ssl',
		      'show_admin_bar_front'
		  );
		  
		  
		  foreach ($_REQUEST as $field => $value) {
		      
		      if (in_array($field, $allowed_params))
			  $user[$field] = trim(sanitize_text_field($value));
		      
		  }
		  $user['role'] = get_option('default_role');
		  $user_id      = wp_insert_user($user);
		  
		  /*Send e-mail to admin and new user - 
		  You could create your own e-mail instead of using this function*/
		  
		  if (isset($_REQUEST['user_pass']) && $_REQUEST['notify'] == 'no') {
		      $notify = '';
		  } elseif ($_REQUEST['notify'] != 'no')
		      $notify = $_REQUEST['notify'];
		  
		  
		  if ($user_id)
		      wp_new_user_notification($user_id, '', $notify);
		  
		  
	      }
	  }
      }
      
      $expiration                      = time() + apply_filters('auth_cookie_expiration', $seconds, $user_id, true);
      $cookie                          = wp_generate_auth_cookie($user_id, $expiration, 'logged_in');
      $user_profile_data               = array();
      $user_profile_data               = get_userdata($user_id);
      $user_profile_data->data->cookie = $cookie;
      
      return array(
	  "cookie" => $cookie,
	  "user_id" => $user_id,
	  "user_profile_data" => $user_profile_data->data,
	  "code"=>200,
	  "msg"=>$this->_ErrorCode[200]
      );
  }
    
  /* Method : fb_connect() facebook signup method on soc app */
  public function fb_connect()
  {
      global $json_api;
      
      if ($json_api->query->fields)
      {            
	$fields = $json_api->query->fields;
      }
      else
      {
	$fields = 'id,name,first_name,last_name,email';
      }
      
      if ($json_api->query->ssl) {
	  $enable_ssl = $json_api->query->ssl;
      }
      else
	  $enable_ssl = true;
  
      if (!$json_api->query->access_token) {
	  $json_api->error($this->_ErrorCode[219],219);
      }
      else
      {            
	  $url = 'https://graph.facebook.com/me/?fields=' . $fields . '&access_token=' . $json_api->query->access_token;
	  
	  //  Initiate curl
	  $ch = curl_init();
	  // Enable SSL verification
	  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $enable_ssl);
	  // Will return the response, if false it print the response
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	  // Set the url
	  curl_setopt($ch, CURLOPT_URL, $url);
	  // Execute
	  $result = curl_exec($ch);
	  // Closing
	  curl_close($ch);
	  
	  $result = json_decode($result, true);
	      
	  if(isset($result["id"]))
	  {
	      if(!empty($result["email"])) # email coming from facebok
	      {
		$user_email   = $result["email"];
	      }
	      else
	      {
		$user_email   = $result["id"].'@facebook.com';
	      }	      
	      $email_exists = email_exists($user_email);		
	      if ($email_exists)
	      {
		  $user      = get_user_by('email', $user_email);
		  $user_id   = $user->ID;
		  $user_name = $user->user_login;
	      }		
	     
	      if (!$user_id && $email_exists == false) {
		  
		  $user_name = strtolower($result['first_name'] . '.' . $result['last_name']);
		  
		  while (username_exists($user_name)) {
		      $i++;
		      $user_name = strtolower($result['first_name'] . '.' . $result['last_name']) . '.' . $i;
		      
		  }
		  
		  $random_password = wp_generate_password($length = 12, $include_standard_special_chars = false);
		  $userdata        = array(
		      'user_login' => $user_name,
		      'user_email' => $user_email,
		      'user_pass' => $random_password,
		      'display_name' => $result["name"],
		      'first_name' => $result['first_name'],
		      'last_name' => $result['last_name']
		  );
		  
		  
		  $user_id = wp_insert_user($userdata);

		  if ($user_id)
		      $user_account = 'user registered.';
		  
	      }
	      else
	      {
		  
		  if ($user_id)
		      $user_account = 'user logged in.';
	      }
	      
	      $expiration = time() + apply_filters('auth_cookie_expiration', 1209600, $user_id, true);
	      $cookie     = wp_generate_auth_cookie($user_id, $expiration, 'logged_in');
	      if ($user_id) {
		  $user_profile_data               = get_userdata($user_id);
		  $user_profile_data->data->cookie = $cookie;
	      }
	      
	      //$response['msg']               = $user_account;
	      $response['wp_user_id']        = $user_id;
	      $response['user_profile_data'] = $user_profile_data->data;
	      $response['cookie']            = $cookie;
	      $response['user_login']        = $user_name;
	      
	  }
	  else
	  {
	      $json_api->error($this->_ErrorCode[220],220);
	  }
      }
      
      $response['msg'] = $this->_ErrorCode[200];
      $response["code"] = 200;
      return $response;	    
  }
    
  /* Method : get_userinfo() get user basic detail on soc app */
  public function get_userinfo()
  {        
      global $json_api;
      global $wpdb;
      
      $tbl_users_follow = $wpdb->prefix.'users_follow';
      $login_id = (int)$json_api->query->login_id;
      
      if($json_api->query->user_id)
      {
	$user_id = (int)$json_api->query->user_id;
      }
      elseif($json_api->query->profile_id)
      {
	$user_id = (int)$json_api->query->profile_id;
      }
      
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
      if(!$user_id)
      {
	  $json_api->error($this->_ErrorCode[224],224);
      }
      elseif( get_userdata( $user_id ) === false)
      {
	  $json_api->error($this->_ErrorCode[208],208);  
      }
      else
      {	  
	  $user = get_userdata($user_id);
	  $user_avatar_detail = self::wp_get_soc_user_avatar($user_id,$user->user_email);	  
	  $response =  array(
	      "id" => $user->ID,
	      "username" => $user->user_login,
	      "nicename" => $user->user_nicename,
	      "email" => $user->user_email,
	      "url" => $user->user_url,
	      "displayname" => $user->display_name,
	      "firstname" => $user->user_firstname,
	      "lastname" => $user->last_name,
	      "nickname" => $user->nickname,
	      "user_registered" => $user->user_registered,
	      "avatar" => $user_avatar_detail['avatar_url'],
	      "avatar_set"=>$user_avatar_detail['default']
	  );
      
	  $result = $wpdb->get_results ("SELECT id FROM ".$tbl_users_follow." WHERE follow_to = '".$user_id."' AND follow_by='".$login_id."'");
	  if( count($result) > 0)
	  {
	    $response["you_follow"] = 1;  
	  }
	  else
	  {
	    $response["you_follow"] = 0;
	  }	
      
	  $follower_count_sql = 
	  "SELECT count(*) as total_follower FROM ".$tbl_users_follow."
	  WHERE follow_to = ".$user_id;
	  $follower_count_row = (array)$wpdb->get_row($follower_count_sql,ARRAY_A);
	  
	  $following_count_sql = 
	  "SELECT count(*) as total_following FROM ".$tbl_users_follow."
	  WHERE follow_by = ".$user_id;
	  $following_count_row = (array)$wpdb->get_row($following_count_sql,ARRAY_A);
	  
	  $response["total_follower"] = $follower_count_row["total_follower"];
	  $response["total_following"] = $following_count_row["total_following"];
	  
	  $response["code"] = 200;
	  $response["msg"] = $this->_ErrorCode[200];
      
	  return $response;
      }        
  }
    
  public function wp_get_soc_user_avatar($user_id,$user_email)
  {
    /*
      $avatar =  get_avatar_url( $user_id );     
      if(strpos($user_email, '@facebook.com') !== false)
      {
	$user_email_arr = explode("@",$user_email);
	$avatar = "http://graph.facebook.com/".$user_email_arr[0]."/picture?type=large";
	$avatar_set = 1;
      }
      else
      {
	$hash = md5($user_email);
	$uri = 'http://www.gravatar.com/avatar/' . $hash . '?d=404';
	$headers = @get_headers($uri);
	if (!preg_match("|200|", $headers[0]))
	{
	  $avatar_set = 0;	  
	  $user_meta_data = get_user_meta( $user_id, "wp_user_avatars" );
	  $wp_user_avatar_data = unserialize($user_meta_data["wp_user_avatars"][0]);   	  
	  if(!empty($wp_user_avatar_data['full']))
	  {
	    $avatar_set = 1;
	    $avatar = $wp_user_avatar_data['full'];
	  }
	}
	else
	{
	  $avatar_set = 1;
	}
      }
      return array("avatar_url"=>$avatar,"default"=>$avatar_set);
      
      */
      //echo "wp_get_soc_user_avatar....";
     
      $avatar_set = 0;	  
      $wp_user_avatar_data = get_user_meta( $user_id, "wp_user_avatars" );
      if(!empty($wp_user_avatar_data[0]['full']))
      {
	$avatar_set = 1;
	$avatar = $wp_user_avatar_data[0]['full'];
      }
      elseif(strpos($user_email, '@facebook.com') !== false)
      {
	$avatar_set = 1;
	$user_email_arr = explode("@",$user_email);
	$avatar = "http://graph.facebook.com/".$user_email_arr[0]."/picture?type=large";
      }
      else
      {
	$avatar =  get_avatar_url( $user_id );
	$hash = md5($user_email);
	$uri = 'http://www.gravatar.com/avatar/' . $hash . '?d=404';
	$headers = @get_headers($uri);	
	if (!preg_match("|200|", $headers[0]))
	{
	  $avatar_set = 0;	  
	  $user_meta_data = get_user_meta( $user_id, "wp_user_avatars" );
	  $wp_user_avatar_data = unserialize($user_meta_data["wp_user_avatars"][0]);   	  
	  if(!empty($wp_user_avatar_data['full']))
	  {
	    $avatar_set = 1;
	    $avatar = $wp_user_avatar_data['full'];
	  }
	}
	else
	{
	  $avatar_set = 1;
	}
      }
      if($avatar_set == 0){
	$wp_upload_dir = wp_upload_dir();	
	$avatar = $wp_upload_dir['baseurl'].'/soc_default_pic.png';
      }
      
      return array("avatar_url"=>$avatar,"default"=>$avatar_set);
  }
  
  public function wp_get_soc_user_avatar_new($user_id,$user_email)
  {   
    $avatar_set = 0;	  
    $user_meta_data = get_user_meta( $user_id, "wp_user_avatars" );
    $wp_user_avatar_data = unserialize($user_meta_data["wp_user_avatars"][0]);
    
    echo "<pre>";
    
    print_r($wp_user_avatar_data);die;
    
    
    if(!empty($wp_user_avatar_data['full']))
    {
      $avatar_set = 1;
      $wp_user_avatar_data['full'] = $wp_user_avatar_data['full'];
      $avatar = $wp_user_avatar_data['full'];
    }
    else
    {      
      $avatar =  get_avatar_url( $user_id );
      $hash = md5($user_email);
      $uri = 'http://www.gravatar.com/avatar/' . $hash . '?d=404';
      $headers = @get_headers($uri);
      if (!preg_match("|200|", $headers[0]))
      {
	$avatar_set = 0;
      }
      else
      {
	$avatar_set = 1;
      }
    }
    return array("avatar_url"=>$avatar,"default"=>$avatar_set);
  }
  
  
  /* Method : wp_user_list() all users list who are registered on soc app */
  public function wp_user_list()
  {
      global $json_api;
      global $wpdb;	
      
      $tbl_users = $wpdb->prefix.'users';
      $tbl_users_follow = $wpdb->prefix.'users_follow';
      $tbl_list_items = $wpdb->prefix.'list_item';
      $login_id = (int)$json_api->query->login_id;
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
      else
      {
	  $all_users_sql = 
	  "SELECT
	  U.ID,U.user_login,U.user_email,U.user_nicename,U.display_name,
	  IF(UF1.id IS NOT NULL,'1','0') AS you_follow,
	  IF(UF2.id IS NOT NULL,'1','0') AS follow_you
	  FROM ".$tbl_users." AS U
	  LEFT JOIN ".$tbl_users_follow." AS UF1 ON UF1.follow_to = U.ID AND UF1.follow_by = '".$login_id."'
	  LEFT JOIN ".$tbl_users_follow." AS UF2 ON UF2.follow_by = U.ID AND UF2.follow_to = '".$login_id."'
	  WHERE U.ID != ".$login_id." GROUP BY U.ID";
	  $all_users_result = (array)$wpdb->get_results($all_users_sql,ARRAY_A);
	  $response["all_user_list"] = $all_users_result;
	  
	  /*
	  $all_users_result_arr = array();
	  foreach($all_users_result as $all_users_row)
	  {
	      $all_item_sql = "SELECT	post_id	FROM ".$tbl_list_items." WHERE user_id = '".$all_users_row['ID']."'";
	      $all_users_row["user_items"] = (array)$wpdb->get_results($all_item_sql,ARRAY_A);
	      $all_item_result_arr = array();
	      foreach($all_users_row["user_items"] as $user_item_row)
	      {
		$user_item_row["post_weburl"] = (string)get_permalink( $user_item_row['post_id'] );
		$all_item_result_arr[] = $user_item_row;
	      }
	      $all_users_row["user_items"] = $all_item_result_arr;
	      $all_users_result_arr[] = $all_users_row;		
	  }
	  $response["all_user_list"] = $all_users_result_arr;
	  */
      }      
      if(count($response["all_user_list"]) > 0)
      {
	  $response["code"] = 200;
	  $response["msg"] = $this->_ErrorCode[200];    
      }
      else
      {
	  $response["code"] = 209;
	  $response["msg"] = $this->_ErrorCode[209];
      }
      if($json_api->query->debug == 1)
      {
	echo "<pre>";print_r($response);
	
      }
      return $response;			
  }
  
  /* Method : follow_user()  allow to follow any user on soc app */
  public function follow_user()
  {
      global $json_api;
      global $wpdb;
      
      $table_name = $wpdb->prefix.'users_follow';
      $follow_to = (int)$json_api->query->follow_to;
      $follow_by = (int)$json_api->query->follow_by;
      $cookie = $json_api->query->cookie;	
      
      if(!$follow_to)
      {
	  $json_api->error($this->_ErrorCode[230],230);		
      }
      if(!$follow_by)
      {
	  $json_api->error($this->_ErrorCode[223],223);			
      }
      elseif($follow_to == $follow_by)
      {
	  $json_api->error($this->_ErrorCode[232],232);
      }
      elseif( get_userdata( $follow_to ) === false)
      {
	$json_api->error($this->_ErrorCode[208],208);  
      }
      elseif( get_userdata( $follow_by ) === false)
      {
	$json_api->error($this->_ErrorCode[208],208);  
      }
      elseif(!$cookie)
      {
	  $json_api->error($this->_ErrorCode[221],221);
      }
      elseif( wp_validate_auth_cookie($cookie, 'logged_in') != $follow_by)
      {
	$json_api->error($this->_ErrorCode[222],222);  
      }	
      else
      {
	  $followTableData = array("id"=>'',"follow_to"=>$json_api->query->follow_to,"follow_by"=>$json_api->query->follow_by);
	  $wpdb->insert($table_name,$followTableData);
	  if($wpdb->insert_id)
	  {
	      $response['msg'] = "User has been added";
	      $response["code"] = 200;
	  }
	  else
	  {
	      $json_api->error($this->_ErrorCode[236],236);
	  }
      }		
      return $response;
  }
    
  /* Method : unfollow_user()  allow to unfollow any user on soc app */
  public function unfollow_user()
  {
      global $json_api;
      global $wpdb;
      
      $table_name = $wpdb->prefix.'users_follow';
      $unfollow_to = (int)$json_api->query->unfollow_to;
      $unfollow_by = (int)$json_api->query->unfollow_by;
      $cookie = $json_api->query->cookie;
      
      if(!$unfollow_to)
      {
	  $json_api->error($this->_ErrorCode[233],233);			
      }
      elseif(!$unfollow_by)
      {
	  $json_api->error($this->_ErrorCode[234],234);		
      }
      elseif($unfollow_to == $unfollow_by)
      {
	  $json_api->error($this->_ErrorCode[235],235);
      }
      elseif(!$cookie)
      {
	  $json_api->error($this->_ErrorCode[221],221);
      }
      elseif( wp_validate_auth_cookie($cookie, 'logged_in') != $unfollow_by)
      {
	  $json_api->error($this->_ErrorCode[222],222);  
      }
      else
      {
	  $unfollowTableData = array("follow_to"=>$json_api->query->unfollow_to,"follow_by"=>$json_api->query->unfollow_by);
	  if($wpdb->delete($table_name,$unfollowTableData))
	  {
	      $response['msg'] = $this->_ErrorCode[200];
	      $response["code"] = 200;
	  }
	  else
	  {
	      $json_api->error($this->_ErrorCode[236],236);
	  }
      }
      return $response;
  }
  
  /* Method : follower_list()  give you listing of all users who are following you on soc app */
  public function follower_list()    
  {
      global $json_api;
      global $wpdb;	  
      
      $tbl_users = $wpdb->prefix.'users';
      $tbl_users_follow = $wpdb->prefix.'users_follow';
      
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
	  $follower_sql = 
	  "SELECT UF.*,U.ID,U.user_login,U.user_email,U.user_nicename,U.display_name,IF(UF1.id IS NOT NULL,'1','0') AS you_follow
	  FROM ".$tbl_users_follow." AS UF 
	  LEFT JOIN ".$tbl_users." AS U ON UF.follow_by = U.ID
	  LEFT JOIN ".$tbl_users_follow." AS UF1 ON UF1.follow_to = U.ID AND UF1.follow_by = '".$login_id."'
	  WHERE UF.follow_to = ".$profile_id;
	  $follower_list = (array)$wpdb->get_results($follower_sql,ARRAY_A);
	  $response["follower_list"] = $follower_list;
	  
	  //$user_avatar_detail = self::wp_get_soc_user_avatar($user_id,$user->user_email);
	  
	  if($follower_list)
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
    
  /* Method : following_list()  give you listing of all users whom you are following on soc app */
  public function following_list()
  {		
      global $json_api;
      global $wpdb;
    
      $tbl_users = $wpdb->prefix.'users';
      $tbl_users_follow = $wpdb->prefix.'users_follow';
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
	$following_list = array(); 
	$following_sql = 
	"SELECT UF.*,U.ID,U.user_login,U.user_email,U.user_nicename,U.display_name,IF(UF1.id IS NOT NULL,'1','0') AS you_follow
	FROM ".$tbl_users_follow." AS UF 
	LEFT JOIN ".$tbl_users." AS U ON UF.follow_to = U.ID
	LEFT JOIN ".$tbl_users_follow." AS UF1 ON UF1.follow_to = U.ID AND UF1.follow_by = '".$login_id."'
	WHERE U.ID IS NOT NULL AND UF.follow_by = ".$profile_id;	  
	$following_list = (array)$wpdb->get_results($following_sql,ARRAY_A);
	$response["following_list"] = $following_list;
	if(count($response["following_list"]) > 0)
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
  
  /* Method : follow_count() give you count of follower and following on soc app */
  public function follow_count()
  {
      global $json_api;
      global $wpdb;

      $tbl_users = $wpdb->prefix.'users';
      $tbl_users_follow = $wpdb->prefix.'users_follow';
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
	  $follower_count_sql = 
	  "SELECT count(*) as total_follower FROM ".$tbl_users_follow."
	  WHERE follow_to = ".$profile_id;
	  $follower_count_row = (array)$wpdb->get_row($follower_count_sql,ARRAY_A);
	  $response["total_follower"] = $follower_count_row["total_follower"];
	  
	  $following_count_sql = 
	  "SELECT count(*) as total_following FROM ".$tbl_users_follow."
	  WHERE follow_by = ".$profile_id;
	  $following_count_row = (array)$wpdb->get_row($following_count_sql,ARRAY_A);
	  $response["total_following"] = $following_count_row["total_following"];
      }
      
      $response["code"] = 200;
      $response["msg"] = $this->_ErrorCode[200];
      return $response;
  }
    
  /* Method : get_taxonomy() give you listing dropd down values for education hub search module on soc app */
  public function get_taxonomy()
  {
      global $json_api;
      global $wpdb;

      $login_id = (int)$json_api->query->login_id;
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
      else
      {
	  $response["ages"] = array();
	  $response["strengths"] = array();
	  $response["resource"] = array();
	  $response["purpose"] = array();
      
	  $ages_list = get_terms( 'ages', 'orderby=count&hide_empty=0');	 
	  if($ages_list)
	  {
	    $response["ages"] = $ages_list;
	  }
	  
	  $strength_list = get_terms( 'strengths', 'orderby=count&hide_empty=0');	 
	  if($strength_list)
	  {
	    $response["strengths"] = $strength_list;
	  }
      
	  $resource_list = get_terms( 'resource', 'orderby=count&hide_empty=0');	 
	  if($resource_list)
	  {
	    $response["resource"] = $resource_list;
	  }
    
	  $purpose_list = get_terms( 'purpose', 'orderby=count&hide_empty=0');	 
	  if($purpose_list)
	  {
	    $response["purpose"] = $purpose_list;
	  }
      }
	
      $response["code"] = 200;
      $response["msg"] = $this->_ErrorCode[200];
      return $response;
  }    
    
  /* This method edit avatar of user*/
  public function edit_avatar()
  {
      global $wpdb;
      global $json_api;
      
      $login_id = (int)$json_api->query->login_id;
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
      else
      {
	  if ( ! empty( $_FILES['wp-user-avatars']['name'] ) )
	  {       
	      if ( ! function_exists( 'wp_handle_upload' ) )
	      {
		  require_once( ABSPATH . 'wp-admin/includes/file.php' );
	      }
	      $ext = end(explode(".",$_FILES['wp-user-avatars']['name']));
	      $_FILES['wp-user-avatars']['name'] = md5($login_id.'-'.time().'-'.uniqid()).'.'.$ext;
	      
	      // Handle upload
	      $avatar = wp_handle_upload( $_FILES['wp-user-avatars'], array(
		      'mimes' => array(
			      'jpg|jpeg|jpe' => 'image/jpeg',
			      'gif'          => 'image/gif',
			      'png'          => 'image/png',
		      ),
		      'test_form' => false,
		      'unique_filename_callback' => 'wp_user_avatars_unique_filename_callback'
	      ) );
	  
	      if(!empty($avatar["error"]))
	      {
		$response["code"] = 245;
		$response["msg"] = $this->_ErrorCode[245];
	      }
	      else
	      {
		$response["code"] = 200;
		$response["msg"] = $this->_ErrorCode[200];
		$response["avatar_url"] = $avatar['url'];
		self::wp_user_avatars_update_avatar( $login_id, $avatar['url'] );		  
	      }
	  }
	  else
	  {
	    $response["code"] = 245;
	    $response["msg"] = $this->_ErrorCode[245];
	  }
      }
      return $response;
  }

  /* This method edit avatar of user*/
  public function edit_avatar_debug()
  {
      global $wpdb;
      global $json_api;
      
      $login_id = (int)$json_api->query->login_id;
      $cookie = $json_api->query->cookie;
      
      //echo "<pre>";
      print_r($_REQUEST);
      
      
      $response["_REQUEST"] = $_REQUEST;
      $response["_FILES"] = $_FILES;
      
      if ( ! empty( $_FILES['wp-user-avatars']['name'] ) )
      {       
	  if ( ! function_exists( 'wp_handle_upload' ) )
	  {
	      require_once( ABSPATH . 'wp-admin/includes/file.php' );
	  }
	  
	  $ext = end(explode(".",$_FILES['wp-user-avatars']['name']));
	  $_FILES['wp-user-avatars']['name'] = md5($login_id.'-'.time().'-'.uniqid()).'.'.$ext;
	  
	  print_r($_FILES);
	  die;
	  
	  // Handle upload
	  $avatar = wp_handle_upload( $_FILES['wp-user-avatars'], array(
		  'mimes' => array(
			  'jpg|jpeg|jpe' => 'image/jpeg',
			  'gif'          => 'image/gif',
			  'png'          => 'image/png',
		  ),
		  'test_form' => false,
		  'unique_filename_callback' => 'wp_user_avatars_unique_filename_callback'
	  ) );
      
	  if(!empty($avatar["error"]))
	  {
	    $response["code"] = 245;
	    $response["msg"] = $this->_ErrorCode[245];
	  }
	  else
	  {
	    $response["code"] = 200;
	    $response["msg"] = $this->_ErrorCode[200];
	    $response["avatar_url"] = $avatar['url'];
	    //self::wp_user_avatars_update_avatar( $login_id, $avatar['url'] );		  
	  }
      }
	  
      
      return $response;
  }
  
  /* This method delete the avatar of user*/
  public function delete_avatar()
  {
      global $wpdb;
      global $json_api;    
      $login_id = (int)$json_api->query->login_id;
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
      else
      {	
	self::wp_user_avatars_delete_avatar( $login_id );
	$response['msg'] = $this->_ErrorCode[200];
	$response["code"] = 200;
      }
      return $response;
  }
    
  private function wp_user_avatars_update_avatar( $user_id, $media )
  {
      self::wp_user_avatars_delete_avatar( $user_id );
  
      #Setup empty meta array
      $meta_value = array();

      #Set the attachment URL
      if ( is_int( $media ) )
      {
	  $meta_value['media_id'] = $media;
	  $media = wp_get_attachment_url( $media );
      }

      #Set full value to media URL
      $meta_value['full'] = esc_url_raw( $media );
      
      #Update user metadata
      update_user_meta( $user_id, 'wp_user_avatars', $meta_value );
  }
    
  
  private function wp_user_avatars_delete_avatar( $user_id = 0 )
  {
      #Bail if no avatars to delete
      $old_avatars = (array) get_user_meta( $user_id, 'wp_user_avatars', true );     
      if ( empty( $old_avatars ) ) {
	  return;
      }

      #Don't erase media library files
      if ( array_key_exists( 'media_id', $old_avatars ) )
      {
	  unset( $old_avatars['media_id'], $old_avatars['full'] );
      }
  
      #Are there files to delete?
      if ( ! empty( $old_avatars ) )
      {
	  $upload_path = wp_upload_dir();
	  #Loop through avatars
	  foreach($old_avatars as $old_avatar)
	  {
	    #Use the upload directory
	    $old_avatar_path = str_replace($upload_path['baseurl'],$upload_path['basedir'],$old_avatar);	    
	    #Maybe delete the file	    
	    if( file_exists( $old_avatar_path ) )
	    {	      
	      unlink( $old_avatar_path );
	    }	    
	  }
      }
      
      #Remove metadata
      delete_user_meta( $user_id, 'wp_user_avatars' );
  }
  
  
  public function testUpload()
  {
      global $json_api;    
  
  
      echo "This is testing apis......";die;
      
      /*
      $login_id = isset($json_api->query->login_id)?$json_api->query->login_id:39;
      $email = isset($json_api->query->email)?$json_api->query->email:"vishal.khanjan@gmail.com";
      $pass = isset($json_api->query->pass)?$json_api->query->pass:"123456";
      
      $user_id = 1;
      $password = 'HelloWorld';
      wp_set_password( $password, $login_id );
      */

      
      //reset_password($login_id, $pass);
      //wp_set_password($pass, $login_id);

      
      /*
      $wp_get_soc_user_avatar_new = self::wp_get_soc_user_avatar_new($login_id,$email);
      print_r($wp_get_soc_user_avatar_new);
      die;
      */
      
      return $response;

  }

}

function add_soc_controller($controllers)
{
    $controllers[] = 'soc';
    return $controllers;
}
add_filter('json_api_controllers', 'add_soc_controller');

function set_soc_controller_path()
{
    return get_stylesheet_directory() . "/soc.php";
}
add_filter('json_api_soc_controller_path', 'set_soc_controller_path');
