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
	
	
});

function showMailboxList() {
	// Remove new indicator for mailbox
	jq(".mailboxList").removeClass("new");
}