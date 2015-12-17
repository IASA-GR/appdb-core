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

class Repository_DispatchController extends Zend_Controller_Action {
	public function init(){
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
        $this->session = new Zend_Session_Namespace('default');
		header('Access-Control-Allow-Origin: *');
    }
		
	public function publishAction(){
		$id = ( ( isset($_POST["id"]) )?$_POST["id"]:null );
		$type = ( ( isset($_POST["type"]) )?$_POST["type"]:null );

		if( $_SERVER['REQUEST_METHOD'] != "POST" || 
			$_SERVER["Repository_Enabled"] !== 'true' || 
			is_numeric($this->session->userid) === false ||
			is_numeric($id) === false || $type == null ||
			Repository::canManageEntity($this->session->userid, $id, "release") == false
		) {
			header("Status: 404 Not Found");
			return;
		}
		$typename = "";
		if( trim($type) !== ""){
			switch(strtolower(trim($type)) ){
				case "candidate":
					$typename = "candidate";
					break;
				case "production":
					$typename = "production";
				default:
					break;
			}
		}
		$output = "";
		$result = true;
		$result = RepositoryBackend::publish($id, $typename, $output);
		echo $output;
	}
	public function unpublishAction(){
		$id = ( ( isset($_POST["id"]) )?$_POST["id"]:null );
		$type = ( ( isset($_POST["type"]) )?$_POST["type"]:null );

		if( $_SERVER['REQUEST_METHOD'] != "POST" || 
			$_SERVER["Repository_Enabled"] !== 'true' || 
			is_numeric($this->session->userid) == false ||
			is_numeric($id) === false || $type == null ||
			Repository::canManageEntity($this->session->userid, $id, "release") == false
		) {
			header("Status: 404 Not Found");
			return;
		}
		$typename = "";
		if( trim($type) !== ""){
			switch(strtolower(trim($type)) ){
				case "candidate":
					$typename = "candidate";
					break;
				case "production":
					$typename = "production";
				default:
					break;
			}
		}
		$output = "";
		$result = true;
		$result = RepositoryBackend::unpublish($id, $typename, $output);
		echo $output;
		
	}
	
	public function buildrepositoriesAction(){
		$id = ( ( isset($_POST["id"]) )?$_POST["id"]:null );
		if( $_SERVER['REQUEST_METHOD'] != "POST" || 
			$_SERVER["Repository_Enabled"] !== 'true' || 
			is_numeric($this->session->userid) === false ||
			is_numeric($id) === false ||
			Repository::canManageEntity($this->session->userid, $id, "release") == false
		) {
			header("Status: 404 Not Found");
			return;
		}
		
		$output = "";
		$result = true;
		$result = RepositoryBackend::buildRepositories($id, $output);
		echo $output;
	}
	public function buildrepofilesAction(){
		$id = ( ( isset($_POST["id"]) )?$_POST["id"]:null );
		
		if( $_SERVER['REQUEST_METHOD'] != "POST" || 
			$_SERVER["Repository_Enabled"] !== 'true' || 
			is_numeric($this->session->userid) === false ||
			is_numeric($id) === false ||
			Repository::canManageEntity($this->session->userid, $id, "release") == false
				
		) {
			header("Status: 404 Not Found");
			return;
		}
		
		$output = "";
		$result = true;
		$result = RepositoryBackend::buildRepofiles($id, $output);
		echo $output;
	}
	
