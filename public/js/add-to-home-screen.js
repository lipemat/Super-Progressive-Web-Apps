(function (config, $) {
	/**
	 * Add the "Add To Home" prompt to the screen
	 * and handle the event when clicked on
	 *
	 * @param {event} deferredPrompt
	 */
	var setupPrompt = function (deferredPrompt) {
		var el = $('<div id="superpwa-add-to-home" style="background:' + config.addToHomeColor + '; display:none">' + config.addToHomeText + '</div>');
		$('body').prepend(el);
		$(el).slideDown();

		/**
		 * Trigger the prompt on click
		 */
		el.on('click', 'span', function (e) {
			el.remove();

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
					if (position <= 0) {
						el.slideUp();
					}
					scrollTimeout = null;
				}, 500);
			}
		});

	};

	var dismiss = function () {
		localStorage.setItem('superpwaAddToHomeDismissed', '1');
	};

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
		var dismissed = localStorage.getItem('superpwaAddToHomeDismissed');
		if (null === dismissed) {
			setupPrompt(deferredPrompt);
		}
	});
})(superpwa_sw, jQuery);
