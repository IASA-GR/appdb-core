<?php
/**
 * Copyright (C) 2015 IASA - Institute of Accelerating Systems and Applications (http://www.iasa.gr)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and 
 * limitations under the License.
 */

class Storage {
	CONST DEFAULT_DRAFT_FOLDER = '/tmp/appdb/uploads/';
	CONST DEFAULT_STORE_FOLDER = '/storage/';
	CONST DEFAULT_DRAFT_URL = '/storage/drafts/';
	CONST DEFAULT_STORE_URL = '/storage/{group}/';
	CONST DEFAULT_GROUP_FOLDER = 'misc';
	CONST DEFAULT_LOG_FOLDER = '/storage/{group}/log/';
	CONST DEFAULT_DELETED_FOLDER = '/storage/{group}/deleted/';
	
	/*
	 * Escape filename for fs and http security issues.
	 * Eg replace '.. ../ \/ /\ < > />' with '_'
	 */
	public static function escapeFileName($filename){
		$filename = trim($filename);
		$escfilename = preg_replace('/(\\<\\/)+|(\\\\\\/+)|(\\/\\\\+)|(\\.{2,}[\\/\\\\]*)|(\\/+)|(\\\\+)|(\\?+)|(\\;+)|(\\s+)|(\\<+)|(\\>+)|(\\\'+)|(\\"+)/i', '_',$filename);
		if( $escfilename === null )
		{
			return $filename;
		}
		
		return $escfilename;
	}
	
	/*
	 * Will delete past draft folders.
	 * Offset is how many past days to ignore.
	 * Eg. if offset 4 it will remove all draft folders 
	 * before 4 days ago.
	 * 
	 * The current and previous day draft folder will never 
	 * be removed by this function.
	 */
	public static function clearDrafts($offset=1){
		$exclude = array( Storage::getDraftFolder(false, false) );
		
		for($i=0; $i<$offset; $i+=1)
		{
			$exclude[] = Storage::getPreviousDraftFolder($i+1, false);
		}
		
		$folders = scandir(Storage::DEFAULT_DRAFT_FOLDER);
		foreach( $folders as $folder){
			$fpath = Storage::DEFAULT_DRAFT_FOLDER . $folder;
			if ($folder === '.' ||
				$folder === '..' ||
				is_dir($fpath) === FALSE
			)
			{
				continue;
			}
			
			if( in_array($folder, $exclude) === FALSE )
			{
				Storage::removeDraftFolder(Storage::DEFAULT_DRAFT_FOLDER . $folder);
			}
		}
	}
	
	/*
	 * Will remove the given folder/path as long it is under the draft folder.
	 * 
	 * USed by Storage::clearDrafts function.
	 */
	private static function removeDraftFolder($folder){
		if( in_array(trim($folder), array("",".","..","/","../","./") )	||
			strpos($folder, "../") > -1 ||
			Storage::isInDraft($folder) === false
		)
		{
			return array();
		}
		
		if( !file_exists($folder) )
		{
			return array();
		}
		
		$iter = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($folder, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST,
			RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
		);
		
		foreach ($iter as $path => $dir)
		{
			if ( $dir->isFile() ) 
			{
				unlink($path);
			} 
			else if( $dir->isDir() )
			{
				Storage::removeDraftFolder($path);
			}			
		}
		
		if( count( scandir( $folder ) ) == 2)
		{
			rmdir($folder);
		}
	}
	
	/*
	 * CHecks if given path is in draft folder.
	 */
	public static function isInDraft($path){
		$path = trim($path);
		if(strpos($path, Storage::DEFAULT_DRAFT_FOLDER ) == 0 )
		{
			return true;
		}
		return false;
	}
	
	public static function uniqSmallId($alphabet='0123456789abcdefghijklmnopqrstuvwxyz')
	{
		$id = intval( (time() * 1000) + rand(1,999) );
		$base = strlen($alphabet);
		$short = '';
		
		while($id) {
			$id = ($id-($r=$id%$base))/$base;     
			$short = $alphabet{$r} . $short;
		}
		
		return $short;
	}
	
