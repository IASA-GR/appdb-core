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

class ElixirController extends Zend_Controller_Action{
	//put your code here
	public function init(){
		$this->_helper->layout->disableLayout();
	}
	
	public function topicsmapAction(){
		if( Supports::isProductionInstance() ) {
			$this->_helper->viewRenderer->setNoRender();
			header("Status: 404 Not Found");
			return;
		}
		$error = "";
		if( isset($_GET["xml"])  || $_SERVER['REQUEST_METHOD'] === 'POST' ){//elixir.discipline_topics_to_xml
			$this->_helper->viewRenderer->setNoRender();
			if ($_SERVER['REQUEST_METHOD'] === 'POST') {
				if( !(isset($_POST["discipline_id"]) || is_numeric($_POST["discipline_id"]) ) ){
					$error = "Invalid discipline id";
				}elseif( !( isset($_POST["topic_id"]) || is_numeric($_POST["topic_id"])) ){
					$error = "Invalid topic id";
				}elseif( !(isset($_POST["topic_uri"]) || trim($_POST["topic_uri"])==="" ) ){
					$error = "Invalid topic uri";
				}elseif( !(isset($_POST["topic_label"]) || trim($_POST["topic_label"])==="" )){
					$error = "Invalid topic label";
				}elseif( !(isset($_POST["action"]) || (trim($_POST["topic_label"])!=="add" && trim($_POST["topic_label"])!=="remove") )){
					$error = "Invalid action";
				}else {
					try{
						if($_POST["action"] == "add" && $this->mappingexists($_POST["topic_uri"], $_POST["discipline_id"])===false ){
							db()->query("INSERT INTO elixir.discipline_topics VALUES(?,?,?,?)", array($_POST["topic_id"],$_POST["topic_uri"],$_POST["topic_label"], $_POST["discipline_id"]))->fetchAll();
						}else if($_POST["action"] == "remove" && $this->mappingexists($_POST["topic_uri"], $_POST["discipline_id"])===true ){
							db()->query("DELETE from elixir.discipline_topics WHERE topic_uri=? AND discipline_id=?",array($_POST["topic_uri"],$_POST["discipline_id"]))->fetchAll();
						}
					} catch (Exception $ex) {
							$error = $ex->getMessage();
					}
				}
			}
			$rows = db()->query("SELECT elixir.discipline_topics_to_xml() AS f;")->fetchall();
			header('Content-type: text/xml');
			$res = "<mappings count='".count($rows)."'";
			if( trim($error) !== "" ){
				$res .= " error='" . $error . "' ";
			}
			$res .= ">";
			foreach($rows as $r){
				$res .= $r["f"];
			}
			$res .= "</mappings>";
			echo $res;
			return;
		}
		
	}
	
	private function mappingexists($uri,$disciplineid){
		$rows = db()->query("SELECT * FROM elixir.discipline_topics WHERE discipline_id=? AND topic_uri = ?", array($disciplineid,$uri))->fetchAll();
		if( count($rows) > 0 ){
			return true;
		}
		return false;
		
	}
	
}
