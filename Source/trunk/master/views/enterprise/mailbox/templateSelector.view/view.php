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

// Import APP Packages
application::import("Mail");
//#section_end#
//#section#[view]
use \APP\Mail\templates;
use \UI\Apps\APPContent;

// Create Application Content
$appContent = new APPContent();
$actionFactory = $appContent->getActionFactory();

// Build the application view content
$appContent->build("", "templateSelectorContainer");

// Get all templates
$allTemplates = templates::getTemplates();
foreach ($allTemplates as $templateID => $templateName)
{
	// create template item
	$tplItem = DOM::create("div", $templateName, "", "tplItem");
	$appContent->append($tplItem);
	
	// Set action to load the template
	$attr = array();
	$attr['tid'] = $templateID;
	$actionFactory->setAction($tplItem, "enterprise/mailbox/templateMessage", ".newMessageForm .farea", $attr);
}

// Return output
return $appContent->getReport();
//#section_end#
?>