	/*
	 * Get the file path of the draft storage folder
	 */		
	public static function getDraftFolder($create = false, $fullpath = true )
	{
		if( !is_bool($fullpath) )
		{
			$fullpath = true;
		}
		$path = Storage::DEFAULT_DRAFT_FOLDER . date('Y') . '_' . date('m') . '_' . date('d') . DIRECTORY_SEPARATOR;
		
		if( $create === true && !file_exists($path))
		{
			@mkdir($path, 0777, true);
		}
		
		if( $fullpath === false )
		{
			return date('Y') . '_' . date('m') . '_' . date('d');
		}
		
		return $path;
	}
	
	/*
	 * Draft folders are organized by date. The cuurent function 
	 * returns the draft folder of previous days.
	 * 
	 * By default it retruns the draft folder of the previous day.
	 * The offset parameter indicates the folder of days in the past.
	 * eg. offset = 3 retruns the filepath of 3 days old draft folder.
	 */
	public static function getPreviousDraftFolder($offset = 1, $fullpath = true)
	{
		$offset = intval($offset);
		if( !is_numeric($offset) || $offset < 1)
		{
			$offset = 1;
		}
		
		if( !is_bool($fullpath) )
		{
			$fullpath = true;
		}
		
		$offday = '-' . $offset . ' day';
		$offtime = strtotime($offday);
		$folder = date('Y', $offtime) . '_' . date('m', $offtime) . '_' . date('d', $offtime);
		if( $fullpath === true )
		{
			$folder = Storage::DEFAULT_DRAFT_FOLDER . $folder . DIRECTORY_SEPARATOR;
		}
		return $folder; 
	}
	
	/*
	 * Get the file path of the target storage folder
	 */
	public static function getStoreFolder($create = false)
	{
		$path = dirname(dirname(__FILE__)) . Storage::DEFAULT_STORE_FOLDER;
		if( $create === true && !file_exists($path) )
		{
			@mkdir($path, 0777, true);
		}
		return $path;
	}
	
	/*
	 * Get folder full path based on given group.
	 * Parameter group can be a text
	 *	contextscript:
	 *		{guid}:  contextscript guid
	 *	occo script: (only for infrastructure occo scripts, NOT node)
	 *		{guid}: occo script guid 
	 * 
	 */
	public static function getGroupFolderTemplate($group=null)
	{
		$group = ( (trim($group)==="")?DEFAULT_GROUP_FOLDER:trim($group) );
		return $group .DIRECTORY_SEPARATOR . '{guid}' . DIRECTORY_SEPARATOR;
	}
	
	public static function getLogFolderTemplate()
	{
		return dirname(dirname(__FILE__)) . Storage::DEFAULT_LOG_FOLDER;
	}
	
	public static function getDeletedFolderTemplate()
	{
		return dirname(dirname(__FILE__)) . Storage::DEFAULT_DELETED_FOLDER;
	}
	
	/*
	 * Get access url template based on file group (eg contextscript, occo, misc)
	 */
	public static function getGroupUrlTemplate($group=null)
	{
		$group = ($group===null)?'misc':$group;
		
		$template = Storage::DEFAULT_STORE_URL . '{guid}/{filename}';
		$template = str_ireplace('{group}',$group, $template);
		
		return 'http://' . $_SERVER['APPLICATION_UI_HOSTNAME'] . $template;
	}
	
	/*
	 * Get root draft url
	 */
	public static function getLocalDomain()
	{
		return $_SERVER['APPLICATION_UI_HOSTNAME'] . Storage::DEFAULT_DRAFT_URL; 
	}
	
	/*
	 * CHeck if url points to the drafts folder
	 */
	public static function isLocalUrl($url) 
	{
		$u = preg_replace('/^http(s){0,1}\:\/\//i', '', $url);
		$ld = strtolower(Storage::getLocalDomain());
		
		return (substr(strtolower($u), 0, strlen($ld)) === $ld);
	}
	
