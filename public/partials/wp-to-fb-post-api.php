<?php
function load_required_files(){
	if(isset($_POST['fbtopagepost'])){
		session_start();
		$current_user_id = get_current_user_id();
		$all_meta_for_user = get_user_meta( $current_user_id );
		$_fb_app_id = $all_meta_for_user['sc_fb_app_id'][0];
		$_sc_facebook_app_secret = $all_meta_for_user['sc_facebook_app_secret'][0];
		$_fb_page_access_token = $all_meta_for_user['sc_fb_page_access_token'][0];

		if(!empty($_fb_app_id)){
			$_postid = $_POST['the_post_id'];
			
			//This is page id or post id
			$_content_post = get_post($_postid);
			
			$_post_url = $_POST['wptfbp_url'];
			$_post_title = $_content_post->post_title;
			
			$_post_content = $_content_post->post_content;
			$content = apply_filters('the_content', $_post_content);
			$post_content = str_replace(']]>', '&gt;', $_post_content);
			$the_content = $_post_title .' '. strip_tags($post_content);
			// $object_attachment =  get_the_post_thumbnail_url($_postid,'full');
			
			// print_r($object_attachment);
			// exit();

			$page_access_token = $_fb_page_access_token;
			$page_id = $_fb_app_id;

			$fb = new Facebook\Facebook([
			 'app_id' => $page_id,
			 'app_secret' => $_sc_facebook_app_secret,
			 'default_graph_version' => 'v2.10',
			]);
			
			//Post property to Facebook
			$linkData = [
			 'link' => $_post_url,
			 'message' => $the_content,
			 
			];
			$pageAccessToken = $_fb_page_access_token;

			try {
				$response = $fb->post('/me/feed', $linkData, $pageAccessToken);
			} catch(Facebook\Exceptions\FacebookResponseException $e) {
				// echo 'Graph returned an error: '.$e->getMessage();
				$_SESSION['fb_status']['error'] = true;
				$_SESSION['fb_status']['msg'] = __('Your Facebook Application Error: ', 'wp-to-fb-post').$e->getMessage();
				header("Refresh:0");
				exit();
			} catch(Facebook\Exceptions\FacebookSDKException $e) {
				// echo 'Facebook SDK returned an error: '.$e->getMessage();
				$_SESSION['fb_status']['error'] = true;
				$_SESSION['fb_status']['msg'] = __('Facebook SDK returned an error: ', 'wp-to-fb-post').$e->getMessage();
				header("Refresh:0");
				exit();
			}
			$graphNode = $response->getGraphNode();
			$_SESSION['fb_status']['success'] = true;
			$_SESSION['fb_status']['msg'] = __('Your post successfully updated to your page', 'wp-to-fb-post');
			header("Refresh:0");
			exit();
		}
	}
}
add_action('init','load_required_files');