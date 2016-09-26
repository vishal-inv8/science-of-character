<?php
/*
Controller name: Education Controller
Controller description: JSON API Education custom controller for education hub module
*/

class JSON_API_Education_Controller
{
    private $_ErrorCode = array();
    private $perPageRecord = 100;
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
	    'desc'=>"This controller is created for Education hub module"
        );
    }
    
    public function get_taxonomy()
    {
	global $json_api;
	global $wpdb;
	
	$login_id = (int)$json_api->query->login_id;
	$cookie = $json_api->query->cookie;
	
	$response["ages"] = array();
	$response["strengths"] = array();
	$response["resource"] = array();
	$response["purpose"] = array();	
	$ages_list = get_terms( 'ages', 'orderby=term_id&hide_empty=false');	 
	
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
	
	$response["code"] = 200;
	$response["msg"] = $this->_ErrorCode[200];
	return $response;
    }
    
    public function filter()
    {
	global $wpdb;
	global $json_api;
	
	$login_id = (int)$json_api->query->login_id;
	$cookie = $json_api->query->cookie;
	
	$ages = $json_api->query->ages;
	$strengths = $json_api->query->strengths;
	$resource = $json_api->query->resource;
	$purpose = $json_api->query->purpose;
	$search = $json_api->query->search;
	$jewish = $json_api->query->jewish;
	
	$page = (isset($json_api->query->page))?(int)$json_api->query->page:1;
	$per_page_record = $this->perPageRecord;
	$args = array(
	  'post_type'     => 'content',
	  'post_status'   => 'publish',
	  'posts_per_page'  => $per_page_record,
	  'paged' => $page,
	);	
	if($ages !== '')
	{
	  $args['ages'] = $ages;
	}	
	if($strengths !== '')
	{
	  $args['strengths'] = $strengths;
	}	
	if($resource !== '')
	{
	    $resource_terms = get_terms( array(
	      'taxonomy' => 'resource',
	      'slug' => $resource,
	    ));
	    $args['posts_per_page'] = $per_page_record;
	}
	else
	{
	  $resource_terms = get_terms( 'resource', 'orderby=name&order=ASC');
	}	
	if($purpose !== '')
	{
	  $args['purpose'] = $purpose;
	}	
	if($jewish != '')
	{
	  $args['jewish'] = $jewish;
	}	
	if($search != '')
	{
	  $args['s'] = $search;
	}
	
	foreach($resource_terms as $resource_term)
	{
	  $args['resource'] = $resource_term->slug;
	  $term_result = null;
	  $term_result = new WP_Query($args);
	  $results[] = array(
	    'resource_term' => $resource_term,
	    'query_result' => $term_result,
	  );
	}
	
	foreach($results as $result)
	{
	    $resource_term = $result["resource_term"];	    
	    $query_result = $result["query_result"];	    
	    if( $query_result->have_posts() ) : 
		$searchRow = array();
		while ( $query_result->have_posts() ) : $query_result->the_post();		
			$searchRow["name"] = $resource_term->name;			
			$searchRow["post_id"] = get_the_ID();
			$searchRow["post_title"] = get_the_title();
			$searchRow["post_content"] = self::remove_html_tag(get_the_content());
			$searchRow["post_thumbnail"] = (string)get_the_post_thumbnail_url( null, "thumbnail", '' );
			$searchRow["post_weburl"] = (string)get_permalink( get_the_ID());
			
			$get_post_taxonomy = $this->get_post_taxonomy(get_the_ID());
			$searchRow["ages_list"] = $get_post_taxonomy['ages_list'];
			$searchRow["strengths_list"] = $get_post_taxonomy['strengths_list'];
			$searchRow["resource_list"] = $get_post_taxonomy['resource_list'];
			$searchRow["purpose_list"] = $get_post_taxonomy['purpose_list'];
			
			$response["results"][] = $searchRow;
			
		endwhile;
	    endif;
	    wp_reset_query();
	}
	
	if(count($response["results"]) > 0)
	{
	    $response["code"] = 200;
	    $response["msg"] = $this->_ErrorCode[200];    
	}
	else
	{
	   $json_api->error($this->_ErrorCode[209],209);
	}
	return $response;
    }
    
   
    
    public function detail()
    {
	global $wpdb;
	global $json_api;
	
	if(!$json_api->query->ID)
	{
            $json_api->error($this->_ErrorCode[237],237);
        }
	
	$post_detail = get_post($json_api->query->ID);
	
	if($post_detail)
	{
	    $response["detail"]["post_id"] =  $post_detail->ID;
	    $response["detail"]["post_title"] =  $post_detail->post_title;    
	    $response["detail"]["post_content"] =  self::remove_html_tag($post_detail->post_content);
	    $response["detail"]["post_thumbnail"] = (string)get_the_post_thumbnail_url( $post_detail->ID, "thumbnail", '' );
	    $response["detail"]["post_weburl"] = (string)get_permalink( $post_detail->ID);
	    
	    $get_post_taxonomy = $this->get_post_taxonomy($post_detail->ID);
	    
	    $response["detail"]["ages_list"] = $get_post_taxonomy["ages_list"];			
	    $response["detail"]["strengths_list"] = $get_post_taxonomy["strengths_list"];
	    $response["detail"]["resource_list"] = $get_post_taxonomy["resource_list"];			
	    $response["detail"]["purpose_list"] = $get_post_taxonomy["purpose_list"];	    
	    
	    $response["code"] = 200;
	    $response["msg"] = $this->_ErrorCode[200];
	    observePostViews($post_detail->ID);
	}
	else
	{
	    $response["code"] = 209;
	    $response["msg"] = $this->_ErrorCode[209];
	}
	return $response;
    }
   
   
    public function guest_detail()
    {
	global $wpdb;
	global $json_api;
	
	if(!$json_api->query->ID)
	{
            $json_api->error($this->_ErrorCode[237],237);
        }
	
	$post_detail = get_post($json_api->query->ID);
	
	if($post_detail)
	{
	    $response["detail"]["post_id"] =  $post_detail->ID;
	    $response["detail"]["post_title"] =  $post_detail->post_title;    
	    $response["detail"]["post_content"] =  self::remove_html_tag($post_detail->post_content);
	    $response["detail"]["post_thumbnail"] = (string)get_the_post_thumbnail_url( $post_detail->ID, "thumbnail", '' );
	    $response["detail"]["post_weburl"] = (string)get_permalink( $post_detail->ID);	    
	    
	    $get_post_taxonomy = $this->get_post_taxonomy($post_detail->ID);	    
	    
	    $response["detail"]["ages_list"] = $get_post_taxonomy["ages_list"];			
	    $response["detail"]["strengths_list"] = $get_post_taxonomy["strengths_list"];
	    $response["detail"]["resource_list"] = $get_post_taxonomy["resource_list"];			
	    $response["detail"]["purpose_list"] = $get_post_taxonomy["purpose_list"];	    
	    $response["code"] = 200;
	    $response["msg"] = $this->_ErrorCode[200];
	    observePostViews($post_detail->ID);
	}
	else
	{
	    $response["code"] = 209;
	    $response["msg"] = $this->_ErrorCode[209];
	}
	return $response;
    }
    
    public function search()
    {
	global $wpdb;
	global $json_api;	
	$response["recent"] = array();
	$response["popular"] = array();
	$text = $json_api->query->text;
	$page = (isset($json_api->query->page))?(int)$json_api->query->page:1;
	$per_page_record = $this->perPageRecord;    
	if(!empty($text))
	{	    
	    $educationObjSet =
	    $wpdb->get_results
	    (
		"SELECT
		P.ID,P.post_title,P.post_content,P.post_date,PM.meta_key,PM.meta_value
		FROM $wpdb->posts AS P
		LEFT JOIN $wpdb->postmeta AS PM ON (PM.post_id = P.ID AND PM.meta_key='wp_post_views_count') 
		WHERE P.post_title LIKE '%".$text."%' AND P.post_type='content' AND P.post_status='publish'
		ORDER BY P.post_date DESC"
	    );
	}
	
	foreach($educationObjSet as $educationObjSetRow)
	{	   
	    $searchRow = array();
	    $searchRow["post_id"] = $educationObjSetRow->ID;
	    $searchRow["post_title"] = $educationObjSetRow->post_title;
	    $searchRow["post_content"] = self::remove_html_tag($educationObjSetRow->post_content);
	    $searchRow["post_thumbnail"] = (string)get_the_post_thumbnail_url( $educationObjSetRow->ID, "thumbnail", '' );
	    $searchRow["post_weburl"] = (string)get_permalink( $educationObjSetRow->ID);
	    $searchRow["post_date"] = $educationObjSetRow->post_date;
	    $searchRow["meta_key"] = $educationObjSetRow->meta_key;
	    $searchRow["meta_value"] = $educationObjSetRow->meta_value;
	    
	    $get_post_taxonomy = $this->get_post_taxonomy($educationObjSetRow->ID);
	    $searchRow["ages_list"] = $get_post_taxonomy['ages_list'];
	    $searchRow["strengths_list"] = $get_post_taxonomy['strengths_list'];
	    $searchRow["resource_list"] = $get_post_taxonomy['resource_list'];
	    $searchRow["purpose_list"] = $get_post_taxonomy['purpose_list'];		
	    //$response["results"][] = $searchRow; 
	    $response["recent"][] = $searchRow;
	    $response["popular"][] = $searchRow;
	}
	
	//array_multisort($response["popular"],SORT_DESC,"meta_value");
	//echo "<pre>";
	//print_r($response);
	//array_multisort()
	//die;
    
	if(count($response["recent"]) > 0 || count($response["popular"]) > 0)
	{
	    $response["code"] = 200;
	    $response["msg"] = $this->_ErrorCode[200];    
	}
	else
	{
	   $json_api->error($this->_ErrorCode[209],209);
	}
	return $response;
    }
    
    public function popular()
    {
	global $wpdb;
	global $json_api;
	
	$response["recent"] = array();
	$response["popular"] = array();	
	
	$_records_to_fetch = 50;
	
	$args = array(
	    'numberposts' => $_records_to_fetch,
	    'orderby' => 'post_date',
	    'order' => 'DESC',
	    'post_type' => 'content',
	    'post_status' => 'publish',
	    'suppress_filters' => true
	);
	
	$recent_posts = get_posts( $args, ARRAY_A );
	
	if($recent_posts)
	{
	    $ages_list = array();
	    $strengths_list = array();
	    $resource_list = array();
	    $purpose_list = array();

	    foreach($recent_posts as $recent_post)
	    {
		$get_post_taxonomy = $this->get_post_taxonomy($recent_post->ID);
		$recent_post_loop[] = array("post_id"=>$recent_post->ID,
					    "post_title"=>$recent_post->post_title,
					    "post_content"=>self::remove_html_tag($recent_post->post_content),
					    "post_thumbnail"=>(string)get_the_post_thumbnail_url( $recent_post->ID, "thumbnail", '' ),
					    "post_weburl"=>(string)get_permalink( $recent_post->ID),
					    "post_date"=>$recent_post->post_date,
					   "ages_list"=>$get_post_taxonomy['ages_list'],
					    "strengths_list"=>$get_post_taxonomy['strengths_list'],
					    "resource_list"=>$get_post_taxonomy['resource_list'],
					    "purpose_list"=>$get_post_taxonomy['purpose_list']
					);
	    }
	    
	    $response["recent"] = $recent_post_loop;
	}
	
	$args["meta_key"] = "wp_post_views_count";
	$args["orderby"] = "wp_post_views_count";
	
	$popular_posts = get_posts($args, ARRAY_A);	
	if($popular_posts)
	{
	    $ages_list = array();
	    $strengths_list = array();
	    $resource_list = array();
	    $purpose_list = array();
	    
	    foreach($popular_posts as $popular_post)
	    {
		$get_post_taxonomy = $this->get_post_taxonomy($popular_post->ID);
		$popular_post_loop[] = array(
					"post_id"=>$popular_post->ID,
					"post_title"=>$popular_post->post_title,
					"post_content"=>self::remove_html_tag($popular_post->post_content),
					"post_thumbnail"=>(string)get_the_post_thumbnail_url( $popular_post->ID, "thumbnail", '' ),
					"post_weburl"=>(string)get_permalink( $popular_post->ID),
					"post_date"=>$popular_post->post_date,
					"ages_list"=>$get_post_taxonomy['ages_list'],
					"strengths_list"=>$get_post_taxonomy['strengths_list'],
					"resource_list"=>$get_post_taxonomy['resource_list'],
					"purpose_list"=>$get_post_taxonomy['purpose_list']
				    );
		
	    }	    
	    $response["popular"] = $popular_post_loop;	    
	}
	
	if($response["popular"] || $response["recent"])
	{
	    $response["code"] = 200;
	    $response["msg"] = $this->_ErrorCode[200];  
	}
	else
	{
	    $response["code"] = 209;
	    $response["msg"] = $this->_ErrorCode[209];  
	}
	return $response;
    }
    
    public function guest_popular()
    {
	global $wpdb;
	global $json_api;
	$response["recent"] = array();
	$response["popular"] = array();
	$args = array('numberposts'=>10,'orderby'=>'post_date','order'=>'DESC','post_type'=>'content','post_status'=>'publish','suppress_filters'=>true);	
	$recent_posts = get_posts( $args, ARRAY_A );	
	if($recent_posts)
	{
	    $ages_list = array();
	    $strengths_list = array();
	    $resource_list = array();
	    $purpose_list = array();
	    foreach($recent_posts as $recent_post)
	    {
		$get_post_taxonomy = $this->get_post_taxonomy($recent_post->ID);
		$recent_post_loop[] = array("post_id"=>$recent_post->ID,
					    "post_title"=>$recent_post->post_title,
					    "post_content"=>self::remove_html_tag($recent_post->post_content),
					    "post_thumbnail"=>(string)get_the_post_thumbnail_url( $recent_post->ID, "thumbnail", '' ),
					    "post_weburl"=>(string)get_permalink( $recent_post->ID),
					    "post_date"=>$recent_post->post_date,
					   "ages_list"=>$get_post_taxonomy['ages_list'],
					    "strengths_list"=>$get_post_taxonomy['strengths_list'],
					    "resource_list"=>$get_post_taxonomy['resource_list'],
					    "purpose_list"=>$get_post_taxonomy['purpose_list']
					);
	    }	    
	    $response["recent"] = $recent_post_loop;
	}	
	$args["meta_key"] = "wp_post_views_count";
	$args["orderby"] = "wp_post_views_count";	
	$popular_posts = get_posts($args, ARRAY_A);	
	if($popular_posts)
	{
	    $ages_list = array();
	    $strengths_list = array();
	    $resource_list = array();
	    $purpose_list = array();	    
	    foreach($popular_posts as $popular_post)
	    {
		$get_post_taxonomy = $this->get_post_taxonomy($popular_post->ID);
		$popular_post_loop[] = array(
					"post_id"=>$popular_post->ID,
					"post_title"=>$popular_post->post_title,
					"post_content"=>self::remove_html_tag($popular_post->post_content),
					"post_thumbnail"=>(string)get_the_post_thumbnail_url( $popular_post->ID, "thumbnail", '' ),
					"post_weburl"=>(string)get_permalink( $popular_post->ID),
					"post_date"=>$popular_post->post_date,
					"ages_list"=>$get_post_taxonomy['ages_list'],
					"strengths_list"=>$get_post_taxonomy['strengths_list'],
					"resource_list"=>$get_post_taxonomy['resource_list'],
					"purpose_list"=>$get_post_taxonomy['purpose_list']
				    );
		
	    }	    
	    $response["popular"] = $popular_post_loop;	    
	}	
	if($response["popular"] || $response["recent"])
	{
	    $response["code"] = 200;
	    $response["msg"] = $this->_ErrorCode[200];  
	}
	else
	{
	    $response["code"] = 209;
	    $response["msg"] = $this->_ErrorCode[209];  
	}
	return $response;
    }    
    
    public function guest_filter()
    {
	global $wpdb;
	global $json_api;
	
	$ages = $json_api->query->ages;
	$strengths = $json_api->query->strengths;
	$resource = $json_api->query->resource;
	$purpose = $json_api->query->purpose;
	$search = $json_api->query->search;
	$jewish = $json_api->query->jewish;
	
	$page = (isset($json_api->query->page))?(int)$json_api->query->page:1;
	$per_page_record = $this->perPageRecord;
	$args = array(
	  'post_type'     => 'content',
	  'post_status'   => 'publish',
	  'posts_per_page'  => $per_page_record,
	  'paged' => $page,
	);	
	if($ages !== '')
	{
	  $args['ages'] = $ages;
	}	
	if($strengths !== '')
	{
	  $args['strengths'] = $strengths;
	}	
	if($resource !== '')
	{
	    $resource_terms = get_terms( array(
	      'taxonomy' => 'resource',
	      'slug' => $resource,
	    ));
	    $args['posts_per_page'] = $per_page_record;
	}
	else
	{
	  $resource_terms = get_terms( 'resource', 'orderby=name&order=ASC');
	}	
	if($purpose !== '')
	{
	  $args['purpose'] = $purpose;
	}	
	if($jewish != '')
	{
	  $args['jewish'] = $jewish;
	}	
	if($search != '')
	{
	  $args['s'] = $search;
	}
	
	foreach($resource_terms as $resource_term)
	{
	  $args['resource'] = $resource_term->slug;
	  $term_result = null;
	  $term_result = new WP_Query($args);
	  $results[] = array(
	    'resource_term' => $resource_term,
	    'query_result' => $term_result,
	  );
	}
	
	foreach($results as $result)
	{
	    $resource_term = $result["resource_term"];	    
	    $query_result = $result["query_result"];	    
	    if( $query_result->have_posts() ) : 
		$searchRow = array();
		while ( $query_result->have_posts() ) : $query_result->the_post();		
			$searchRow["name"] = $resource_term->name;			
			$searchRow["post_id"] = get_the_ID();
			$searchRow["post_title"] = get_the_title();
			$searchRow["post_content"] = self::remove_html_tag(get_the_content());
			$searchRow["post_thumbnail"] = (string)get_the_post_thumbnail_url( null, "thumbnail", '' );
			$searchRow["post_weburl"] = (string)get_permalink( get_the_ID());
			
			$get_post_taxonomy = $this->get_post_taxonomy(get_the_ID());
			$searchRow["ages_list"] = $get_post_taxonomy['ages_list'];
			$searchRow["strengths_list"] = $get_post_taxonomy['strengths_list'];
			$searchRow["resource_list"] = $get_post_taxonomy['resource_list'];
			$searchRow["purpose_list"] = $get_post_taxonomy['purpose_list'];
			
			$response["results"][] = $searchRow;
			
		endwhile;
	    endif;
	    wp_reset_query();
	}
	
	if(count($response["results"]) > 0)
	{
	    $response["code"] = 200;
	    $response["msg"] = $this->_ErrorCode[200];    
	}
	else
	{
	   $json_api->error($this->_ErrorCode[209],209);
	}
	return $response;
    }
    
    /* Custom Method for post taxonomy*/
    public function get_post_taxonomy($ID)
    {
	global $wpdb;
	global $json_api;
	
	$ages_list = $strengths_list = $resource_list = $purpose_list = array();
	$response = array("ages_list"=>$ages_list,"strengths_list"=>$strengths_list,"resource_list"=>$resource_list,"purpose_list"=>$purpose_list);
	if($ID)
	{
	    $ages_list_item = explode(",",strip_tags(get_the_term_list($ID, 'ages','',',','')));		
	    if(count($ages_list_item) > 0 && $ages_list_item[0])
	    {
		$ages_list = $ages_list_item;
	    }
	    
	    $strengths_list_item = explode(",",strip_tags(get_the_term_list($ID, 'strengths','',',','')));		
	    if(count($strengths_list_item) > 0 && $strengths_list_item[0])
	    {
		$strengths_list = $strengths_list_item;
	    }
	    
	    $resource_list_item = explode(",",strip_tags(get_the_term_list($ID, 'resource','',',','')));		
	    if(count($resource_list_item) > 0 && $resource_list_item[0])
	    {
		$resource_list = $resource_list_item;
	    }
	    
	    $purpose_list_item = explode(",",strip_tags(get_the_term_list($ID, 'purpose','',',','')));		
	    if(count($purpose_list_item) > 0 && $purpose_list_item[0])
	    {
		$purpose_list = $purpose_list_item;
	    }	
	}
	$response = array("ages_list"=>$ages_list,"strengths_list"=>$strengths_list,"resource_list"=>$resource_list,"purpose_list"=>$purpose_list);
	return $response;
    }
    
    private function remove_html_tag($content)
    {
	return strip_tags($content);
    }
}

function add_education_controller($controllers){
    $controllers[] = 'education';
    return $controllers;
}
add_filter('json_api_controllers', 'add_education_controller');
function set_education_controller_path(){
    return get_stylesheet_directory() . "/education.php";
}
add_filter('json_api_education_controller_path', 'set_education_controller_path');

