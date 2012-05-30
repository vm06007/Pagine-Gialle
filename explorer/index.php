
<?php 


ini_set('display_errors',1);
include('../igniter.php'); 
error_reporting(E_ALL);

$ignite = new Igniter();
$ignite->connect();

session_start();

if($ignite->logincheck(@$_SESSION['loggedin'], 'logon', 'password', 'useremail') == false) { echo(0);
  if(isset($_REQUEST['action']) && $_REQUEST['action'] == "login") { echo(1);
     if($ignite->login("logon", $_REQUEST['username'], $_REQUEST['password']) == true) { echo(2); 
       //do something on successful login
    } else $ignite->redirectPage('../login.php');
  } else $ignite->redirectPage('../login.php');
}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == "logout") {
 $ignite->logout();
  $ignite->redirectPage('login.php');
}

?>

<?php
/**
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
 * Description : main script called at initialisation.
 */
include_once("server/conf/base.conf.php");
require_once("server/classes/class.AJXP_Utils.php");
require_once("server/classes/class.AJXP_VarsFilter.php");
require_once("server/classes/class.SystemTextEncoding.php");
require_once("server/classes/class.HTMLWriter.php");
require_once("server/classes/class.AJXP_XMLWriter.php");
require_once("server/classes/class.Repository.php");
require_once("server/classes/class.AjxpRole.php");
require_once("server/classes/class.ConfService.php");
require_once("server/classes/class.AuthService.php");
require_once("server/classes/class.AJXP_Logger.php");
require_once("server/classes/class.AJXP_Plugin.php");
require_once("server/classes/class.AJXP_PluginsService.php");
require_once("server/classes/class.AbstractAccessDriver.php");

if(!class_exists("DOMDocument")){
        die("You must have libxml PHP extension enabled on your server.");
}
header("X-UA-Compatible: chrome=1");
HTMLWriter::charsetHeader();
$pServ = AJXP_PluginsService::getInstance();
$pServ->loadPluginsRegistry(INSTALL_PATH."/plugins", INSTALL_PATH."/server/conf");
ConfService::init("server/conf/conf.php");
$confStorageDriver = ConfService::getConfStorageImpl();
include_once($confStorageDriver->getUserClassFileName());
session_name("AjaXplorer");
session_start();

$outputArray = array();
$testedParams = array();
$passed = true;
if(!is_file(TESTS_RESULT_FILE)){
	$passed = AJXP_Utils::runTests($outputArray, $testedParams);
	if(!$passed && !isset($_GET["ignore_tests"])){
		die(AJXP_Utils::testResultsToTable($outputArray, $testedParams));
	}else{
		AJXP_Utils::testResultsToFile($outputArray, $testedParams);
	}
}

$START_PARAMETERS = array("BOOTER_URL"=>"content.php?get_action=get_boot_conf", "MAIN_ELEMENT" => "ajxp_desktop");
if(AuthService::usersEnabled())
{
	AuthService::preLogUser((isSet($_GET["remote_session"])?$_GET["remote_session"]:""));
	AuthService::bootSequence($START_PARAMETERS);
	if(AuthService::getLoggedUser() != null || AuthService::logUser(null, null) == 1)
	{
		if(AuthService::getDefaultRootId() == -1){
			AuthService::disconnect();
		}else{
			$loggedUser = AuthService::getLoggedUser();
			if(!$loggedUser->canRead(ConfService::getCurrentRootDirIndex()) 
					&& AuthService::getDefaultRootId() != ConfService::getCurrentRootDirIndex())
			{
				ConfService::switchRootDir(AuthService::getDefaultRootId());
			}
		}
	}
}

AJXP_Utils::parseApplicationGetParameters($_GET, $START_PARAMETERS, $_SESSION);

$confErrors = ConfService::getErrors();
if(count($confErrors)){
	$START_PARAMETERS["ALERT"] = implode(", ", array_values($confErrors));
}

if(isSet($_COOKIE["AJXP_LAST_KNOWN_VERSION"]) && $_COOKIE["AJXP_LAST_KNOWN_VERSION"] != AJXP_VERSION){
	$mess = ConfService::getMessages();
	$START_PARAMETERS["ALERT"] = sprintf($mess[392], AJXP_VERSION);
}
setcookie("AJXP_LAST_KNOWN_VERSION", AJXP_VERSION, time() + 3600*24*365);

$JSON_START_PARAMETERS = json_encode($START_PARAMETERS);
if(ConfService::getConf("JS_DEBUG")){
	if(!isSet($mess)){
		$mess = ConfService::getMessages();
	}
	include_once(INSTALL_PATH."/".CLIENT_RESOURCES_FOLDER."/html/gui_debug.html");
}else{
	$content = file_get_contents(INSTALL_PATH."/".CLIENT_RESOURCES_FOLDER."/html/gui.html");	
	$content = AJXP_XMLWriter::replaceAjxpXmlKeywords($content, false);
	if($JSON_START_PARAMETERS){
		$content = str_replace("//AJXP_JSON_START_PARAMETERS", "startParameters = ".$JSON_START_PARAMETERS.";", $content);
	}
	print($content);
}
?>