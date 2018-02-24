<?php
//#section#[header]
// Namespace
namespace APP\Mail;

require_once($_SERVER['DOCUMENT_ROOT'].'/_domainConfig.php');

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");

// Import application loader
importer::import("AEL", "Platform", "application");
use \AEL\Platform\application;
//#section_end#
//#section#[class]
/**
 * @library	APP
 * @package	Mail
 * 
 * @copyright	Copyright (C) 2015 doorMail. All rights reserved.
 */

importer::import("AEL", "Mail", "appMailer");
importer::import("AEL", "Resources", "DOMParser");
importer::import("AEL", "Resources", "filesystem/fileManager");
importer::import("API", "Profile", "account");

use \AEL\Mail\appMailer;
use \AEL\Resources\DOMParser;
use \AEL\Resources\filesystem\fileManager;
use \API\Profile\account;

/**
 * doorMail mailbox manager
 * 
 * Manages the saved drafts and sent mail.
 * Manages to send a mail and save the log.
 * 
 * @version	0.1-1
 * @created	November 3, 2015, 2:01 (GMT)
 * @updated	November 3, 2015, 2:01 (GMT)
 */
class mailbox
{
	/**
	 * The xml parser object.
	 * 
	 * @type	DOMParser
	 */
	private $xmlParser;
	
	/**
	 * The fileManager object.
	 * 
	 * @type	fileManager
	 */
	private $fm;
	
	/**
	 * Create a new mailbox instance.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Initialize domparser and file manager
		$this->xmlParser = new DOMParser($mode = DOMParser::ACCOUNT_MODE, $shared = FALSE);
		$this->fm = new fileManager($mode = fileManager::ACCOUNT_MODE, $shared = FALSE);
	}
	
	/**
	 * Create a new message as draft.
	 * 
	 * @param	string	$id
	 * 		The mail id.
	 * 
	 * @param	array	$from
	 * 		The from address as needed for the mailgun.
	 * 
	 * @param	string	$replyTo
	 * 		The reply to address.
	 * 
	 * @param	string	$to
	 * 		The recipients addresses.
	 * 
	 * @param	string	$cc
	 * 		The cc addresses.
	 * 
	 * @param	string	$bcc
	 * 		The bcc addresses.
	 * 
	 * @param	string	$subject
	 * 		The mail subject.
	 * 
	 * @param	string	$message
	 * 		The html message.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($id, $from, $replyTo, $to, $cc, $bcc, $subject, $message)
	{
		// Check id
		$id = (empty($id) ? "mlbx_".time()."_".mt_rand() : $id);
		
		// Create entry log
		try
		{
			$this->xmlParser->load("/mailbox/log.xml");
			$root = $this->xmlParser->evaluate("/mailbox")->item(0);
		}
		catch (Exception $ex)
		{
			$root = $this->xmlParser->create("mailbox");
			$this->xmlParser->append($root);
			$this->xmlParser->save("/mailbox/log.xml");
		}
		
		// Check if entry already exists
		$logEntry = $this->xmlParser->find($id);
		if (!empty($logEntry))
			return FALSE;
		
		// Create entry
		$logEntry = $this->xmlParser->create("mlbx_message", "", $id);
		$this->xmlParser->append($root, $logEntry);
		
		// Set extra attributes
		$this->xmlParser->attr($logEntry, "time_created", time());
		
		// Update file
		$this->xmlParser->update();
		
		// Update message
		return $this->update($id, $from, $replyTo, $to, $cc, $bcc, $subject, $message, $draft = TRUE);
	}
	
	/**
	 * Update the mail in the log.
	 * 
	 * @param	string	$id
	 * 		The mail id.
	 * 
	 * @param	array	$from
	 * 		The from address as needed for the mailgun.
	 * 
	 * @param	string	$replyTo
	 * 		The reply to address.
	 * 
	 * @param	string	$to
	 * 		The recipients addresses.
	 * 
	 * @param	string	$cc
	 * 		The cc addresses.
	 * 
	 * @param	string	$bcc
	 * 		The bcc addresses.
	 * 
	 * @param	string	$subject
	 * 		The mail subject.
	 * 
	 * @param	string	$message
	 * 		The html message.
	 * 
	 * @param	boolean	$draft
	 * 		Whether the message is draft or not.
	 * 		It is FALSE by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($id, $from, $replyTo, $to, $cc, $bcc, $subject, $message, $draft = FALSE)
	{
		// Get log
		try
		{
			$this->xmlParser->load("/mailbox/log.xml");
		}
		catch (Exception $ex)
		{
			return FALSE;
		}
		
		// Create entry
		$logEntry = $this->xmlParser->find($id);
		if (empty($logEntry))
			return FALSE;
		
		// Set extra attributes
		$this->xmlParser->attr($logEntry, "from", key($from));
		$this->xmlParser->attr($logEntry, "reply-to", $replyTo);
		$this->xmlParser->attr($logEntry, "to", $to);
		$this->xmlParser->attr($logEntry, "cc", $cc);
		$this->xmlParser->attr($logEntry, "bcc", $bcc);
		$this->xmlParser->attr($logEntry, "subject", $subject);
		$this->xmlParser->attr($logEntry, "draft", ($draft ? 1 : FALSE));
		
		// Update file
		if (!$this->xmlParser->update())
			return FALSE;
		
		// Create file with message
		return $this->fm->create("/mailbox/messages/".$id.".html", $message);
	}
	
	/**
	 * Send the mail and store it as sent.
	 * In case of a draft, update it not to be draft anymore.
	 * 
	 * @param	string	$id
	 * 		The mail id.
	 * 
	 * @param	array	$from
	 * 		The from address as needed for the mailgun.
	 * 
	 * @param	string	$replyTo
	 * 		The reply to address.
	 * 
	 * @param	string	$to
	 * 		The recipients addresses.
	 * 
	 * @param	string	$cc
	 * 		The cc addresses.
	 * 
	 * @param	string	$bcc
	 * 		The bcc addresses.
	 * 
	 * @param	string	$subject
	 * 		The mail subject.
	 * 
	 * @param	string	$message
	 * 		The html message.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function send($id, $from, $replyTo, $to, $cc, $bcc, $subject, $message)
	{
		// Send email
		$appMail = new appMailer(appMailer::MODE_TEAM);
		$appMail->setFrom($from);
		
		// Set reply to
		if (!empty($replyTo))
			$appMail->setReplyTo($replyTo);

		// Get recipient addresses and send
		$appMail->addRecipient($to, appMailer::RCP_TO);
		if (!empty($cc))
			$appMail->addRecipient($cc, appMailer::RCP_CC);
		if (!empty($bcc))
			$appMail->addRecipient($bcc, appMailer::RCP_BCC);


		$response = $appMail->send($subject, "", $message);
		$responseArray = json_decode($response, TRUE);
		
		// Update email
		if ($responseArray['id'])
		{
			$status = $this->update($id, $from, $replyTo, $to, $cc, $bcc, $subject, $message, $draft = FALSE);
			if ($status)
			{
				$this->create($id, $from, $replyTo, $to, $cc, $bcc, $subject, $message);
				$this->update($id, $from, $replyTo, $to, $cc, $bcc, $subject, $message, $draft = FALSE);
			}
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Get information about a saved email.
	 * 
	 * @param	string	$id
	 * 		The mail id to get info for.
	 * 
	 * @return	array
	 * 		An array of all email informatin.
	 */
	public function info($id)
	{
		// Load log file
		$mailbox = array();
		try
		{
			$this->xmlParser->load("/mailbox/log.xml");
		}
		catch (Exception $ex)
		{
			return $mailbox;
		}
		
		// Get mailbox entry
		$entry = $this->xmlParser->find($id);
		if (empty($entry))
			return FALSE;
		
		// Get id
		$id = $this->xmlParser->attr($entry, "id");

		$mlbx = array();
		$mlbx['id'] = $id;
		$mlbx['from'] = $this->xmlParser->attr($entry, "from");
		$mlbx['reply-to'] = $this->xmlParser->attr($entry, "reply-to");
		$mlbx['to'] = $this->xmlParser->attr($entry, "to");
		$mlbx['cc'] = $this->xmlParser->attr($entry, "cc");
		$mlbx['bcc'] = $this->xmlParser->attr($entry, "bcc");
		$mlbx['subject'] = $this->xmlParser->attr($entry, "subject");
		$mlbx['draft'] = $this->xmlParser->attr($entry, "draft");
		$mlbx['time_created'] = $this->xmlParser->attr($entry, "time_created");
		
		// Get message
		$mlbx['message'] = $this->fm->get("/mailbox/messages/".$id.".html");
		
		// Return mail entry info
		return $mlbx;
	}
	