	/*
	 * Construct draft file path from the given draft url
	 */
	public static function getDraftPathFromUrl($url)
	{
		$u = preg_replace('/^http(s){0,1}\:\/\//i','', $url);
		$u = str_ireplace(self::getLocalDomain(), Storage::getDraftFolder() , $u);
		
		return $u;
	}
	
	/*
	 * Construct draft url from the given draft file path
	 */
	public static function getDraftUrlFromPath($path)
	{
		$p = str_ireplace(Storage::getDraftFolder(), '/', $path);
		$p = 'http://' . Storage::getLocalDomain() . $p;
		
		return $p;
	}
	
	/*
	 * Get the final storage fs path where the file is included
	 */
	public static function getTargetFolder($group, $data = array())
	{
		$template = Storage::getGroupFolderTemplate($group);
		
		foreach($data as $k=>$v)
		{
			if( is_array($v) )
			{
				continue;
			}
			$template = str_ireplace('{' . $k . '}', $v, $template);
		}
		
		return Storage::getStoreFolder() . $template;
	}
	
	/*
	 * Get the final storage url where the file is included
	 */
	public static function getTargetUrl($group, $data = array())
	{
		$template = Storage::getGroupUrlTemplate($group);
		foreach($data as $k=>$v)
		{
			if( is_array($v) )
			{
				continue;
			}
			$template = str_ireplace('{' . $k . '}', $v, $template);
		}
		
		return $template;
	}
	
	public static function getLogFolder($group, $data = array())
	{
		$group = ($group===null)?'misc':$group;
		
		$template = Storage::getLogFolderTemplate();
		$template = str_ireplace('{group}', $group, $template);
		
		foreach($data as $k=>$v)
		{
			if( is_array($v) )
			{
				continue;
			}
			$template = str_ireplace('{' . $k . '}', $v, $template);
		}
		
		return $template;
	}
	
	public static function getDeletedFolder($group, $data = array())
	{
		$group = ($group===null)?'misc':$group;
		
		$template = Storage::getDeletedFolderTemplate();
		$template = str_ireplace('{group}', $group, $template);
		
		foreach($data as $k=>$v)
		{
			if( is_array($v) )
			{
				continue;
			}
			$template = str_ireplace('{' . $k . '}', $v, $template);
		}
		
		return $template;
	}	
	
	/*
	 * Retrieves local file information.
	 * data must contain filename key
	 */
	public static function getStoreFileData($group, $data=array())
	{
		$path = Storage::getTargetFolder($group, $data) . Storage::getFilenameFromPath(Storage::getTargetUrl($group, $data));
		
		if( !file_exists($path) )
		{
			return null;
		}
		
		$file_contents = file_get_contents($path);
		
		$filesize = strlen( $file_contents );
		if( $filesize === 0 ){
			return null;
		}
		
		$md5 = md5($file_contents);
		$file_name = trim( Storage::getFilenameFromPath($path) );
		
		return array (
			"name" => $file_name,
			"md5" => $md5,
			"data" => $file_contents,
			"size"=> $filesize,
			"path"=> $path
		);
	}
	
	/*
	 * Searches in drafts folders the specific file
	 * with span of two days.
	 * The specific time span is for the case when 
	 * a draft file is stored during day transition.
	 */
	public static function locateDraftFile($filepath)
	{
		$file = Storage::getDraftFolder() . $filepath;
		if( !file_exists($file) )
		{
			$file = Storage::getPreviousDraftFolder(1) . $filepath;
		}
		if( !file_exists($file) )
		{
			return false;
		}
		
		return $file;
		
	}
	
	/*
	 * Save previous file in store by prefixing date time
	 */
	public static function archiveFile($filepath)
	{
		if( !file_exists($filepath) )
		{
			return true;
		}
		
		$filename = date('Y') . date('m') . date('d') . date('H') . date('i') . date('s') . '_' . Storage::getFilenameFromPath($filepath);
		
		if( !copy($filepath, dirname($filepath) . DIRECTORY_SEPARATOR . $filename) )
		{
			return false;
		}
		
		return true;
	}
	
