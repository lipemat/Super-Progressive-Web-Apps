<?php
/**
 * Service worker related functions of SuperPWA
 *
 * @since       1.0
 *
 * @function    superpwa_sw()                    Service worker filename, absolute path and link
 * @function    superpwa_generate_sw()            Generate and write service worker into sw.js
 * @function    superpwa_sw_template()            Service worker tempalte
 * @function    superpwa_register_sw()            Register service worker
 * @function    superpwa_delete_sw()            Delete service worker
 * @function    superpwa_offline_page_images()    Add images from offline page to filesToCache
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the Service worker's filename.
 *
 * @since 2.0
 *
 * @return string
 */
function superpwa_get_sw_filename() {
	return apply_filters( 'superpwa_sw_filename', 'superpwa-sw' . superpwa_multisite_filename_postfix() . '.js' );
}

/**
 * Service worker filename, absolute path and link
 *
 * For Multisite compatibility. Used to be constants defined in superpwa.php
 * On a multisite, each sub-site needs a different service worker.
 *
 * @param string $arg    filename for service worker filename (replaces SUPERPWA_SW_FILENAME)
 *                abs for absolute path to service worker (replaces SUPERPWA_SW_ABS)
 *                src for link to service worker (replaces SUPERPWA_SW_SRC). Default value
 *
 * @return string - filename, absolute path or link to manifest.
 *
 * @since  1.6
 * @since  1.7 src to service worker is made relative to accomodate for domain mapped multisites.
 * @since  1.8 Added filter superpwa_sw_filename.
 * @since  2.0 src actually returns the link and the URL_PATH is extracted in superpwa_register_sw().
 * @since  2.0 src uses home_url instead of network_site_url since manifest is no longer in the root folder.
 */
function superpwa_sw( $arg = 'src' ) {
	$sw_filename = superpwa_get_sw_filename();

	switch ( $arg ) {
		// TODO: Case `filename` can be deprecated in favor of @see superpwa_get_sw_filename().

		// Name of service worker file
		case 'filename':
			return $sw_filename;
			break;
		/**
		 * Absolute path to service worker. SW must be in the root folder.
		 *
		 * @since 2.0 service worker is no longer a physical file and absolute path doesn't make sense.
		 * Also using home_url instead of network_site_url in "src" in 2.0 changes the apparent location of the file.
		 * However, absolute path is preserved at the "old" location, so that phyiscal files can be deleted when upgrading from pre-2.0 versions.
		 */
		case 'abs':
			return superpwa_get_output_path() . $sw_filename;
			break;

		// Link to service worker
		case 'src':
		default:
			return home_url( '/' ) . $sw_filename;
			break;
	}
}

/**
 * Generate and write service worker into superpwa-sw.js
 *
 * @return     bool - true on success, false on failure.
 *
 * @since      1.0
 * @since      2.0 Deprecated since Service worker is generated on the fly
 *             {@see superpwa_generate_sw_and_manifest_on_fly()}.
 *
 * @deprecated 2.0 No longer used by internal code.
 */
function superpwa_generate_sw() {
	// Returns TRUE for backward compatibility.
	return true;
}

/**
 * Service Worker Template
 *
 * @since 1.10.0
 *
 * @return string - Contents to be written to superpwa-sw.js
 */