	public function renameAction(){
		$id = ( ( isset($_POST["id"]) )?$_POST["id"]:null );
		$type = ( ( isset($_POST["type"]) )?$_POST["type"]:null );
		$to = ( ( isset($_POST["to"]) )?$_POST["to"]:null );
		
		if( $_SERVER['REQUEST_METHOD'] != "POST" || 
			$_SERVER["Repository_Enabled"] !== 'true' ||
			is_numeric($this->session->userid) == false ||
			is_numeric($id) === false || trim($type) == "" || trim($to) == ""
		) {
			header("Status: 404 Not Found");
			return;
		}
		$type = strtolower(trim($type));
		$to = trim($to);
		$isvalidRequest = false;
		switch( $type ){
			case "release":
				$isvalidRequest = Repository::canManageEntity($this->session->userid, $id, "release");
				break;
			case "series":
				$isvalidRequest = Repository::canManageEntity($this->session->userid, $id, "repoarea");
				break;
			default:
				break;
		}
		
		if( !$isvalidRequest ){
			header("Status: 404 Not Found");
			return;
		}
		$output = "";
		header("Content-Type: text/xml");
		if( $type == "release"){
			$result = RepositoryBackend::renameReleaseVersion($id, $to, $output);
		} else{
			$result = RepositoryBackend::renameSeriesName($id, $to, $output);
		}
		echo $output;
	}
    
	public function removeAction(){
		$id = ( ( isset($_POST["id"]) )?$_POST["id"]:null );
		$type = ( ( isset($_POST["type"]) )?$_POST["type"]:null );
		$userid = $this->session->userid;
		
		if( $_SERVER['REQUEST_METHOD'] != "POST" || 
			$_SERVER["Repository_Enabled"] !== 'true' ||
			is_numeric($userid) == false ||
			is_numeric($id) === false || 
			trim($type) == "" 
		) {
			header("Status: 404 Not Found");
			return;
		}
		$type = strtolower(trim($type));
		$isvalidRequest = false;
		switch( $type ){
			case "release":
				$isvalidRequest = Repository::canManageEntity($userid, $id, "release");
				break;
			case "series":
				$isvalidRequest = Repository::canManageEntity($userid, $id, "repoarea");
				break;
			default:
				break;
		}
		
		if( !$isvalidRequest ){
			header("Status: 404 Not Found");
			return;
		}
		$output = "";
		header("Content-Type: text/xml");
		if( $type == "release"){
			$result = RepositoryBackend::removeRelease($id, $userid, $output);
		} else{
			$result = RepositoryBackend::removeSeries($id, $userid, $output);
		}
		echo $output;
	}
	
	public function packagesAction(){
		$ids = ( ( isset($_POST["ids"]) )?$_POST["ids"]:"");
		$action = ( (isset($_POST["type"]) )?$_POST["type"]:"");
		$releaseid = ( ( isset($_POST["releaseid"]) )?$_POST["releaseid"]:"");
		$swid = ( ( isset($_POST["swid"]) )?$_POST["swid"]:"");
		if( $ids !== null && is_array($ids)==false && is_numeric($ids)==true){
			$ids = array($ids);
		}debug_log("ACTION: " . $action);
		$userid = $this->session->userid;
		if(in_array($action, array("mark","unmark","remove")) == false ){
			$action = "";
		}

		if( $_SERVER['REQUEST_METHOD'] != "POST" || 
				$_SERVER["Repository_Enabled"] !== 'true' ||
				is_numeric($userid) == false ||
				$action == null || 
				is_array($ids) === false ||
				is_numeric($releaseid) == false ||
				is_numeric($swid) == false ||
				Repository::canManageRelease($swid, $userid) == false
		) {
				header("Status: 404 Not Found");
				return;
		}
		switch($action){
			case "mark":
				$res = Repository::markPoaPackages($ids, $releaseid);
				break;
			case "unmark":
				$res = Repository::unmarkPoaPackages($ids, $releaseid);
				break;
			case "remove":
				$res = Repository::removePoaPackages($ids, $releaseid,$userid);
				break;
		}

		header("Content-Type: text/xml");
		if( $res === true ){
			echo "<response success='true'>OK</response>";
			return;
		}else{
			echo "<response>";
			if(isset($res["id"])){
				echo "<error id='" . $res["id"] . "' result='" . $res . "' ></error>";
			}else if(is_string($res)){
				echo "<error>".$res."</error>";
			}else{
				for($i=0; $i<count($res); $i+=1){
					echo "<error id='" . $res[$i]["id"] . "' result='" . $res[$i]["result"] . "' ></error>";
				}
			}
			echo "</response>";
		}
	}
	
