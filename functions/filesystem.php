<?php
/**
 * Filesystem Operations
 *
 * @since 1.0
 *
 * @function	superpwa_wp_filesystem_init()	Initialize the WP filesystem
 * @function	superpwa_put_contents()			Write to a file using WP_Filesystem() functions
 * @function	superpwa_get_contents()			Read contents of a file using WP_Filesystem() functions
 * @function	superpwa_delete()				Delete a file
 */

// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

/**
 * Get the version of resources like JS file so browser cache
 * will bust on a new version of this plugin.
 *
 * @since 1.9.2
 * @since 2.2.3 (add filter `superpwa_resource_version`)
 *
 * @return null|string
 */
function superpwa_get_resources_version() {
	if ( class_exists( '\Lipe\Lib\Theme\Styles' ) ) {
		return \Lipe\Lib\Theme\Styles::in()->get_version();
	}

	return apply_filters( 'superpwa_resource_version', SUPERPWA_VERSION );
}

/**
 * Get a template from either the theme or this plugin
 *
 * @param      $template
 * @param bool $get_contents
 *
 * @since 1.9.2
 *
 * @return string
 */
function superpwa_locate_template( $template, $get_contents = true ) {
	$located = locate_template( 'super-progressive-web-apps/' . $template, true );
	if ( empty( $located ) ) {
		$located = SUPERPWA_PATH_ABS . 'templates/' . $template;
	}
	if ( $get_contents ) {
		ob_start();
		require $located;
		return ob_get_clean();
	}

	return $located;
}


/**
 * Initialize the WP filesystem
 *
 * @since 1.0
 */
function superpwa_wp_filesystem_init() {

	global $wp_filesystem;

	if ( empty( $wp_filesystem ) ) {
		require_once( trailingslashit( ABSPATH ) . 'wp-admin/includes/file.php' );
		WP_Filesystem();
	}
}

/**
 * Get the path to be used to generate files
 *
 * @author Mat Lipe
 * @since  1.9.1
 *
 * @return string
 */
function superpwa_get_output_path() {
	return apply_filters( 'superpwa_output_path', trailingslashit( ABSPATH ) );
}


/**
 * Write to a file using WP_Filesystem() functions
 *
 * @param	$file		Filename with path
 * @param	$content	Contents to be written to the file. Default null
 * @return	True on success, false if file isn't passed or if writing failed.
 *
 * @since	1.0
 */
function superpwa_put_contents( $file, $content = null ) {

	// Return false if no filename is provided
	if ( empty( $file ) ) {
		return false;
	}

	// Initialize the WP filesystem
	superpwa_wp_filesystem_init();
	global $wp_filesystem;

	if( ! $wp_filesystem->put_contents( $file, $content, 0644) ) {
		return false;
	}

	return true;
}

/**
 * Read contents of a file using WP_Filesystem() functions
 *
 * @param    string $file  Filename with path.
 * @param    bool   $array Set true to return read data as an array. False by default.
 *
 * @return    string|array|bool The function returns the read data or false on failure.
 *
 * @since    1.0
 */
function superpwa_get_contents( $file, $array = false ) {
	// Return false if no filename is provided or file does not exist
	if ( empty( $file ) || ! file_exists( $file ) ) {
		return false;
	}

	// Initialize the WP filesystem
	superpwa_wp_filesystem_init();
	global $wp_filesystem;

	// Reads entire file into a string
	if ( $array == false ) {
		return $wp_filesystem->get_contents( $file );
	}

	// Reads entire file into an array
	return $wp_filesystem->get_contents_array( $file );
}

/**
 * Delete a file
 *
 * @param	$file	Filename with path
 * @return	bool	True on success, false otherwise
 *
 * @since	1.0
 */
function superpwa_delete( $file ) {

	// Return false if no filename is provided
	if ( empty( $file ) ) {
		return false;
	}

	// Initialize the WP filesystem
	superpwa_wp_filesystem_init();
	global $wp_filesystem;

	return $wp_filesystem->delete( $file );
}
