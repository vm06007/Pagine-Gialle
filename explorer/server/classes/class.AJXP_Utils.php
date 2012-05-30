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
 * Description : various utils methods.
 */
defined('AJXP_EXEC') or die( 'Access not allowed');

define('AJXP_SANITIZE_HTML', 1);
define('AJXP_SANITIZE_HTML_STRICT', 2);
define('AJXP_SANITIZE_ALPHANUM', 3);

class AJXP_Utils
{
	
	/**
	* Performs a natural sort on the array keys. 
	* Behaves the same as ksort() with natural sorting added. 
	* 
	* @param Array $array The array to sort 
	*/  
	static function natksort(&$array) {
		uksort($array, 'strnatcasecmp');
		return true;
	}

	/**
	* Performs a reverse natural sort on the array keys 
	* Behaves the same as krsort() with natural sorting added. 
	* 
	* @param Array $array The array to sort 
	*/  
	static function natkrsort(&$array) {
		natksort($array);
		$array = array_reverse($array,TRUE);
		return true;
	}

	static function securePath($path)
	{
		if($path == null) $path = ""; 
		//
		// REMOVE ALL "../" TENTATIVES
		//
		$dirs = explode('/', $path);
		for ($i = 0; $i < count($dirs); $i++)
		{
			if ($dirs[$i] == '.' or $dirs[$i] == '..')
			{
				$dirs[$i] = '';
			}
		}
		// rebuild safe directory string
		$path = implode('/', $dirs);
		
		//
		// REPLACE DOUBLE SLASHES
		//
		while (preg_match('/\/\//', $path)) 
		{
			$path = str_replace('//', '/', $path);
		}
		return $path;
	}
	
    public static function sanitize($s , $level = AJXP_SANITIZE_HTML, $expand = 'script|style|noframes|select|option'){
        /**///prep the string
        $s = ' ' . $s;
        if($level == AJXP_SANITIZE_ALPHANUM){
        	return preg_replace("/[^a-zA-Z0-9_\-\.]/", "", $s);
        }
        
        //begin removal
        /**///remove comment blocks
        while(stripos($s,'<!--') > 0){
            $pos[1] = stripos($s,'<!--');
            $pos[2] = stripos($s,'-->', $pos[1]);
            $len[1] = $pos[2] - $pos[1] + 3;
            $x = substr($s,$pos[1],$len[1]);
            $s = str_replace($x,'',$s);
        }
        
        /**///remove tags with content between them
        if(strlen($expand) > 0){
            $e = explode('|',$expand);
            for($i=0;$i<count($e);$i++){
                while(stripos($s,'<' . $e[$i]) > 0){
                    $len[1] = strlen('<' . $e[$i]);
                    $pos[1] = stripos($s,'<' . $e[$i]);
                    $pos[2] = stripos($s,$e[$i] . '>', $pos[1] + $len[1]);
                    $len[2] = $pos[2] - $pos[1] + $len[1];
                    $x = substr($s,$pos[1],$len[2]);
                    $s = str_replace($x,'',$s);
                }
            }
        }
        
        $s = strip_tags($s);
        if($level == AJXP_SANITIZE_HTML_STRICT){
        	  $s = preg_replace("/[\",;\/`<>:\*\|\?!\^\\\]/", "", $s);
        }else{
	        $s = str_replace(array("<", ">"), array("&lt;", "&gt;"), $s);
        }
        return trim($s);
    }	
	
	public static function decodeSecureMagic($data, $sanitizeLevel = AJXP_SANITIZE_HTML){
		return SystemTextEncoding::fromUTF8(AJXP_Utils::sanitize(AJXP_Utils::securePath(SystemTextEncoding::magicDequote($data)), $sanitizeLevel));
	}
	
	public static function getAjxpTmpDir(){
		if(defined("AJXP_TMP_DIR") && AJXP_TMP_DIR != ""){
			return AJXP_TMP_DIR;
		}
		return realpath(sys_get_temp_dir());
	}
	
