
var $linksEnabled;
var $modalLinksEnabled;
var $backForthEnabled;
var $previousBackForthEnabled;
var $urlChangeThroughJs;
var $csrf;
var $formSpinner;
var $loginSpinner;
var $deleteSpinner;
var $setLinksToFalse;
var $setUrlChangeThroughJsToFalse;
var $editFormInputs;
var $searchSpinner;
var $editorPluginsAdded;

var $genericLoginError;
var $genericException;
var $adminTitle;

$(document).ready(function(){

	var head = $('head');

	$csrf = head.attr('data-csrf');
	$adminTitle = head.attr('data-title');
	$cmsPrefix = head.attr('data-cmsprefix');

	setupApp();

});

function setupApp(afterLogin, afterLogout) {

	$linksEnabled = true;
	$modalLinksEnabled = true;
	$backForthEnabled = false;
	$previousBackForthEnabled = false;
	$urlChangeThroughJs = false;
	$formSpinner = false;
	$loginSpinner = false;
	$deleteSpinner = false;
	$setLinksToFalse = false;
	$setUrlChangeThroughJsToFalse = false;
	$editorPluginsAdded = false;

	$genericLoginError = "An error has occurred. Please try again.";
	$genericException = "An error has occurred. Please refresh the page and try again.";

	if (!afterLogin) {
		loadLoginForm();
		setLoginHandling();
	}

	if (!afterLogout) {

	}

	if (!afterLogin && !afterLogout) {
		setBackAndForthHandling();
	}

	setSelectize();
	setBackAndForth();

	if (getGtcmsPremium()) {
		positionQuickEdit();
	}

	resetApp();

}

function resetApp(soft) {
	setFormHandling();
	setEditors();
	setLinkHandling();
	setupEditFormInputs();

	if (!soft) {
		setIndexSearchFunctionality();
		setupRepositioning();
	}

	setDateTimePicker();
	setDatePicker();
	setFileUpload();
	setNumericOnly();
	setAutosize();
	setIndexSelectAjaxUpdate();
}

function getGtcmsPremium() {
	return (typeof $gtcmsPremium === "undefined" || !$gtcmsPremium) ? false : true;
}

function setAutosize() {
	var textareas = $("textarea.autosize");
	if (textareas.length) {
		autosize(textareas);
	}

	$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
		var subTabId = $(e.target).attr("href");
		var autosizeTextareas = $("div" + subTabId).find("textarea.autosize");
		if (autosizeTextareas.length) {
			autosize.update(autosizeTextareas);
		}
	});
}

function displayMessage(data, type) {
	var types = {
		exception : 'danger',
		error: 'warning',
		success: 'info'
	};

	if (!types[type]) {
		type = 'danger';
	} else {
		type = types[type];
	}

	var message;
	if (typeof data === 'object') {
		if (data.exception) {
			message = data.exception;
		} else if (data.message) {
			message = data.message;
		} else {
			message = $genericException;
		}
	} else {
		message = data;
	}

	message = '<div class="alert alert-' + type + ' alert-dismissable">' +
		'<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' +
		message +
		'</div>';

	var container = $("div.globalMessages");
	if (container.length) {
		container.hide();
		container.html(message);
		container.slideDown(250);
	}
}

function setIndexSelectAjaxUpdate() {
	var selects = $("select.ajaxSelectUpdate");
	if (selects.length) {
		selects.off("change");
		selects.on("change", function(){
			var select = $(this);
			if (select.hasClass('disabled')) {
				return;
			}
			select.addClass('disabled');

			var className = select.attr('data-classname');
			var objectId = select.attr('data-objectid');
			var property = select.attr('data-property');
			var token = select.attr('data-token');
			var value = select.val();

			$.ajax({
				type: 'POST',
				url: getCmsPrefix(true, true) + 'ajaxUpdate',
				data: {
					className: className,
					objectId: objectId,
					property: property,
					value: value,
					_token: token,
					getIgnore_isAjax: true
				},
				beforeSend: function(xhr){
					xhr.setRequestHeader('X-CSRF-TOKEN', $csrf);
				},
				success: function(data) {
					if (data) {
						if (data.success) {

						} else {
							if (data.redirectToLogin) {
								window.location.replace(getCmsPrefix(true, true) + "login");
							} else {
								displayMessage(data);
							}
						}
					} else {
						console.error("no data");
						displayMessage(data);
					}
				},
				error: function(jqXHR, error, errorThrown) {
					console.error("Status: " + jqXHR.status + "; Response text: " + jqXHR.responseText);
					displayMessage($genericException);
				},
				complete: function() {
					select.removeClass('disabled');
				}
			});

		});
	}
}

function setNumericOnly() {
	var numericInt = $("input.numericInt");
	numericInt.removeNumeric();
	numericInt.numeric({ decimal: false, negative: false });

	var numericDecimal = $("input.numericDecimal");
	numericDecimal.removeNumeric();
	numericDecimal.numeric({ decimalPlaces: 2, negative: false });
}

function setFileUpload() {

	var fileUploadFields = $("input.fileUpload");
	if (fileUploadFields.length) {

		fileUploadFields.each(function(i){
			var field = $(this);
			//console.error(field, "field");
			var imageUpload = false;
			var customAutoUpload = true;
			var container = field.parents("div.fileUploadContainer");
			var entityUrlParam = "uploadFile";
			if (container.hasClass('imageUpload')) {
				imageUpload = true;
				entityUrlParam = "uploadImage";
				field.imagePreviewContainer = container.find("div.imagePreview");
				field.theImage = container.find("img.theImage");
			}
			field.container = container;
			field.progressBar = container.find("div.progress-bar");
			field.messageContainer = container.find("div.uploadError");
			field.fileUploadContainer = container.find("div.fileUploadForm");
			field.fileDownloadContainer = container.find('div.fileDownloadContainer');
			field.fileDownloadLink = container.find('a.fileDownloadLink');
			field.hiddenInput = container.find('input[type="hidden"]');

			var entityName = container.attr('data-entityname');
			var entityId = container.attr('data-entityid');
			var fileNameField = container.attr('data-filenamefield');

			var getParams = window.location.search.replace("?", "");
			var url = getCmsPrefix(true, true) + entityName + "/" + entityUrlParam + "/" + fileNameField + "/" + entityId + "?" + getParams;

			field.fileupload({
				url: url,
				sequentialUploads: true,
				dataType: 'json',
				disableImageResize: true,
				previewMaxWidth: 80,
				previewMaxHeight: 50,
				previewCrop: false,
				previewContainer: field.imagePreviewContainer,
				imageUpload: imageUpload,
				autoUpload: false,
				progressall: function (e, data) {
					//console.error("progress");
					var progress = parseInt(data.loaded / data.total * 100, 10);
					field.progressBar.css(
						'width',
						progress + '%'
					);
				},
				submit: function (e, data) {
					//console.error("submit");
					$linksEnabled = false;
					field.messageContainer.hide();
				},
				done: function (e, data) {
					//console.error("done");
					if (data && data.result) {
						if (data.result.success) {
							field.fileDownloadLink.attr("href", data.result.fileOriginalUrl);
							if (imageUpload) {
								field.theImage.attr('src', data.result.fileUrl);
							}
							field.hiddenInput.val(data.result.filename);
							field.container.attr('data-filenamevalue', data.result.filename);
							setTimeout(function(){
								field.fileUploadContainer .addClass('hidden');
								field.fileDownloadContainer .removeClass('hidden');
							}, 350);
							setTimeout(function(){field.progressBar.css('width', '0')}, 550);
						} else {
							if (data.redirectToLogin) {
								window.location.replace(getCmsPrefix(true, true) + "login");
							} else {
								field.hiddenInput.val("");
								field.progressBar.css('width', '0');
								field.messageContainer.html(data.result.message).show();
							}
						}
					} else {
						field.progressBar.css('width', '0');
						field.messageContainer.html("An error has occurred. Please try again.").show();
					}
				},
				fail: function (e, data) {
					//console.error("fail");
					field.progressBar.css('width', '0');
					field.messageContainer.html("An error has occurred. Please try again.").show();
				},
				always: function (e, data) {
					//console.error("always");
					$linksEnabled = true;
				}
			}).on('fileuploadadd', function (e, data) {
				//console.error("file upload add");
				//console.error(data);
				if (customAutoUpload || (customAutoUpload !== false &&
					$(this).fileupload('option', 'autoUpload'))) {
					data.process().done(function () {
						if (imageUpload) {
							var acceptFileTypes = /^image\/(gif|jpe?g|png)$/i;
							if(data.originalFiles[0]['type'].length && !acceptFileTypes.test(data.originalFiles[0]['type'])) {
								//console.error("file type");
								field.messageContainer.html('Not an accepted file type').show();
							} else if(!data.originalFiles[0]['size'] || data.originalFiles[0]['size'] > 2000000) {
								//console.error("file size");
								field.messageContainer.html('Filesize is too big. Maximum filesize is 2 MB.').show();
							} else {
								//console.error("submitting");
								data.submit();
							}
						} else {
							data.submit();
						}
					});
				}
			}).on('fileuploadprocessalways', function (e, data) {
				//console.error("process always");
				if (data.imageUpload) {
					data.previewContainer.html(data.files[0].preview);
				}
			})

		});

	}

}

