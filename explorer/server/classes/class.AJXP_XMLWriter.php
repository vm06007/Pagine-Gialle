<?php
/**
 * @package info.ajaxplorer
 * 
 * Copyright 2007-2009 Charles du Jeu
 * This file is part of AjaXplorer.
 * The latest code can be found at http://www.ajaxplorer.info/
 * 
 * This program is published under the LGPL Gnu Lesser General Public License.
 * You should have received a copy of the license along with AjaXplorer.
 * 
 * The main conditions are as follow : 
 * You must conspicuously and appropriately publish on each copy distributed 
 * an appropriate copyright notice and disclaimer of warranty and keep intact 
 * all the notices that refer to this License and to the absence of any warranty; 
 * and give any other recipients of the Program a copy of the GNU Lesser General 
 * Public License along with the Program. 
 * 
 * If you modify your copy or copies of the library or any portion of it, you may 
 * distribute the resulting library provided you do so under the GNU Lesser 
 * General Public License. However, programs that link to the library may be 
 * licensed under terms of your choice, so long as the library itself can be changed. 
 * Any translation of the GNU Lesser General Public License must be accompanied by the 
 * GNU Lesser General Public License.
 * 
 * If you copy or distribute the program, you must accompany it with the complete 
 * corresponding machine-readable source code or with a written offer, valid for at 
 * least three years, to furnish the complete corresponding machine-readable source code. 
 * 
 * Any of the above conditions can be waived if you get permission from the copyright holder.
 * AjaXplorer is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * 
 * Description : Util methods for generating XML outputs.
 */
defined('AJXP_EXEC') or die( 'Access not allowed');

class AJXP_XMLWriter
{
	static $headerSent = false;
	
	static function header($docNode="tree", $attributes=array())
	{
		if(self::$headerSent !== false && self::$headerSent == $docNode) return ;
		header('Content-Type: text/xml; charset=UTF-8');
		header('Cache-Control: no-cache');
		print('<?xml version="1.0" encoding="UTF-8"?>');
		$attString = "";
		if(count($attributes)){
			foreach ($attributes as $name=>$value){
				$attString.="$name=\"$value\" ";
			}
		}
		self::$headerSent = $docNode;
		print("<$docNode $attString>");
		
	}
	
	static function close($docNode="tree")
	{
		print("</$docNode>");
	}
	
	static function write($data, $print){
		if($print) {
			print($data);
			return "";		
		}else{
			return $data;
		}
	}
	
	static function renderPaginationData($count, $currentPage, $totalPages, $dirsCount = -1){
		$string = '<pagination count="'.$count.'" total="'.$totalPages.'" current="'.$currentPage.'" overflowMessage="306" icon="folder.png" openicon="folder_open.png" dirsCount="'.$dirsCount.'"/>';		
		AJXP_XMLWriter::write($string, true);
	}
	
	static function renderHeaderNode($nodeName, $nodeLabel, $isLeaf, $metaData = array()){
		header('Content-Type: text/xml; charset=UTF-8');
		header('Cache-Control: no-cache');
		print('<?xml version="1.0" encoding="UTF-8"?>');
		self::$headerSent = "tree";
		AJXP_XMLWriter::renderNode($nodeName, $nodeLabel, $isLeaf, $metaData, false);
	}
	
	static function renderNode($nodeName, $nodeLabel, $isLeaf, $metaData = array(), $close=true){
		$string = "<tree";
		$metaData["filename"] = $nodeName;
		$metaData["text"] = $nodeLabel;
		$metaData["is_file"] = ($isLeaf?"true":"false");
		foreach ($metaData as $key => $value){
			$string .= " $key=\"$value\"";
		}
		if($close){
			$string .= "/>";
		}else{
			$string .= ">";
		}
		AJXP_XMLWriter::write($string, true);
	}
	
	static function renderNodeArray($array){
		self::renderNode($array[0],$array[1],$array[2],$array[3]);
	}
	
