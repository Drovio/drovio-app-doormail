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
importer::import("UI", "Forms");

// Import APP Packages
application::import("Mail");
//#section_end#
//#section#[view]
use \APP\Mail\templates;
use \UI\Apps\APPContent;
use \UI\Forms\templates\simpleForm;

// Create Application Content
$appContent = new APPContent();
$actionFactory = $appContent->getActionFactory();

// Build the application view content
$appContent->build("", "templateListContainer", TRUE);
$templateList = HTML::select(".templateList")->item(0);

// Create form
$form = new simpleForm();
$signForm = $form->build("", FALSE)->engageApp("enterprise/templates/templateEditor")->get();
DOM::append($templateList, $signForm);

$input = $form->getInput($type = "hidden", $name = "load", $value = 1, $class = "", $autofocus = FALSE, $required = TRUE);
$form->append($input);

// Get all templates
$allTemplatesResource = templates::getTemplates();

// Build a form row with label and input
$input = $form->getResourceSelect($name = "tid", $multiple = FALSE, $class = "template_resource", $resource = $allTemplatesResource, $selectedValue = "");
$form->append($input);

$submit = $form->getSubmitButton($title = "Load", $id = "", $name = "", $class = "btn_load_template");
$form->append($submit);

// Return output
return $appContent->getReport();
//#section_end#
?>