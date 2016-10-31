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

class AppstatsController extends Zend_Controller_Action
{
	protected $appType = "app";

    public function init()
	{
		$this->session = new Zend_Session_Namespace('default');
		$ct = $this->_getParam("ct");
		if ( $ct === null ) $ct = $this->session->chartType;
		if ( $ct === null ) { $ct = "Pie"; } else { $this->session->chartType = $ct; }; 
		$this->view->chartType = $ct;
		$this->view->chartObject = "App";
		$ctype = @($this->_getParam("content"));
		switch ($ctype) {
		case "vappliance":
			$this->appType = "vapp";
			break;
		default:
			$this->appType = "app";
			break;
		}
		$this->view->contentType = $this->appType;
    }

    public function indexAction()
    {
        // action body
    }

    public function preDispatch()
    {
	$this->_helper->layout->disableLayout();
    }

    public function perdomainAction()
    {
		$stats = new Default_Model_AppStats($this->appType);
		$this->view->entries = $stats->perDiscipline();
    }

    public function persubdomainAction()
    {
		$stats = new Default_Model_AppStats($this->appType);
		$this->view->entries = $stats->perSubdomain();
    }

    public function percountryAction()
    {
		$stats = new Default_Model_AppStats($this->appType);
		$this->view->entries = $stats->perCountry();
    }

    public function perregionAction()
    {
		$stats = new Default_Model_AppStats($this->appType);
		$this->view->entries = $stats->perRegion();
    }

    public function pervoAction()
    {
		$stats = new Default_Model_AppStats($this->appType);
		$this->view->entries = $stats->perVO();
    }

	public function pertimeAction()
	{
		return;
		db()->exec("SELECT store_stats_graph('app', '2010-01-01', NOW()::date::text)");
//		$stats = new Default_Model_AppStats($this->appType);
//		$this->view->entries = $stats->perVO();
    }

	public  function percategoryAction(){
	}

	public  function perdisciplineAction(){
	}
	
	public function exportdataAction()
    {
		$type = $_POST["type"];
		if ( $type == "csv") $this->view->type = 'text/x-csv';
		
		$data = $_POST["data"];
		$labels = $_POST["labels"];
		$fname = tempnam(sys_get_temp_dir(),"dat");
		$f = fopen($fname,'w');
		switch ( $type ) {
			case 'csv':
				$data = explode(",", $data);
				$labels = explode(",", $labels);

				for ($i = 0; $i < count($data); $i++) {
						fwrite($f, $labels[$i]);
						fwrite($f, ",");
						fwrite($f, $data[$i]);
						fwrite($f, "\n");
				}
				break;
		}
		fclose($f);
		rename($fname,$fname.".".$type);
		$this->view->fname = $fname.".".$type;
    }
	public function exportobjectdataAction(){
		$this->_helper->viewRenderer->setNoRender();
		$type = $_POST["type"];
		$entitytype = (isset($_POST["entitytype"])==true)?$_POST["entitytype"]:"Item";
		if ( $type == "csv") $this->view->type = 'text/x-csv';
		
		if( isset($_POST["data"]) == false ){
			return;
		}
		$data = json_decode($_POST["data"],true);
		if( json_last_error() !== JSON_ERROR_NONE ){
			return;
		}
		if( is_array($data) == false ){
			$data = array($data);
		}
		$fname = tempnam(sys_get_temp_dir(),"dat");
		$f = fopen($fname,'w');
		fwrite($f,$entitytype);
		fwrite($f,",");
		fwrite($f,"parent");
		fwrite($f,",");
		fwrite($f,"count");
		fwrite($f,",");
		fwrite($f,"\n");
		fwrite($f,"\n");
		for ($i = 0; $i < count($data); $i++) {
				$this->exportobjectchild($f, $data[$i]);
		}
		fclose($f);
		rename($fname,$fname.".".$type);
		$fname = $fname.".".$type;
		
		header('Pragma: no-cache');
		header('Content-disposition: attachment; filename='.basename($fname));
		header('Content-type: '.$type);
		echo file_get_contents($fname);
		unlink($fname);
	}
	private function exportobjectchild($f, $d){
		if( isset($d["text"]) ){
			fwrite($f, $d["text"]);
		}else{
			fwrite($f, "");
		}
		fwrite($f, ",");
		
		if(isset($d["parent"])){
			fwrite($f, $d["parent"]["text"]);
		}else{
			fwrite($f, "");
		}
		fwrite($f, ",");
		
		if( isset($d["count"]) ){
			fwrite($f, $d["count"]);
		}else{
			fwrite($f, "0");
		}
		fwrite($f, ",");
		fwrite($f, "\n");
		
		if( isset($d["children"]) ){
			if( is_array($d["children"]) == false ){
				$d["children"] = array($d["children"]);
			}
			$data = $d["children"];
			for ($i = 0; $i < count($data); $i++) {
				$this->exportobjectchild($f, $data[$i]);
			}	
		}
	}
    public function exportimageAction()
    {
		$type = $_POST["type"];
		$this->view->type = $type;
		$svg = $_POST["svgdata"];
		$svg = substr($svg,strpos($svg,"<svg")+4);
		$svg = '<?xml version="1.0" encoding="UTF-8" standalone="no"?><svg xmlns="http://www.w3.org/2000/svg"'.$svg;
		$svg = str_replace(">",">\n",$svg);
		$fname = tempnam(sys_get_temp_dir(),"img");
		$f = fopen($fname,'w');
		// opera / safari BUG: attribute "path" in "path" element is invalid (missing value)
		$svg = preg_replace('/ path /',' path="" ',$svg);
		fwrite($f,$svg);
		fclose($f);
		rename($fname,$fname.".svg");
		$this->view->fname = $fname.".svg";
		exec("cat $fname.svg|grep height|head -n1|sed -e 's/.\+height=\"\(\w\+\)\".\+/\\1/g'",$height);
		$height = 4*($height[0]);
		exec("cat $fname.svg|grep width|head -n1|sed -e 's/.\+height=\"\(\w\+\)\".\+/\\1/g'",$width);
		$width = 4*($width[0]);
		if ($type == "png" || $type == "pdf") {
			`mogrify -resize ${width}x${height} -density 90 -format $type $fname.svg`;
			$this->view->fname = "$fname.$type";
			unlink($fname.".svg");
		}
    }
}


