<?php
/**
 * single post template
 *
*/
error_reporting(E_ALL);
ini_set('display_errors', 1);
get_header();
$text_domain = 'wp-to-fb-post';
$prefix = 'wptfbp_';
if(is_user_logged_in()){
?>
<div class="wptofb-single-post">
	<div class="form-style-5">
		<?php while ( have_posts() ) : the_post();
			$post_id = get_the_ID();
			$post = get_post( $post_id, OBJECT, 'edit' );
			$settings = array( 'media_buttons' => false );

			$content = $post->post_content;
			$editor_id = 'editpost';
		?>
		<form method="POST">
			<fieldset>
				<?php 
					/*if(isset($_COOKIE["success"])){
						echo '<div class="alert alert-success">'.$_COOKIE["msg"].'</div>';
						unset($_COOKIE["msg"]);
						unset($_COOKIE["success"]);
						unset($_COOKIE["error"]);
					}elseif(isset($_COOKIE["error"])){
						echo '<div class="alert alert-danger">'.$_COOKIE["msg"].'</div>';
						unset($_COOKIE["msg"]);
						unset($_COOKIE["success"]);
						unset($_COOKIE["error"]);
					}*/

					if ( isset ( $_COOKIE['StatusMessages'] ) )
					{
						$StatusMessages = explode ( ';' , $_COOKIE['StatusMessages'] );
						foreach ( $StatusMessages as $StatusMessage )
						{
							if ( $StatusMessage != '' )
							{
								$StatusComponents = explode ( ',' , $StatusMessage );
								if ( $StatusComponents[0] == 'Error' )
								{
									echo '<div class="alert alert-danger">'.$StatusComponents[0].': '.$StatusComponents[2].'<br />';
									echo 'Page Id: '.$StatusComponents[1].'<br />';
									if ( isset($StatusComponents[4]) )
									{
										echo $StatusComponents[3].':<br />';
										echo $StatusComponents[4];
									}
									else
									{
										echo $StatusComponents[3];
									}
								}
								if ( $StatusComponents[0] == 'Success' )
								{
									echo '<div class="alert alert-success">'.$StatusComponents[0].':<br />Page Id: '.$StatusComponents[1];
								}
								echo '</div>';
							}
						}
						unset ( $_COOKIE['StatusMessages'] );
					}
					
				?>
				<legend><span class="number"><?php _e('1',$text_domain)?></span><?php _e('Customize Your Post',$text_domain)?></legend>
				<?php /* ?>
				<label for="post_title"><?php _e('Enter Your Post Title',$text_domain)?>:</label>
				<input type="text" name="<?php echo $prefix.'title' ?>" placeholder="<?php the_title();?>">
				<?php */ ?>
				<label for="post_title"><?php _e('Post Content',$text_domain)?>:</label>
				
				<span class="wptfbp_content"><?php wp_editor( $content, $editor_id,$settings );?></span>

				<label for="post_title"><?php _e('Enter Your Url',$text_domain)?>:</label>
				<input type="url" name="<?php echo $prefix.'url' ?>" placeholder="<?php the_permalink();?>">
				

				<div class="fusion-image-wrapper"><?php the_post_thumbnail('medium_large'); ?></div>
			</fieldset>
			<br /><br />
			<fieldset>
			<?php
			function FacebookPagesSelect () {
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
					$content = '';
					//$content .= '<form action="" method="post" name="user_fb_page" class="fb_page_list">';
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
					//$content .= '<input type="hidden" name="fb_choose_page_post" value="yes" ><input type="submit" name="save_fb_page" value="Save"></form>';
					echo $content;
				}
			}
			FacebookPagesSelect();
			?>
			</fieldset>
			<br /><br />
			<fieldset>
			<input type="checkbox" name="schedule_post" value="1" id="schedule_post"> <label style="display: inline-block;margin-bottom: 30px;"><?php _e( 'Schedule this post', 'wp-to-fb-post' );?></label> <input type="text" name="schedule_date" id="datepicker" placeholder="Enter date to be scheduled your post" autocomplete="off" style="display: none;">
			
			<input type="hidden" name="the_post_id" value="<?php echo get_the_ID(); ?>" />
			<input type="submit" class="submite_btn" name="fbtopagepost" value="<?php _e('Post To Facebook',$text_domain)?>" />
		</form>

		<?php endwhile; ?>
		
	</div>
</div>
<script type="text/javascript">
	jQuery( function() {
		jQuery( "#datepicker" ).datetimepicker({
			timeFormat: "hh:mm tt"
		});
		
		jQuery('#schedule_post').click(function(event) {
			if (jQuery(this).is(':checked')) {
				jQuery('#datepicker').show();
			}else{
				jQuery('#datepicker').hide();
			}
		});
	} );
</script>
<?php
}else{
	echo '<div class="loginmsg">';
	_e("You need to Login For Facebook Post.");
	echo "</div>";
}
get_footer();