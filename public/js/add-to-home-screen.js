(function (config, $) {


	var setupPrompt = function (deferredPrompt) {
		var el = $('<div id="superpwa-add-to-home" style="background:' + config.addToHomeColor + '; display:none">' + config.addToHomeText + '</div>');
		$('body').prepend(el);
		$(el).slideDown();

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
		if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
			return;
		}

		deferredPrompt.preventDefault();
		var dismissed = localStorage.getItem('superpwaAddToHomeDismissed');
		if (null === dismissed) {
			setupPrompt(deferredPrompt);
		}
	});
})(superpwa_sw, jQuery);
