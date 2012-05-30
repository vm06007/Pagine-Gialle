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

class sqlConfDriver extends AbstractConfDriver {
		
	
	/**
	 * Initialise the driver.
	 * 
	 * Expects options containing a key 'SQL_DRIVER' with constructor values from dibi::connect()
	 * 
	 * Example:
	 * 		"SQL_DRIVER" => Array(
	 *		'driver' => 'sqlite',
	 *			'file' => "./server/ajxp.db"
	 *		)
	 *
	 * Example 2:
	 * 		"SQL_DRIVER" => Array(
	 * 		'driver' => 'mysql',
	 * 		'host' => 'localhost',
	 * 		'username' => 'root',
	 * 		'password' => '***',
	 * 		'database' => 'dbname'
	 * 		)
	 * 
	 * @see server/classes/AbstractConfDriver#init($options)
	 */
	function init($options){
		parent::init($options);
		require_once(INSTALL_PATH."/server/classes/dibi.compact.php");		
		$this->sqlDriver = $options["SQL_DRIVER"];
		try {
			dibi::connect($this->sqlDriver);		
		} catch (DibiException $e) {
			echo get_class($e), ': ', $e->getMessage(), "\n";
			exit(1);
		}
	}
	
	/**
	 * Create a Repository object from a Database Result
	 * 
	 * The method expects the following schema:
	 * CREATE TABLE ajxp_repo ( uuid VARCHAR(33) PRIMARY KEY, 
	 * 							path VARCHAR(255), 
	 * 							display VARCHAR(255), 
	 * 							accessType VARCHAR(20), 
	 * 							recycle VARCHAR(255) , 
	 * 							bcreate BOOLEAN, -- For some reason 'create' is a reserved keyword
	 * 							writeable BOOLEAN, 
	 * 							enabled BOOLEAN );
	 * 
	 * Additionally, the options are stored in a separate table:
	 * CREATE TABLE ajxp_repo_options ( oid INTEGER PRIMARY KEY, uuid VARCHAR(33), name VARCHAR(50), val VARCHAR(255) );
	 * 
	 * I recommend an index to increase performance of uuid lookups:
	 * CREATE INDEX ajxp_repo_options_uuid_idx ON ajxp_repo_options ( uuid );
	 * 
	 * 
	 * @param $result Result of a dibi::query() as array
	 * @param $options_result Result of dibi::query() for options as array
	 * @return Repository object
	 */
	function repoFromDb($result, $options_result = Array())
	{
		$repo = new Repository($result['id'], $result['display'], $result['accessType']);
		$repo->uuid = $result['uuid'];
		$repo->setOwnerData($result['parent_uuid'], $result['owner_user_id'], $result['child_user_id']);
		$repo->path = $result['path'];
		$repo->create = $result['bcreate'];
		$repo->writeable = $result['writeable'];
		$repo->writeable = true;
		$repo->enabled = $result['enabled'];
		$repo->recycle = "";
		$repo->setSlug($result['slug']);
		
		foreach ($options_result as $k => $v) {
			if($k == "META_SOURCES"){
				$v = unserialize($v);
			}
			$repo->options[$k] = $v;
		}
		
		return $repo;
	}
	
	/**
	 * Convert a repository object to an array, which will be stored in the database.
	 * 
	 * @param $repository Repository
	 * @return Array containing row values, and another array with the key "options" to be stored as repo options.
	 */
	function repoToArray($repository)
	{

		$repository_row = Array(
				'uuid' => $repository->getUniqueId(),
				'parent_uuid' => $repository->getParentId(), 
				'owner_user_id' => $repository->getOwner(), 
				'child_user_id' => $repository->getUniqueUser(), 
				'path' => $repository->options['PATH'],
				'display' => $repository->getDisplay(),
				'accessType' => $repository->getAccessType(),
				'recycle' => $repository->recycle, 
				'bcreate' => $repository->getCreate(),
				'writeable' => $repository->isWriteable(),
				'enabled' => $repository->isEnabled(),
				'options' => $repository->options,
				'slug'		=> $repository->getSlug()
		);
		
		return $repository_row;
	}
	
	
	/**
	 * Get a list of repositories
	 * 
	 * The list is an associative array of Array( 'uuid' => [Repository Object] );
	 * 
	 * @todo Create a repository object that lazy loads options, so that these list queries don't incur the multiple queries of options.
	 * @see server/classes/AbstractConfDriver#listRepositories()
	 */
	function listRepositories(){

		$res = dibi::query('SELECT * FROM [ajxp_repo]');
		$all = $res->fetchAll();
		
		$repositories = Array();
		
		foreach ($all as $repo_row) {

			$res_opts = dibi::query('SELECT * FROM [ajxp_repo_options] WHERE [uuid] = %s', $repo_row['uuid']);
			$opts = $res_opts->fetchPairs('name', 'val');
			$repo = $this->repoFromDb($repo_row, $opts);
						
			$repositories[$repo->getUniqueId()] = $repo;	
		}
		
		return $repositories;
	}
	