	static function catchError($code, $message, $fichier, $ligne, $context){
		if(error_reporting() == 0) return ;
		if(ConfService::getConf("SERVER_DEBUG")){
			$message = "$message in $fichier (l.$ligne)";
		}
		AJXP_Logger::logAction("error", array("message" => $message));
		AJXP_XMLWriter::header();
		AJXP_XMLWriter::sendMessage(null, SystemTextEncoding::toUTF8($message), true);
		AJXP_XMLWriter::close();
		exit(1);
	}
	
	/**
	 * Catch exceptions
	 *
	 * @param Exception $exception
	 */
	static function catchException($exception){
		AJXP_XMLWriter::catchError($exception->getCode(), SystemTextEncoding::fromUTF8($exception->getMessage()), $exception->getFile(), $exception->getLine(), null);
	}
	
	static function replaceAjxpXmlKeywords($xml, $stripSpaces = false){
		$messages = ConfService::getMessages();			
		$matches = array();
		$xml = str_replace("AJXP_CLIENT_RESOURCES_FOLDER", CLIENT_RESOURCES_FOLDER, $xml);
		
		if(isSet($_SESSION["AJXP_SERVER_PREFIX_URI"])){
			$xml = str_replace("AJXP_THEME_FOLDER", $_SESSION["AJXP_SERVER_PREFIX_URI"].AJXP_THEME_FOLDER, $xml);
			$xml = str_replace("AJXP_SERVER_ACCESS", $_SESSION["AJXP_SERVER_PREFIX_URI"].SERVER_ACCESS, $xml);
		}else{
			$xml = str_replace("AJXP_THEME_FOLDER", AJXP_THEME_FOLDER, $xml);
			$xml = str_replace("AJXP_SERVER_ACCESS", SERVER_ACCESS, $xml);
		}
		$xml = str_replace("AJXP_MIMES_EDITABLE", AJXP_Utils::getAjxpMimes("editable"), $xml);
		$xml = str_replace("AJXP_MIMES_IMAGE", AJXP_Utils::getAjxpMimes("image"), $xml);
		$xml = str_replace("AJXP_MIMES_AUDIO", AJXP_Utils::getAjxpMimes("audio"), $xml);
		$xml = str_replace("AJXP_MIMES_ZIP", AJXP_Utils::getAjxpMimes("zip"), $xml);
		$authDriver = ConfService::getAuthDriverImpl();
		if($authDriver != NULL){
			$loginRedirect = $authDriver->getLoginRedirect();
			$xml = str_replace("AJXP_LOGIN_REDIRECT", ($loginRedirect!==false?"'".$loginRedirect."'":"false"), $xml);
		}
        $xml = str_replace("AJXP_REMOTE_AUTH", "false", $xml);
        $xml = str_replace("AJXP_NOT_REMOTE_AUTH", "true", $xml);
        $xml = str_replace("AJXP_ALL_MESSAGES", "MessageHash=".json_encode(ConfService::getMessages()).";", $xml);
		
		if(preg_match_all("/AJXP_MESSAGE(\[.*?\])/", $xml, $matches, PREG_SET_ORDER)){
			foreach($matches as $match){
				$messId = str_replace("]", "", str_replace("[", "", $match[1]));
				$xml = str_replace("AJXP_MESSAGE[$messId]", $messages[$messId], $xml);
			}
		}
		if($stripSpaces){
			$xml = preg_replace("/[\n\r]?/", "", $xml);
			$xml = preg_replace("/\t/", " ", $xml);
		}
		return $xml;		
	}	
	
	static function reloadCurrentNode($print = true)
	{
		return AJXP_XMLWriter::write("<reload_instruction object=\"tree\"/>", $print);
	}
	
	static function reloadNode($nodeName, $print = true)
	{
		return AJXP_XMLWriter::write("<reload_instruction object=\"tree\" node=\"$nodeName\"/>", $print);
	}
		
