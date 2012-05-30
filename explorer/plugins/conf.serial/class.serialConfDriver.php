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
 * Description : Serialized Files implementation of AbstractConfDriver
 */
defined('AJXP_EXEC') or die( 'Access not allowed');

require_once(INSTALL_PATH."/server/classes/class.AbstractConfDriver.php");
class serialConfDriver extends AbstractConfDriver {
		
	var $repoSerialFile;
	var $usersSerialDir;
	var $rolesSerialFile;
	
	var $aliasesIndexFile;
	
	function init($options){
		parent::init($options);
		$this->repoSerialFile = AJXP_VarsFilter::filter($options["REPOSITORIES_FILEPATH"]);
		$this->usersSerialDir = AJXP_VarsFilter::filter($options["USERS_DIRPATH"]);
		$this->rolesSerialFile = AJXP_VarsFilter::filter($options["ROLES_FILEPATH"]);
		$this->aliasesIndexFile = dirname($this->repoSerialFile)."/aliases.ser";
	}
	
	function performChecks(){
		$this->performSerialFileCheck($this->repoSerialFile, "repositories file");
		$this->performSerialFileCheck($this->usersSerialDir, "users file", true);
		$this->performSerialFileCheck($this->rolesSerialFile, "roles file");
	}
	
	function performSerialFileCheck($file, $fileLabel, $isDir = false){
		if($isDir){
			if(!is_dir($file) || !is_writable($file)){
				throw new Exception("Folder for storing $fileLabel is either inexistent or not writeable.");
			}
			return ;
		}
		$dir = dirname($file);
		if(!is_dir($dir) || !is_writable($dir)){
			throw new Exception("Parent folder for $fileLabel is either inexistent or not writeable.");
		}
		if(is_file($file) && !is_writable($file)){
			throw new Exception(ucfirst($fileLabel)." exists but is not writeable!");
		}
	}
	
	// SAVE / EDIT / CREATE / DELETE REPOSITORY
	function listRepositories(){
		return AJXP_Utils::loadSerialFile($this->repoSerialFile);
		
	}
	
	function listRoles(){
		return AJXP_Utils::loadSerialFile($this->rolesSerialFile);
	}
	
	function saveRoles($roles){
		AJXP_Utils::saveSerialFile($this->rolesSerialFile, $roles);
	}
	
	function countAdminUsers(){
		$confDriver = ConfService::getConfStorageImpl();
		$authDriver = ConfService::getAuthDriverImpl();			
		$count = 0;
		$users = $authDriver->listUsers();
		foreach (array_keys($users) as $userId){
			$userObject = $confDriver->createUserObject($userId);
			$userObject->load();			
			if($userObject->isAdmin()) $count++;
		}		
		return $count;
	}
	
	/**
	 * Unique ID of the repositor
	 *
	 * @param String $repositoryId
	 * @return Repository
	 */
	function getRepositoryById($repositoryId){
		$repositories = AJXP_Utils::loadSerialFile($this->repoSerialFile);
		if(isSet($repositories[$repositoryId])){
			return $repositories[$repositoryId];		
		}
		return null;
	}
	/**
	 * Retrieve a Repository given its alias.
	 *
	 * @param String $repositorySlug
	 * @return Repository
	 */	
	function getRepositoryByAlias($repositorySlug){
		$data = AJXP_Utils::loadSerialFile($this->aliasesIndexFile);
		if(isSet($data[$repositorySlug])){
			return $this->getRepositoryById($data[$repositorySlug]);
		}
		return null;
	}
	
	/**
	 * Store a newly created repository 
	 *
	 * @param Repository $repositoryObject
	 * @param Boolean $update 
	 * @return -1 if failed
	 */
	function saveRepository($repositoryObject, $update = false){
		$repositories = AJXP_Utils::loadSerialFile($this->repoSerialFile);
		if(!$update){
			$repositoryObject->writeable = true;
			$repositories[$repositoryObject->getUniqueId()] = $repositoryObject;
		}else{
			foreach ($repositories as $index => $repo){
				if($repo->getUniqueId() == $repositoryObject->getUniqueId()){
					$repositories[$index] = $repositoryObject;
					break;
				}
			}
		}
		$res = AJXP_Utils::saveSerialFile($this->repoSerialFile, $repositories);
		if($res == -1){
			return $res;
		}else{
			$this->updateAliasesIndex($repositoryObject->getUniqueId(), $repositoryObject->getSlug());
		}
	}
	/**
	 * Delete a repository, given its unique ID.
	 *
	 * @param String $repositoryId
	 */	
	function deleteRepository($repositoryId){
		$repositories = AJXP_Utils::loadSerialFile($this->repoSerialFile);
		$newList = array();
		foreach ($repositories as $repo){
			if($repo->getUniqueId() != $repositoryId){
				$newList[$repo->getUniqueId()] = $repo;
			}
		}
		AJXP_Utils::saveSerialFile($this->repoSerialFile, $newList);
	}
	/**
	 * Serial specific method : indexes repositories by slugs, for better performances
	 */
	function updateAliasesIndex($repositoryId, $repositorySlug){
		$data = AJXP_Utils::loadSerialFile($this->aliasesIndexFile);
		$byId = array_flip($data);
		$byId[$repositoryId] = $repositorySlug;
		AJXP_Utils::saveSerialFile($this->aliasesIndexFile, array_flip($byId));
	}
	
	// SAVE / EDIT / CREATE / DELETE USER OBJECT (except password)
	/**
	 * Instantiate the right class
	 *
	 * @param AbstractAjxpUser $userId
	 */
	function instantiateAbstractUserImpl($userId){
		return new AJXP_User($userId, $this);
	}
	
	
	function getUserClassFileName(){
		return INSTALL_PATH."/plugins/conf.serial/class.AJXP_User.php";
	}	
}
?>