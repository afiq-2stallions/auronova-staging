
function updateFrequencyVisibility() {
	var selected = jQuery( '[name="mfm-settings[scan-frequency]"]' ).find(":selected").val();if ( selected == 'daily' ) {
		jQuery( '#scan-time-row' ).fadeIn( 0 );
		jQuery( '[name="mfm-settings[scan-day]"], [name="mfm-settings[scan-day]"] + br , [name="mfm-settings[scan-day]"] + br + span' ).fadeOut( 0 );
		jQuery( '[name="mfm-settings[scan-date]"], [name="mfm-settings[scan-date]"] + br, [name="mfm-settings[scan-date]"] + br + span' ).fadeOut( 0 );	
	} else if ( selected == 'hourly' ) {
		jQuery( '#scan-time-row' ).fadeOut( 0 );
	} else if ( selected == 'weekly' ) {
		jQuery( '#scan-time-row' ).fadeIn( 0 );
		jQuery( '[name="mfm-settings[scan-day]"], [name="mfm-settings[scan-day]"] + br , [name="mfm-settings[scan-day]"] + br + span' ).fadeIn( 0 );
		jQuery( '[name="mfm-settings[scan-date]"], [name="mfm-settings[scan-date]"] + br, [name="mfm-settings[scan-date]"] + br + span' ).fadeOut( 0 );	
	}
}

updateFrequencyVisibility();