	static function reloadFileList($fileOrBool, $print = true)
	{
		if(is_string($fileOrBool)) return AJXP_XMLWriter::write("<reload_instruction object=\"list\" file=\"".AJXP_Utils::xmlEntities(SystemTextEncoding::toUTF8($fileOrBool))."\"/>", $print);
		else return AJXP_XMLWriter::write("<reload_instruction object=\"list\"/>", $print);
	}
	
	static function reloadDataNode($nodePath="", $pendingSelection="", $print = true){
		$nodePath = AJXP_Utils::xmlEntities($nodePath, true);
		$pendingSelection = AJXP_Utils::xmlEntities($pendingSelection, true);
		return AJXP_XMLWriter::write("<reload_instruction object=\"data\" node=\"$nodePath\" file=\"$pendingSelection\"/>", $print);
	}
	
	static function reloadRepositoryList($print = true){
		return AJXP_XMLWriter::write("<reload_instruction object=\"repository_list\"/>", $print);
	}
	
	static function requireAuth($print = true)
	{
		return AJXP_XMLWriter::write("<require_auth/>", $print);
	}
	
	static function triggerBgAction($actionName, $parameters, $messageId, $print=true){
		$data = AJXP_XMLWriter::write("<trigger_bg_action name=\"$actionName\" messageId=\"$messageId\">", $print);
		foreach ($parameters as $paramName=>$paramValue){
			$data .= AJXP_XMLWriter::write("<param name=\"$paramName\" value=\"$paramValue\"/>", $print);
		}
		$data .= AJXP_XMLWriter::write("</trigger_bg_action>", $print);
		return $data;		
	}
	
	static function writeBookmarks($allBookmarks, $print = true)
	{
		$buffer = "";
		foreach ($allBookmarks as $bookmark)
		{
			$path = ""; $title = "";
			if(is_array($bookmark)){
				$path = $bookmark["PATH"];
				$title = $bookmark["TITLE"];
			}else if(is_string($bookmark)){
				$path = $bookmark;
				$title = basename($bookmark);
			}
			$buffer .= "<bookmark path=\"".AJXP_Utils::xmlEntities($path)."\" title=\"".AJXP_Utils::xmlEntities($title)."\"/>";
		}
		if($print) print $buffer;
		else return $buffer;
	}
	
	static function sendFilesListComponentConfig($config){
		if(is_string($config)){
			print("<client_configs><component_config className=\"FilesList\">$config</component_config></client_configs>");
		}
	}
	
	static function sendMessage($logMessage, $errorMessage, $print = true)
	{
		$messageType = ""; 
		$message = "";
		if($errorMessage == null)
		{
			$messageType = "SUCCESS";
			$message = AJXP_Utils::xmlEntities($logMessage);
		}
		else
		{
			$messageType = "ERROR";
			$message = AJXP_Utils::xmlEntities($errorMessage);
		}
		return AJXP_XMLWriter::write("<message type=\"$messageType\">".$message."</message>", $print);
	}

	static function sendUserData($userObject = null, $details=false){
		print(AJXP_XMLWriter::getUserXML($userObject, $details));
	}
	
