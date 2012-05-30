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
 * Description : Abstract representation of an action driver. Must be implemented.
 */
defined('AJXP_EXEC') or die( 'Access not allowed');

class AbstractAccessDriver extends AJXP_Plugin {
	
	/**
	* @var Repository
	*/
	public $repository;
	public $driverType = "access";
		
	public function init($repository, $options = null){
		//$this->loadActionsFromManifest();
		parent::init($options);
		$this->repository = $repository;
	}
	
	function initRepository(){
		// To be implemented by subclasses
	}
	
	
	function accessPreprocess($actionName, &$httpVars, &$filesVar)
	{
		if($actionName == "cross_copy"){
			$this->crossRepositoryCopy($httpVars);
			return ;
		}
		if($actionName == "ls"){
			// UPWARD COMPATIBILTY
			if(isSet($httpVars["options"])){
				if($httpVars["options"] == "al") $httpVars["mode"] = "file_list";
				else if($httpVars["options"] == "a") $httpVars["mode"] = "search";
				else if($httpVars["options"] == "d") $httpVars["skipZip"] = "true";
				// skip "complete" mode that was in fact quite the same as standard tree listing (dz)
			}
			/*
			if(!isSet($httpVars["skip_history"])){
				if(AuthService::usersEnabled() && AuthService::getLoggedUser()!=null){
					$user = AuthService::getLoggedUser();
					$user->setArrayPref("history", $this->repository->getId(), ((isSet($httpVars["dir"])&&trim($httpVars["dir"])!="")?$httpVars["dir"]:"/"));
					$user->save();
				}
			}
			*/
		}
	}

	protected function parseSpecificContributions(&$contribNode){
		parent::parseSpecificContributions($contribNode);
		if(isSet($this->actions["public_url"])){
			$disableSharing = false;
			if(PUBLIC_DOWNLOAD_FOLDER == ""){
				$disableSharing = true;
			}else if((!is_dir(PUBLIC_DOWNLOAD_FOLDER) || !is_writable(PUBLIC_DOWNLOAD_FOLDER))){
				AJXP_Logger::logAction("Disabling Public links, PUBLIC_DOWNLOAD_FOLDER is not writeable!", array("folder" => PUBLIC_DOWNLOAD_FOLDER, "is_dir" => is_dir(PUBLIC_DOWNLOAD_FOLDER),"is_writeable" => is_writable(PUBLIC_DOWNLOAD_FOLDER)));
				$disableSharing = true;
			}else{
				if(AuthService::usersEnabled()){					
					$loggedUser = AuthService::getLoggedUser();
					/*
					// Should be disabled by AJXP_Controller directly
					$currentRepo = ConfService::getRepository();
					if($currentRepo != null){
						$rights = $loggedUser->getSpecificActionsRights($currentRepo->getId());
						if(isSet($rights["public_url"]) && $rights["public_url"] === false){
							$disableSharing = true;
						}
					}
					*/
					if($loggedUser != null && $loggedUser->getId() == "guest" || $loggedUser == "shared"){
						$disableSharing = true;
					}
				}else{
					$disableSharing = true;
				}
			}
			if($disableSharing){
				unset($this->actions["public_url"]);
				$actionXpath=new DOMXPath($contribNode->ownerDocument);
				$publicUrlNodeList = $actionXpath->query('action[@name="public_url"]', $contribNode);
				$publicUrlNode = $publicUrlNodeList->item(0);
				$contribNode->removeChild($publicUrlNode);
			}
		}
		if($this->detectStreamWrapper() !== false){
			$this->actions["cross_copy"] = array();
		}
	}
	
