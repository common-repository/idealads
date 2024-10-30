<?php
/**
 * @package IdealAds
 * @author Leonard Apeltsin
 * @version 1.0
 */
/*
Plugin Name: IdealAds
Plugin URI: http://IdealAds.net/
Description: Insert custom selected Ads anywhere in any blog post. Search for Ads that best match your content in our Ad Database. Includes Amazon Associate Ads.
Author: Leonard Apeltsin
Version: 1.0
Author URI: http://IdealAds.net
*/



/***Begin Functions Pertaining to IdealAds Security ****/

//create hash-value differentiating blog 
function generate_idealads_hash(){
      
	$result = "";
      	$charPool = '0123456789abcdefghijklmnopqrstuvwxyz';
      	for($p = 0; $p<20; $p++)
      	$result .= $charPool[mt_rand(0,strlen($charPool)-1)];
      	return md5(sha1($result));
}


//create options pertaining to unique blog hash value when installing plugin
function initialize_idealads_options() {
      
       
	$hash = generate_idealads_hash();
	add_option('idealads_hash', $hash);
	add_option('idealads_h_acceptance_status',0);
         

	//store current domain in variable which will be permanent domain identifier (in case blog is later shifted to a new domain)
	add_option('idealads_domain',$_SERVER["SERVER_NAME"]);
        
        
       
}


//connect to IdealAds when confirmation not present, register new blog, confirm registration
function connect_and_confirm(){

	$blog_hash = get_option('idealads_hash');
	
	$blog_domain = get_option('idealads_domain');
	
	$url = "http://idealads.net/register_blog.php";
	
	//set POST variables
	$fields = array(
			'hash' => urlencode($blog_hash),
			'domain' => urlencode($blog_domain)
			);

	foreach($fields as $key=>$value)
		$fields_string .= $key.'='.$value.'&';
	
	rtrim($fields_String,'&');
	
	//open connection
	$ch = curl_init();

	//set the url, number of POST vars, POST data
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_POST,count($fields));
	curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	//execute post
	$result = curl_exec($ch);
       	curl_close($ch);
 
        return $result;
}






//checks status. Sets cookie if registered Reconnects with with IdealAds if pending. Prints warning if denied
function check_confirmation_status() {


	$curr_status = get_option('idealads_h_acceptance_status');
        
        //echo "Curr Status: $curr_status</br>";

	if($curr_status == 0) {
 

		//connect to idealads
		$new_status = connect_and_confirm();

                
                $new_status = (int)$new_status;               
 
		if( ($new_status == -1) || ($new_status > 0)){

			$curr_status = $new_status;
			update_option('idealads_h_acceptance_status',$new_status);
		}
	}	

	
		
	//blog connection denied because different hash exists for current blog domain in IdealAds DB
	if($curr_status == -1)
		echo '<p><b>WARNING. THIS BLOG HAS ALREADY BEEN REGISTERED WITH IDEALADS FROM A DIFFERENT SERVER. FOR SECURITY REASONS, PLEASE DEACTIVE THE IDEALADS PLUGIN AND CONTACT AND CONTACT THE PLUGIN ADMINISTRATOR FOR ADDITIONAL INFO. THANK YOU!</b></p>';

}
/**End Functions Pertaining to IdealAds Security ****/
add_action('admin_head', 'check_confirmation_status');


$front_page_links = array();

register_activation_hook(__FILE__,'initialize_idealads_options');


add_action( 'template_redirect', 'start_idealads',1 );



	
function start_idealads() {

      

      if ( is_user_logged_in() ) { 
      
           $curr_status = get_option('idealads_h_acceptance_status');

           $blog_hash = get_option('idealads_hash');

          //blog registered in DB. Add security cookie for External IdealAds server
           if($curr_status > 0)
                  wp_enqueue_script('wp_cookie_link',"http://idealads.net/idealads_cookie_set.php?&h=$blog_hash&id=$curr_status");

         

         
             wp_enqueue_script('admin_script','http://idealads.net/js_plugin_code/admin_multi.js');
       
      }

      else
          wp_enqueue_script('visitor_script','http://idealads.net/js_plugin_code/visitor_multi.js');

     
      
}
?>