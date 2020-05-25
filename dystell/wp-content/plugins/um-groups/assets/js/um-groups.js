var um_groups_members_directory_busy = [];
var um_groups_members_directories = [];

jQuery(document).ready(function () {

	/**
	 * Join Group
	 */
	jQuery(document).on('click', '.um-groups-btn-join', function () {
		var me = jQuery(this);
		var group_id = me.data('group_id');
		var wrap_actions = me.parents('div.actions');

		jQuery.ajax({
			method: 'POST',
			url: wp.ajax.settings.url,
			type: 'post',
			data: {action: 'um_groups_join_group', group_id: group_id, nonce: um_scripts.nonce}
		}).done(function (data) {

			if ( data.status === true ) {
				me.addClass('um-groups-btn-leave');
				me.removeClass('um-groups-btn-join');
				me.text(data.labels.leave);
				me.addClass('um-groups-btn-join-request');

				if ( data.privacy === 'public' ) {
					wrap_actions.find('ul').find('li.count-members').find('span').text(data.members);
					me.addClass( 'um-groups-has-joined' );
				} else if ( data.privacy === 'hidden' ) {
					me.hide();
				}

				if ( me.parents( '.um-group-single-header' ).length ) {
					window.location.reload();
				}
			}

		}).error(function (error) {
			console.log('join group error', error);
		});
	});

	/**
	 * Leave Group
	 */
	jQuery(document).on('click', '.um-groups-btn-leave', function () {
		var me = jQuery(this);
		var group_id = me.data('group_id');
		var wrap_actions = me.parents('div.actions');

		jQuery.ajax({
			method: 'POST',
			url: wp.ajax.settings.url,
			type: 'post',
			data: {action: 'um_groups_leave_group', group_id: group_id, nonce: um_scripts.nonce}
		}).done(function (data) {

			if (data.status) {
				me.addClass('um-groups-btn-join');
				me.removeClass('um-groups-btn-leave');
				me.removeClass('um-groups-btn-join-request');
				me.text( data.labels.join );
				me.attr( 'data-groups-button-hover', data.labels.hover );

				if ( data.privacy == 'public' || data.privacy == 'private' ) {
					wrap_actions.find('ul').find('li.count-members').find('span').text( data.members );
				}

				if ( me.parents( '.um-group-single-header' ).length ) {
					window.location.reload();
				}
			}

		}).error(function (error) {
			console.log('leave group error', error);
		});
	});

	/**
	 * Switch button text
	 */
	jQuery(document).on({
		mouseenter: function () {
			var me = jQuery(this);
			var label = me.attr('data-groups-button-hover');
			if (label) {
				me.text(label);
			}
			me.addClass('um-groups-btn-leave');
		},
		mouseleave: function () {
			var me = jQuery(this);
			var label = me.attr('data-groups-button-default');
			if (label) {
				me.text(label);
			}
			me.removeClass('um-groups-btn-leave');
		}
	}, '.um-groups-single-button a.um-button:not(.um-groups-btn-join,.um-groups-has-joined)');

	/**
	 * Swap invite button label and icon
	 */
	jQuery(document).on({
		mouseenter: function () {
			var me = jQuery(this);
			me.removeClass('disabled');
			me.html('<span class="um-faicon-paper-plane-o"></span> ' + um_scripts.groups_settings.labels.resend);
		},
		mouseleave: function () {
			var me = jQuery(this);
			me.addClass('disabled');
			me.html('<span class="um-faicon-check"></span> ' + um_scripts.groups_settings.labels.invited);

		}
	}, '#um-group-buttons .um-group-button[data-action-key="resend_invite"]:not(.um-groups-has-invited)');

	/**
	 * Show/Hide role and status options
	 */
	jQuery(document).on({
		mouseenter: function () {
			var tr = jQuery(this);
			tr.find('td').find('select').show();
			tr.find('td').find('span.label').hide();
		},
		mouseleave: function () {
			var tr = jQuery(this);
			tr.find('td').find('select').hide();
			tr.find('td').find('span.label').show();
		}
	}, 'table#um_groups_manage_members tbody tr');

	/**
	 * Redirect to default tab
	 */
	function um_groups_default_tab() {
		jQuery('ul.um-groups-single-tabs li:first a').trigger('click');
	}

	jQuery('ul.um-groups-single-tabs li:first a').on('click', function () {
		window.location.href = jQuery(this).attr('href');
	});

	/**
	 * Confirm Join Invite
	 */
	jQuery(document.body).on('click', '.um-groups-confirm-invite', function () {
		var me = jQuery(this);
		var user_id = me.attr('data-user_id');
		var group_id = me.attr('data-group_id') || jQuery('input[name="group_id"]').val();

		jQuery.ajax({
			method: 'POST',
			url: wp.ajax.settings.url,
			type: 'post',
			data: {action: 'um_groups_confirm_invite', group_id: group_id, user_id: user_id, self: true, nonce: um_scripts.nonce}

		}).done(function (data) {
			window.location.reload();

		}).error(function (e) {
			console.log(e);
		});
	});

	jQuery(document.body).on('click', '.um-groups-confirm-invite-in-list', function () {
		var me = jQuery(this);
		var user_id = me.attr('data-user_id');
		var group_id = me.attr('data-group');

		jQuery.ajax({
			method: 'POST',
			url: wp.ajax.settings.url,
			type: 'post',
			data: {action: 'um_groups_confirm_invite', group_id: group_id, user_id: user_id, self: true, nonce: um_scripts.nonce}

		}).done(function (data) {
			window.location.reload();

		}).error(function (e) {
			console.log(e);
		});
	});

	/**
	 * Ignore Join Invite
	 */
	jQuery(document.body).on('click', '.um-groups-ignore-invite', function () {
		var me = jQuery(this);
		var user_id = me.attr('data-user_id');
		var group_id = me.attr('data-group_id') || jQuery('input[name="group_id"]').val();

		jQuery.ajax({
			method: 'POST',
			url: wp.ajax.settings.url,
			type: 'post',
			data: {action: 'um_groups_ignore_invite', group_id: group_id, user_id: user_id, nonce: um_scripts.nonce}

		}).done(function (data) {
			window.location.reload();

		}).error(function (e) {
			console.log(e);
		});
	});

	jQuery(document.body).on('click', '.um-groups-ignore-invite-in-list', function () {
		var me = jQuery(this);
		var user_id = me.attr('data-user_id');
		var group_id = me.attr('data-group');

		jQuery.ajax({
			method: 'POST',
			url: wp.ajax.settings.url,
			type: 'post',
			data: {action: 'um_groups_ignore_invite', group_id: group_id, user_id: user_id, nonce: um_scripts.nonce}

		}).done(function (data) {
			window.location.reload();

		}).error(function (e) {
			console.log(e);
		});
	});

	/**
	 * Show more buttons
	 */
	jQuery(document).on('click', '.um-group-buttons a.um-group-button-more', function () {
		var me = jQuery(this);

		jQuery('.um-group-buttons a.um-group-button-more.active').removeClass('active');

		var group_buttons = me.parent('.um-group-buttons').find('ul[class=um-group-buttons-more]');
		if (group_buttons.is(':visible')) {
			group_buttons.hide();
		} else {
			jQuery('ul[class=um-group-buttons-more]').hide();
			group_buttons.show();
			me.addClass('active');
		}
		return false;
	});

	/**
	 * Group User Actions
	 */
	jQuery(document.body).on('click', '.um-group-buttons a[data-action-key]', function () {
		var me = jQuery(this);
		var parent = me.parents('.um-groups-user-wrap');
		var action = me.data('action-key');
		var group_id = parent.data("group-id");
		var user_id = parent.data("group-uid");
		//var members_count = jQuery('.um-groups-single').find('.um-group-members-count').find('.count');

		switch ( action ) {
			/* invites tab */
			case 'invite':

				if ( ! me.hasClass( 'um-groups-has-invited' ) ) {
					wp.ajax.send( 'um_groups_send_invitation_mail', {
						data: {
							group: group_id,
							user_id: user_id,
							nonce: um_scripts.nonce
						},
						success: function( data ) {
							me.addClass('disabled');
							me.addClass('um-groups-has-invited');
							me.html('<span class="um-faicon-check"></span> ' + um_scripts.groups_settings.labels.invited);
						},
						error: function( data ) {
							console.log( 'send invite error', data );
						}
					});
				}

				break;

			/* invites tab */
			case 'resend_invite':

				if ( ! me.hasClass( 'um-groups-has-invited' ) ) {
					wp.ajax.send( 'um_groups_send_invitation_mail', {
						data: {
							group: group_id,
							user_id: user_id,
							nonce: um_scripts.nonce
						},
						success: function( data ) {
							me.addClass('disabled');
							me.addClass('um-groups-has-invited');
							me.html('<span class="um-faicon-check"></span> ' + um_scripts.groups_settings.labels.resent_invite);
						},
						error: function( data ) {
							console.log('resend invite error', error);
						}
					});
				}

				break;

			/* blocked tab */
			case 'unblock':

				wp.ajax.send( 'um_groups_unblock_user', {
					data: {
						group: group_id,
						user_id: user_id,
						nonce: um_scripts.nonce
					},
					success: function( data ) {
						parent.fadeOut();
						um_groups_update_tab_count();
					},
					error: function( data ) {
						console.log('unblock member error', data );
					}
				});

				break;
		}

		return false;
	});

	/**
	 * Group User Actions in dropdown
	 */
	jQuery(document.body).on('click', '.um-group-buttons .um-new-dropdown li a', function () {

		var me = jQuery(this);
		var parent = me.parents('.um-groups-user-wrap');
		var action = me.attr('class');

		var group_id = parent.data('group-id');
		var user_id = parent.data('group-uid');
		//var members_count = jQuery('.um-groups-single').find('.um-group-members-count').find('.count');

		switch ( action ) {
			/* members tab */
			case 'make-admin':
			case 'make-moderator':
			case 'make-member':

				wp.ajax.send( 'um_groups_change_member_role', {
					data: {
						group: group_id,
						user_id: user_id,
						role: action.replace( 'make-', '' ),
						nonce: um_scripts.nonce
					},
					success: function( data ) {
						if ( typeof data.dropdown_actions != 'undefined' ) {
							var menu_html = '';

							jQuery.each( data.dropdown_actions, function( i ) {
								menu_html += '<li><a href="javascript:void(0);" class="' + i + '">' + data.dropdown_actions[ i ]['title'] + '</a></li>';
							});

							me.parents('.um-group-buttons').find( '.um-member-actions .um-new-dropdown ul' ).html( menu_html )
						} else {
							me.parents('.um-group-buttons').html('');
						}
					},
					error: function( data ) {
						console.log( 'change member role error', error, data );
					}
				});

				break;

			/* members tab */
			case 'remove-from-group':

				if ( confirm( wp.i18n.__( 'Are you sure that you want to expel this member?', 'um-groups' ) ) ) {
					wp.ajax.send( 'um_groups_delete_member', {
						data: {
							group: group_id,
							user_id: user_id,
							nonce: um_scripts.nonce
						},
						success: function( data ) {
							parent.fadeOut();
							um_groups_update_tab_count();
						},
						error: function( data ) {
							console.log( data );
						}
					});
				}

				break;

			/* members tab */
			case 'remove-self-from-group':

				if ( confirm( wp.i18n.__( 'Are you sure that you want to leave this group?', 'um-groups' ) ) ) {
					wp.ajax.send( 'um_groups_delete_member', {
						data: {
							group: group_id,
							user_id: user_id,
							nonce: um_scripts.nonce
						},
						success: function( data ) {
							parent.fadeOut();
							um_groups_update_tab_count();

							window.location.reload();
						},
						error: function( data ) {
							console.log( data );
						}
					});
				}

				break;

			/* requests tab */
			case 'approve':

				wp.ajax.send( 'um_groups_approve_user', {
					data: {
						group: group_id,
						user_id: user_id,
						nonce: um_scripts.nonce
					},
					success: function( data ) {
						parent.fadeOut();
						um_groups_update_tab_count();
					},
					error: function( data ) {
						console.log('approve member error', data );
					}
				});

				break;

			/* requests tab */
			case 'reject':

				wp.ajax.send( 'um_groups_reject_user', {
					data: {
						group: group_id,
						user_id: user_id,
						nonce: um_scripts.nonce
					},
					success: function( data ) {
						parent.fadeOut();
						um_groups_update_tab_count();
					},
					error: function( data ) {
						console.log('reject member error', data );
					}
				});

				break;

			/* requests tab */
			case 'block':

				wp.ajax.send( 'um_groups_block_user', {
					data: {
						group: group_id,
						user_id: user_id,
						nonce: um_scripts.nonce
					},
					success: function( data ) {
						parent.fadeOut();
						um_groups_update_tab_count();
					},
					error: function( data ) {
						console.log('block member error', data );
					}
				});

				break;
		}

		return false;
	});


	/**
	 * Updates tab requests count
	 */
	function um_groups_update_tab_count() {
		var requested_users_count = jQuery('li[class^=um-groups-tab-slug_].active').find('.count');
		if ( requested_users_count ) {
			var c = parseInt( requested_users_count.text() ) - 1;
			requested_users_count.text( c );

			if ( c <= 0 ) {
				window.location.reload();
			}
		}
	}


	/**
	 * Groups directory pagination
	 */
	jQuery(document).on('click', '.um-groups-lazy-load', function () {

		var me = jQuery(this);
		var data = {action: 'um_groups_load_more_groups', settings: me.data("groups-pagi-settings"), nonce: um_scripts.nonce};
		var parent = me.parent('.um-groups-list-pagination');
		var page = parseInt(me.attr("data-groups-page")) + 1;

		data.settings.page = page;

		me.text('...');

		jQuery.ajax({
			method: 'POST',
			url: wp.ajax.settings.url,
			type: 'json',
			data: data

		}).done(function (data) {

			if (data.found.total_groups === 0) {
				me.text(me.data('no-more-groups-text'));
				parent.remove();
			} else {
				me.text(me.data('load-more-text'));
				me.attr("data-groups-page", page);
				parent.before(data.html);
			}

			if (page >= data.found.total_pages) {
				parent.remove();
			}

		}).error(function (e) {
			me.text(um_scripts.groups_settings.labels.went_wrong);
		});

		return false;
	});



	/*New code*/

	//filters controls
	jQuery('.um-groups-single .um-member-directory-filters-a').click( function() {
		var obj = jQuery(this);
		var search_bar = obj.parents('.um-groups-invites-users-wrapper').find('.um-search');

		if ( search_bar.is( ':visible' ) ) {
			search_bar.slideUp( 250, function(){
				obj.toggleClass('um-member-directory-filters-visible');
				search_bar.parents('.um-member-directory-header-row').toggleClass('um-header-row-invisible');
			});
		} else {
			search_bar.slideDown({
				start: function() {
					jQuery(this).css({
						display: "grid"
					});
					obj.toggleClass('um-member-directory-filters-visible');
					search_bar.parents('.um-member-directory-header-row').toggleClass('um-header-row-invisible');
				}
			}, 250 );
		}
	});



	if ( jQuery( '.um-groups-users-wrapper' ).length ) {
		jQuery( '.um-groups-users-wrapper' ).each( function() {
			var directory = jQuery(this);

			var hash = um_group_members_get_hash( directory );
			um_groups_members_directories.push( hash );

			um_group_members_show_preloader( directory );

			um_ajax_get_group_members( directory );
		});
	}


	if ( jQuery( '.um-groups-requests-wrapper' ).length ) {
		jQuery( '.um-groups-requests-wrapper' ).each( function() {
			var directory = jQuery(this);

			var hash = um_group_members_get_hash( directory );
			um_groups_members_directories.push( hash );

			um_group_members_show_preloader( directory );

			um_ajax_get_group_requests( directory );
		});
	}


	if ( jQuery( '.um-groups-invites-users-wrapper' ).length ) {
		jQuery( '.um-groups-invites-users-wrapper' ).each( function() {
			var directory = jQuery(this);

			var hash = um_group_members_get_hash( directory );
			um_groups_members_directories.push( hash );

			um_group_members_show_preloader( directory );

			um_ajax_get_group_invites( directory );

			//slider filter
			directory.find('.um-slider').each( function() {
				var slider = jQuery( this );

				var filter_name = slider.data('field_name');

				var min_default_value = um_get_data_for_group( directory, 'filter_' + filter_name + '_from' );
				var max_default_value = um_get_data_for_group( directory, 'filter_' + filter_name + '_to' );
				if ( typeof min_default_value == 'undefined' ) {
					min_default_value = parseInt( slider.data('min') );
				}

				if ( typeof max_default_value == 'undefined' ) {
					max_default_value =  parseInt( slider.data('max') );
				}

				var default_value = [ min_default_value, max_default_value ];

				slider.slider({
					range: true,
					min: parseInt( slider.data('min') ),
					max: parseInt( slider.data('max') ),
					values: default_value,
					create: function( event, ui ) {
						//console.log( ui );
					},
					step: 1,
					slide: function( event, ui ) {
						um_group_set_range_label( jQuery( this ), ui );
					},
					stop: function( event, ui ) {
						if ( ! um_group_is_directory_busy( directory ) ) {
							um_group_set_url_from_data( directory, 'filter_' + filter_name + '_from', ui.values[0] );
							um_group_set_url_from_data( directory, 'filter_' + filter_name + '_to', ui.values[1] );

							//set 1st page after filtration
							directory.data( 'page', 1 ).attr('data-page', 1);
							um_group_set_url_from_data( directory, 'page', '' );

							um_ajax_get_group_invites( directory );

							um_group_filters_change_tag( directory );

							directory.data( 'searched', 1 );
						}
					}
				});

				um_group_set_range_label( slider );
			});


			//datepicker filter
			directory.find('.um-datepicker-filter').each( function() {
				var elem = jQuery(this);

				var min = new Date( elem.data('date_min')*1000 );
				var max = new Date( elem.data('date_max')*1000 );

				var $input = elem.pickadate({
					selectYears: true,
					min: min,
					max: max,
					formatSubmit: 'yyyy/mm/dd',
					hiddenName: true,
					onOpen: function() {
						elem.blur();
					},
					onClose: function() {
						elem.blur();
					},
					onSet: function( context ) {
						if ( um_group_is_directory_busy( directory ) ) {
							return;
						}

						um_group_members_show_preloader( directory );

						var filter_name = elem.data( 'filter_name' );
						var range = elem.data( 'range' );

						var current_value_from = um_get_data_for_group( directory, 'filter_' + filter_name + '_from' );
						var current_value_to = um_get_data_for_group( directory, 'filter_' + filter_name + '_to' );
						if ( typeof current_value_from === "undefined" ) {
							current_value_from = min / 1000;
						}
						if ( typeof current_value_to === "undefined" ) {
							current_value_to = max / 1000;
						}

						var select_val = context.select / 1000;
						var change_val = elem.val();

						if ( range === 'from' ) {
							current_value_from = select_val;
						} else if ( range === 'to' ) {
							current_value_to = select_val;
						}

						um_group_set_url_from_data( directory, 'filter_' + filter_name + '_from', current_value_from );
						um_group_set_url_from_data( directory, 'filter_' + filter_name + '_to', current_value_to );

						//set 1st page after filtration
						directory.data( 'page', 1 ).attr('data-page', 1);
						um_group_set_url_from_data( directory, 'page', '' );

						um_ajax_get_group_invites( directory );

						um_group_filters_change_tag( directory );

						directory.data( 'searched', 1 );
					}
				});

				var $picker = $input.pickadate('picker');
				var $fname = elem.data('filter_name');
				var $frange = elem.data('range');

				var query_value = um_get_data_for_group( directory, 'filter_' + $fname + '_' + $frange );
				if ( typeof query_value !== 'undefined' ) {
					$picker.set( 'select', query_value*1000 );
				}

			});


			//timepicker filter
			directory.find('.um-timepicker-filter').each( function() {
				var elem = jQuery(this);

				//using arrays formatted as [HOUR,MINUTE]

				var min = elem.data('min');
				var max = elem.data('max');
				var picker_min = min.split(':');
				var picker_max = max.split(':');

				var $input = elem.pickatime({
					format:         elem.data('format'),
					interval:       parseInt( elem.data('intervals') ),
					min: [picker_min[0],picker_min[1]],
					max: [picker_max[0],picker_max[1]],
					formatSubmit:   'HH:i',
					hiddenName:     true,
					onOpen:         function() { elem.blur(); },
					onClose:        function() { elem.blur(); },
					onSet:          function( context ) {
						if ( um_group_is_directory_busy( directory ) ) {
							return;
						}

						um_group_members_show_preloader( directory );

						var filter_name = elem.data( 'filter_name' );
						var range = elem.data( 'range' );

						var current_value_from = um_get_data_for_group( directory, 'filter_' + filter_name + '_from' );
						var current_value_to = um_get_data_for_group( directory, 'filter_' + filter_name + '_to' );
						if ( typeof current_value_from === "undefined" ) {
							current_value_from = min;
						}
						if ( typeof current_value_to === "undefined" ) {
							current_value_to = max;
						}

						if ( typeof context.select !== 'undefined' ) {
							var select_val = context.select / 60;
							var change_val = elem.val();

							if ( range === 'from' ) {
								current_value_from = select_val + ':00';
							} else if ( range === 'to' ) {
								current_value_to = select_val + ':00';
							}
						} else {
							if ( range === 'from' ) {
								current_value_from = min;
							} else if ( range === 'to' ) {
								current_value_to = max;
							}
						}

						um_group_set_url_from_data( directory, 'filter_' + filter_name + '_from', current_value_from );
						um_group_set_url_from_data( directory, 'filter_' + filter_name + '_to', current_value_to );

						//set 1st page after filtration
						directory.data( 'page', 1 ).attr('data-page', 1);
						um_group_set_url_from_data( directory, 'page', '' );

						um_ajax_get_group_invites( directory );

						um_group_filters_change_tag( directory );

						directory.data( 'searched', 1 );
					}
				});


				var $picker = $input.pickatime('picker');
				var $fname = elem.data('filter_name');
				var $frange = elem.data('range');

				var query_value = um_get_data_for_group( directory, 'filter_' + $fname + '_' + $frange );
				if ( typeof query_value !== 'undefined' ) {
					var arr = query_value.split(':');
					$picker.set( 'select', arr[0]*60 );
				}
			});

			um_group_filters_change_tag( directory );
		});
	}


	if ( jQuery( '.um-groups-blocked-wrapper' ).length ) {
		jQuery( '.um-groups-blocked-wrapper' ).each( function() {
			var directory = jQuery(this);

			var hash = um_group_members_get_hash( directory );
			um_groups_members_directories.push( hash );

			um_group_members_show_preloader( directory );

			um_ajax_get_group_blocked( directory );
		});
	}



	//history events when back/forward and change window.location.hash
	window.addEventListener( "popstate", function(e) {
		if ( jQuery( '.um-groups-users-wrapper' ).length ) {
			jQuery( '.um-groups-users-wrapper' ).each( function() {
				var directory = jQuery(this);

				var hash = um_group_members_get_hash( directory );
				um_groups_members_directories.push( hash );

				um_group_members_show_preloader( directory );

				um_ajax_get_group_members( directory );
			});
		}

		if ( jQuery( '.um-groups-requests-wrapper' ).length ) {
			jQuery( '.um-groups-requests-wrapper' ).each( function() {
				var directory = jQuery(this);

				var hash = um_group_members_get_hash( directory );
				um_groups_members_directories.push( hash );

				um_group_members_show_preloader( directory );

				um_ajax_get_group_requests( directory );
			});
		}

		if ( jQuery( '.um-groups-blocked-wrapper' ).length ) {
			jQuery( '.um-groups-blocked-wrapper' ).each( function() {
				var directory = jQuery(this);

				var hash = um_group_members_get_hash( directory );
				um_groups_members_directories.push( hash );

				um_group_members_show_preloader( directory );

				um_ajax_get_group_blocked( directory );
			});
		}

		if ( jQuery( '.um-groups-invites-users-wrapper' ).length ) {
			jQuery( '.um-groups-invites-users-wrapper' ).each( function() {
				var directory = jQuery(this);

				var hash = um_group_members_get_hash( directory );
				um_groups_members_directories.push( hash );

				um_group_members_show_preloader( directory );

				directory.find('.um-groups-members-list, .um-members-pagination-box').html('');

				// set search from history
				if ( directory.find( '.um-member-directory-search-line' ).length ) {
					var search = um_get_data_for_group( directory, 'search' );
					if ( typeof search == 'undefined' ) {
						search = '';
					}
					directory.data( 'general_search', search );
					directory.find('.um-search-line').val( search );
				}

				var page = um_get_data_for_group( directory, 'page' );
				if ( typeof page == 'undefined' ) {
					page = 1;
				} else if ( page > directory.data( 'total_pages' ) ) {
					page = directory.data( 'total_pages' );
				}

				directory.data( 'page', page ).attr( 'data-page', page );

				um_ajax_get_group_invites( directory );

				//datepicker filter
				directory.find('.um-datepicker-filter').each( function() {
					var elem = jQuery(this);

					var $picker = elem.pickadate('picker');
					var $fname = elem.data('filter_name');
					var $frange = elem.data('range');

					var query_value = um_get_data_for_group( directory, 'filter_' + $fname + '_' + $frange );
					if ( typeof query_value !== 'undefined' ) {
						$picker.set( 'select', query_value*1000 );
					} else {
						$picker.clear();
					}
				});


				directory.find('.um-slider').each( function() {
					var slider = jQuery( this );
					var filter_name = slider.data('field_name');

					var min_default_value = um_get_data_for_group( directory, 'filter_' + filter_name + '_from' );
					var max_default_value = um_get_data_for_group( directory, 'filter_' + filter_name + '_to' );
					if ( typeof min_default_value == 'undefined' ) {
						min_default_value = slider.data('min');
					}
					min_default_value = parseInt( min_default_value );

					if ( typeof max_default_value == 'undefined' ) {
						max_default_value =  slider.data('max');
					}
					max_default_value = parseInt( max_default_value );

					slider.slider( 'values', [min_default_value, max_default_value] );
					um_group_set_range_label( slider );
				});

				//timepicker filter
				directory.find('.um-timepicker-filter').each( function() {
					var elem = jQuery(this);

					var $picker = elem.pickatime('picker');
					var $fname = elem.data('filter_name');
					var $frange = elem.data('range');

					var query_value = um_get_data_for_group( directory, 'filter_' + $fname + '_' + $frange );
					if ( typeof query_value !== 'undefined' ) {
						var arr = query_value.split(':');
						$picker.set( 'select', arr[0]*60 );
					} else {
						$picker.clear();
					}
				});

				um_group_filters_change_tag( directory );
			});
		}
	});


	jQuery( document.body ).on( 'click', '.um-group-tab-content-wrap .pagi:not(.current)', function() {
		if ( jQuery(this).hasClass('disabled') ) {
			return;
		}

		var directory;
		if ( jQuery(this).parents( '.um-groups-users-wrapper' ).length ) {
			directory = jQuery(this).parents( '.um-groups-users-wrapper' );
		} else if ( jQuery(this).parents( '.um-groups-requests-wrapper' ).length ) {
			directory = jQuery(this).parents( '.um-groups-requests-wrapper' );
		} else if ( jQuery(this).parents( '.um-groups-invites-users-wrapper' ).length ) {
			directory = jQuery(this).parents( '.um-groups-invites-users-wrapper' );
		} else if ( jQuery(this).parents( '.um-groups-blocked-wrapper' ).length ) {
			directory = jQuery(this).parents( '.um-groups-blocked-wrapper' );
		}

		if ( um_group_is_directory_busy( directory ) ) {
			return;
		}

		um_group_members_show_preloader( directory );

		var page;
		if ( 'first' === jQuery(this).data('page') ) {
			page = 1;
		} else if ( 'prev' === jQuery(this).data('page') ) {
			page = directory.data( 'page' )*1 - 1;
		} else if ( 'next' === jQuery(this).data('page') ) {
			page = directory.data( 'page' )*1 + 1;
		} else if ( 'last' === jQuery(this).data('page') ) {
			page = parseInt( directory.attr( 'total_pages' ) );
		} else {
			page = parseInt( jQuery(this).data('page') );
		}

		if ( page === 1 ) {
			directory.find('.pagi[data-page="first"], .pagi[data-page="prev"]').addClass('disabled');
			directory.find('.pagi[data-page="prev"], .pagi[data-page="last"]').removeClass('disabled');
		} else if ( page === parseInt( directory.attr( 'total_pages' ) ) ) {
			directory.find('.pagi[data-page="prev"], .pagi[data-page="last"]').addClass('disabled');
			directory.find('.pagi[data-page="first"], .pagi[data-page="prev"]').removeClass('disabled');
		} else {
			directory.find('.pagi[data-page="prev"], .pagi[data-page="last"]').removeClass('disabled');
			directory.find('.pagi[data-page="first"], .pagi[data-page="prev"]').removeClass('disabled');
		}

		directory.find('.pagi').removeClass('current');
		directory.find('.pagi[data-page="' + page + '"]').addClass('current');

		directory.data( 'page', page ).attr( 'data-page', page );
		if ( page === 1 ) {
			um_group_set_url_from_data( directory, 'page', '' );
		} else {
			um_group_set_url_from_data( directory, 'page', page );
		}

		if ( jQuery(this).parents( '.um-groups-users-wrapper' ).length ) {
			um_ajax_get_group_members( directory );
		} else if ( jQuery(this).parents( '.um-groups-requests-wrapper' ).length ) {
			um_ajax_get_group_requests( directory );
		} else if ( jQuery(this).parents( '.um-groups-invites-users-wrapper' ).length ) {
			um_ajax_get_group_invites( directory );
		} else if ( jQuery(this).parents( '.um-groups-blocked-wrapper' ).length ) {
			um_ajax_get_group_blocked( directory );
		}
	});


	//mobile pagination
	jQuery( document.body ).on( 'change', '.um-group-tab-content-wrap .um-members-pagi-dropdown', function() {
		var directory;
		if ( jQuery(this).parents( '.um-groups-users-wrapper' ).length ) {
			directory = jQuery(this).parents( '.um-groups-users-wrapper' );
		} else if ( jQuery(this).parents( '.um-groups-requests-wrapper' ).length ) {
			directory = jQuery(this).parents( '.um-groups-requests-wrapper' );
		} else if ( jQuery(this).parents( '.um-groups-invites-users-wrapper' ).length ) {
			directory = jQuery(this).parents( '.um-groups-invites-users-wrapper' );
		} else if ( jQuery(this).parents( '.um-groups-blocked-wrapper' ).length ) {
			directory = jQuery(this).parents( '.um-groups-blocked-wrapper' );
		}

		if ( um_group_is_directory_busy( directory ) ) {
			return;
		}

		um_group_members_show_preloader( directory );

		var page = jQuery(this).val();

		directory.find('.pagi').removeClass('current');
		directory.find('.pagi[data-page="' + page + '"]').addClass('current');

		directory.data( 'page', page ).attr( 'data-page', page );
		if ( page === 1 ) {
			um_group_set_url_from_data( directory, 'page', '' );
		} else {
			um_group_set_url_from_data( directory, 'page', page );
		}

		if ( jQuery(this).parents( '.um-groups-users-wrapper' ).length ) {
			um_ajax_get_group_members( directory );
		} else if ( jQuery(this).parents( '.um-groups-requests-wrapper' ).length ) {
			um_ajax_get_group_requests( directory );
		} else if ( jQuery(this).parents( '.um-groups-invites-users-wrapper' ).length ) {
			um_ajax_get_group_invites( directory );
		} else if ( jQuery(this).parents( '.um-groups-blocked-wrapper' ).length ) {
			um_ajax_get_group_blocked( directory );
		}
	});


	//searching
	jQuery( document.body ).on( 'click', '.um-group-tab-content-wrap .um-do-search', function() {
		var directory;
		if ( jQuery(this).parents( '.um-groups-users-wrapper' ).length ) {
			directory = jQuery(this).parents( '.um-groups-users-wrapper' );
		} else if ( jQuery(this).parents( '.um-groups-requests-wrapper' ).length ) {
			directory = jQuery(this).parents( '.um-groups-requests-wrapper' );
		} else if ( jQuery(this).parents( '.um-groups-invites-users-wrapper' ).length ) {
			directory = jQuery(this).parents( '.um-groups-invites-users-wrapper' );
		} else if ( jQuery(this).parents( '.um-groups-blocked-wrapper' ).length ) {
			directory = jQuery(this).parents( '.um-groups-blocked-wrapper' );
		}

		um_run_group_search( directory );
	});


	//make search on Enter click
	jQuery( document.body ).on( 'keypress', '.um-group-tab-content-wrap .um-search-line', function(e) {
		if ( e.which === 13 ) {
			var directory;
			if ( jQuery(this).parents( '.um-groups-users-wrapper' ).length ) {
				directory = jQuery(this).parents( '.um-groups-users-wrapper' );
			} else if ( jQuery(this).parents( '.um-groups-requests-wrapper' ).length ) {
				directory = jQuery(this).parents( '.um-groups-requests-wrapper' );
			} else if ( jQuery(this).parents( '.um-groups-invites-users-wrapper' ).length ) {
				directory = jQuery(this).parents( '.um-groups-invites-users-wrapper' );
			} else if ( jQuery(this).parents( '.um-groups-blocked-wrapper' ).length ) {
				directory = jQuery(this).parents( '.um-groups-blocked-wrapper' );
			}

			um_run_group_search( directory );
		}
	});


	//filters controls
	jQuery('.um-group-tab-content-wrap .um-member-directory-filters-a').click( function() {
		var obj = jQuery(this);
		var search_bar = obj.parents('.um-directory').find('.um-search');

		if ( search_bar.is( ':visible' ) ) {
			search_bar.slideUp( 250, function(){
				obj.toggleClass('um-member-directory-filters-visible');
				search_bar.parents('.um-member-directory-header-row').toggleClass('um-header-row-invisible');
			});
		} else {
			search_bar.slideDown({
				start: function() {
					jQuery(this).css({
						display: "grid"
					});
					obj.toggleClass('um-member-directory-filters-visible');
					search_bar.parents('.um-member-directory-header-row').toggleClass('um-header-row-invisible');
				}
			}, 250 );
		}
	});


	//filtration process
	jQuery( document.body ).on( 'change', '.um-group-tab-content-wrap .um-search-filter select', function() {
		if ( jQuery(this).val() === '' ) {
			return;
		}

		var directory;
		if ( jQuery(this).parents( '.um-groups-users-wrapper' ).length ) {
			directory = jQuery(this).parents( '.um-groups-users-wrapper' );
		} else if ( jQuery(this).parents( '.um-groups-requests-wrapper' ).length ) {
			directory = jQuery(this).parents( '.um-groups-requests-wrapper' );
		} else if ( jQuery(this).parents( '.um-groups-invites-users-wrapper' ).length ) {
			directory = jQuery(this).parents( '.um-groups-invites-users-wrapper' );
		} else if ( jQuery(this).parents( '.um-groups-blocked-wrapper' ).length ) {
			directory = jQuery(this).parents( '.um-groups-blocked-wrapper' );
		}

		if ( um_group_is_directory_busy( directory ) ) {
			return;
		}

		um_group_members_show_preloader( directory );

		var filter_name = jQuery(this).prop('name');

		var current_value = um_get_data_for_group( directory, 'filter_' + filter_name );
		if ( typeof current_value == 'undefined' ) {
			current_value = [];
		} else {
			current_value = current_value.split( '||' );
		}

		if ( -1 === jQuery.inArray( jQuery(this).val(), current_value ) ) {
			current_value.push( jQuery(this).val() );
			current_value = current_value.join( '||' );

			um_group_set_url_from_data( directory, 'filter_' + filter_name, current_value );

			//set 1st page after filtration
			directory.data( 'page', 1 ).attr('data-page', 1);
			um_group_set_url_from_data( directory, 'page', '' );
		}

		//disable options and disable select if all options are disabled
		jQuery(this).find('option[value="' + jQuery(this).val() + '"]').prop('disabled', true).hide();
		if ( jQuery(this).find('option:not(:disabled)').length === 1 ) {
			jQuery(this).prop('disabled', true);
		}

		jQuery(this).select2('destroy').select2();
		jQuery(this).val('').trigger( 'change' );

		um_ajax_get_group_invites( directory );

		um_group_filters_change_tag( directory );

		directory.data( 'searched', 1 );
	});


	jQuery( document.body ).on( 'click', '.um-group-tab-content-wrap .um-members-filter-remove', function() {
		var directory;
		if ( jQuery(this).parents( '.um-groups-users-wrapper' ).length ) {
			directory = jQuery(this).parents( '.um-groups-users-wrapper' );
		} else if ( jQuery(this).parents( '.um-groups-requests-wrapper' ).length ) {
			directory = jQuery(this).parents( '.um-groups-requests-wrapper' );
		} else if ( jQuery(this).parents( '.um-groups-invites-users-wrapper' ).length ) {
			directory = jQuery(this).parents( '.um-groups-invites-users-wrapper' );
		} else if ( jQuery(this).parents( '.um-groups-blocked-wrapper' ).length ) {
			directory = jQuery(this).parents( '.um-groups-blocked-wrapper' );
		}

		if ( um_group_is_directory_busy( directory ) || ! directory ) {
			return;
		}

		um_group_members_show_preloader( directory );

		var removeItem = jQuery(this).data('value');
		var filter_name = jQuery(this).data('name');

		var type = jQuery(this).data('type');
		if ( type === 'select' ) {

			var current_value = um_get_data_for_group( directory, 'filter_' + filter_name );
			if ( typeof current_value == 'undefined' ) {
				current_value = [];
			} else {
				current_value = current_value.split( '||' );
			}

			if ( -1 !== jQuery.inArray( removeItem.toString(), current_value ) ) {
				current_value = jQuery.grep( current_value, function( value ) {
					return value !== removeItem.toString();
				});
			}

			if ( ! current_value.length ) {
				current_value = '';
			}

			um_group_set_url_from_data( directory, 'filter_' + filter_name, current_value );


			var select = jQuery( '.um-search-filter select[name="' + filter_name + '"]' );
			select.find('option[value="' + removeItem + '"]').prop('disabled', false).show();

			//disable options and disable select if all options are disabled
			if ( select.find('option:not(:disabled)').length > 1 ) {
				select.prop('disabled', false);
			}
			select.select2('destroy').select2();

		} else if ( type === 'slider' ) {
			um_group_set_url_from_data( directory, 'filter_' + filter_name + '_from','' );
			um_group_set_url_from_data( directory, 'filter_' + filter_name + '_to', '' );
		} else if ( type === 'datepicker' ) {
			um_group_set_url_from_data( directory, 'filter_' + filter_name + '_from','' );
			um_group_set_url_from_data( directory, 'filter_' + filter_name + '_to', '' );
		} else if ( type === 'timepicker' ) {
			um_group_set_url_from_data( directory, 'filter_' + filter_name + '_from','' );
			um_group_set_url_from_data( directory, 'filter_' + filter_name + '_to', '' );
		}


		//set 1st page after filtration
		directory.data( 'page', 1 ).attr('data-page', 1);
		um_group_set_url_from_data( directory, 'page', '' );

		jQuery(this).tipsy('hide');
		jQuery(this).parents('.um-members-filter-tag').remove();

		if ( directory.find( '.um-members-filter-remove' ).length === 0 ) {
			directory.find('.um-clear-filters').hide();
		} else {
			directory.find('.um-clear-filters').show();
		}

		um_ajax_get_group_invites( directory );
	});


	jQuery( document.body ).on( 'click', '.um-group-tab-content-wrap .um-clear-filters-a', function() {
		var directory;
		if ( jQuery(this).parents( '.um-groups-users-wrapper' ).length ) {
			directory = jQuery(this).parents( '.um-groups-users-wrapper' );
		} else if ( jQuery(this).parents( '.um-groups-requests-wrapper' ).length ) {
			directory = jQuery(this).parents( '.um-groups-requests-wrapper' );
		} else if ( jQuery(this).parents( '.um-groups-invites-users-wrapper' ).length ) {
			directory = jQuery(this).parents( '.um-groups-invites-users-wrapper' );
		} else if ( jQuery(this).parents( '.um-groups-blocked-wrapper' ).length ) {
			directory = jQuery(this).parents( '.um-groups-blocked-wrapper' );
		}

		if ( um_group_is_directory_busy( directory ) ) {
			return;
		}

		um_group_members_show_preloader( directory );

		directory.find( '.um-members-filter-remove' ).each( function() {
			var removeItem = jQuery(this).data('value');
			var filter_name = jQuery(this).data('name');

			var type = jQuery(this).data('type');
			if ( type === 'select' ) {

				var current_value = um_get_data_for_directory( directory, 'filter_' + filter_name );
				if ( typeof current_value == 'undefined' ) {
					current_value = [];
				} else {
					current_value = current_value.split( '||' );
				}

				if ( -1 !== jQuery.inArray( removeItem.toString(), current_value ) ) {
					current_value = jQuery.grep( current_value, function( value ) {
						return value !== removeItem.toString();
					});
				}

				if ( ! current_value.length ) {
					current_value = '';
				}

				um_group_set_url_from_data( directory, 'filter_' + filter_name, current_value );

				var select = jQuery( '.um-search-filter select[name="' + filter_name + '"]' );
				select.find('option[value="' + removeItem + '"]').prop('disabled', false).show();

				//disable options and disable select if all options are disabled
				if ( select.find('option:not(:disabled)').length > 1 ) {
					select.prop('disabled', false);
				}
				select.select2('destroy').select2();

			} else if ( type === 'slider' ) {
				um_group_set_url_from_data( directory, 'filter_' + filter_name + '_from','' );
				um_group_set_url_from_data( directory, 'filter_' + filter_name + '_to', '' );
			} else if ( type === 'datepicker' ) {
				um_group_set_url_from_data( directory, 'filter_' + filter_name + '_from','' );
				um_group_set_url_from_data( directory, 'filter_' + filter_name + '_to', '' );
			} else if ( type === 'timepicker' ) {
				um_group_set_url_from_data( directory, 'filter_' + filter_name + '_from','' );
				um_group_set_url_from_data( directory, 'filter_' + filter_name + '_to', '' );
			}
		});

		//set 1st page after filtration
		directory.data( 'page', 1 ).attr('data-page', 1);
		um_group_set_url_from_data( directory, 'page', '' );
		directory.find('.um-members-filter-tag').remove();

		//jQuery(this).hide();
		if ( directory.find( '.um-members-filter-remove' ).length === 0 ) {
			directory.find('.um-clear-filters').hide();
			directory.find('.um-clear-filters').parents('.um-member-directory-header-row').addClass( 'um-header-row-invisible' );
		} else {
			directory.find('.um-clear-filters').show();
			directory.find('.um-clear-filters').parents('.um-member-directory-header-row').removeClass( 'um-header-row-invisible' );
		}

		var show_after_search = directory.data('must-search');
		if ( show_after_search === 1 ) {
			var search = um_get_search( directory );
			if ( ! search ) {
				directory.data( 'searched', 0 );
				directory.find('.um-members-grid, .um-members-list').remove();
				directory.find( '.um-member-directory-sorting-options' ).prop( 'disabled', true );
				directory.find( '.um-member-directory-view-type' ).addClass( 'um-disabled' );
				um_group_members_hide_preloader( directory );
				return;
			}
		}

		directory.find( '.um-member-directory-sorting-options' ).prop( 'disabled', false );
		directory.find( '.um-member-directory-view-type' ).removeClass( 'um-disabled' );

		um_ajax_get_group_invites( directory );
	});
}); // end jQuery(document).ready


