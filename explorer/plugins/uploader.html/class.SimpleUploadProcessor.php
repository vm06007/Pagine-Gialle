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
 * Description : Class for handling flex upload
 */
defined('AJXP_EXEC') or die( 'Access not allowed');

class SimpleUploadProcessor extends AJXP_Plugin {
	
	public function getDropBg($action, $httpVars, $fileVars){
		$lang = ConfService::getLanguage();
		$img = INSTALL_PATH."/plugins/uploader.html/i18n/$lang-dropzone.png";
		if(!is_file($img)) $img = INSTALL_PATH."/plugins/uploader.html/i18n/en-dropzone.png";
		header("Content-Type: image/png; name=\"dropzone.png\"");
		header("Content-Length: ".filesize($img));
		header('Cache-Control: public');
		readfile($img);
	}
	
	public function preProcess($action, &$httpVars, &$fileVars){
		if(!isSet($httpVars["input_stream"])){
			return false;
		}
		//AJXP_Logger::debug("SimpleUpload::preProcess", $httpVars);
				
	    $headersCheck = (
	        // basic checks
	        isset(
	            //$_SERVER['CONTENT_TYPE'],
	            $_SERVER['CONTENT_LENGTH'],
	            $_SERVER['HTTP_X_FILE_SIZE'],
	            $_SERVER['HTTP_X_FILE_NAME']
	        ) &&
	        //$_SERVER['CONTENT_TYPE'] === 'multipart/form-data' &&
	        $_SERVER['CONTENT_LENGTH'] === $_SERVER['HTTP_X_FILE_SIZE']
	    );
	    $fileNameH = $_SERVER['HTTP_X_FILE_NAME'];
	    $fileSizeH = $_SERVER['HTTP_X_FILE_SIZE'];		
	       
	    if($headersCheck){
	        // create the object and assign property
        	$fileVars["userfile_0"] = array(
        		"input_upload" => true,
        		"name"		   => SystemTextEncoding::fromUTF8(basename($fileNameH)),
        		"size"		   => $fileSizeH
        	);
	    }else{
	    	exit("Warning, missing headers!");
	    }
	}
	
	public function postProcess($action, $httpVars, $postProcessData){
		if(!isSet($httpVars["simple_uploader"]) && !isSet($httpVars["xhr_uploader"])){
			return false;
		}
		AJXP_Logger::debug("SimpleUploadProc is active");
		$result = $postProcessData["processor_result"];
		
		if(isSet($httpVars["simple_uploader"])){	
			print("<html><script language=\"javascript\">\n");
			if(isSet($result["ERROR"])){
				$message = $result["ERROR"]["MESSAGE"]." (".$result["ERROR"]["CODE"].")";
				print("\n if(parent.ajaxplorer.actionBar.multi_selector) parent.ajaxplorer.actionBar.multi_selector.submitNext('".str_replace("'", "\'", $message)."');");		
			}else{		
				print("\n if(parent.ajaxplorer.actionBar.multi_selector) parent.ajaxplorer.actionBar.multi_selector.submitNext();");
			}
			print("</script></html>");
		}else{
			if(isSet($result["ERROR"])){
				$message = $result["ERROR"]["MESSAGE"]." (".$result["ERROR"]["CODE"].")";
				exit($message);
			}else{
				exit("OK");
			}
		}
		
	}	
	
	public function unifyChunks($action, $httpVars, $fileVars){
		$repository = ConfService::getRepository();
		if(!$repository->detectStreamWrapper(false)){
			return false;
		}
		$plugin = AJXP_PluginsService::findPlugin("access", $repository->getAccessType());
		$streamData = $plugin->detectStreamWrapper(true);		
		$dir = AJXP_Utils::decodeSecureMagic($httpVars["dir"]);
    	$destStreamURL = $streamData["protocol"]."://".$repository->getId().$dir."/";    	
		$filename = AJXP_Utils::decodeSecureMagic($httpVars["file_name"]);
		$chunks = array();
		$index = 0;
		while(isSet($httpVars["chunk_".$index])){
			$chunks[] = AJXP_Utils::decodeSecureMagic($httpVars["chunk_".$index]);
			$index++;
		}
		
		$newDest = fopen($destStreamURL.$filename, "w");
		for ($i = 0; $i < count($chunks) ; $i++){
			$part = fopen($destStreamURL.$chunks[$i], "r");
			while(!feof($part)){
				fwrite($newDest, fread($part, 4096));
			}
			fclose($part);
			unlink($destStreamURL.$chunks[$i]);
		}
		fclose($newDest);
		
	}
}
?>