function setupRepositioning() {

	var currentIndex;
	var offsetTop;

	// --- REGULAR CONTAINER --- //
	var sortContainer = $("table.hasPositioning tbody");
	if (sortContainer.length) {

		sortContainer.each(function(i){
			if ($(this).data('sortable')) {
				$(this).sortable("destroy");
			}
		});

		sortContainer.each(function(i){
			var thisContainer = $(this);
			if ((thisContainer).hasClass('searchDataPresent')) {
				thisContainer.addClass('sortableDisabled')
			} else {
				thisContainer.removeClass('sortableDisabled');
			}
			thisContainer.sortable({
				handle: "td.sortHandle",
				items: "tr.isSortable",
				disabled: $(this).hasClass('searchDataPresent') ? true : false,
				start: function (event, ui) {
					disableUrlChange();
					currentIndex = ui.item.index();
					offsetTop = ui.originalPosition.top;
				},
				stop: function (event, ui) {
					if (ui.item.index() == currentIndex) {
						enableUrlChange();
						return false;
					}
					disableRepositioning();

					var belowItem;
					var aboveItem;
					var aboveItemId = false;
					var belowItemId = false;
					var direction;

					$(this).sortable("refreshPositions");

					if (ui.position.top < offsetTop) {
						direction = 'move-up';
					} else {
						direction = 'move-down';
					}

					if (direction == 'move-up') {
						var belowIndex = ui.item.index() + 1;
						belowItem = $(this).find("tr:eq(" + (ui.item.index() + 1) + ")");
						belowItemId = belowItem.attr('data-objectid');
					} else {
						aboveItem = $(this).find("tr:eq(" + (ui.item.index() - 1) + ")");
						aboveItemId = aboveItem.attr('data-objectid');
					}

					var modelName = ui.item.attr('data-modelname');

					$.ajax({
						url: getCmsPrefix(true, true) + modelName + "/ajaxMove",
						type: "GET",
						data: {
							objectId: ui.item.attr('data-objectid'),
							parentName: ui.item.attr('data-parentname'),
							aboveItemId: aboveItemId,
							belowItemId: belowItemId,
							direction: direction,
							treeStructure: false,
							getIgnore_isAjax: true
						},
						beforeSend: function(xhr){
							xhr.setRequestHeader('X-CSRF-TOKEN', $csrf);
						},
						success: function (data) {
							if (data.redirectToLogin) {
								window.location.replace(getCmsPrefix(true, true) + "login");
							} else {
								enableRepositioning();
							}
						},
						error: function (data) {

						},
						complete: function (data) {
							enableUrlChange();
						}
					});

				}
			});
		});
	}

	// --- TREE STRUCTURE CONTAINER --- //
	var treeSortContainer = $("table.hasTreeStructure tbody");
	var containerChanged = false;
	var emptySortContainers;
	if (treeSortContainer.length) {

		treeSortContainer.each(function(i){
			if ($(this).data('sortable')) {
				$(this).sortable("destroy");
			}
		});

		$('table.baseContainer tr').removeClass('sortableDisabled');

		var depth;
		var checkForPosition = true;
		var currentSortable;
		treeSortContainer.each(function(i){
			depth = parseInt($(this).parent('table.hasTreeStructure').attr('data-depth'));
			currentSortable = $(this);
			currentSortable.sortable({
				handle: "td.sortHandle",
				items: "tr.isSortable"+depth,
				placeholder: "sortablePlaceholder",
				forcePlaceholderSize: true,
				forceHelperSize: true,
				connectWith: ["table.sortContainer"+depth+" > tbody"],
				receive: function(event, ui) {
					containerChanged = true;
				},
				start: function (event, ui) {
					disableUrlChange();
					var depth = ui.item.attr('data-depth');
					emptySortContainers = $("table.hasTreeStructure tbody.depth"+depth+":not(:has(*))");
					emptySortContainers.addClass('openContainer');

					currentIndex = ui.item.index();
					offsetTop = ui.originalPosition.top;
					ui.item.css('opacity', '0.4').css('height', '40px').css('display', 'block');

					treeSortContainer.sortable('refresh');
					treeSortContainer.sortable('refreshPositions');
				},
				sort: function(event, ui) {
					if (checkForPosition) {
						if (Math.abs(parseInt(ui.position.top, 10) - parseInt(ui.originalPosition.top, 10)) > 10) {
							checkForPosition = false;
							setTimeout(function(){
								treeSortContainer.sortable('refresh');
								treeSortContainer.sortable('refreshPositions');
							}, 100);
						}
					}
				},
				stop: function (event, ui) {
					checkForPosition = true;
					ui.item.css('opacity', '1').removeAttr('style');
					emptySortContainers.removeClass('openContainer');

					if (ui.item.index() == currentIndex && !containerChanged) {
						//console.error("returning false");
						enableUrlChange();
						return false;
					}
					containerChanged = false;

					var objectId = ui.item.attr('data-objectid');
					var parent = ui.item.closest('tr.childTableContainer.isSortable:not(.objectId'+objectId+')');
					var parentId = parent.attr('data-objectid') || false;
					var position = parseInt(ui.item.index(), 10) + 1;
					var baseTable = ui.item.closest('table.baseContainer');
					var modelName = baseTable.attr('data-modelname');

					disableRepositioning();

					$.ajax({
						url: getCmsPrefix(true, true) + modelName + "/ajaxMove",
						type: "GET",
						data: {
							objectId: objectId,
							parentId: parentId,
							position: position,
							treeStructure: true,
							getIgnore_isAjax: true
						},
						beforeSend: function(xhr){
							xhr.setRequestHeader('X-CSRF-TOKEN', $csrf);
						},
						success: function (data) {
							if (data.redirectToLogin) {
								window.location.replace(getCmsPrefix(true, true) + "login");
							} else {
								enableRepositioning();
							}
						},
						error: function (jqXHR, error, errorThrown) {
							console.error("Status: " + jqXHR.status + "; Response text: " + jqXHR.responseText);
						},
						complete: function (data) {
							enableUrlChange();
						}
					});

				}
			});
		});

	}
}

