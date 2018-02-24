var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	// Load application containers
	jq(document).on("click", ".newMessageForm .btn-ctrl.cancel", function() {
		// Check cancel radio button
		jq(".newMessageForm .frm-mail input[name='cancel_message']").val(1);
		
		// Submit form
		jq(".newMessageForm .frm-mail").trigger("submit");
	});
	
	jq(document).on("mailbox.message_sent", function(ev, messageTitle) {
		// Show notification
		pageNotification.show(jq(".newMessageForm"), ntf_id = "doorMail_message_send", messageTitle, null, null, null, true);
	});
	
	// Toggle recipients
	jq(document).on("click", ".newMessageForm .frm-mail .recipients .toggle_button", function() {
		// Toggle open class
		jq(this).closest(".recipients").toggleClass("open");
		
		// Toggle recipients height
		jq(this).closest(".recipients").find(".rcc").animate({
			height: "toggle"
		}, 200);
		
		// Alter farea
		var fTop = 124;
		if (jq(this).closest(".recipients").hasClass("open"))
			fTop = 186;
		jq(".newMessageForm .frm-mail .farea").animate({
			top: fTop + "px"
		}, 200);
	});
});