    /** Cypher the publiclet object data and write to disk.
        @param $data The publiclet data array to write 
                     The data array must have the following keys:
                     - DRIVER      The driver used to get the file's content      
                     - OPTIONS     The driver options to be successfully constructed (usually, the user and password)
                     - FILE_PATH   The path to the file's content
                     - PASSWORD    If set, the written publiclet will ask for this password before sending the content
                     - ACTION      If set, action to perform
                     - USER        If set, the AJXP user 
                     - EXPIRE_TIME If set, the publiclet will deny downloading after this time, and probably self destruct.
        @return the URL to the downloaded file
    */
    function writePubliclet($data)
    {
    	if(!defined('PUBLIC_DOWNLOAD_FOLDER') || !is_dir(PUBLIC_DOWNLOAD_FOLDER)){
    		return "ERROR : Public URL folder does not exist!";
    	}
    	if(!function_exists("mcrypt_create_iv")){
    		return "ERROR : MCrypt must be installed to use publiclets!";
    	}
    	if($data["PASSWORD"] && !is_file(PUBLIC_DOWNLOAD_FOLDER."/allz.css")){    		
    		@copy(INSTALL_PATH."/".AJXP_THEME_FOLDER."/css/allz.css", PUBLIC_DOWNLOAD_FOLDER."/allz.css");
    		@copy(INSTALL_PATH."/".AJXP_THEME_FOLDER."/images/actions/22/dialog_ok_apply.png", PUBLIC_DOWNLOAD_FOLDER."/dialog_ok_apply.png");
    		@copy(INSTALL_PATH."/".AJXP_THEME_FOLDER."/images/actions/16/public_url.png", PUBLIC_DOWNLOAD_FOLDER."/public_url.png");    		
    	}
    	if(!is_file(PUBLIC_DOWNLOAD_FOLDER."/index.html")){
    		@copy(INSTALL_PATH."/server/index.html", PUBLIC_DOWNLOAD_FOLDER."/index.html");
    	}
        $data["PLUGIN_ID"] = $this->id;
        $data["BASE_DIR"] = $this->baseDir;
        $data["REPOSITORY"] = $this->repository;
        if(AuthService::usersEnabled()){
        	$data["OWNER_ID"] = AuthService::getLoggedUser()->getId();
        }
        if($this->hasMixin("credentials_consumer")){
        	$cred = AJXP_Safe::tryLoadingCredentialsFromSources(array(), $this->repository);
        	if(isSet($cred["user"]) && isset($cred["password"])){
        		$data["SAFE_USER"] = $cred["user"];
        		$data["SAFE_PASS"] = $cred["password"];        		
        	}
        }
        // Force expanded path in publiclet
        $data["REPOSITORY"]->addOption("PATH", $this->repository->getOption("PATH"));
        if ($data["ACTION"] == "") $data["ACTION"] = "download";
        // Create a random key
        $data["FINAL_KEY"] = md5(mt_rand().time());
        // Cypher the data with a random key
        $outputData = serialize($data);
        // Hash the data to make sure it wasn't modified
        $hash = md5($outputData);
        // The initialisation vector is only required to avoid a warning, as ECB ignore IV
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
        // We have encoded as base64 so if we need to store the result in a database, it can be stored in text column
        $outputData = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $hash, $outputData, MCRYPT_MODE_ECB, $iv));
        // Okay, write the file:
        $fileData = "<"."?"."php \n".
        '   require_once("'.str_replace("\\", "/", INSTALL_PATH).'/publicLet.inc.php"); '."\n".
        '   $id = str_replace(".php", "", basename(__FILE__)); '."\n". // Not using "" as php would replace $ inside
        '   $cypheredData = base64_decode("'.$outputData.'"); '."\n".
        '   $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND); '."\n".
        '   $inputData = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $id, $cypheredData, MCRYPT_MODE_ECB, $iv));  '."\n".
        '   if (md5($inputData) != $id) { header("HTTP/1.0 401 Not allowed, script was modified"); exit(); } '."\n".
        '   // Ok extract the data '."\n".
        '   $data = unserialize($inputData); AbstractAccessDriver::loadPubliclet($data); ?'.'>';
        if (@file_put_contents(PUBLIC_DOWNLOAD_FOLDER."/".$hash.".php", $fileData) === FALSE){
            return "Can't write to PUBLIC URL";
        }
        require_once(INSTALL_PATH."/server/classes/class.PublicletCounter.php");
        PublicletCounter::reset($hash);
        if(defined('PUBLIC_DOWNLOAD_URL') && PUBLIC_DOWNLOAD_URL != ""){
        	return rtrim(PUBLIC_DOWNLOAD_URL, "/")."/".$hash.".php";
        }else{
	        $http_mode = (!empty($_SERVER['HTTPS'])) ? 'https://' : 'http://';
	        $fullUrl = $http_mode . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);    
	        return str_replace("\\", "/", $fullUrl.rtrim(str_replace(INSTALL_PATH, "", PUBLIC_DOWNLOAD_FOLDER), "/")."/".$hash.".php");
        }
    }

    /** Load a uncyphered publiclet */
    function loadPubliclet($data)
    {
        // create driver from $data
        $className = $data["DRIVER"]."AccessDriver";
        if ($data["EXPIRE_TIME"] && time() > $data["EXPIRE_TIME"])
        {
            // Remove the publiclet, it's done
            if (strstr(realpath($_SERVER["SCRIPT_FILENAME"]),realpath(PUBLIC_DOWNLOAD_FOLDER)) !== FALSE){
		        $hash = md5(serialize($data));
		        require_once(INSTALL_PATH."/server/classes/class.PublicletCounter.php");
		        PublicletCounter::delete($hash);
                unlink($_SERVER["SCRIPT_FILENAME"]);
            }
            
            echo "Link is expired, sorry.";
            exit();
        }
        // Check password
        if (strlen($data["PASSWORD"]))
        {
            if (!isSet($_POST['password']) || ($_POST['password'] != $data["PASSWORD"]))
            {   
            	$content = file_get_contents(INSTALL_PATH."/client/html/public_links.html");
            	$language = "en";
            	if(isSet($_GET["lang"])){
            		$language = $_GET["lang"];
            	}
            	$messages = array();
            	if(is_file(INSTALL_PATH."/client/i18n/$language.php")){
            		include(INSTALL_PATH."/client/i18n/$language.php");
            		$messages = $mess;
            	}            	
				if(preg_match_all("/AJXP_MESSAGE(\[.*?\])/", $content, $matches, PREG_SET_ORDER)){
					foreach($matches as $match){
						$messId = str_replace("]", "", str_replace("[", "", $match[1]));
						if(isSet($messages[$messId])) $content = str_replace("AJXP_MESSAGE[$messId]", $messages[$messId], $content);
					}
				}
				echo $content;
                exit(1);
            }
        }
        $filePath = INSTALL_PATH."/plugins/access.".$data["DRIVER"]."/class.".$className.".php";
        if(!is_file($filePath)){
                die("Warning, cannot find driver for conf storage! ($className, $filePath)");
        }
        require_once($filePath);        
        $driver = new $className($data["PLUGIN_ID"], $data["BASE_DIR"]);
        $driver->loadManifest();        
        if($driver->hasMixin("credentials_consumer") && isSet($data["SAFE_USER"]) && isSet($data["SAFE_PASS"])){
        	// FORCE SESSION MODE
        	AJXP_Safe::getInstance()->forceSessionCredentialsUsage();
        	AJXP_Safe::storeCredentials($data["SAFE_USER"], $data["SAFE_PASS"]);
        }
        $driver->init($data["REPOSITORY"], $data["OPTIONS"]);
        ConfService::setRepository($data["REPOSITORY"]);
        $driver->initRepository();
        // Increment counter
        $hash = md5(serialize($data));
        require_once(INSTALL_PATH."/server/classes/class.PublicletCounter.php");
        PublicletCounter::increment($hash);       
        // Now call switchAction 
        //@todo : switchAction should not be hard coded here!!!
        // Re-encode file-path as it will be decoded by the action.
        try{
	        $driver->switchAction($data["ACTION"], array("file"=>SystemTextEncoding::toUTF8($data["FILE_PATH"])), "");
        }catch (Exception $e){
        	die($e->getMessage());
        }
    }

    /** Create a publiclet object, that will be saved in PUBLIC_DOWNLOAD_FOLDER
	/* Typically, the class will simply create a data array, and call return writePubliclet($data)
    /* @param String $filePath The path to the file to share
    /* @param String $password optionnal password
    /* @param String $expires optional expiration date        
	/* @return The full public URL to the publiclet.
    */
    function makePubliclet($filePath, $password, $expires) {}
    
    function makeSharedRepositoryOptions($httpVars){}

    function createSharedRepository($httpVars){
		// ERRORS
		// 100 : missing args
		// 101 : repository label already exists
		// 102 : user already exists
		// 103 : current user is not allowed to share
		// SUCCESS
		// 200
    	
		if(!isSet($httpVars["repo_label"]) || $httpVars["repo_label"] == "" 
			||  !isSet($httpVars["repo_rights"]) || $httpVars["repo_rights"] == ""
			||  !isSet($httpVars["shared_user"]) || $httpVars["shared_user"] == ""){
			return 100;
		}
		$loggedUser = AuthService::getLoggedUser();
		$actRights = $loggedUser->getSpecificActionsRights($this->repository->id);
		if(isSet($actRights["public_url"]) && $actRights["public_url"] === false){
			return 103;
		}
		$dir = AJXP_Utils::decodeSecureMagic($httpVars["dir"]);
		$userName = AJXP_Utils::decodeSecureMagic($httpVars["shared_user"], AJXP_SANITIZE_ALPHANUM);
		$label = AJXP_Utils::decodeSecureMagic($httpVars["repo_label"]);
		$rights = $httpVars["repo_rights"];
		if($rights != "r" && $rights != "rw") return 100;
		// CHECK USER & REPO DOES NOT ALREADY EXISTS
		$repos = ConfService::getRepositoriesList();
		foreach ($repos as $obj){
			if($obj->getDisplay() == $label){
				return 101;
			}
		}		
		$confDriver = ConfService::getConfStorageImpl();
		if(AuthService::userExists($userName)){
			// check that it's a child user
			$userObject = $confDriver->createUserObject($userName);
			if(!$userObject->hasParent() || $userObject->getParent() != $loggedUser->id){
				return 102;
			}
		}else{
			if(!isSet($httpVars["shared_pass"]) || $httpVars["shared_pass"] == "") return 100;
			AuthService::createUser($userName, md5($httpVars["shared_pass"]));
			$userObject = $confDriver->createUserObject($userName);
			$userObject->clearRights();
			$userObject->setParent($loggedUser->id);			
		}
		
		// CREATE SHARED OPTIONS		
		$newRepo = $this->repository->createSharedChild(
			$label, 
			$this->makeSharedRepositoryOptions($httpVars), 
			$this->repository->id, 
			$loggedUser->id, 
			$userName
		);
		ConfService::addRepository($newRepo);
						
		// CREATE USER WITH NEW REPO RIGHTS
		$userObject->setRight($newRepo->getUniqueId(), $rights);
		$userObject->setSpecificActionRight($newRepo->getUniqueId(), "public_url", false);
		$userObject->save();
		
    	return 200;
    }
       
    function crossRepositoryCopy($httpVars){
    	
    	ConfService::detectRepositoryStreams(true);
    	$mess = ConfService::getMessages();
		$selection = new UserSelection();
		$selection->initFromHttpVars($httpVars);
    	$files = $selection->getFiles();
    	
    	$accessType = $this->repository->getAccessType();    	
    	$repositoryId = $this->repository->getId();
    	$origStreamURL = "ajxp.$accessType://$repositoryId";    	
    	
    	$destRepoId = $httpVars["dest_repository_id"];
    	$destRepoObject = ConfService::getRepositoryById($destRepoId);
    	$destRepoAccess = $destRepoObject->getAccessType();
    	$destStreamURL = "ajxp.$destRepoAccess://$destRepoId";
    	
    	// Check rights
    	if(AuthService::usersEnabled()){
	    	$loggedUser = AuthService::getLoggedUser();
	    	if(!$loggedUser->canRead($repositoryId) || !$loggedUser->canWrite($destRepoId)
	    		|| (isSet($httpVars["moving_files"]) && !$loggedUser->canWrite($repositoryId))
	    	){
	    		throw new Exception($mess[364]);
	    	}
    	}
    	
    	$messages = array();
    	foreach ($files as $file){
    		$origFile = $origStreamURL.$file;
    		$destFile = $destStreamURL.SystemTextEncoding::fromUTF8($httpVars["dest"])."/".basename($file);    		
    		if(!is_file($origFile)){
    			throw new Exception("Cannot find $origFile");
    		}
			$origHandler = fopen($origFile, "r");
			$destHandler = fopen($destFile, "w");
			if($origHandler === false || $destHandler === false) {
				$errorMessages[] = AJXP_XMLWriter::sendMessage(null, $mess[114]." ($origFile to $destFile)", false);
				continue;
			}
			while(!feof($origHandler)){
				fwrite($destHandler, fread($origHandler, 4096));
			}
			fflush($destHandler);
			fclose($origHandler); 
			fclose($destHandler);			
			$messages[] = $mess[34]." ".SystemTextEncoding::toUTF8(basename($origFile))." ".(isSet($httpVars["moving_files"])?$mess[74]:$mess[73])." ".SystemTextEncoding::toUTF8($destFile);
    	}
    	AJXP_XMLWriter::header();    	
    	if(count($errorMessages)){
    		AJXP_XMLWriter::sendMessage(null, join("\n", $errorMessages), true);
    	}
    	AJXP_XMLWriter::sendMessage(join("\n", $messages), null, true);
    	AJXP_XMLWriter::close();
    }
    
    /**
     * 
     * Try to reapply correct permissions
     * @param oct $mode
     * @param Repository $repoObject
     * @param Function $remoteDetectionCallback
     */
    public static function fixPermissions(&$stat, $repoObject, $remoteDetectionCallback = null){
    	
        $fixPermPolicy = $repoObject->getOption("FIX_PERMISSIONS");    	
    	$loggedUser = AuthService::getLoggedUser();
    	if($loggedUser == null){
    		return;
    	}
    	$sessionKey = md5($repoObject->getId()."-".$loggedUser->getId()."-fixPermData");

    	
    	if(!isSet($_SESSION[$sessionKey])){			
    	    if($fixPermPolicy == "detect_remote_user_id" && $remoteDetectionCallback != null){
    	    	list($uid, $gid) = call_user_func($remoteDetectionCallback, $repoObject);
    	    	if($uid != null && $gid != null){
    	    		$_SESSION[$sessionKey] = array("uid" => $uid, "gid" => $gid);
    	    	} 
		    	
	    	}else if(substr($fixPermPolicy, 0, strlen("file:")) == "file:"){
	    		$filePath = AJXP_VarsFilter::filter(substr($fixPermPolicy, strlen("file:")));
	    		if(file_exists($filePath)){
	    			// GET A GID/UID FROM FILE
	    			$lines = file($filePath);
	    			foreach($lines as $line){
	    				$res = explode(":", $line);
	    				if($res[0] == $loggedUser->getId()){
	    					$uid = $res[1];
	    					$gid = $res[2];
	    					$_SESSION[$sessionKey] = array("uid" => $uid, "gid" => $gid);
	    					break;
	    				}
	    			}
	    		}
	    	}
	    	// If not set, set an empty anyway
	    	if(!isSet($_SESSION[$sessionKey])){
	    		$_SESSION[$sessionKey] = array(null, null);
	    	}
    		
    	}else{
    		$data = $_SESSION[$sessionKey];
    		if(!empty($data)){
    			if(isSet($data["uid"])) $uid = $data["uid"];
    			if(isSet($data["gid"])) $gid = $data["gid"];
    		}    		
    	}
	    	
    	$p = $stat["mode"];
    	$st = sprintf("%07o", ($p & 7777770));
    	AJXP_Logger::debug("FIX PERM DATA ($fixPermPolicy, $st)".$p,sprintf("%o", ($p & 000777)));
    	if($p != NULL){
	    	if( ( isSet($uid) && $stat["uid"] == $uid ) || $fixPermPolicy === "u"  ) {
    			AJXP_Logger::debug("upgrading abit to ubit");
    			$p  = $p&7777770;
    			if( $p&0x0100 ) $p += 04;
	    		if( $p&0x0080 ) $p += 02;
	    		if( $p&0x0040 ) $p += 01;
	    	}else if( ( isSet($gid) && $stat["gid"] == $gid )  || $fixPermPolicy === "g"  ) {
	    		AJXP_Logger::debug("upgrading abit to gbit");
    			$p  = $p&7777770;
	    		if( $p&0x0020 ) $p += 04;
	    		if( $p&0x0010 ) $p += 02;
	    		if( $p&0x0008 ) $p += 01;
	    	}	    	
			$stat["mode"] = $stat[2] = $p;
    		AJXP_Logger::debug("FIXED PERM DATA ($fixPermPolicy)",sprintf("%o", ($p & 000777)));
    	}
    }
    
    protected function resetAllPermission($value){
    	
    }

}

?>
