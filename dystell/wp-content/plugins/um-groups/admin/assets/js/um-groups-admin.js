jQuery(document).ready(function () {
	var um_groups_dt = jQuery('#um_groups_manage_members').DataTable({
		"ajax": {
			"url": wp.ajax.settings.url,
			"type": "POST",
			"data": {
				action: 'um_groups_members',
				group_id: jQuery('input[name="post_ID"]').val(),
				wp_admin_referer: true,
				nonce: um_admin_scripts.nonce
			},
			dataSrc: function (json) {
				return json.members;
			}
		},
		"bFilter": true,
		"oLanguage": {
			"sEmptyTable": um_admin_scripts.groups_settings.labels.no_members_yet,
			"sInfo": um_admin_scripts.groups_settings.labels.pagination_info,
			"sInfoEmpty": um_admin_scripts.groups_settings.labels.pagination_info_empty,
			"sLengthMenu": um_admin_scripts.groups_settings.labels.display + ' <select>' +
							'<option value="10">10</option>' +
							'<option value="20">20</option>' +
							'<option value="30">30</option>' +
							'<option value="40">40</option>' +
							'<option value="50">50</option>' +
							'<option value="-1">' + um_admin_scripts.groups_settings.labels.all + '</option>' +
							'</select> ' + um_admin_scripts.groups_settings.labels.members,
			"zeroRecords": um_admin_scripts.groups_settings.labels.no_members_found,
			"search": um_admin_scripts.groups_settings.labels.search_name,
			"infoFiltered": um_admin_scripts.groups_settings.labels.pagination_info_filtered,

		},
		"aoColumns": [
			{"mData": "user", // 0
				"sDefaultContent": "",
				"mRender": function (user) {
					var user_profile = '';

					if (typeof user.avatar !== 'undefined') {
						user_profile = user.avatar;
					}

					user_profile += '<span class="name">' + user.name + '</span>';

					return user_profile;
				}
			}, {"mData": "group_status", // 1
				"mRender": function (status) {
					var res = '<select style="width:100%;text-align:center;display:none;" name="status">';

					jQuery.each(um_admin_scripts.groups_settings.statuses, function (slug, title) {
						res += '<option ' + um_selected(status.slug, slug) + ' value="' + slug + '">' + title + '</option>';
					});
					res += '</select>';

					return res + '<span class="label">' + status.title + '</span>';

				}
			}, {"mData": "group_role", // 2
				"mRender": function (role) {
					var res = '<select style="width:100%;text-align:center;display:none;" name="role">';

					jQuery.each(um_admin_scripts.groups_settings.roles, function (slug, title) {
						res += '<option ' + um_selected(role.slug, slug) + ' value="' + slug + '">' + title + '</option>';
					});
					res += '</select>';

					return res + '<span class="label">' + role.title + '</span>';
				},

			}, {"mData": "actions", // 3
				"mRender": function (actions) {
					var res = '' +
									'<a href="javascript:void(0);" data-user_id="' + actions.user_id + '" class="button um-groups-send-invite"><span class="um-faicon-paper-plane-o"></span> ' + um_admin_scripts.groups_settings.labels.invite + '</a>' +
									'&nbsp;' +
									'<a href="javascript:void(0);" data-user_id="' + actions.user_id + '" class="button um-groups-expel"><span class="um-faicon-trash-o"></span> ' + um_admin_scripts.groups_settings.labels.expel + '</a>';
					return res;
				},
				"bSortable": false
			}, {"mData": "group_status", // 4
				"mRender": function (status) {
					return status.slug;
				},
				"visible": false,
			}, {"mData": "group_role", // 5
				"mRender": function (role) {
					return role.slug;
				},
				"visible": false,
			}, {"mData": "user_login", // 6
				"mRender": function (user_login) {
					return user_login;
				},
				"visible": false,
			}, {"mData": "user_email", // 7
				"mRender": function (user_email) {
					return user_email;
				},
				"visible": false,
			}, {"mData": "timestamp", // 8
				"mRender": function (timestamp) {
					return timestamp;
				},
				"visible": false,
			}
		],
		"aaSorting": [[8, "asc"]],
		createdRow: function (row, data, index) {
			jQuery(row).attr('data-user_id', data.actions.user_id);
		}
	});

	// Filter role
	jQuery('#um_groups_filter_role').on('change', function () {

		um_groups_dt.columns(5).search(this.value).draw();

	});

	// Filter status
	jQuery('#um_groups_filter_status').on('change', function () {

		um_groups_dt.columns(4).search(this.value).draw();
	});

	// Sort member by date joined
	jQuery('#um_groups_sort_member_list').on('change', function () {
		var sort = jQuery(this).val();
		um_groups_dt.order([[8, sort]]).draw();
	});

	// Search member suggestion
	var group_id = jQuery('input[name="post_ID"]').val();
	var suggest_url = wp.ajax.settings.url + '?action=um_groups_search_member_suggest&group_id=' + group_id + '&nonce=' + um_admin_scripts.nonce;
	jQuery('input[name="um_groups_add_new_members"]').suggest( suggest_url, {
		minLength: 3,
		onSelect: function () {
			var suggested = this.value;
			var user_login = suggested.split(" - ", 1);
			jQuery('input[name="um_groups_add_new_members"]').val(user_login[0]);
			um_groups_search_members(false);
		}
	});

	/**
	 * Search members by user_login or user_email
	 * @param  bool force 
	 */
	function um_groups_search_members(force) {
		var me = jQuery('input[name="um_groups_add_new_members"]');
		var keyword = me.val();
		if (!force && keyword.length < 3) {
			return;
		}

		var group_id = jQuery('input[name="post_ID"]').val();

		jQuery.ajax({
			method: 'POST',
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action: 'um_groups_search_member', 
				search: keyword, 
				group_id: group_id, 
				nonce: um_admin_scripts.nonce
			}
		}).done(function (data) {

			if (data.found) {
				var wrap = jQuery('.um-groups-found-user');
				wrap.fadeIn();
				jQuery('.um-groups-search-member input[name="um_groups_add_new_members"][type="text"]').hide();
				jQuery('.um-groups-found-user .user-info .display-name').text(data.user.name);
				jQuery('.um-groups-found-user .image-wrapper').html(data.user.avatar);
				if (data.user.has_joined) {
					jQuery('.um-groups-found-user .has-joined-current').show();
					jQuery('.um-groups-found-user .actions input[name=add-member]').hide();
					jQuery('.um-groups-found-user .role').text(data.user.role);
					var addedbyWrap = jQuery('.um-groups-found-user .has-joined-current .added-by');
					addedbyWrap.text("( " + addedbyWrap.data('text') + " " + data.user.added_by + " )");
				} else {
					jQuery('.um-groups-found-user .has-joined-current').hide();
					jQuery('.um-groups-found-user .actions input[name=add-member]').show();
				}

				jQuery('.um-groups-found-user .actions input[name="add-member"]').attr('data-user_id', data.user.ID);
				jQuery('.um-groups-found-user a.new-search:visible').focus();
				jQuery('.um-groups-found-user .actions input[name="add-member"]:visible').focus();

			}
		}).error(function (error) {
			console.log('search members error', error);
		});
	}

	// New search
	jQuery('.um-groups-found-user a.new-search').click(function (e) {
		jQuery('.um-groups-found-user').hide();
		jQuery('.um-groups-search-member input[name="um_groups_add_new_members"][type="text"]').show().val('').focus();
	});

	// Add Member
	jQuery(document).on('click', '.um-groups-found-user .actions input[name="add-member"]', function () {

		var user_id = jQuery(this).attr('data-user_id');
		var group_id = jQuery('input[name="post_ID"]').val();

		jQuery.ajax({
			method: 'POST',
			url: wp.ajax.settings.url,
			type: 'post',
			data: {action: 'um_groups_add_member', group_id: group_id, user_id: user_id, nonce: um_admin_scripts.nonce},
		}).done(function (data) {
			if (data.found) {
				um_groups_dt.clear();
				um_groups_dt.ajax.reload();
				jQuery('.um-groups-found-user a.new-search').trigger('click');
			}
		}).error(function (error) {
			console.log('add member error', error);
		});
	});

	// Expel/Remove Member from a Group
	jQuery(document).on('click', '.um-groups-expel', function () {

		var result = confirm(um_admin_scripts.groups_settings.labels.confirm_expel);
		var user_id = jQuery(this).data('user_id');
		var group_id = jQuery('input[name="post_ID"]').val();
		var me = jQuery(this);

		if (result) {

			jQuery.ajax({
				method: 'POST',
				url: wp.ajax.settings.url,
				type: 'post',
				data: {action: 'um_groups_delete_member', group_id: group_id, user_id: user_id, nonce: um_admin_scripts.nonce, admin: true}
			}).done(function (data) {
				if (data.found) {
					um_groups_dt.row(me.parents('td').parents('tr')).remove().draw();
				}
			}).error(function (error) {
				console.log('delete member error', error);
			});

		}

	});

	// Invite Member / Send invitation link
	jQuery(document).on('click', '.um-groups-send-invite', function () {
		var me = jQuery(this);
		var user_id = me.attr('data-user_id');
		var group_id = jQuery('input[name="post_ID"]').val();

		jQuery.ajax({
			method: 'POST',
			url: wp.ajax.settings.url,
			type: 'post',
			data: {action: 'um_groups_send_invitation_mail', group_id: group_id, user_id: user_id, nonce: um_admin_scripts.nonce, admin: true},
		}).done(function (data) {
			if (data.found) {
				me.addClass('disabled');
				me.addClass('um-groups-has-invited');
				me.html('<span class="um-faicon-check"></span> ' + um_admin_scripts.groups_settings.labels.invited);
			}

		}).error(function (error) {
			console.log('send invite error', error);
		});
	});

	// Swap invite button label and icon
	jQuery(document).on({
		mouseenter: function () {
			var me = jQuery(this);
			me.removeClass('disabled');
			me.html('<span class="um-faicon-paper-plane-o"></span> ' + um_admin_scripts.groups_settings.labels.resend);

		},
		mouseleave: function () {
			var me = jQuery(this);
			me.addClass('disabled');
			me.html('<span class="um-faicon-check"></span> ' + um_admin_scripts.groups_settings.labels.invited);

		}
	}, '.um-groups-send-invite.um-groups-has-invited');


	// Show/Hide role and status options
	jQuery(document).on({
		mouseenter: function () {
			var tr = jQuery(this);
			tr.find('td').find('select').show();
			tr.find('td').find('span.label').hide();
		},
		mouseleave: function (e) {

			/* Solution */
			if ( e.relatedTarget == null ) return;

			var tr = jQuery(this);
			tr.find('td').find('select').hide();
			tr.find('td').find('span.label').show();
		}
	}, 'table#um_groups_manage_members tbody tr');


	// Change member status
	jQuery(document.body).on('change', 'select[name="status"]', function () {
		var me = jQuery(this);
		var status = me.val();
		var user_id = me.parents('tr').data('user_id');
		var group_id = jQuery('#post_ID').val();

		jQuery.ajax({
			method: 'POST',
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action: 'um_groups_change_member_status',
				group_id: group_id,
				user_id: user_id,
				status: status,
				nonce: um_admin_scripts.nonce
			}
		}).done(function (data) {
			me.parent('tr').trigger('mouseleave');
			me.hide();
			me.parent('td').find('span[class="label"]').fadeOut();

			var d = um_groups_dt.row(me.parents('tr')).data();
			d.group_status.slug = data.status_slug;
			d.group_status.title = data.status;

			um_groups_dt.row(me.parents('tr')).data(d).draw();
			me.parent('td').find('span[class="label"]').fadeIn('slow');

		}).error(function (error) {
			console.log('delete member error', error);
		});
	});

	// Change member role
	jQuery(document).on('change', 'select[name=role]', function () {
		var me = jQuery(this);
		var role = me.val();
		var user_id = me.parents('tr').attr('data-user_id');
		var group_id = jQuery('input[name="post_ID"]').val();
		jQuery.ajax({
			method: 'POST',
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action: 'um_groups_change_member_role',
				group_id: group_id,
				user_id: user_id,
				role: role,
				nonce: um_admin_scripts.nonce,
				admin: true
			}
		}).done(function (data) {
			me.parent('tr').trigger('mouseleave');
			me.hide();
			me.parent('td').find('span[class="label"]').fadeOut();

			var d = um_groups_dt.row(me.parents('tr')).data();
			d.group_role.slug = data.role_slug;
			d.group_role.title = data.role;

			um_groups_dt.row(me.parents('tr')).data(d).draw();
			me.parent('td').find('span[class="label"]').fadeIn('slow');
		}).error(function (error) {
			console.log('delete member error', error);
		});

	});

}); // jQuery documents