	static function parseFileDataErrors($boxData)
	{
		$mess = ConfService::getMessages();
		$userfile_error = $boxData["error"];		
		$userfile_tmp_name = $boxData["tmp_name"];
		$userfile_size = $boxData["size"];
		if ($userfile_error != UPLOAD_ERR_OK)
		{
			$errorsArray = array();
			$errorsArray[UPLOAD_ERR_FORM_SIZE] = $errorsArray[UPLOAD_ERR_INI_SIZE] = array(409,"File is too big! Max is".ini_get("upload_max_filesize"));
			$errorsArray[UPLOAD_ERR_NO_FILE] = array(410,"No file found on server!");
			$errorsArray[UPLOAD_ERR_PARTIAL] = array(410,"File is partial");
			$errorsArray[UPLOAD_ERR_INI_SIZE] = array(410,"No file found on server!");
			if($userfile_error == UPLOAD_ERR_NO_FILE)
			{
				// OPERA HACK, do not display "no file found error"
				if(!ereg('Opera',$_SERVER['HTTP_USER_AGENT']))
				{
					return $errorsArray[$userfile_error];				
				}
			}
			else
			{
				return $errorsArray[$userfile_error];
			}
		}
		if ($userfile_tmp_name=="none" || $userfile_size == 0)
		{
			return array(410,$mess[31]);
		}
		return null;
	}
	
	public static function parseApplicationGetParameters($parameters, &$output, &$session){
		$output["EXT_REP"] = "/";
		
		if(isSet($parameters["repository_id"]) && isSet($parameters["folder"])){
			$repository = ConfService::getRepositoryById($parameters["repository_id"]);
			if($repository == null){
				$repository = ConfService::getRepositoryByAlias($parameters["repository_id"]);
				if($repository != null){
					$parameters["repository_id"] = $repository->getId();
				}			
			}
			require_once("server/classes/class.SystemTextEncoding.php");
			if(AuthService::usersEnabled()){
				$loggedUser = AuthService::getLoggedUser();
				if($loggedUser!= null && $loggedUser->canSwitchTo($parameters["repository_id"])){			
					$output["EXT_REP"] = SystemTextEncoding::toUTF8(urldecode($parameters["folder"]));
					$loggedUser->setArrayPref("history", "last_repository", $parameters["repository_id"]);
					$loggedUser->setPref("pending_folder", SystemTextEncoding::toUTF8(AJXP_Utils::decodeSecureMagic($parameters["folder"])));
					$loggedUser->save();
					AuthService::updateUser($loggedUser);
				}else{
					$session["PENDING_REPOSITORY_ID"] = $parameters["repository_id"];
					$session["PENDING_FOLDER"] = SystemTextEncoding::toUTF8(AJXP_Utils::decodeSecureMagic($parameters["folder"]));
				}
			}else{
				ConfService::switchRootDir($parameters["repository_id"]);
				$output["EXT_REP"] = SystemTextEncoding::toUTF8(urldecode($parameters["folder"]));
			}
		}
		
		
		if(isSet($parameters["skipDebug"])) {
			ConfService::setConf("JS_DEBUG", false);
		}
		if(ConfService::getConf("JS_DEBUG") && isSet($parameters["compile"])){
			require_once(SERVER_RESOURCES_FOLDER."/class.AJXP_JSPacker.php");
			AJXP_JSPacker::pack();
		}		
		if(ConfService::getConf("JS_DEBUG") && isSet($parameters["update_i18n"])){
			AJXP_Utils::updateI18nFiles((isSet($parameters["plugin_path"])?$parameters["plugin_path"]:""));
		}
		if(ConfService::getConf("JS_DEBUG") && isSet($parameters["clear_plugins_cache"])){
			@unlink(AJXP_PLUGINS_CACHE_FILE);
			@unlink(AJXP_PLUGINS_REQUIRES_FILE);
		}
		
		if(isSet($parameters["external_selector_type"])){
			$output["SELECTOR_DATA"] = array("type" => $parameters["external_selector_type"], "data" => $parameters);
		}
		
		if(isSet($parameters["skipIOS"])){
			setcookie("SKIP_IOS", "true");
		}
	}
	
	
	static function removeWinReturn($fileContent)
	{
		$fileContent = str_replace(chr(10), "", $fileContent);
		$fileContent = str_replace(chr(13), "", $fileContent);
		return $fileContent;
	}
		