function um_get_group_filters_data( directory ) {
	var filters_data = [];

	directory.find('.um-search-filter').each( function() {

		var filter = jQuery(this);
		var filter_name,
			filter_title;

		var filter_type;
		if ( filter.find('input.um-datepicker-filter').length ) {
			filter_type = 'datepicker';

			filter.find('input.um-datepicker-filter').each( function() {
				var range = jQuery(this).data('range');
				if ( range === 'to' ) {
					return;
				}

				var filter_name = jQuery(this).data('filter_name');

				var filter_value_from = um_get_data_for_directory( directory, 'filter_' + filter_name + '_from' );
				var filter_value_to = um_get_data_for_directory( directory, 'filter_' + filter_name + '_to' );
				if ( typeof filter_value_from === 'undefined' && typeof filter_value_to === 'undefined' ) {
					return;
				}

				var from_val = jQuery(this).val();
				var to_val = directory.find('input.um-datepicker-filter[data-range="to"][data-filter_name="' + filter_name + '"]').val();

				var value;
				if ( from_val === to_val ) {
					value = to_val;
				} else if ( from_val !== '' &&  to_val !== '' ) {
					value = from_val + ' - ' + to_val;
				} else if ( from_val === '' ) {
					value = 'before ' + to_val;
				} else if ( to_val === '' ) {
					value = 'since ' + from_val;
				}

				filters_data.push( {'name':filter_name, 'label':jQuery(this).data('filter-label'), 'value_label': value, 'value':[filter_value_from, filter_value_to], 'type':filter_type} );
			});

		} else if( filter.find('input.um-timepicker-filter').length ) {
			filter_type = 'timepicker';

			filter.find('input.um-timepicker-filter').each( function() {
				var range = jQuery(this).data('range');
				if ( range === 'to' ) {
					return;
				}

				var filter_name = jQuery(this).data('filter_name');

				var filter_value_from = um_get_data_for_directory( directory, 'filter_' + filter_name + '_from' );
				var filter_value_to = um_get_data_for_directory( directory, 'filter_' + filter_name + '_to' );
				if ( typeof filter_value_from === 'undefined' && typeof filter_value_to === 'undefined' ) {
					return;
				}

				var from_val = jQuery(this).val();
				var to_val = directory.find('input.um-timepicker-filter[data-range="to"][data-filter_name="' + filter_name + '"]').val();

				var value;
				if ( from_val === to_val ) {
					value = to_val;
				} else if ( from_val !== '' &&  to_val !== '' ) {
					value = from_val + ' - ' + to_val;
				} else if ( from_val === '' ) {
					value = 'before ' + to_val;
				} else if ( to_val === '' ) {
					value = 'since ' + from_val;
				}

				filters_data.push( {'name':filter_name, 'label':jQuery(this).data('filter-label'), 'value_label': value, 'value':[filter_value_from, filter_value_to], 'type':filter_type} );
			});
		} else if( filter.find('select').length ) {

			filter_type = 'select';
			filter_name = filter.find('select').attr('name');
			filter_title = filter.find('select').data('placeholder');

			var filter_value = um_get_data_for_directory( directory, 'filter_' + filter_name );
			if ( typeof filter_value == 'undefined' ) {
				filter_value = [];
			} else {
				filter_value = filter_value.split( '||' );
			}

			jQuery.each( filter_value, function(i) {
				var filter_value_title = filter.find('select option[value="' + filter_value[ i ] + '"]').data('value_label');
				filters_data.push( {'name':filter_name, 'label':filter_title, 'value_label':filter_value_title, 'value':filter_value[ i ], 'type':filter_type} );
			});

		} else if( filter.find('div.ui-slider').length ) {
			filter_type = 'slider';

			filter_name = filter.find('div.ui-slider').data( 'field_name' );
			var filter_value_from = um_get_data_for_directory( directory, 'filter_' + filter_name + '_from' );
			var filter_value_to = um_get_data_for_directory( directory, 'filter_' + filter_name + '_to' );

			if ( typeof filter_value_from === 'undefined' && typeof filter_value_to === 'undefined' ) {
				return;
			}

			filter_title = filter.find('div.um-slider-range').data('label');

			var filter_value_title = filter.find('div.um-slider-range').data( 'placeholder' ).replace( '\{min_range\}', filter_value_from )
				.replace( '\{max_range\}', filter_value_to )
				.replace( '\{field_label\}', filter.find('div.um-slider-range').data('label') );

			filters_data.push( {'name':filter_name, 'label':filter_title, 'value_label':filter_value_title, 'value':[filter_value_from, filter_value_to], 'type':filter_type} );
		}
	});

	return filters_data;
}


