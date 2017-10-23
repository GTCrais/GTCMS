
$gtcmsPremium = true;

function selectizeAjaxLoad(query, callback, jqSelect) {
	if ($.trim(query).length < 3) return callback();
	var model = jqSelect.attr('data-model');
	var searchFields = jqSelect.attr('data-searchfields');
	var value = jqSelect.attr('data-value');
	var text = jqSelect.attr('data-text');
	var url = getCmsPrefix(true, true) + model + "/ajaxSearch";

	$.ajax({
		url: url,
		type: 'GET',
		dataType: 'json',
		data: {
			searchFields: searchFields,
			value: value,
			text: text,
			query: query
		},
		beforeSend: function(xhr){
			xhr.setRequestHeader('X-CSRF-TOKEN', $csrf);
		},
		error: function() {
			callback();
		},
		success: function(data) {
			if (data && data.success && data.items) {
				callback(data.items);
			} else {
				callback();
			}
		}
	});
}

function positionQuickEdit() {
	var container = $(".quickEditContainer");
	if (container.length) {
		container.css('right', '-' + (container.outerWidth() + 10) + 'px');
	}
}

function handleQuickEdit(button) {
	var container = $(".quickEditContainer");
	if (container.length) {
		if (button.hasClass('close')) {
			closeQuickEdit(container);
		} else if ($linksEnabled && !container.hasClass('open')) {
			disableRepositioning();
			$linksEnabled = false;

			container.addClass('open');

			$.ajax({
				type: 'get',
				url: button.attr('href'),
				data: {
					getIgnore_quickEdit: true,
					getIgnore_isAjax: true
				},
				beforeSend: function(xhr){
					xhr.setRequestHeader('X-CSRF-TOKEN', $csrf);
				},
				success: function(data) {
					if (data && data.success) {
						container.html(data.view);
						setFormHandling();
						setSelectize();
						container.animate({
							right: '0'
						}, 70, function(){
							resetApp(true);
						});
					} else {
						if (data.redirectToLogin) {
							window.location.replace(getCmsPrefix(true, true) + "login");
						} else {
							container.removeClass('open');
							displayMessage(data);
							enableRepositioning();
							$linksEnabled = true;
						}
					}
				},
				error: function() {
					container.removeClass('open');
					enableRepositioning();
					$linksEnabled = true;
				},
				complete: function() {

				}
			});
		}
	}
}

function closeQuickEdit(container, data) {
	if (!container) {
		container = $(".quickEditContainer");
	}

	if (container.length) {
		container.animate({
			right: "-" + (container.outerWidth() + 10) + "px"
		}, 70, function () {
			resetApp(true);
			container.html("");
			container.removeClass("open");
			$linksEnabled = true;
		});

		if (data && data.objectId) {
			var objectRow;
			if (data.objectRowHtml) {
				objectRow = $("tr[data-objectid=" + data.objectId + "][data-modelname=" + data.modelName + "]:not(.childTableContainer)");
				if (objectRow.length) {
					objectRow.replaceWith(data.objectRowHtml);
				}
			} else if (data.printPropertyValue) {
				objectRow = $("tr[data-objectid=" + data.objectId + "]:not(.childTableContainer)");
				objectRow.find("a.printPropertyValue").html(data.printPropertyValue);
			}
		}
	}
}
