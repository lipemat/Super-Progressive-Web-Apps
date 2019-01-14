<?php
/**
 * Plugin Name: Super Progressive Web Apps
 * Plugin URI: https://superpwa.com/?utm_source=superpwa-plugin&utm_medium=plugin-uri
 * Description: Convert your WordPress website into a Progressive Web App
 * Author: SuperPWA
 * Author URI: https://superpwa.com/?utm_source=superpwa-plugin&utm_medium=author-uri
 * Contributors: Arun Basil Lal, Jose Varghese, Mat Lipe
 * Version: 2.0.2
 * Text Domain: super-progressive-web-apps
 * Domain Path: /languages
 * License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */
define( 'SUPERPWA_VERSION', '2.0.2' );

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Absolute path to the plugin directory.
 * eg - /var/www/html/wp-content/plugins/super-progressive-web-apps/
 *
 * @since 1.0
 */
if ( ! defined( 'SUPERPWA_PATH_ABS' ) ) {
	define( 'SUPERPWA_PATH_ABS', plugin_dir_path( __FILE__ ) );
}

/**
 * Link to the plugin folder.
 * eg - https://example.com/wp-content/plugins/super-progressive-web-apps/
 *
 * @since 1.0
 */
if ( ! defined( 'SUPERPWA_PATH_SRC' ) ) {
	define( 'SUPERPWA_PATH_SRC', plugin_dir_url( __FILE__ ) );
}

/**
 * Full path to the plugin file.
 * eg - /var/www/html/wp-content/plugins/Super-Progressive-Web-Apps/superpwa.php
 *
 * @since 2.0
 */
if ( ! defined( 'SUPERPWA_PLUGIN_FILE' ) ) {
	define( 'SUPERPWA_PLUGIN_FILE', __FILE__ );
}

// Load everything
require_once( SUPERPWA_PATH_ABS . 'loader.php' );
