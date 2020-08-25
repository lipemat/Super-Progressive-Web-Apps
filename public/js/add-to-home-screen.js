/* global superpwa_sw */
(function (config, $) {
	/**
	 * Add the "Add To Home" prompt to the screen
	 * and handle the event when clicked on
	 *
	 * @param {Event} deferredPrompt
	 */
	var setupPrompt = function (deferredPrompt) {
		var el = $('<div id="superpwa-add-to-home" style="background:' + config.addToHomeColor + '; display:none">' + config.addToHomeText + '</div>');
		var body = $( 'body' );
		body
			.prepend( el )
			.addClass( 'add-to-home' );
		$(el).slideDown();

		/**
		 * Trigger the prompt on click
		 */
		el.on('click', 'span', function (e) {
			el.remove();
			body.removeClass( 'add-to-home' );

			if ($(this).hasClass('dismiss')) {
				dismiss();
				return;
			}
			deferredPrompt.prompt();
			// Wait for the user to respond to the prompt
			deferredPrompt.userChoice
				.then(function (choiceResult) {
					if (choiceResult.outcome !== 'accepted') {
						dismiss();
					}
					deferredPrompt = null;
				});
		});

		/**
		 * Hide the element on scroll to bottom of screen
		 */
		var scrollTimeout = null;
		$(window).on('scroll', function () {
			if (null === scrollTimeout) {
				scrollTimeout = setTimeout(function () {
					var position = ($(document).height() - $(this).height() - $(this).scrollTop());
					if (position <= 50) {
						el.slideUp();
						body.removeClass( 'add-to-home' );
					}
					scrollTimeout = null;
				}, 500);
			}
		});

		/**
		 * If a user stays on this page for 15 seconds or more we
		 * will not show them the prompt again for 1 hour.
		 *
		 * @since 1.11.0
		 */
		if ( config.addToHomeIncrement ) {
			setTimeout(function () {
				setCookie('superpwaAddToHomeIncrement', '1');
			}, 15000);
		}
	};

	/**
	 * Set a cookie which expires an hour from now.
	 *
	 * @since 1.11.0
	 *
	 * @param {string} key
	 * @param {string} value
	 */
	function setCookie(key, value) {
		var expires = new Date();
		var hourInSeconds = 60 * 60 * 1000;
		expires.setTime(expires.getTime() + hourInSeconds);
		document.cookie = key + '=' + value + ';expires=' + expires.toUTCString();
	}

	function getCookie(key) {
		var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
		return keyValue ? keyValue[2] : null;
	}


	var dismiss = function() {
		localStorage.setItem('superpwaAddToHomeDismissed', '1');
	};

	/**
	 * Is the prompt already dismissed by a timeout or
	 * a user clicking the X?
	 *
	 * @since 1.11.0
	 *
	 * @returns {boolean}
	 */
	var dismissed = function() {
		return ( localStorage.getItem('superpwaAddToHomeDismissed') || getCookie( 'superpwaAddToHomeIncrement' ) );
	};

	if ( ! dismissed() ) {
		/**
		 * Fires when all criteria is available to install the app
		 *
		 * @link https://developers.google.com/web/fundamentals/app-install-banners/
		 */
		window.addEventListener('beforeinstallprompt', function (deferredPrompt) {
			//only display on web (mobile has their own)
			if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
				return;
			}

			deferredPrompt.preventDefault();
			setupPrompt(deferredPrompt);
		});
	}
})(superpwa_sw, jQuery);
