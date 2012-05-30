<?php
/**
 * @package info.ajaxplorer
 * 
 * Copyright 2007-2010 Charles du Jeu
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
 *	ADVANCED : DO NOT CHANGE THESE VARIABLES BELOW
 */
if(function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get")){
	@date_default_timezone_set(@date_default_timezone_get());
}

@error_reporting(E_ALL & ~E_NOTICE);
//Windows users may have to uncomment this
//setlocale(LC_ALL, '');


ini_set("session.cookie_httponly", 1);

define("AJXP_VERSION", "3.2.4");
define("AJXP_VERSION_DATE", "2011/06/08");

define("AJXP_EXEC", true);
require("compat.php");
$installPath = realpath(dirname(__FILE__)."/../..");
define("INSTALL_PATH", $installPath);
define("AJXP_INSTALL_PATH", $installPath);
define("AJXP_CACHE_DIR", AJXP_INSTALL_PATH."/server/cache");
define("AJXP_PLUGINS_CACHE_FILE", AJXP_CACHE_DIR."/plugins_cache.ser");
define("AJXP_PLUGINS_REQUIRES_FILE", AJXP_CACHE_DIR."/plugins_requires.ser");
define("SERVER_ACCESS", "content.php");
define("ADMIN_ACCESS", "admin.php");
define("IMAGES_FOLDER", "client/themes/oxygen/images");
define("CLIENT_RESOURCES_FOLDER", "client");
define("AJXP_THEME_FOLDER", "client/themes/oxygen");
define("SERVER_RESOURCES_FOLDER", "server/classes");
define("DOCS_FOLDER", "client/doc");
define("TESTS_RESULT_FILE", $installPath."/server/conf/diag_result.php");
define("AJXP_SKIP_CACHE", false);


define("INITIAL_ADMIN_PASSWORD", "admin");

define("SOFTWARE_UPDATE_SITE", "http://www.ajaxplorer.info/update/");

function AjaXplorer_autoload($className){
	$fileName = AJXP_INSTALL_PATH."/".SERVER_RESOURCES_FOLDER."/"."class.".$className.".php";
	if(file_exists($fileName)){
		require_once($fileName);
	}
}
spl_autoload_register('AjaXplorer_autoload');

?>