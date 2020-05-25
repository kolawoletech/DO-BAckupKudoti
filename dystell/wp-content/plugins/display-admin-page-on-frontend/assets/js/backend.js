
/**
 * A (possibly faster) way to get the current timestamp as an integer.
 * @returns int
 */
function _now() {
	var out = Date.now() || new Date().getTime();
	return out;
}
/**
 * Returns a function, that, when invoked, will only be triggered at most once during a given window of time. Normally, the throttled function will run as much as it can, without ever going more than once per wait duration; but if you’d like to disable the execution on the leading edge, pass {leading: false}. To disable execution on the trailing edge, ditto.
 * @param func
 * @param int wait
 * @param obj options
 * @returns func
 */
function _throttle(func, wait, options) {

	if (!wait) {
		wait = 300;
	}
	var context, args, result;
	var timeout = null;
	var previous = 0;
	if (!options)
		options = {};
	var later = function () {
		previous = options.leading === false ? 0 : _now();
		timeout = null;
		result = func.apply(context, args);
		if (!timeout)
			context = args = null;
	};
	return function () {
		var now = _now();
		if (!previous && options.leading === false)
			previous = now;
		var remaining = wait - (now - previous);
		context = this;
		args = arguments;
		if (remaining <= 0 || remaining > wait) {
			if (timeout) {
				clearTimeout(timeout);
				timeout = null;
			}
			previous = now;
			result = func.apply(context, args);
			if (!timeout)
				context = args = null;
		} else if (!timeout && options.trailing !== false) {
			timeout = setTimeout(later, remaining);
		}
		return result;
	};
}
;


// Add the title to the "view in frontend" request
jQuery(document).ready(function () {
	var $viewInFrontend = jQuery('#wp-admin-bar-vgca-direct-frontend-link a');
	if ($viewInFrontend.length) {
		var slug = encodeURIComponent(window.location.href.replace(window.vgfaWpAdminBase, ''));
		var $title = jQuery('h1').first().clone();

		// Some plugins have weird tags inside the main <h1>, like gravityforms
		$title.find('script, style, iframe, form, input').remove();

		$viewInFrontend.attr('href', $viewInFrontend.attr('href') + '&vgca_slug=' + slug + '&title=' + $title.text());
	}
});
if (window.parent.location.href !== window.location.href) {
	// Fix for elementor because it loads the editor without our scripts and the admin page with our script is inside an iframe
	var iframeParent = (window.location.href.indexOf('&elementor-preview') > -1) ? window.parent.parent : window.parent;

	var vgfaCustomCssFinal = (typeof window.vgfaCustomCss === 'undefined' && iframeParent.location.href !== window.location.href && iframeParent.vgfaCustomCss) ? iframeParent.vgfaCustomCss : window.vgfaCustomCss;
	if (typeof vgfaCustomCssFinal !== 'undefined') {
		jQuery('head').append('<style id="vgca-custom-css">' + vgfaCustomCssFinal + '</style>');
	}

	// we use vanilla JS for the parent because we execute this very early when the frontend page hasn't loaded jquery yet
	if (iframeParent.location.href !== window.location.href && iframeParent.document.getElementsByClassName('vgfa-admin-css').length) {
		jQuery('head').append(iframeParent.document.getElementsByClassName('vgfa-admin-css')[0].outerHTML);
	}

	// If URL is not for wp-admin page, open outside the iframe
	jQuery(document).ready(function () {
		jQuery('body').on('click', 'a', function (e) {
			var url = jQuery(this).attr('href');

			if (typeof url === 'string' && url.indexOf(vgfaWpAdminBase) < 0 && url.indexOf('http') > -1) {
				top.window.location.href = url;
				e.preventDefault();
				return false;
			}
		});
	});
	jQuery(window).unload(function () {
		if (typeof iframeParent.jQuery === 'function') {
			iframeParent.vgfaStartLoading();
		}
		return null;
	});
	jQuery(window).load(function () {
		if (typeof iframeParent.jQuery === 'function') {
			iframeParent.vgfaStopLoading(null, jQuery('body').height());
			if (typeof vgfaRequiredRoles !== 'undefined') {
				iframeParent.jQuery('.required-capability-target').append(vgfaRequiredRoles);
			}
		}
	});
}
function vgfaGetElementCSSSelector(el) {
	var names = [];
	while (el.parentNode) {
		if (el.id) {
			names.unshift('#' + el.id);
			break;
		} else {
			if (el == el.ownerDocument.documentElement)
				names.unshift(el.tagName);
			else {
				for (var c = 1, e = el; e.previousElementSibling; e = e.previousElementSibling, c++)
					;
				names.unshift(el.tagName + ":nth-child(" + c + ")");
			}
			el = el.parentNode;
		}
	}
	return names.join(" > ");
}
function vgfaSelectedElementToHide(element) {
	var selector = vgfaGetElementCSSSelector(element);
	var existingSelectors = iframeParent.jQuery('.hide-elements-input').val();

	// Hide elements for the preview
	jQuery(selector).hide();

	if (existingSelectors) {
		selector = ',' + selector;
	}
	iframeParent.jQuery('.hide-elements-input').val(existingSelectors + selector);
}
function vgfaStartHideElementOutline() {
	window.vgfaHideElementOutline = DomOutline({onClick: vgfaSelectedElementToHide});// Start outline:
	vgfaHideElementOutline.start();
}