function superpwa_sw_template() {
	$settings = superpwa_get_settings();

	// phpcs:disable
	ob_start(); ?>
	<script>
		'use strict';
		const cacheName = '<?php echo parse_url( get_bloginfo( 'wpurl' ), PHP_URL_HOST ) . '-superpwa-' . superpwa_get_resources_version(); ?>';
		const offlinePage = '<?php echo get_permalink( $settings['offline_page'] ) ? superpwa_httpsify( get_permalink( $settings['offline_page'] ) ) : superpwa_httpsify( get_bloginfo( 'wpurl' ) ); ?>';
		var filesToCache = <?php echo wp_json_encode( superpwa_get_must_cache_urls() ); ?>;
		const networkFirstUrls = [<?php echo apply_filters( 'superpwa_sw_network_first_urls', '/\/wp-json/' ); ?>];
		const neverCacheUrls = [<?php echo apply_filters( 'superpwa_sw_never_cache_urls', '/\/wp-admin/,/\/wp-login/,/preview=true/' ); ?>];
		const allowedOrigins = [<?php echo apply_filters( 'superpwa_sw_allowed_domain_patterns', '/https?:\/\/fonts.+/,/https?:\/\/secure\.gravatar\.com/' ); ?>];

		<?php
		// phpcs:enable;
		?>

		// Install
		self.addEventListener( 'install', function ( e ) {
			//we are not going to wait
			self.skipWaiting();

			console.log( 'PWA service worker installation' );
			e.waitUntil(
				caches.open( cacheName ).then( function ( cache ) {
					console.log( 'PWA service worker caching dependencies' );
					var _cached = [];
					filesToCache.map( function ( url ) {
						//to prevent doubling up
						if ( _cached.indexOf( url ) !== -1 ) {
							return;
						}
						_cached.push( url );

						return cache.add( url ).catch( function ( reason ) {
							return console.log( 'PWA: ' + String( reason ) + ' ' + url );
						} );
					} );
				} )
			);
		} );

		// Activate
		self.addEventListener( 'activate', function ( e ) {
			console.log( 'PWA service worker activation' );
			e.waitUntil(
				caches.keys().then( function ( keyList ) {
					return Promise.all( keyList.map( function ( key ) {
						if ( key !== cacheName ) {
							console.log( 'PWA old cache removed', key );
							return caches.delete( key );
						}
					} ) );
				} )
			);
			return self.clients.claim();
		} );

		<?php
		if ( ! defined( 'SCRIPT_DEBUG' ) || SCRIPT_DEBUG === false ) {
		?>

		// Fetch
		self.addEventListener( 'fetch', function ( e ) {
			// Return if the current request url is in the never cache list and
			// not in the filesToCache list after stripping query args.
			if ( isURLInPatterns( neverCacheUrls, e.request.url ) && filesToCache.indexOf( e.request.url.substring(0, e.request.url.indexOf('?') ) ) === -1 ) {
				console.log( "Current request %s is excluded from cache.", e.request.url );
				return;
			}

			// Return if request url is from an external domain not on allowed list.
			var $origin = new URL( e.request.url ).origin;
			if ( $origin !== location.origin && !isURLInPatterns( allowedOrigins, $origin ) ) {
				return;
			}

			// For POST requests, do not use the cache. Serve offline page if offline.
			if ( e.request.method !== 'GET' ) {
				e.respondWith(
					fetch( e.request ).catch( function () {
						return caches.match( offlinePage );
					} )
				);
				return;
			}

			// If this url specified to check the network first?
			var networkFirst = isURLInPatterns( networkFirstUrls, e.request.url );

			/**
			 * For document loading "HTML" we use the network first
			 * and fallback to cache only when unable to retrieve the content
			 * via the network.
			 *
			 * This keeps the site up to date with the latest content on each page
			 * while online.
			 *
			 * This also caches a copy of each viewed page for later offline usage.
			 *
			 * If not online ignore this block.
			 *
			 */
			if ( ( networkFirst || e.request.mode === 'navigate' ) && navigator.onLine ) {
				e.respondWith(
					fetch( e.request ).then( function ( response ) {
						return caches.open( cacheName ).then( function ( cache ) {
							cache.put( e.request, response.clone() );
							return response;
						} );
					} ).catch( function() {
						return caches.match( e.request ).then( function( response ) {
							return response || caches.match( offlinePage );
						} );
					} )
				);
				return;
			}

			/**
			 * Check the cache first for the request and return it if available.
			 * If not available, request it from the site, cache the response,
			 * and return it.
			 *
			 * If not request is not available in the cache and we can't get it from
			 * the site, we return the offlinePage.
			 *
			 * This is used for all non document "HTML" requests, unless we or offline
			 * then it is used for document requests as well
			 *
			 */
			e.respondWith(
				caches.match( e.request ).then( function ( response ) {
					return response || fetch( e.request ).then( function ( response ) {
						return caches.open( cacheName ).then( function ( cache ) {
							cache.put( e.request, response.clone() );
							return response;
						} );
					} );
				} ).catch( function () {
					return caches.match( offlinePage );
				} )
			);
		} );

		<?php
		} else {
		?>
		var consoleLogTimeout = null;
		// Fetch
		self.addEventListener( 'fetch', function ( e ) {
			//do nothing for fetch except post a message to console log se we know we have not cache
			if ( null === consoleLogTimeout ) {
				consoleLogTimeout = setTimeout( function () {
					console.log( 'Fetching from cache is disabled because SCRIPT_DEBUG is true. To enable caching, set SCRIPT_DEBUG = false.' );
					consoleLogTimeout = null;
				}, 1000 );
			}
		} );

		<?php

		}
		?>

		/**
		 * See if a url matches any items in an array of regular expression.
		 *
		 * @param {string} url
		 * @param {array} patterns
		 * @returns {boolean}
		 */
		function isURLInPatterns( patterns, url ) {
			return patterns.some( function ( pattern ) {
				var regex = new RegExp( pattern );
				return regex.test( url );
			} );
		}
	</script>
	<?php return apply_filters( 'superpwa_sw_template', strip_tags( ob_get_clean() ) );
}

