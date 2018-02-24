var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	// Switch to new mail
	jq(document).on("mailbox.new", function() {
		// Add new indicator for mailbox
		jq(".mailboxList").addClass("new");
	});
	
	// Switch to mail list
	jq(document).on("mailbox.showlist", function() {
		showMailboxList();
	});
	
	// View mail body
	jq(document).on("click", ".listItem .lhd", function() {
		jq(this).closest(".listItem").find(".mail_body").animate({
			height: "toggle"
		}, 200);
	});
	
	// On resume stop bubbling
	jq(document).on("click", ".listItem .lhd .info.draft", function(ev) {
		ev.stopPropagation();
	});
	
	// On delete stop bubbling
	jq(document).on("click", ".delete_mail_form .btn_delete_mail", function(ev) {
		ev.stopPropagation();
	});
});

function showMailboxList() {
	// Remove new indicator for mailbox
	jq(".mailboxList").removeClass("new").trigger("reload");
}