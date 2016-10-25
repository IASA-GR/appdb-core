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

require_once('Storage.php');

class StorageController extends Zend_Controller_Action{
	private $target_directory;
	//put your code here
	public function init(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$this->target_directory = Storage::getDraftFolder();
		if( !file_exists($this->target_directory) ){
			@mkdir($this->target_directory, 0777,true);
		}
	}
	
	public function uploadAction(){
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		// 10 minutes execution time
		@set_time_limit(10 * 60);
		
		$cleanupTargetDir = true; // Remove old files
		$maxFileAge = 5 * 3600; // Temp file age in seconds
		
		// Get a file name
		if (isset($_REQUEST['filename']) ){
			$fileName = $_REQUEST['filename'];
			$fileName = Storage::escapeFileName($fileName);
		} else {
			die('{"jsonrpc" : "2.0", "error" : {"code": 9001, "message": "Invalid request"}, "id" : "id"}');
		}
		
		// Get guid
		$guid = '';
		if( isset($_REQUEST["guid"]) ){
			$guid = DIRECTORY_SEPARATOR . trim($_REQUEST["guid"]);
		} else {
			die('{"jsonrpc" : "2.0", "error" : {"code": 9001, "message": "Invalid request"}, "id" : "id"}');
		}
		// Create target dir
		if (!file_exists($this->target_directory.$guid)) {
			mkdir($this->target_directory.$guid, 0777, true);
		}
		
		
		$filePath = $this->target_directory . $guid . DIRECTORY_SEPARATOR . $fileName;
		// Chunking might be enabled
		$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
		$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
		// Remove old temp files	
		if ($cleanupTargetDir) {
			if (!is_dir($this->target_directory) || !$dir = opendir($this->target_directory)) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
			}
			while (($file = readdir($dir)) !== false) {
				$tmpfilePath = $this->target_directory . $file;
				// If temp file is current file proceed to the next
				if ($tmpfilePath == "{$filePath}.part") {
					continue;
				}
				// Remove temp file if it is older than the max age and is not the current file
				if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
					@unlink($tmpfilePath);
				}
			}
			closedir($dir);
		}	
		// Open temp file
		if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
			die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}
		if (!empty($_FILES)) {
			if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
			}
			// Read binary input stream and append it to temp file
			if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			}
		} else {	
			if (!$in = @fopen("php://input", "rb")) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			}
		}
		while ($buff = fread($in, 4096)) {
			fwrite($out, $buff);
		}
		@fclose($out);
		@fclose($in);
		// Check if file has been uploaded
		if (!$chunks || $chunk == $chunks - 1) {
			// Strip the temp .part suffix off 
			rename("{$filePath}.part", $filePath);
		}
		
		$finfo = finfo_open(FILEINFO_MIME);

		//check to see if the mime-type starts with 'text'
		if(file_exists($filePath) && substr(finfo_file($finfo, $filePath), 0, 4) != 'text' ){
			unlink($filePath);
			die('{"jsonrpc" : "2.0", "error" : {"code": 90101, "message": "Only text files are allowed."}, "id" : "id"}');
		}
		
		// Return Success JSON-RPC response
		die('{"jsonrpc" : "2.0", "url" : "'.$guid . DIRECTORY_SEPARATOR . $fileName.'", "id" : "id"}');
	}
	
	public function codeAction(){
		if( $_SERVER['REQUEST_METHOD'] !== 'POST') {
			return;
		}		
		
		$name = (isset($_POST['name']) && trim($_POST['name']) !== "")?trim($_POST['name']):uniqid();
		$code = (isset($_POST['code']))?trim($_POST['code']):null;
		$guid = (isset($_POST['guid']))?$_POST['guid']:uniqid();
		
		$name =Storage::escapeFileName($name);
		
		$filesize = strlen($code);
		if( $filesize === 0 ){
			echo '{"error" : "No data given"}';
			return;
		}
		
		$dirPath = $this->target_directory . $guid . DIRECTORY_SEPARATOR;
		$filePath = $dirPath . $name;
				
		if (!file_exists($dirPath)) {
			@mkdir($dirPath, 0777, true);
		}
		
		try{
			file_put_contents($filePath, $code);
			$md5 = md5($code);
			echo '{"url" : "'.DIRECTORY_SEPARATOR . $guid . DIRECTORY_SEPARATOR . $name.'", "name" : "'.$name.'", "md5":"'.$md5.'", "size": "'.$filesize.'"}';
		}catch(Exception $ex){
			echo '{"error" : "'.$ex->getMessage().'"}';
		}
	}
	
	public function urlAction(){
		if( $_SERVER['REQUEST_METHOD'] !== 'POST') {
			return;
		}
		
		$url = (isset($_POST['url']))?trim($_POST['url']):null;
		if( $url === null ) {
			echo '{"error":"Invalid request"}';
			return;
		}		
		
		try{
			$guid = (isset($_POST['guid']))?$_POST['guid']:uniqid(); 
			$data = Storage::fetchExternalUrl($url, $guid);
			$name = $name = (isset($data['name']) && trim($data['name']) !== "")?trim($data['name']):uniqid();
			$name = Storage::escapeFileName($name);
			echo '{"url" : "'.DIRECTORY_SEPARATOR . $guid . DIRECTORY_SEPARATOR . $name.'", "name" : "'.$name.'", "md5":"'.$data['md5'].'", "size":"'.$data['size'].'"}';
		} catch (Exception $ex) {
			$res = array(
				"error" => $ex->getMessage()
			);
			echo json_encode($res);
			return;
		}
	}
	
	public function draftsAction() {
		$filename = $this->_getParam('filename');
		$filename = Storage::escapeFileName($filename);
		$file = Storage::locateDraftFile($this->_getParam('folder') . DIRECTORY_SEPARATOR . $filename);
		if (file_exists($file)) {
			$fp = fopen($file, 'rb');
			header("Content-Type: text/plain");
			header("Content-Length: " . filesize($file));
			fpassthru($fp);
			exit;
		}
	}
	
	private function echo_file($file, $is_archived = false){
		if (file_exists($file)) {
			$fp = fopen($file, 'rb');
			header("Content-Type: text/plain");
			header("Content-Length: " . filesize($file));
			if( $is_archived === true )
			{
				header('Warning: 404 Resource is archived');
			}
			fpassthru($fp);
			exit;
		} else {
			header("HTTP/1.0 404 Not Found");
			exit;
		}
	}
	
	public function groupAction() {
		$folder1 = trim($this->_getParam('folder'));
		$folder2 = trim($this->_getParam('folder2'));
		$group = trim($this->_getParam('group'));
		$filename = Storage::escapeFileName(trim($this->_getParam('filename')));
		$guid = $folder1;
		$is_archived = false;
		
		if( $folder2 !== ''){
			$group = $group . DIRECTORY_SEPARATOR . $folder1;
			$guid = $folder2;
		} 
		
		$file =  Storage::search($group, $guid, $filename, false);
		
		if( !file_exists($file) )
		{
			//check archived scripts
			$is_archived = true;
			$file = Storage::searchArchive($group, $guid, $filename);
		}
		
		$this->echo_file($file, $is_archived);
	}
	
	
	/*
	 * Get the most recent contextualization script uploaded
	 * for the given vmiinstance (based on guid)
	*/
	public function vmiAction() {
		$guid = trim($this->_getParam('guid'));
		$is_archived = false;
		
		$vmis = new Default_Model_VAviews();
		$f0 = new Default_Model_VAviewsFilter();
		$f0->vmiinstance_guid->equals($guid);
		$f1 = new Default_Model_VAviewsFilter();
		$f1->va_version_published->equals(true);
		$f2 = new Default_Model_VAviewsFilter();
		$f2->va_version_enabled->equals(true);
		$vmis->filter->chain($f0->chain($f1->chain($f2, "AND"),"AND"),"AND");
		$vmis->filter->orderBy('vmiinstance_addedon DESC');
		if( count($vmis->items) === 0 )
		{
			return $this->echo_file(null);
		}
		
		$vmi = $vmis->items[0];
		$vmi_scripts = new Default_Model_VMIinstanceContextScripts();
		$vmi_scripts->filter->vmiinstanceid->numequals($vmi->vmiinstanceid);
		$vmi_scripts->filter->orderBy('addedon DESC');
		
		if( count($vmi_scripts->items) === 0 )
		{
			return $this->echo_file(null);
		}
		
		$vmi_script = $vmi_scripts->items[0];
		$cscripts = new Default_Model_ContextScripts();
		$cscripts->filter->id->numequals($vmi_script->contextscriptid);
		
		if( count($cscripts->items) === 0 )
		{
			return $this->echo_file(null);
		}
		
		$cscript = $cscripts->items[0];
		$url = $cscript->url;
		$cs_guid = $cscript->guid;
		$group = 'cs/vapp';
		$filename = basename($url);
		
		$path = Storage::search($group, $cs_guid, $filename, false);
		
		if( !$path )
		{
			$path = Storage::searchArchive($group, $guid, $filename);
			if( $path )
			{
				$is_archived = true;
			}
		}
		$this->echo_file($path, $is_archived);
	}	
	
	public function swappAction(){
		$associd = trim($this->_getParam('associationid'));
		$is_archived = false;
		
		$swapp_relations = new Default_Model_ContextScriptAssocs();
		$swapp_relations->filter->id->numequals($associd);
		if( count($swapp_relations->items) === 0 )
		{
			return $this->echo_file(null);
		}
		
		$swapp_relation = $swapp_relations->items[0];
		$contextscripts = new Default_Model_ContextScripts();
		$contextscripts->filter->id->numequals($swapp_relation->contextscriptid);
		if( count($contextscripts->items) === 0 )
		{
			return $this->echo_file(null);
		}
		
		$cscript = $contextscripts->items[0];
		$url = $cscript->url;
		$cs_guid = $cscript->guid;
		$group = 'cs/swapp';
		$filename = basename($url);
		
		$path = Storage::search($group, $cs_guid, $filename, false);
		
		if( !$path )
		{
			$path = Storage::searchArchive($group, $cs_guid, $filename);
			if( $path )
			{
				$is_archived = true;
			}
		}
		$this->echo_file($path, $is_archived);
	}
	
	public function cleardraftsAction(){
		if( !localRequest() ){
			header('HTTP/1.0 404 Not Found');
			header("Status: 404 Not Found");
			return;
		}
		
		$offset = intval(trim($this->_getParam('offset')));
		if( $offset < 1 ){
			$offset = 1;
		}
		Storage::clearDrafts($offset);
	}
}
