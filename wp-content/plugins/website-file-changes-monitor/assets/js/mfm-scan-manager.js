/**
 * MFM Scan Monitor
 *
 * Handles scan status monitoring and polling for the Melapress File Monitor plugin.
 *
 * @package MFM
 * @since 2.3.0
 */

(function ($) {
	'use strict';

	$(document).ready(function () {
		// Only run on pages with scan controls.
		if (document.querySelector('#mfm-file-scanning-controls') === null) {
			return;
		}

		// Module variables.
		let eventCounter = 0;
		let neweventsCounter = 0;
		let initTimer;
		let mainTimer;

		/**
		 * Monitor ongoing scan status via REST API.
		 *
		 * @return {void}
		 */
		function monitorScanStatus() {
			fetch(mfmJSData.status_route, {
				method: 'GET',
				credentials: 'same-origin',
				headers: {
					'X-WP-Nonce': mfmJSData.wp_rest_nonce,
				},
			})
				.then(function (response) {
					if (!response.ok) {
						throw new Error('Request failed with status: ' + response.status);
					}
					return response.json();
				})
				.then(function (result) {
					// Check for WordPress REST API error response.
					if (result.code) {
						handleRestError(result);
						return;
					}

					if (eventCounter === 0) {
						eventCounter = result.current_events_count;
					} else if (result.current_events_count > eventCounter) {
						eventCounter = result.current_events_count;
						$('#mfm-events-wrap').load(
							location.href + ' #mfm-events-wrap>*',
							'',
						);
						$('.mfm_event_item_file_list_wrapper:not(.list-show)').each(
							function (index, value) {
								if (
									$(this).find('.mfm-list-item').length <
									mfmJSData.expandListBelowAmount
								) {
									$(this).addClass('list-show');
								}
							},
						);
						neweventsCounter = neweventsCounter + 1;
					}

					if (result.status !== 'scan_complete') {
						$('#data-readout').text(
							result.current_step + ' ' + mfmJSData.youMayContinue,
						);
						$('#mfm_status_monitor_bar').slideDown(300);
					} else {
						$('#run_tool')
							.attr('value', mfmJSData.startScanLabel)
							.removeClass('disabled');
						clearInterval(initTimer);
						clearInterval(mainTimer);
						$('#data-readout').text(result.current_step);
						setTimeout(function () {
							$('#mfm-events-wrap').load(
								location.href + ' #mfm-events-wrap>*',
								'',
							);
							$('.mfm_event_item_file_list_wrapper:not(.list-show)').each(
								function (index, value) {
									if (
										$(this).find('.mfm-list-item').length <
										mfmJSData.expandListBelowAmount
									) {
										$(this).addClass('list-show');
									}
								},
							);
							if (!$('body').hasClass('mfm-scan-init')) {
								$('#mfm_status_monitor_bar').slideUp(300);
							}
						}, 5000);

						setTimeout(function () {
							$('#mfm-events-wrap').load(
								location.href + ' #mfm-events-wrap>*',
								'',
							);
							$('.mfm_event_item_file_list_wrapper:not(.list-show)').each(
								function (index, value) {
									if (
										$(this).find('.mfm-list-item').length <
										mfmJSData.expandListBelowAmount
									) {
										$(this).addClass('list-show');
									}
								},
							);
						}, 4000);
					}
				})
				.catch(function (error) {
					handleFetchError(error);
				});
		}

		/**
		 * Handle WordPress REST API error responses.
		 *
		 * @param {Object} result - The error response object.
		 * @return {void}
		 */
		function handleRestError(result) {
			clearInterval(initTimer);
			clearInterval(mainTimer);

			$('#run_tool')
				.attr('value', mfmJSData.startScanLabel)
				.removeClass('disabled');
			$('#data-readout').text(
				'Session expired or access denied. Please refresh the page.',
			);
			$('#mfm_status_monitor_bar').addClass('mfm-status-error');
		}

		/**
		 * Handle fetch errors (network issues, etc.).
		 *
		 * @param {Error} error - The error object.
		 * @return {void}
		 */
		function handleFetchError(error) {
			clearInterval(initTimer);
			clearInterval(mainTimer);

			$('#run_tool')
				.attr('value', mfmJSData.startScanLabel)
				.removeClass('disabled');
			$('#data-readout').text(
				'Connection error. Please refresh the page and try again.',
			);
			$('#mfm_status_monitor_bar').addClass('mfm-status-error');
		}

		/**
		 * Start the file scan.
		 *
		 * @param {Event} e - The form submit event.
		 * @return {void}
		 */
		function startScan(e) {
			e.preventDefault();
			$('#run_tool')
				.attr('value', mfmJSData.scanInProgressLabel)
				.addClass('disabled');

			const formData = $('#mfm-file-scanning-controls').serializeArray();
			const eventNonce = $('#run_tool').attr('data-nonce');

			$('#data-readout').text(
				'File Scan Initialising ' + mfmJSData.youMayContinue,
			);
			$('#mfm_status_monitor_bar').slideDown(300);
			$('body').addClass('mfm-scan-init');

			$.ajax({
				url: mfmJSData.ajaxURL,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'mfm_start_directory_runner',
					form_data: formData,
					nonce: eventNonce,
				},
				complete: function (data) {
					mainTimer = setInterval(monitorScanStatus, 10000);
					setTimeout(function () {
						$('#mfm-events-wrap').load(
							location.href + ' #mfm-events-wrap>*',
							'',
						);
					}, 200);
					setTimeout(function () {
						$('body').removeClass('mfm-scan-init');
					}, 5000);
				},
			});
		}

		// Check if scan is already active on page load.
		const checkScanActive = document.querySelector('.mfm-scan-is-active');
		if (checkScanActive) {
			initTimer = setInterval(monitorScanStatus, 10000);
			monitorScanStatus();
			$('#run_tool')
				.attr('value', mfmJSData.scanInProgressLabel)
				.addClass('disabled');
		}

		// Bind form submission.
		document
			.querySelector('#mfm-file-scanning-controls')
			.addEventListener('submit', startScan);
	});
})(jQuery);
