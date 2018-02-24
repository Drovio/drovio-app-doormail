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
use \APP\Mail\templates;
use \UI\Apps\APPContent;
use \UI\Forms\templates\simpleForm;
use \UI\Developer\editors\HTML5Editor;
use \UI\Navigation\navigationBar;
use \UI\Presentation\notification;
use \UI\Presentation\popups\popup;

// Create Application Content
$appContent = new APPContent();
$actionFactory = $appContent->getActionFactory();

$templateID = engine::getVar("tid");
$loadTemplate = engine::getVar("load");
if (engine::isPost() && empty($loadTemplate))
{
	if ($_POST['tdelete'])
		$status = templates::remove($templateID);
	else
		$status = templates::update($templateID, $_POST['tname'], $_POST['template']);
	
	// Create popup
	$pp = new popup();
	$pp->fade(TRUE);
	$pp->timeout(TRUE);
	
	// Build Notification
	$reportNtf = new notification();
	if ($status === TRUE)
	{
		$reportNtf->build($type = notification::SUCCESS, $header = FALSE, $timeout = FALSE, $disposable = FALSE);
		$reportMessage = $reportNtf->getMessage("success", "success.save_success");
		
		// Add action to refresh the list
		if ($_POST['tdelete'])
			$pp->addReportAction($name = "templates.refresh");
		else
			$pp->addReportAction($name = "templates.list.refresh");
	}
	else if ($status === FALSE)
	{
		$reportNtf->build($type = notification::ERROR, $header = FALSE, $timeout = FALSE, $disposable = FALSE);
		$reportMessage = $reportNtf->getMessage("error", "err.save_error");
	}
	
	$reportNtf->append($reportMessage);
	$pp->build($reportNtf->get());
	
	return $pp->getReport();
}
if (empty($templateID))
{
	// Create new template
	$templateID = templates::create("New Template");
}

// Build the application view content
$appContent->build("", "templateEditorContainer");

// Get template info
$allTemplates = templates::getTemplates();
$templateName = $allTemplates[$templateID];

// Create form
$form = new simpleForm();
$signForm = $form->build("", FALSE)->engageApp("enterprise/templates/templateEditor")->get();
$appContent->append($signForm);

$input = $form->getInput($type = "hidden", $name = "tid", $value = $templateID, $class = "", $autofocus = FALSE, $required = TRUE);
$form->append($input);

// Build a form row with label and input
$input = $form->getInput($type = "text", $name = "tname", $value = $templateName, $class = "", $autofocus = FALSE, $required = TRUE);
$frow = $form->buildRow("Template Name", $input, $required = TRUE, $notes = "");
$form->append($frow);

$input = $form->getInput($type = "checkbox", $name = "tdelete", $value = $templateName, $class = "", $autofocus = FALSE, $required = FALSE);
$frow = $form->buildRow("Delete Template?", $input, $required = FALSE, $notes = "");
$form->append($frow);

// Create signature editor container
$signatureEditor = DOM::create("div", "", "", "templateEditor");
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
$editor = new HTML5Editor("template", $enablePreview = TRUE);
$signEditor = $editor->build(templates::get($templateID), $id = "", $class = "tpl_editor")->get();
DOM::append($signatureEditor, $signEditor);

// Return output
return $appContent->getReport(".templateManager .templateFormContainer");
//#section_end#
?>