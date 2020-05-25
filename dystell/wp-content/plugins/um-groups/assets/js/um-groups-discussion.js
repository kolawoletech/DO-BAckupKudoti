Loadwall_ajax = false;

/* Load of posts */
jQuery(window).scroll(function () {

	if (jQuery('.um-groups-wall').length > 0
		&& jQuery(window).scrollTop() + jQuery(window).height() >= jQuery('.um-groups-wall').offset().top + jQuery('.um-groups-wall').height()
		// && jQuery('.um-groups-widget:not(.um-groups-new-post):visible').length >= jQuery('.um-groups-wall').attr('data-per_page')
		&& Loadwall_ajax == false
		&& jQuery('.um-groups-wall').attr('data-single_post') == false) {

		Loadwall_ajax = true;
		jQuery('.um-groups-load:last').show();

		group_id = jQuery('.um-groups-wall').attr('data-group_id') || jQuery('input[name="_group_id"]').val();
		core_page = jQuery('.um-groups-wall').attr('data-core_page');
		hashtag = jQuery('.um-groups-wall').attr('data-hashtag');
		user_id = jQuery('.um-groups-wall').attr('data-user_id');
		user_wall = jQuery('.um-groups-wall').attr('data-user_wall');
		show_pending = jQuery('.um-groups-wall').attr('data-show-pending');

		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action: 'um_groups_load_wall',
				offset: jQuery('.um-groups-widget:not(.um-groups-new-post):visible').length,
				user_id: user_id,
				user_wall: user_wall,
				hashtag: hashtag,
				core_page: core_page,
				group_id: group_id,
				show_pending: show_pending,
				nonce: um_scripts.nonce
			},
			success: function (data) {
				jQuery('.um-groups-load').hide();

				if (data == '') {
					Loadwall_ajax = true;
				} else {
					jQuery('.um-groups-wall').append(data);
					Loadwall_ajax = false;
				}

				if( typeof data.restricted !== 'undefined' ){
					alert( data.restricted );
					window.location.reload();
				}

				if( typeof data.debug !== 'undefined' ){
					console.log( data.debug );
				}

			},
			error: function (e) {
				console.log('UM Groups Discussion Error', e);
			}
		});
	}

});

/* Setup image upload */
function UM_wall_img_upload() {
	jQuery('.ajax-upload-dragdrop').remove();
	jQuery('.um-groups-insert-photo').each(function () {

		apu = jQuery(this);
		var formData = {
			key: 'wall_img_upload',
			action: 'um_imageupload',
			set_id: 0,
			set_mode: 'wall',
			timestamp: apu.data('timestamp'),
			_wpnonce: apu.data('nonce'),
			group_id: jQuery('input[name="_group_id"]').val()
		};

		apu.uploadFile({
			url: wp.ajax.settings.url,
			method: "POST",
			multiple: false,
			formData: formData,
			fileName: 'wall_img_upload',
			allowedTypes: apu.attr('data-allowed'),
			maxFileSize: 9999999,
			dragDropStr: '',
			sizeErrorStr: apu.attr('data-size-err'),
			extErrorStr: apu.attr('data-ext-err'),
			maxFileCountErrorStr: '',
			maxFileCount: 1,
			showDelete: false,
			showAbort: false,
			showDone: false,
			showFileCounter: false,
			showStatusAfterSuccess: true,
			returnType: 'json',
			onSubmit: function (files) {

				apu.parents('.um-groups-widget').find('.um-error-block').remove();
				apu.parents('.um-groups-widget').find('.um-groups-post').addClass('um-disabled');
				apu.parents('.um-groups-widget').find('.um-groups-preview').hide();
				apu.parents('.um-groups-widget').find('.um-groups-preview img').attr('src', '');
				apu.parents('.um-groups-widget').find('.um-groups-preview input[type=hidden]').val('');

			},

			onSuccess: function (files, response, xhr) {

				apu.selectedFiles = 0;

				if ( response.status && response.status == false ) {

					apu.parents('.um-groups-widget').find('.um-groups-post').addClass('um-disabled');

					apu.parents('.um-groups-widget').find('.um-groups-textarea-elem').attr('placeholder', jQuery('.um-groups-textarea-elem').attr('data-ph'));

					apu.parents('.um-groups-widget').find('.upload-statusbar').prev('div').append('<div class="um-error-block">' + response.error + '</div>');

					apu.parents('.um-groups-widget').find('.upload-statusbar').remove();

				} else {

					apu.parents('.um-groups-widget').find('.um-groups-post').removeClass('um-disabled');

					apu.parents('.um-groups-widget').find('.um-groups-textarea-elem').attr('placeholder', jQuery('.um-groups-textarea-elem').attr('data-photoph'));

					apu.parents('.um-groups-widget').find('.upload-statusbar').remove();

					jQuery.each( response.data, function ( key, data ) {

						apu.parents('.um-groups-widget').find('.um-groups-preview').show();
						apu.parents('.um-groups-widget').find('.um-groups-preview img').attr('src', data.url );
						apu.parents('.um-groups-widget').find('.um-groups-preview input[type=hidden][name="_post_img"]').val( data.file );
						apu.parents('.um-groups-widget').find('.um-groups-preview input[type=hidden][name="_post_img_url"]').val( data.url );

					});

				}

			},
			onError: function( e ){
				console.log( e );
			}
		});

	});
}

/* Show confirm box */
function UM_wall_confirmbox_show(post_id, msg, custclass) {
	var modal = jQuery('.um-groups-confirm');
	if (modal.is(':visible')) {

	} else {
		jQuery('.um-groups-confirm-m').html(msg);
		jQuery('.um-groups-confirm-o,.um-groups-confirm').show();
		jQuery('.um-groups-confirm').find('.um-groups-confirm-btn').addClass(custclass).attr('data-post_id', post_id);
	}
}

/* Hides confirm box */
function UM_wall_confirmbox_hide() {

	jQuery('.um-groups-confirm-o,.um-groups-confirm').hide();
}

