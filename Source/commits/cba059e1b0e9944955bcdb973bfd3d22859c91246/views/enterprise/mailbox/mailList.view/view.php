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
use \APP\Mail\mailbox;
use \UI\Apps\APPContent;
use \UI\Forms\templates\simpleForm;

// Create Application Content
$appContent = new APPContent();
$actionFactory = $appContent->getActionFactory();

// Build the application view content
$appContent->build("", "mailboxListContainer", TRUE);

// Get mailbox messages
$mailbox = new mailbox();
$allMail = $mailbox->getMessages();
if (empty($allMail))
{
	// Remove control list
	$controlList = HTML::select(".control-list")->item(0);
	HTML::remove($controlList);
	
	// Create new email button
	$btnCreate = HTML::select(".mailboxList .btn-create")->item(0);
	$actionFactory->setAction($btnCreate, "enterprise/mailbox/newMessageForm", ".mailboxList .message-creator", $attr = array(), $loading = TRUE);
}
else
{
	// Remove no messages container
	$noMessages = HTML::select(".no-messages")->item(0);
	HTML::remove($noMessages);
	
	// Create new email button
	$btnCreate = HTML::select(".mailboxList .btn-ctrl.create")->item(0);
	$actionFactory->setAction($btnCreate, "enterprise/mailbox/newMessageForm", ".mailboxList .message-creator", $attr = array(), $loading = TRUE);
	
	$mailList = HTML::select(".mailboxList .message-list")->item(0);
	$draftContainer = DOM::create("div", "", "", "drafts");
	DOM::append($mailList, $draftContainer);
	
	// List all messages
	uasort($allMail, "sort_mail_by_time");
	foreach ($allMail as $mailID => $mailInfo)
	{
		$listItem = DOM::create("div", "", "", "listItem");
		if ($mailInfo['draft'])
			DOM::append($draftContainer, $listItem);
		else
			DOM::append($mailList, $listItem);
		
		$lhd = DOM::create("div", "", "", "lhd");
		DOM::append($listItem, $lhd);
		
		// Create delete form on the right
		$form = new simpleForm();
		$deleteForm = $form->build("", FALSE)->engageApp("enterprise/mailbox/deleteMail")->get();
		DOM::append($lhd, $deleteForm);
		HTML::addClass($deleteForm, "delete_mail_form");
		
		$input = $form->getInput($type = "hidden", $name = "mid", $value = $mailID, $class = "", $autofocus = FALSE, $required = FALSE);
		$form->append($input);
		
		$deleteButton = $form->getSubmitButton($title, $id = "", $name = "", $class = "btn_delete_mail");
		$form->append($deleteButton);
		
		$linfo = DOM::create("div", date("M d, Y H:i", $mailInfo['time_created']), "", "info timec");
		DOM::append($lhd, $linfo);
		
		if ($mailInfo['draft'])
		{
			$linfo = DOM::create("div", "Resume Draft", "", "info draft");
			DOM::append($lhd, $linfo);
			
			// Set action
			$attr = array();
			$attr['mid'] = $mailID;
			$actionFactory->setAction($linfo, "enterprise/mailbox/newMessageForm", ".mailboxList .message-creator", $attr, $loading = TRUE);
		}
		
		$linfo = DOM::create("div", $mailInfo['subject'], "", "info subject");
		DOM::append($lhd, $linfo);
		
		// Add mail info container
		$mailBody = DOM::create("div", "", "", "mail_body");
		DOM::append($listItem, $mailBody);
		
		$binfo = getMessageRow("From", $mailInfo['from']);
		DOM::append($mailBody, $binfo);
		$binfo = getMessageRow("To", $mailInfo['to']);
		DOM::append($mailBody, $binfo);
		if (!empty($mailInfo['cc']))
		{
			$binfo = getMessageRow("Cc", $mailInfo['cc']);
			DOM::append($mailBody, $binfo);
		}
		if (!empty($mailInfo['bcc']))
		{
			$binfo = getMessageRow("Bcc", $mailInfo['bcc']);
			DOM::append($mailBody, $binfo);
		}
		if (!empty($mailInfo['reply-to']))
		{
			$binfo = getMessageRow("Reply-To", $mailInfo['reply-to']);
			DOM::append($mailBody, $binfo);
		}
		$binfo = getMessageRow("Subject", $mailInfo['subject']);
		DOM::append($mailBody, $binfo);
		
		// Add message body
		if (!empty($mailInfo['message']))
		{
			$bmessage = DOM::create("div", "", "", "bmessage");
			DOM::innerHTML($bmessage, $mailInfo['message']);
			DOM::append($mailBody, $bmessage);
		}
	}
}
// Return output
return $appContent->getReport();

function getMessageRow($title, $value)
{
	$mrow = DOM::create("div", "", "", "mrow");
	
	$mtitle = DOM::create("div", $title.":", "", "mtitle");
	DOM::append($mrow, $mtitle);
	
	$mvalue = DOM::create("div", $value, "", "mvalue");
	DOM::append($mrow, $mvalue);
	
	return $mrow;
}

function sort_mail_by_time($mailA, $mailB)
{
	if ($mailA['time_created'] == $mailB['time_created'])
		return 0;
	
	return ($mailA['time_created'] > $mailB['time_created']) ? -1 : 1;
}
//#section_end#
?>