function um_group_filters_change_tag( directory ) {
	var filters_data = um_get_group_filters_data( directory );

	directory.find('.um-members-filter-tag').remove();

	var filtered_line = directory.find('.um-filtered-line');
	if ( filtered_line.length ){
		var filters_template = wp.template( 'um-members-filtered-line' );
		filtered_line.prepend( filters_template( {'filters': filters_data} ) );

		if ( directory.find( '.um-members-filter-remove' ).length === 0 ) {
			directory.find('.um-clear-filters').hide();
			directory.find('.um-clear-filters').parents('.um-member-directory-header-row').addClass( 'um-header-row-invisible' );
		} else {
			directory.find('.um-clear-filters').show();
			directory.find('.um-clear-filters').parents('.um-member-directory-header-row').removeClass( 'um-header-row-invisible' );
		}
	}
}


function um_group_set_range_label( slider, ui ) {
	var placeholder = '';
	var placeholder_s = slider.siblings( '.um-slider-range' ).data( 'placeholder-s' );
	var placeholder_p = slider.siblings( '.um-slider-range' ).data( 'placeholder-p' );

	if ( ui ) {
		if ( ui.values[ 0 ] === ui.values[ 1 ] ) {
			placeholder = placeholder_s.replace( '\{value\}', ui.values[ 0 ] )
				.replace( '\{field_label\}', slider.siblings( '.um-slider-range' )
					.data('label') );
		} else {
			placeholder = placeholder_p.replace( '\{min_range\}', ui.values[ 0 ] )
				.replace( '\{max_range\}', ui.values[ 1 ] )
				.replace( '\{field_label\}', slider.siblings( '.um-slider-range' )
					.data('label') );
		}
	} else {
		if ( slider.slider( "values", 0 ) === slider.slider( "values", 1 ) ) {
			placeholder = placeholder_s.replace( '\{value\}', slider.slider( "values", 0 ) )
				.replace( '\{field_label\}', slider.siblings( '.um-slider-range' )
					.data('label') );
		} else {
			placeholder = placeholder_p.replace( '\{min_range\}', slider.slider( "values", 0 ) )
				.replace( '\{max_range\}', slider.slider( "values", 1 ) )
				.replace( '\{field_label\}', slider.siblings( '.um-slider-range' )
					.data('label') );
		}
	}
	slider.siblings( '.um-slider-range' ).html( placeholder );

	slider.siblings( ".um_range_min" ).val( slider.slider( "values", 0 ) );
	slider.siblings( ".um_range_max" ).val( slider.slider( "values", 1 ) );
}


