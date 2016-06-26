
$(document).ready(function(){
	handleContactForm();
});

function handleContactForm() {

	if(typeof $.fn.validate !== 'undefined'){

		$('#contact-form').validate({
			errorClass: 'validation-error', // so that it doesn't conflict with the error class of alert boxes
			errorPlacement: function(error, element) {
				error.prependTo( element.parents('p') );
			},
			rules: {
				name: {
					required: true
				},
				email: {
					required: true,
					email: true
				},
				subject: {
					required: true
				},
				message: {
					required: true
				}
			},
			messages: {
				name: {
					required: "Field is required!"
				},
				email: {
					required: "Field is required!",
					email: "Please enter a valid email address"
				},
				subject: {
					required: "Field is required!"
				},
				message: {
					required: "Field is required!"
				}
			},
			submitHandler: function(form) {
				var result;
				if ($(form).hasClass('submitting')) {
					return;
				}
				$(form).addClass('submitting');

				$(form).ajaxSubmit({
					type: "POST",
					url: "/send-message",
					data: {
						name: $("input#name").val(),
						email: $("input#email").val(),
						subject: $("input#subject").val(),
						message: $("input#message").val(),
						getIgnore_isAjax: true
					},

					success: function(data) {
						if (data) {
							result = '<div class="alert success"><i class="fa fa-check-circle-o"></i>' + (data.message) + '</div>';
							if (data.success) {
								$('#contact-form').clearForm();
							}
						} else {
							formMessage.html("An error has occurred.<br>Please refresh the page and try again.");
							$(form).removeClass('submitting');
						}
						$("#formstatus").html(result);
						$(form).removeClass('submitting');
					},
					error: function() {
						result = '<div class="alert error"><i class="fa fa-times-circle"></i>There was an error sending the message!</div>';
						$("#formstatus").html(result);
						$(form).removeClass('submitting');
					}
				});
			}
		});

	}

}

String.prototype.replaceAll = function(search, replacement) {
	var target = this;
	return target.split(search).join(replacement);
};