	/*
	 * Compare source and destination file.
	 */
	public static function shouldArchive($sourcepath, $targetpath)
	{
		if( !file_exists($targetpath) || !file_exists($sourcepath))
		{
			return false;
		}
		
		$source = file_get_contents($sourcepath);
		$target = file_get_contents($targetpath);
		
		$sourcemd5 = md5($source);
		$targetmd5 = md5($target);
		
		if( $targetmd5 == $sourcemd5 )
		{
			return false;
		}
		return true;		
	}
	
	public static function getFilenameFromPath($filepath)
	{
		$result = basename($filepath);
		if( trim($result) === "" )
		{
			return uniqid();
		}
		
		return $result;
	}
	
	/*
	 * Move draft to target storage
	 */
	public static function moveFile($source_file, $group, $data = array()) 
	{
		$target_folder = Storage::getTargetFolder($group, $data);
		if( !file_exists($target_folder) )
		{
			@mkdir($target_folder, 0777, true);
		}
		
		if( !file_exists($target_folder) )
		{
			throw new Exception('Could not create target directory');
		}
		
		$target_file = $target_folder . Storage::getFilenameFromPath($source_file);
		
		if( Storage::shouldArchive($source_file, $target_file) && !Storage::archiveFile($target_file) )
		{
			throw new Exception('Could not archive file');
		}
		
		if( !copy($source_file, $target_file))
		{
			throw new Exception('Could not copy draft file to storage');
		}
		
		return $target_file;
	}
	
	/*
	 * In case the given url is an external resource (not in drafts storage)
	 * Fetch the resource and store it in the draft storage.
	 */
	public static function fetchExternalUrl($url, $guid = null)
	{
		$data = ContextualizationScripts::fetchUrl($url);
		if(is_string($data)) {
			throw new Exception($data);
		}
		
		/*
		 * If already in draft return;
		 * else store in drafts
		 */
		if( Storage::isLocalUrl($url) ){
			$data['filepath'] = Storage::getDraftPathFromUrl($url);
			return $data;
		}
		
		
		
		$name = ( (isset($data['name']) && trim($data['name']) !== '')?trim($data['name']):uniqid() );
		$guid = ( ($guid!==null)?$guid:uniqid() ); 
		
		$dirpath = Storage::getDraftFolder(true) . $guid . DIRECTORY_SEPARATOR;
		$filepath = $dirpath . $name;
		
		if (!file_exists($dirpath)) {
			@mkdir($dirpath, 0777, true);
		}
		
		try
		{
			file_put_contents($filepath, $data['data']);
			$data['filepath'] = $filepath;
			$data['name'] = $name;
		} catch (Exception $ex) {
			throw new Exception('Cannot fetch content from external url');
		}
		
		return $data;		
	}
	
	/*
	 * Entry point.
	 * Process the storage of a url resource.
	 */
	public static function storeFile($url, $group, $data)
	{
		$target_path = null;
		$source_url = trim($url);
		
		if( !Storage::isLocalUrl($source_url) )
		{
			$fdata = Storage::fetchExternalUrl($source_url);
			$source_url = Storage::getDraftUrlFromPath($fdata['filepath']);
		}
		
		if( Storage::isLocalUrl($source_url) )
		{
			$source_path = Storage::getDraftPathFromUrl($source_url);
			$target_path = Storage::moveFile($source_path, $group, $data);
		}
		
		return $target_path;
	}
	
	/*
	 * As Storage::store but returns store url instead of store path
	 */
	public static function storeUrl($url, $group, $data)
	{
		$target_url = null;
		$target_path = Storage::storeFile($url, $group, $data);
		if( $target_path )
		{
			$data['filename'] = Storage::getFilenameFromPath($target_path);
			$target_url = Storage::getTargetUrl($group, $data);
		}
		
		return $target_url;
	}
	