function um_get_group_search( directory ) {
	if ( directory.find('.um-search-line').length ) {
		return directory.find( '.um-search-line' ).val();
	} else {
		return '';
	}
}


function um_run_group_search( directory ) {
	if ( um_group_is_directory_busy( directory ) ) {
		return;
	}
	um_group_members_show_preloader( directory );

	var pre_search = um_get_data_for_group( directory, 'search' );

	var search = directory.find('.um-search-line').val();
	if ( search === pre_search || ( search === '' && typeof pre_search == 'undefined' ) ) {
		um_group_members_hide_preloader( directory );
		return;
	}

	directory.data( 'general_search', search );
	um_group_set_url_from_data( directory, 'search', search );

	//set 1st page after search
	directory.data( 'page', 1 ).attr('data-page', 1);
	um_group_set_url_from_data( directory, 'page', '' );

	directory.data( 'searched', 1 );

	if ( directory.hasClass( 'um-groups-users-wrapper' ) ) {
		um_ajax_get_group_members( directory );
	} else if ( directory.hasClass( 'um-groups-requests-wrapper' ) ) {
		um_ajax_get_group_requests( directory );
	} else if ( directory.hasClass( 'um-groups-invites-users-wrapper' ) ) {
		um_ajax_get_group_invites( directory );
	} else if ( directory.hasClass( 'um-groups-blocked-wrapper' ) ) {
		um_ajax_get_group_blocked( directory );
	}
}


