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
 * Template manager
 * 
 * Manages doorMail templates for the entire team.
 * 
 * @version	0.1-1
 * @created	November 3, 2015, 12:08 (GMT)
 * @updated	November 3, 2015, 12:08 (GMT)
 */
class templates
{
	/**
	 * The template index file.
	 * 
	 * @type	string
	 */
	const TPL_INDEX_FILE = "/mailbox/templates/templates.xml";
	
	/**
	 * Create a new template.
	 * 
	 * @param	string	$templateName
	 * 		The template name/title.
	 * 
	 * @param	string	$template
	 * 		The template content.
	 * 
	 * @return	mixed
	 * 		The template id on success, false on failure.
	 */
	public static function create($templateName, $template = "")
	{
		// Create template entry
		$templateID = "mlbx_tpl_".time().mt_rand();
		
		// Create entry log
		$xmlParser = new DOMParser($mode = DOMParser::TEAM_MODE, $shared = FALSE);
		try
		{
			$xmlParser->load(self::TPL_INDEX_FILE);
			$root = $xmlParser->evaluate("/templates")->item(0);
		}
		catch (Exception $ex)
		{
			$root = $xmlParser->create("templates");
			$xmlParser->append($root);
			$xmlParser->save(self::TPL_INDEX_FILE);
		}
		
		// Check if entry already exists
		$tplEntry = $xmlParser->find($templateID);
		if (!empty($tplEntry))
			return FALSE;
		
		// Create entry
		$tplEntry = $xmlParser->create("template", $templateName, $templateID);
		$xmlParser->append($root, $tplEntry);
		
		// Update file
		if (!$xmlParser->update())
			return FALSE;
		
		// Update template
		self::update($templateID, $template);
		
		// Return template id
		return $templateID;
	}
	
	/**
	 * Get the template html content.
	 * 
	 * @param	string	$templateID
	 * 		The template id.
	 * 
	 * @return	string
	 * 		The template html content.
	 */
	public static function get($templateID)
	{
		// Get account signature
		$fm = new fileManager($mode = fileManager::TEAM_MODE, $shared = FALSE);
		$templateFile = self::getTemplateFile($templateID);
		return $fm->get($templateFile);
	}
	
	/**
	 * Update a template.
	 * 
	 * @param	string	$templateID
	 * 		The template id.
	 * 
	 * @param	string	$templateName
	 * 		The template new name/title.
	 * 
	 * @param	string	$template
	 * 		The template html content.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function update($templateID, $templateName, $template = "")
	{
		// Update entry log
		$xmlParser = new DOMParser($mode = DOMParser::TEAM_MODE, $shared = FALSE);
		try
		{
			$xmlParser->load(self::TPL_INDEX_FILE);
		}
		catch (Exception $ex)
		{
			return TRUE;
		}
		
		// Check if entry already exists
		$tplEntry = $xmlParser->find($templateID);
		if (empty($tplEntry))
			return FALSE;
		
		$xmlParser->nodeValue($tplEntry, $templateName);
		if (!$xmlParser->update())
			return FALSE;
		
		// Get account signature
		$fm = new fileManager($mode = fileManager::TEAM_MODE, $shared = FALSE);
		$templateFile = self::getTemplateFile($templateID);
		return $fm->create($templateFile, $template);
	}
	
	/**
	 * Remove the given template from the list.
	 * 
	 * @param	string	$templateID
	 * 		The template id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function remove($templateID)
	{
		// Remove entry log
		$xmlParser = new DOMParser($mode = DOMParser::TEAM_MODE, $shared = FALSE);
		try
		{
			$xmlParser->load(self::TPL_INDEX_FILE);
		}
		catch (Exception $ex)
		{
			return TRUE;
		}
		
		// Check if entry already exists
		$tplEntry = $xmlParser->find($templateID);
		if (empty($tplEntry))
			return TRUE;
		
		$xmlParser->remove($tplEntry);
		if (!$xmlParser->update())
			return FALSE;
		
		// Return template file
		$fm = new fileManager($mode = fileManager::TEAM_MODE, $shared = FALSE);
		$templateFile = self::getTemplateFile($templateID);
		return $fm->remove($templateFile);
	}
	
	/**
	 * Get all team templates.
	 * 
	 * @return	array
	 * 		An associative array of template id and name.
	 */
	public static function getTemplates()
	{
		// Load log file
		$templates = array();
		$xmlParser = new DOMParser($mode = DOMParser::TEAM_MODE, $shared = FALSE);
		try
		{
			$xmlParser->load(self::TPL_INDEX_FILE);
		}
		catch (Exception $ex)
		{
			return $templates;
		}
		
		// Get all entries
		$tplEntries = $xmlParser->evaluate("//template");
		foreach ($tplEntries as $entry)
			$templates[$xmlParser->attr($entry, "id")] = $entry->nodeValue;
		
		// Return template list
		return $templates;
	}
	
	/**
	 * Get the template html content file path.
	 * 
	 * @param	string	$templateID
	 * 		The template id.
	 * 
	 * @return	string
	 * 		The full file path.
	 */
	private static function getTemplateFile($templateID)
	{
		return "/mailbox/templates/".$templateID.".template.html";
	}
}
//#section_end#
?>