/* Responsive confirm box */
function UM_wall_confirmbox_mobile() {
	var width = jQuery(window).width();
	if (width <= 500) {
		max_width = width;
		margin_left = 0;
		left = 0;
	} else {
		max_width = '400px';
		margin_left = '-200px';
		left = '50%';
	}
	jQuery('.um-groups-confirm').css({
		'top': (jQuery(window).height() - jQuery('.um-groups-confirm').height() ) / 2 + 'px',
		'width': max_width,
		'margin-left': margin_left,
		'left': left
	});
}

var getUrlParameter = function getUrlParameter(sParam) {
		var sPageURL = decodeURIComponent(window.location.search.substring(1)),
			sURLVariables = sPageURL.split('&'),
			sParameterName,
			i;

		for (i = 0; i < sURLVariables.length; i++) {
			sParameterName = sURLVariables[i].split('=');

			if (sParameterName[0] === sParam) {
				return sParameterName[1] === undefined ? true : sParameterName[1];
			}
		}
};

function split(val) {

	return val.split(" ");
}

function extractLast(term) {
   
	return split(term).pop();

}

function UM_wall_autocomplete_start() {
	jQuery('textarea.um-groups-textarea-elem,textarea.um-groups-comment-textarea').each(function () {
		el = jQuery(this);

		if (typeof jQuery.ui === 'undefined') {
			return false;
		}

		var el_autocomplete = el.autocomplete({
			minLength: 1,
			source: function (request, response) {

				if (extractLast(request.term).charAt(0) == '@') {
					jQuery.getJSON( wp.ajax.settings.url + '?action=um_groups_get_user_suggestions&term=' + extractLast(request.term) + '&nonce=' + um_scripts.nonce, function (data) {
						response(data);
					});
				}
			},
			select: function (event, ui) {

				ui.item.name = ui.item.name.replace('<strong>', '');
				ui.item.name = ui.item.name.replace('</strong>', '');

				var terms = split(this.value);
				terms.pop();
				terms.push('@' + ui.item.name);
				terms.push("");
				this.value = jQuery.trim(terms.join(" "));
				return false;

			}
		}).data("ui-autocomplete")._renderItem = function (ul, item) {
			return jQuery("<li />").data("item.autocomplete", item).append(item.photo + item.name + '<span>@' + item.username + '</span>').appendTo(ul);
		};


	});
}

/* Resize function */
jQuery(window).resize(function () {

	UM_wall_confirmbox_mobile();
});