function um_get_data_for_group( directory, search_key ) {
	var hash = um_group_members_get_hash( directory );
	var data = {};

	var url_data = um_parse_current_url();
	jQuery.each( url_data, function( key ) {
		if ( key.indexOf( '_' + hash ) !== -1 && url_data[ key ] !== '' ) {
			data[ key.replace( '_' + hash, '' ) ] = url_data[ key ];
		}
	});

	if ( ! search_key ) {
		return data;
	} else {
		if ( typeof data[ search_key ] !== 'undefined' ) {
			try {
				data[ search_key ] = decodeURI( data[ search_key ] );
			} catch(e) { // catches a malformed URI
				console.error(e);
			}
		}

		return data[ search_key ];
	}
}

function um_group_set_url_from_data( directory, key, value ) {
	var hash = um_group_members_get_hash( directory );
	var data = um_get_data_for_group( directory );

	var new_data = {};
	if ( value !== '' ) {
		new_data[ key + '_' + hash ] = value;
	}
	jQuery.each( data, function( data_key ) {
		if ( key === data_key ) {
			if ( value !== '' ) {
				new_data[ data_key + '_' + hash ] = value;
			}
		} else {
			new_data[ data_key + '_' + hash ] = data[ data_key ];
		}
	});

	// added data of other directories to the url
	jQuery.each( um_groups_members_directories, function( k ) {
		var dir_hash = um_groups_members_directories[ k ];
		if ( dir_hash !== hash ) {
			var other_directory = jQuery( '.um-directory[data-hash="' + dir_hash + '"]' );
			var dir_data = um_get_data_for_group( other_directory );

			jQuery.each( dir_data, function( data_key ) {
				new_data[ data_key + '_' + dir_hash ] = dir_data[ data_key ];
			});
		}
	});

	var query_strings = [];
	jQuery.each( new_data, function( data_key ) {
		query_strings.push( data_key + '=' + new_data[ data_key ] );
	});

	query_strings = wp.hooks.applyFilters( 'um_groups_member_directory_url_attrs', query_strings );

	var tab;
	if ( directory.hasClass( 'um-groups-users-wrapper' ) ) {
		tab = 'tab=members';
	} else if ( directory.hasClass( 'um-groups-requests-wrapper' ) ) {
		tab = 'tab=requests';
	} else if ( directory.hasClass( 'um-groups-invites-users-wrapper' ) ) {
		tab = 'tab=invites';
	} else if ( directory.hasClass( 'um-groups-blocked-wrapper' ) ) {
		tab = 'tab=blocked';
	}

	var query_string = '?' + tab;
	if ( query_strings.join( '&' ) !== '' ) {
		query_string += '&' + query_strings.join( '&' );
	}

	window.history.pushState("string", "UM Groups Member Directory", window.location.origin + window.location.pathname + query_string );
}


