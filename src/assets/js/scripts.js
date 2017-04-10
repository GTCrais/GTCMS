
$(document).ready(function(){
	handleContactForm();
});

function handleContactForm() {
	var form = $("form#contact-form");

	if (form.length) {
		form.on("submit", function(e){
			e.preventDefault();
			if (form.hasClass("disabled")) {
				return;
			}
			form.addClass("disabled");

			var button = $("input#submit");
			button.addClass('disabled');

			var name = $.trim($("input#name").val());
			var email = $.trim($("input#email").val());
			var subject = $.trim($("input#subject").val());
			var message = $.trim($("textarea#contact-message").val());
			var token = $('input[name="_token"]').val();

			var errors = $("p.contact-error");
			var nameErr = $("p.name-error");
			var emailErr = $("p.email-error");
			var subjectErr = $("p.subject-error");
			var messageErr = $("p.message-error");
			var formMessage = $("div.form-message");

			var emptyField = nameErr.attr('data-emptyfield');
			var incorrectEmailFormat = emailErr.attr('data-incorrectemailformat');

			var valid = true;
			errors.html("").hide();
			formMessage.html("").removeClass('is-error').hide();

			if (name == "") {
				nameErr.html(emptyField).show();
				valid = false;
			}

			if (email == "") {
				emailErr.html(emptyField).show();
				valid = false;
			} else if (!validateEmail(email)) {
				emailErr.html(incorrectEmailFormat).show();
				valid = false;
			}

			if (subject == "") {
				subjectErr.html(emptyField).show();
				valid = false;
			}

			if (message == "") {
				messageErr.html(emptyField).show();
				valid = false;
			}

			if (valid) {
				$.ajax({
					type: 'POST',
					url: form.attr('action'),
					data: {
						name: name,
						email: email,
						subject: subject,
						message: message,
						_token: token,
						getIgnore_isAjax: true
					},
					success: function(data) {
						if (data) {
							if (data.success) {
								button.remove();
							} else {
								formMessage.addClass('is-error');
							}
							formMessage.html(data.message).show();
						} else {
							formMessage.addClass('is-error').html("An error has occurred.<br>Please refresh the page and try again.").show();
						}
					},
					error: function() {
						formMessage.addClass('is-error').html("An error has occurred.<br>Please refresh the page and try again.").show();
					},
					complete: function() {
						$("input#name").val("");
						$("input#email").val("");
						$("input#subject").val("");
						$("textarea#contact-message").val("");
						form.removeClass("disabled");
						button.removeClass('disabled');
					}
				});
			} else {
				form.removeClass("disabled");
				button.removeClass('disabled');
			}

		});
	}
}

String.prototype.replaceAll = function(search, replacement) {
	var target = this;
	return target.split(search).join(replacement);
};

function validateEmail(email) {
	var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return re.test(email);
}