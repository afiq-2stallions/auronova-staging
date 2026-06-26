/**
 * UAEL Turnstile Integration
 *
 * Handles Cloudflare Turnstile widget initialization and token management
 * for UAE login and registration forms.
 *
 * @package UAEL
 * @since x.x.x
 */

(function() {
	'use strict';

	// Global variables accessible across all scopes
	window.uaelTurnstileWidgets = window.uaelTurnstileWidgets || {};
	window.uaelTurnstileTokens = window.uaelTurnstileTokens || {};

	/**
	 * Turnstile API loaded callback
	 */
	window.uaelTurnstileOnLoad = function() {
		// API loaded, rendering widgets
		// The widget will auto-render due to data attributes
	};

	/**
	 * UAE-specific Turnstile success callback
	 *
	 * @param {string} token - The Turnstile token
	 */
	window.onUAELTurnstileCallback = function(token) {
		var widgetId = this.dataset && this.dataset.widgetId ? this.dataset.widgetId : '';
		var nodeId = this.dataset && this.dataset.nodeId ? this.dataset.nodeId : '';

		// Store token globally
		if (nodeId) {
			window.uaelTurnstileTokens[nodeId] = token;
		}

		// Find the form containing the Turnstile widget
		var turnstileWidget = widgetId ? document.getElementById(widgetId) : this;
		var form = turnstileWidget ? turnstileWidget.closest('form') : null;

		if (form) {
			// Remove any existing token inputs first
			var existingInputs = form.querySelectorAll('input[name="cf-turnstile-response"]');
			existingInputs.forEach(function(input) {
				input.remove();
			});

			// Add fresh hidden input
			var hiddenInput = document.createElement('input');
			hiddenInput.type = 'hidden';
			hiddenInput.name = 'cf-turnstile-response';
			hiddenInput.value = token;
			hiddenInput.className = 'uael-turnstile-token';
			form.appendChild(hiddenInput);

			// Hook into form submission to ensure token persistence
			if (!form.dataset.turnstileHooked) {
				form.dataset.turnstileHooked = 'true';

				// Use both jQuery and native event listeners for broader compatibility
				if (typeof jQuery !== 'undefined') {
					jQuery(form).on('submit', function() {
						ensureTurnstileToken(form, token);
					});
				}

				form.addEventListener('submit', function() {
					ensureTurnstileToken(form, token);
				});
			}
		}
	};

	/**
	 * Helper function to ensure token is in form
	 *
	 * @param {HTMLFormElement} form - The form element
	 * @param {string} token - The Turnstile token
	 */
	function ensureTurnstileToken(form, token) {
		var tokenInput = form.querySelector('input[name="cf-turnstile-response"]');
		if (!tokenInput) {
			tokenInput = document.createElement('input');
			tokenInput.type = 'hidden';
			tokenInput.name = 'cf-turnstile-response';
			tokenInput.className = 'uael-turnstile-token';
			form.appendChild(tokenInput);
		}
		tokenInput.value = token;
	}

	/**
	 * UAE-specific Turnstile error callback
	 *
	 * @param {string} error - The error message
	 */
	window.onUAELTurnstileError = function(error) {
		// Error callback - can be extended for custom error handling
	};

	// Hook into jQuery AJAX to inject token at the last moment
	if (typeof jQuery !== 'undefined') {
		// Store original jQuery.ajax
		var originalAjax = jQuery.ajax;

		// Override jQuery.ajax
		jQuery.ajax = function(options) {
			// Check if this is a UAE form submission - check multiple possible patterns
			var isUAEForm = false;
			if (options && options.url && typeof options.url === 'string') {
				isUAEForm = options.url.indexOf('uael_login_form_submit') !== -1 ||
							options.url.indexOf('uael_register_form_submit') !== -1 ||
							options.url.indexOf('admin-ajax.php') !== -1;
			}

			// Also check the data for UAE action
			if (!isUAEForm && options && options.data) {
				var dataStr = '';
				if (typeof options.data === 'string') {
					dataStr = options.data;
				} else if (typeof options.data === 'object') {
					dataStr = JSON.stringify(options.data);
				}
				isUAEForm = dataStr.indexOf('uael_login_form_submit') !== -1 ||
							dataStr.indexOf('uael_register_form_submit') !== -1;
			}

			if (isUAEForm) {
				// Find any available Turnstile token
				var availableToken = '';
				for (var nodeId in window.uaelTurnstileTokens) {
					if (window.uaelTurnstileTokens[nodeId]) {
						availableToken = window.uaelTurnstileTokens[nodeId];
						break;
					}
				}

				// Also check for any cf-turnstile-response inputs on the page
				if (!availableToken) {
					var tokenInputs = document.querySelectorAll('input[name="cf-turnstile-response"]');
					if (tokenInputs.length > 0) {
						availableToken = tokenInputs[0].value;
					}
				}

				if (availableToken) {
					// Ensure data object exists
					if (!options.data) {
						options.data = {};
					}

					// Handle different data formats
					if (typeof options.data === 'string') {
						// Data is URL-encoded string
						options.data += '&cf-turnstile-response=' + encodeURIComponent(availableToken);
					} else if (typeof options.data === 'object') {
						// Data is object
						options.data['cf-turnstile-response'] = availableToken;

						// Also add to data sub-object if it exists
						if (options.data.data) {
							options.data.data['cf-turnstile-response'] = availableToken;
						}
					}
				}

				// Add nonce for security
				if (typeof uaelTurnstileData !== 'undefined' && uaelTurnstileData.nonce) {
					// Ensure data object exists
					if (!options.data) {
						options.data = {};
					}

					// Handle different data formats
					if (typeof options.data === 'string') {
						// Data is URL-encoded string
						options.data += '&uael_turnstile_nonce=' + encodeURIComponent(uaelTurnstileData.nonce);
					} else if (typeof options.data === 'object') {
						// Data is object
						options.data['uael_turnstile_nonce'] = uaelTurnstileData.nonce;
					}
				}
			}

			// Call original AJAX function
			return originalAjax.call(this, options);
		};
	}

	// Fallback: Try to capture token using MutationObserver
	if (typeof MutationObserver !== 'undefined') {
		var observer = new MutationObserver(function(mutations) {
			mutations.forEach(function(mutation) {
				if (mutation.type === 'childList') {
					mutation.addedNodes.forEach(function(node) {
						if (node.nodeType === 1 && node.tagName === 'INPUT' && node.name === 'cf-turnstile-response') {
							// Token input detected via MutationObserver
						}
					});
				}
			});
		});
		observer.observe(document.body, { childList: true, subtree: true });
	}
})();
