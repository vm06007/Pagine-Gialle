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
 * Description : Abstract representation of Ajaxplorer Data Access
 */
defined('AJXP_EXEC') or die( 'Access not allowed');

abstract class AbstractConfDriver extends AJXP_Plugin {
		
	var $options;
	var $driverType = "conf";
	
	protected function parseSpecificContributions(&$contribNode){
		parent::parseSpecificContributions($contribNode);
		if(!AJXP_WEBDAV_ENABLE && $contribNode->nodeName == "actions"){
			unset($this->actions["webdav_preferences"]);
			$actionXpath=new DOMXPath($contribNode->ownerDocument);
			$publicUrlNodeList = $actionXpath->query('action[@name="webdav_preferences"]', $contribNode);
			$publicUrlNode = $publicUrlNodeList->item(0);
			$contribNode->removeChild($publicUrlNode);			
		}		
	}
	
	// SAVE / EDIT / CREATE / DELETE REPOSITORY
	/**
	 * Returns a list of available repositories (dynamic ones only, not the ones defined in the config file).
	 * @return Array
	 */
	abstract function listRepositories();
	/**
	 * Retrieve a Repository given its unique ID.
	 *
	 * @param String $repositoryId
	 * @return Repository
	 */	
	abstract function getRepositoryById($repositoryId);
	/**
	 * Retrieve a Repository given its alias.
	 *
	 * @param String $repositorySlug
	 * @return Repository
	 */	
	abstract function getRepositoryByAlias($repositorySlug);
	/**
	 * Stores a repository, new or not.
	 *
	 * @param Repository $repositoryObject
	 * @param Boolean $update 
	 * @return -1 if failed
	 */	
	abstract function saveRepository($repositoryObject, $update = false);
	/**
	 * Delete a repository, given its unique ID.
	 *
	 * @param String $repositoryId
	 */
	abstract function deleteRepository($repositoryId);
		
	/**
	 * Must return an associative array of roleId => AjxpRole objects.
	 *
	 */
	abstract function listRoles();
	abstract function saveRoles($roles);
	
	/**
	 * Specific queries
	 */
	abstract function countAdminUsers();
	
	/**
	 * Instantiate a new AJXP_User
	 *
	 * @param String $userId
	 * @return AbstractAjxpUser
	 */
	function createUserObject($userId){
		$abstractUser = $this->instantiateAbstractUserImpl($userId);
		if(!$abstractUser->storageExists()){			
			AuthService::updateDefaultRights($abstractUser);
		}
		return $abstractUser;
	}
	
	/**
	 * Instantiate the right class
	 *
	 * @param AbstractAjxpUser $userId
	 */
	abstract function instantiateAbstractUserImpl($userId);
	
	abstract function getUserClassFileName();
	
