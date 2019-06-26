<?php
class SpartanNash_SocialCenter_FacebookShare {
	function Action_FacebookShare(){
		if(isset($_POST['fbtopagepost'])){
			session_start();
			$current_user_id = get_current_user_id();
			$all_meta_for_user = get_user_meta( $current_user_id );
			

			//$_fb_app_id = $all_meta_for_user['sc_fb_app_id'][0];
			$_fb_app_id = '524022474717135';
			//$_sc_facebook_app_secret = $all_meta_for_user['sc_facebook_app_secret'][0];
			$_sc_facebook_app_secret = 'cb425a76db31161c5184577028bfa3d8';
			
			//$user_choose_fb_page_id = get_user_meta($current_user_id, "user_choose_fb_page", true );
			$pages = get_user_meta($current_user_id, "sc_fb_pages", true );
			
			/*print_r($pages);
			exit;*/
			if (!empty($pages)) {
				$pages = json_decode($pages);
				foreach ($pages as $key => $value) {
					if($_POST[$key] == '1')
					{
						$_fb_page_access_token[$key] = $value->access_token;			}
					}

			}

			//echo $_fb_page_access_token.'<br/>';
			//echo $user_choose_fb_page_id; 
			//exit;
			//$_fb_page_access_token = $all_meta_for_user['sc_fb_page_access_token'][0];

			if($_fb_page_access_token != '' ){
				
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

				$FacebookPostVideo = get_field('facebook_post_video', $_content_post);
				if ( $FacebookPostVideo != '' ) {
					// Then this is a video post.
					$FacebookPostType = 'Video';
					$object_attachment = $FacebookPostVideo;
					// The following code allows testing in an offline environment.
					// To turn things back to normal, comment out the following line.
					//$object_attachment = 'http://socialcenterdev.flywheelsites.com/wp-content/uploads/2019/06/SampleVideo_360x240_1mb.mp4';
				}
				else
				{
					// Then this is an image post.
					$FacebookPostType = 'Image';
					// The following line allows testing in an offline environment.
					// $object_attachment = 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/66/SMPTE_Color_Bars.svg/1200px-SMPTE_Color_Bars.svg.png';
					// To turn things back to normal, comment out the line above, and uncomment the line below.
					$object_attachment =  get_the_post_thumbnail_url($_postid,'full');
				}

				if ( $object_attachment == '' )
				{
					$FacebookPostType = 'Text';
				}

				//$page_access_token = $_fb_page_access_token;
				//$page_id = $_fb_app_id;
				$StatusMessages = '';

				foreach ($_fb_page_access_token as $PageId => $PageAccessToken)
				{
					$fb = new Facebook\Facebook([
					'app_id' => $_fb_app_id,
					'app_secret' => $_sc_facebook_app_secret,
					'default_graph_version' => 'v2.10',
					]);

					if ($timestamp) {
						$linkData['published'] = false;
						$linkData['scheduled_publish_time'] = $timestamp;
					}

					/*$photolinkData = [
						'published'=> false,
						'temporary'=>true,
						'url' =>$object_attachment,
					];*/

					if ( $FacebookPostType == 'Image' )
					{
						$PhotoLinkData = [
							'published' => false,
							'temporary' => true,
							'url' => $object_attachment,
						];

						try {
							//$response_photo = $fb->post('/'.$PageId.'/photos', $photolinkData, $PageAccessToken	);
							$response = $fb->post( '/'.$PageId.'/photos', $PhotoLinkData, $PageAccessToken );
						} catch(Facebook\Exceptions\FacebookResponseException $e) {
							// echo 'Facebook SDK returned an error: '.$e->getMessage();
							//setcookie("error", true, time()+ 5,'/'); // expires after 
							//setcookie("msg", __('Facebook SDK returned an error: ', 'wp-to-fb-post').$object_attachment.$e->getMessage(), time()+ 5,'/'); // expires after
							// $_SESSION['fb_status']['error'] = true;
							// $_SESSION['fb_status']['msg'] = __('Facebook SDK returned an error: ', 'wp-to-fb-post').$e->getMessage();
							//header("Refresh:0");
							//exit();
							$StatusMessages .= 'Error,'.$PageId.',Facebook Response Exception,'.$e->getMessage().','.$object_attachment.';';
							continue;
						}

						$GraphNode = $response->getGraphObject();

						$linkData['attached_media[0]'] = '{"media_fbid":"'.$GraphNode->getProperty("id").'"}';
						$linkData['message'] = "$the_content\n$_post_url";
					}

					if ( $FacebookPostType == 'Video' )
					{
						
						$linkData['description'] = "$the_content\n$_post_url";
						$linkData['file_url'] = $object_attachment;
						
						try
						{
							$response = $fb->post( '/'.$PageId.'/videos', $linkData, $PageAccessToken );
						}
						catch (Facebook\Exceptions\FacebookResponseException $e)
						{
							$StatusMessages .= 'Error,'.$PageId.',Facebook Response Exception,'.$e->getMessage().','.$object_attachment.';';
							continue;
						}

						$StatusMessages .= 'Success,'.$PageId.';';
						continue;
						
					}

					if ( $FacebookPostType == 'Text' )
					{
						$linkData['link'] = $_post_url;
				
						/*//Post property to Facebook
						$linkData = [
							// 'picture' => $object_attachment,
							'message' => $the_content
						];*/

						$linkData['message'] = $the_content;
					}

					if ( $FacebookPostType == 'Image' || $FacebookPostType == 'Text' )
					{
						//$pageAccessToken = $_fb_page_access_token;

						try {
							$response = $fb->post('/'.$PageId.'/feed', $linkData, $PageAccessToken);
						} catch(Facebook\Exceptions\FacebookResponseException $e) {
							// echo 'Graph returned an error: '.$e->getMessage();
							// $_SESSION['fb_status']['error'] = true;
							//setcookie("error", true, time()+ 5,'/'); // expires after 
							//setcookie("msg", __('Your Facebook Application Error: ', 'wp-to-fb-post').$e->getMessage(), time()+ 5,'/'); // expires after 
							// $_SESSION['fb_status']['msg'] = __('Your Facebook Application Error: ', 'wp-to-fb-post').$e->getMessage();
							//header("Refresh:0");
							//exit();
							$StatusMessages .= 'Error,'.$PageId.',Facebook Response Exception,'.$e->getMessage().';';
							continue;
						} catch(Facebook\Exceptions\FacebookSDKException $e) {
							// echo 'Facebook SDK returned an error: '.$e->getMessage();
							//setcookie("error", true, time()+ 5,'/'); // expires after 
							//setcookie("msg", __('Facebook SDK returned an error: ', 'wp-to-fb-post').$e->getMessage(), time()+ 5,'/'); // expires after 
							// $_SESSION['fb_status']['error'] = true;
							// $_SESSION['fb_status']['msg'] = __('Facebook SDK returned an error: ', 'wp-to-fb-post').$e->getMessage();
							//header("Refresh:0");
							//exit();
							$StatusMessages .= 'Error,'.$PageId.',Facebook SDK Exception,'.$e->getMessage().';';
							continue;
						}
						$graphNode = $response->getGraphNode();
						//setcookie("success", true, time()+ 5,'/'); // expires after 
						//setcookie("msg", __('Your post successfully updated to your page ', 'wp-to-fb-post'), time()+ 5,'/'); // expires after 
						// $_SESSION['fb_status']['success'] = true;
						// $_SESSION['fb_status']['msg'] = __('Your post successfully updated to your page', 'wp-to-fb-post');
						//header("Refresh:0");
						//exit();
						$StatusMessages .= 'Success,'.$PageId.';';
						continue;
					}
				}
				// Transmit collected status messages via a cookie.
				if ( $StatusMessages != '' )
				{
					setcookie("StatusMessages", $StatusMessages, time()+ 5,'/');
					header("Refresh:0");
					exit();
				}
			}
		}
	}
}