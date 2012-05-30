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
 * Description : Authentication against an FTP server, using a defined FTP Repository.
 */
defined('AJXP_EXEC') or die( 'Access not allowed');

require_once(INSTALL_PATH."/server/classes/class.AbstractAuthDriver.php");
require_once(INSTALL_PATH."/plugins/access.smb/smb.php");

class smbAuthDriver extends AbstractAuthDriver {
	
	var $driverName = "smb";
		
	function listUsers(){
		$adminUser = $this->options["ADMIN_USER"];
		return array($adminUser => $adminUser);
	}
	
	function userExists($login){
		return true;
	}	
	
	function logoutCallback($actionName, $httpVars, $fileVars){		
		$crtUser = $_SESSION["AJXP_SESSION_REMOTE_USER"];
		unset($_SESSION["AJXP_SESSION_REMOTE_USER"]);
		unset($_SESSION["AJXP_SESSION_REMOTE_PASS"]);
		$adminUser = $this->options["ADMIN_USER"];
		$subUsers = array();
		if($login != $adminUser && $crtUser!=""){
			//AJXP_User::deleteUser($crtUser, $subUsers);
		}
		AuthService::disconnect();
		session_write_close();
		AJXP_XMLWriter::header();
		AJXP_XMLWriter::loggingResult(2);
		AJXP_XMLWriter::close();
	}
			
	function checkPassword($login, $pass, $seed){
		$repoId = $this->options["REPOSITORY_ID"];
    	$repoObject = ConfService::getRepositoryById($repoId);
    	if(!isSet($repoObject)) throw new Exception("Cannot find repository with id ".$repoId);
		$path = "";
		$basePath = $repoObject->getOption("PATH", true);
		$basePath = str_replace("AJXP_USER", $login, $basePath);
		$host = $repoObject->getOption("HOST");
		$url = "smb://$login:$pass@".$host."/".$basePath."/";
		try{
			if(!is_dir($url)) return false;
			$_SESSION["AJXP_SESSION_REMOTE_USER"] = $login;
			$_SESSION["AJXP_SESSION_REMOTE_PASS"] = $pass;
		}catch(Exception $e){
			return false;
		}
		return true;		
	}
	
	function usersEditable(){
		return false;
	}
	function passwordsEditable(){
		return false;
	}
	
	function createUser($login, $passwd){
	}	
	function changePassword($login, $newPass){
	}	
	function deleteUser($login){
	}

	function getUserPass($login){
		return "";
	}

}
?>