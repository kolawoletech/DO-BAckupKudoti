function vgfaInitIframes() {
	// Bail if iframes were already initiated
	if (typeof window.vgcaIsFrontendSession !== 'undefined' && window.vgcaIsFrontendSession) {
		return true;
	}
	var popupSelectors = '.thickbox-loading .media-modal.wp-core-ui,.media-modal';
	if (vgfa_data.extra_popup_selectors) {
		popupSelectors += ', ' + vgfa_data.extra_popup_selectors;
	}
	window.vgcaIsFrontendSession = true;
	window.$iframeWrappers = jQuery('.vgca-iframe-wrapper ');
	$iframeWrappers.each(function () {
		var $iframeWrapper = jQuery(this);
		var $iframe = $iframeWrapper.find('iframe');
		var hash = window.location.hash;
		var iframeStartsAt = $iframe.offset().top;

		if ($iframe.data('forward-parameters') && hash) {
			if ($iframe.data('lazy-load')) {
				$iframe.data('src', $iframe.data('src') + hash);
			} else {
				$iframe.attr('src', $iframe.attr('src') + hash);
			}
		}

		$iframe.data('lastPage', $iframe.contents().get(0).location.href);

		// Update iframe height when the window is resized
		jQuery(window).on('resize', function () {
			$iframeWrappers.css('height', '');
			$iframe.css('height', '');
		});
		setInterval(function () {

			if ($iframe.data('lazy-load') && $iframe.is(':visible') && !$iframe.attr('src')) {
				vgfaStartLoading($iframeWrapper);
				$iframe.attr('src', $iframe.data('src'));
			}

			var currentPage = $iframe.contents().get(0).location.href;

			// If the user navigated to another admin page, update the iframe height
			if (currentPage !== $iframe.data('lastPage')) {
				$iframeWrappers.css('height', '');
				$iframe.css('height', '');
				$iframe.data('lastPage', currentPage);
			}

			// Prevent js errors when the admin page hasn't loaded yet
			try {
				var iframeHeight = $iframe.contents().find('body').height();

				// Support for full screen pages. The frontend editor has height=0 and it uses the window height
				if (vgfa_data.fullscreen_pages_keywords) {
					var isFullScreen = false;
					vgfa_data.fullscreen_pages_keywords.forEach(function (keyword) {
						if (currentPage.indexOf(keyword) > -1) {
							isFullScreen = true;
						}
					});

					if ($iframe.contents().find('.block-editor__container').length) {
						isFullScreen = true;
					}

					if (isFullScreen) {
						$iframe.addClass('vgfa-full-screen');
						jQuery('body').addClass('vgfa-full-screen-activated');
					} else {
						$iframe.removeClass('vgfa-full-screen');
						jQuery('body').removeClass('vgfa-full-screen-activated');
					}
				}


				// Set iframe height based on the content height
				$iframe.height(iframeHeight);
				$iframeWrapper.height(iframeHeight);

				// Auto scroll towards the visible popups
				if (typeof $iframe.contents().find === 'function') {
					if (vgfa_data.backend_js_urls) {
						var jsInsertedLate = false;
						vgfa_data.backend_js_urls.forEach(function (backendJsUrl) {
							if (typeof $iframe[0].contentWindow.jQuery === 'function' && !$iframe[0].contentWindow.jQuery('body').html().indexOf(backendJsUrl) < 0) {
								$iframe[0].contentWindow.jQuery('body').append('<script type="text/javascript" src="' + backendJsUrl + '"></script>');
								jsInsertedLate = true;

							}

						});
						if (jsInsertedLate) {
							vgfaStopLoading($iframeWrapper);
						}
					}
					$iframe.contents().find('body').data('parent-id', $iframe.attr('id'));

					var $visiblePopups = $iframe.contents().find(popupSelectors).filter(function () {
						return jQuery(this).is(':visible');
					});

					$visiblePopups.each(function () {
						var $popup = jQuery(this);
						var topPosition = $popup.offset().top;
						var elementStart = iframeStartsAt + topPosition - 150;
						var elementEnd = elementStart + $popup.height();

						if (jQuery(window).scrollTop() !== elementStart && ((jQuery(window).scrollTop() > elementEnd) || (jQuery(window).scrollTop() + jQuery(window).height()) < elementStart)) {
							jQuery('html,body').scrollTop(elementStart);
						}
					});
				}
			} finally {

			}
		}, 1000);
	});
}

function vgfaStartLoading($parent) {
	if (!$parent) {
		var $parent = jQuery('.vgca-iframe-wrapper');
	}
	$parent.find('.vgca-loading-indicator').show();
	$parent.addClass('vgfa-is-loading');
}

function vgfaStopLoading($parent, newHeight) {
	if (!$parent) {
		var $parent = jQuery('.vgca-iframe-wrapper');
	}
	if (newHeight) {
		$parent.find('iframe').height(newHeight);
		$parent.height(newHeight);
	}

	$parent.find('.vgca-loading-indicator').hide();
	$parent.removeClass('vgfa-is-loading');
}