function disableRepositioning() {
	var treeSortContainer = $("table.hasTreeStructure tbody");
	if (treeSortContainer.length) {
		treeSortContainer.sortable('disable');
		var baseTable = $('table.baseContainer');
		baseTable.find('tr').addClass('sortableDisabled');
	}

	var sortContainer = $("table.hasPositioning tbody");
	if (sortContainer.length) {
		sortContainer.sortable("disable").addClass('sortableDisabled');
	}
}

function enableRepositioning() {
	var treeSortContainer = $("table.hasTreeStructure tbody");
	if (treeSortContainer.length) {
		treeSortContainer.each(function(){
			$(this).sortable('enable');
		});
		var baseTable = $('table.baseContainer');
		baseTable.find('tr').removeClass('sortableDisabled');
	}

	var sortContainer = $("table.hasPositioning tbody");
	if (sortContainer.length) {
		sortContainer.each(function(){
			$(this).sortable("enable").removeClass('sortableDisabled');
		});
	}
}

function disableUrlChange() {
	$linksEnabled = false;
	$urlChangeThroughJs = true;
	$previousBackForthEnabled = $backForthEnabled;
	$backForthEnabled = false;
}

function enableUrlChange() {
	$linksEnabled = true;
	$urlChangeThroughJs = false;
	$backForthEnabled = $previousBackForthEnabled;
}

function getCurrentModel() {
	var cmsPrefix = getCmsPrefix(false, false, true);
	var subtract = cmsPrefix.length > 0 ? 0 : 1;

	var href = document.URL;
	var segments = href.split("?");
	segments = segments[0].split("/");

	return segments[4 - subtract];
}

function getCmsPrefix(prependSlash, appendSlash, returnEmptyIfNoPrefix) {
	var prefix = $cmsPrefix;

	if (returnEmptyIfNoPrefix && !prefix.length) {
		return "";
	}

	if (prependSlash) {
		prefix = "/" + prefix;
	}
	if (appendSlash) {
		prefix += "/";
	}
	prefix = prefix.replace("//", "/");

	return prefix;
}

function setIndexSearchFunctionality() {

	var viewContainer = $(".searchResultObjects");
	var objectsContainer = viewContainer.find('.objectsContainer');
	var forms = $("form.searchForm");

	// --- OPEN SEARCH BUTTON --- //
	var button = $("button.openSearch");
	var searchFormContainer = $(".searchContainer");

	button.off("click");
	button.on("click", function(){
		button.off("click");
		if (!button.hasClass('searchIsOpen')) {
			button.addClass('searchIsOpen');
			viewContainer.addClass('col-lg-9').removeClass('col-lg-12');
			searchFormContainer.removeClass('zeroWidth');
		} else {
			button.removeClass('searchIsOpen');
			viewContainer.addClass('col-lg-12').removeClass('col-lg-9');
			searchFormContainer.addClass('zeroWidth');
		}

		setTimeout(setIndexSearchFunctionality(), 300);
	});

	// --- CLEAR RESULTS HANDLER --- //
	var model = getCurrentModel();
	var clearButton = $("span.clearSearchResults");
	var searchIsOpen = "";
	if (button.hasClass('searchIsOpen')) {
		searchIsOpen = "&getIgnore_searchIsOpen=true";
	}
	var href = getCmsPrefix(true, true) + model + "?getIgnore_getSearchResults=true" + searchIsOpen;

	//objectsContainer is set up at the beginning of the function

	if (clearButton.length) {
		clearButton.off('click');
		clearButton.on("click", function(){
			$.ajax({
				type: "GET",
				url: href,
				data: {
					getIgnore_isAjax: true
				},
				beforeSend: function(xhr){
					xhr.setRequestHeader('X-CSRF-TOKEN', $csrf);
				},
				success: function (data) {
					//console.error(data);
					if (data.success) {
						if (data.setUrl) {
							History.pushState(null, $adminTitle, data.setUrl);
						} else {
							History.pushState(null, $adminTitle, href);
						}
						reloadSearchResults(objectsContainer, data);
						forms[0].reset();
						forms.find("input.dateTime").val("");
					} else {
						if (data.redirectToLogin) {
							window.location.replace(getCmsPrefix(true, true) + "login");
						} else {
							resetLinksAfterSearch();
							setSelectize();
							setIndexSelectAjaxUpdate();
						}
					}
				},
				error: function () {
					console.error("an error has occured");
					resetLinksAfterSearch();
					setSelectize();
					setIndexSelectAjaxUpdate();
				},
				complete: function () {

				}
			});
		});
	}

	// --- SEARCH FORM HANDLER --- //
	$linksEnabled = true;
	forms.off("submit");
	forms.on("submit", function(e) {
		e.preventDefault();
		if (!$linksEnabled) {
			return false;
		}

		var form = $(this);

		//objectsContainer is set up at the beginning of the function

		$linksEnabled = false;
		$urlChangeThroughJs = true;
		disableRepositioning();
		var errorMsg = $('div.formSubmitMessage span.errorMessage');
		var spinnerTarget = $('div.formSubmitMessage .formSpinner');

		var searchIsOpen = "";
		if (button.hasClass('searchIsOpen')) {
			searchIsOpen = "&getIgnore_searchIsOpen=true";
		}

		if (!spinnerTarget.find("div.spinner").length) {
			$searchSpinner = spin(spinnerTarget);
		}
		$searchSpinner.stop();

		if (errorMsg.is(":visible")) {
			errorMsg.fadeOut(100).promise().done(function(){
				spinnerTarget.fadeIn(250).css('display', 'inline-block');
				spin($formSpinner);
			});
		} else {
			spinnerTarget.fadeIn(250).css('display', 'inline-block');
		}

		var href = form.attr('action') || document.URL;
		href += "?getIgnore_getSearchResults=true"+searchIsOpen;

		$.ajax({
			type: "GET",
			url: href,
			data: form.serialize() + "&getIgnore_isAjax=true",
			beforeSend: function(xhr){
				xhr.setRequestHeader('X-CSRF-TOKEN', $csrf);
			},
			success: function (data) {
				if (data.success) {
					spinnerTarget.fadeOut(100).promise().done(function(){
						if (data.setUrl) {
							History.pushState(null, $adminTitle, data.setUrl);
						}
						$searchSpinner.stop();
						$("span.errorMsg").slideUp(100).remove();
						$("html, body").animate({ scrollTop: "0" }, 35).promise().done(function() {
							setTimeout(function(){
								reloadSearchResults(objectsContainer, data)
							}, 100);
						});
					});
				} else {
					if (data.redirectToLogin) {
						window.location.replace(getCmsPrefix(true, true) + "login");
					} else {
						spinnerTarget.fadeOut(100).promise().done(function(){
							$searchSpinner.stop();
							errorMsg.fadeIn(200);
							resetLinksAfterSearch();
							setSelectize();
							setIndexSelectAjaxUpdate();
						});
					}
				}
			},
			error: function () {
				spinnerTarget.fadeOut(100).promise().done(function(){
					$searchSpinner.stop();
					errorMsg.fadeIn(200);
					resetLinksAfterSearch();
					setSelectize();
					setIndexSelectAjaxUpdate();
				});
			},
			complete: function () {

			}
		});

	});
}

