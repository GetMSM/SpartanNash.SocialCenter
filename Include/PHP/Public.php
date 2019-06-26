<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @link       #
 * @since      1.0.0
 * @package    spartan-nash_social-center
 * @subpackage spartan-nash_social-center/Public
 * @author     # <#>
*/
class SpartanNash_SocialCenter_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	*/
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	*/
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	*/
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	*/
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_To_Fb_Post_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_To_Fb_Post_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		*/

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../CSS/wp-to-fb-post-public.css', array(), $this->version, 'all' );

		wp_enqueue_style( 'jquery-ui', plugin_dir_url( __FILE__ ) . '../../Lib/JQuery/jquery-ui.css', array(), $this->version, 'all' );

		wp_enqueue_style( 'ui-date-picker', plugin_dir_url( __FILE__ ) . '../../Lib/JQuery/jquery-ui-timepicker-addon.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	*/
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_To_Fb_Post_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_To_Fb_Post_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		*/
		
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../JS/wp-to-fb-post-public.js', array( 'jquery' ), $this->version, false );
		
		wp_enqueue_script( 'jquery-ui', plugin_dir_url( __FILE__ ) . '../../Lib/JQuery/jquery-ui.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'ui-date-picker', plugin_dir_url( __FILE__ ) . '../../Lib/JQuery/jquery-ui-timepicker-addon.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'ui-date-picker-addon', plugin_dir_url( __FILE__ ) . '../../Lib/JQuery/jquery-ui-timepicker-addon-i18n.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'ui-sliderAccess', plugin_dir_url( __FILE__ ) . '../../Lib/JQuery/jquery-ui-sliderAccess.js', array( 'jquery' ), $this->version, false );
		wp_dequeue_script( 'wp_calendar_datepicker' );
		wp_dequeue_script( 'ihc-jquery-ui' );
		wp_deregister_script( 'jquery-ui-datepicker' );
	}
	
	public function Filter_PostSelect($content){
		global $post;
		
		$btn_visibility = get_field('facebook_post_button_visibility', $post->ID);

		if($btn_visibility || is_null($btn_visibility)){
			if(isset($_GET['wptofb']) && $_GET['wptofb'] == 'wptofb_edit'){
				?>
				<style type="text/css" media="screen">
					span.wptofb_btn {
					    display: none;
					}	
				</style>
				<?php
			}
			
			if(is_user_logged_in()){
				if ( 'post' == get_post_type( $post ) ) {
					$post_edit_type = 'wptofb=wptofb_edit';
					$text_domain = 'wp-to-fb-post';
					
					$wptofbbtn = '<span class="wptofb_btn"><a href="'.get_the_permalink().'?'. $post_edit_type.'">'.__('Customize and Post to Facebook',$text_domain).'</a></span>';
					$content .= $wptofbbtn;
			    }

			    if(is_page('account') && isset($_GET['ihc_ap_menu']) && $_GET['ihc_ap_menu'] == 'profile')
			    {
			    	$fb = new Facebook\Facebook([
					  'app_id' => '524022474717135', // Replace {app-id} with your app id
					  'app_secret' => 'cb425a76db31161c5184577028bfa3d8',
					  'default_graph_version' => 'v2.10',
					  ]);

					$helper = $fb->getRedirectLoginHelper();

					$permissions = ['email','manage_pages','publish_pages','pages_show_list']; // Optional permissions
					$loginUrl = $helper->getReAuthenticationUrl(site_url('/account/?ihc_ap_menu=profile&continuewithfb=yes'), $permissions);

					$content .= '<a class="facebookbtn" href="' . htmlspecialchars($loginUrl) . '">Continue with Facebook!</a>';

					global $current_user;

					$pages = get_user_meta($current_user->ID, "sc_fb_pages", true );
					if(!empty($pages))
					{
						//$user_choose_fb_page = get_user_meta($current_user->ID, "user_choose_fb_page", true );
						$PagesSelected = get_user_meta ( $current_user -> ID , "user_choose_fb_page" , true );
						$PagesSelected = explode ( ',' , $PagesSelected );
						for ( $i = 0; isset($PagesSelected[$i]); $i++ )
						{
							$PagesSelected[$PagesSelected[$i]] = true;
						}

						$pages = json_decode($pages);
						$content .= '<form action="" method="post" name="user_fb_page" class="fb_page_list">';
						//$content .= '<select name="user_choose_fb_page">';
						//$content .= '<option value="">'. __('Select Page','wp-to-fb-post') .'</option>';
						$content .= '<legend>Select Pages:</legend>';
						foreach ($pages as $key => $value) {
							if( isset($PagesSelected[$key]) )
							{
								$checked = 'checked="checked"';
							}
							else
							{
								$checked = '';
							}
							$content .= '<input type="checkbox" name="'.$key.'" value="1" '. $checked .' /><label style="display: inline-block;margin: 15px;">'.$value->name.'</label><br />';
							// $content .= '<input type="radio" value="'.$key.'" name="user_choose_fb_page" '.$checked.' />'.$value->name;
						}
						//$content .= '</select>';
						$content .= '<input type="hidden" name="fb_choose_page_post" value="yes" ><input type="submit" name="save_fb_page" value="Save"></form>';
					}
				}
			}
		}
		return $content;
	}

	// The following function has been disabled. See "Core.php".
	/*public function add_btn_compose_post_excerpt_more( $more ) {
		global $post;
		$btn_visibility = get_field('facebook_post_button_visibility', $post->ID);

		if($btn_visibility || is_null($btn_visibility)){
		if(is_user_logged_in()){
			if ( 'post' == get_post_type( $post ) ) {
				$post_edit_type = 'wptofb=wptofb_edit';
				$text_domain = 'wp-to-fb-post';
				
				$wptofbbtn = '<span class="wptofb_btn"><a href="'.get_the_permalink().'?'. $post_edit_type.'">'.__('Customize and Post to Facebook',$text_domain).'</a></span>';
				$more .= $wptofbbtn;
		    }
		}
		}
		return $more;
	}*/
	
	/* Filter the single_template with our custom function*/

	public function Filter_PostCompose($single) {

	    global $wp_query, $post;

	    /* Checks for single template by post type */
	   	if(isset($_GET['wptofb']) && $_GET['wptofb'] == 'wptofb_edit'){
		    if ( $post->post_type == 'post' ) {
		    	if ( file_exists( plugin_dir_path( __FILE__ ) . 'Compose.php' ) ) {
		            return plugin_dir_path( __FILE__ ) . 'Compose.php';
		        }
		    }
		}
	    return $single;
	}

	/*post to facebook page */
	public function Action_FacebookAuthorize(){
		ini_set('display_errors', 1);
		if(!session_id()) {
		    session_start();
		}


		global $current_user;

		if(isset($_POST['fb_choose_page_post']))
		{

			$pages = get_user_meta ( $current_user -> ID , "sc_fb_pages" , true );
			$pages = json_decode ( $pages );
			$PagesSelected = '';
			foreach ( $pages as $key => $value )
			{
				if ( $_POST[$key] == '1')
				{
					echo $key . ': Selected.<br />';
					$PagesSelected .= $key . ',';
					// Stuff to do if the page was checked.
				}
			}
			update_user_meta( $current_user -> ID , 'user_choose_fb_page' , $PagesSelected );


			// This whole chunk is probably going away shortly.
			/*if(isset($_POST['user_choose_fb_page']) && $_POST['user_choose_fb_page'] != '')
			{
				update_user_meta($current_user->ID, "user_choose_fb_page", $_POST['user_choose_fb_page'] );
			}*/
		}

		if(isset($_GET['continuewithfb']) && $_GET['continuewithfb'] == 'yes'){
			$fb = new Facebook\Facebook([
		  		'app_id' => '524022474717135', // Replace {app-id} with your app id
		  		'app_secret' => 'cb425a76db31161c5184577028bfa3d8',
		  		'default_graph_version' => 'v3.1',
		  		'persistent_data_handler'=>'session'
		  	]);

			$helper = $fb->getRedirectLoginHelper();

			if (isset($_GET['state'])) {
		    	$helper->getPersistentDataHandler()->set('state', $_GET['state']);
			}
		
			try {
				// The following line has been added to allow local testing. To put everything back to normal, swap it with the one below it.
				$accessToken = $helper->getAccessToken('https://d4d9f97c.ngrok.io/account/?ihc_ap_menu=profile&continuewithfb=yes');
				//$accessToken = $helper->getAccessToken('https://socialcenter.spartannash.com/account/?ihc_ap_menu=profile&continuewithfb=yes');
				  
			} catch(Facebook\Exceptions\FacebookResponseException $e) {
		  		// When Graph returns an error
		  		echo 'Graph returned an error: ' . $e->getMessage();
		  		echo 'Access token error';
		  		exit;
			} catch(Facebook\Exceptions\FacebookSDKException $e) {
		  		// When validation fails or other local issues
		  		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		  		echo 'Access token error';
		  		exit;
			}

		
			if (! isset($accessToken)) {
		  		if ($helper->getError()) {
		    		header('HTTP/1.0 401 Unauthorized');
		    		echo "Error: " . $helper->getError() . "\n";
		    		echo "Error Code: " . $helper->getErrorCode() . "\n";
		    		echo "Error Reason: " . $helper->getErrorReason() . "\n";
		    		echo "Error Description: " . $helper->getErrorDescription() . "\n";
		  		} else {
		    		header('HTTP/1.0 400 Bad Request');
		    		echo 'Bad request';
		  		}
		  		exit;
			}
		
			// Logged in
			//echo '<h3>Access Token</h3>';
			//var_dump($accessToken->getValue());

			// The OAuth 2.0 client handler helps us manage access tokens
			$oAuth2Client = $fb->getOAuth2Client();

			// Get the access token metadata from /debug_token
			$tokenMetadata = $oAuth2Client->debugToken($accessToken);
			//echo '<h3>Metadata</h3>';
			//var_dump($tokenMetadata);

			// Validation (these will throw FacebookSDKException's when they fail)
			$tokenMetadata->validateAppId('524022474717135'); // Replace {app-id} with your app id
			// If you know the user ID this access token belongs to, you can validate it here
			//$tokenMetadata->validateUserId('123');
			$tokenMetadata->validateExpiration();

			if (! $accessToken->isLongLived()) {
		  		// Exchanges a short-lived access token for a long-lived one
			  	try {
				    $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
		  		} catch (Facebook\Exceptions\FacebookSDKException $e) {
		    		echo "<p>Error getting long-lived access token: " . $e->getMessage() . "</p>\n\n";
		    		exit;
		  		}

		  		//echo '<h3>Long-lived</h3>';
		  		//var_dump($accessToken->getValue());
			}

			if($accessToken->getValue() != '')
			{
				//update_user_meta('sc_fb_page_access_token');
				update_user_meta($current_user->ID, "sc_fb_page_access_token", $accessToken->getValue() );
				try {
					$response = $fb->get('/me/accounts', (string)$accessToken->getValue() );
				} catch(Facebook\Exceptions\FacebookResponseException $e) {
			  		// When Graph returns an error
			  		echo 'Graph returned an error: ' . $e->getMessage();
			  		echo 'Access token error';
			  		exit;
				} catch(Facebook\Exceptions\FacebookSDKException $e) {
			  		// When validation fails or other local issues
			  		echo 'Facebook SDK returned an error: ' . $e->getMessage();
			  		echo 'Access token error';
			  		exit;
				}
				//$graphNode = $response->getGraphNode();
				$data =array();
				$data_decode = $response->getDecodedBody();
				if(isset($data_decode['data'])){
					$data = $data_decode['data'];
				}
			
				$newarray = array();
				if(!empty($data)){
					foreach ($data as $value) {
						$newarray[$value['id']] = $value;
					}
				}
				update_user_meta($current_user->ID, "sc_fb_pages", json_encode($newarray) );
				$url = site_url('/account/?ihc_ap_menu=profile');
				wp_redirect( $url );
				exit;
			}
			else {
			    header('HTTP/1.0 400 Bad Request');
		    	echo 'Bad request';
			}
		}
	}
}