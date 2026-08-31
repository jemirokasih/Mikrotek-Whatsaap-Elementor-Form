(function ($) {
	'use strict';

	/**
	 * Validate if URL is a trusted WhatsApp URL
	 * @param {string} urlString
	 * @returns {boolean}
	 */
	function isSafeWhatsAppUrl(urlString) {
		try {
			var parsedUrl = new URL(urlString, window.location.href);
			if (parsedUrl.protocol !== 'https:') {
				return false;
			}
			var hostname = parsedUrl.hostname.toLowerCase();
			return hostname === 'api.whatsapp.com' || hostname === 'wa.me' || hostname === 'web.whatsapp.com';
		} catch (e) {
			return false;
		}
	}

	$(document).ready(function () {
		$(document).on('submit_success', function (event, response) {
			if (
				response &&
				response.data &&
				response.data.mikrotek_wa_redirect &&
				response.data.mikrotek_wa_redirect.url
			) {
				var redirectData = response.data.mikrotek_wa_redirect;
				var targetUrl = String(redirectData.url);
				var target = redirectData.target === '_blank' ? '_blank' : '_self';

				// Security check: only proceed if URL is trusted WhatsApp endpoint
				if (!isSafeWhatsAppUrl(targetUrl)) {
					return;
				}

				if (target === '_blank') {
					var win = window.open(targetUrl, '_blank', 'noopener,noreferrer');
					if (win) {
						win.focus();
					} else {
						// Fallback if popup blocker intercepted
						window.location.href = targetUrl;
					}
				} else {
					window.location.href = targetUrl;
				}
			}
		});
	});
})(jQuery);