	static function getUserXML($userObject = null, $details=false)
	{
		$buffer = "";
		$loggedUser = AuthService::getLoggedUser();
		if($userObject != null) $loggedUser = $userObject;
		if(!AuthService::usersEnabled()){
			$buffer.="<user id=\"shared\">";
			if(!$details){
				$buffer.="<active_repo id=\"".ConfService::getCurrentRootDirIndex()."\" write=\"1\" read=\"1\"/>";
			}
			$buffer.= AJXP_XMLWriter::writeRepositoriesData(null, $details);
			$buffer.="</user>";	
		}else if($loggedUser != null){
			$buffer.="<user id=\"".$loggedUser->id."\">";
			if(!$details){
				$buffer.="<active_repo id=\"".ConfService::getCurrentRootDirIndex()."\" write=\"".($loggedUser->canWrite(ConfService::getCurrentRootDirIndex())?"1":"0")."\" read=\"".($loggedUser->canRead(ConfService::getCurrentRootDirIndex())?"1":"0")."\"/>";
			}else{
				$buffer .= "<ajxp_roles>";
				foreach ($loggedUser->getRoles() as $roleId => $boolean){
					if($boolean === true) $buffer.= "<role id=\"$roleId\"/>";
				}
				$buffer .= "</ajxp_roles>";
			}
			$buffer.= AJXP_XMLWriter::writeRepositoriesData($loggedUser, $details);
			$buffer.="<preferences>";
			$buffer.="<pref name=\"display\" value=\"".$loggedUser->getPref("display")."\"/>";
			$buffer.="<pref name=\"lang\" value=\"".$loggedUser->getPref("lang")."\"/>";
			$buffer.="<pref name=\"diapo_autofit\" value=\"".$loggedUser->getPref("diapo_autofit")."\"/>";
			$buffer.="<pref name=\"sidebar_splitter_size\" value=\"".$loggedUser->getPref("sidebar_splitter_size")."\"/>";
			$buffer.="<pref name=\"vertical_splitter_size\" value=\"".$loggedUser->getPref("vertical_splitter_size")."\"/>";			
			$buffer.="<pref name=\"history_last_repository\" value=\"".$loggedUser->getArrayPref("history", "last_repository")."\"/>";
			$buffer.="<pref name=\"ls_history\"><![CDATA[".$loggedUser->getPref("ls_history")."]]></pref>";			
			$buffer.="<pref name=\"pending_folder\" value=\"".$loggedUser->getPref("pending_folder")."\"/>";
			$buffer.="<pref name=\"thumb_size\" value=\"".$loggedUser->getPref("thumb_size")."\"/>";
			$buffer.="<pref name=\"columns_size\" value=\"".stripslashes(str_replace("\"", "'", $loggedUser->getPref("columns_size")))."\"/>";
			$buffer.="<pref name=\"columns_visibility\" value=\"".stripslashes(str_replace("\"", "'", $loggedUser->getPref("columns_visibility")))."\"/>";
			$buffer.="<pref name=\"plugins_preferences\" value=\"".stripslashes(str_replace("\"", "'", $loggedUser->getPref("plugins_preferences")))."\"/>";
			$buffer.="<pref name=\"upload_auto_send\" value=\"".$loggedUser->getPref("upload_auto_send")."\"/>";
			$buffer.="<pref name=\"upload_auto_close\" value=\"".$loggedUser->getPref("upload_auto_close")."\"/>";
			$buffer.="<pref name=\"upload_existing\" value=\"".$loggedUser->getPref("upload_existing")."\"/>";
			$buffer.="</preferences>";
			$buffer.="<special_rights is_admin=\"".($loggedUser->isAdmin()?"1":"0")."\"/>";
			$bMarks = $loggedUser->getBookmarks();
			if(count($bMarks)){
				$buffer.= "<bookmarks>".AJXP_XMLWriter::writeBookmarks($bMarks, false)."</bookmarks>";
			}
			$buffer.="</user>";
		}
		return $buffer;		
	}
	
