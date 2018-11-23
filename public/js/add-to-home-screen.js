(function (config, $) {


	var setupPrompt = function (deferredPrompt) {
		var el = $('<div id="superpwa-add-to-home" style="background:' + config.addToHomeColor + '">' + config.addToHomeText + '</div>');
		$('body').prepend(el);

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

	window.addEventListener('beforeinstallprompt', function (deferredPrompt) {
		deferredPrompt.preventDefault();
		var dismissed = localStorage.getItem('superpwaAddToHomeDismissed');
		if (null === dismissed) {
			setupPrompt(deferredPrompt);
		}
	});
})(superpwa_sw, jQuery);