function vgfaGetElementsWithTextEdit() {
	return jQuery('h1,h2,h3,h4,h5,h6,span,a,button,p,div,label, td, th, abbr, blockquote').filter(function () {
		return jQuery(this).children().length < 1;
	});
}

function vgfaStartTextEdit() {
	vgfaGetElementsWithTextEdit().attr('contenteditable', '');
	jQuery('body').append('<style id="text-change-css">[contenteditable] {    border: 2px solid #ffb300 !important;}</style>');
}
function vgfaStopTextEdit() {
	vgfaGetElementsWithTextEdit().removeAttr('contenteditable');
	jQuery('#text-change-css').remove();
}
jQuery(document).ready(function () {
	if (!iframeParent || !iframeParent.jQuery('.text-changes-input').length) {
		return true;
	}
	// Continue in editing mode when we navigate to another page by checking on the parent window's flag
	if (typeof iframeParent.vgfaIsEditingText !== 'undefined' && iframeParent.vgfaIsEditingText) {
		vgfaStartTextEdit();
	}

	// Listen for text changes
	jQuery('body').on('focus', '[contenteditable]', function () {
		const $this = jQuery(this);
		$this.data('before', $this.html());
		console.log('Now1', $this.html());
	}).on('blur keyup paste input', '[contenteditable]', function () {
		const $this = jQuery(this);
		if ($this.data('before') !== $this.html()) {
			$this.data('after', $this.html());
			$this.trigger('change');
			console.log('Now2', $this.html());
		}
	}).on('mouseover', '[contenteditable]', function () {
		jQuery(this).focus();
	});
	vgfaGetElementsWithTextEdit().on('change', _throttle(function (e) {
		var $element = jQuery(this);
		if ($element.data('before') && $element.data('after') && $element.data('before') !== $element.data('after')) {

			var existingTextEdits = iframeParent.jQuery('.text-changes-input').val();

			console.log('existingTextEdits: ', existingTextEdits);
			if (existingTextEdits) {
				var textEdits = JSON.parse(existingTextEdits);
				if (!textEdits || !textEdits.length) {
					textEdits = {};
				}
			} else {
				var textEdits = {};
			}
			var url = window.location.href;

			if (typeof textEdits[url] === 'undefined') {
				textEdits[url] = {};
			}
			var before = jQuery.trim($element.data('before'));
			var after = jQuery.trim($element.data('after'));

			textEdits[url][before] = after;
			console.log('textEdits: ', textEdits);
			iframeParent.jQuery('.text-changes-input').val(JSON.stringify(textEdits));
		}
	}, 4000, {
		leading: true,
		trailing: true
	}));
});

// Show own posts
jQuery(document).ready(function () {
	if (!iframeParent) {
		return true;
	}
	if (!iframeParent.jQuery('.vg-frontend-admin-quick-settings .show-own-posts').length || typeof window.vgfaTableColumnsPostType === 'undefined' || !window.vgfaTableColumnsPostType) {
		iframeParent.jQuery('.vg-frontend-admin-quick-settings .show-own-posts').hide();
		return true;
	}
	var $showOwnPosts = iframeParent.jQuery('.vg-frontend-admin-quick-settings .show-own-posts input');
	$showOwnPosts.each(function () {
		jQuery(this).attr('name', jQuery(this).attr('name').replace('{post_type}', vgfaTableColumnsPostType));
	});
});

// Table columns manager
jQuery(document).ready(function () {
	if (!iframeParent) {
		return true;
	}
	var $columnsManager = iframeParent.jQuery('.vg-frontend-admin-quick-settings .columns-manager');
	if (!$columnsManager.length || typeof window.vgfaTableColumns === 'undefined' || !window.vgfaTableColumns) {
		$columnsManager.hide();
		return true;
	}

	$columnsManager.find('.columns-wrapper').empty();
	jQuery.each(vgfaTableColumns, function (columnKey, columnLabel) {
		var $column = $columnsManager.find('.column-template').first().clone();
		$column.find('span').text(columnLabel);
		$column.find('input').attr('value', columnKey);
		$column.find('input').attr('name', $column.find('input').attr('name').replace('{post_type}', vgfaTableColumnsPostType));

		if (typeof iframeParent.vgfaDisabledColumns !== 'undefined' && typeof iframeParent.vgfaDisabledColumns[vgfaTableColumnsPostType] !== 'undefined' && iframeParent.vgfaDisabledColumns[vgfaTableColumnsPostType].indexOf(columnKey) > -1) {
			$column.find('input').attr('checked', 'checked');
		}

		$columnsManager.find('.columns-wrapper').append($column);
	});
});