	public static function remove($group, $data=array())
	{
		
		$target = Storage::getTargetFolder($group, $data);
		
		if( !file_exists($target) )
		{
			return true;
		}
		$prefix = '';
		if( isset($data['prefix']) && trim($data['prefix']) !== '')
		{
			$prefix = trim($data['prefix']);
		}
		
		$deleted_target = Storage::getDeletedFolder($group). $prefix . DIRECTORY_SEPARATOR;
		if( !file_exists($deleted_target) )
		{
			@mkdir($deleted_target, 0777, true);
		}
		
		$deleted_target = $deleted_target . Storage::getFilenameFromPath($target);
		
		rename($target, $deleted_target);
		
		if( is_array($data) && isset($data['meta']) && isset($data['meta']['url']))
		{
			if( trim(basename($deleted_target)) !== trim(basename($data['meta']['url'])) )
			{
				$deleted_target = $deleted_target . DIRECTORY_SEPARATOR . basename($data['meta']['url']);
			}
		}
		
		$data['path'] = $deleted_target;
		$data['group'] = $group;
		Storage::save_log("remove", $group, $data);
		
		return $deleted_target;
	}
	
	/*
	 * Append storage actions in log file based on group
	 */
	public static function save_log($action, $group, $data)
	{
		if( !isset($data['meta']) )
		{
			return;
		}
		$log_folder = Storage::getLogFolder($group, $data);
		
		$log_path = $log_folder . $data['prefix'] . '.log.xml';
		
		if( !file_exists($log_folder) )
		{
			@mkdir($log_folder, 0777, true);
		}
		
		$x = new SimpleXMLElement('<' . $action . '></' . $action .'>');
		$x->addAttribute('on', date('Y') . '-' . date('m') . '-'. date('d') . ' '. date('H') . ':' . date('i') . ':' . date('s'));
		$x->addAttribute('user', $data['meta']['userid']);
		$x->addAttribute('id', $data['meta']['id']);
		$x->addAttribute('guid', $data['meta']['guid']);
		$x->addAttribute('size', $data['meta']['size']);
		$x->addAttribute('formatid', $data['meta']['formatid']);
		$checksum = $x->addChild('checksum', $data['meta']['hash']);
		$checksum->addAttribute('func', $data['meta']['hashfunc']);
		$x->addChild('url', $data['meta']['url']);
		$x->addChild('path', $data['path']);

		$dom = dom_import_simplexml($x)->ownerDocument;
		$dom->formatOutput = TRUE;
		$formatted = $dom->saveXML();
		$customXML = new SimpleXMLElement($formatted);
		$dom = dom_import_simplexml($customXML);
		$formatted = $dom->ownerDocument->saveXML($dom->ownerDocument->documentElement);
		file_put_contents($log_path, $formatted , FILE_APPEND );
	}
	
	public static function searchArchive($group, $guid, $filename)
	{
		$data = array( "guid" => $guid, "filename" => $filename );
		
		$arch_path = Storage::getDeletedFolder($group, $data);
		if( !file_exists($arch_path) ){
			return null;
		}
		$iter = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($arch_path, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST,
			RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
		);
		
		$foundpath = null;
		$subpath = preg_quote(DIRECTORY_SEPARATOR . $guid . DIRECTORY_SEPARATOR .$filename, DIRECTORY_SEPARATOR);
		foreach ($iter as $path => $dir) {
			if (!$dir->isDir() ) {
				$sp = preg_match('/' . $subpath . '$/', $path);
				if( $sp != false ){
					$foundpath = $path;
					break;
				}
			}
		}
		
		return $foundpath;
	}
	
	public static function search($group, $guid, $filename, $searchArchive = true) {
		$data = array( "guid" => $guid, "filename" => $filename );
		$store_path = Storage::getTargetFolder($group, $data) . Storage::getFilenameFromPath(Storage::getTargetUrl($group, $data));
		
		if( !$store_path && $searchArchive === true )
		{
			return Storage::searchArchive($group, $guid, $filename);
		}
		
		return $store_path;
	}
}


class ContextualizationStorage 
{
	public static function remove($script, $context, $userid)
	{
		$data = array(
			"group"=>"cs/swapp",
			"guid"=> $script->guid, 
			"prefix"=>"CONTEXT_" . $context->id, 
			"meta"=> array(
				"userid" => $userid,
				"id" => $script->id,
				"guid" => $script->guid,
				"formatid" => $script->formatid,
				"hashfunc" => $script->checksumfunc,
				"hash" => $script->checksum,
				"size" => $script->size,
				"url" => $script->url
		));
		
		return Storage::remove('cs/swapp', $data);
	}
	
