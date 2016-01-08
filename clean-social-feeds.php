<?php
/**
 * Plugin Name: Clean Social Feeds
 * Plugin URI:  https://github.com/tomimaen/clean-social-feeds
 * Description: Provides a library for retrieving pure data from social media platforms, made for WordPress.
 * Version:     1.2
 * Author:      Tomi Mäenpää
 * Author URI:  http://github.com/tomimaen
 * Text Domain: clean-social-feeds
 * License:     GPLv2 or later
 */

require_once( 'clean-social-feeds-class.php' );
require_once( 'clean-social-feeds-admin-class.php' );

if ( is_admin() ) {
	$admin = new Clean_Social_Feeds_Admin();
}