	/**
	 * Get repository by Unique ID (a hash calculated from the serialised object).
	 *
	 * @param String $repositoryId hash uuid
	 * @return Repository object
	 */
	function getRepositoryById($repositoryId){
		$res = dibi::query('SELECT * FROM [ajxp_repo] WHERE [uuid] = %s', $repositoryId);
		
		if (count($res) > 0) {
			$repo_row = $res->fetchSingle();
			$res_opts = dibi::query('SELECT * FROM [ajxp_repo_options] WHERE [uuid] = %s', $repo_row['uuid']);
			$opts = $res_opts->fetchPairs('name', 'val');
			$repository = $this->repoFromDb($repo_row, $opts);	
			return $repository;
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
		$res = dibi::query('SELECT * FROM [ajxp_repo] WHERE [slug] = %s', $repositorySlug);
		
		if (count($res) > 0) {
			$repo_row = $res->fetchSingle();
			$res_opts = dibi::query('SELECT * FROM [ajxp_repo_options] WHERE [uuid] = %s', $repo_row['uuid']);
			$opts = $res_opts->fetchPairs('name', 'val');
			$repository = $this->repoFromDb($repo_row, $opts);	
			return $repository;
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
		try {
				$repository_array = $this->repoToArray($repositoryObject);
				$options = $repository_array['options'];
				unset($repository_array['options']);
			if (!$update) {
				dibi::query('INSERT INTO [ajxp_repo]', $repository_array);

				foreach ($options as $k => $v ) {
					if($k == "META_SOURCES"){
						$v = serialize($v);
					}
					dibi::query('INSERT INTO [ajxp_repo_options]', 
						Array(
							'uuid' => $repositoryObject->getUniqueId(),
							'name' => $k,
							'val' => $v
						)
					);
				}
				/*
				//set maximum rights to the repositorie's creator jcg
				$user_right['login'] = $_SESSION["AJXP_USER"]->id;
				$user_right['repo_uuid'] = $repository_array['uuid'];
				$user_right['rights'] = 'rw';
				dibi::query('INSERT INTO [ajxp_user_rights]', $user_right);
				$userid=$_SESSION["AJXP_USER"]->id;
				*/
				
			} else {
				dibi::query('DELETE FROM [ajxp_repo] WHERE [uuid] = %s',$repositoryObject->getUniqueId());
				dibi::query('DELETE FROM [ajxp_repo_options] WHERE [uuid] = %s',$repositoryObject->getUniqueId());
				dibi::query('INSERT INTO [ajxp_repo]', $repository_array);
				foreach ($options as $k => $v ) {
					if($k == "META_SOURCES"){
						$v = serialize($v);
					}
					dibi::query('INSERT INTO [ajxp_repo_options]', 
						Array(
							'uuid' => $repositoryObject->getUniqueId(),
							'name' => $k,
							'val' => $v
						)
					);
				}
			}
		
		} catch (DibiException $e) {
			
			echo get_class($e), ': ', $e->getMessage(), "\n";
			exit(1);
			
			return -1;
		}
	}
	/**
	 * Delete a repository, given its unique ID.
	 *
	 * @param String $repositoryId
	 */	
	function deleteRepository($repositoryId){
		try {
			$result = dibi::query('DELETE FROM [ajxp_repo] WHERE [uuid] = %s', $repositoryId);
			$result_opts = dibi::query('DELETE FROM [ajxp_repo_options] WHERE [uuid] = %s', $repositoryId);
			$result_opts_rights = dibi::query('DELETE FROM [ajxp_user_rights] WHERE [repo_uuid] = %s',$repositoryId); //jcg

			
		} catch (DibiException $e) {
			return -1;
		}
		
		// Deleting a non-existent repository also qualifies as an error jcg Call to a member function getAffectedRows() on a non-object 
		/*
		if (false === $result->getAffectedRows()) {
			return -1;
		}
		*/
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
	
	/**
	 * Get the full path to the Ajxp user class.
	 * 
	 * @see server/classes/AbstractConfDriver#getUserClassFileName()
	 */
	function getUserClassFileName(){
		return INSTALL_PATH."/plugins/conf.sql/class.AJXP_User.php";
	}	
	
	
	function listRoles(){
		
		$res = dibi::query('SELECT * FROM [ajxp_roles]');
		$all = $res->fetchAll();
		
		$roles = Array();
		
		foreach ($all as $role_row) {
			$id = $role_row['role_id'];
			$serialized = $role_row['serial_role'];
			$object = unserialize($serialized);
			if(is_a($object, "AjxpRole")){
				$roles[$id] = $object;
			}
		}
		
		return $roles;
		
	}
	
	function saveRoles($roles){
		dibi::query("DELETE FROM [ajxp_roles]");
		foreach ($roles as $roleId => $roleObject){
			dibi::query("INSERT INTO [ajxp_roles]", array(
				'role_id' => $roleId, 
				'serial_role' => serialize($roleObject))
				);
		}
	}
	
	function countAdminUsers(){
		$rows = dibi::query("SELECT [login] FROM ajxp_user_rights WHERE [repo_uuid] = %s AND [rights] = %s", "ajxp.admin", "1");
		return count($rows);
	}
	
}
?>