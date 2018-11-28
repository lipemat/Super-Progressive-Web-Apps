<?php

/**
 * @author Mat Lipe
 * @since  November, 2018
 *
 */
class SuperPWA_WP_CLI extends \WP_CLI_Command{

	/**
	 * Regenerates the service-worker file
	 *
	 * ## OPTIONS
	 *
	 * ## EXAMPLES
	 *
	 *     wp superpwa regenerate
	 *
	 * @when after_wp_load
	 */
	public function regenerate( $args, $assoc_args ) : void {
		if ( superpwa_generate_sw() ) {
			\WP_CLI::success( 'Regenerated the service worker file @ ' . superpwa_sw( 'abs' ) );
		} else {
			\WP_CLI::error( 'Failed regenerating the service worker file', true );
		}
	}
}
\WP_CLI::add_command( 'superpwa', new SuperPWA_WP_CLI() );
