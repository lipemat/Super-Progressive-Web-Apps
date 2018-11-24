<?php
$settings = superpwa_get_settings();
?>
<script>
'use strict';

const cacheName = '<?php echo parse_url( get_bloginfo( 'wpurl' ), PHP_URL_HOST ) . '-superpwa-' . SUPERPWA_VERSION; ?>';
const startPage = '<?php echo superpwa_get_start_url(); ?>';
const offlinePage = '<?php echo get_permalink( $settings['offline_page'] ) ? superpwa_httpsify( get_permalink( $settings['offline_page'] ) ) : superpwa_httpsify( get_bloginfo( 'wpurl' ) ); ?>';
const filesToCache = [<?php echo apply_filters( 'superpwa_sw_files_to_cache', 'startPage, offlinePage' ); ?>];
const neverCacheUrls = [<?php echo apply_filters( 'superpwa_sw_never_cache_urls', '/\/wp-admin/,/\/wp-login/,/preview=true/' ); ?>];
const allowedOrigins = [<?php echo apply_filters( 'superpwa_sw_allowed_domain_patterns', '/https?:\/\/fonts.+/,/https?:\/\/secure\.gravatar\.com/'); ?>];

// Install
self.addEventListener('install', function(e) {
	//we are not going to wait
	self.skipWaiting();

	console.log('PWA service worker installation');
	e.waitUntil(
		caches.open(cacheName).then(function(cache) {
			console.log('PWA service worker caching dependencies');
			var _cached = [];
			filesToCache.map(function(url) {
				//to prevent doubling up
				if ( _cached.indexOf(url) !== -1 ){
					return;
				}
				_cached.push(url);

				return cache.add(url).catch(function (reason) {
					return console.log('PWA: ' + String(reason) + ' ' + url);
				});
			});
		})
	);
});

// Activate
self.addEventListener('activate', function(e) {
	console.log('PWA service worker activation');
	e.waitUntil(
		caches.keys().then(function(keyList) {
			return Promise.all(keyList.map(function(key) {
				if ( key !== cacheName ) {
					console.log('PWA old cache removed', key);
					return caches.delete(key);
				}
			}));
		})
	);
	return self.clients.claim();
});

// Fetch
self.addEventListener('fetch', function(e) {
	// Return if the current request url is in the never cache list
	if ( isURLInPatterns(neverCacheUrls, e.request.url) ) {
		console.log( "Current request %s is excluded from cache.", e.request.url );
		return;
	}

	// Return if request url is from an external domain not on allowed list.
	var $origin = new URL(e.request.url).origin;
	if ($origin !== location.origin && ! isURLInPatterns(allowedOrigins, $origin)) {
		return;
	}

	// For POST requests, do not use the cache. Serve offline page if offline.
	if ( e.request.method !== 'GET' ) {
		e.respondWith(
			fetch(e.request).catch( function() {
				return caches.match(offlinePage);
			})
		);
		return;
	}

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
	if ( e.request.mode === 'navigate' && navigator.onLine ) {
		e.respondWith(
			fetch(e.request).then(function(response) {
				return caches.open(cacheName).then(function(cache) {
					cache.put(e.request, response.clone());
					return response;
				});
			})
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
		caches.match(e.request).then(function(response) {
			return response || fetch(e.request).then(function(response) {
				return caches.open(cacheName).then(function(cache) {
					cache.put(e.request, response.clone());
					return response;
				});
			});
		}).catch(function() {
			return caches.match(offlinePage);
		})
	);
});

/**
 * See if a url matches any items in an array of regular expression.
 *
 * @param {string} url
 * @param {array} patterns
 * @returns {boolean}
 */
function isURLInPatterns(patterns, url) {
	return patterns.some( function(pattern) {
		var regex = new RegExp( pattern );
		return regex.test(url);
	} );
}


</script>
