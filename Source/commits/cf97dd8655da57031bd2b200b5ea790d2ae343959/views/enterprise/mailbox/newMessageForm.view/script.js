var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	// Load application containers
	jq(document).on("click", ".newMessageForm .btn-ctrl.cancel", function() {
		// Go back to mailbox
		showMailboxList();
	});
	
	jq(document).on("mailbox.message_sent", function(ev, messageTitle) {
		// Show notification
		pageNotification.show(jq(".newMessageForm"), ntf_id = "doorMail_message_send", messageTitle, null, null, null, true);
	});
});