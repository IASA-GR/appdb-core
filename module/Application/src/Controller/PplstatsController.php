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

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class PplstatsController extends AbstractActionController
{


    public function init()
    {
		$this->session = new Zend_Session_Namespace('default');
		$ct = $this->getRequest()->getParam("ct");
		if ( $ct === null ) $ct = $this->session->chartType;
		if ( $ct === null ) { $ct = "Bars"; } else { $this->session->chartType = $ct; }; 
		$this->view->chartType = $ct;
		$this->view->chartObject = "Ppl";
		$this->view->contentType = "ppl";
    }

    public function indexAction()
    {
        // action body
    }

    public function preDispatch()
    {
	$this->_helper->layout->disableLayout();
    }

    public function perpositionAction()
    {
		$stats = new Default_Model_PplStats();
		$this->view->entries = $stats->perPosition();
		include 'pplaccounting.php';
    }

    public function percountryAction()
    {
		$stats = new Default_Model_PplStats();
		$this->view->entries = $stats->perCountry();
		include 'pplaccounting.php';
    }

    public function perregionAction()
    {
		$stats = new Default_Model_PplStats();
		$this->view->entries = $stats->perRegion();
		include 'pplaccounting.php';
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
				$data = explode(",",$data);
				$labels = preg_split('/","/',$labels);
				for ($i=0;$i<count($labels);$i++) {
					if ($i==0) {
						$labels[$i]=$labels[$i].'"';	
					} elseif ($i==count($labels)-1) { 
						$labels[$i]='"'.$labels[$i];
					} else $labels[$i]='"'.$labels[$i].'"';
				}
				for ($i=0;$i<count($data);$i++) {
						fwrite($f,$labels[$i]);
						fwrite($f,",");
						fwrite($f,$data[$i]);
						fwrite($f,"\n");
				}
				break;
		}
		fclose($f);
		rename($fname,$fname.".".$type);
		$this->view->fname = $fname.".".$type;
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