	static function mimetype($fileName,$mode, $isDir)
	{
		$mess = ConfService::getMessages();
		$fileName = strtolower($fileName);
		include(INSTALL_PATH."/server/conf/extensions.conf.php");
		if($isDir){
			$mime = $RESERVED_EXTENSIONS["folder"];
		}else{
			foreach ($EXTENSIONS as $ext){
				if(preg_match("/\.$ext[0]$/", $fileName)){
					$mime = $ext;
				}
			}
		}
		if(!isSet($mime)){
			$mime = $RESERVED_EXTENSIONS["unkown"];
		}
		if(is_numeric($mime[2])){
			$mime[2] = $mess[$mime[2]];
		}
		return (($mode == "image"? $mime[1]:$mime[2]));
	}
		
	static function getAjxpMimes($keyword){
		if($keyword == "editable"){
			// Gather editors!
			$pServ = AJXP_PluginsService::getInstance();
			$plugs = $pServ->getPluginsByType("editor");
			//$plugin = new AJXP_Plugin();
			$mimes = array();
			foreach ($plugs as $plugin){
				$node = $plugin->getManifestRawContent("/editor/@mimes", "node");
				$openable = $plugin->getManifestRawContent("/editor/@openable", "node");
				if($openable->item(0) && $openable->item(0)->value == "true" && $node->item(0)) {
					$mimestring = $node->item(0)->value;
					$mimesplit = explode(",",$mimestring);
					foreach ($mimesplit as $value){
						$mimes[$value] = $value;
					}
				}
			}
			return implode(",", array_values($mimes));
		}else if($keyword == "image"){
			return "png,bmp,jpg,jpeg,gif";
		}else if($keyword == "audio"){
			return "mp3";
		}else if($keyword == "zip"){
			if(ConfService::zipEnabled()){
				return "zip,ajxp_browsable_archive";
			}else{
				return "none_allowed";
			}
		}
		return "";
	}
		
	static function is_image($fileName)
	{
		if(preg_match("/\.png$|\.bmp$|\.jpg$|\.jpeg$|\.gif$/i",$fileName)){
			return 1;
		}
		return 0;
	}
	
	static function is_mp3($fileName)
	{
		if(preg_match("/\.mp3$/i",$fileName)) return 1;
		return 0;
	}
	
	static function getImageMimeType($fileName)
	{
		if(preg_match("/\.jpg$|\.jpeg$/i",$fileName)){return "image/jpeg";}
		else if(preg_match("/\.png$/i",$fileName)){return "image/png";}	
		else if(preg_match("/\.bmp$/i",$fileName)){return "image/bmp";}	
		else if(preg_match("/\.gif$/i",$fileName)){return "image/gif";}	
	}
	
	static function getStreamingMimeType($fileName){
		if(preg_match("/\.mp3$/i",$fileName)){return "audio/mp3";}
		else if (preg_match("/\.wav$/i",$fileName)){return "audio/wav";}
		else if (preg_match("/\.aac$/i",$fileName)){return "audio/aac";}
		else if (preg_match("/\.m4a$/i",$fileName)){return "audio/m4a";}
		else if (preg_match("/\.aiff$/i",$fileName)){return "audio/aiff";}
		else if (preg_match("/\.mp4$/i",$fileName)){return "video/mp4";}
		else if (preg_match("/\.mov$/i",$fileName)){return "video/quicktime";}
		else if (preg_match("/\.m4v$/i",$fileName)){return "video/m4v";}
		else if (preg_match("/\.3gp$/i",$fileName)){return "video/3gpp";}
		else if (preg_match("/\.3g2$/i",$fileName)){return "video/3gpp2";}
		else return false;
	}
	
	static function roundSize($filesize)
	{
		$mess = ConfService::getMessages();
		$size_unit = $mess["byte_unit_symbol"];
		if($filesize < 0){
			$filesize = sprintf("%u", $filesize);
		}
		if ($filesize >= 1073741824) {$filesize = round($filesize / 1073741824 * 100) / 100 . " G".$size_unit;}
		elseif ($filesize >= 1048576) {$filesize = round($filesize / 1048576 * 100) / 100 . " M".$size_unit;}
		elseif ($filesize >= 1024) {$filesize = round($filesize / 1024 * 100) / 100 . " K".$size_unit;}
		else {$filesize = $filesize . " ".$size_unit;}
		if($filesize==0) {$filesize="-";}
		return $filesize;
	}
		