	static function writeRepositoriesData($loggedUser, $details=false){
		$st = "";
		$st .= "<repositories>";
		$streams = ConfService::detectRepositoryStreams(false);
		foreach (ConfService::getRepositoriesList() as $rootDirIndex => $rootDirObject)
		{		
			$toLast = false;
			if($rootDirObject->getAccessType()=="ajxp_conf"){
				if(AuthService::usersEnabled() && !$loggedUser->isAdmin()){
					continue;
				}else{
					$toLast = true;
				}				
			}
			if($rootDirObject->getAccessType() == "ajxp_shared" && !AuthService::usersEnabled()){
				continue;
			}
			if($loggedUser == null || $loggedUser->canRead($rootDirIndex) || $loggedUser->canWrite($rootDirIndex) || $details) {
				// Do not display standard repositories even in details mode for "sub"users
				if($loggedUser != null && $loggedUser->hasParent() && !$loggedUser->canRead($rootDirIndex)) continue;
				// Do not display shared repositories otherwise.
				if($loggedUser != null && $rootDirObject->hasOwner() && !$loggedUser->hasParent())continue;
				
				$rightString = "";
				if($details){
					$rightString = " r=\"".($loggedUser->canRead($rootDirIndex)?"1":"0")."\" w=\"".($loggedUser->canWrite($rootDirIndex)?"1":"0")."\"";
				}
				$streamString = "";
				if(in_array($rootDirObject->accessType, $streams)){
					$streamString = "allowCrossRepositoryCopy=\"true\"";
				}
				$slugString = "";
				$slug = $rootDirObject->getSlug();
				if(!empty($slug)){
					$slugString = "repositorySlug=\"$slug\"";
				}
				if($toLast){
					$lastString = "<repo access_type=\"".$rootDirObject->accessType."\" id=\"".$rootDirIndex."\"$rightString $streamString $slugString><label>".SystemTextEncoding::toUTF8(AJXP_Utils::xmlEntities($rootDirObject->getDisplay()))."</label>".$rootDirObject->getClientSettings()."</repo>";
				}else{
					$st .= "<repo access_type=\"".$rootDirObject->accessType."\" id=\"".$rootDirIndex."\"$rightString $streamString $slugString><label>".SystemTextEncoding::toUTF8(AJXP_Utils::xmlEntities($rootDirObject->getDisplay()))."</label>".$rootDirObject->getClientSettings()."</repo>";
				}
			}
		}
		if(isSet($lastString)){
			$st.= $lastString;
		}
		$st .= "</repositories>";
		return $st;
	}
	
	/**
	 * Send repositories access for given role as XML
	 *
	 * @param AjxpRole $role
	 * @return string
	 */
	static function writeRoleRepositoriesData($role){
		$st = "<repositories>";
		foreach (ConfService::getRepositoriesList() as $repoId => $repoObject)
		{		
			$toLast = false;
			if($repoObject->getAccessType() == "ajxp_conf") continue;
			if($repoObject->getAccessType() == "ajxp_shared" && !AuthService::usersEnabled()){
				continue;
			}				
			$rightString = " r=\"".($role->canRead($repoId)?"1":"0")."\" w=\"".($role->canWrite($repoId)?"1":"0")."\"";
			$string = "<repo access_type=\"".$repoObject->accessType."\" id=\"".$repoId."\"$rightString><label>".SystemTextEncoding::toUTF8(AJXP_Utils::xmlEntities($repoObject->getDisplay()))."</label></repo>";
			if($toLast){
				$lastString = $string;
			}else{
				$st .= $string;
			}
		}
		if(isSet($lastString)){
			$st.= $lastString;
		}
		$st .= "</repositories>";
		$st .= "<actions_rights>";
		foreach ($role->getSpecificActionsRights("ajxp.all") as $actionId => $actionValue){
			$st.="<action name=\"$actionId\" value=\"".($actionValue?"true":"false")."\"/>";
		}
		$st .= "</actions_rights>";
		return $st;
	}
	
	static function loggingResult($result, $rememberLogin="", $rememberPass = "", $secureToken="")
	{
		$remString = "";
		if($rememberPass != "" && $rememberLogin!= ""){
			$remString = " remember_login=\"$rememberLogin\" remember_pass=\"$rememberPass\"";
		}
		if($secureToken != ""){
			$remString .= " secure_token=\"$secureToken\"";
		}
		print("<logging_result value=\"$result\"$remString/>");
	}
	
}

?>
