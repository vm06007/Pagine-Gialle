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
 * Description : Abstract representation of an access to an authentication system (ajxp, ldap, etc).
 */
defined('AJXP_EXEC') or die( 'Access not allowed');

class AbstractAuthDriver extends AJXP_Plugin {
	
	var $options;
	var $driverName = "abstract";
	var $driverType = "auth";
					
	public function switchAction($action, $httpVars, $fileVars)	{
		if(!isSet($this->actions[$action])) return;
		$mess = ConfService::getMessages();
		
		switch ($action){			
			//------------------------------------
			//	CHANGE USER PASSWORD
			//------------------------------------	
			case "pass_change":
							
				$userObject = AuthService::getLoggedUser();
				if($userObject == null || $userObject->getId() == "guest"){
					header("Content-Type:text/plain");
					print "SUCCESS";
				}
				$oldPass = $httpVars["old_pass"];
				$newPass = $httpVars["new_pass"];
				$passSeed = $httpVars["pass_seed"];
				if(defined("AJXP_PASSWORD_MINLENGTH") && strlen($newPass) < AJXP_PASSWORD_MINLENGTH){
					header("Content-Type:text/plain");
					print "PASS_ERROR";
				}
				if(AuthService::checkPassword($userObject->getId(), $oldPass, false, $passSeed)){
					AuthService::updatePassword($userObject->getId(), $newPass);
				}else{
					header("Content-Type:text/plain");
					print "PASS_ERROR";
				}
				header("Content-Type:text/plain");
				print "SUCCESS";
				
			break;					
					
			default;
			break;
		}				
		return "";
	}
	
	
	public function getRegistryContributions(){
		$logged = AuthService::getLoggedUser();
		if(AuthService::usersEnabled()) {
			if($logged == null){
				return $this->registryContributions;
			}else{
				$xmlString = AJXP_XMLWriter::getUserXml($logged, false);
			}
		}else{
			$xmlString = AJXP_XMLWriter::getUserXml(null, false);
		}		
		$dom = new DOMDocument();
		$dom->loadXML($xmlString);
		$this->registryContributions[]=$dom->documentElement;				
		return $this->registryContributions;
	}
	
	protected function parseSpecificContributions(&$contribNode){
		parent::parseSpecificContributions($contribNode);
		if($contribNode->nodeName != "actions") return ;
		if(AuthService::usersEnabled() && $this->passwordsEditable()) return ;
		// Disable password change action
		$actionXpath=new DOMXPath($contribNode->ownerDocument);
		$passChangeNodeList = $actionXpath->query('action[@name="pass_change"]', $contribNode);
		if(!$passChangeNodeList->length) return ;
		unset($this->actions["pass_change"]);
		$passChangeNode = $passChangeNodeList->item(0);
		$contribNode->removeChild($passChangeNode);
	}
	
	function preLogUser($sessionId){}	

	function listUsers(){}
	function userExists($login){}	
	function checkPassword($login, $pass, $seed){}
	function createCookieString($login){}
	
	
	function usersEditable(){}
	function passwordsEditable(){}
	
	function createUser($login, $passwd){}	
	function changePassword($login, $newPass){}	
	function deleteUser($login){}
	
	function getLoginRedirect(){
		if(isSet($this->options["LOGIN_REDIRECT"])){
			return $this->options["LOGIN_REDIRECT"];
		}else{
			return false;
		}
	}

	function getLogoutRedirect(){
        return false;
    }
	
	function getOption($optionName){	
		return (isSet($this->options[$optionName])?$this->options[$optionName]:"");	
	}
	
	function isAjxpAdmin($login){
		return ($this->getOption("AJXP_ADMIN_LOGIN") === $login);
	}
	
	function autoCreateUser(){
		$opt = $this->getOption("AUTOCREATE_AJXPUSER");
		if($opt === true) return true;
		return false;
	}

	function getSeed($new=true){
		if($this->getOption("TRANSMIT_CLEAR_PASS") === true) return -1;
		if($new){
			$seed = md5(time());
			$_SESSION["AJXP_CURRENT_SEED"] = $seed;	
			return $seed;		
		}else{
			return (isSet($_SESSION["AJXP_CURRENT_SEED"])?$_SESSION["AJXP_CURRENT_SEED"]:0);
		}
	}	
	
	function filterCredentials($userId, $pwd){
		return array($userId, $pwd);
	}
		
}
?>