	static function isHidden($fileName){
		return (substr($fileName,0,1) == ".");
	}
	
	static function isBrowsableArchive($fileName){
		return preg_match("/\.zip$/i",$fileName);
	}
	
	/**
	 * Convert a shorthand byte value from a PHP configuration directive to an integer value
	 * @param    string   $value
	 * @return   int
	 */
	static function convertBytes( $value ) 
	{
	    if ( is_numeric( $value ) ) 
	    {
	        return $value;
	    } 
	    else 
	    {
	        $value_length = strlen( $value );
	        $qty = substr( $value, 0, $value_length - 1 );
	        $unit = strtolower( substr( $value, $value_length - 1 ) );
	        switch ( $unit ) 
	        {
	            case 'k':
	                $qty *= 1024;
	                break;
	            case 'm':
	                $qty *= 1048576;
	                break;
	            case 'g':
	                $qty *= 1073741824;
	                break;
	        }
	        return $qty;
	    }
	}

	static function xmlEntities($string, $toUtf8=false){
		$xmlSafe = str_replace(array("&", "<",">", "\""), array("&amp;", "&lt;","&gt;", "&quot;"), $string);
		if($toUtf8){
			return SystemTextEncoding::toUTF8($xmlSafe);
		}else{
			return $xmlSafe;
		}
	}

	/**
	 * Modifies a string to remove all non ASCII characters and spaces.
	 */
	static public function slugify($text)
	{
		if(empty($text)) return "";
	    // replace non letter or digits by -
	    $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
	 
	    // trim
	    $text = trim($text, '-');
	 
	    // transliterate
	    if (function_exists('iconv'))
	    {
	        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
	    }
	 
	    // lowercase
	    $text = strtolower($text);
	 
	    // remove unwanted characters
	    $text = preg_replace('~[^-\w]+~', '', $text);
	 
	    if (empty($text))
	    {
	        return 'n-a';
	    }
	 
	    return $text;
	}	
	
	static function updateI18nFiles($pluginPath = ""){
		if($pluginPath != ""){
			$baseDir = INSTALL_PATH."/plugins/".$pluginPath;
			$filenames = glob($baseDir."/*.php");
		}else{
			$baseDir = INSTALL_PATH."/".CLIENT_RESOURCES_FOLDER."/i18n";
			$languages = ConfService::listAvailableLanguages();
			$filenames = array();
			foreach ($languages as $key => $value){
				$filenames[] = $baseDir."/".$key.".php";
			}
		}
		include($baseDir."/en.php");
		$reference = $mess;
		foreach ($filenames as $filename){
			//$filename = $baseDir."/".$key.".php";
			include($filename);
			$missing = array();
			foreach ($reference as $messKey=>$message){
				if(!array_key_exists($messKey, $mess)){
					$missing[] = "\"$messKey\" => \"$message\",";
				}
			}
			//print_r($missing);
			if(count($missing)){
				$header = array();
				$currentMessages = array();
				$footer = array();
				$fileLines = file($filename);
				foreach ($fileLines as $line){
					if(strstr($line, "\"") !== false){
						$currentMessages[] = trim($line);
					}else{
						if(!count($currentMessages)){
							$header[] = trim($line);
						}else{
							$footer[] = trim($line);
						}
					}
				}
				$currentMessages = array_merge($header, $currentMessages, $missing, $footer);
				file_put_contents($filename, join("\n", $currentMessages));
			}
		}
	}
	
