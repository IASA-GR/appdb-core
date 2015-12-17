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

if ( $_SERVER["Repository_Enabled"] === 'true') {
	include_once('../library/repository.php');
}

class Repository_StorageController extends Zend_Controller_Action {
	public function init(){
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
		$this->session = new Zend_Session_Namespace('default');
		header('Access-Control-Allow-Origin: *');
    }
	
	private function handleresterror($rest, $type){
		if( $rest->getError() == RestErrorEnum::RE_OK ){
			return true;
		}
		header("Status: 404 Not Found");
		switch($rest->getError() ){
			case RestErrorEnum::RE_ACCESS_DENIED:
				echo "access denied";
				break;
			case RestErrorEnum::RE_ITEM_NOT_FOUND:
				echo $type . " not found";
				break;
		}
		return false;
	}
	
	private function chunkUploadResponse($out){
		// HTTP headers for no cache etc
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		echo $out;
		return false;
	}
	
	private function chunkUploadHandling($targetDir){
		@set_time_limit(5 * 60);

		// Uncomment this one to fake upload latency
		// usleep(5000);
		
		$cleanupTargetDir = true; // Remove old files
		$maxFileAge = 5 * 3600; // Temp file age in seconds

		// Create target dir
		if (!file_exists($targetDir)) {
			@mkdir($targetDir);
		}

		$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
		$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
		$_REQUEST["offset"] = (isset($_REQUEST["chunk"])?$_REQUEST["chunk"]:"0");
		$_REQUEST["total"] = trim((isset($_REQUEST["chunks"])?$_REQUEST["chunks"]:"0"));
		
		$chunking = is_numeric($_REQUEST["offset"]) && is_numeric($_REQUEST["total"]) && ($_REQUEST["offset"] !== $_REQUEST["total"] || $_REQUEST["total"] === "1");
		$chunks = isset($_POST["chunks"]) ? $_POST["chunks"] : 0;
		$chunk = isset($_POST["chunk"]) ? $_POST["chunk"] : 0;
		
		// Remove old temp files	
		if ($cleanupTargetDir) {
			if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
				return $this->chunkUploadResponse('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
			}

			while (($file = readdir($dir)) !== false) {
				$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

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
		if (!$out = @fopen("{$filePath}.part", $chunking ? "ab" : "wb")) {
			return $this->chunkUploadResponse('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}

		if (!empty($_FILES)) {
			if ($_FILES['file']['error'] || !is_uploaded_file($_FILES['file']['tmp_name'])) {
				return $this->chunkUploadResponse('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
			}

			// Read binary input stream and append it to temp file
			if (!$in = @fopen($_FILES['file']['tmp_name'], "rb")) {
				return $this->chunkUploadResponse('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			}
		} else {	
			if (!$in = @fopen("php://input", "rb")) {
				return $this->chunkUploadResponse('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			}
		}

		while ($buff = fread($in, 4096)) {
			fwrite($out, $buff);
		}

		@fclose($out);
		@fclose($in);

		// Check if file has been uploaded
		if ($chunking && $chunk == ($chunks-1) ) {
			// Strip the temp .part suffix off 
			rename("{$filePath}.part", $filePath);
			return $filePath;
		}else if($chunking === false ){
			rename("{$filePath}.part", $filePath);
			return $filePath;
		}

		return false;
	}
	public function uploadAction(){
		ob_start();
		ob_get_clean();
		ob_end_clean();
		$this->_helper->viewRenderer->setNoRender();
		$releaseid = $this->_getParam("releaseid");
		$targetid = $this->_getParam("targetid");
		$swid = $this->_getParam("swid");
		$userid = $this->session->userid;
		if( $_SERVER["Repository_Enabled"] !== 'true' ||
			is_numeric($this->session->userid) === false ||
			is_numeric($releaseid) === false ||
			is_numeric($targetid) === false ||
			is_numeric($swid) === false ||
			$releaseid <= 0 ||
			$targetid <= 0 ||
			$swid <= 0 ||
			Repository::canManageRelease($swid, $userid) === false
			) {
			header("Status: 404 Not Found");
			return;
		}
		
		//Get release
		$rl = new RestProductReleaseItem(array("id"=>$releaseid));
		$release = $rl->getRawData();
		if( !$this->handleresterror($rl, "release") ) return;
		
		//retrieve upload target directory
		$targetDir = RepositoryFS::getScratchSpaceForRelease($release, $error, true);
		$targetDir .= $targetid;
		
		//If cannot find or create the target upload directory...
		if( $targetDir === false){
			return $this->chunkUploadResponse('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "'.$error.'"}, "id" : "id"}');
		}
		$file = $this->chunkUploadHandling($targetDir);
		if( $file === false ) return;
		
		
		/*****************************************************
		* THIS CODE WILL BE REACHED IF UPLOADING IS COMPLETE *
		******************************************************/
		//check if release is a candidate revert it to unverified
		if($release->currentStateId == 3){
			RepositoryBackend::unpublish($release->id, "candidate", $output);
			//TODO: Send command to commrepo backend to remove candidate repositories
		}
		
		//get uploaded filename (full path)
		$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
		
		//Get target (os arch combination)
		$rl = new RestTargetItem(array("id"=>$targetid));
		$target = $rl->getRawData();
		if( !$this->handleresterror($rl,"target")) return;
		
		$warnings = null;
		$res = RepositoryFS::storeUploadedFile($file, $release, $target, $userid, $warnings);
		ob_start();
		ob_get_clean();
		ob_end_clean();
		header("Content-Type: text/xml");	
		if( file_exists($file) ){
			@unlink($file);
		}
		if( $res !== true && (is_numeric($res) === false || $res===false)){
			echo '<repository datatype="item" content="upload" ><upload result="error" releaseid="'.$releaseid.'" targetid="'.$targetid.'" filename="'.$file.'" error="' . $res . '"></upload></repository>';
			return ;
		}
		
		echo '<repository datatype="item" content="upload"><upload result="success" releaseid="'.$releaseid.'" targetid="'.$targetid.'" filename="'.$file.'"';
		if($warnings !== null){
			echo 'warning="'.$warnings.'"';
		}
		echo '>';
		$pcks = new Repository_Model_MetaPoaReleasePackages();
		$pcks->filter->id->equals($res);
		if( count($pcks->items) > 0 ){
			$pck = $pcks->items[0];
			$xml = $pck->toXML(true);
			$xml = RepositoryXSLT::transform($xml, "poapackage");
			debug_log($xml);
			echo $xml;
		}
		echo '</upload></repository>';
		return;		
	}
	
	public function downloadAction(){
		ini_set("zlib.output_compression", "off");
		ob_clean();
		ob_get_clean();
		$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
		$id = $this->_getParam("id");
		if( $_SERVER["Repository_Enabled"] !== 'true' ||
			is_numeric($id) == false ||
			$_SERVER['REQUEST_METHOD'] != "GET"
		) {
			header("Status: 404 Not Found");
			return;
		}
		
		$mime = "application/octet-stream";
		$url = RepositoryFS::getPackageDatabankUrl($id, $mime);
		if( $url === false || file_exists($url) === false){
			header("Status: 404 Not Found");
			return;
		}
		$filename = basename($url);
		ob_start();
		ob_get_clean();
		header('Content-Description: File Transfer');
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Expires: 0");
		header("Content-type: " . $mime);
		header("Content-length: ". filesize($url) );
		header("Content-Transfer-Encoding: binary");
		header("Content-disposition: attachement;filename=\"".urlencode($filename)."\"");	
		ob_clean();
        flush();
		$file = @fopen($url,'rb');
		if($file) {
			@fpassthru($file);
		}
		exit;
	}
}?>