function resetLinksAfterSearch() {
	$linksEnabled = true;
	$urlChangeThroughJs = false;
	setLinkHandling();
	setIndexSearchFunctionality();
	setupRepositioning();
}

function reloadSearchResults(objectsContainer, data) {
	objectsContainer.animate({
		opacity: '0'
	}, 100).promise().done(function(){
		$(this).replaceWith(data.view);
		setSelectize();
		setIndexSelectAjaxUpdate();
		var newContainer = $('.objectsContainer');
		newContainer.animate({
			opacity: '1'
		}, 250).promise().done(function(){
			$(this).removeClass('hidden');

			if (!data.sideTablePagination) {
				var addUrl = getCmsPrefix(true, true) +  + data.entity + "/add" + data.getParams
				var excelExport = getCmsPrefix(true, true) + "excelExport/" + data.entity + data.getParams
				$("a.addButton").attr("href", addUrl);
				$("a.excelExport").attr("href", excelExport);
			}

			updatePaginationInfo(data);
			setupRepositioning();
			resetLinksAfterSearch();

		});
	});
}

function updatePaginationInfo(data) {
	var paginationFrom = $("span.paginationFrom");
	var paginationTo = $("span.paginationTo");
	var paginationTotal = $("span.paginationTotal");

	if (data.paginationFrom && paginationFrom.length) {
		paginationFrom.html(data.paginationFrom);
	}
	if (data.paginationTo && paginationTo.length) {
		paginationTo.html(data.paginationTo);
	}
	if (data.paginationTotal && paginationTotal.length) {
		paginationTotal.html(data.paginationTotal);
	}
}

function setupEditFormInputs() {
	if ($editFormInputs && $editFormInputs instanceof jQuery && $editFormInputs.length) {
		$editFormInputs.off('focus');
		$editFormInputs.off('blur');
	}
	$editFormInputs =
		$("form.entityForm.editForm div.form-group:not(.has-warning):not(.isSelect) input:not(.readOnly)[type!='checkbox'][type!='radio'], " +
			"form.entityForm.editForm div.form-group:not(.has-warning) textarea");
	setEditFormFunctionality();
}

function setEditFormFunctionality() {

	$editFormInputs.off('blur').off('focus');

	$editFormInputs.on('focus', function(){
		$(this).siblings('i.inputEdit').fadeOut(100);
	});

	$editFormInputs.on('blur', function(){
		$(this).siblings('i.inputEdit').fadeIn(100);
	});

	var editIcon = $("i.inputEdit");
	editIcon.off('click');
	editIcon.on("click", function(){
		$(this).siblings("input").focus();
		$(this).siblings("textarea").focus();
	});

	setDatePicker();
	setDateTimePicker();
}

function setBackAndForth() {
	var cmsPrefix = getCmsPrefix(false, false, true);
	var subtract = cmsPrefix.length > 0 ? 0 : 1;

	var href = document.URL;
	var segments = href.split("/");
	if (segments.length <= (5 - subtract)) {
		$backForthEnabled = false;
	} else {
		$backForthEnabled = true;
	}
}

function setLinkHandling() {
	var links = $("a:not(.standardLink):not([class*=phpdebugbar])");

	links.off("click");
	links.on("click", function(e) {
		e.preventDefault();

		var button = $(this);
		if (button.hasClass('deleteButton') && $linksEnabled) {
			$linksEnabled = false;
			$previousBackForthEnabled = $backForthEnabled;
			$backForthEnabled = false;
			disableRepositioning();
			handleDeleteLink(button);
			return false;
		}
		if (button.hasClass('navDisabled')) {
			return false;
		}
		if (button.hasClass('changeNavigationSize')) {
			toggleNavigationSize();
			return false;
		}
		if (getGtcmsPremium() && button.hasClass('quickEditButton')) {
			handleQuickEdit(button);
			return false;
		}
		if ($linksEnabled) {
			disableRepositioning();
			$linksEnabled = false;
			$urlChangeThroughJs = true;
			$('a.navigationLink').removeClass('navDisabled');

			var href = $(this).attr('href');
			var loadType = $(this).attr('data-loadtype') || 'moveLeft';
			if ($(this).closest('.paginationContainer').length) {
				loadType = 'fadeIn';
			}

			var objectsContainer = false;
			if (loadType == 'fadeIn') {
				objectsContainer = button.closest(".objectsContainer");
			}

			getAjaxContent(button, href, loadType, objectsContainer);

		} else {
			return false;
		}
	});
}

function toggleNavigationSize() {
	var body = $("body");

	if ($linksEnabled) {
		$linksEnabled = false;
		if (body.hasClass('nav-narrow')) {
			body.removeClass('nav-narrow');
			$.ajax({
				type: 'GET',
				url: getCmsPrefix(true, true) + 'setNavigationSize',
				data: {
					navigationSize: "wide",
					getIgnore_isAjax: true
				},
				beforeSend: function(xhr){
					xhr.setRequestHeader('X-CSRF-TOKEN', $csrf);
				},
				complete: function() {
					$linksEnabled = true;
				}
			});
		} else {
			body.addClass('nav-narrow');
			$.ajax({
				type: 'GET',
				url: getCmsPrefix(true, true) + 'setNavigationSize',
				data: {
					navigationSize: "narrow",
					getIgnore_isAjax: true
				},
				beforeSend: function(xhr){
					xhr.setRequestHeader('X-CSRF-TOKEN', $csrf);
				},
				complete: function() {
					$linksEnabled = true;
				}
			});
		}
	}
}