	static function testResultsToTable($outputArray, $testedParams, $showSkipLink = true){
		$style = '
		<style>
		body {
		background-color:#fff;
		margin:0;
		padding:20;
		text-align:center;
		}
		* {font-family:arial, sans-serif;font-size:11px;color:#000}
		table{margin:0px auto;}
		h1 {font-size: 20px; color:#676965;background: url("client/themes/oxygen/images/ICON.png") no-repeat;height: 20px;padding: 8px 36px;text-align: left;margin: 0px auto;width: 300px;}
		thead tr{background-color: #ccc; font-weight:bold;}
		tr:nth-child(even){background-color: #f4f4f4;}
		td {border:1px solid #eee;padding:5px;}
		div.titre {width:700px; text-align:left; font-size: 16px;margin:6px auto;padding-top:14px;padding-left:5px; font-weight:bold;}
		div.passed thead tr{background-color: #ae9;}
		div.error thead tr{background-color: #ea9;}
		div.warning thead tr{background-color: #f90;}
		
		div.passed div.titre{color: #060;}
		div.error div.titre{color: #933;}
		div.warning div.titre{color: #f90;}
		div.detail,p {width:700px;margin:0px auto;text-align:left;}
		
		td.col{font-weight: bold;}
		td.tdName, td:nth-child(1){width:200px;}
		</style>
		';
		$htmlHead = "<html><head><title>AjaXplorer : Diagnostic Tool</title>$style</head><body><h1>AjaXplorer Diagnostic Tool</h1>";
		if($showSkipLink){
			$htmlHead .= "<p>The diagnostic tool detected some errors or warning : you are likely to have problems running AjaXplorer!</p>";
		}
		$tableHeader = "<table width='700' border='0' cellpadding='0' cellspacing='0'><thead><tr><td class='tdName'>Name</td><td class='tdInfo'>Info</td></tr></thead>"; 
		$tableFooter = "</table>";		
		$html = "";
		$dumpRows = "";
		$passedRows = array();
		$warnRows = "";
		$errRows = "";
		$errs = $warns = 0;
		foreach($outputArray as $item)
		{
		    // A test is output only if it hasn't succeeded (doText returned FALSE)
		    $result = $item["result"] ? "passed" : ($item["level"] == "info" ? "dump" : ($item["level"]=="warning"? "warning":"failed"));
		    $success = $result == "passed";    
		    $row = "<tr class='$result'><td class='col'>".$item["name"]."</td><td>".(!$success ? $item["info"] : "")."&nbsp;</td></tr>";
		    if($result == "dump"){
		    	$dumpRows .= $row;
		    }else if($result == "passed"){
		    	$passedRows []= str_replace("\n", "", $item["name"]);
		    
		    }else if($item["level"] == "warning"){
		    	$warnRows .= $row;
		    	$warns ++;
		    }else{
		    	$errRows .= $row;
		    	$errs ++;
		    }
		}
		if(strlen($errRows)){
			$html .= '<div class="error"><div class="titre">Failed Tests</div>'.$tableHeader.$errRows.$tableFooter.'</div>';
		}
		if(strlen($warnRows)){
			$html .= '<div class="warning"><div class="titre">Warnings</div>'.$tableHeader.$warnRows.$tableFooter.'</div>';
		}
		if(strlen($dumpRows)){
			$html .= '<div class="dumped"><div class="titre">Server Info</div>'.$tableHeader.$dumpRows.$tableFooter.'</div>';
		}		
		if(count($passedRows)){
			$html .= '<div class="passed"><div class="titre">Other Tests Passed</div><div class="detail">'.implode(", ", $passedRows).'</div></div>';
		}
		if($showSkipLink){
			if(!$errs){
				$htmlHead .= "<p>STATUS : You have some warning, but no fatal error, AjaXplorer should run ok, <a href='index.php?ignore_tests=true'>click here to continue to AjaXplorer!</a> (this test won't be launched anymore)</p>";
			}else{
				$htmlHead .= "<p>STATUS : You have some errors that may prevent AjaXplorer from running. Please check the red lines to see what action you should do. If you are confident enough and know that your usage of AjaXplorer does not need these errors to fixed, <a href='index.php?ignore_tests=true'>continue here to Ajaxplorer!.</a></p>";
			}
		}
		$html.="</body></html>";
		return $htmlHead.nl2br($html);
	}
	
	static function runTests(&$outputArray, &$testedParams){
		// At first, list folder in the tests subfolder
		chdir(INSTALL_PATH.'/server/tests');
		$files = glob('*.php'); 
		
		$outputArray = array();
		$testedParams = array();
		$passed = true;
		foreach($files as $file)
		{
		    require_once($file);
		    // Then create the test class
		    $testName = str_replace(".php", "", substr($file, 5));
		    $class = new $testName();
		    
		    $result = $class->doTest();
		    if(!$result && $class->failedLevel != "info") $passed = false;
		    $outputArray[] = array(
		    	"name"=>$class->name, 
		    	"result"=>$result, 
		    	"level"=>$class->failedLevel, 
		    	"info"=>$class->failedInfo); 
		   	if(count($class->testedParams)){
			    $testedParams = array_merge($testedParams, $class->testedParams);
		   	}
		}
		
        // PREPARE REPOSITORY LISTS
        $repoList = array();
        require_once("../classes/class.ConfService.php");
        require_once("../classes/class.Repository.php");
        include("../conf/conf.php");
        foreach($REPOSITORIES as $index => $repo){
            $repoList[] = ConfService::createRepositoryFromArray($index, $repo);
        }        
        // Try with the serialized repositories
        if(is_file("../conf/repo.ser")){
            $fileLines = file("../conf/repo.ser");
            $repos = unserialize($fileLines[0]);
            $repoList = array_merge($repoList, $repos);
        }
		
		// NOW TRY THE PLUGIN TESTS
		chdir(INSTALL_PATH.'/server/tests/plugins');
		$files = glob('*.php'); 
		foreach($files as $file)
		{
		    require_once($file);
		    // Then create the test class
		    $testName = str_replace(".php", "", substr($file, 5))."Test";
		    $class = new $testName();
		    foreach ($repoList as $repository){
			    $result = $class->doRepositoryTest($repository);
			    if($result === false || $result === true){			    	
				    if(!$result && $class->failedLevel != "info") $passed = false;
				    $outputArray[] = array(
				    	"name"=>$class->name . "\n Testing repository : ".$repository->getDisplay(), 
				    	"result"=>$result, 
				    	"level"=>$class->failedLevel, 
				    	"info"=>$class->failedInfo); 				    
				   	if(count($class->testedParams)){
					    $testedParams = array_merge($testedParams, $class->testedParams);
				   	}
			    }
		    }
		}
		
		return $passed;
	}	
	
	static function testResultsToFile($outputArray, $testedParams){
		ob_start();
		echo '$diagResults = ';
		var_export($testedParams);
		echo ';';
		echo '$outputArray = ';
		var_export($outputArray);
		echo ';';
		$content = '<?php '.ob_get_contents().' ?>';
		ob_end_clean();
		//print_r($content);
		file_put_contents(TESTS_RESULT_FILE, $content);		
	}
	
	/**
	 * Load an array stored serialized inside a file.
	 *
	 * @param String $filePath Full path to the file
	 * @return Array
	 */
	static function loadSerialFile($filePath){
		$filePath = AJXP_VarsFilter::filter($filePath);
		$result = array();
		if(is_file($filePath))
		{
			$fileLines = file($filePath);
			$result = unserialize($fileLines[0]);
		}
		return $result;
	}
	
	/**
	 * Stores an Array as a serialized string inside a file.
	 *
	 * @param String $filePath Full path to the file
	 * @param Array $value The value to store
	 * @param Boolean $createDir Whether to create the parent folder or not, if it does not exist.
	 */
	static function saveSerialFile($filePath, $value, $createDir=true, $silent=false){
		$filePath = AJXP_VarsFilter::filter($filePath);
		if($createDir && !is_dir(dirname($filePath))) {			
			if(!is_writeable(dirname(dirname($filePath)))){
				if($silent) return ;
				else throw new Exception("[AJXP_Utils::saveSerialFile] Cannot write into ".dirname(dirname($filePath)));
			}
			mkdir(dirname($filePath));
		}
		try {
			$fp = fopen($filePath, "w");
			fwrite($fp, serialize($value));
			fclose($fp);
		}catch (Exception $e){
			if($silent) return ;
			else throw $e;
		}
	}
	
	
	
	public static function userAgentIsMobile(){
		$isMobile = false;
		
		$op = strtolower($_SERVER['HTTP_X_OPERAMINI_PHONE'] OR "");
		$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
		$ac = strtolower($_SERVER['HTTP_ACCEPT']);		
		$isMobile = strpos($ac, 'application/vnd.wap.xhtml+xml') !== false
		        || $op != ''
		        || strpos($ua, 'sony') !== false 
		        || strpos($ua, 'symbian') !== false 
		        || strpos($ua, 'nokia') !== false 
		        || strpos($ua, 'samsung') !== false 
		        || strpos($ua, 'mobile') !== false
		        || strpos($ua, 'windows ce') !== false
		        || strpos($ua, 'epoc') !== false
		        || strpos($ua, 'opera mini') !== false
		        || strpos($ua, 'nitro') !== false
		        || strpos($ua, 'j2me') !== false
		        || strpos($ua, 'midp-') !== false
		        || strpos($ua, 'cldc-') !== false
		        || strpos($ua, 'netfront') !== false
		        || strpos($ua, 'mot') !== false
		        || strpos($ua, 'up.browser') !== false
		        || strpos($ua, 'up.link') !== false
		        || strpos($ua, 'audiovox') !== false
		        || strpos($ua, 'blackberry') !== false
		        || strpos($ua, 'ericsson,') !== false
		        || strpos($ua, 'panasonic') !== false
		        || strpos($ua, 'philips') !== false
		        || strpos($ua, 'sanyo') !== false
		        || strpos($ua, 'sharp') !== false
		        || strpos($ua, 'sie-') !== false
		        || strpos($ua, 'portalmmm') !== false
		        || strpos($ua, 'blazer') !== false
		        || strpos($ua, 'avantgo') !== false
		        || strpos($ua, 'danger') !== false
		        || strpos($ua, 'palm') !== false
		        || strpos($ua, 'series60') !== false
		        || strpos($ua, 'palmsource') !== false
		        || strpos($ua, 'pocketpc') !== false
		        || strpos($ua, 'smartphone') !== false
		        || strpos($ua, 'rover') !== false
		        || strpos($ua, 'ipaq') !== false
		        || strpos($ua, 'au-mic,') !== false
		        || strpos($ua, 'alcatel') !== false
		        || strpos($ua, 'ericy') !== false
		        || strpos($ua, 'up.link') !== false
		        || strpos($ua, 'vodafone/') !== false
		        || strpos($ua, 'wap1.') !== false
		        || strpos($ua, 'wap2.') !== false;
	/*
	      		$isBot = false;		
	      		$ip = $_SERVER['REMOTE_ADDR'];
	      		
		        $isBot =  $ip == '66.249.65.39' 
		        || strpos($ua, 'googlebot') !== false 
		        || strpos($ua, 'mediapartners') !== false 
		        || strpos($ua, 'yahooysmcm') !== false 
		        || strpos($ua, 'baiduspider') !== false
		        || strpos($ua, 'msnbot') !== false
		        || strpos($ua, 'slurp') !== false
		        || strpos($ua, 'ask') !== false
		        || strpos($ua, 'teoma') !== false
		        || strpos($ua, 'spider') !== false 
		        || strpos($ua, 'heritrix') !== false 
		        || strpos($ua, 'attentio') !== false 
		        || strpos($ua, 'twiceler') !== false 
		        || strpos($ua, 'irlbot') !== false 
		        || strpos($ua, 'fast crawler') !== false                        
		        || strpos($ua, 'fastmobilecrawl') !== false 
		        || strpos($ua, 'jumpbot') !== false
		        || strpos($ua, 'googlebot-mobile') !== false
		        || strpos($ua, 'yahooseeker') !== false
		        || strpos($ua, 'motionbot') !== false
		        || strpos($ua, 'mediobot') !== false
		        || strpos($ua, 'chtml generic') !== false
		        || strpos($ua, 'nokia6230i/. fast crawler') !== false;
	*/
		return $isMobile;
	}	
	
	public static function userAgentIsIOS(){
		if(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "iphone") !== false) return true;
		if(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "ipad") !== false) return true;
		return false;	
	}
	
	public static function silentUnlink($file){
		@unlink($file);
	}
	
}

?>