	public static function store($script, $swappliance, $context, $data)
	{
		$contextscript = null;
		$contextscripts = new Default_Model_ContextScripts();
		$contextscripts->filter->id->numequals($script->id);
		if( count($contextscripts->items) > 0 )
		{
			$contextscript = $contextscripts->items[0];
		}
		
		$url = Storage::storeUrl( $contextscript->url, 'cs/swapp', array("guid"=>$contextscript->guid) );
		$filename = Storage::getFilenameFromPath($url);
		$data = Storage::getStoreFileData( 'cs/swapp', array("guid"=>$contextscript->guid, "filename"=> $filename) );
		if( $data === null ){
			throw new Exception('Could not retrieve context script data');
		}
		
		$contextscript->url = $url;
		$contextscript->name = $filename;
		$contextscript->checksum = $data['md5'];
		$contextscript->checksumfunc = 'md5';
		$contextscript->size = $data['size'];
		$contextscript->save();
		
		 $meta = array(
			 "group" => "cs/swapp",
			 "prefix"=>"CONTEXT_" . $context->id,
			 "path"=>$data['path'],
			 "guid"=>$contextscript->guid,
			 "meta"=> array(
					"userid" => $contextscript->addedbyID,
					"id" => $contextscript->id,
					"guid" => $contextscript->guid,
					"formatid" => $contextscript->formatid,
					"hashfunc" => $contextscript->checksumfunc,
					"hash" => $contextscript->checksum,
					"size" => $contextscript->size,
					"url" => $contextscript->url
			));
		
		 Storage::save_log("store", 'cs/swapp', $meta);
		 
		return true;
	}
}


class VapplianceStorage
{
	public static function remove($script, $vmiinstanceid, $userid)
	{
		$data = array("guid"=> $script->guid, "prefix"=>"VMI_".$vmiinstanceid, "meta"=> array(
				"userid" => $userid,
				"id" => $script->id,
				"guid" => $script->guid,
				"formatid" => $script->formatid,
				"hashfunc" => $script->checksumfunc,
				"hash" => $script->checksum,
				"size" => $script->size,
				"url" => $script->url
		));
		Storage::remove('cs/vapp', $data);
	}
	
	public static function store($script, $vmiinstanceid, $userid){
		$contextscript = null;
		$contextscripts = new Default_Model_ContextScripts();
		$contextscripts->filter->id->numequals($script->id);
		if( count($contextscripts->items) > 0 )
		{
			$contextscript = $contextscripts->items[0];
		}
		else
		{
			throw new Exception("Could not store context script. Context script not found");
		}
		
		$url = Storage::storeUrl( $contextscript->url, 'cs/vapp', array("guid"=>$contextscript->guid) );
		$filename = Storage::getFilenameFromPath($url);
		$data = Storage::getStoreFileData( 'cs/vapp', array("guid"=>$contextscript->guid, "filename"=> $filename) );
		if( $data === null ){
			throw new Exception('Could not retrieve context script data');
		}
		
		$contextscript->url = $url;
		$contextscript->name = $filename;
		$contextscript->checksum = $data['md5'];
		$contextscript->checksumfunc = 'md5';
		$contextscript->size = $data['size'];
		$contextscript->save();
		
		$meta = array(
			 "group" => "cs/vapp",
			 "prefix"=>"VMI_" . $vmiinstanceid,
			 "path" => $data['path'],
			"guid"=>$contextscript->guid,
			 "meta"=> array(
					"userid" => $userid,
					"id" => $contextscript->id,
					"guid" => $contextscript->guid,
					"formatid" => $contextscript->formatid,
					"hashfunc" => $contextscript->checksumfunc,
					"hash" => $contextscript->checksum,
					"size" => $contextscript->size,
					"url" => $contextscript->url
			));
		
		Storage::save_log("store", "cs/vapp", $meta);
		return $contextscript;
	}
}
