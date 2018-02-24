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
$actionFactory = $appContent->getActionFactory();

// Build the application view content
$appContent->build("", "enterpriseDoorMailApplicationContainer", TRUE);

// Set navigation
$menuItems = array();
$menuItems['email'] = "mailbox/mailList";
$menuItems['lists'] = "lists/listManager";
$menuItems['templates'] = "templates/templateManager";
$menuItems['settings'] = "settings/appSettings";
$menuItems['about'] = "AboutView";
$mainContent = HTML::select(".enterpriseDoorMailApplication .mainContent")->item(0);
foreach ($menuItems as $class => $viewName)
{
	// Get menu item
	$mItem = HTML::select(".enterpriseDoorMailApplication .sidebar .menuitem.".$class)->item(0);
	
	// Set navigation ref
	$ref = "drml_ref_".$class;
	$appContent->setStaticNav($mItem, $ref, $targetcontainer = "drmlMainContainer", $targetgroup = "drml_ngroup", $navgroup = "drml_ngroup", $display = "none");
	DOM::data($mItem, "ref", $ref);
	
	$preload = FALSE;
	if (HTML::hasClass($mItem, "selected"))
		$preload = TRUE;
	$mContainer = $appContent->getAppViewContainer("/enterprise/".$viewName, $attr = array(), $startup = FALSE, $ref, $loading = TRUE, $preload);
	DOM::append($mainContent, $mContainer);
	
	// Set navigation target group
	$appContent->setNavigationGroup($mContainer, "drml_ngroup");
}

// Return output
return $appContent->getReport();
//#section_end#
?>