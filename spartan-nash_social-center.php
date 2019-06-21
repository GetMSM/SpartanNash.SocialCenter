<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              #
 * @since             1.0.0
 * @package           spartan-nash_social-center
 *
 * @wordpress-plugin
 * Plugin Name:       Social Center By Spartan Nash
 * Plugin URI:        #
 * Description:       This plugin provides most of the core functionality for Social Center.
 * Version:           2.0.0
 * Author:            #
 * Author URI:        #
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * 
 * GitHub Plugin URI: https://github.com/GetMSM/SpartanNash.SocialCenter
*/

if ( ! defined( 'WPINC' ) )
{ die; }

if ( ! defined( 'SpartanNash_SocialCenter_Version' ) )
{ define( 'SpartanNash_SocialCenter_Version', '2.0.0' ); }

function SpartanNash_SocialCenter_Activate (  )
{
	require_once plugin_dir_path( __FILE__ ).'Include/PHP/Activate.php';
	SpartanNash_SocialCenter_Activate::Activate();
}
register_activation_hook( __FILE__, 'SpartanNash_SocialCenter_Activate' );

/**
 * The code that runs during plugin deactivation.
 */
function SpartanNash_SocialCenter_Deactivate() {
	require_once plugin_dir_path( __FILE__ ).'Include/PHP/Deactivate.php';
	SpartanNash_SocialCenter_Deactivate::Deactivate();
}
register_deactivation_hook( __FILE__, 'SpartanNash_SocialCenter_Deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ).'Include/PHP/Core.php';
require plugin_dir_path( __FILE__ ).'Include/PHP/Facebook.Post.php';
define( 'FACEBOOK_SDK_V4_SRC_DIR', plugin_dir_path( __FILE__ ).'Lib/Facebook.SDK/PHP/');
require_once(FACEBOOK_SDK_V4_SRC_DIR.'autoload.php');

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function SpartanNash_SocialCenter_Initialize() {

	$plugin = new SpartanNash_SocialCenter();
	$plugin->run();

}
SpartanNash_SocialCenter_Initialize();