function handleDeleteLink(button) {
	var modal = $("div#modalDelete");
	var yesBtn = $("div#confirmButtons .btn-confirm");
	var noBtn = $("div#confirmButtons .btn-cancel");
	var url = button.attr("href");

	var modelName = button.attr('data-modelname');
	var objectName = button.attr('data-objectname');

	var objectData;
	if (objectName == 'image') {
		objectData = "this <strong>image</strong>?";
	} else {
		objectData = modelName+" <strong>" + htmlEntities(objectName) + "<span class='regular'>?</span></strong>";
	}

	$("div#confirmWindow span.objectData").html(objectData);

	var buttons = $('div#modalDelete div#confirmButtons');

	var width = $(window).outerWidth();
	var height = $(window).outerHeight();
	var scrollTop = $(document).scrollTop();

	if (width <= 768) {
		height++;
		scrollTop--;
	}

	$("body").addClass("staticBody").css('height', height+"px").css('margin-top', scrollTop+"px");

	modal.fadeIn(120);

	noBtn.off("click");
	noBtn.on("click", function(){
		if ($modalLinksEnabled) {
			$modalLinksEnabled = false;
			modal.fadeOut(150).promise().done(function(){
				$modalLinksEnabled = true;
				$linksEnabled = true;
				$backForthEnabled = $previousBackForthEnabled;
				$("body").removeClass("staticBody").css('height', "auto").css('margin-top', "0px");
			});
			enableRepositioning();
			setupRepositioning();
		}
	});

	yesBtn.off("click");
	yesBtn.on("click", function() {
		if ($modalLinksEnabled) {
			$modalLinksEnabled = false;

			var successCheckmark = $('div#modalDelete div#successCheckmark');
			var spinnerTarget = $('div#modalDelete div#confirmSpinner');
			var errorMsg = $('div#modalDelete div#errorMsg');

			if(!spinnerTarget.find("div.spinner").length) {
				$deleteSpinner = spin(spinnerTarget);
			}
			$deleteSpinner.stop();

			var animationSpeed = 150;
			var url = button.attr("href");

			if (button.hasClass('deleteUploadedFile')) {
				var parentContainer = button.parents("div.fileUploadContainer");
				url = url + "?fileNameValue=" + parentContainer.attr('data-filenamevalue');
			}

			if (button.hasClass('deleteImageFile')) {
				url = url + "&imageFile=true"
			}

			buttons.fadeOut(100).promise().done(function(){
				spin($deleteSpinner);
				spinnerTarget.fadeIn(250);
			});

			$.ajax({
				url: url,
				type: 'GET',
				data: {
					getIgnore_isAjax: true
				},
				beforeSend: function(xhr){
					xhr.setRequestHeader('X-CSRF-TOKEN', $csrf);
				},
				success: function(data) {
					//console.error(data);
					if (data && data.success) {
						spinnerTarget.fadeOut(100).promise().done(function(){
							$deleteSpinner.stop();
							successCheckmark.fadeIn(250).promise().done(function(){
								if (button.hasClass('deleteUploadedFile')) {
									modal.delay(500).fadeOut(150).promise().done(function() {
										var parentContainer = button.parents("div.fileUploadContainer");
										var uploadContainer = parentContainer.find('div.fileUploadForm');
										var downloadContainer = parentContainer.find('div.fileDownloadContainer');
										var hiddenInput = parentContainer.find('input[type="hidden"]');

										parentContainer.attr('data-filenamevalue', '');
										hiddenInput.val("");
										if (button.hasClass('deleteImageFile')) {
											uploadContainer.find('div.imagePreview').html("");
										}
										uploadContainer.removeClass('hidden');
										downloadContainer.addClass('hidden');
										resetModalDelete(successCheckmark, errorMsg, buttons);
									});
								} else {
									var parentTable = button.parents('table');
									var buttonParent = button.parent('td').parent('tr');
									var delRow;
									if (parentTable.hasClass('hasTreeStructure')) {
										//var rowContainer = $();
										//delRow = rowContainer.add(buttonParent);
										delRow = getChildRows(buttonParent);
									} else {
										delRow = buttonParent;
									}

									modal.delay(500).fadeOut(150).promise().done(function() {
										//remove table rows
										delRow.children('td, th')
											.animate({
												paddingTop: 0,
												paddingBottom: 0
											}, animationSpeed).wrapInner('<div />')
											.children()
											.slideUp(animationSpeed)
											.promise().done(function () {
												delRow.remove();
												resetModalDelete(successCheckmark, errorMsg, buttons);
											});
									});
								}
							});
						});
					} else {
						spinnerTarget.fadeOut(100).promise().done(function() {
							$deleteSpinner.stop();
							errorMsg.fadeIn(250).promise().done(function(){
								modal.delay(5000).fadeOut(150).promise().done(function() {
									resetModalDelete(successCheckmark, errorMsg, buttons);
								});
							});
						});
					}
				},
				error: function() {
					spinnerTarget.fadeOut(100).promise().done(function() {
						$deleteSpinner.stop();
						errorMsg.fadeIn(250).promise().done(function(){
							modal.delay(5000).fadeOut(150).promise().done(function() {
								resetModalDelete(successCheckmark, errorMsg, buttons);
							});
						});
					});
				},
				complete: function() {

				}
			});
		}
	});
}

function getChildRows(parentObject) {
	var objectId = parseInt(parentObject.attr('data-objectid'), 10);
	console.error(objectId, "object id");
	var container = $("tr.childTableContainer.objectId"+objectId);
	if (container.length) {
		console.error("container exists");
		return container;
	} else {
		console.error("returning original object");
		return parentObject;
	}
}

function resetModalDelete(successCheckmark, errorMsg, buttons) {
	successCheckmark.hide();
	errorMsg.hide();
	buttons.show();
	$modalLinksEnabled = true;
	$linksEnabled = true;
	$backForthEnabled = $previousBackForthEnabled;
	$("body").removeClass("staticBody").css('height', "auto").css('margin-top', "0px");
	enableRepositioning();
	setupRepositioning();
}

function getAjaxContent(button, href, loadType, objectsContainer) {
	var $wrapper = $("#page-wrapper");
	var $row = $wrapper.find("> .row");
	var $rowWidth = $row.outerWidth();

	$.ajax({
		url: href,
		data: {
			getIgnore_isAjax: true
		},
		type: "GET",
		beforeSend: function(xhr){
			xhr.setRequestHeader('X-CSRF-TOKEN', $csrf);
		},
		success: function(data) {
			if (data.success) {
				var historyLinks = "";
				if (data.history) {
					for (i in data.history) {
						historyLinks += getHistoryLink(data.history[i]);
					}
				}

				if (data.setUrl) {
					History.pushState(null, $adminTitle, data.setUrl);
				} else {
					History.pushState(null, $adminTitle, href);
				}

				if (loadType == 'fadeIn') {
					reloadSearchResults(objectsContainer, data);
				} else {
					$wrapper.append(data.view);

					setFormHandling();
					setSelectize();

					var $absRow = $wrapper.find('.absoluteRow');
					$absRow.addClass(loadType);
					$absRow.width($rowWidth + "px");
					$row.width($rowWidth + "px");
					if (data.setHistoryLinks) {
						$absRow.find("h3.page-header").prepend(historyLinks);
					}

					if (loadType == 'moveLeft') {
						$row.animate({
							'margin-left': '-' + ($rowWidth + 20)
						}, 250);

						$absRow.animate({
							'margin-left': '-15px'
						}, 250).promise().done(function () {
							$row.remove();
							resetLinksFunctionality($absRow, loadType, data)
						});
					} else if (loadType == 'moveRight') {
						$absRow.css('margin-left', '-' + ($rowWidth + 20) + 'px');

						$row.animate({
							'margin-left': '100%'
						}, 250);

						$absRow.animate({
							'margin-left': '-15px'
						}, 250).promise().done(function () {
							$row.remove();
							resetLinksFunctionality($absRow, loadType, data)
						});
					}
				}
			} else {
				if (data.redirectToLogin) {
					window.location.replace(getCmsPrefix(true, true) + "login");
				} else {
					$linksEnabled = true;
					$urlChangeThroughJs = false;
					enableRepositioning();
					displayMessage(data);
				}
			}

		},
		error: function(jqXHR, error, errorThrown) {
			console.error("Status: " + jqXHR.status + "; Response text: " + jqXHR.responseText);
			$linksEnabled = true;
		},
		complete: function() {
			if (button && button.hasClass('navigationLink')) {
				$("a.navigationLink").attr('data-loadtype', 'moveLeft');
			} else {
				$("a.navigationLink").attr('data-loadtype', 'moveRight');
			}
		}
	});
}