function um_group_members_get_hash( directory ) {
	return directory.data( 'hash' );
}

function um_group_is_directory_busy( directory ) {
	var hash = um_group_members_get_hash( directory );
	return typeof um_groups_members_directory_busy[ hash ] != 'undefined' && um_groups_members_directory_busy[ hash ];
}


function um_group_members_show_preloader( directory ) {
	um_groups_members_directory_busy[ um_group_members_get_hash( directory ) ] = true;
	directory.find('.um-members-overlay').show();
}


function um_group_members_hide_preloader( directory ) {
	um_groups_members_directory_busy[ um_group_members_get_hash( directory ) ] = false;
	directory.find('.um-members-overlay').hide();
}

function um_ajax_get_group_members( directory ) {

	var hash = um_group_members_get_hash( directory );
	var page = jQuery( directory ).attr( 'data-page' );

	wp.ajax.send( 'um_groups_get_members', {
		data: {
			group_id: hash,
			page: page,
			nonce: um_scripts.nonce
		},
		success: function( data ) {
			if ( data.users ) {
				var template = wp.template( 'um_groups_members' );
				var template_content = template({
					users: data.users,
					group_id: hash
				});
				jQuery('.um-groups-members-list').html( template_content );

				directory.addClass( 'um-loaded' );

				if ( data.pagination ) {
					directory.attr( 'total_pages', data.pagination.total_pages );

					var template = wp.template( 'um-members-pagination' );
					var pagination_content = template({
						pagination: data.pagination
					});
					jQuery('.um-members-pagination-box').html( pagination_content );
				}
			}

			um_group_members_hide_preloader( directory );

			um_init_new_dropdown();
		},
		error: function(e){
			console.log(e);
		}
	});
}


