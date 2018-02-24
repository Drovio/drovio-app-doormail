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
//#section_end#
//#section#[view]
use \UI\Apps\APPContent;

// Create Application Content
$appContent = new APPContent();

// Get action factory
$actionFactory = $appContent->getActionFactory();

// Build the application view content
$appContent->build("", "doorMailApplicationContainer");

// Check the environment where the application is running on
$viewName = "enterprise/enterpriseMainView";
if (application::onAPC())
	$viewName = "appcenter/AppCenterMainView";
else if (application::onBOSS())
	$viewName = "enterprise/enterpriseMainView";
	

// Load application startup view
$mView = $appContent->loadView($viewName);
$appContent->append($mView);

// Return output
return $appContent->getReport();
//#section_end#
?>