	/**
	 * Remove a mail from the logs.
	 * 
	 * @param	string	$id
	 * 		The mail id to remove.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove($id)
	{
		// Load log file
		$mailbox = array();
		try
		{
			$this->xmlParser->load("/mailbox/log.xml");
		}
		catch (Exception $ex)
		{
			return FALSE;
		}
		
		// Get mailbox entry
		$entry = $this->xmlParser->find($id);
		if (empty($entry))
			return TRUE;
		
		// Remove entry and update
		$this->xmlParser->remove($entry);
		if (!$this->xmlParser->update())
			return FALSE;
		
		// Remove file
		return $this->fm->remove("/mailbox/messages/".$id.".html");
	}
	
	/**
	 * Get all messages in the logs.
	 * 
	 * @return	array
	 * 		An array of all messages and their information.
	 */
	public function getMessages()
	{
		// Load log file
		$mailbox = array();
		try
		{
			$this->xmlParser->load("/mailbox/log.xml");
		}
		catch (Exception $ex)
		{
			return $mailbox;
		}
		
		// Get all entries
		$mailEntries = $this->xmlParser->evaluate("//mlbx_message");
		foreach ($mailEntries as $entry)
		{
			// Get id
			$id = $this->xmlParser->attr($entry, "id");
			
			$mlbx = array();
			$mlbx['id'] = $id;
			$mlbx['from'] = $this->xmlParser->attr($entry, "from");
			$mlbx['reply-to'] = $this->xmlParser->attr($entry, "reply-to");
			$mlbx['to'] = $this->xmlParser->attr($entry, "to");
			$mlbx['cc'] = $this->xmlParser->attr($entry, "cc");
			$mlbx['bcc'] = $this->xmlParser->attr($entry, "bcc");
			$mlbx['subject'] = $this->xmlParser->attr($entry, "subject");
			$mlbx['draft'] = $this->xmlParser->attr($entry, "draft");
			$mlbx['time_created'] = $this->xmlParser->attr($entry, "time_created");
			
			// Get message
			$mlbx['message'] = $this->fm->get("/mailbox/messages/".$id.".html");
			
			// Append to mailbox
			$mailbox[$id] = $mlbx;
		}
		
		// Return mailbox
		return $mailbox;
	}
}
//#section_end#
?>