	public static function removepackagesAction(){
		$ids = ( ( isset($_POST["ids"]) )?$_POST["ids"]:null);
		$releaseid = ( ( isset($_POST["releaseid"]) )?$_POST["releaseid"]:null);
		$swid = ( ( isset($_POST["swid"]) )?$_POST["swid"]:null);
		if( $ids !== null && is_array($ids)==false && is_numeric($ids)==true){
			$ids = array($ids);
		}
		$userid = $this->session->userid;

		if( $_SERVER['REQUEST_METHOD'] != "POST" || 
				$_SERVER["Repository_Enabled"] !== 'true' ||
				is_numeric($userid) == false ||
				is_array($ids) === false ||
				is_numeric($releaseid) == false ||
				is_numeric($swid) == false ||
				Repository::canManageRelease($swid, $userid) == false
		) {
				header("Status: 404 Not Found");
				return;
		}

		$res = Repository::removePoaPackage($ids, $releaseid,$userid);
		header("Content-Type: text/xml");
		if( $res === true ){
			echo "<response success='true'>OK</response>";
			return;
		}else{
			echo "<response success='false'>";
			for($i=0; $i<count($res); $i+=1){
				echo "<package id='" . $res[$i]["id"] . "' result='" . $res[$i]["result"] . "' />";
			}
			echo "</response>";
		}
	}

	public static function markpackagesAction(){
		$ids = ( ( isset($_POST["ids"]) )?$_POST["ids"]:null);
		$releaseid = ( ( isset($_POST["releaseid"]) )?$_POST["releaseid"]:null);
		$swid = ( ( isset($_POST["swid"]) )?$_POST["swid"]:null);
		$userid = $this->session->userid;
		if( $ids !== null && is_array($ids)==false && is_numeric($ids)==true){
			$ids = array($ids);
		}
		debug_log($ids);
		if( $_SERVER['REQUEST_METHOD'] != "POST" || 
				$_SERVER["Repository_Enabled"] !== 'true' ||
				is_numeric($userid) == false ||
				is_array($ids) === false ||
				is_numeric($releaseid) == false ||
				is_numeric($swid) == false || 
				Repository::canManageRelease($swid, $userid) == false
		) {
				header("Status: 404 Not Found");
				return;
		}

		$res = Repository::markPoaPackage($ids, $releaseid);
		header("Content-Type: text/xml");
		if( $res === true ){
			echo "<response success='true'>OK</response>";
			return;
		}else{
			echo "<response success='false'>";
			for($i=0; $i<count($res); $i+=1){
				echo "<package id='" . $res[$i]["id"] . "' result='" . $res[$i]["result"] . "' />";
			}
			echo "</response>";
		}
	}

	public static function unmarkpackagesAction(){
		$ids = ( ( isset($_POST["ids"]) )?$_POST["ids"]:null);
		$releaseid = ( ( isset($_POST["releaseid"]) )?$_POST["releaseid"]:null);
		$swid = ( ( isset($_POST["swid"]) )?$_POST["swid"]:null);
		$userid = $this->session->userid;
		if( $ids !== null && is_array($ids)==false && is_numeric($ids)==true){
			$ids = array($ids);
		}
		if( $_SERVER['REQUEST_METHOD'] != "POST" || 
				$_SERVER["Repository_Enabled"] !== 'true' ||
				is_numeric($userid) == false ||
				is_array($ids) === false ||
				is_numeric($releaseid) == false ||
				is_numeric($swid) == false || 
				Repository::canManageRelease($swid, $userid) == false
		) {
				header("Status: 404 Not Found");
				return;
		}

		$res = Repository::unmarkPoaPackage($ids, $releaseid, $userid);

		header("Content-Type: text/xml");
		if( $res === true ){
			echo "<response success='true'>OK</response>";
			return;
		}else{
			echo "<response success='false'>";
			for($i=0; $i<count($res); $i+=1){
				echo "<package id='" . $res[$i]["id"] . "' result='" . $res[$i]["result"] . "' />";
			}
			echo "</response>";
		}
	}
}
?>