/**
 * Register service worker
 *
 * @refer https://developers.google.com/web/fundamentals/primers/service-workers/registration#conclusion
 *
 * @since 1.0
 */
function superpwa_register_sw() {
	$settings = superpwa_get_settings();
	if ( superpwa_is_enabled() ) {
		wp_enqueue_script( 'superpwa-register-sw', SUPERPWA_PATH_SRC . 'public/js/register-sw.js', [], superpwa_get_resources_version(), true );
	} else {
		wp_enqueue_script( 'superpwa-register-sw', SUPERPWA_PATH_SRC . 'public/js/unregister-sw.js', [], superpwa_get_resources_version(), true );

	}
	wp_localize_script( 'superpwa-register-sw', 'superpwa_sw', apply_filters( 'superpwa_js_config', [
		'url'                => parse_url( superpwa_sw( 'src' ), PHP_URL_PATH ),
		'enabled'            => (bool) $settings['enabled'],
		'addToHomeText'      => '<span class="dashicons dashicons-plus"></span> <span>' . __( 'Add To Home Screen', 'super-progressive-web-apps' ) . '</span> <span class="dashicons dashicons-no-alt dismiss"></span>',
		'addToHomeColor'     => $settings['theme_color'],
		'addToHomeIncrement' => (bool) $settings['add_to_home_increment'],
	] ) );

	if ( $settings['add_to_home'] && superpwa_is_enabled() ) {
		wp_enqueue_script( 'superpwa-add-to-home-screen/js', SUPERPWA_PATH_SRC . 'public/js/add-to-home-screen.js', [
			'jquery',
			'superpwa-register-sw',
		], superpwa_get_resources_version(), true );
		wp_enqueue_style( 'superpwa-add-to-home-screen/css', SUPERPWA_PATH_SRC . 'public/css/add-to-home-screen.css', [ 'dashicons' ], SUPERPWA_VERSION );
	}

}

add_action( 'wp_enqueue_scripts', 'superpwa_register_sw' );

/**
 * Delete Service Worker
 *
 * @return true on success, false on failure
 *
 * @since      1.0
 *
 * @deprecated 2.0 No longer used by internal code.
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
 * @param  (string) $files_to_cache Comma separated list of files to cache during service worker install
 *
 * @return (string) Comma separated list with image src's appended to $files_to_cache
 *
 * @since  1.9
 */
function superpwa_offline_page_images( $files_to_cache ) {
	// Get Settings
	$settings = superpwa_get_settings();

	// Retrieve the post
	$post = get_post( $settings['offline_page'] );

	// Return if the offline page is set to default
	if ( $post === null ) {
		return $files_to_cache;
	}

	// Match all images
	preg_match_all( '/<img[^>]+src="([^">]+)"/', $post->post_content, $matches );

	// $matches[1] will be an array with all the src's
	if ( ! empty( $matches[1] ) ) {
		return superpwa_httpsify( $files_to_cache . ', \'' . implode( '\', \'', $matches[1] ) . '\'' );
	}

	return $files_to_cache;
}

add_filter( 'superpwa_sw_files_to_cache', 'superpwa_offline_page_images' );

/**
 * @since 2.1.0
 *
 * @return string
 */
function superpwa_get_offline_page() {

	// Get Settings
	$settings = superpwa_get_settings();

	return get_permalink( $settings['offline_page'] ) ? superpwa_httpsify( get_permalink( $settings['offline_page'] ) ) : superpwa_httpsify( get_bloginfo( 'url' ) );
}

/**
 * @since 2.1.0
 *
 * @return array
 */
function superpwa_get_must_cache_urls() {
	$must_cache_urls = [
		superpwa_get_offline_page(),
		superpwa_get_start_url(),
	];
	array_push( $must_cache_urls, ...explode( ',', superpwa_get_settings()['must_cache_urls'] ) );

	return  apply_filters( 'superpwa_sw_files_to_cache', array_map( 'trim', $must_cache_urls ) );
}