	function getOption($optionName){	
		return (isSet($this->options[$optionName])?$this->options[$optionName]:"");	
	}
	
		
	function switchAction($action, $httpVars, $fileVars)
	{
		if(!isSet($this->actions[$action])) return;
		$xmlBuffer = "";
		foreach($httpVars as $getName=>$getValue){
			$$getName = AJXP_Utils::securePath($getValue);
		}
		if(isSet($dir) && $action != "upload") $dir = SystemTextEncoding::fromUTF8($dir);
		$mess = ConfService::getMessages();
		
		switch ($action){			
			//------------------------------------
			//	SWITCH THE ROOT REPOSITORY
			//------------------------------------	
			case "switch_repository":
			
				if(!isSet($repository_id))
				{
					break;
				}
				$dirList = ConfService::getRootDirsList();
				if(!isSet($dirList[$repository_id]))
				{
					$errorMessage = "Trying to switch to an unkown repository!";
					break;
				}
				ConfService::switchRootDir($repository_id);
				// Load try to init the driver now, to trigger an exception
				// if it's not loading right.
				ConfService::loadRepositoryDriver();
				if(AuthService::usersEnabled() && AuthService::getLoggedUser()!=null){
					$user = AuthService::getLoggedUser();
					$activeRepId = ConfService::getCurrentRootDirIndex();
					$user->setArrayPref("history", "last_repository", $activeRepId);
					$user->save();
				}
				//$logMessage = "Successfully Switched!";
				AJXP_Logger::logAction("Switch Repository", array("rep. id"=>$repository_id));
				
			break;	
									
			//------------------------------------
			//	BOOKMARK BAR
			//------------------------------------
			case "get_bookmarks":
				
				$bmUser = null;
				if(AuthService::usersEnabled() && AuthService::getLoggedUser() != null)
				{
					$bmUser = AuthService::getLoggedUser();
				}
				else if(!AuthService::usersEnabled())
				{
					$confStorage = ConfService::getConfStorageImpl();
					$bmUser = $confStorage->createUserObject("shared");
				}
				if($bmUser == null) exit(1);
				if(isSet($_GET["bm_action"]) && isset($_GET["bm_path"]))
				{
					if($_GET["bm_action"] == "add_bookmark")
					{
						$title = "";
						if(isSet($_GET["bm_title"])) $title = $_GET["bm_title"];
						if($title == "" && $_GET["bm_path"]=="/") $title = ConfService::getCurrentRootDirDisplay();
						$bmUser->addBookMark(SystemTextEncoding::magicDequote($_GET["bm_path"]), SystemTextEncoding::magicDequote($title));
					}
					else if($_GET["bm_action"] == "delete_bookmark")
					{
						$bmUser->removeBookmark($_GET["bm_path"]);
					}
					else if($_GET["bm_action"] == "rename_bookmark" && isset($_GET["bm_title"]))
					{
						$bmUser->renameBookmark($_GET["bm_path"], $_GET["bm_title"]);
					}
				}
				if(AuthService::usersEnabled() && AuthService::getLoggedUser() != null)
				{
					$bmUser->save();
					AuthService::updateUser($bmUser);
				}
				else if(!AuthService::usersEnabled())
				{
					$bmUser->save();
				}		
				AJXP_XMLWriter::header();
				AJXP_XMLWriter::writeBookmarks($bmUser->getBookmarks());
				AJXP_XMLWriter::close();
				exit(1);
			
			break;
					
			//------------------------------------
			//	SAVE USER PREFERENCE
			//------------------------------------
			case "save_user_pref":
				
				$userObject = AuthService::getLoggedUser();
				$i = 0;
				while(isSet($_GET["pref_name_".$i]) && isSet($_GET["pref_value_".$i]))
				{
					$prefName = AJXP_Utils::sanitize($_GET["pref_name_".$i], AJXP_SANITIZE_ALPHANUM);
					$prefValue = AJXP_Utils::sanitize(SystemTextEncoding::magicDequote(($_GET["pref_value_".$i])));
					if($prefName == "password") continue;
					if($prefName != "pending_folder" && ($userObject == null || $userObject->getId() == "guest")){
						$i++;
						continue;
					}
					$userObject->setPref($prefName, $prefValue);
					$userObject->save();
					AuthService::updateUser($userObject);
					//setcookie("AJXP_$prefName", $prefValue);
					$i++;
				}
				header("Content-Type:text/plain");
				print "SUCCESS";
				exit(1);
				
			break;					
					
			//------------------------------------
			// WEBDAV PREFERENCES
			//------------------------------------
			case "webdav_preferences" :
				
				$userObject = AuthService::getLoggedUser();
				$webdavActive = false;
				$passSet = false;
				// Detect http/https and host
				if(defined("AJXP_WEBDAV_BASEHOST")){
					$baseURL = AJXP_WEBDAV_BASEHOST;
				}else{
					$http_mode = (!empty($_SERVER['HTTPS'])) ? 'https://' : 'http://';
					$baseURL = $http_mode . $_SERVER['HTTP_HOST'];
				}				
				$webdavBaseUrl = $baseURL.AJXP_WEBDAV_BASEURI."/";
				if(isSet($httpVars["activate"]) || isSet($httpVars["webdav_pass"])){
					$davData = $userObject->getPref("AJXP_WEBDAV_DATA");
					if(!empty($httpVars["activate"])){
						$activate = ($httpVars["activate"]=="true" ? true:false);
						if(empty($davData)){
							$davData = array();						
						}
						$davData["ACTIVE"] = $activate;
					}
					if(!empty($httpVars["webdav_pass"])){
						$password = $httpVars["webdav_pass"];
						if (function_exists('mcrypt_encrypt'))
				        {
				        	$user = $userObject->getId();
				        	$secret = (defined("AJXP_SECRET_KEY")? AJXP_SAFE_SECRET_KEY:"\1CDAFx¨op#");
					        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
					        $password = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256,  md5($user.$secret), $password, MCRYPT_MODE_ECB, $iv));
				        }						
						$davData["PASS"] = $password;
					}
					$userObject->setPref("AJXP_WEBDAV_DATA", $davData);
					$userObject->save();
				}
				$davData = $userObject->getPref("AJXP_WEBDAV_DATA");				
				if(!empty($davData)){
					$webdavActive = (isSet($davData["ACTIVE"]) && $davData["ACTIVE"]===true); 
					$passSet = (isSet($davData["PASS"])); 
				}
				$repoList = ConfService::getRepositoriesList();
				$davRepos = array();
				$loggedUser = AuthService::getLoggedUser();
				foreach($repoList as $repoIndex => $repoObject){
					$accessType = $repoObject->getAccessType();
					if(in_array($accessType, array("fs", "ftp")) && ($loggedUser->canRead($repoIndex) || $loggedUser->canWrite($repoIndex))){
						$davRepos[$repoIndex] = $webdavBaseUrl ."".($repoObject->getSlug()==null?$repoObject->getId():$repoObject->getSlug());
					}
				}
				$prefs = array(
					"webdav_active"  => $webdavActive,
					"password_set"   => $passSet,
					"webdav_base_url"  => $webdavBaseUrl, 
					"webdav_repositories" => $davRepos
				);
				HTMLWriter::charsetHeader("application/json");
				print(json_encode($prefs));
				
			break;
			
			default;
			break;
		}
		if(isset($logMessage) || isset($errorMessage))
		{
			$xmlBuffer .= AJXP_XMLWriter::sendMessage((isSet($logMessage)?$logMessage:null), (isSet($errorMessage)?$errorMessage:null), false);			
		}
		
		if(isset($requireAuth))
		{
			$xmlBuffer .= AJXP_XMLWriter::requireAuth(false);
		}
				
		return $xmlBuffer;		
	}
	

}
?>