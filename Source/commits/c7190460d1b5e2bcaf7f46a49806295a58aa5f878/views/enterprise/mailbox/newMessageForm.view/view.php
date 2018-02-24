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
importer::import("AEL", "Mail");
importer::import("API", "Profile");
importer::import("UI", "Apps");
importer::import("UI", "Forms");

// Import APP Packages
//#section_end#
//#section#[view]
use \AEL\Mail\appMailer;
use \API\Profile\account;
use \UI\Apps\APPContent;
use \UI\Forms\templates\simpleForm;

// Create Application Content
$appContent = new APPContent();
$actionFactory = $appContent->getActionFactory();

// Get account info
$accountInfo = account::getInstance()->info();
if (engine::isPost())
{
	// Get all fields and validate
	
	// Create mailer
	$appMail = new appMailer(appMailer::MODE_TEAM);
	$from = array();
	switch ($_POST['from'])
	{
		case "team":
			$from = $appMail->getTeamFromAddress();
			break;
		case "app":
			$from = $appMail->getApplicationFromAddress();
			break;
	}
	$appMail->setFrom($from);
	$appMail->setReplyTo($_POST['reply_to']);
	
	// Get recipient addresses and send
	$appMail->addRecipient($_POST['to'], appMailer::RCP_TO);
	if (!empty($_POST['cc']))
		$appMail->addRecipient($_POST['cc'], appMailer::RCP_CC);
	if (!empty($_POST['bcc']))
		$appMail->addRecipient($_POST['bcc'], appMailer::RCP_BCC);
	
	
	$response = $appMail->send($_POST['subject'], "", $_POST['message']);
	$responseArray = json_decode($response, TRUE);

	// Create response notification
	if (!$responseArray['id'])
	{
		// Add error notification
		$appContent->addReportAction($name = "mailbox.message_sent", $value = "An error occurred while sending the email. Please try again later.");
		
		// Return report
		return $appContent->getReport($holder = ".dump-pool");
	}
	
	// Add action to go back to the mailing list
	$appContent->addReportAction("mailbox.showlist");

	// Add sent message notification
	$appContent->addReportAction($name = "mailbox.message_sent", "Message queued for sending");
	
	// Return report
	return $appContent->getReport();
}

// Build the application view content
$appContent->build("", "newMessageFormContainer", TRUE);

// Get form and engage action
$form = new simpleForm();
$mailForm = HTML::select(".message-creator-form")->item(0);
$form->engageStaticApp($mailForm, "enterprise/mailbox/newMessageForm");
$formControls = HTML::select(".newMessageForm .frm-controls")->item(0);

// Get submit button
$title = $appContent->getLiteral("mailbox.new", "lbl_sendEmail");
$sendButton = $form->getSubmitButton($title, $id = "", $name = "", $class = "btn-ctrl send");
DOM::prepend($formControls, $sendButton);
$ico = DOM::create("div", "", "", "ico");
DOM::prepend($sendButton, $ico);

// Get reset button
$title = $appContent->getLiteral("mailbox.new", "lbl_cancelEmail");
$cancelButton = $form->getResetButton($title, $id = "", $class = "btn-ctrl cancel");
DOM::prepend($formControls, $cancelButton);
$ico = DOM::create("div", "", "", "ico");
DOM::prepend($cancelButton, $ico);

// Get from fields
$appMail = new appMailer();
$teamFrom = $appMail->getTeamFromAddress();
$teamFromAddress = key($teamFrom);
$appFrom = $appMail->getApplicationFromAddress();
$appFromAddress = key($appFrom);

$fromResource = array();
$fromResource['team'] = $teamFrom[$teamFromAddress]." <".$teamFromAddress.">";
$fromResource['application'] = $appFrom[$appFromAddress]." <".$appFromAddress.">";

// Add email fields
$fromFieldContainer = HTML::select(".newMessageForm .frm-mail .frow.from")->item(0);
$select = $form->getResourceSelect($name = "from", $multiple = FALSE, $class = "mfinput", $fromResource, $selectedValue = "team");
DOM::append($fromFieldContainer, $select);
// $input


// Load new message container
$btnLoadNew = HTML::select(".newMessageForm .farea .btn-load.new")->item(0);
$actionFactory->setAction($btnLoadNew, "enterprise/mailbox/emptyMessage", ".newMessageForm .farea");


// Add action to switch to new message
$appContent->addReportAction("mailbox.new");

// Return output
return $appContent->getReport();
//#section_end#
?>