function vgfaGetVisibleAdminPage() {

	var $visibleBackendPage = jQuery('.vgca-iframe-wrapper iframe').filter(function () {
		return jQuery(this).is(':visible');
	}).first();

	return $visibleBackendPage;
}

jQuery(window).on('load', function () {
	vgfaInitIframes();

	// Fix. TinyMCE plugins call the send_to_editor on the parent window, 
	// which by mistake is our frontend page. We forward the call 
	// to the function inside the iframe (backend)
	window.send_to_editor = function (arg) {
		var $visibleAdminPage = vgfaGetVisibleAdminPage();
		$visibleAdminPage[0].contentWindow.send_to_editor(arg);
	}
});

jQuery(document).ready(function () {
	var $quickSettings = jQuery('.vg-frontend-admin-quick-settings');

	if (!$quickSettings.length) {
		return true;
	}

	var $toggle = jQuery('.vg-frontend-admin-quick-settings-toggle');
	jQuery('body').append($quickSettings);
	jQuery('body').append($toggle);
	jQuery('body').addClass('vgfa-has-quick-settings');

	$quickSettings.find('.common-errors').hide();
	$quickSettings.find('.expand-common-errors').click(function (e) {
		e.preventDefault();

		$quickSettings.find('.common-errors').slideToggle();
	});

	var $saveButton = $quickSettings.find('button');
	$quickSettings.submit(function (e) {
		e.preventDefault();

		$saveButton.text($saveButton.data('saving-text'));

		jQuery.post(vgfa_data.wp_ajax_url, $quickSettings.serialize(), function (response) {
			if (response.success) {
				alert(response.data.message);
				window.location.href = response.data.new_url;
			}
		});
		return false;
	});

// Remove elements tool
	var $hideElements = $quickSettings.find('.hide-elements-trigger');
	var $hideElementsInput = $quickSettings.find('.hide-elements-input');
	$quickSettings.find('.show-elements-trigger').click(function (e) {
		e.preventDefault();

		var $visibleBackendPage = vgfaGetVisibleAdminPage();
		if (!$visibleBackendPage.length) {
			return true;
		}

		$visibleBackendPage[0].contentWindow.jQuery($hideElementsInput.val()).each(function () {
			jQuery(this).attr('style', 'display: initial !important');
		});

		$hideElementsInput.val('');
	});
	$hideElements.click(function (e) {
		e.preventDefault();

		var $visibleBackendPage = vgfaGetVisibleAdminPage();
		if (!$visibleBackendPage.length) {
			return true;
		}
		$visibleBackendPage[0].contentWindow.vgfaStartHideElementOutline();
		window.isHideElementOutlineActive = true;
	});

// Edit texts tool
	var $startEditingText = $quickSettings.find('.edit-text-trigger');
	var $stopEditingText = $quickSettings.find('.stop-edit-text-trigger');
	var $revertTextChangesInput = $quickSettings.find('.revert-all-text-edits-trigger');
	var $textChangesInput = $quickSettings.find('.text-changes-input');
	$revertTextChangesInput.click(function (e) {
		e.preventDefault();
		$textChangesInput.val('');
		jQuery('.vg-frontend-admin-save-button').click();
	});
	$startEditingText.click(function (e) {
		e.preventDefault();
		var $visibleBackendPage = vgfaGetVisibleAdminPage();
		if (!$visibleBackendPage.length || typeof $visibleBackendPage[0].contentWindow.vgfaStartTextEdit !== 'function') {
			return true;
		}

		$visibleBackendPage[0].contentWindow.vgfaStartTextEdit();
		$startEditingText.hide();
		$stopEditingText.show();

		// Use by the admin page window, when we navigate from one admin page to another
		// we check this flag in the parent window to continue in editing mode
		window.vgfaIsEditingText = true;
	});
	$stopEditingText.click(function (e) {
		e.preventDefault();
		var $visibleBackendPage = vgfaGetVisibleAdminPage();
		if (!$visibleBackendPage.length || typeof $visibleBackendPage[0].contentWindow.vgfaStopTextEdit !== 'function') {
			return true;
		}

		$visibleBackendPage[0].contentWindow.vgfaStopTextEdit();
		$stopEditingText.hide();
		$startEditingText.show();
		window.vgfaIsEditingText = false;
	});

	jQuery('body').addClass('vg-frontend-admin-visible-quick-settings');
	$toggle.click(function (e) {
		e.preventDefault();

		if ($quickSettings.is(':visible')) {
			$quickSettings.hide();
			jQuery('body').removeClass('vg-frontend-admin-visible-quick-settings');
			$toggle.text('+');
			$toggle.css('left', '0');
			var $visibleBackendPage = vgfaGetVisibleAdminPage();
			if ($visibleBackendPage.length) {
				// Force to resize the iframe
				$visibleBackendPage.data('lastPage', 'xx');
			}
		} else {
			$quickSettings.show();
			jQuery('body').addClass('vg-frontend-admin-visible-quick-settings');
			$toggle.text('x');
			$toggle.css('left', '');
			var $visibleBackendPage = vgfaGetVisibleAdminPage();
			if ($visibleBackendPage.length) {
				// Force to resize the iframe
				$visibleBackendPage.data('lastPage', 'xx');
			}
		}
	});
});