function resetLinksFunctionality(absRow, loadType, data) {
	absRow.removeClass('absoluteRow');
	absRow.removeClass(loadType);
	absRow.removeAttr('style');

	$('a.navigationLink').removeClass('active');
	if (data.modelConfigName) {
		$('a.navigationLink.model'+data.modelConfigName).addClass('active');
	}

	if (data.indexView) {
		$backForthEnabled = false;
	} else {
		$backForthEnabled = true;
	}

	$linksEnabled = true;
	$urlChangeThroughJs = false;

	resetApp();
}

function getHistoryLink(linkData) {
	var link = '<a data-loadtype="moveRight" href="'+linkData.link+'">'
		+ '<i class="fa '+linkData.modelIcon+'"></i> '+linkData.modelName
		+ ' </a><i class="fa fa-caret-right"></i>';
	return link;
}

function setBackAndForthHandling() {
	if (window.history && window.history.pushState ) {
		$(window).on('popstate', function() {
			if ($linksEnabled && $backForthEnabled) {
				var href = document.URL;
				$backForthEnabled = false;
				if ($setLinksToFalse) {
					$linksEnabled = false;
					$setLinksToFalse = false;
				}
				getAjaxContent(false, href, 'moveRight');
			} else if (!$backForthEnabled && !$urlChangeThroughJs) {
				$urlChangeThroughJs = true;
				$setUrlChangeThroughJsToFalse = true;
				window.history.forward();
			}

			if ($setUrlChangeThroughJsToFalse) {
				$setUrlChangeThroughJsToFalse = false;
				$urlChangeThroughJs = false;
			}
		});
	}
}

function setButtonTransition(button, transitionValue) {
	if (transitionValue == 'on') {
		button.css('transition', 'background, 0.25s, linear, 0s');
	} else if (transitionValue == 'off') {
		button.css('transition', 'background, 0s, linear, 0s');
	}
}

function loadLoginForm() {
	var outerFormContainer = $("div.login-panel");
	var formContainer = $("div.login-panel div.panel-body");
	if (formContainer.length) {
		var fcHeight = formContainer.outerHeight();
		var wHeight = $(window).height();

		if ((fcHeight + 400) > wHeight) {
			var marginTop = (wHeight - fcHeight)/2;
			outerFormContainer.css('margin-top', marginTop+"px");
		}

		var form = $("form.entityForm.loginForm");
		var loginLogo = $("img.login-logo");

		setTimeout(function () {
			form.animate({
				opacity: 1
			}, 500);

			if (loginLogo.length) {
				loginLogo.animate({
					opacity: 1
				}, 500);
			}
		}, 500);
	}
}

function setLoginHandling() {

	var loginForm = $("form.entityForm.loginForm");

	if (loginForm.length) {
		loginForm.off("submit");
		loginForm.on("submit", function (e) {
			e.preventDefault();
			var form = $(this);

			form.off("submit");

			var button = form.find("input.btn-block");
			var errorMsg = form.find(".errorMessage");
			var spinnerTarget = form.find(".loginSpinner");

			if (!spinnerTarget.find("div.spinner").length) {
				$loginSpinner = spin(spinnerTarget);
			}
			$loginSpinner.stop();

			setButtonTransition(button, 'off');
			button.fadeOut(250).promise().done(function () {
				spinnerTarget.fadeIn(250).css('display', 'inline-block');
				spin($loginSpinner);

				$.ajax({
					url: getCmsPrefix(true, true) + 'login',
					type: 'POST',
					data: form.serialize() + "&getIgnore_isAjax=true",
					beforeSend: function(xhr){
						xhr.setRequestHeader('X-CSRF-TOKEN', $csrf);
					},
					success: function (data) {
						if (data) {
							if (data.success) {
								$linksEnabled = false;
								$urlChangeThroughJs = true;
								$("div.container").fadeOut(250).promise().done(function () {
									$(this).remove();
									$("body").animate({
										backgroundColor: "#000"
									}, 250).promise().done(function () {
										$(this).removeClass("loginBody").addClass("loginRedirect").prepend(data.view);
										History.replaceState(null, $adminTitle, data.setUrl);
										setContentContainerSize();
										setupApp(true);

										$linksEnabled = true;
										$urlChangeThroughJs = false;

										$(this).animate({
											backgroundColor: "#f8f8f8"
										});

										$("body div#wrapper").animate({
											opacity: 1
										}, 250).promise().done(function () {
											$("body div#page-wrapper").animate({
												opacity: 1
											}, 250).promise().done(function () {
												$("body").removeClass('loginRedirect');
											});
										});
									});
								});
							} else {
								errorMsg.html(data.message);
								resetLoginForm(errorMsg, spinnerTarget, button);
							}
						} else {
							errorMsg.html($genericLoginError);
							resetLoginForm(errorMsg, spinnerTarget, button);
						}
					},
					error: function () {
						errorMsg.html($genericLoginError);
						resetLoginForm(errorMsg, spinnerTarget, button);
					},
					complete: function () {

					}
				});
			});

		});
	}
}

function resetLoginForm(errorMsg, spinnerTarget, button) {
	spinnerTarget.fadeOut(100).promise().done(function(){
		$loginSpinner.stop();
		errorMsg.fadeIn(250).promise().done(function(){
			errorMsg.delay(3500).fadeOut(250).promise().done(function(){
				button.fadeIn(250).promise().done(function(){
					setLoginHandling();
				})
			});
		});
	});
}

