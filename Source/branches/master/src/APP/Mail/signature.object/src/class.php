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

importer::import("AEL", "Resources", "filesystem/fileManager");
importer::import("AEL", "Resources", "resource");
importer::import("API", "Profile", "account");

use \AEL\Resources\filesystem\fileManager;
use \AEL\Resources\resource;
use \API\Profile\account;

/**
 * Mailbox signature manager
 * 
 * Manages the account's signatures for the application.
 * 
 * @version	0.1-1
 * @created	November 3, 2015, 11:07 (GMT)
 * @updated	November 3, 2015, 11:07 (GMT)
 */
class signature
{
	/**
	 * Get the account's signature.
	 * 
	 * @param	boolean	$resolve
	 * 		Resolve variables in the signature.
	 * 		It is TRUE by default.
	 * 
	 * @return	string
	 * 		The account signature.
	 * 		If empty, get the default signature.
	 */
	public static function get($resolve = TRUE)
	{
		// Get account signature
		$fm = new fileManager($mode = fileManager::ACCOUNT_MODE, $shared = FALSE);
		$accountSignature = $fm->get("/mailbox/signature.html");
		
		// If empty, get default and set
		if (empty($accountSignature))
		{
			$accountSignature = resource::get("/templates/signature.html");
			self::set($accountSignature);
		}
			
		// Set account title (if any)
		if ($resolve)
		{
			$accountInfo = account::getInstance()->info();
			$accountSignature = str_replace("%{account_title}", $accountInfo['title'], $accountSignature);
		}
		
		// Return account signature
		return $accountSignature;
	}
	
	/**
	 * Set the account's mail signature.
	 * 
	 * @param	string	$signature
	 * 		The signature html.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function set($signature)
	{
		// Set account signature
		$fm = new fileManager($mode = fileManager::ACCOUNT_MODE, $shared = FALSE);
		return $fm->create("/mailbox/signature.html", $signature);
	}
}
//#section_end#
?>