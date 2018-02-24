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
importer::import("AEL", "Resources");
importer::import("UI", "Apps");
importer::import("UI", "Developer");

// Import APP Packages
application::import("Mail");
//#section_end#
//#section#[view]
use \AEL\Resources\resource;
use \APP\Mail\signature;
use \UI\Apps\APPContent;
use \UI\Developer\editors\HTML5Editor;

// Create Application Content
$appContent = new APPContent();
$actionFactory = $appContent->getActionFactory();

// Build the application view content
$appContent->build("", "mailComposerContainer");

// Get html template
$htmlTemplate = resource::get("/templates/emptyTemplate.html");

// Get doorMail signature
$signature = signature::get();

// Create new html5 editor
$editor = new HTML5Editor("message", $enablePreview = TRUE);
$emailEditor = $editor->build($htmlTemplate."\n".$signature, $id = "", $class = "email_editor")->get();
$appContent->append($emailEditor);

// Return output
return $appContent->getReport("", APPContent::REPLACE_METHOD);
//#section_end#
?>