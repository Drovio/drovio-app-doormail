var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	// Refresh template manager
	jq(document).on("templates.refresh", function() {
		jq(".templateManager").trigger("reload");
	});
	
	// Refresh template list
	jq(document).on("templates.list.refresh", function() {
		jq("#templateListViewContainer").trigger("reload");
	});
});