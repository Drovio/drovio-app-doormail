<?php
//#section#[header]
// Use Important Headers
use \API\Platform\importer;
use \API\Platform\engine;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");

// Import DOM, HTML
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");

use \UI\Html\DOM;
use \UI\Html\HTML;

// Import application for initialization
importer::import("AEL", "Platform", "application");
use \AEL\Platform\application;

// Increase application's view loading depth
application::incLoadingDepth();

// Set Application ID
$appID = 91;

// Init Application and Application literal
application::init(91);
// Secure Importer
importer::secure(TRUE);

// Import SDK Packages
importer::import("UI", "Apps");
importer::import("UI", "Developer");
importer::import("UI", "Forms");
importer::import("UI", "Navigation");
importer::import("UI", "Presentation");

// Import APP Packages
application::import("Mail");
//#section_end#
//#section#[view]
use \APP\Mail\signature;
use \UI\Apps\APPContent;
use \UI\Forms\templates\simpleForm;
use \UI\Developer\editors\HTML5Editor;
use \UI\Navigation\navigationBar;
use \UI\Presentation\notification;
use \UI\Presentation\popups\popup;

// Create Application Content
$appContent = new APPContent();
$actionFactory = $appContent->getActionFactory();

if (engine::isPost())
{
	// Get signature to update
	$status = signature::set($_POST['signature']);
	
	// Build Notification
	$reportNtf = new notification();
	if ($status === TRUE)
	{
		$reportNtf->build($type = notification::SUCCESS, $header = FALSE, $timeout = FALSE, $disposable = FALSE);
		$reportMessage = $reportNtf->getMessage("success", "success.save_success");
	}
	else if ($status === FALSE)
	{
		$reportNtf->build($type = notification::ERROR, $header = FALSE, $timeout = FALSE, $disposable = FALSE);
		$reportMessage = $reportNtf->getMessage("error", "err.save_error");
	}
	
	$reportNtf->append($reportMessage);
	$notification = $reportNtf->get();
	
	// Create popup
	$pp = new popup();
	$pp->fade(TRUE);
	$pp->timeout(TRUE);
	$pp->build($notification);
	return $pp->getReport();
}

// Build the application view content
$appContent->build("", "signatureEditorContainer");

// Create form
$form = new simpleForm();
$signForm = $form->build("", FALSE)->engageApp("enterprise/settings/signEditor")->get();
$appContent->append($signForm);

// Create signature editor container
$signatureEditor = DOM::create("div", "", "", "signatureEditor");
$form->append($signatureEditor);

$codeMgrToolbar = new navigationBar();
// Create Source Code Manager Toolbar
$codeMgrToolbar->build($dock = navigationBar::TOP, $signatureEditor);
DOM::append($signatureEditor, $codeMgrToolbar->get());

// Save Tool
$saveTool = DOM::create("button", "", "", "objTool save");
DOM::attr($saveTool, "type", "submit");
$codeMgrToolbar->insertToolbarItem($saveTool);

// Create html editor
$editor = new HTML5Editor("signature", $enablePreview = TRUE);
$signEditor = $editor->build(signature::get(FALSE), $id = "", $class = "sign_editor")->get();
DOM::append($signatureEditor, $signEditor);

// Return output
return $appContent->getReport();
//#section_end#
?>