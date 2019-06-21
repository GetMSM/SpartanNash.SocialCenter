<?php
function Facebook_Post(){
	if(isset($_POST['fbtopagepost'])){
		session_start();
		$current_user_id = get_current_user_id();
		$all_meta_for_user = get_user_meta( $current_user_id );
		

		//$_fb_app_id = $all_meta_for_user['sc_fb_app_id'][0];
		$_fb_app_id = '524022474717135';
		//$_sc_facebook_app_secret = $all_meta_for_user['sc_facebook_app_secret'][0];
		$_sc_facebook_app_secret = 'cb425a76db31161c5184577028bfa3d8';
		
		$user_choose_fb_page_id = get_user_meta($current_user_id, "user_choose_fb_page", true );
		$pages = get_user_meta($current_user_id, "sc_fb_pages", true );
		
		/*print_r($pages);
		exit;*/
		if (!empty($pages)) {
			$pages = json_decode($pages);
			foreach ($pages as $key => $value) {
				if($key == $user_choose_fb_page_id)
				{
					$_fb_page_access_token = $value->access_token;			}
				}

		}
		//echo $_fb_page_access_token.'<br/>';
		//echo $user_choose_fb_page_id; 
		//exit;
		//$_fb_page_access_token = $all_meta_for_user['sc_fb_page_access_token'][0];

		if($_fb_page_access_token != '' && $user_choose_fb_page_id !='' ){
			
			$_postid = $_POST['the_post_id'];
			$timestamp = false;

			if (isset($_POST['schedule_post']) && $_POST['schedule_post'] != '' && $_POST['schedule_date'] != '') {
				date_default_timezone_set("America/New_York"); //Added by Ian 190503Fr1125. This will need to be removed, see below. 
				$schedule_date = $_POST['schedule_date']; // This will need to be changed, see below.
				$timestamp = strtotime($schedule_date);  // This will need to be removed when we make this conversion client-side in the future.
			}
			
			//This is page id or post id
			$_content_post = get_post($_postid);
			
			$_post_url = $_POST['wptfbp_url'];
			// $_post_title = $_POST['wptfbp_title'];
			// if(empty($_post_title)){
			// 	$_post_title = $_content_post->post_title;	
			// }
			$_post_content = $_POST['editpost'];
			if(empty($_post_content)){
				$_post_content = $_content_post->post_content;
			}
			$content = apply_filters('the_content', $_post_content);
			$post_content = str_replace(']]>', '&gt;', $_post_content);
			// $the_content = $_post_title .' '. stripslashes(strip_tags($post_content));
			$the_content = stripslashes(strip_tags($post_content));
			$object_attachment =  get_the_post_thumbnail_url($_postid,'full');
			

			$page_access_token = $_fb_page_access_token;
			$page_id = $_fb_app_id;

			$fb = new Facebook\Facebook([
			 'app_id' => $page_id,
			 'app_secret' => $_sc_facebook_app_secret,
			 'default_graph_version' => 'v2.10',
			]);

			//Post property to Facebook
			$linkData = [
				// 'picture' => $object_attachment,
				'message' => $the_content
			];

			if ($object_attachment) {
				$photolinkData = [
					'published'=> false,
					'temporary'=>true,
				 	'url' =>$object_attachment,
				];

				try {
					$response_photo = $fb->post('/'.$user_choose_fb_page_id.'/photos', $photolinkData, $_fb_page_access_token	);
				} catch(Facebook\Exceptions\FacebookResponseException $e) {
					// echo 'Facebook SDK returned an error: '.$e->getMessage();
					setcookie("error", true, time()+ 5,'/'); // expires after 
					setcookie("msg", __('Facebook SDK returned an error: ', 'wp-to-fb-post').$object_attachment.$e->getMessage(), time()+ 5,'/'); // expires after 
					// $_SESSION['fb_status']['error'] = true;
					// $_SESSION['fb_status']['msg'] = __('Facebook SDK returned an error: ', 'wp-to-fb-post').$e->getMessage();
					header("Refresh:0");
					exit();
				}

				$photoGraphNode = $response_photo->getGraphObject();

				$linkData['attached_media[0]'] = '{"media_fbid":"'.$photoGraphNode->getProperty("id").'"}';
				$linkData['message'] = "$the_content\n$_post_url";
			}else{
				$linkData['link'] = $_post_url;
			}

			if ($timestamp) {
				$linkData['published'] = false;
				$linkData['scheduled_publish_time'] = $timestamp;
			}

			$pageAccessToken = $_fb_page_access_token;

			try {
				$response = $fb->post('/'.$user_choose_fb_page_id.'/feed', $linkData, $pageAccessToken);
			} catch(Facebook\Exceptions\FacebookResponseException $e) {
				// echo 'Graph returned an error: '.$e->getMessage();
				// $_SESSION['fb_status']['error'] = true;
				setcookie("error", true, time()+ 5,'/'); // expires after 
				setcookie("msg", __('Your Facebook Application Error: ', 'wp-to-fb-post').$e->getMessage(), time()+ 5,'/'); // expires after 
				// $_SESSION['fb_status']['msg'] = __('Your Facebook Application Error: ', 'wp-to-fb-post').$e->getMessage();
				header("Refresh:0");
				exit();
			} catch(Facebook\Exceptions\FacebookSDKException $e) {
				// echo 'Facebook SDK returned an error: '.$e->getMessage();
				setcookie("error", true, time()+ 5,'/'); // expires after 
				setcookie("msg", __('Facebook SDK returned an error: ', 'wp-to-fb-post').$e->getMessage(), time()+ 5,'/'); // expires after 
				// $_SESSION['fb_status']['error'] = true;
				// $_SESSION['fb_status']['msg'] = __('Facebook SDK returned an error: ', 'wp-to-fb-post').$e->getMessage();
				header("Refresh:0");
				exit();
			}
			$graphNode = $response->getGraphNode();
			setcookie("success", true, time()+ 5,'/'); // expires after 
				setcookie("msg", __('Your post successfully updated to your page ', 'wp-to-fb-post'), time()+ 5,'/'); // expires after 
			// $_SESSION['fb_status']['success'] = true;
			// $_SESSION['fb_status']['msg'] = __('Your post successfully updated to your page', 'wp-to-fb-post');
			header("Refresh:0");
			exit();
		}
	}
}
add_action('init','Facebook_Post');