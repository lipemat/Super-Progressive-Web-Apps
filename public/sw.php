<?php
/**
 * Service worker related functions of SuperPWA
 *
 * @since 1.0
 *
 * @function	superpwa_sw()					Service worker filename, absolute path and link
 * @function	superpwa_generate_sw()			Generate and write service worker into sw.js
 * @function	superpwa_sw_template()			Service worker tempalte
 * @function	superpwa_register_sw()			Register service worker
 * @function	superpwa_delete_sw()			Delete service worker
 * @function 	superpwa_offline_page_images()	Add images from offline page to filesToCache
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Service worker filename, absolute path and link
 *
 * For Multisite compatibility. Used to be constants defined in superpwa.php
 * On a multisite, each sub-site needs a different service worker.
 *
 * @param $arg 	filename for service worker filename (replaces SUPERPWA_SW_FILENAME)
 *				abs for absolute path to service worker (replaces SUPERPWA_SW_ABS)
 *				src for relative link to service worker (replaces SUPERPWA_SW_SRC). Default value
 *
 * @return (string) filename, absolute path or link to manifest.
 *
 * @since 1.6
 * @since 1.7 src to service worker is made relative to accomodate for domain mapped multisites.
 * @since 1.8 Added filter superpwa_sw_filename.
 */
function superpwa_sw( $arg = 'src' ) {

	$sw_filename = apply_filters( 'superpwa_sw_filename', 'superpwa-sw' . superpwa_multisite_filename_postfix() . '.js' );

	switch( $arg ) {

		// Name of service worker file
		case 'filename':
			return $sw_filename;
			break;

		// Absolute path to service worker. SW must be in the root folder
		case 'abs':
			return superpwa_get_output_path() . $sw_filename;
			break;

		// Link to service worker
		case 'src':
		default:
			return parse_url( superpwa_get_output_url() . $sw_filename, PHP_URL_PATH );
			break;
	}
}

/**
 * Generate and write service worker into superpwa-sw.js
 *
 * @return (boolean) true on success, false on failure.
 *
 * @since 1.0
 */
function superpwa_generate_sw() {

	// Get Settings
	$settings = superpwa_get_settings();

	// Get the service worker tempalte
	$sw = superpwa_sw_template();

	// Delete service worker if it exists
	superpwa_delete_sw();

	if ( ! superpwa_put_contents( superpwa_sw( 'abs' ), $sw ) ) {
		return false;
	}

	return true;
}

/**
 * Service Worker Tempalte
 *
 * @since 1.10.0
 *
 * @return string - Contents to be written to superpwa-sw.js
 */
function superpwa_sw_template() {
	ob_start();
	$template = locate_template( 'super-progressive-web-apps/sw.php', true );
	if ( empty( $template ) ) {
		require SUPERPWA_PATH_ABS . 'templates/sw.php';
	}

	return apply_filters( 'superpwa_sw_template', ob_get_clean() );
}

/**
 * Register service worker
 *
 * @refer https://developers.google.com/web/fundamentals/primers/service-workers/registration#conclusion
 *
 * @since 1.0
 */
function superpwa_register_sw() {

	wp_enqueue_script( 'superpwa-register-sw', SUPERPWA_PATH_SRC . 'public/js/register-sw.js', array(), superpwa_get_resources_version(), true );
	wp_localize_script( 'superpwa-register-sw', 'superpwa_sw', array(
			'url' => superpwa_sw( 'src' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'superpwa_register_sw' );

/**
 * Delete Service Worker
 *
 * @return true on success, false on failure
 *
 * @since 1.0
 */
function superpwa_delete_sw() {
	return superpwa_delete( superpwa_sw( 'abs' ) );
}

/**
 * Add images from offline page to filesToCache
 *
 * If the offlinePage set by the user contains images, they need to be cached during sw install.
 * For most websites, other assets (css, js) would be same as that of startPage which would be cached
 * when user visits the startPage the first time. If not superpwa_sw_files_to_cache filter can be used.
 *
 * @param (string) $files_to_cache Comma separated list of files to cache during service worker install
 *
 * @return (string) Comma separated list with image src's appended to $files_to_cache
 *
 * @since 1.9
 */
function superpwa_offline_page_images( $files_to_cache ) {

	// Get Settings
	$settings = superpwa_get_settings();

	// Retrieve the post
	$post = get_post( $settings['offline_page'] );

	// Return if the offline page is set to default
	if( $post === NULL ) {
		return $files_to_cache;
	}

	// Match all images
	preg_match_all( '/<img[^>]+src="([^">]+)"/', $post->post_content, $matches );

	// $matches[1] will be an array with all the src's
	if( ! empty( $matches[1] ) ) {
		return superpwa_httpsify( $files_to_cache . ', \'' . implode( '\', \'', $matches[1] ) . '\'' );
	}

	return $files_to_cache;
}
add_filter( 'superpwa_sw_files_to_cache', 'superpwa_offline_page_images' );
