<?php
/**
 * Plugin Name: Super Progressive Web Apps
 * Plugin URI: https://superpwa.com/?utm_source=superpwa-plugin&utm_medium=plugin-uri
 * Description: Convert your WordPress website into a Progressive Web App
 * Author: SuperPWA
 * Author URI: https://superpwa.com/?utm_source=superpwa-plugin&utm_medium=author-uri
 * Contributors: Arun Basil Lal, Jose Varghese, Mat Lipe
 * Version: 1.11.0
 * Text Domain: super-progressive-web-apps
 * Domain Path: /languages
 * License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */
define( 'SUPERPWA_VERSION'	, '1.11.0' );

// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

if ( ! defined( 'SUPERPWA_PATH_ABS' ) ) 	define( 'SUPERPWA_PATH_ABS'	, plugin_dir_path( __FILE__ ) ); // Absolute path to the plugin directory. eg - /var/www/html/wp-content/plugins/super-progressive-web-apps/
if ( ! defined( 'SUPERPWA_PATH_SRC' ) ) 	define( 'SUPERPWA_PATH_SRC'	, plugin_dir_url( __FILE__ ) ); // Link to the plugin folder. eg - https://example.com/wp-content/plugins/super-progressive-web-apps/

// Load everything
require_once( SUPERPWA_PATH_ABS . 'loader.php' );
