
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

			var button = $("button#submit");

			var name = $.trim($("input#name").val());
			var email = $.trim($("input#email").val());
			var subject = $.trim($("input#subject").val());
			var message = $.trim($("textarea#contact-message").val());
			var token = $('input[name="_token"]').val();

			var errors = $("span.contact-error");
			var nameErr = $("span.name-error");
			var emailErr = $("span.email-error");
			var subjectErr = $("span.subject-error");
			var messageErr = $("span.message-error");
			var formMessage = $("div.form-message");

			var emptyField = nameErr.attr('data-emptyfield');
			var incorrectEmailFormat = emailErr.attr('data-incorrectemailformat');

			var valid = true;
			errors.each(function(){
				$(this).html("");
			});
			formMessage.html("");

			if (name == "") {
				nameErr.html(emptyField);
				valid = false;
			}

			if (email == "") {
				emailErr.html(emptyField);
				valid = false;
			} else if (!validateEmail(email)) {
				emailErr.html(incorrectEmailFormat);
				valid = false;
			}

			if (subject == "") {
				subjectErr.html(emptyField);
				valid = false;
			}

			if (message == "") {
				messageErr.html(emptyField);
				valid = false;
			}

			if (valid) {
				$.ajax({
					type: 'POST',
					url: '/send-message',
					data: {
						name: name,
						email: email,
						subject: subject,
						message: message,
						_token: token
					},
					success: function(data) {
						if (data) {
							formMessage.html(data.message);
							if (data.success) {
								button.remove();
							}
						} else {
							formMessage.html("An error has occurred.<br>Please refresh the page and try again.")
						}
					},
					error: function() {
						formMessage.html("An error has occurred.<br>Please refresh the page and try again.")
					},
					complete: function() {
						$("input#name").val("");
						$("input#email").val("");
						$("input#subject").val("");
						$("textarea#contact-message").val("");
						form.removeClass("disabled");
					}
				});
			} else {
				form.removeClass("disabled");
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