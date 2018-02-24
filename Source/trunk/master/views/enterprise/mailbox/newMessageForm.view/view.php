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
importer::import("UI", "Developer");
importer::import("UI", "Forms");

// Import APP Packages
application::import("Mail");
//#section_end#
//#section#[view]
use \AEL\Mail\appMailer;
use \API\Profile\account;
use \APP\Mail\mailbox;
use \UI\Apps\APPContent;
use \UI\Developer\editors\HTML5Editor;
use \UI\Forms\templates\simpleForm;

// Create Application Content
$appContent = new APPContent();
$actionFactory = $appContent->getActionFactory();

// Get mailbox
$mailID = engine::getVar("mid");
$mailbox = new mailbox();
if (empty($mailID))
{
	// Create draft
	$mailID = "mlbx_".time()."_".mt_rand();
	$mailbox->create($mailID);
}
else
	$mailInfo = $mailbox->info($mailID);

// Get account info
$accountInfo = account::getInstance()->info();
if (engine::isPost())
{
	// Get from field
	$from = array();
	$appMail = new appMailer($mode = appMailer::TEAM_MODE);
	switch ($_POST['from'])
	{
		case "team":
			$from = $appMail->getTeamFromAddress();
			break;
		case "application":
			$from = $appMail->getApplicationFromAddress();
			break;
	}
	
	// Check if it's cancel
	$cancelMessage = engine::getVar("cancel_message");
	if ($cancelMessage)
	{
		$mailbox = new mailbox();
		$status = $mailbox->update($mailID, $from, $_POST['reply_to'], $_POST['to'], $_POST['cc'], $_POST['bcc'], $_POST['subject'], $_POST['message'], $draft = TRUE);
		
		// Add action to go back to the mailing list
		$appContent->addReportAction("mailbox.showlist");
		
		// Return report
		return $appContent->getReport();
	}
	
	// Send email
	$mailbox = new mailbox();
	$status = $mailbox->send($mailID, $from, $_POST['reply_to'], $_POST['to'], $_POST['cc'], $_POST['bcc'], $_POST['subject'], $_POST['message']);

	// Create response notification
	if (!$status)
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

// Add mail id
$formContainer = HTML::select(".newMessageForm .frm-mail")->item(0);
$input = $form->getInput($type = "hidden", $name = "mid", $value = $mailID, $class = "", $autofocus = FALSE, $required = FALSE);
DOM::prepend($formContainer, $input);

// Cancel input
$input = $form->getInput($type = "hidden", $name = "cancel_message", $value = 0, $class = "", $autofocus = FALSE, $required = FALSE);
DOM::prepend($formContainer, $input);

// Get from fields
$appMail = new appMailer();
$teamFrom = $appMail->getTeamFromAddress();
$teamFromAddress = key($teamFrom);
$appFrom = $appMail->getApplicationFromAddress();
$appFromAddress = key($appFrom);

$fromResource = array();
$fromResource['team'] = $teamFrom[$teamFromAddress]." <".$teamFromAddress.">";
$fromResource['application'] = $appFrom[$appFromAddress]." <".$appFromAddress.">";
$selectedFromValue = "team";
if (!empty($mailInfo))
{
	if ($mailInfo['from'] == $teamFromAddress)
		$selectedFromValue = "team";
	else if ($mailInfo['from'] == $appFromAddress)
		$selectedFromValue = "application";
}

// Add email fields
$fromFieldContainer = HTML::select(".newMessageForm .frm-mail .frow.from")->item(0);
$select = $form->getResourceSelect($name = "from", $multiple = FALSE, $class = "mfinput", $fromResource, $selectedFromValue);
DOM::append($fromFieldContainer, $select);

// Set recipients
if (!empty($mailInfo))
{
	$input = HTML::select(".newMessageForm .frm-mail .frow.to input")->item(0);
	HTML::attr($input, "value", $mailInfo['to']);
	
	$input = HTML::select(".newMessageForm .frm-mail .frow.cc input")->item(0);
	HTML::attr($input, "value", $mailInfo['cc']);
	
	$input = HTML::select(".newMessageForm .frm-mail .frow.bcc input")->item(0);
	HTML::attr($input, "value", $mailInfo['bcc']);
	
	$input = HTML::select(".newMessageForm .frm-mail .frow.reply-to input")->item(0);
	HTML::attr($input, "value", $mailInfo['reply-to']);
	
	$input = HTML::select(".newMessageForm .frm-mail .frow.subject input")->item(0);
	HTML::attr($input, "value", $mailInfo['subject']);
}

if (!empty($mailInfo['message']))
{
	$farea = HTML::select(".newMessageForm .frm-mail .farea")->item(0);
	HTML::innerHTML($farea, "");
	
	// Create html editor
	$editor = new HTML5Editor("message", $enablePreview = TRUE);
	$emailEditor = $editor->build($mailInfo['message'], $id = "", $class = "email_editor")->get();
	DOM::append($farea, $emailEditor);
}
else
{
	// Load templateList container
	$btnLoadNew = HTML::select(".newMessageForm .farea .btn-load.template")->item(0);
	$actionFactory->setAction($btnLoadNew, "enterprise/mailbox/templateSelector", ".newMessageForm .farea");
	
	// Load new message container
	$btnLoadNew = HTML::select(".newMessageForm .farea .btn-load.new")->item(0);
	$actionFactory->setAction($btnLoadNew, "enterprise/mailbox/emptyMessage", ".newMessageForm .farea");
}

// Add action to switch to new message
$appContent->addReportAction("mailbox.new");

// Return output
return $appContent->getReport();
//#section_end#
?>