jQuery(document).ready(function() {

	// Events page.
	if (document.querySelector("#mfm-file-scanning-controls") !== null) {

		// Setup wizard.
		if (document.querySelector("#mfm-setup-wizard") !== null) {

			// Create Wizard.
			jQuery.get( mfmJSData.settingsPageURL, function(html) {
				var newElem = jQuery.parseHTML('<div><h3>' + mfmJSData.wizardIntroTitle + '</h3><p>' + mfmJSData.wizardIntroText + '</p></div>');
				jQuery('#mfm-setup-wizard-content form').append(newElem);
				if ( mfmJSData.isWFCMDataFound ) {
					var oldDataMarkup = jQuery.parseHTML( mfmJSData.MFMWizardPanelMarkup );
					jQuery('#mfm-setup-wizard-content form').append( oldDataMarkup );
				}
				jQuery('#mfm-setup-wizard-content form').append(jQuery(html).find("#mfm-wizard-frequency").prop('outerHTML'));
				jQuery('#mfm-setup-wizard-content form').append(jQuery(html).find("#mfm-wizard-enable-core").prop('outerHTML'));
				jQuery('#mfm-setup-wizard-content form').append(jQuery(html).find("#mfm-wizard-notification").prop('outerHTML') + jQuery(html).find("#mfm-wizard-notification-when").prop('outerHTML'));
				jQuery('#mfm-setup-wizard-content form').append(jQuery(html).find("#mfm-wizard-ignore").prop('outerHTML'));
				jQuery('#mfm-setup-wizard-content form').append(jQuery(html).find("#mfm-wizard-ignore-step-2").prop('outerHTML'));
				jQuery('#mfm-setup-wizard-content form').append(jQuery(html).find("#mfm-wizard-ignore-step-3").prop('outerHTML'));
				jQuery('#mfm-setup-wizard-content form').append(jQuery(html).find("#mfm-wizard-ignore-step-4").prop('outerHTML'));
				jQuery('#mfm-setup-wizard-content form').append(jQuery(html).find("#mfm-wizard-purging").prop('outerHTML'));
				var newElem = jQuery.parseHTML('<div><h3>' + mfmJSData.wizardOutroTitle + '</h3><p>' + mfmJSData.wizardOutroText + '</p></div>');
				jQuery('#mfm-setup-wizard-content form').append(newElem);

				jQuery('#mfm-setup-wizard-content form > div').each(function(i, obj) {
					jQuery(this).attr('data-wizard-step', i);
				});

				jQuery('#mfm-setup-wizard').attr('data-current-wizard-step', 0);
				jQuery('#mfm-wizard-controls a[href="#prev"]').fadeOut(0);
				jQuery('#mfm-setup-wizard').addClass('loaded');
				updateFrequencyVisibility();

				jQuery( '[name="mfm-settings[scan-frequency]"]' ).bind('change', function(e) {
					updateFrequencyVisibility();
				});
			});
			jQuery('body').on('click', 'a[href="#mfm-close-wizard"]', function(e) {
				jQuery('#mfm-setup-wizard').removeClass('loaded');
				jQuery('#mfm-setup-wizard-wrapper').fadeOut(300);
				var nonce = jQuery( this ).attr('data-cancel-wizard-nonce');

				jQuery.ajax({
					url: mfmJSData.ajaxURL,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'mfm_cancel_setup_wizard',
						nonce: nonce,
					},
					complete: function(data) {
						if (data.responseJSON.success) {
							// Silence.
						}
					},
				});
			});
			

			// Wizard controls.
			jQuery('body').on('click', '#mfm-wizard-controls a', function(e) {
				e.preventDefault();
				var direction = jQuery(this).attr('href');
				var maxSteps = jQuery('#mfm-setup-wizard-content form > div').length - 1;
				var currentStep = jQuery('#mfm-setup-wizard').attr('data-current-wizard-step');
				if (direction == '#next') {
					currentStep++
					jQuery('#mfm-setup-wizard').attr('data-current-wizard-step', currentStep);
				} else if (direction == '#prev') {
					jQuery('#mfm-wizard-controls a[href="#finish-setup"]').text('Next step');
					jQuery('#mfm-wizard-controls a[href="#finish-setup"]').attr('href', '#next');
					jQuery('#mfm-setup-wizard').attr('data-current-wizard-step', currentStep - 1);
				}

				var stepNo = currentStep - 1;

				var currentStepID = jQuery( '[data-wizard-step="'+ stepNo +'"]').attr('id');

				if ( 'mfm-wizard-notification' == currentStepID ) {
					if ( 'custom' == jQuery('[name="mfm-settings[email_notice_type]"]:checked').val() ) {
						if ( ! jQuery('#notice-email-address').val() ) {
							jQuery('#notice-email-address').css( 'border-color', 'red' );
							return;
						} else {
							jQuery('#notice-email-address').css( 'border-color', '#8c8f94' );
						}
					}
				}

				if (direction == '#finish-setup') {
					let formData = jQuery("#mfm-setup-wizard form").serializeArray();
					var nonce = jQuery('#mfm-setup-wizard').attr('data-finish-nonce');

					var checkboxes = jQuery("#mfm-setup-wizard form").find( "input[type=checkbox]" );
					jQuery.each( checkboxes, function( key, val ) {
						if ( ! jQuery( this ).is( ':checked' ) ) {
							formData.push( {name: jQuery( val ).attr('name'), value: jQuery( this ).is( ':checked' ) } );
						}
					});

					jQuery.ajax({
						url: mfmJSData.ajaxURL,
						type: 'POST',
						dataType: 'json',
						data: {
							action: 'mfm_finish_setup_wizard',
							form_data: formData,
							remove_old_data: mfmJSData.isWFCMDataFound,
							nonce: nonce,
						},
						complete: function(data) {
							if (data.responseJSON.success) {

								jQuery('#mfm-setup-wizard').removeClass('loaded');
								jQuery('#mfm-setup-wizard').removeClass('loaded');
								setTimeout(() => {
									jQuery('#mfm-setup-wizard-wrapper').fadeOut(300);
								}, 300);

								setTimeout(() => {
									jQuery('#run_tool').trigger('click');
								}, 900);

							}
						},
					});
				}

				if (jQuery('#mfm-setup-wizard').attr('data-current-wizard-step') == 0) {
					jQuery('#mfm-wizard-controls a[href="#prev"]').fadeOut(0);
				} else {
					jQuery('#mfm-wizard-controls a[href="#prev"]').fadeIn(0);
				}

				if (jQuery('#mfm-setup-wizard').attr('data-current-wizard-step') == maxSteps) {
					jQuery('#mfm-wizard-controls a[href="#next"]').attr('href', '#finish-setup');
					jQuery('#mfm-wizard-controls a[href="#finish-setup"]').text('Complete setup & run scan');
				} else {
					jQuery('#mfm-wizard-controls a[href="#next"]').fadeIn(0);
				}

				jQuery('#mfm-setup-wizard-content form > div').slideUp(300);
				setTimeout(() => {
					jQuery('#mfm-setup-wizard-content form > div[data-wizard-step="' + jQuery('#mfm-setup-wizard').attr('data-current-wizard-step') + '"]').slideDown(300);
				}, 300);
			});
		}

		jQuery(document).on('keypress',function(e) {
			var isFocused =  jQuery( '[data-mfm-event-search-input]' ).is(":focus") 
			if( e.which == 13 ) {
				e.preventDefault()
				if ( isFocused ) {
					jQuery('.mfm-run-event-search').trigger('click');
					
				}
			}
		});

		// Remove strays.
		jQuery('.mfm_event_item_file_list_wrapper:empty').remove();

		// Events per-page input.
		jQuery("#mfm-per-page-input input").bind('change', function(e) {
			var eventNonce = jQuery(this).attr('data-event-update-nonce');
			var target = jQuery(this).val();
			jQuery.ajax({
				url: mfmJSData.ajaxURL,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'mfm_update_setting',
					nonce: eventNonce,
					event_type: 'events-view-per-page',
					event_target: target,
				},
				complete: function(data) {
					jQuery('#mfm_inline_notification_bar').text('Settings updated to show ' + target + ' items per page, refreshing now');

					if (data.responseJSON.success) {
						jQuery('#mfm_inline_notification_bar').removeClass('failed').slideDown(300).delay(2000).slideUp(300);
					} else {
						jQuery('#mfm_inline_notification_bar').addClass('failed').slideDown(300).delay(2000).slideUp(300);
					}
					setTimeout(() => {
						location.reload();
					}, 3000);

				},
			});
		});

		jQuery('body').on('click', '.mfm-mark-all-read', function(e) {
			jQuery( '#mfm_inline_mark_read_bar' ).slideDown( 300 );
		});

		jQuery('body').on('click', '#mfm_inline_mark_read_bar a[href="#cancel"]', function(e) {
			e.preventDefault();
			jQuery( '#mfm_inline_mark_read_bar' ).slideUp( 300 );
		});

		jQuery('body').on('click', '#mfm_inline_mark_read_bar a[href="#all-events"]', function(e) {
			e.preventDefault();

			var eventNonce = jQuery(this).attr('data-nonce');
			jQuery.ajax({
				url: mfmJSData.ajaxURL,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'mfm_mark_as_read',
					nonce: eventNonce,
					target: 'all',
				},
				complete: function(data) {
					if (data.responseJSON.success) {
						setTimeout(() => {
							jQuery( '#mfm_inline_mark_read_bar' ).slideUp( 300 )
						}, 200 );
			
						setTimeout(() => {
							location.reload();
						}, 300);
					}
				},
			});
		});

		jQuery('body').on('click', '#mfm_inline_mark_read_bar a[href="#current-events"]', function(e) {
			e.preventDefault();
			jQuery( '.mfm_event_list_item' ).each(function( index, value ) {
				var timer = 300 * index;
				setTimeout(() => {
					jQuery( this ).find( '[data-mfm-mark-as-read]' ).trigger('click');
				}, timer );;
			});
			
			setTimeout(() => {
				jQuery( '#mfm_inline_mark_read_bar' ).slideUp( 300 )
			}, 200 );

			setTimeout(() => {
				location.reload();
			}, 3000);
		});

		jQuery('body').on('click', 'a[data-expand-list]:not(.list-open)', function(e) {
			e.preventDefault();
			jQuery( this ).addClass( 'list-open' );
			jQuery( this ).attr( 'aria-label', mfmJSData.clickToHideLabel );
			jQuery( this ).closest( '.mfm_event_item_file_list_wrapper' ).addClass( 'list-show' );
		});

		jQuery('body').on('click', 'a[data-expand-list].list-open', function(e) {
			e.preventDefault();
			jQuery( this ).removeClass( 'list-open' );
			jQuery( this ).attr( 'aria-label', mfmJSData.clickToViewLabel );
			jQuery( this ).closest( '.mfm_event_item_file_list_wrapper' ).removeClass( 'list-show' );
		});

		jQuery( '.mfm_event_item_file_list_wrapper:not(.list-show)' ).each(function( index, value ) {
			if ( jQuery( this ).find( '.mfm-list-item' ).length < mfmJSData.expandListBelowAmount ) {
				jQuery( this ).addClass( 'list-show' );
			}
		});
		
		jQuery('body').on('click', '.mfm-run-event-search', function(e) {
			e.preventDefault();
			var searchString = jQuery( '[data-mfm-event-search-input]' ).val();
			var eventNonce = jQuery( '.mfm-run-event-search[data-nonce]' ).attr( 'data-nonce' );

			if ( ! searchString ) {
				jQuery( '[data-mfm-event-search-input]' ).css( 'border-color', 'red' );
			} else {
				jQuery( '[data-mfm-event-search-input]' ).css( 'border-color', '#8c8f94' );
				jQuery.ajax({
					url: mfmJSData.ajaxURL,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'mfm_event_lookup',
						nonce: eventNonce,
						lookup_target: searchString,
					},
					complete: function(data) {
						if ( data.responseJSON.success ) {
							var original = jQuery( '#mfm-events-wrap' ).html();
							jQuery( '#mfm-events-wrap' ).html( data.responseJSON.data.event_data );

							jQuery( '#mfm-events-wrap .mfm_event_list_item' ).each(function(index, value) {
								var src_str = jQuery( this ).find( '.mfm_event_item_file_list_container' ).html();
								var term = searchString;
								term = term.replace(/(\s+)/,"(<[^>]+>)*$1(<[^>]+>)*");
								var pattern = new RegExp("("+term+")", "gi");
								
								src_str = src_str.replace(pattern, "<mark>$1</mark>");
								src_str = src_str.replace(/(<mark>[^<>]*)((<[^>]+>)+)([^<>]*<\/mark>)/,"$1</mark>$2<mark>$4");
								
								jQuery( this ).find( '.mfm_event_item_file_list_container' ).html(src_str);
							});

							var tempNotice = jQuery('#mfm_inline_notification_bar').clone();
							jQuery(tempNotice).insertBefore( '#mfm_inline_mark_read_bar' );
							jQuery(tempNotice).text( searchString + ' ' + data.responseJSON.data['message']);
							jQuery(tempNotice).slideDown(300).delay(3000).slideUp(300);
						} else {
							var tempNotice = jQuery('#mfm_inline_notification_bar').clone();
							jQuery(tempNotice).insertBefore( '#mfm_inline_mark_read_bar' );
							jQuery(tempNotice).text( searchString + ' ' + data.responseJSON.data['message']);
							jQuery(tempNotice).addClass('failed').slideDown(300).delay(3000).slideUp(300);
						}
					},
				});

				//alert( 'if we could, we would look for ' + searchString );
			}
		});

		// Load more metadata into view.
		jQuery('body').on('click', '[data-mfm-load-further-changes]', function(e) {
			e.preventDefault();
			var target = jQuery(this).attr('data-mfm-load-further-changes');
			var eventNonce = jQuery(this).attr('data-nonce');
			var offset = (jQuery(this).attr('data-offset-amount')) ? jQuery(this).attr('data-offset-amount') : 0;
			var ourItem = jQuery(this);

			jQuery(ourItem).parent().find('.mfm-action-spinner').fadeIn(300);

			jQuery.ajax({
				url: mfmJSData.ajaxURL,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'mfm_load_extra_metadata',
					nonce: eventNonce,
					event_target: target,
					offset: offset,
				},
				complete: function(data) {

					if (data.responseJSON.success) {
						jQuery(data.responseJSON.data.event_data).each(function(index, value) {
							var newElem = jQuery.parseHTML(mfmJSData.file_display_markup);
							jQuery(newElem).find('[data-change-type-holder]').text(value.event_type);
							jQuery(newElem).find('[data-file-path-holder]').text(value.data);
							jQuery(ourItem).closest('.mfm_event_item_file_list_wrapper').append(newElem);

						});

						var objDiv = jQuery(ourItem).closest('.mfm_event_item_file_list_wrapper');
						var h = objDiv.get(0).scrollHeight;
						objDiv.animate({
							scrollTop: h
						});

						if (data.responseJSON.data.remaining > 0) {
							jQuery(ourItem).closest('.mfm_event_item_file_list_wrapper').append(ourItem.parent());
							jQuery(ourItem).attr('data-offset-amount', data.responseJSON.data.next_offset);
							jQuery(ourItem).parent().find('[data-additional-prefix-holder]').text(data.responseJSON.data.remaining + ' ' + mfmJSData.evenmoreItems);
							jQuery(ourItem).parent().find('a').text(mfmJSData.continueLoading);
							jQuery(ourItem).parent().find('.mfm-action-spinner').fadeOut(300);
						} else {
							jQuery(ourItem).parent().remove();
						}
					} else {
						jQuery(ourItem).parent().find('.mfm-action-spinner').fadeOut(300);
					}
				},
			});
		});

		// Mark item as read.
		jQuery('body').on('click', '[data-mfm-mark-as-read]', function(e) {
			e.preventDefault();
			var wrapper = jQuery(this).closest('.mfm_event_list_item');
			var target = jQuery(this).closest('.mfm_event_list_item').attr('data-event-id');
			var eventNonce = jQuery(this).closest('.mfm_event_list_item').attr('data-event-update-nonce');

			jQuery.ajax({
				url: mfmJSData.ajaxURL,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'mfm_mark_as_read',
					nonce: eventNonce,
					target: target,
				},
				complete: function(data) {
					if (data.responseJSON.success) {
						jQuery(wrapper).slideUp(600);
						var current = jQuery( '#mfm-inline-count' ).text() - 1;
						jQuery( '#mfm-inline-count' ).text( current ) - 1;
					}
				},
			});
		});

		// Update inline setting.
		jQuery('body').on('click', '[data-mfm-update-setting]', function(e) {
			e.preventDefault();

			if (typeof jQuery(this).attr('data-exclude-file') !== 'undefined' && jQuery(this).attr('data-exclude-file') !== false) {
				var event_type = 'exclude-file';
				var target = jQuery(this).attr('data-exclude-file');

			} else if (typeof jQuery(this).attr('data-exclude-directory') !== 'undefined' && jQuery(this).attr('data-exclude-directory') !== false) {
				var event_type = 'exclude-directory';
				var target = jQuery(this).attr('data-exclude-directory');
			} else {
				return;
			}

			var eventNonce = jQuery(this).closest('.mfm_event_list_item').attr('data-event-update-nonce');
			var eventWrapper = jQuery(this).closest('.mfm_event_list_item');

			jQuery('#mfm_inline_notification_bar').slideUp(0);

			jQuery.ajax({
				url: mfmJSData.ajaxURL,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'mfm_update_setting',
					nonce: eventNonce,
					event_type: event_type,
					event_target: target,
				},
				complete: function(data) {
					var tempNotice = jQuery('#mfm_inline_notification_bar').clone();
					jQuery(tempNotice).insertBefore(eventWrapper);
					jQuery(tempNotice).text(target + ' ' + data.responseJSON.data['message']);

					if (data.responseJSON.success) {
						jQuery(tempNotice).removeClass('failed').slideDown(300).delay(3000).slideUp(300);
					} else {
						jQuery(tempNotice).addClass('failed').slideDown(300).delay(3000).slideUp(300);
					}
				},
			});
		});
	}

	// Nab nav within settings.
	jQuery('body').on('click', '.mfm-nav-tab-wrapper:not(.file-events-tabs) .nav-tab', function(e) {
		e.preventDefault();
		var targetID = jQuery(this).attr('data-target-id');
		jQuery('[data-settings-section]').fadeOut(300);
		setTimeout(() => {
			jQuery('[id="' + targetID + '"]').fadeIn(300);
		}, 300);
		jQuery('[data-target-id].nav-tab-active').removeClass('nav-tab-active');
		jQuery('[data-target-id="' + targetID + '"]').addClass('nav-tab-active');

		if (history.pushState) {
			var newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '&tab=' + targetID;
			var $withoutHash = newurl.substr(0, newurl.indexOf('#'));

			var pre = window.location.href.split('&tab=')[0];

			var newurl = window.location.href.split('&tab=')[0] + '&tab=' + targetID;

			if (window.location.protocol + "//" + window.location.host + window.location.pathname != newurl) {
				window.history.pushState({
					path: newurl
				}, '', newurl);
			}
		}
	});

	// Update tab classes.
	if (window.location.href.indexOf("file-monitor-settings") > -1 || window.location.href.indexOf("file-monitor-help") > -1) {
		if (window.location.href.indexOf("tab=") > -1) {
			var targetID = window.location.href.split("tab=").pop();
			jQuery('[data-settings-section]').fadeOut(0);
			jQuery('[id="' + targetID + '"]').fadeIn(0);
			jQuery('[data-target-id].nav-tab-active').removeClass('nav-tab-active');
			jQuery('[data-target-id="' + targetID + '"]').addClass('nav-tab-active');
		} else {
			jQuery('[data-settings-section]:not(#scanning-preferences, #help)').fadeOut(0);
		}
	}

	// Add and remove list items.
	jQuery('body').on('click', '.button[data-list-type]', function(e) {
		e.preventDefault();

		var newElem = jQuery.parseHTML(mfmJSData.excluded_directory_markup);
		var targetList = jQuery(this).attr('data-list-type');
		var newItemInput = jQuery('[data-input-for=' + targetList + ']').val();
		var currentValues = [];
		
		jQuery('[data-list-items-wrapper-for=' + targetList + '] label').each(function(i, obj) {
			currentValues.push( jQuery( this ).text() );
		});

		var eventNonce = jQuery(this).attr('data-validate-setting-nonce');
		var responseArea = jQuery('[data-validation-response-for="' + targetList + '"]');

		if ('' != newItemInput) {
			let pattern = '';
			if (targetList == 'excluded_directories' || targetList == 'ignored_directories') {
				pattern = /^\s*[a-z-._\d/*?]+\s*$/i;
			} else if (targetList == 'excluded_files' || targetList == 'allowed-in-core-files') {
				pattern = /^\s*[a-z-._\d/]+\s*$/i;
			} else if (targetList == 'excluded_file_extensions') {
				pattern = /^\s*[a-z-._\d]+\s*$/i;
			}
			
			var newItemInputLabel = newItemInput;

			if (null === newItemInput.match(pattern)) {
				if (targetList == 'excluded_directories' || targetList == 'ignored_directories') {
					jQuery(responseArea).find('span').text(mfmJSData.dirInvalid);
					jQuery(responseArea).slideDown(300);
				} else if (targetList == 'excluded_files' || targetList == 'allowed-in-core-files') {
					jQuery(responseArea).find('span').text(mfmJSData.fileInvalid);
					jQuery(responseArea).slideDown(300);
				} else if (targetList == 'excluded_file_extensions') {
					jQuery(responseArea).find('span').text(mfmJSData.extensionInvalid);
					jQuery(responseArea).slideDown(300);
				}
			} else {
				if ( currentValues.includes( newItemInput ) ) {
					jQuery(responseArea).find('span').text(mfmJSData.valueAlreadyExists);
					jQuery(responseArea).slideDown(300);
					return;
				}

				if ( targetList == 'ignored_directories' ) {
					var excludedCurrentValues = [];		
					jQuery('[data-list-items-wrapper-for="excluded_directories"] label').each(function(i, obj) {
						excludedCurrentValues.push( jQuery( this ).text() );
					});
					if ( excludedCurrentValues.includes( newItemInput ) ) {
						jQuery(responseArea).find('span').text(mfmJSData.dirAlreadyExcluded);
						jQuery(responseArea).slideDown(300);
						return;
					}
				}

				if (targetList == 'excluded_directories' || targetList == 'included_directories') {
					newItemInputLabel = newItemInput;
					newItemInput = mfmJSData.basepath + newItemInput;
				}

				jQuery(responseArea).slideUp(300);
				jQuery.ajax({
					url: mfmJSData.ajaxURL,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'mfm_validate_setting',
						nonce: eventNonce,
						event_type: targetList,
						event_target: newItemInput,
					},
					complete: function(data) {
						if (data.responseJSON.success) {
							jQuery(newElem).find('input').attr('id', newItemInput).attr('value', newItemInput).attr('name', 'mfm-settings[' + targetList + '][]');
							jQuery(newElem).find('label').attr('for', newItemInput).text( newItemInputLabel );
							jQuery('[data-list-items-wrapper-for=' + targetList + ']').append(newElem);
							jQuery('[data-input-for=' + targetList + ']').css('border', '1px solid #8c8f94');
							jQuery('[data-input-for=' + targetList + ']').val('');
						} else {
							jQuery('[data-input-for=' + targetList + ']').css('border', '1px solid red');
						}
					},
				});
			}
		} else {
			jQuery('[data-input-for=' + targetList + ']').css('border', '1px solid red');
		}

	});

	// Purge data prompt.
	jQuery('body').on('click', '#mfm-perform-purge', function(e) {
		e.preventDefault();
		jQuery('#mfm-purge-proceed').slideDown(300);
	});

	// Purge data prompt.
	jQuery('body').on('click', '#mfm-cancel-in-progress', function(e) {
		e.preventDefault();
		jQuery('#mfm-cancel-proceed').slideDown(300);
	});

	// Reset data promp.
	jQuery('body').on('click', '#mfm-perform-setting-reset', function(e) {
		e.preventDefault();
		jQuery('#mfm-reset-proceed').slideDown(300);
	});

	// Purge data proceed.
	jQuery('body').on('click', '#mfm-purge-proceed a[href="#proceed"]', function(e) {
		e.preventDefault();
		jQuery('#mfm-purge-proceed').slideUp(300);
		var purge_nonce = jQuery(this).attr('data-nonce');
		jQuery.ajax({
			url: mfmJSData.ajaxURL,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'mfm_purge_data',
				nonce: purge_nonce,
			},
			complete: function(data) {
				jQuery('#mfm-purge-response').text(data.responseJSON.data['message']);

				if (data.responseJSON.success) {
					jQuery('#mfm-purge-response').removeClass('failed').slideDown(300).delay(3000).slideUp(300);
					setTimeout(() => {
						window.location.href = mfmJSData.eventPageURL;
					}, 3600);
				} else {
					jQuery('#mfm-purge-response').addClass('failed').slideDown(300).delay(3000).slideUp(300);
				}
			},
		});
	});

	// Reset setting proceed.
	jQuery('body').on('click', '#mfm-reset-proceed a[href="#proceed"]', function(e) {
		e.preventDefault();
		jQuery('#mfm-reset-proceed').slideUp(300);
		var purge_nonce = jQuery(this).attr('data-nonce');
		var targetSetting = document.getElementById('selected-setting').value;

		jQuery.ajax({
			url: mfmJSData.ajaxURL,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'mfm_reset_setting',
				nonce: purge_nonce,
				target: targetSetting,
			},
			complete: function(data) {
				jQuery('#mfm-reset-response').text(data.responseJSON.data['message']);

				if (data.responseJSON.success) {
					jQuery('#mfm-reset-response').removeClass('failed').slideDown(300).delay(3000).slideUp(300);
				} else {
					jQuery('#mfm-reset-response').addClass('failed').slideDown(300).delay(3000).slideUp(300);
				}
			},
		});
	});

	// Cancel scan proceed.
	jQuery('body').on('click', '#mfm-cancel-proceed a[href="#proceed"]', function(e) {
		e.preventDefault();
		jQuery('#mfm-cancel-proceed').slideUp(300);
		var cancel_nonce = jQuery(this).attr('data-nonce');
		jQuery.ajax({
			url: mfmJSData.ajaxURL,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'mfm_cancel_scan',
				nonce: cancel_nonce,
			},
			complete: function(data) {
				jQuery('#mfm-cancel-response').text(data.responseJSON.data['message']);

				if (data.responseJSON.success) {
					jQuery('#mfm-cancel-response').removeClass('failed').slideDown(300).delay(3000).slideUp(300);
					setTimeout(() => {
						//window.location.href = mfmJSData.eventPageURL;
					}, 3600);
				} else {
					jQuery('#mfm-cancel-response').addClass('failed').slideDown(300).delay(3000).slideUp(300);
				}
			},
		});
	});

	// Cancel.
	jQuery('body').on('click', 'a[href="#cancel"]', function(e) {
		e.preventDefault();
		jQuery('#mfm-purge-proceed, #mfm-cancel-proceed').slideUp(300);
	});

	// Pending change notification.
	jQuery('body').on('change', '[data-list-items-wrapper-for] input[type=checkbox]', function(e) {
		var target = jQuery(this).closest('[data-list-items-wrapper-for]').attr('data-list-items-wrapper-for');
		var removedItemsArea = jQuery('[data-marked-for-removal-for="' + target + '"]');
		var currentText = jQuery(removedItemsArea).find('span').text();
		var newVal = jQuery(this).val();
		var currentArray = currentText.split(',');
		var currentArrayfiltered = currentArray.filter(function(el) {
			return el;
		});

		if (!this.checked) {
			currentArrayfiltered.push(newVal);
			var string = currentArrayfiltered.join(', ');
			jQuery(removedItemsArea).find('span').text(string);
		} else {
			var test = currentArrayfiltered.splice(jQuery.inArray(newVal, currentArrayfiltered), 1);
			var string = currentArrayfiltered.join(', ');
			jQuery(removedItemsArea).find('span').text(string);
		}

		if ('' == jQuery(removedItemsArea).find('span').text()) {
			jQuery(removedItemsArea).slideUp(300);
		} else {
			jQuery(removedItemsArea).slideDown(300);
		}
	});

	// Test email.
	jQuery('body').on('click', '#mfm-send-test-email', function(e) {
		e.preventDefault();

		var eventNonce = jQuery(this).attr('data-nonce');
		var selected = jQuery("input[name='mfm-settings[email_notice_type]']:checked").val();

		if (selected == 'admin') {
			var addressToTest = mfmJSData.adminEmail;
		} else {
			var addressToTest = jQuery('#notice-email-address').val();
		}

		jQuery('#mfm-test-email-response').slideUp(0);

		jQuery.ajax({
			url: mfmJSData.ajaxURL,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'mfm_send_test_email',
				nonce: eventNonce,
				email_address: addressToTest,
			},
			complete: function(data) {
				jQuery('#mfm-test-email-response').text(data.responseJSON.data['message']);

				if (data.responseJSON.success) {
					jQuery('#mfm-test-email-response').removeClass('failed').slideDown(300).delay(3000).slideUp(300);
				} else {
					jQuery('#mfm-test-email-response').addClass('failed').slideDown(300).delay(3000).slideUp(300);
				}
			},
		});
	});

	

	jQuery( '[name="mfm-settings[scan-frequency]"]' ).bind('change', function(e) {
		updateFrequencyVisibility();
	});

	jQuery( '[name="mfm-settings[logging-enabled]"]' ).bind('change', function(e) {
		if ( ! this.checked ) {
			if ( document.querySelector('.mfm-scan-is-active') ) {
				jQuery( '#mfm-disable-logging-warning' ).slideDown( 300 );
			} else {
				jQuery.ajax({
					url: mfmJSData.ajaxURL,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'mfm_abort_scan',
					},
					complete: function(data) {
						if ( data.responseJSON.data['message'].status != 'scan_complete'  ) {
							jQuery( '#mfm-disable-logging-warning' ).slideDown( 300 );
						}
					},
				});
			}

			
		}
	});
});