function setFormHandling() {
	$linksEnabled = true;
	var forms = $("form.entityForm:not(.loginForm)");
	forms.off("submit");
	forms.on("submit", function(e){
		e.preventDefault();
		if (!$linksEnabled) {
			return false;
		}

		var form = $(this);
		$linksEnabled = false;
		var errorMsg = form.find('div.formSubmitMessage span.errorMessage');
		var successCheckmark = form.find('div.formSubmitMessage i.fa');
		var spinnerTarget = form.find('.formSpinner');
		var tabs = form.find("ul.nav.nav-tabs > li");

		if(!spinnerTarget.find("div.spinner").length) {
			$formSpinner = spin(spinnerTarget);
		}
		$formSpinner.stop();

		if (errorMsg.is(":visible")) {
			errorMsg.fadeOut(100).promise().done(function(){
				spinnerTarget.fadeIn(250).css('display', 'inline-block');
				spin($formSpinner);
			});
			successCheckmark.fadeOut(100);
		} else if(successCheckmark.is(":visible")) {
			successCheckmark.fadeOut(100).promise().done(function(){
				spinnerTarget.fadeIn(250).css('display', 'inline-block');
				spin($formSpinner);
			});
			errorMsg.fadeOut(100);
		} else {
			spinnerTarget.fadeIn(250).css('display', 'inline-block');
		}

		//console.error("submitting form");

		var href = form.attr('action') || document.URL;

		$.ajax({
			type: "POST",
			url: href,
			data: form.serialize() + "&getIgnore_isAjax=true",
			beforeSend: function(xhr){
				xhr.setRequestHeader('X-CSRF-TOKEN', $csrf);
			},
			success: function (data) {
				if (data) {
					//console.error(data, "data");
					if (data.success) {
						//console.error("success");
						$("span.errorMsg").slideUp(100).remove();
						$("div.form-group").removeClass('has-warning');
						tabs.removeClass('has-warning');

						spinnerTarget.fadeOut(100).promise().done(function(){
							$formSpinner.stop();
							successCheckmark.fadeIn(250).promise().done(function(){
								if (data.returnToParent) {
									$("html, body").delay(500).animate({ scrollTop: "0" }, 35).promise().done(function(){
										$linksEnabled = true;
										$setLinksToFalse = true;
										History.back();
										setFormHandling();
									});
								} else {
									if (data.replaceUrl) {
										History.replaceState(null, $adminTitle, data.replaceUrl);
										form.attr('action', data.replaceUrl);
										var addRelatedHref;
										$("a.addRelatedObject").each(function(e){
											addRelatedHref = $(this).attr("href");
											$(this).attr("href", addRelatedHref.replace("new_gtcms_entry", data.objectId));
										});
										$("div.fileUploadContainer").each(function(e){
											$(this).attr("data-entityid", data.objectId);
										});
										$("a.deleteUploadedFile").each(function(e){
											addRelatedHref = $(this).attr("href");
											$(this).attr("href", addRelatedHref.replace("new_gtcms_entry", data.objectId));
										});
									}

									if (data.replaceCurrentHistory) {
										var historyContainer = $("h3.page-header");
										var newHistory = data.replaceCurrentHistory.modelName;
										if (htmlEntities(data.replaceCurrentHistory.objectName)) {
											newHistory += " <strong>" + htmlEntities(data.replaceCurrentHistory.objectName) + "</strong>";
										}
										historyContainer.find("span.currentHistory").fadeOut(100).promise().done(function(){
											$(this).html(newHistory);
											$(this).fadeIn(250);
										});
									}

									successCheckmark.delay(500).fadeOut(100);
									$("html, body").delay(500).animate({ scrollTop: "0" }, 250);

									if (form.hasClass('addForm')) {
										var disabledInputs = form.find("div.form-group.disabledInput");
										if (disabledInputs.length) {
											disabledInputs.slideDown(250).promise().done(function(){
												form.addClass('editForm').removeClass('addForm');
												$("div.disableRelatedModel").fadeOut(100);
											});
										} else {
											form.addClass('editForm').removeClass('addForm');
											$("div.disableRelatedModel").fadeOut(100);
										}
									}

									if (getGtcmsPremium() && data.quickEdit) {
										closeQuickEdit(false, data);
										setSelectize();
									} else {
										updatePaginationInfo(data);
										setupEditFormInputs();
										setFormHandling();
									}
								}
							});
						});
					} else {
						if (data.redirectToLogin) {
							window.location.replace(getCmsPrefix(true, true) + "login");
						}

						if (data.exception) {
							displayMessage(data.exception);
						}

						tabs.removeClass('has-warning');

						if (data.errors) {
							for (var fieldId in data.errors) {
								var formGroup = $("input#"+fieldId).closest("div.form-group");
								if (!formGroup.length) {
									formGroup = $("select#"+fieldId).closest("div.form-group");
								}
								if (!formGroup.length) {
									formGroup = $("textarea#"+fieldId).closest("div.form-group");
								}
								if (formGroup.length) {
									var tabPane = formGroup.closest("div.tab-pane");
									if (tabPane.length) {
										var tab = $('a[href="#' + (tabPane.attr('id')) + '"]').parents('li');
										tab.addClass('has-warning');
									}

									var infoClass = "";
									var horizontalForm = form.hasClass('form-horizontal');
									if (horizontalForm) {
										var infoOffset = form.attr('data-infooffset');
										var infoWidth = form.attr('data-infowidth');
										infoClass = "col-sm-offset-" + infoOffset + " col-sm-" + infoWidth;
									}

									formGroup.addClass('has-warning hasNewWarning');
									formGroup.find("span.errorMsg").remove();
									var fieldErrors = data.errors[fieldId];
									fieldErrors.forEach(function (errMsg) {
										if (formGroup.find("label") && !horizontalForm) {
											formGroup.find("label").after("<span class='info errorMsg'>" + errMsg + "</span>");
										} else {
											formGroup.prepend("<span class='info errorMsg " + infoClass + "'>" + errMsg + "</span>");
										}
									});
								} else {
									console.error("Cannot find FormGroup element for ID "+fieldId);
								}
							}
						}

						var oldWarningFields = $("div.form-group.has-warning:not(.hasNewWarning)");
						if (oldWarningFields.length) {
							oldWarningFields.find("span.errorMsg").slideUp(90).promise().done(function(){
								//console.error("removing old spans");
								$(this).remove();
								oldWarningFields.removeClass('has-warning');
								$("div.form-group").removeClass('hasNewWarning');
							});
						} else {
							$("div.form-group").removeClass('hasNewWarning');
						}

						var returnMsg = data.errorMsg || "An error has occurred. Please try again.";
						spinnerTarget.fadeOut(100).promise().done(function(){
							$formSpinner.stop();
							errorMsg.html(returnMsg).fadeIn(250).promise().done(function(){
								var offsetTop = 0;
								var errorSpan = $("span.errorMsg");
								if (errorSpan.length) {
									offsetTop = parseInt(errorSpan.eq(0).offset().top, 10) - 25;
								}

								var scrollSelector;
								if (data.quickEdit) {
									scrollSelector = $(".quickEditContainer");
								} else {
									scrollSelector = $("html, body");
								}

								scrollSelector.delay(1000).animate({ scrollTop: offsetTop }, 250);
								setFormHandling();
								setupEditFormInputs();
							});
						});

					}
				} else {
					console.error("no data!");
					var returnMsg = "An error has occurred. Please try again.";
					spinnerTarget.fadeOut(100).promise().done(function(){
						$formSpinner.stop();
						errorMsg.html(returnMsg).fadeIn(250);
						setFormHandling();
						setupEditFormInputs();
					});
				}
			},
			error: function(jqXHR, error, errorThrown) {
				console.error("Status: " + jqXHR.status + "; Response text: " + jqXHR.responseText);
				var returnMsg = "An error has occurred. Please try again.";
				spinnerTarget.fadeOut(100).promise().done(function(){
					$formSpinner.stop();
					errorMsg.html(returnMsg).fadeIn(250);
					setFormHandling();
				});
			},
			complete: function() {

			}
		});
	});
}

function htmlEntities(str) {
	if (str) {
		return str.replace(/[\u00A0-\u9999<>\&]/gim, function (i) {
			return '&#' + i.charCodeAt(0) + ';';
		});
	}

	return str;
}

function spin(target) {
	var opts = {
		lines: 11, // The number of lines to draw
		length: 8, // The length of each line
		width: 3, // The line thickness
		radius: 4, // The radius of the inner circle
		corners: 0.7, // Corner roundness (0..1)
		rotate: 21, // The rotation offset
		direction: 1, // 1: clockwise, -1: counterclockwise
		color: '#000', // #rgb or #rrggbb or array of colors
		speed: 1, // Rounds per second
		trail: 32, // Afterglow percentage
		shadow: false, // Whether to render a shadow
		hwaccel: false, // Whether to use hardware acceleration
		className: 'spinner', // The CSS class to assign to the spinner
		zIndex: 2e9, // The z-index (defaults to 2000000000)
		top: '50%', // Top position relative to parent
		left: '50%' // Left position relative to parent
	};

	var loginForm = target.parents('form.loginForm');
	if (loginForm.length && $('body').hasClass('skin-dark')) {
		opts.color = '#f6f6f6';
	}

	target.spin(opts);
	return target;
}

