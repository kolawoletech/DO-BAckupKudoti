if ( typeof ( wp.UM ) !== 'object' ) {
	wp.UM = {};
}

if ( typeof ( wp.UM.profile_completeness ) !== 'object' ) {
	wp.UM.profile_completeness = {
		widget: {
			wrapper: {},
			build: function( widget ) {
				wp.ajax.send( 'um_profile_completeness_get_widget', {
					data: {
						is_profile: widget.data('is_profile'),
						user_id: widget.data('user_id'),
						nonce: um_scripts.nonce
					},
					success: function( response ) {
						wp.UM.profile_completeness.widget.set_content( response );
						widget.addClass( 'um-is-loaded' );
						init_tipsy();
					},
					error: function( response ) {
						console.error( response );
					}
				});
			},
			set_content: function ( data ) {
				var template = wp.template( 'ultimatemember_profile_completeness' );
				wp.UM.profile_completeness.widget.wrapper.html( template( data ) );
			}
		},
		popup: {
			init_plugins: function( popup ) {

				popup.find('.um-s1').select2({
					allowClear: true,
					minimumResultsForSearch: 10
				});

				popup.find('.um-datepicker').each( function() {
					var elem = jQuery(this);

					var disable = false;
					if ( elem.attr('data-disabled_weekdays') !== '' ) {
						disable = JSON.parse( elem.attr('data-disabled_weekdays') );
					}

					var years_n = elem.attr('data-years');

					var min = elem.attr('data-date_min');
					var max = elem.attr('data-date_max');

					min = min.split(",");
					max = max.split(",");

					elem.pickadate({
						selectYears: years_n,
						min: min,
						max: max,
						disable: disable,
						format: elem.attr('data-format'),
						formatSubmit: 'yyyy/mm/dd',
						hiddenName: true,
						onOpen: function() { elem.blur(); },
						onClose: function() { elem.blur(); }
					});
				});

				popup.find('.um-timepicker').each( function() {
					var elem = jQuery(this);

					elem.pickatime({
						format: elem.attr('data-format'),
						interval: parseInt( elem.attr('data-intervals') ),
						formatSubmit: 'HH:i',
						hiddenName: true,
						onOpen: function() { elem.blur(); },
						onClose: function() { elem.blur(); }
					});
				});

			},
			load: function( step ) {

				var key = step.data('key');
				if ( typeof key !== 'undefined' && ! step.parents('.um-completeness-step').hasClass('completed') ) {
					prepare_Modal();

					wp.ajax.send( 'um_profile_completeness_edit_popup', {
						data: {
							key: key,
							nonce: um_scripts.nonce
						},
						success: function( response ) {
							show_Modal( response );
							responsive_Modal();

							wp.UM.profile_completeness.popup.init_plugins( jQuery('.um-popup') );
						},
						error: function( response ) {
							remove_Modal();
							console.error( response );
						}
					});
				} else {
					if ( ! step.parents('.um-completeness-step').hasClass( 'is-core' ) ) {
						//e.preventDefault();
						return false;
					}
				}
			},
			save: function( popup ) {
				var no_value = true;

				var type = popup.find('.um-completeness-editwrap').find('input[type=text],input[type=radio],input[type=checkbox],textarea,select').attr('type');
				var key = popup.find('.um-completeness-editwrap').find('input[type=text],input[type=radio],input[type=checkbox],textarea,select').attr('name');

				if ( ! key ) {
					key = popup.find('.um-completeness-editwrap').data('key');
				}

				if ( popup.find('.um-completeness-editwrap').find('select').length && popup.find('.um-completeness-editwrap').find('.picker').length === 0 ) {
					type = 'select';
					key = popup.find('.um-completeness-editwrap').find('select').attr('id');
				}

				var value;

				if ( type === 'radio' ) {

					value = jQuery('input[name="' + key + '"]:checked').val();
					if ( value ) {
						no_value = false;
					}

				} else if ( type === 'checkbox' ) {

					value = [];
					jQuery('input[name="' + key + '"]:checked').each( function(i){
						value.push( jQuery(this).val() );
					});

					if ( value ) {
						no_value = false;
						value = value.join(", ");
					}

				} else if ( type === 'select' ) {

					value = jQuery( '#' + key ).val();

					if ( value ) {
						no_value = false;
					}

					if ( jQuery('.um-popup select[multiple]').length && value ) {
						no_value = false;
						value = value.join(", ");
					}

				} else {

					if ( popup.find('.um-completeness-editwrap').find('textarea').length === 1 ) {
						key = popup.find('.um-completeness-editwrap').find('textarea').attr('id');
					}

					value = jQuery( '.um-completeness-field #' + key ).val();

					if ( popup.find('.picker').length ) {
						value = jQuery( '#' + key + '_hidden' ).val()
							|| jQuery( '#' + key ).parents('.um-field-area').find('[type=hidden][name=' + key + ']').val();
					}

					if ( value.trim().length > 0 ) {
						no_value = false;
					}

				}

				key = key.replace('[]','');
				key = key.replace( 'um_completeness_widget_', '' );

				if ( no_value || ! value ) {
					popup.find('input[name="' + key + '"]').focus();
				} else {

					popup.addClass('um-visible-overlay');

					wp.ajax.send( 'um_profile_completeness_save_popup', {
						data: {
							key: key,
							value: value,
							nonce: um_scripts.nonce
						},
						success: function( data ) {
							var completeness_bar = jQuery( '.um-completeness-bar[data-user_id="' + data.user_id + '"]' );
							completeness_bar.attr('original-title', data.percent + wp.i18n.__( '% Complete', 'um-profile-completeness' ) );
							completeness_bar.find('.um-completeness-done').animate({ width: data.percent + '%' });
							jQuery('.um-completeness-jx[data-user_id="' + data.user_id + '"]').html( data.percent );

							wp.UM.profile_completeness.widget.build( jQuery( '.um-completeness-widget[data-user_id="' + data.user_id + '"]' ) );

							var current_step_div = jQuery( '.um-completeness-step[data-key=' + key + ']' );
							current_step_div.addClass( 'completed' );

							var next_step = current_step_div.next('.um-completeness-step:not(.completed,.is-core)').data('key');
							if ( next_step ) {
								wp.ajax.send( 'um_profile_completeness_edit_popup', {
									data: {
										key: next_step,
										nonce: um_scripts.nonce
									},
									success: function( response ) {
										popup.removeClass('um-visible-overlay');

										show_Modal( response );
										responsive_Modal();

										wp.UM.profile_completeness.popup.init_plugins( popup );
									},
									error: function( response ) {
										remove_Modal();
										console.error( response );
									}
								});
							} else {
								remove_Modal();

								if ( typeof data.redirect != 'undefined' && data.redirect !== '' ) {
									window.location = data.redirect;
								} else {
									location.reload();
								}
							}
						},
						error: function( data ) {
							console.log( data );
							location.reload();
						}
					});
				}
				return false;
			},
			skip: function( popup ) {
				var key = jQuery('.um-completeness-editwrap').attr( 'data-key' );
				var next_step = jQuery('.um-completeness-step[data-key=' + key + ']').next('.um-completeness-step:not(.completed,.is-core)').attr('data-key');

				popup.addClass('um-visible-overlay');

				if ( next_step ) {
					wp.ajax.send( 'um_profile_completeness_edit_popup', {
						data: {
							key: next_step,
							nonce: um_scripts.nonce
						},
						success: function( response ) {
							popup.removeClass('um-visible-overlay');

							show_Modal( response );
							responsive_Modal();

							wp.UM.profile_completeness.popup.init_plugins( popup );
						},
						error: function( response ) {
							remove_Modal();
							console.error( response );
						}
					});
				} else {
					remove_Modal();
				}

				return false;
			}
		},
		init: function() {
			wp.UM.profile_completeness.widget.wrapper = jQuery('.um-completeness-widget-wrapper');

			/**
			 * Show "Complete your Profile" widget content
			 */
			if ( wp.UM.profile_completeness.widget.wrapper.length ) {
				wp.UM.profile_completeness.widget.wrapper.each( function() {
					wp.UM.profile_completeness.widget.build( jQuery(this).parents( '.um-completeness-widget' ) );
				});
			}

			/**
			 Skipping a profile progress
			 **/
			jQuery( document.body ).on('click', '.um-completeness-save a.skip',function(e) {
				e.preventDefault();
				wp.UM.profile_completeness.popup.skip( jQuery(this).parents('.um-popup') );
			});

			/**
			 Saving profile progress
			 **/
			jQuery( document.body ).on('click', '.um-completeness-save a.save',function(e){
				e.preventDefault();
				wp.UM.profile_completeness.popup.save( jQuery(this).parents('.um-popup') );
			});


			/**
			 Editing a profile progress
			 **/
			jQuery( document.body ).on('click', '.um-completeness-edit',function(e) {
				var step = jQuery(this);

				if ( ! step.hasClass( 'um-real-url' ) ) {
					wp.UM.profile_completeness.popup.load( step );
				}
			});


			// Append user id in modal for image uploads
			jQuery( document.body ).on( 'click', 'body.um-own-profile a[data-modal="um_upload_single"]', function(e) {
				var user_id = jQuery('.um-completeness-widget').attr('data-user_id');
				if ( user_id ) {
					jQuery("#um_upload_single").attr('data-user_id', user_id );
				}
			});
		}
	};
}


jQuery( document ).ready( function() {
	wp.UM.profile_completeness.init();
});