function um_ajax_get_group_requests( directory ) {

	var hash = um_group_members_get_hash( directory );
	var page = jQuery( directory ).attr( 'data-page' );

	wp.ajax.send( 'um_groups_get_requests', {
		data: {
			group_id: hash,
			page: page,
			nonce: um_scripts.nonce
		},
		success: function( data ) {
			if ( data.users ) {
				var template = wp.template( 'um_groups_members' );
				var template_content = template({
					users: data.users,
					group_id: hash
				});
				jQuery('.um-groups-members-list').html( template_content );

				directory.addClass( 'um-loaded' );

				if ( data.pagination ) {
					directory.attr( 'total_pages', data.pagination.total_pages );

					var template = wp.template( 'um-members-pagination' );
					var pagination_content = template({
						pagination: data.pagination
					});
					jQuery('.um-members-pagination-box').html( pagination_content );
				}
			}

			um_group_members_hide_preloader( directory );

			um_init_new_dropdown();
		},
		error: function(e){
			console.log(e);
		}
	});
}


function um_ajax_get_group_invites( directory ) {

	var hash = um_group_members_get_hash( directory );
	var page = jQuery( directory ).attr( 'data-page' );
	var search = um_get_group_search( directory );

	var request = {
		group_id: hash,
		page: page,
		search: search,
		nonce: um_scripts.nonce
	};

	if ( directory.find('.um-search-filter').length ) {
		directory.find('.um-search-filter').each( function() {
			var filter = jQuery(this);

			if ( filter.find( '.um-slider' ).length ) {
				var filter_name = filter.find( '.um-slider' ).data('field_name');

				var value_from = um_get_data_for_group( directory, 'filter_' + filter_name + '_from' );
				var value_to = um_get_data_for_group( directory, 'filter_' + filter_name + '_to' );
				if ( typeof value_from != 'undefined' || typeof value_to != 'undefined' ) {
					request[ filter_name ] = [ value_from, value_to ];
				}
			} else if ( filter.find( '.um-datepicker-filter' ).length ) {
				var filter_name = filter.find( '.um-datepicker-filter' ).data('filter_name');
				var value_from = um_get_data_for_group( directory, 'filter_' + filter_name + '_from' );
				var value_to = um_get_data_for_group( directory, 'filter_' + filter_name + '_to' );
				if (  typeof value_from != 'undefined' || typeof value_to != 'undefined') {
					request[ filter_name ] = [ value_from, value_to ];
				}
			} else if ( filter.find( '.um-timepicker-filter' ).length ) {
				var filter_name = filter.find( '.um-timepicker-filter' ).data('filter_name');
				var value_from = um_get_data_for_group( directory, 'filter_' + filter_name + '_from' );
				var value_to = um_get_data_for_group( directory, 'filter_' + filter_name + '_to' );
				if (  typeof value_from != 'undefined' || typeof value_to != 'undefined' ) {
					request[ filter_name ] = [ value_from, value_to ];
				}
			} else {
				var filter_name = filter.find('select').attr('name');
				var value = um_get_data_for_group( directory, 'filter_' + filter_name );
				if ( typeof value != 'undefined' ) {
					request[ filter_name ] = value.split( '||' );
				}
			}
		});
	}

	request = wp.hooks.applyFilters( 'um_group_invites_filter_request', request );

	wp.ajax.send( 'um_groups_get_invites', {
		data: request,
		success: function( data ) {
			if ( data.users ) {
				var template = wp.template( 'um_groups_members' );
				var template_content = template({
					users: data.users,
					group_id: hash
				});
				jQuery('.um-groups-members-list').html( template_content );

				directory.addClass( 'um-loaded' );

				if ( data.pagination ) {
					directory.attr( 'total_pages', data.pagination.total_pages );

					var template = wp.template( 'um-members-pagination' );
					var pagination_content = template({
						pagination: data.pagination
					});
					jQuery('.um-members-pagination-box').html( pagination_content );
				}
			}

			um_group_members_hide_preloader( directory );
		},
		error: function(e){
			console.log(e);
		}
	});
}


function um_ajax_get_group_blocked( directory ) {

	var hash = um_group_members_get_hash( directory );
	var page = jQuery( directory ).attr( 'data-page' );

	wp.ajax.send( 'um_groups_get_blocked', {
		data: {
			group_id: hash,
			page: page,
			nonce: um_scripts.nonce
		},
		success: function( data ) {
			if ( data.users ) {
				var template = wp.template( 'um_groups_members' );
				var template_content = template({
					users: data.users,
					group_id: hash
				});
				jQuery('.um-groups-members-list').html( template_content );

				directory.addClass( 'um-loaded' );

				if ( data.pagination ) {
					directory.attr( 'total_pages', data.pagination.total_pages );

					var template = wp.template( 'um-members-pagination' );
					var pagination_content = template({
						pagination: data.pagination
					});
					jQuery('.um-members-pagination-box').html( pagination_content );
				}
			}

			um_group_members_hide_preloader( directory );
		},
		error: function(e){
			console.log(e);
		}
	});
}