function setDateTimePicker() {
	var objToday = new Date();
	var inputDateTime = $('input.dateTimePicker');
	if (inputDateTime.length > 0) {
		inputDateTime.each(function(i){
			if ($(this).hasClass('hasDatepicker')) {
				$(this).datepicker("destroy");
			}
		});
		inputDateTime.datetimepicker ({
			dateFormat : 'dd.mm.yy.',
			timeFormat : 'HH:mm',
			hour : objToday.getHours (),
			minute : objToday.getMinutes (),
			second : objToday.getSeconds ()
		});
	}
}

function setDatePicker() {
	var inputDateTime = $('input.datePicker');
	if (inputDateTime.length > 0) {
		inputDateTime.each(function(i){
			if ($(this).hasClass('hasDatepicker')) {
				$(this).datepicker("destroy");
			}
		});
		inputDateTime.datepicker ({
			dateFormat : 'dd.mm.yy.'
		});
	}
}

function setEditors() {
	var editors = $("textarea.editor");
	var editorObjects = [];

	if (editors.length) {
		if (typeof(CKEDITOR) === "undefined") {
			var editorScripts =
				"<script src='/components/ckeditor/ckeditor.js'></script>" +
				"<script src='/components/ckeditor/adapters/jquery.js'></script>";

			$("head").append(editorScripts);
		}

		if (!$editorPluginsAdded) {
			$editorPluginsAdded = true;
			CKEDITOR.plugins.addExternal('webkit-span-fix', '/gtcms/ckeditor/webkit-span-fix/', 'plugin.js');
		}

		editors.each(function(i) {
			var toolbarOptions = $(this).attr('data-editortoolbar') ? $(this).attr('data-editortoolbar').split("|") : [];

			var toolbar = [
				[ 'Undo' ]
			];

			var styleSet = [];
			var hasStyles = false;

			// Toolbar Options

			if ($.inArray('bold-italic', toolbarOptions) != -1) {
				toolbar.push(['Bold', 'Italic']);
			}

			if ($.inArray('bullet-list', toolbarOptions) != -1) {
				toolbar.push(['BulletedList']);
			}

			if ($.inArray('justify', toolbarOptions) != -1) {
				toolbar.push(['JustifyLeft', 'JustifyBlock']);
			}

			if (($.inArray('link', toolbarOptions) != -1) || ($.inArray('file-upload', toolbarOptions) != -1)) {
				toolbar.push(['Link', 'Unlink']);
			}

			if ($.inArray('image', toolbarOptions) != -1) {
				toolbar.push(['Image']);
			}

			// Options in Styles dropdown

			if ($.inArray('h1', toolbarOptions) != -1) {
				styleSet.push({ name: 'H1', element: 'h1', attributes: {}, styles: {} });
				hasStyles = true;
			}

			if ($.inArray('h2', toolbarOptions) != -1) {
				styleSet.push({ name: 'H2', element: 'h2', attributes: {}, styles: {} });
				hasStyles = true;
			}

			if ($.inArray('h3', toolbarOptions) != -1) {
				styleSet.push({ name: 'H3', element: 'h3', attributes: {}, styles: {} });
				hasStyles = true;
			}

			if ($.inArray('h4', toolbarOptions) != -1) {
				styleSet.push({ name: 'H4', element: 'h4', attributes: {}, styles: {} });
				hasStyles = true;
			}

			if ($.inArray('h5', toolbarOptions) != -1) {
				styleSet.push({ name: 'H5', element: 'h5', attributes: {}, styles: {} });
				hasStyles = true;
			}

			if (hasStyles) {
				toolbar.push(['Styles']);
			}

			// CKE Config

			var ckeConfig = {
				toolbar: toolbar,
				stylesSet: styleSet,
				width: "100%",
				resize_dir: 'vertical',
				tabSpaces: 4,
				skin: "gtcms,/gtcms/ckeditor/gtcms-skin/gtcms/",
				extraPlugins: 'justify,stylesheetparser,webkit-span-fix,autogrow',
				autoGrow_minHeight: 130,
				autoGrow_maxHeight: 600,
				autoGrow_bottomSpace: 20,
				autoGrow_onStartup: true,
				contentsCss: '/gtcms/css/gtcms-ckeditor.css?v=1.1',
				entities_latin: false,
				forcePasteAsPlainText: true,

				on: {
					focus: function() {
						editors.eq(i).siblings('i.fa').fadeOut(100);
						var editorHtml = $(editorObjects[i].editor.container.$);
						var contentHtml = $(editorObjects[i].editor.editable().$);
						editorHtml.find("span.cke_top").addClass('hasHeight');
						editorHtml.find("span.cke_bottom").addClass('hasHeight');
						contentHtml.addClass('editMode');
					},
					blur: function() {
						editors.eq(i).siblings('i.fa').fadeIn(100);
						var editorHtml = $(editorObjects[i].editor.container.$);
						var contentHtml = $(editorObjects[i].editor.editable().$);
						editorHtml.find("span.cke_top").removeClass('hasHeight');
						editorHtml.find("span.cke_bottom").removeClass('hasHeight');
						contentHtml.removeClass('editMode');
					}
				}
			};

			if ($.inArray('file-upload', toolbarOptions) != -1) {
				ckeConfig.filebrowserImageBrowseUrl = '/laravel-filemanager?type=Images';
				ckeConfig.filebrowserImageUploadUrl = '/laravel-filemanager/upload?type=Images&_token=' + $csrf;
				ckeConfig.filebrowserBrowseUrl = '/laravel-filemanager?type=Files';
				ckeConfig.filebrowserUploadUrl = '/laravel-filemanager/upload?type=Files&_token=' + $csrf;
			}

			editorObjects[i] = $(this).ckeditor(ckeConfig);
		});
	}
}

function setSelectize() {
	var $selects = $("select.doSelectize");
	var jqSelects = [];
	if ($selects.length) {
		var $cSelect;
		var $isSelectized;
		var $pluginsArray;

		$selects.each(function(i) {
			$cSelect = $(this);
			$isSelectized = $cSelect.next("div.selectize-control");
			if (!$isSelectized.length) {
				jqSelects[i] = $(this);

				$pluginsArray = [];
				if (!jqSelects[i].hasClass('selectablePlaceholder')) {
					$pluginsArray.push('remove_button');
				}
				if (getGtcmsPremium()) {
					$pluginsArray.push('drag_drop');
				}

				var $select = $cSelect.selectize({
					plugins: $pluginsArray,
					delimiter: ',',
					persist: false,
					createOnBlur: true,
					allowEmptyOption: jqSelects[i].hasClass('selectablePlaceholder'),
					create: !$cSelect.hasClass('selectizeCreate') || !getGtcmsPremium() ? false : function(input) {
						return {
							value: input + "_gtcms_selectizejs_newitem",
							text: input
						}
					},
					createFilter: function(input) {
						return input.length <= 50;
					},
					load: function(query, callback) {
						if (!jqSelects[i].hasClass('ajax') || !getGtcmsPremium()) {
							return null;
						} else {
							selectizeAjaxLoad(query, callback, jqSelects[i]);
						}
					}
				});

				var control = $select[0].selectize;
				control.removeOption('gtcms_load_default');
			}
		});

		$("tr.rowSelectize").removeClass('rowSelectize');
	}

}