jQuery(document).ready(function () {

	if (jQuery('textarea.um-groups-textarea-elem').length) {

		UM_wall_autocomplete_start();

	}

	/* Scroll to wall post */
	var wall_post = getUrlParameter('group_post');
	var wall_comment = getUrlParameter('wall_comment_id');

	if (wall_post > 0 && !wall_comment) {
		jQuery('body').scrollTo('#postid-' + parseInt(wall_post), 500, {
			offset: 0,
			onAfter: function () {
				jQuery('#postid-' + parseInt(wall_post)).addClass('highlighted');
			}
		});
	}

	if (wall_post > 0 && wall_comment > 0) {
		jQuery('body').scrollTo('#commentid-' + parseInt(wall_comment), 500, {
			offset: -10,
			onAfter: function () {
				jQuery('#commentid-' + parseInt(wall_comment)).addClass('highlighted');
			}
		});
	}

	/* Scroll to comments area */
	jQuery(document).on('click', '.um-groups-disp-comments', function (e) {
		e.preventDefault();
		var post_id = jQuery(this).parents('.um-groups-widget').attr('id').replace('postid-', '');
		jQuery('body').scrollTo('#wallcomments-' + parseInt(post_id), {duration: 200});
		return false;
	});

	/* Removes a post */
	jQuery(document).on('click', '.um-groups-confirm-removepost', function (e) {
		e.preventDefault();
		var el = jQuery(this);
		var post_id = el.attr('data-post_id');

		jQuery('.um-groups-widget#postid-' + post_id).hide();
		UM_wall_confirmbox_hide();

		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action: 'um_groups_remove_post',
				post_id: post_id,
				group_id: jQuery('input[name="_group_id"]').val(),
				nonce: um_scripts.nonce
			},
			success: function (data) {

				if( typeof data.restricted !== 'undefined' ){
					alert( data.restricted );
					window.location.reload();
				}

				if( typeof data.debug !== 'undefined' ){
					console.log( data.debug );
				}

			}
		});

		return false;
	});

	/* Removes a comment */
	jQuery(document).on('click', '.um-groups-confirm-removecomment', function (e) {
		e.preventDefault();
		var el = jQuery(this);
		var comment_id = el.attr('data-post_id');

		jQuery('.um-groups-commentl#commentid-' + comment_id).hide();

		UM_wall_confirmbox_hide();

		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action: 'um_groups_remove_comment',
				comment_id: comment_id,
				group_id: jQuery('input[name="_group_id"]').val(),
				nonce: um_scripts.nonce
			},
			success: function (data) {
				if( typeof data.restricted !== 'undefined' ){
					alert( data.restricted );
					window.location.reload();
				}

				if( typeof data.debug !== 'undefined' ){
					console.log( data.debug );
				}
			}
		});

		return false;
	});

	/* Trash post popup */
	jQuery(document).on('click', '.um-groups-trash', function (e) {
		e.preventDefault();
		var el = jQuery(this);
		var post_id = el.parents('.um-groups-widget').attr('id').replace('postid-', '');
		var msg = el.attr('data-msg');

		el.parents('.um-groups-dialog').hide();

		UM_wall_confirmbox_show(post_id, msg, 'um-groups-confirm-removepost');
		UM_wall_confirmbox_mobile();

		return false;
	});

	/* Trash comment popup */
	jQuery(document).on('click', '.um-groups-editc a.delete', function (e) {
		e.preventDefault();
		var el = jQuery(this);
		var post_id = el.parent().parent().parent().parent().parent().attr('id').replace('commentid-', '');
		var msg = el.attr('data-msg');
		el.parents('.um-groups-dialog').hide();

		UM_wall_confirmbox_show(post_id, msg, 'um-groups-confirm-removecomment');
		UM_wall_confirmbox_mobile();

		return false;
	});

	/* Reply to comment */
	jQuery(document).on('click', '.um-groups-editc a.edit', function (e) {
		e.preventDefault();
		if (!jQuery(this).parents('.um-groups-commentl').hasClass('unready')) {

			if (jQuery(this).parents('.um-groups-comment-info').find('.um-groups-comment-area').length > 0) {
				jQuery(this).parents('.um-groups-comment-info').find('.um-groups-comment-area').remove();
			}

			var cbox = jQuery(this).parents('.um-groups-comments').find('.um-groups-comment-area:first');
			var comment_id = jQuery(this).data('commentid');
			var cloned = cbox.clone();

			jQuery(this).parents('.um-groups-comment-info').find('.um-groups-editc-d').hide();
			jQuery(this).parents('.um-groups-comment-info').find('div').hide();
			jQuery('#commentid-' + comment_id).addClass('editing');

			cloned.css({'paddingTop': 0, 'paddingLeft': 0});
			cloned.find('.um-groups-comment-avatar').hide();
			cloned.appendTo(jQuery(this).parents('.um-groups-comment-info'));
			cloned.find('textarea').attr('data-commentid', comment_id)
				.attr('placeholder', cloned.find('textarea').attr('data-replytext'))
				.val(jQuery('#um-groups-reply-' + comment_id).val()).focus();
			UM_wall_autocomplete_start();

		}
		return false;
	});

	/* Hides confirm box */
	jQuery(document).on('click', '.um-groups-confirm-close,.um-groups-confirm-o', function (e) {
		e.preventDefault();
		UM_wall_confirmbox_hide();
		return false;
	});

	/* Hide modal view */
	jQuery(document).on('click', '.um-groups-modal-hide', function (e) {
		e.preventDefault();
		remove_Modal();
		return false;
	});

	/* Remove image upload */
	jQuery(document).on('click', '.um-groups-img-remove', function (e) {
		el = jQuery(this).parents('form');
		el.find('#_post_img').val('');
		el.find('.um-groups-preview img').attr('src', '');
		el.find('.um-groups-preview').hide();
		if (el.find('textarea:visible').val().trim().length == 0) {
			el.find('.um-groups-post').addClass('um-disabled');
		}
	});

	/* Image upload */
	UM_wall_img_upload();

	/* Hide a comment */
	jQuery(document).on('click', '.um-groups-comment-hide', function (e) {
		e.preventDefault();
		el = jQuery(this);
		div = el.parent();

		if (div.hasClass('editing')) {
			div.find('.um-groups-comment-area').remove();
			div.find('.um-groups-comment-info > div').show();
			div.find('.um-groups-editc-d').hide();
			div.removeClass('editing');
		} else {
			var comment_id = div.attr('id').replace('commentid-', '');

			div.find('.um-groups-comment-info').hide();
			div.find('.um-groups-comment-hidden').show();
			div.find('.um-groups-comment-avatar').addClass('hidden-1');
			el.remove();

			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'post',
				data: {
					action: 'um_groups_hide_comment',
					comment_id: comment_id,
					group_id: jQuery('input[name="_group_id"]').val(),
					nonce: um_scripts.nonce
				},
				success: function (data) {
					if( typeof data.restricted !== 'undefined' ){
						alert( data.restricted );
						window.location.reload();
					}

					if( typeof data.debug !== 'undefined' ){
						console.log( data.debug );
					}
				}
			});
		}

		return false;
	});

	/* Unhide a comment */
	jQuery(document).on('click', '.um-groups-comment-hidden a', function (e) {
		e.preventDefault();
		el = jQuery(this);
		var comment_id = el.parent().parent().attr('id').replace('commentid-', '');

		el.parent().parent().find('.um-groups-comment-info').show();
		el.parent().parent().find('.um-groups-comment-hidden').hide();
		el.parent().parent().find('.um-groups-comment-hide').show();
		el.parent().parent().find('.um-groups-comment-avatar').removeClass('hidden-1');

		el.parent().parent().prepend('<a href="#" class="um-groups-comment-hide um-tip-s"><i class="um-icon-close-round"></i></a>');

		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action: 'um_groups_unhide_comment',
				comment_id: comment_id,
				group_id: jQuery('input[name="_group_id"]').val(),
				nonce: um_scripts.nonce
			},
			success: function (data) {
				if( typeof data.restricted !== 'undefined' ){
					alert( data.restricted );
					window.location.reload();
				}

				if( typeof data.debug !== 'undefined' ){
					console.log( data.debug );
				}
			}
		});

		return false;
	});

	/* Show post likes in modal */
	jQuery(document).on('click', '.um-groups-show-likes', function (e) {
		e.preventDefault();

		el = jQuery(this);
		var post_id = el.attr('data-post_id');

		if (parseInt(el.find('.um-groups-post-likes').html()) <= 0) {
			return false;
		}

		prepare_Modal();

		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action:'um_groups_get_post_likes',
				post_id: post_id,
				group_id: jQuery('input[name="_group_id"]').val(),
				nonce: um_scripts.nonce
			},
			success: function (data) {
				if (data) {
					show_Modal(data);
					responsive_Modal();
				} else {
					remove_Modal();
				}

				if( typeof data.restricted !== 'undefined' ){
					alert( data.restricted );
					window.location.reload();
				}

				if( typeof data.debug !== 'undefined' ){
					console.log( data.debug );
				}

			}
		});

		return false;
	});

	/* Show comment likes in modal */
	jQuery(document).on('click', '.um-groups-comment-likes', function (e) {
		e.preventDefault();

		el = jQuery(this);
		var comment_id = el.parent().parent().parent().attr('id').replace('commentid-', '');

		prepare_Modal();

		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action: 'um_groups_get_comment_likes',
				comment_id: comment_id,
				group_id: jQuery('input[name="_group_id"]').val(),
				nonce: um_scripts.nonce
			},
			success: function (data) {
				if (data) {
					show_Modal(data);
					responsive_Modal();
				} else {
					remove_Modal();
				}

				if( typeof data.restricted !== 'undefined' ){
					alert( data.restricted );
					window.location.reload();
				}

				if( typeof data.debug !== 'undefined' ){
					console.log( data.debug );
				}
			}
		});

		return false;
	});

	/* Toggle comment hiding icon */
	jQuery(document).on('mouseover', '.um-groups-commentl', function (e) {
		jQuery(this).find('.um-groups-comment-hide').show();
	});

	jQuery(document).on('mouseout', '.um-groups-commentl', function (e) {
		jQuery(this).find('.um-groups-comment-hide').hide();
	});

	/* Report post */
	jQuery(document).on('click', '.um-groups-report:not(.flagged)', function (e) {
		var el = jQuery(this);
		var post_id = el.parents('.um-groups-widget').attr('id').replace('postid-', '');
		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action: 'um_groups_report_post',
				post_id: post_id,
				group_id: jQuery('input[name="_group_id"]').val(),
				nonce: um_scripts.nonce
			},
			success: function (data) {
				el.addClass('flagged').html(el.attr('data-cancel_report'));

				if( typeof data.restricted !== 'undefined' ){
					alert( data.restricted );
					window.location.reload();
				}

				if( typeof data.debug !== 'undefined' ){
					console.log( data.debug );
				}
			}
		});
	});

	/* Cancel report post */
	jQuery(document).on('click', '.um-groups-report.flagged', function (e) {
		var el = jQuery(this);
		var post_id = el.parents('.um-groups-widget').attr('id').replace('postid-', '');
		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action: 'um_groups_unreport_post',
				post_id: post_id,
				group_id: jQuery('input[name="_group_id"]').val(),
				nonce: um_scripts.nonce
			},
			success: function (data) {
				el.removeClass('flagged').html(el.attr('data-report'));

				if( typeof data.restricted !== 'undefined' ){
					alert( data.restricted );
					window.location.reload();
				}

				if( typeof data.debug !== 'undefined' ){
					console.log( data.debug );
				}
			}
		});
	});

	/* load more comments */
	jQuery(document).on('click', '.um-groups-commentload', function (e) {
		e.preventDefault();
		var el = jQuery(this);

		el.hide();
		el.parent().find('.um-groups-commentload-spin').show();

		var offset = el.attr('data-loaded');
		var post_id = el.parents('.um-groups-widget').attr('id').replace('postid-', '');

		el.parents('.um-groups-comments').find('.um-groups-commentload-end').remove();
		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action: 'um_groups_load_more_comments',
				post_id: post_id,
				offset: offset,
				group_id: jQuery('input[name="_group_id"]').val(),
				nonce: um_scripts.nonce
			},
			success: function (data) {
				el.before(data);
				el.attr('data-loaded', el.parents('.um-groups-comments').find('.um-groups-commentl:not(.is-child):not(.um-groups-comment-area):visible').length);
				el.parent().find('.um-groups-commentload-spin').hide();
				if (el.parents('.um-groups-comments').find('.um-groups-commentload-end').length) {
					el.show().find('span').html(el.attr('data-load_comments'));
				}

				if( typeof data.restricted !== 'undefined' ){
					alert( data.restricted );
					window.location.reload();
				}

				if( typeof data.debug !== 'undefined' ){
					console.log( data.debug );
				}

			}
		});

		return false;
	});

	/* load more replies */
	jQuery(document).on('click', '.um-groups-ccommentload', function (e) {
		e.preventDefault();
		var el = jQuery(this);

		el.hide();
		el.parent().find('.um-groups-ccommentload-spin').show();

		var offset = el.attr('data-loaded');
		var post_id = el.parents('.um-groups-widget').attr('id').replace('postid-', '');
		var comment_id = el.parents('.um-groups-commentwrap').attr('data-comment_id');

		el.parents('.um-groups-comments').find('.um-groups-ccommentload-end').remove();
		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action: 'um_groups_load_more_replies',
				post_id: post_id,
				comment_id: comment_id,
				offset: offset,
				group_id: jQuery('input[name="_group_id"]').val(),
				nonce: um_scripts.nonce
			},
			success: function (data) {
				el.before(data);
				el.attr('data-loaded', el.parents('.um-groups-commentwrap').find('.um-groups-commentl.is-child:not(.um-groups-comment-area):visible').length);
				el.parent().find('.um-groups-ccommentload-spin').hide();
				if (el.parents('.um-groups-commentwrap').find('.um-groups-ccommentload-end').length) {
					el.show().find('span').html(el.attr('data-load_replies'));
				}

				if( typeof data.restricted !== 'undefined' ){
					alert( data.restricted );
					window.location.reload();
				}

				if( typeof data.debug !== 'undefined' ){
					console.log( data.debug );
				}
			}
		});

		return false;
	});

	/* Post a status */
	jQuery(document).on('click', '.um-groups-post', function (e) {
		e.preventDefault();
		if ( jQuery(this).hasClass('um-disabled') )
			return false;

		jQuery(this).parents('.um-groups-widget').find('.um-groups-publish').submit();
		return false;
	});


	/* Detect change in textarea content */
	jQuery(document).on('input properychange', '.um-groups-textarea-elem', function () {
		if (jQuery(this).val().trim().length > 0) {
			jQuery(this).parents('.um-groups-widget').find('.um-groups-post').removeClass('um-disabled');
		} else {
			jQuery(this).parents('.um-groups-widget').find('.um-groups-post').addClass('um-disabled');
		}
	});

	/* Reply to comment */
	jQuery(document).on('click', '.um-groups-comment-reply', function (e) {
		e.preventDefault();
		if (!jQuery(this).parents('.um-groups-commentl').hasClass('unready')) {

			if (jQuery(this).parents('.um-groups-comment-info').find('.um-groups-comment-area').length == 0) {
				var cbox = jQuery(this).parents('.um-groups-comments').find('.um-groups-comment-area:first');
				var cloned = cbox.clone();
				cloned.appendTo(jQuery(this).parents('.um-groups-comment-info'));
				cloned.find('textarea').attr('data-reply_to', jQuery(this).attr('data-commentid')).attr('placeholder', cloned.find('textarea').attr('data-replytext')).focus();
				UM_wall_autocomplete_start();
			} else {
				jQuery(this).parents('.um-groups-comment-info').find('.um-groups-comment-area').remove();
			}

		}
		return false;
	});

	/* posting a comment */
	jQuery(document).on('keypress', '.um-groups-comment-textarea', function (e) {
		if (( e.keyCode == 10 || e.keyCode == 13 ) && !e.shiftKey && jQuery(this).val().trim().length > 0) {
			e.preventDefault();
			var textarea = jQuery(this);
			var comment_id = textarea.data('commentid');
			var comment = textarea.val();
			var postid = textarea.parents('.um-groups-widget').attr('id').replace('postid-', '');
			var parent_div = jQuery('#commentid-' + comment_id);

			// if we are editing a reply
			if (comment_id && parent_div.length && parent_div.hasClass('editing')) {
				jQuery.ajax({
					url: wp.ajax.settings.url,
					type: 'post',
					dataType: 'json',
					data: {
						action: 'um_groups_wall_comment',
						postid: postid,
						commentid: comment_id,
						comment: comment,
						group_id: jQuery('input[name="_group_id"]').val(),
						nonce: um_scripts.nonce
					},
					success: function (data) {
						parent_div.find('#um-groups-reply-' + comment_id).val(comment);
						parent_div.find('.um-groups-comment-text').html(data.comment_content);
						parent_div.find('.um-groups-editc-d').hide();
						parent_div.find('.um-groups-comment-area').remove();
						parent_div.find('.um-groups-comment-info > div').show();
						parent_div.removeClass('editing');
						parent_div.find('.um-groups-commentl.um-groups-comment-area').show();

						if( typeof data.restricted !== 'undefined' ){
							alert( data.restricted );
							window.location.reload();
						}

						if( typeof data.debug !== 'undefined' ){
							console.log( data.debug );
						}
					}
				});
			}

			// if we are writing a new reply
			else {
				var commentbox = textarea.parents('.um-groups-comments');
				var reply_to = textarea.attr('data-reply_to');
				textarea.val('');

				var count = textarea.parents('.um-groups-widget').find('.um-groups-post-comments');
				count.html(parseInt(count.html()) + 1);
				var loader_content = commentbox.find('.um-groups-commentload');
				commentbox.find('.um-groups-commentload').remove();
				var comment_loader = commentbox.find('.um-groups-commentload-spin');
				commentbox.find('.um-groups-commentload-spin').remove();
				var comment_wrap = commentbox.find('.um-groups-commentwrap-clone');
				var cchild = textarea.parents('.um-groups-commentwrap').find('.um-groups-comment-child');
				cchild_clone = cchild.clone();

				if (reply_to > 0) { // Writing a reply to a comment
					var clone = commentbox.find('.um-groups-commentlre-clone:first');
					var clonel = clone.clone();

					if (textarea.parents('.um-groups-commentwrap').find('.um-groups-comment-child').length) {
						if (cchild.find('.um-groups-ccommentload').length > 0) {
							clonel.addClass('unready');
							clonel.insertBefore(cchild.find('.um-groups-ccommentload'));
							clonel.find('.um-groups-comment-text').text(comment);
						} else {
							clonel.addClass('unready').appendTo(cchild).fadeIn().find('.um-groups-comment-text').text(comment);

						}
					}

				} else {
					var clone = commentbox.find('.um-groups-commentl-clone:first');
					var clonel = clone.clone();
					clonel.addClass('unready').appendTo(commentbox.find('.um-groups-comments-loop')).fadeIn().find('.um-groups-comment-text').text(comment);
				}

				loader_content.appendTo(commentbox.find('.um-groups-comments-loop'));
				comment_loader.appendTo(commentbox.find('.um-groups-comments-loop'));

				jQuery.ajax({
					url: wp.ajax.settings.url,
					type: 'post',
					dataType: 'json',
					data: {
						action: 'um_groups_wall_comment',
						postid: postid,
						reply_to: reply_to,
						comment: comment,
						group_id: jQuery('input[name="_group_id"]').val(),
						nonce: um_scripts.nonce
					},
					success: function (data) {
						clonel.attr('id', 'commentid-' + data.commentid).removeClass('um-groups-commentl-clone');
						clonel.find('.original-content').attr('id', 'um-groups-reply-' + data.commentid).val(comment);
						clonel.find('.um-groups-editc-d .edit').data('commentid', data.commentid);


						if (clonel.find('.um-groups-comment-reply').length) {
							clonel.find('.um-groups-comment-reply').attr('data-commentid', data.commentid);
							jQuery('#commentid-' + data.commentid + ' .um-groups-comment-text').html(data.comment_content);

							var comment_content = jQuery('#commentid-' + data.commentid + '').clone();
							jQuery('#commentid-' + data.commentid + '').remove();

							var new_comment_wrap = comment_wrap.clone();
							new_comment_wrap.removeClass('um-groups-commentwrap-clone');
							new_comment_wrap.addClass('um-groups-commentwrap');
							new_comment_wrap.attr('data-comment_id', data.commentid);
							comment_content.removeClass('unready');

							if (reply_to <= 0) {
								comment_content.appendTo(new_comment_wrap);
								new_comment_wrap.appendTo(commentbox.find('.um-groups-comments-loop'));
							}

						}
						clonel.removeClass('unready');

						if (reply_to > 0) {

							var cchild = textarea.parents('.um-groups-commentwrap').find('.um-groups-comment-child');
							var new_comment_wrap = textarea.parents('.um-groups-commentwrap');

							textarea.parents('.um-groups-commentwrap').find('.um-groups-comment-child').remove();

							cchild_clone = cchild.clone();
							cchild_clone.appendTo(new_comment_wrap).fadeIn();

						}

						jQuery('#commentid-' + data.commentid + '').fadeTo(1000, 1);

						if( typeof data.restricted !== 'undefined' ){
							alert( data.restricted );
							window.location.reload();
						}

						if( typeof data.debug !== 'undefined' ){
							console.log( data.debug );
						}
					}
				});
			}
		} else if (( e.keyCode == 10 || e.keyCode == 13 ) && !e.shiftKey) {
			e.preventDefault();
			return false;
		}
	});

	/* Default behaviour */
	jQuery(document).on('click', '.um-groups-dialog a', function (e) {
		e.preventDefault();
		e.stopPropagation();
		return false;
	});

	/* open dialogs */
	jQuery(document).on('click', '.um-groups-start-dialog', function (e) {
		e.stopPropagation();
		e.preventDefault();
		if (!jQuery(this).parents('.um-groups-widget').hasClass('unready')) {
			var to_open = jQuery(this).parent().find('.' + jQuery(this).attr('data-role'));
			if (to_open.is(':visible')) {
				to_open.hide();
			} else {
				to_open.show();
			}
		}
		return false;
	});

	/* Opens comment edit dropdown */
	jQuery(document).on('click', '.um-groups-editc a', function (e) {
		e.stopPropagation();
		e.preventDefault();
		jQuery('.um-groups-comment-meta').find('.um-groups-editc-d:visible').hide();
		var commentedit = jQuery(this).parents('.um-groups-comment-meta').find('.um-groups-editc-d');

		if (commentedit.is(':visible')) {
			commentedit.hide();
		} else {
			commentedit.show();
		}
		return false;
	});

	/* Hides dropdown */
	jQuery(document).click(function () {
		jQuery('.um-groups-dialog').hide();
		jQuery('.um-groups-comment-meta').find('.um-groups-editc-d:visible').hide();
	});

	/* focus on comment area */
	jQuery(document).on('click', '.um-groups-comment a', function (e) {
		e.preventDefault();
		if (!jQuery(this).parents('.um-groups-widget').hasClass('unready')) {
			jQuery(this).parents('.um-groups-widget').find('.um-groups-comments .um-groups-comment-box textarea').focus();
		}
		return false;
	});

	/* Like of a comment */
	jQuery(document).on('click', '.um-groups-comment-like:not(.active)', function (e) {
		e.preventDefault();
		if (!jQuery(this).parents('.um-groups-commentl').hasClass('unready')) {
			var commentid = jQuery(this).parents('.um-groups-commentl').attr('id').replace('commentid-', '');
			var counter = jQuery(this).parents('.um-groups-commentl').find('.um-groups-ajaxdata-commentlikes');
			var ncount = parseInt(counter.html()) + 1;
			counter.html(ncount);
			jQuery(this).parents('.um-groups-commentl').find('.um-groups-comment-likes').removeClass().addClass('um-groups-comment-likes').addClass('count-' + ncount);
			jQuery(this).addClass('active');
			jQuery(this).html(jQuery(this).attr('data-unlike_text'));
			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'post',
				dataType: 'json',
				data: {
					action: 'um_groups_like_comment',
					commentid: commentid,
					group_id: jQuery('input[name="_group_id"]').val(),
					nonce: um_scripts.nonce
				},
				success: function (data) {
					if( typeof data.restricted !== 'undefined' ){
						alert( data.restricted );
						window.location.reload();
					}

					if( typeof data.debug !== 'undefined' ){
						console.log( data.debug );
					}
				}
			});
		}
		return false;
	});

	/* Unlike of a comment */
	jQuery(document).on('click', '.um-groups-comment-like.active', function (e) {
		e.preventDefault();
		var commentid = jQuery(this).parents('.um-groups-commentl').attr('id').replace('commentid-', '');
		var counter = jQuery(this).parents('.um-groups-commentl').find('.um-groups-ajaxdata-commentlikes');
		var ncount = parseInt(counter.html()) - 1;
		counter.html(ncount);
		jQuery(this).parents('.um-groups-commentl').find('.um-groups-comment-likes').removeClass().addClass('um-groups-comment-likes').addClass('count-' + ncount);
		jQuery(this).removeClass('active');
		jQuery(this).html(jQuery(this).attr('data-like_text'));
		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			dataType: 'json',
			data: {
				action: 'um_groups_unlike_comment',
				commentid: commentid,
				group_id: jQuery('input[name="_group_id"]').val(),
				nonce: um_scripts.nonce
			},
			success: function (data) {
				if( typeof data.restricted !== 'undefined' ){
					alert( data.restricted );
					window.location.reload();
				}

				if( typeof data.debug !== 'undefined' ){
					console.log( data.debug );
				}
			}
		});
		return false;
	});

	/* Like of a post */
	jQuery(document).on('click', '.um-groups-like:not(.active) a', function (e) {
		e.preventDefault();
		if (!jQuery(this).parents('.um-groups-widget').hasClass('unready')) {
			var postid = jQuery(this).parents('.um-groups-widget').attr('id').replace('postid-', '');
			jQuery(this).find('i').addClass('um-effect-pop');
			jQuery(this).parent().addClass('active');
			jQuery(this).find('span').html(jQuery(this).parent().attr('data-unlike_text'));
			jQuery(this).find('i').addClass('um-active-color');
			var count = jQuery(this).parents('.um-groups-widget').find('.um-groups-post-likes');
			count.html(parseInt(count.html()) + 1);
			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'post',
				dataType: 'json',
				data: {
					action: 'um_groups_like_post',
					postid: postid,
					group_id: jQuery('input[name="_group_id"]').val(),
					nonce: um_scripts.nonce
				},
				success: function (data) {
					if( typeof data.restricted !== 'undefined' ){
						alert( data.restricted );
						window.location.reload();
					}

					if( typeof data.debug !== 'undefined' ){
						console.log( data.debug );
					}
				}
			});
		}
		return false;
	});

	/* Unlike of a post */
	jQuery(document).on('click', '.um-groups-like.active a', function (e) {
		e.preventDefault();
		var postid = jQuery(this).parents('.um-groups-widget').attr('id').replace('postid-', '');
		jQuery(this).find('i').removeClass('um-effect-pop');
		jQuery(this).parent().removeClass('active');
		jQuery(this).find('span').html(jQuery(this).parent().attr('data-like_text'));
		jQuery(this).find('i').removeClass('um-active-color');
		var count = jQuery(this).parents('.um-groups-widget').find('.um-groups-post-likes');
		count.html(parseInt(count.html()) - 1);
		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			dataType: 'json',
			data: {
				action: 'um_groups_unlike_post',
				postid: postid,
				group_id: jQuery('input[name="_group_id"]').val(),
				nonce: um_scripts.nonce
			},
			success: function (data) {
				if( typeof data.restricted !== 'undefined' ){
					alert( data.restricted );
					window.location.reload();
				}

				if( typeof data.debug !== 'undefined' ){
					console.log( data.debug );
				}

			}
		});
		return false;
	});

	/* Open post edit */
	jQuery(document).on('click', '.um-groups-manage, .um-groups-edit-cancel', function (e) {
		e.preventDefault();
		var el = jQuery(this);
		var post_id = el.parents('.um-groups-widget').attr('id').replace('postid-', '');

		if (jQuery(this).parents('.um-groups-dialog').length) {
			jQuery(this).parents('.um-groups-dialog').hide();
		}

		if (el.parents('.um-groups-widget').find('form').length > 0) {

			el.parents('.um-groups-widget').find('.um-groups-bodyinner-txt').show();
			el.parents('.um-groups-widget').find('.um-groups-bodyinner-photo').show();
			el.parents('.um-groups-widget').find('.um-groups-bodyinner-video').show();
			el.parents('.um-groups-widget').find('form').remove();

		} else {

			var editarea = jQuery('.um-groups-new-post form').clone();
			editarea.appendTo(el.parents('.um-groups-widget').find('.um-groups-bodyinner-edit'));
			editarea.find('textarea:visible').val(el.parents('.um-groups-widget').find('.um-groups-bodyinner-edit').find('textarea:hidden').val()).focus();

			if ( el.parents('.um-groups-widget').find('.um-groups-bodyinner-edit').find('#_photo_').val() ) {
				editarea.find('.um-groups-preview').show();

				if( el.parents('.um-groups-widget').find('.um-groups-bodyinner-edit').find('#_photo_url').val() ){
					editarea.find('.um-groups-preview img').attr('src', el.parents('.um-groups-widget').find('.um-groups-bodyinner-edit').find('#_photo_url').val() );
					editarea.find('.um-groups-preview input[type=hidden]').val( el.parents('.um-groups-widget').find('.um-groups-bodyinner-edit').find('#_photo_url').val() );
				}else{
					editarea.find('.um-groups-preview img').attr('src', el.parents('.um-groups-widget').find('.um-groups-bodyinner-edit').find('#_photo_').val());
					editarea.find('.um-groups-preview input[type=hidden]').val(el.parents('.um-groups-widget').find('.um-groups-bodyinner-edit').find('#_photo_').val());
				}

				var image_input = el.parents('.um-groups-widget').find('.um-groups-bodyinner-edit').find('#_photo_').clone();
				var image_url_input = el.parents('.um-groups-widget').find('.um-groups-bodyinner-edit').find('#_photo_url').clone();

				image_input.appendTo(el.parents('.um-groups-widget').find('.um-groups-bodyinner-edit'));
				image_url_input.appendTo(el.parents('.um-groups-widget').find('.um-groups-bodyinner-edit'));

			}

			editarea.find('.um-groups-post').html(el.attr('data-update_text'));
			editarea.find('.um-groups-post').before('<a href="#" class="um-groups-edit-cancel">' + el.attr('data-cancel_text') + '</a>');
			editarea.find('#_post_id').val(post_id);
			el.parents('.um-groups-widget').find('.um-groups-bodyinner-txt').hide();
			el.parents('.um-groups-widget').find('.um-groups-bodyinner-photo').hide();
			el.parents('.um-groups-widget').find('.um-groups-bodyinner-video').hide();

			UM_wall_img_upload();
			UM_wall_autocomplete_start();
		}

		jQuery('textarea.um-groups-textarea-elem').autoResize();
		return false;
	});


	function um_objectifyForm( formArray ) {//serialize data function

		var returnArray = {};
		for (var i = 0; i < formArray.length; i++){
			returnArray[formArray[i]['name']] = formArray[i]['value'];
		}
		return returnArray;
	}


	/* Post publish */
	jQuery(document).on('submit', '.um-groups-publish', function (e) {
		e.preventDefault();
		var this_form = jQuery(this);
		if (this_form.find('textarea').val().trim().length == 0 && this_form.find('#_post_img').val().trim().length == 0) {
			this_form.find('textarea').focus();
		} else {

			jQuery('.um-groups-post').addClass('um-disabled');
			formdata = this_form.serialize();
			formdata_array = um_objectifyForm( this_form.serializeArray() );


			// new post
			if (this_form.find('#_post_id').val() == 0) {

				var wall = this_form.parents('.um').find('.um-groups-wall');
				var clone = wall.find('.um-groups-clone:first');
				var clonel = clone.clone();
				clonel.prependTo(wall).addClass('unready').fadeIn().find('.um-groups-bodyinner-txt').text(this_form.find('textarea').val());
				if (this_form.find('#_post_img').val().trim().length > 0) {
					if (clonel.find('.um-groups-bodyinner-txt').html().trim().length == 0) {
						clonel.find('.um-groups-bodyinner-txt').hide();
					}
					clonel.prependTo(wall).find('.um-groups-bodyinner-photo').html('<a href="#" class="um-photo-modal" data-src="' + this_form.find('#_post_img').val() + '"><img src="' + this_form.find('#_post_img_url').val() + '" alt="" /></a>');
				}
				this_form.find('textarea').val('').height('auto');
				this_form.find('#_post_img').val('');

				this_form.find('.um-groups-preview').hide();

				jQuery('.um-groups-textarea-elem').attr('placeholder', jQuery('.um-groups-textarea-elem').attr('data-ph'));

			} else {

				this_form.css({opacity: 0.5});

			}

			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'post',
				dataType: 'json',
				data: formdata,
				success: function( data ) {

					// new post
					if ( this_form.find('#_post_id').val() == 0 ) {

						this_form.find('.um-groups-preview').find('img').attr('src', '');

						clonel.removeClass('unready').attr('id', 'postid-' + data.postid).removeClass('um-groups-clone');
						clonel.find('.um-groups-comment-textarea').show();
						if ( data.orig_content ) {
							clonel.find('.um-groups-bodyinner-edit textarea').val( data.orig_content );
						} else {
							clonel.find('.um-groups-bodyinner-edit textarea').val('');
						}
						if ( data.content ) {
							clonel.find('.um-groups-bodyinner-txt').html( data.content );
						} else {
							clonel.find('.um-groups-bodyinner-txt').empty().hide();
						}

						if (data.link) {
							if (clonel.find('.um-groups-bodyinner-txt').find('.post-meta').length) {
								clonel.find('.um-groups-bodyinner-txt').show().find('.post-meta').replaceWith(data.link);
							} else {
								clonel.find('.um-groups-bodyinner-txt').show().append(data.link);
							}
						}

						if ( data.photo ) {
							clonel.find('.um-groups-bodyinner-edit input#_photo_').val( data.photo_base );
							clonel.find('.um-groups-bodyinner-edit input#_photo_url').val( data.photo );
							clonel.find('.um-groups-bodyinner-photo').find('a').attr('data-src', data.photo);
							clonel.find('.um-groups-bodyinner-photo').find('a').attr('href', data.photo);
							clonel.find('.um-groups-bodyinner-photo').find('img').attr('src', data.photo);
						} else {
							clonel.find('.um-groups-bodyinner-edit input#_photo_').val('');
						}
						if (data.video) {
							clonel.find('.um-groups-bodyinner-video').html(data.video);
						}

						clonel.find('.um-groups-metadata a').attr('href', data.permalink);
						jQuery(clonel.find('.um-groups-comment-textarea')).autoResize();
					} else {

						elem = this_form.parents('.um-groups-widget');
						elem.find('form').remove();
						if (data.orig_content) {
							elem.find('.um-groups-bodyinner-edit textarea').val(data.orig_content);
						} else {
							elem.find('.um-groups-bodyinner-edit textarea').val('');
						}

						if (data.content) {
							elem.find('.um-groups-bodyinner-txt').html(data.content);
							elem.find('.um-groups-bodyinner-txt').show();
						} else {
							elem.find('.um-groups-bodyinner-txt').empty().hide();
						}

						if (data.link) {
							if (elem.find('.um-groups-bodyinner-txt').find('.post-meta').length) {
								elem.find('.um-groups-bodyinner-txt').show().find('.post-meta').replaceWith(data.link);
							} else {
								elem.find('.um-groups-bodyinner-txt').show().append(data.link);
							}
						}

						if ( data.photo ) {
							elem.find('.um-groups-bodyinner-edit input#_photo_').val( data.photo_base );
							elem.find('.um-groups-bodyinner-edit input#_photo_url').val( data.photo );

							if (elem.find('.um-groups-bodyinner-photo').find('a').length == 0) {
								elem.find('.um-groups-bodyinner-photo').html('<a href="' + data.photo + '"><img src="' + data.photo + '" alt="" /></a>');
							} else {
								elem.find('.um-groups-bodyinner-photo').find('a').attr('href', data.photo);
								elem.find('.um-groups-bodyinner-photo').find('img').attr('src', data.photo);
							}
							elem.find('.um-groups-bodyinner-photo').show();
						} else {
							elem.find('.um-groups-bodyinner-edit input#_photo_').val('');
							elem.find('.um-groups-bodyinner-photo').empty().hide();
						}
						if (data.video) {
							elem.find('.um-groups-bodyinner-video').html(data.video);
							elem.find('.um-groups-bodyinner-video').show();
						} else {
							elem.find('.um-groups-bodyinner-video').empty().hide();
						}

					}

					UM_wall_autocomplete_start()

				}

			});

		}
		return false;
	});

	/* Show hidden post content */
	jQuery(document).on('click', '.um-groups-seemore a', function (e) {
		e.preventDefault();
		p = jQuery(this).parents('.um-groups-bodyinner-txt');
		p.find('.um-groups-seemore').remove();
		p.find('.um-groups-hiddentext').show();
		return false;
	});

	/* Comment area */
	jQuery('.um-groups-widget:not(.um-groups-clone) .um-groups-comment-textarea').autoResize();
	jQuery('.um-groups-textarea-elem').autoResize();


	// Groups discussion post approval
	jQuery(document).on('click','.um-groups-post-approval-tool',function(){
		var me = jQuery(this);
		var role = jQuery(this).attr('data-role');
		var user_id = jQuery(this).attr('data-uid');
		var group_id = jQuery('input[name="_group_id"]').val();
		var post_id = jQuery(this).attr('data-discussion-id');
		var msg = me.attr('data-msg');

		if( action == 'delete' ){
			UM_wall_confirmbox_show( post_id, msg, 'um-groups-confirm-removepost');
			UM_wall_confirmbox_mobile();
		} else {
			jQuery.ajax({
				method: 'POST',
				url: wp.ajax.settings.url,
				type: 'post',
				data: {
					role: role,
					action: 'um_groups_approve_discussion_post',
					group_id: group_id,
					user_id: user_id,
					post_id: post_id,
					nonce: um_scripts.nonce
				}
			}).done( function( data ){

				if( data.status == 'deleted' || data.status == 'approved' ){
					me.parents('div[class=um-groups-widget]').remove();
				}

				if( me.parents('div[class=um-groups-wall]').find('div[class=um-groups-widget]:not(.um-groups-clone)').length <= 0 ){
				   um_groups_default_tab();
				}

			});
		}
	});

	/**
	 * Redirect to default tab
	 */
	function um_groups_default_tab() {
		jQuery('ul.um-groups-single-tabs li:first a').trigger('click');
	}

	jQuery('ul.um-groups-single-tabs li:first a').on('click',function(){
		 window.location.href = jQuery(this).attr('href');
	});
});
