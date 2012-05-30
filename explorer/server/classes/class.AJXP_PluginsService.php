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
 * Description : Core parser for handling plugins.
 */
defined('AJXP_EXEC') or die( 'Access not allowed');

 class AJXP_PluginsService{
 	private static $instance;
 	private $registry = array();
 	private $required_files = array();
 	private $tmpDependencies = array();
 	private $activePlugins = array();
 	private $streamWrapperPlugins = array();
 	private $xmlRegistry;
 	private $pluginFolder;
 	private $confFolder;
 	private $mixinsDoc;
 	private $mixinsXPath;
 	
 	public function loadPluginsRegistry($pluginFolder, $confFolder){ 		
 		if(!defined("AJXP_SKIP_CACHE") || AJXP_SKIP_CACHE === false){
	 		$reqs = AJXP_Utils::loadSerialFile(AJXP_PLUGINS_REQUIRES_FILE);
	 		if(count($reqs)){
	 			foreach ($reqs as $filename){
	 				require_once($filename);
	 			}
	 			$res = AJXP_Utils::loadSerialFile(AJXP_PLUGINS_CACHE_FILE);
	 			$this->registry = $res;
	 			// Refresh streamWrapperPlugins
	 			foreach ($this->registry as $pType => $plugs){
	 				foreach ($plugs as $plugin){
	 					if(method_exists($plugin, "detectStreamWrapper") && $plugin->detectStreamWrapper(false) !== false){
	 						$this->streamWrapperPlugins[] = $plugin->getId();
	 					}
	 				}
	 			}
	 			return ;
	 		}
 		}
 		$this->pluginFolder = $pluginFolder;
 		$this->confFolder = $confFolder;
 		$handler = opendir($pluginFolder);
 		$pluginsPool = array();
 		if($handler){
 			while ( ($item = readdir($handler)) !==false) {
 				if($item == "." || $item == ".." || !is_dir($pluginFolder."/".$item) || strstr($item,".")===false) continue ;
 				$plugin = new AJXP_Plugin($item, $pluginFolder."/".$item);				
				$plugin->loadManifest();
				if($plugin->manifestLoaded()){
					$pluginsPool[$plugin->getId()] = $plugin;
					if(method_exists($plugin, "detectStreamWrapper") && $plugin->detectStreamWrapper(false) !== false){
						$this->streamWrapperPlugins[] = $plugin->getId();
					}
				}
 			}
 			closedir($handler);
 		}
 		if(count($pluginsPool)){
 			$this->checkDependencies($pluginsPool);
 			foreach ($pluginsPool as $plugin){
 				$this->recursiveLoadPlugin($plugin, $pluginsPool);
 			}
 		}
 		if(!defined("AJXP_SKIP_CACHE") || AJXP_SKIP_CACHE === false){
	 		AJXP_Utils::saveSerialFile(AJXP_PLUGINS_REQUIRES_FILE, $this->required_files, false, false);
	 		AJXP_Utils::saveSerialFile(AJXP_PLUGINS_CACHE_FILE, $this->registry, false, false);
 		}
 	}
 	
 	/**
 	 * Load plugin class with dependencies first
 	 *
 	 * @param AJXP_Plugin $plugin
 	 */
 	private function recursiveLoadPlugin($plugin, $pluginsPool){
 		if($plugin->loadingState!=""){
 			//print("<div style='padding-left:30px;'>--- Pluging ".$plugin->getId()." already loaded</div>");
 			return ;
 		}
 		//print("<div style='padding-left:30px;'>--- Loading ".$plugin->getId());
		$dependencies = $plugin->getDependencies();
		$plugin->loadingState = "lock";
		foreach ($dependencies as $dependencyId){
			if(isSet($pluginsPool[$dependencyId])){
				$this->recursiveLoadPlugin($pluginsPool[$dependencyId], $pluginsPool);
			}
		}
		$plugType = $plugin->getType();
		if(!isSet($this->registry[$plugType])){
			$this->registry[$plugType] = array();
		}
		$plugin = $this->instanciatePluginClass($plugin);
		if(is_file($this->confFolder."/conf.".$plugin->getType().".inc")){
			$plugin->loadConfig($this->confFolder."/conf.".$plugin->getType().".inc", "inc");
		}
		if(is_file($this->confFolder."/conf.".$plugin->getId().".inc")){
			$plugin->loadConfig($this->confFolder."/conf.".$plugin->getId().".inc", "inc");
		}
		$this->registry[$plugType][$plugin->getName()] = $plugin;
		$plugin->loadingState = "loaded";
		//print("</div>");
 	}
 	
 	/**
 	 * Save the plugins order in a cache
 	 *
 	 * @param array $sortedPlugins
 	 */
 	private function cachePlugSort($sortedPlugins){
 		if(!AJXP_PLUGINS_CACHE_FILE) return false;
 		$indexes = array();
 		$i = 0;
 		foreach ($sortedPlugins as $plugin){
 			$indexes[$i] = $plugin->getId();
 			$i++;
 		}
 		AJXP_Utils::saveSerialFile(AJXP_PLUGINS_CACHE_FILE, $indexes, false, true);
 	}
 	
 	/**
 	 * Load the cache, check that all registered plugins
 	 * are known by the cache, and in that case sort the array.
 	 *
 	 * @param array $sortedPlugins
 	 * @return mixed
 	 */
 	private function loadPlugCache($sortedPlugins){
 		if(!AJXP_PLUGINS_CACHE_FILE) return false;
 		$cache = AJXP_Utils::loadSerialFile(AJXP_PLUGINS_CACHE_FILE);
 		if(!count($cache)) return false;
 		// Break if one plugin is not present in cache
 		foreach ($sortedPlugins as $index => $plugin){
 			if(!in_array($plugin->getId(), $cache)){return false;}
 		}
 		$sorted = array();
 		foreach ($cache as $id => $plugId){
 			// Walk the cache and add the plugins in right order.
 			if(isSet($sortedPlugins[$plugId])){
 				$sorted[] = $sortedPlugins[$plugId];
 			}
 		} 		
 		return $sorted;
 	}
 	
 	/**
 	 * Find a PHP class and instanciate it to replace the empty AJXP_Plugin
 	 *
 	 * @param AJXP_Plugin $plugin
 	 * @return AJXP_Plugin
 	 */
 	private function instanciatePluginClass($plugin){
 		$definition = $plugin->getClassFile();
 		if(!$definition) return $plugin;
 		$filename = INSTALL_PATH."/".$definition["filename"];
 		$className = $definition["classname"];
 		if(is_file($filename)){ 			
 			require_once($filename);
 			$newPlugin = new $className($plugin->getId(), $plugin->getBaseDir());
 			$newPlugin->loadManifest();
 			$this->required_files[] = $filename;
	 		return $newPlugin;
 		}else{
 			return $plugin;
 		}
 	}
 	
 	private function checkDependencies(&$arrayToSort){
 		// First make sure that the given dependencies are present
 		foreach ($arrayToSort as $plugId => $plugObject){
 			$plugObject->updateDependencies($this);
 			$dependencies = $plugObject->getDependencies();
 			if(!count($dependencies)) return ;
 			$found = false;
 			foreach ($dependencies as $requiredPlugId){
 				if(isSet($arrayToSort[$requiredPlugId])){
 					$found = true; break;
 				}
 			}
 			if(!$found){
 				unset($arrayToSort[$plugId]);
 			}
 		}
 	}
 	/**
 	 * User function for sorting
 	 *
 	 * @param AJXP_Plugin $pluginA
 	 * @param AJXP_Plugin $pluginB
 	 */
 	private function sortByDependency($pluginA, $pluginB){
 		//var_dump("Checking " . $pluginA->getId() . " vs " . $pluginB->getId());
 		if($pluginA->getId() == $pluginB->getId()){
 			//var_dump("should not check");
 			return 0;
 		}
 		if($pluginA->dependsOn($pluginB->getId())) {
 			return 1;
 		}
 		if($pluginB->dependsOn($pluginA->getId())) {
 			return -1;
 		}
 		return 0;
 	}

	private function usort(&$tableau){
		uasort($tableau, array($this, "sortByDependency"));
		return ;
    }
	
 	public function getPluginsByType($type){
 		if(isSet($this->registry[$type])) return $this->registry[$type];
 		else return array();
 	}
 	
 	/**
 	 * Get a plugin instance
 	 *
 	 * @param string $pluginId
 	 * @return AJXP_Plugin
 	 */
 	public function getPluginById($pluginId){
 		$split = explode(".", $pluginId);
 		return $this->getPluginByTypeName($split[0], $split[1]);
 	}
 	
 	public function removePluginById($pluginId){
 		$split = explode(".", $pluginId);
 		if(isSet($this->registry[$split[0]]) && isSet($this->registry[$split[0]][$split[1]])){
 			unset($this->registry[$split[0]][$split[1]]);
 		}
 	}
 	
 	public static function setPluginActive($type, $name, $active=true){
 		self::getInstance()->setPluginActiveInst($type, $name, $active);
 	}
 	
 	public function setPluginActiveInst($type, $name, $active=true){
 		if($active){
	 		// Check active plugin dependencies
	 		$plug = $this->getPluginById($type.".".$name);
	 		$deps = $plug->getActiveDependencies($this);
	 		if(count($deps)){
	 			$found = false;
		 		foreach ($deps as $dep){
					if(isSet($this->activePlugins[$dep]) && $this->activePlugins[$dep] !== false){
						$found = true; break;
		 			}
		 		}
		 		if(!$found){
	 				$this->activePlugins[$type.".".$name] = false;
	 				return ;
		 		}
	 		}
 		}
 		$this->activePlugins[$type.".".$name] = $active;
 		if(isSet($this->xmlRegistry)){
 			$this->buildXmlRegistry();
 		}
 	}
 	
 	public function setPluginUniqueActiveForType($type, $name){
 		$typePlugs = $this->getPluginsByType($type);
 		foreach($typePlugs as $plugName => $plugObject){
 			$this->setPluginActiveInst($type, $plugName, false);
 		}
 		$this->setPluginActiveInst($type, $name, true);
 	}
 	
 	public function getActivePlugins(){
 		return $this->activePlugins;	
 	}
 	
 	public function getStreamWrapperPlugins(){
 		return $this->streamWrapperPlugins;
 	}
 	
 	public function buildXmlRegistry(){
 		$actives = $this->getActivePlugins();
 		$reg = new DOMDocument();
 		$reg->loadXML("<ajxp_registry></ajxp_registry>");
 		foreach($actives as $activeName=>$status){
 			if($status === false) continue; 			
 			$plug = $this->getPluginById($activeName);
 			$contribs = $plug->getRegistryContributions();
 			foreach($contribs as $contrib){
 				$parent = $contrib->nodeName;
 				$nodes = $contrib->childNodes;
 				if(!$nodes->length) continue;
 				$uuidAttr = $contrib->getAttribute("uuidAttr");
 				if($uuidAttr == "") $uuidAttr = "name";
 				$this->mergeNodes($reg, $parent, $uuidAttr, $nodes);
	 		}
 		}
 		$this->xmlRegistry = $reg;
 	}
 	
 	public static function getXmlRegistry(){
 		$self = self::getInstance();
 		if(!isSet($self->xmlRegistry)){
 			$self->buildXmlRegistry();
 		}
 		return $self->xmlRegistry;
 	}
 	
 	public static function updateXmlRegistry($registry){
 		$self = self::getInstance();
 		$self->xmlRegistry = $registry;
 	}
 	
 	/**
 	 * Append some predefined XML to a plugin instance 
 	 * @param AJXP_Plugin $plugin
 	 * @param DOMDocument $manifestDoc
 	 * @param String $behaviourName
 	 */
 	public function patchPluginWithMixin(&$plugin, &$manifestDoc, $mixinName){
 		
 		// Load behaviours if not already
 		if(!isSet($this->mixinsDoc)){
 			$this->mixinsDoc = new DOMDocument();
 			$this->mixinsDoc->load(AJXP_INSTALL_PATH."/server/xml/ajxp_mixins.xml");
 			$this->mixinsXPath = new DOMXPath($this->mixinsDoc);
 		}
 		// Merge into manifestDoc
 		$nodeList = $this->mixinsXPath->query($mixinName);
 		if(!$nodeList->length) return;
 		$mixinNode = $nodeList->item(0);
 		foreach($mixinNode->childNodes as $child){
 			if($child->nodeType != XML_ELEMENT_NODE) continue;
 			$uuidAttr = $child->getAttribute("uuidAttr") OR "name";
 			$this->mergeNodes($manifestDoc, $child->nodeName, $uuidAttr, $child->childNodes);
 		}

 		// Reload plugin XPath
 		$plugin->reloadXPath();
 	}
 	
 	/**
 	 * 
 	 * @param unknown_type $query
 	 * @param unknown_type $stringOrNodeFormat
 	 * @param return DOMNode[]
 	 */
 	public static function searchAllManifests($query, $stringOrNodeFormat = "string"){
 		$buffer = "";
 		$nodes = array();
 		$self = self::getInstance();
 		foreach ($self->registry as $plugType){ 			
 			foreach ($plugType as $plugName => $plugObject){
 				$res = $plugObject->getManifestRawContent($query, $stringOrNodeFormat);
 				if($stringOrNodeFormat == "string"){
	 				$buffer .= $res;
 				}else{
 					foreach ($res as $node){
 						$nodes[] = $node;
 					}
 				}
 			}
 		}
 		if($stringOrNodeFormat == "string") return $buffer;
 		else return $nodes;
 	}
 	
 	protected function mergeNodes(&$original, $parentName, $uuidAttr, $childrenNodes){
 		// find or create parent
 		$parentSelection = $original->getElementsByTagName($parentName);
 		if($parentSelection->length){
 			$parentNode = $parentSelection->item(0);
 			$xPath = new DOMXPath($original);
 			foreach($childrenNodes as $child){
 				if($child->nodeType != XML_ELEMENT_NODE) continue;
 				if($child->getAttribute($uuidAttr) == "*"){
 					$query = $parentName.'/'.$child->nodeName;
 				}else{
	 				$query = $parentName.'/'.$child->nodeName.'[@'.$uuidAttr.' = "'.$child->getAttribute($uuidAttr).'"]';
 				}
 				$childrenSel = $xPath->query($query);
 				if($childrenSel->length){
 					foreach ($childrenSel as $existingNode){
	 					// Clone as many as needed	 					
	 					$clone = $original->importNode($child, true);
	 					$this->mergeChildByTagName($clone, $existingNode);
 					}
 				}else{
 					$clone = $original->importNode($child, true);
 					$parentNode->appendChild($clone);
 				}
 			} 			
 		}else{
 			//create parentNode and append children
 			if($childrenNodes->length){
 				$parentNode = $original->importNode($childrenNodes->item(0)->parentNode, true);
 				$original->documentElement->appendChild($parentNode);
 			}else{
	 			$parentNode = $original->createElement($parentName);
	 			$original->documentElement->appendChild($parentNode);
 			}
 		}
 	}
 	
 	protected function mergeChildByTagName($new, &$old){
 		if(!$this->hasElementChild($new) || !$this->hasElementChild($old)){
 			$old->parentNode->replaceChild($new, $old); 			
 			return;
 		} 		
 		foreach($new->childNodes as $newChild){ 			
 			if($newChild->nodeType != XML_ELEMENT_NODE) continue;
 			
 			$found = null;
 			foreach($old->childNodes as $oldChild){
 				if($oldChild->nodeType != XML_ELEMENT_NODE) continue;
 				if($oldChild->nodeName == $newChild->nodeName){
 					$found = $oldChild;
 				}
 			}
 			if($found != null){
 				if($newChild->nodeName == "post_processing" || $newChild->nodeName == "pre_processing"){
 					$old->appendChild($newChild->cloneNode(true));
 				}else{
	 				$this->mergeChildByTagName($newChild, $found);
 				}
 			}else{
 				// CloneNode or it's messing with the current foreach loop.
 				$old->appendChild($newChild->cloneNode(true));
 			}
 		}
 	}
 	
 	private function hasElementChild($node){
 		if(!$node->hasChildNodes()) return false;
 		foreach($node->childNodes as $child){
 			if($child->nodeType == XML_ELEMENT_NODE) return true;
 		}
 		return false;
 	}
 	
 	public function getPluginByTypeName($plugType, $plugName){
 		if(isSet($this->registry[$plugType]) && isSet($this->registry[$plugType][$plugName])){
 			return $this->registry[$plugType][$plugName];
 		}else{
 			return false;
 		} 		
 	}
 	
 	public static function findPlugin($type, $name){
 		$instance = self::getInstance();
 		return $instance->getPluginByTypeName($type, $name);
 	}
 	
 	public static function findPluginById($id){
 		return self::getInstance()->getPluginById($id);
 	}
 	
 	private function __construct(){ 		
 	}
 	/**
 	 * Singleton method
 	 *
 	 * @return AJXP_PluginsService the service instance
 	 */
 	public static function getInstance()
 	{
 		if(!isSet(self::$instance)){
 			$c = __CLASS__;
 			self::$instance = new $c;
 		}
 		return self::$instance;
 	}
    public function __clone()
    {
        trigger_error("Cannot clone me, i'm a singleton!", E_USER_ERROR);
    } 	
 }
 ?>