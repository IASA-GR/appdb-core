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
 
class AppsController extends Zend_Controller_Action
{
	public function buildlogocacheAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$islocal = localRequest();
		if (! $islocal) {
			header('HTTP/1.0 404 Not Found');
			header("Status: 404 Not Found");
			return;
		} else {
			db()->setFetchMode(Zend_Db::FETCH_NUM);
			$res = db()->query("SELECT id FROM applications ORDER BY id")->fetchAll();
			foreach($res as $r) {
				$id = $r[0];
				$this->getlogo($id, 0);
			}
		}
	}

	private function syncAppContactItems($appid, $data) {
		$collection = new Default_Model_AppContactItems();
		$collection->filter->appid->equals($appid);
		for ($i = $collection->count()-1; $i >=0; $i--) {			
			$found = false;
			foreach ($data as $key => $value) {
				if (substr($key,0,6) === "cntpnt") {
					$datum = json_decode($value,true);
					if ( ($datum['itemtype'] === $collection->items[$i]->itemType) && 
						 ($datum['researcherid'] == $collection->items[$i]->researcherID) && 
						 ($datum['item'] == $collection->items[$i]->item) &&
						 ($datum['itemid'] == $collection->items[$i]->itemID)
					) {
						$found = true;
						break;
					}
				}
			}
			if (! $found) {
				$col2 = null;
				switch($collection->items[$i]->itemType) {
					case "vo":
						$col2 = new Default_Model_AppContactVOs();
						$col2->filter->appid->equals($appid)->and($col2->filter->void->equals($collection->items[$i]->itemID))->and($col2->filter->researcherid->equals($collection->items[$i]->researcherID));
						if (count($col2->items) > 0) $col2->remove(0);
						break;
					case "middleware":
						$col2 = new Default_Model_AppContactMiddlewares();
						$col2->filter->appid->equals($appid)->and($col2->filter->appmiddlewareid->equals($collection->items[$i]->itemID))->and($col2->filter->researcherid->equals($collection->items[$i]->researcherID));
						if (count($col2->items) > 0) $col2->remove(0);
						break;
					case "other":
						$col2 = new Default_Model_AppContactOtherItems();
						$col2->filter->appid->equals($appid)->and($col2->filter->item->equals($collection->items[$i]->item))->and($col2->filter->researcherid->equals($collection->items[$i]->researcherID));
						if (count($col2->items) > 0) $col2->remove(0);
						break;
				}
			}
		}
		$collection->refresh();
		foreach ($data as $key => $value) {
			if (substr($key,0,6) === "cntpnt") {
				$datum = json_decode($value,true);
				$found = false;
				for ($i = $collection->count()-1; $i >= 0; $i--) {
					if ( ($datum['itemtype'] === $collection->items[$i]->itemType) && 
						 ($datum['researcherid'] == $collection->items[$i]->researcherID) && 
						 ($datum['item'] == $collection->items[$i]->item) &&
						 ($datum['itemid'] == $collection->items[$i]->itemID)
					) {
						$found = true;
						break;
					}
				}
				if (! $found) {
					$item = null;
					switch ($datum['itemtype']) {
						case "vo":
							$item = new Default_Model_AppContactVO();
							$item->void = $datum['itemid'];
							break;
						case "middleware":
							$mws = new Default_Model_AppMiddlewares();
							$mws->filter->appid->equals($appid);
							$mwid = null;
							for ($j=0; $j <= count($mws->items); $j++) {
								if ($datum['itemid'] == 5) { // custom middleware, check comment
									if ($mws->items[$j]->comment == $datum['item']) {
										$mwid = $mws->items[$j]->id;
										break;
									}
								} else { // predefined middleware
									if ($mws->items[$j]->middlewareID == $datum['itemid']) {
										$mwid = $mws->items[$j]->id;
										break;
									}
								}
							}
							if ($mwid !== null) {
								$item = new Default_Model_AppContactMiddleware();
								$item->appmiddlewareid = $mwid;
							}
							break;
						case "other":
							$item = new Default_Model_AppContactOtherItem();
							$item->item = $datum['item'];
							break;
					}
					if ( $item !== null ) {
						$item->appid = $appid;
						$item->researcherid = $datum['researcherid'];
						$item->save();
					} else {
						error_log('warning: could not match to-be-inserted posted appContactItem to appropriate DB item. Possible data loss');
						error_log('posted appContactItem data: '.var_export($datum,true));
					}
				}
			}
		}
	}

	private function syncDBCollection($masterName, $masterID, $slaveName, $collectionName, $collectionItemName, &$data, $dataSlaveName = "") {
		if ( $dataSlaveName === "" ) $dataSlaveName = $slaveName;
		$collectionName = "Default_Model_".$collectionName;
		$collectionItemName = "Default_Model_".$collectionItemName;
		$collection = new $collectionName();
		$collection->filter->$masterName->equals($masterID);
		for ( $i = $collection->count()-1; $i >= 0; $i-- ) {
			$found = false;
			foreach ( $data as $key => $value ) {
				if ( strtolower(substr($key, 0, strlen($dataSlaveName))) === strtolower($dataSlaveName) ) {
					if ( $dataSlaveName == "url" ) {
						$urlData = json_decode($value, true);
                        $slaveID = $urlData['id'];
                    } elseif ( $dataSlaveName == "mw" ) {
                        $mws = new Default_Model_Middlewares();
                        $mws->filter->name->equals($value);
                        if ( count($mws->items) > 0 ) {
                            $slaveID = $mws->items[0]->id;
                        } else $slaveID = $value;
					} else {
						$slaveID = $value;
					}
					if ( $slaveID == $collection->items[$i]->$slaveName ) {
						$found = true;
						break;
					}    
				}    
			}    
			if ( ! $found ) $collection->remove($i);
		}    
		$collection->refresh();
        $j = 0;		// have a counter handy, needed in some cases
        $firstCategory = true;
		foreach ($data as $key => $value) {
			if ( strtolower(substr($key,0,strlen($dataSlaveName))) === strtolower($dataSlaveName) ) {
				$found = false;
				$slaveID = null;
				if ( $dataSlaveName == "url" ) {
					$urlData = json_decode($value, true);
					$slaveID = $urlData['id'];
					// default to http:// if relative url is given
					if (parse_url($urlData['url'], PHP_URL_SCHEME) == '') {
						$urlData['url'] = 'http://'.$urlData['url'];
					}
				} elseif ( $dataSlaveName == "mw" ) {
					$mws2 = new Default_Model_Middlewares();
					$mws2->filter->name->equals($value);
					if ($mws2->count()>0) {
							$mwid = $mws2->items[0]->id;
							$mwcomment = null;
					} else {
							$mwid = 5;
							$mwcomment = $value;
                    }
                    $slaveID = $mwid;
				} else {
					$slaveID = $value;
				}
				for ($i=$collection->count()-1; $i>=0; $i--) {
					if ( $slaveID == $collection->items[$i]->$slaveName) {
						if ( $collectionItemName != "Default_Model_AppUrl" ) $found = true;
						break;
					}    
				}    
				if ( ! $found ) {
					$collectionItem = new $collectionItemName();
					$collectionItem->$masterName = $masterID;
					$collectionItem->$slaveName = $slaveID;
					if ( $collectionItemName == "Default_Model_AppUrl" ) {
						if ( $collectionItem->$slaveName == "" ) $collectionItem->$slaveName = null;
						$collectionItem->url = $urlData['url'];
						$collectionItem->description = $urlData['type'];
						$collectionItem->title = $urlData['title'];
						$collectionItem->ord = (string)$j;					
					} elseif ( $collectionItemName == "Default_Model_AppMiddleware" ) {
						$collectionItem->middlewareID = $mwid;
						$collectionItem->comment = $mwcomment;
                    } elseif ( $collectionItemName == "Default_Model_AppCategory") {
                        if ( $firstCategory ) {
                            $collectionItem->isPrimary = true;
                            $firstCategory = false;
                        }
                    }
					$collectionItem->save();
					$j++;
				}    
			}    
		}   		
	}

    public function init()
    {
        $this->session = new Zend_Session_Namespace('default');
        $this->apisession = new Zend_Session_Namespace('api');
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addActionContext('index', 'xml')
			->addActionContext('details', 'xml')
			->initContext();
		# this line is needed in order for moderated applications to be managed correctly by managers/admins
		if ( ! isset($_GET['userid']) ) if ( $this->session->userid !== null ) $_GET["userid"] = $this->session->userid;
    }
    
	private function cachelogos($items) {
		foreach ($items as $item) {
			$fname = APPLICATION_PATH . "/../cache/app-logo-".$item->id.".png";
			$f = fopen($fname, "w");
			if (!isnull($item->logo)) {
				$logo = base64_decode(pg_unescape_bytea($item->logo));
				fwrite($f, $logo);
			} else {
				fwrite($f, 'NULL');
			}
			fclose($f);
			$fname2 = str_replace("/app-logo", "/55x55/app-logo", $fname);
			$fname3 = str_replace("/app-logo", "/100x100/app-logo", $fname);
			$fname2 = str_replace(".png", ".jpg", $fname2);
			$fname3 = str_replace(".png", ".jpg", $fname3);
			`convert -background white -flatten -strip -interlace Plane -quality 80 -scale 55x55 $fname $fname2`;
			`convert -background white -flatten -strip -interlace Plane -quality 80 -scale 100x100 $fname $fname3`;
		}
	}

    private function getlogo($id, $size)
	{
		if ($size == "") $size = 0;
		switch($size) {
			case 0:
				$size = "55x55/";
				$type = "jpg";
				break;
			case 1:
				$size = "100x100/";
				$type = "jpg";
				break;
			case 2:
				$size = "";
				$type = "png";
				break;
			default:
				$size = "55x55/";
				$type = "jpg";
		}
		$logo = 'NULL';
		$tool = false;
		if ( !( ($id == "0") || ($id == '') ) && is_numeric($id) == true )	{
			if ( file_exists(APPLICATION_PATH . "/../cache/app-logo-" . $id . "." . "png") ) {
				$logo = @file_get_contents(APPLICATION_PATH . "/../cache/" . $size . "app-logo-". $id . "." . $type);
			} 
			if ( $logo == 'NULL' || $logo == false || isnull($logo) ) {
				$type = "png";
				$apps = new Default_Model_Applications();
				$apps->filter->id->equals($id);
				if (count($apps->items) > 0) {
					if ( (! isnull($apps->items[0]->logo) ) && $apps->items[0]->logo !== '' )
						$logo=base64_decode($apps->items[0]->logo);
					else
						$logo = null;
					
					if( is_null($logo) ) {
						$logo = getPrimaryCategoryLogo($apps->items[0]);
						if(substr($logo,0,1) == "/"){
							$logo = substr($logo,1);
						}
						$logo = @file_get_contents($logo);
					}
					$this->cachelogos($apps->items);
				}
			}
		}
		if ( empty($logo) || ($logo == 'NULL') ) {
			$type = "png";
			if ($tool) {
				$logo = file_get_contents("images/tool.png");
			} else {
				$logo = file_get_contents("images/app.png");
			}
		} 
		if ($type = "jpg") $type = "jpeg";
		header('Content-type: image/' . $type);
		return $logo;
    }

	public function getlogoAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		if ( !( ($this->_getParam("id") == "0") || ($this->_getParam("id") == '') ) && is_numeric($this->_getParam("id")) == true )	{
			echo $this->getlogo($this->_getParam("id"), $this->_getParam("size"));
		}
	}

	public function getfblogoAction()
    {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$logo = 'NULL';
		$tool = false;
		$id = $this->_getParam("id");
		if( strpos($id, "_") > -1 ){
			$id = split("_", $id);
			$id = $id[0];
		}
		if ( !( ($id == "0") || ($id == '') ) && is_numeric($id) == true )	{
			if ( file_exists(APPLICATION_PATH . "/../cache/app-logo-".$id.".png") ) {
				$logo = @file_get_contents(APPLICATION_PATH . "/../cache/app-logo-".$id.".png");
			} 
			if ( $logo == 'NULL' || $logo == false || isnull($logo) ) {
				$apps = new Default_Model_Applications();
				$apps->filter->id->equals($id);
				if (count($apps->items) > 0) {
					if ( (! isnull($apps->items[0]->logo) ) && $apps->items[0]->logo !== '' )
						$logo=base64_decode($apps->items[0]->logo);
					else
						$logo = null;
					
					if( is_null($logo) ) {
						$logo = getPrimaryCategoryLogo($apps->items[0]);
						if(substr($logo,0,1) == "/"){
							$logo = substr($logo,1);
						}
						$logo = @file_get_contents($logo);
					}
					$this->cachelogos($apps->items);
				}
			}
		}
		if ( empty($logo) || ($logo == 'NULL') ) {
			if ($tool) {
				$logo = file_get_contents("images/tool.png");
			} else {
				$logo = file_get_contents("images/app.png");
			}
		} 
		header('Content-type: image/png');
		$img = imagecreatefromstring($logo);
		if( $img ){
			$minsize=210;
			$width = imagesx( $img );
			$height = imagesy( $img );
			// calculate thumbnail size
			if( $width < $height && $width < $minsize ){
				$new_width = $minsize;
				$new_height = floor( $height * ( $minsize / $width ) );
			}else if( $height < $width && $height < $minsize ){
				$new_height = $minsize;
				$new_width = floor( $width * ( $minsize / $height ) );
			}else if( $width<$minsize){
				$new_height = $minsize;
				$new_width = $minsize;
			}else{
				echo $logo;
				return;
			}
			// create a new temporary image
			$tmp_img = imagecreatetruecolor( $new_width, $new_height );
			imagealphablending($tmp_img, false);
			imagesavealpha($tmp_img, true);  
			// copy and resize old image into new image 
			imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
			imagepng( $tmp_img, null, 9);
			imagedestroy($img);
			imagedestroy($tmp_img);
			return;

		}
		
		echo $logo;
    }
    public function showlogoAction()
    {
		$this->_helper->layout->disableLayout();
		$apps = new Default_Model_Applications();
		$apps->filter->id->equals($this->_getParam("id"));
		if ( count($apps->items) > 0 ) {
			$app = $apps->items[0];
			if ( $app->logo == "" ) {
				$logo = getPrimaryCategoryLogo($app);
				if( is_null($logo) == false ){
					$this->view->logo = $logo;
					return;
				}
				$cats = $app->categoryid;
				if(count($cats) == 1){
					if($cats[0] == "2")
						$this->view->logo = "/images/tool.png";
					else
						$this->view->logo = "/images/app.png";
				}else if ($app->tool == "1")
					$this->view->logo = "/images/tool.png";
				else
					$this->view->logo = "/images/app.png";
			} else {
				$this->view->logo = "/apps/getlogo?size=2&id=".$app->id."&req=".urlencode($app->lastUpdated);
			}
		} else $this->view->logo = '';
	}

    public function indexAction(){
		$this->_helper->layout->disableLayout();
    }

	public function addratingAction() {
		$this->_helper->layout->disableLayout();
		$ratingID = $this->_getParam("ratingid");
		$rating = null;
		if ( $ratingID != '' ) {
			$ratings = new Default_Model_AppRatings();
			$ratings->filter->id->equals($ratingID);
			$ratings->refresh();
			if ( count($ratings->items) > 0 ) $rating = $ratings->items[0];
		} else {
			$rating = new Default_Model_AppRating();
		}
		$rating->appid = $this->_getParam("appid");
		$rating->rating = $this->_getParam("rating");
		if ( $rating->rating == "0" ) $rating->rating = null;
		$rating->comment = trim($this->_getParam("comment"));
		if ( $rating->comment == '' || $rating->comment == "undefined" ) {
			$rating->comment = null;
		} else {
			$rating->comment = substr($rating->comment,0,512);
		}
		$rating->submittedOn = date("Y-m-d H:i:s");
		if ( $this->_getParam("submitterid") != "" ) {
			$rating->submitterid = $this->_getParam("submitterid");
		} else {
			$rating->submittername = $this->_getParam("submittername");
			$rating->submitteremail = $this->_getParam("submittemail");
		}
		if ( $rating !== null ) {
			$rating->save();
			$apps = new Default_Model_Applications();
			$apps->filter->id = $this->_getParam("appid");
			echo '{"id":"'.$rating->id.'","average":"'.$apps->items[0]->rating.'"}';
		}
	}

	public function revokeratingAction() {
		$this->_helper->layout->disableLayout();
		$ratings = new Default_Model_AppRatings();
		if ( $this->session->userid === null ) {
			$r = json_decode($_COOKIE['ratings'],true);
			$ratingid = $r['app'.$this->_getParam("appid")];
			$ratings->filter->id->equals($ratingid);
		} else {
			$ratings->filter->appid->equals($this->_getParam("appid"))->and($ratings->filter->submitterid->equals($this->session->userid));
		}
		if ( count($ratings->refresh()->items) > 0 ) {
			$id = $ratings->items[0]->id;
			$ratings->remove($ratings->items[0]);
			$apps = new Default_Model_Applications();
			$apps->filter->id = $this->_getParam("appid");
			echo '{"id":"'.$id.'","average":"'.$apps->items[0]->rating.'"}';
		}
	}

    public function detailsAction()
	{
		$this->_helper->layout->disableLayout();
		$appID = $this->_getParam("id");
		$format = $this->_getParam("format");
		if ( $format === "json" ) $format = "xml";
        trackPage('/apps/details/'.$appID, $format);
		if ( ($appID == '') ) $appID = $this->session->lastAppID;
		$this->view->dialogCount=$_GET['dc'];
		if($appID == '0'){
			$appID = '';
		}
		$this->view->entryid = $appID;
        $this->view->session = $this->session;
		$this->view->entitytype = 'software';
        if ( $this->_getParam('histid') != '' ) $this->view->histid = $this->_getParam('histid');
        if ( $this->_getParam('histtype') != '' ) $this->view->histtype= $this->_getParam('histtype');
		if ( $this->_getParam('entitytype') != '') {
			$this->view->entitytype= strtolower( trim( $this->_getParam('entitytype') ) );
			switch ($this->view->entitytype){
				case "vappliance":
				case "virtualappliance":
					$this->view->entitytypeid = 1;
					break;
				case "softwareappliance":
				case "swappliance":
					$this->view->entitytypeid = 2;
					break;
				case "software":
				case "sw":
					$this->view->entitytypeid = 0;
					break;
				default:
					$this->view->entitytypeid = "invalid metatype";
					break;
			}
		}
		
    }

    public function exportAction() {
   		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
        if (array_key_exists("type",$_GET))	$type = $_GET['type']; else $type = 'xml';
		$apps = new Default_Model_Applications();
		$apps->filter = FilterParser::getApplications($this->_getParam("flt"));
        if ( $type === "xml" ) {
            $apps->refresh("xmlexport");
        } else {
            $apps->refresh("csvexport");
		}
		$s = '';
		foreach($apps->items as $item) {
			$s = $s . preg_replace("/[\n\r]/", '', $item) . "\n";
		}
		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename=apps'.time().'.'.$type);
		header('Content-type: text/'.($type==="xml"?"xml":"x-csv"));
		header("Pragma: no-cache");
		header("Expires: 0");
		header('Content-Length: '.strlen($s));
		if ( $type === "xml" ) {
			echo '<applications>'. $s . '</applications>';
		} else {
			echo '"Name","Description","Abstract","Date Added","Added By","Owner","Status","Categories","Middlewares","VOs","Disciplines","Countries","URLs","Researchers"' . "\n";
			echo $s;
		}
    }
   
    public function makemapAction() {
		$this->_helper->layout->disableLayout();	
		echo '<?xml version="1.0" encoding="UTF-8"?'.'>'."\n";
		echo "<map map_file='maps/world.swf' url='#movie1' width='50%' height='50%'>\n";
		echo "<areas>\n";

		$cs = new Default_Model_AppPerCountries();
		foreach ( $cs->items as $c ) {
			$line = array();
			$line['id'] = $c->id;
			$line['sum'] = $c->sum;
			$line['name'] = $c->name;
			$line['ISOcode'] = $c->ISOCode;
			$arr_name=explode("/",$line['name']);
			$arr_iso=explode("/",$line['ISOcode']);
			$arr_id=sizeof($arr_name);
			for($i=0;$i<$arr_id;$i++) {
				//use a specific random seed for the map, so it always comes up the same
				srand(($c->id+$i)^2);
				$color = "#FF".dechex(204+rand(-50,50)).dechex(rand(80,100));
				if ( isset($arr_name[$i]) && isset($arr_iso[$i]) && isset($line['sum']) && isset($line['id']) ) {
				 if( trim($arr_iso[$i]) == "RS"){
					echo "<area color='".$color."' title='".$arr_name[$i]." (".$line['sum'].")' mc_name='".trim($arr_iso[$i])."' oid='".trim($arr_iso[$i])."' link_with='RS,KV' value='".$line['id']."' url='javascript:showCountry(".$line['id'].");'></area>\n";
					echo "<area color='".$color."' title='".$arr_name[$i]." (".$line['sum'].")' mc_name='KV' oid='KV' link_with='RS,KV' value='".$line['id']."' url='javascript:showCountry(".$line['id'].");'></area>\n";
				  } else {
					echo "<area color='".$color."' title='".$arr_name[$i]." (".$line['sum'].")' mc_name='".trim($arr_iso[$i])."' oid='".trim($arr_iso[$i])."' value='".$line['id']."' url='javascript:showCountry(".$line['id'].");'></area>\n";
				  }
				}
			}
		}
		//radomize
		srand();
		echo "</areas>\n";
		echo "</map>\n";
    }
    
    private function &populateAppDoc(&$existing,&$docdatum) {
		if (array_key_exists("url",$docdatum)) $existing->url = $docdatum['url']; else $existing->url = null;
                $existing->title = $docdatum['title'];
		// Default to http:// if relative URL is given
		if (parse_url($existing->url, PHP_URL_SCHEME) == '') {
			$existing->url = 'http://'.$existing->url;
		}
		if (trim($existing->url) == 'http://') $existing->url = '';
		$existing->conference = $docdatum['conference'];
		$existing->proceedings = $docdatum['proceedings'];
		if (array_key_exists('journal',$docdatum)) $existing->journal = $docdatum['journal']; else $existing->journal = null;
		$existing->isbn = $docdatum['isbn'];
		$existing->volume = $docdatum['volume'];
		$existing->pageStart = $docdatum['pageStart'];
		if (array_key_exists('pageEnd',$docdatum)) $existing->pageEnd = $docdatum['pageEnd']; else $existing->pageEnd = null;
		if ( isnull($existing->pageStart) || ($existing->pageStart == '') ) $existing->pageStart = null;
		if ( isnull($existing->pageEnd) || ($existing->pageEnd == '') ) $existing->pageEnd = null;
		$existing->year = $docdatum['year'];
		if ( isnull($existing->year) || ($existing->year == '') ) $existing->year = null;
		$existing->publisher = $docdatum['publisher'];
		$existing->docTypeID =  $docdatum['typeID'];
		if ( $existing->Id !== null ) {
			$intAuthors = new Default_Model_IntAuthors();
			$intAuthors->refresh();
			$extAuthors = new Default_Model_ExtAuthors();
			$extAuthors->refresh();
			//remove all existing authors
			for ($j=count($existing->authors)-1; $j>=0; $j--) {
				if ( ! isnull($existing->authors[$j]->AuthorId) ) {
					$intAuthors->remove($intAuthors->item($existing->authors[$j]->Id));
				} else {
					$extAuthors->remove($extAuthors->item($existing->authors[$j]->Id));
				}
			}
			//add authors from scratch
			foreach($docdatum['intAuthors'] as $xauthor) {
				$author = new Default_Model_IntAuthor();
				$author->authorID = $xauthor[0];
				if ( $xauthor[1] == "true" ) $author->main = true; else $author->main = false;
				$author->docID = (string)($existing->Id);
				$intAuthors->add($author);
			}
			foreach($docdatum['extAuthors'] as $xauthor) {
				$author = new Default_Model_ExtAuthor();
				$author->author = $xauthor[0];
				if ( $xauthor[1] == "true" ) $author->main = true; else $author->main = false;
				$author->docID = (string)($existing->Id);
				$extAuthors->add($author);
			}
		}
		return $existing;
    }

    private function noDupes($data) {
        $r = array();
        $d = array();
        foreach ($data as $k => $v) {
            $t = '';
            if ( substr(strtolower($k),0,8) == "disciplineid" ) $t = "discipline";
            if ( substr(strtolower($k),0,10) == "categoryid" ) $t = 'category';
            if ( substr(strtolower($k),0,2) == "vo" ) $t = 'vo';
            if ( substr(strtolower($k),0,2) == "mw" ) $t = 'mw';
            if ( substr(strtolower($k),0,6) == "scicon" ) $t = 'scicon';
            if ( substr(strtolower($k),0,9) == "countryid" ) $t = 'country';
            if ( $t != '' ) {
                if ( ! in_array("$t ---> $v", $r) ) {
                    $r[] = "$t ---> $v";
                    $d[$k] = $v;
                }
            } else {
                $d[$k] = $v;
            }
        }
        return $d;
    }

	public function updateAction() {
        $data = &$_POST;
        $data = $this->noDupes($data);
    	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		db()->beginTransaction();
		try {
		if ( $data['id'] == '0' ) $data['id'] = '';
		if ( isset($data['name']) == true ) { $data['name'] = ltrim(rtrim($data['name'])); }
		if ( isset($data['description']) == true ) { $data['description'] = ltrim(rtrim($data['description'])); }
		if ( isset($data['abstract']) == true ) { $data['abstract'] = rtrim($data['abstract']); }
		if ( $this->session->userid !== null ) {
			$users = new Default_Model_Researchers();
			$users->filter->id->equals($this->session->userid);
			$user = $users->items[0];
			$apps = new Default_Model_Applications();
			if ( $data['id'] != '' ) { // UPDATE
				$apps->filter->id->equals($data['id']);
				$app = $apps->items[0];
				if ( $user->privs->canModifyApplicationName($app) ) $app->name = $data['name'];
				if ( $user->privs->canModifyApplicationDescription($app) ) $app->description = $data['description'];
				if ( $user->privs->canModifyApplicationAbstract($app) ) $app->abstract = $data['abstract'];
				if ( $user->privs->canModifyApplicationStatus($app) ) if ( $data['statusID'] != '' ) $app->statusID = $data['statusID'];
				if ($_POST['newimage'] !== "") {
					if ( $user->privs->canModifyApplicationLogo($app) ) {
						$imgfile = APPLICATION_PATH."/../public/".$_POST['newimage'];
						if ( file_exists(APPLICATION_PATH . "/../cache/app-logo-".$data["id"].".png") ) unlink(APPLICATION_PATH . "/../cache/app-logo-".$data["id"].".png");
						if ( file_exists(APPLICATION_PATH . "/../cache/55x55/app-logo-".$data["id"].".png") ) unlink(APPLICATION_PATH . "/../cache/55x55/app-logo-".$data["id"].".png");
						if ( file_exists(APPLICATION_PATH . "/../cache/100x100/app-logo-".$data["id"].".png") ) unlink(APPLICATION_PATH . "/../cache/55x55/app-logo-".$data["id"].".png");
						$app->logo = pg_escape_bytea(base64_encode(@file_get_contents($imgfile)));
					};
				};
				if ( isset($_POST['addedBy']) ) {
					if ($_POST['addedBy'] !== '') {
						if ($user->privs->canGrantOwnership($app) ) {
							$app->addedBy = $_POST['addedBy'];
							$app->ownerID = $_POST['addedBy'];
						}
					}
				}
				$app->lastUpdated = date('Y-m-d');
				$app->keywords = null;
				$app->save();
				
				if ( $user->privs->canModifyApplicationCategory($app) ) $this->syncDBCollection ("appid", $data['id'], "categoryid", "AppCategories", "AppCategory", $data);
				/* set primary category */
				$newapps = new Default_Model_Applications();
				$newapps->filter->id->equals($app->id);
				if ( count($newapps->items) > 0 ) {
					if ( count($newapps->items[0]->categories) > 0 ) {
						foreach($newapps->items[0]->categories as $cat) {
							if ( isset($data["categoryID0"]) && $cat->categoryid == $data["categoryID0"] ) {
								if ( ! $cat->isPrimary ) {
									$cat->isPrimary = true;
									$cat->save();
									break;
								}
							}
						}
					}
				}

				if ( $user->privs->canModifyApplicationDiscipline($app) ) $this->syncDBCollection("appid", $data['id'], "disciplineid", "AppDisciplines", "AppDiscipline", $data);
				
				$this->session->privs = null;
				if ( $user->privs->canModifyApplicationVO($app) ) $this->syncDBCollection("appid", $data['id'], "void", "AppVOs", "AppVO", $data, "vo");
				if ( $user->privs->canModifyApplicationMiddleware($app) ) $this->syncDBCollection("appid", $data['id'], "middlewareid", "AppMiddlewares", "AppMiddleware", $data, "mw");
				if ( $user->privs->canAssociatePersonToApplication($app) ) {
					$this->syncDBCollection("appid", $data['id'], "researcherid", "ResearchersApps", "ResearchersApp", $data, "scicon");
					$this->syncAppContactItems($data['id'], $data);
				}
				if ( $user->privs->canModifyApplicationCountry($app) ) $this->syncDBCollection("appid", $data['id'], "countryid", "AppManualCountries", "AppManualCountry", $data); 
				if ( $user->privs->canModifyApplicationURLs($app) ) $this->syncDBCollection("appid", $data['id'], "id", "AppUrls", "AppUrl", $data, "url");
				if ( $user->privs->canModifyApplicationDocuments($app) ) { // this is rather too complicated to be included in syncDBCollection...
					if ( $data['documents'] != "UNCHANGED" ) {
						$xdocs = new SimpleXMLElement($data['documents']);
						$docdata=array();
						foreach ($xdocs->document as $doc) {
							$ddd = "".$doc;
							$ddd = base64_decode($ddd);
							$ddd = urldecode($ddd);
							$ddd = json_decode($ddd,true);
							$docdata[] = $ddd;						
						}
						$docs = new Default_Model_AppDocuments();
						$docs->filter->appid->equals($data['id']);
						$docs->refresh();                                                
						$docCount = count($docs->items);
						//handle existing and deleted entries
						for ($i=$docCount-1; $i>=0; $i--) {
							$existing=null;
							foreach($docdata as $docdatum) {
								$doc = $docs->items[$i];
								if ( $doc->id == $docdatum['id'] ) {
									$existing = $this->populateAppDoc($doc,$docdatum);
									break;
								}
							}
							if ($existing === null) {
								$docs->remove($docs->items[$i]);
							} else {
								$existing->save();
							}
						}
						//handle new entries
						foreach($docdata as $docdatum) {
							if ( $docdatum['id'] == '' || strtolower($docdatum['id']) == 'null') {
								$doc = new Default_Model_AppDocument();
								//first time only main data is saved
								$doc->appID = $data['id'];
								$doc = $this->populateAppDoc($doc,$docdatum);
								$docs->add($doc);
								//second time referenced data is saved
								$doc = $this->populateAppDoc($doc,$docdatum);
								$doc->save();
							}
						}
					}
				}
			} else { // INSERT
				if ( $user->privs->canInsertApplication() ) {
					if (($data['tool'] == "true")) {                                    
						$data['tool'] = true;
					} else {
						$data['tool'] = false;
					}
					$app = new Default_Model_Application();
					$app->name = $data['name'];
					$app->description = $data['description'];
					$app->abstract = $data['abstract'];
					if ( $data['statusID'] != '' ) $app->statusID = $data['statusID'];
					//EGI RT #1820: Remove RESPECT				
					//$app->respect = $data['respect'];
					$app->respect = false;
					if ($data['tool']) {
						$app->tool = true; 
					} else {
						$app->tool = false;
					};
					$app->dateAdded = date('Y-m-d');
					$app->lastUpdated = date('Y-m-d');
					if (array_key_exists('addedBy',$_POST)) {
						if ($user->privs->canGrantOwnership($app) ) {
							$app->addedBy = $_POST['addedBy'];
							$app->ownerID = $_POST['addedBy'];
                        } else {
                            $app->addedBy = $this->session->userid;
                            $app->ownerID = $this->session->userid;
                        }
                    } else {
                        $app->addedBy = $this->session->userid;
                        $app->ownerID = $this->session->userid;
                    }
					if ($_POST['newimage'] !== "") {
						$imgfile = APPLICATION_PATH."/../public/".$_POST['newimage'];
						$app->logo = pg_escape_bytea(base64_encode(@file_get_contents($imgfile)));
					}
					$app->keywords = null;
					$apps->add($app);
					
					$this->syncDBCollection ("appid", $app->id, "categoryid", "AppCategories", "AppCategory", $data);
					$this->syncDBCollection("appid", $app->id, "disciplineid", "AppDisciplines", "AppDiscipline", $data);
					
					$this->session->lastAppID = $app->id;
					$this->session->privs = null;
				}
			}
		}
		db()->commit();
		} catch (Exception $e) {
			db()->rollBack();
			$this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 500 Internal server error");
			$this->getResponse()->setHeader("Status","500 Internal server error");
			echo base64_encode(encrypt($e, substr(ApplicationConfiguration::api('key',''), 0, 8)));
			error_log("Transaction ROLLBACKed: $e");
		}
    }

    public function editdocAction() {
        $this->_helper->layout->disableLayout();
		if (array_key_exists('data',$_GET)) $this->view->data = $_GET['data']; else $this->view->data = "''";
		$dt = new Default_Model_DocTypes();
		$this->view->docTypes = $dt->refresh();
		$this->view->people = new Default_Model_Researchers();
		$this->view->people->filter->orderBy(array('lastname','firstname'));
		$this->view->people->refresh();
    }

    public function uploadframeAction() {
		$this->_helper->layout->disableLayout();
	}
    
    public function uploadlogoAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();    
        $upload_path = APPLICATION_PATH."/../public/upload/applogo/";         //relative to this file
        $data = "";
        foreach ($_FILES as $ufile) {
            if ( ($ufile['size'] <= 204800) && ($ufile['size'] > 0) ) {                                
                $file = tempnam($upload_path,'img');
                move_uploaded_file($ufile['tmp_name'], $file);

                $type = exif_imagetype($file);
                if ( $type != '' ) {
                    `convert $file ${file}.png`;
                    $file=$file.".png";
                    list($width, $height) = getimagesize($file);

                    $file=basename($file);
                    $data .='file='.$ufile['name'].',width='.$width.',height='.$height.',filename='.$file;
                }
            } else {
                $file='';
            }
        }
        echo($file);
    }
    
    public function togglemodAction() {
		$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();    
		if ( $this->session->userid !== null ) {
			if ( userIsAdminOrManager($this->session->userid) ) {
				$apps = new Default_Model_Applications();
				$apps->filter->id->equals($this->_getParam('id'));
				$apps->refresh();
				if ( count($apps->items) > 0 ) {
					$app = $apps->items[0];
					if ($app->moderated) {
						$app->moderated = false;
					} else {
						$app->moderated = true;
						$modInfo = $app->modInfo;
						$modInfo->moddedBy = $this->session->userid;
						$modInfo->moddedOn = 'NOW()';
						$modInfo->modReason = trim($this->_getParam('reason'));
					}
					$app->save();
					echo '{"id":"'.$app->id.'","name":"'.base64_encode($app->name).'","moderated":"'.$app->moderated.'","moderatedOn":"'.date("Y-m-d H:i:s").'","reason":"'.base64_encode($app->modInfo->modReason).'","moderatorID":"'.$this->session->userid.'","moderatorFirstname":"'.base64_encode($this->session->fullName).'","moderatorLastname":""}';
				}
			}
		}
    }

    public function deleteAction() {
		$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();    
		if ( $this->session->userid !== null ) {
			$users = new Default_Model_Researchers();
			$users->filter->id->equals($this->session->userid);
			$user = $users->items[0];
			$apps = new Default_Model_Applications();
			$apps->filter->id->equals($_POST['id']);
			$apps->refresh();
			if ( $user->privs->canDeleteApplication($apps->items[0]) ) {
				$app = $apps->items[0];
				$app->deleted = true;
				$app->name = $app->name.'-DELETED-'.$app->guid;
				$app->save();
				$delinfos = new Default_Model_AppDelInfos();
				$delinfo = new Default_Model_AppDelInfo();
				$delinfo->appID = $_POST['id'];
				$delinfo->deletedBy = $this->session->userid;
				$delinfos->add($delinfo);
			}
		}
	}

        public function tagpolicyAction(){
            $this->_helper->layout->disableLayout();
            $uid = $this->session->userid;
            $urole = $this->session->userRole;
            $appid = (isset($_GET["id"])?$_GET["id"]:null);
            $app = null;
            $policy = (isset($_GET["p"])?$_GET["p"]:null);
            $policy = (is_numeric($policy)?intval($policy):null);
            if(is_null($appid)){
                $this->view->Error = "no appid is given";
                return;
            }else{
                $app = new Default_Model_Applications();
                $app->filter->id->equals($appid);
                $app->refresh();
                if($app->count()===0){
                    $this->view->Error = "Requested software not found";
                    return;
                }
                $app = $app->items[0];
            }
            if(in_array($policy,array(0,1,2))===false){
                $this->view->Error = "Invalid policy";
                return;
            }
            if(is_null($policy)){
                $this->view->Response = $app->tagpolicy;
                return;
            }
            if(is_null($uid) || is_null($urole)){
                $this->view->Error = "Only logged in users can set the tag policy value";
                return;
            }
            if ( ! ( $uid === $app->addedBy || userIsAdminOrManager($uid) ) ) {
                // neither owner nor admin
                // search for associated researchers
                $res = $app->researchers;
                $resfound = false;
                foreach($res as $r){
                    if($r->id===$uid){
                        $resfound = true;
                        break;
                    }
                }
                if($resfound===false){
                    $this->view->Error = "Permission denied";
                    return;
                }
            }
            try{
                $app->tagPolicy = $policy;
                $app->save();
            }catch(Exception $e){
                $this->view->Error = htmlspecialchars($e->getMessage(),ENT_SUBSTITUTE);
                return;
            }
            $this->view->Response = "OK";
            
        }
	public function tagsAction(){
		$this->_helper->layout->disableLayout();
		$uid = $this->session->userid;
		$urole = $this->session->userRole;
		$action = (isset($_GET["action"])?strtolower($_GET["action"]):'');
		$appid = (isset($_GET["id"])?$_GET["id"]:-1);
		$tag = (isset($_GET["tag"])?trim($_GET["tag"]):'');
		$tag = urldecode($tag);
        $tag = str_replace(" ", ".",$tag);
		
		if($appid === -1){
			$this->view->Error = "no appid given";
			return;
		}
		if($tag === '' && $action!==''){
			$this->view->Error = "no tag given";
			return;
		}
		if($action==="add" || $action==="remove"){
			if(is_null($uid)){
				$this->view->Error = "not logged in";
				return;
			}
			$apptags = new Default_Model_AppTags();
			$flt1 = $apptags->filter;
			$flt1->appid->equals($appid)->and($flt1->tag->ilike($tag));
			if (count($apptags->items) > 0) {
				if ($action === "remove") {
					if ($apptags->items[0]->researcherid !== $uid) {
						$isOwner = false;
						$isAdmin = false;
						$apps = new Default_Model_Applications();
						$apps->filter->appid->equals($appid);
						//Check if current user is the owner of the applicaiton entry
						if ( count($apps->items) > 0) if ($apps->items[0]->addedBy === $uid || $apps->items[0]->ownerid === $uid) $isOwner = true;
						//Check if current user role is administrator or manager
						if ( userIsAdminOrManager($uid) ) $isAdmin = true;
						if ( ! ($isOwner || $isAdmin) ) { 
							//check if the current user is the submitter of the tag
							$apptags = new Default_Model_AppTags();
							$flt1 = $apptags->filter;
							$flt1->appid->equals($appid)->and($flt1->tag->ilike($tag))->and($flt1->researcherid->equals($uid));
							$apptagsitems = $apptags->items;
							if( count($apptagsitems) == 0 ){
								$this->view->Error = 'permission denied';
								return;
							}
						}
					}
				}
			}
		} else if($tag!=''){
			$this->view->Error = "No action given";
			return;
		}

		$p = new Default_Model_Permissions();
		$p->filter->researcherid->equals($this->session->userid)->and($p->filter->actionid->equals(24));
		$pc = $p->count();
		if($pc===0){
			$this->view->Error = "The user is not allowed to change tags";
			return;
		}
		global $application;
		$db = $application->getBootstrap()->getResource('db');
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
		try{
			if($action==="add"){
				$t = substr($tag, 0,1);
				if(preg_match("/[A-Za-z]/",  $t)<=0){
					$this->view->Error = "Tags must start with a text character.";
					return;
				}
				if(preg_match("/[\>\<\=\!]/",$tag)>0){
					$this->view->Error = "Tag contains invalid characters (> < = !)";
					return;
				}
				if(strlen($tag)>50){
					$this->view->Error = "Tags must be less than 50 characters long.";
					return;
				}
				$tags = new Default_Model_AppTags();
				$tags->filter->appid->equals($appid)->and($tags->filter->tag->ilike($tag));
				if($tags->count()==0){
					$t = new Default_Model_AppTag();
					$t->appid = $appid;
					$t->tag = $tag;
					$t->researcherid = $uid;
					$tags->add($t);
				}
			}else if($action==="remove"){
				$tags = new Default_Model_AppTags();
				$tags->filter->appid->equals($appid)->and($tags->filter->tag->ilike($tag));
				$tags->refresh();
				if($tags->count()>0){
					$tags->remove($tags->items[0]);
				}
			}else{
				$apps = new Default_Model_Applications();
				$apps->filter->id->equals($appid);
				$c = $apps->count();
				if($c>0){
					$apps->refresh();
					$kws=$apps->items[0]->keywords;
					$kws = (is_array($kws)?implode(",",$kws):$kws);
					$kws = str_replace(array("{","}","\""), "", $kws);
					$this->view->Response =$kws;
					$this->view->total = $apps->count();
					return;
				}else{
					$this->view->Error = "Could not find the software";
				}
			}
		}catch(Exception $e){
			$this->view->Error = simpleHTML2Text($e->getMessage());
			return;
		}
		$this->view->Response = "OK";
	}

	public function nameavailAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		if ( $res === true ) {
			echo "TRUE";
		} else {
			echo "FALSE: ".$res->name;
		}
	}

	public function nameavailableAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		if($this->session->userid===null || isset($_GET["n"])===false){
			$this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
			$this->getResponse()->setHeader("Status","403 Forbidden");
			return;
		}
        $error = '';
		$reason = '';
		$name = $this->_getParam("n");
		$id = $this->_getParam("id");
		$res = validateAppName($name, $error, $reason,$id);
				
		if ( $res === true ) {
            if ( $reason !== '' ) {
                echo "<response warning='" . htmlentities($reason). "'>OK</response>";
            } else {
                echo "<response>OK</response>";
            }
		} else {
			echo "<response error='".htmlentities($error)."' reason='".htmlentities($reason)."'></response>";
		}
	}

	public function usedurltitlesAction(){
	 $this->_helper->layout->disableLayout();
	 $this->_helper->viewRenderer->setNoRender();
	 $au = new Default_Model_AppUrls();
	 $items = $au->getTitles(true);
	 header('Content-type: text/xml');
	 $res = "<response>";
	 $i = 0;
	 foreach($items as $item){
	  $res .= "<title index='".($i++)."'>" . htmlentities($item) . "</title>";
	 }
	 $res .= "</response>";
	 echo $res;
	}
	
	public function joinrequestAction(){
		$this->_helper->layout->disableLayout();
	 	$this->_helper->viewRenderer->setNoRender();
		header('Content-type: text/xml');
		$appid = -1;
		$app = null;
		//Validate user input data
		$err = "";
		$uid = $this->session->userid;
		
		//Get current user GUID
		$ps = new Default_Model_Researchers();
		$ps->filter->id->equals($uid);
		$user = $ps->items[0];
		$uguid = $user->guid;
		
		//Various validations
		if ( is_null($uid)) {
			$err = 'Must be logged in';
		} else if (isset($_GET["id"]) == false){
			$err = 'Software id is required';
		} else if ( is_numeric($_GET["id"]) ==  false) {
			$err = 'Software id is not valid';
		} else {
			$appid = $_GET["id"];
			$apps = new Default_Model_Applications();
			$apps->filter->appid->equals($appid);
			if( count($apps->items) === 0 ){
				$err = "Software not found";
			}
		}
		//Check if any error occured during validations
		if( $err !== "" ) {
			echo "<response error='" . $err . "'></response>";
			return;
		}
		
		//Check if requestor is already associated with the application
		if( $err === "" ) {
			$app = $apps->items[0];
			$rs = $app->getResearchers();
			if( count($rs) > 0 ){
				foreach($rs as $r) {
					if( $r->id == $uid){
						$err = "Already associated to the software";
						break;
					}
				}
			}
		}
		
		//Get if user only queried for user existence into contacts
		if( isset($_GET["state"]) ) {
			if( $err !== "" ){
				echo "<response>joined</response>";
			} else {
				$urs = new Default_Model_UserRequests();
				$s1 = new Default_Model_UserRequestTypesFilter();
				$s1->name->equals("joinapplication");
				$s2 = new Default_Model_UserRequestsFilter();
				$s2->targetguid->equals($app->guid)->and($s2->userguid->equals($uguid));
				$s4 = new Default_Model_UserRequestStatesFilter();
				$s4->id->equals(1);
				$urs->filter->chain($s1->chain($s2->chain($s4,"AND"),"AND"),"AND");						
				if($urs->count() > 0){
					echo "<response>pending</response>";
				}else{
					echo "<response>false</response>";
				}
			}
			return;
		}
		
		//Check if any error occured during validations
		if( $err !== "" ) {
			echo "<response error='" . $err . "'></response>";
			return;
		}
		
		//Validation is OK, continue to user request submition
		db()->beginTransaction();
		try{
			$msg = (isset($_GET["m"]))?$_GET["m"]:"";
			
			//If not in base64 format it will crash
			if($msg !== ""){
				$m = base64_decode($msg);
			}
			//Check inclusion list. This receiver will get the notification even if he is not allowed.
			if( isset($_GET["r"]) ) {
				//TODO
			}
			//Check exclution list. This receivers won't get the mail notification. 
			if( isset($_GET["e"]) ) {
				//TODO
			}
			//save request
			$ur = new Default_Model_UserRequest();
			$ur->typeid = 1;//joinapplication
			$ur->userguid = $uguid;
			$ur->userdata = $msg;
			$ur->targetguid = $app->guid;
			$ur->stateid = 1; //submitted;
			$ur->save();
			
			db()->commit();
		}catch(Exception  $e){
			db()->rollBack();
			echo "<response error='Could not save request' >" . $e->getMessage() . "</response>";
			return;
		}
		
		// Send E-Mail notifications to receivers
		try{
			UserRequests::sendEmailRequestNotifications($user, $app, $msg);
		}catch(Exception $e){
			error_log("EMAIL ERROR:Could not send email notification about user request to join software.Details:".$e->getMessage());
		}
		//respond OK
		echo "<response>ok</response>";
	}
	public function requestreleasemanagerAction(){
		$this->_helper->layout->disableLayout();
	 	$this->_helper->viewRenderer->setNoRender();
		header('Content-type: text/xml');
		$appid = -1;
		$app = null;
		//Validate user input data
		$err = "";
		$uid = $this->session->userid;
		
		//Get current user GUID
		$ps = new Default_Model_Researchers();
		$ps->filter->id->equals($uid);
		$user = $ps->items[0];
		$uguid = $user->guid;
		
		//Various validations
		if ( is_null($uid)) {
			$err = 'Must be logged in';
		} else if (isset($_GET["id"]) == false){
			$err = 'Software id is required';
		} else if ( is_numeric($_GET["id"]) ==  false) {
			$err = 'Software id is not valid';
		} else {
			$appid = $_GET["id"];
			$apps = new Default_Model_Applications();
			$apps->filter->appid->equals($appid);
			if( count($apps->items) === 0 ){
				$err = "Software not found";
			}
		}
		
		if( $err === "" ){
			$app = $apps->items[0];
			$appguid = $app->guid;
			$perms = new Default_Model_Permissions();
			$perms->filter->researcherid->equals($uid)->and($perms->filter->actionid->equals(30)->and($perms->filter->uuid->equals($appguid)));
			if( count($perms->items) > 0 ){
				$err = "Already have permissions to manage releases";
			}
		}
		
		//Check if requestor is associated with the application
		if( $err === "" ) {
			$app = $apps->items[0];
			$rs = $app->getResearchers();
			$found = false;
			if( count($rs) > 0 ){
				foreach($rs as $r) {
					if( $r->id == $uid){
						$found = true;
						break;
					}
				}
			}
			if( $found == false ){
				$err = "User must be associated to the software item as a contact.";
			}
		}
		
		//Check if any error occured during validations
		if( $err !== "" ) {
			echo "<response error='" . $err . "'></response>";
			return;
		}
		
		//User only checks the state of request
		if( isset($_GET["state"]) ) {
			$urs = new Default_Model_UserRequests();
			$s1 = new Default_Model_UserRequestTypesFilter();
			$s1->name->equals("releasemanager");
			$s2 = new Default_Model_UserRequestsFilter();
			$s2->targetguid->equals($app->guid)->and($s2->userguid->equals($uguid));
			$s4 = new Default_Model_UserRequestStatesFilter();
			$s4->id->equals(1);
			$urs->filter->chain($s1->chain($s2->chain($s4,"AND"),"AND"),"AND");						
			if($urs->count() > 0){
				echo "<response>pending</response>";
			}else{
				echo "<response>false</response>";
			}
			return;
		}
		
		//Validation is OK, continue to user request submition
		db()->beginTransaction();
		try{
			$msg = (isset($_GET["m"]))?$_GET["m"]:"";
			
			//If not in base64 format it will crash
			if($msg !== ""){
				//do nothing
			}
			//Check inclusion list. This receiver will get the notification even if he is not allowed.
			if( isset($_GET["r"]) ) {
				//TODO
			}
			//Check exclution list. This receivers won't get the mail notification. 
			if( isset($_GET["e"]) ) {
				//TODO
			}
			//save request
			$ur = new Default_Model_UserRequest();
			$ur->typeid = 2;//releasemanager
			$ur->userguid = $uguid;
			$ur->userdata = $msg;
			$ur->targetguid = $app->guid;
			$ur->stateid = 1; //submitted;
			$ur->save();
			
			db()->commit();
		}catch(Exception  $e){
			db()->rollBack();
			echo "<response error='Could not save request' >" . $e->getMessage() . "</response>";
			return;
		}
		
		// Send E-Mail notifications to receivers
		try{
			UserRequests::sendEmailRequestNotifications($user, $app, $msg, "releasemanager");
		}catch(Exception $e){
			error_log("EMAIL ERROR:Could not send email notification about user request to join software.Details:".$e->getMessage());
		}
		//respond OK
		echo "<response>ok</response>";
	}
	/*
	 * Sends a message to a related user of an application, or returns a list 
	 * of possible related users for an application (owner,contacts,ngis ...)
	 * List Mode: param : list (empty)
	 *			  param : id   (application id)
	 * 
	 * Send Mode: param : id   (application id)
	 *			  param : rid  (recipient id)
	 *            param : m    (message in base64 format)
	 */
	public function sendmessageAction(){
		$this->_helper->layout->disableLayout();
	 	$this->_helper->viewRenderer->setNoRender();
		header('Content-type: text/xml');
		
		$uid = $this->session->userid;
		
		//Validate user input data
		$err = "";
		if ( is_null($uid)) {
			$err = 'Must be logged in';
		} else if (isset($_GET["id"]) == false){
			$err = 'Software id is required';
		} else if ( is_numeric($_GET["id"]) ==  false) {
			$err = 'Software id is not valid';
		} 
		
		if( $err !== "" ) {
			echo "<response error='".$err."'></response>";
			return;
		}
		$appid = $_GET["id"];
		
		if( isset($_GET["list"]) ){
			$this->getApplicationMessageRecipients($appid);
			return;
		}
		
		if ( isset($_GET["rid"]) == false) {
			$err = 'No software contacts given';
		} else if ( isset($_GET["m"]) == false) {
			$err = 'No message to send';
		} else if ( strlen(trim($_GET["m"])) == 0 ) {
			$err = 'Empty message';
		} else {
			try{
				$rid = $_GET["rid"];
				$m = $_GET["m"];
				$m = base64_decode($m);
				if ( strlen(trim($m)) == 0 ) {
					$err = "Empty message is not allowed";
				}
			}catch(Exception $e){
				$err = "Invalid message";
			}
		}
		
		//Fetching user email
		if($err == ''){
			$err = ApplicationMessage::sendMessage($appid, $uid, $rid, $_GET["m"]);
		}
		
		//Checking for errors
		if( $err !== "" ) {
			echo "<response error='" . $err . "'></response>";
			return;
		}
		
		echo "<response>OK</response>";
	}

	private function messageRecipientToXML($i) {
		return "<user id='" . $i->id . "' firstname='" . htmlspecialchars($i->firstname, ENT_COMPAT | ENT_HTML401, "UTF-8") . "' lastname='" . htmlspecialchars($i->lastname, ENT_COMPAT | ENT_HTML401, "UTF-8") . "' countryiso='" . $i->country->isocode . "' institute='". htmlspecialchars($i->institution, ENT_COMPAT | ENT_HTML401, "UTF-8") ."' />";
	}
	
	public function getApplicationMessageRecipients($appid = 0){
		if($appid == 0 ){
			echo "<response error='no software id given'></response>";
			return;
		}
		echo "<response>";
		
		
		$apps = new Default_Model_Applications();
		$apps->filter->id->equals($appid);
		$app = $apps->items[0];
		
		//Add Application owner
		$users = new Default_Model_Researchers();
		$users->filter->id->equals($app->addedby);
		$owner = $users->items[0];
		echo "<group name='Software Entry Owner'>";
		echo $this->messageRecipientToXML($owner);
		echo "</group>";
		
		//Add Application Contacts
		$uitems = $app->researchers;
		echo "<group name='Software Main Contacts'>";
		foreach($uitems as $i){
			if( $owner->id != $i->id){
				echo $this->messageRecipientToXML($i);
			}
		}
		echo "</group>";
		
		//adding National Representatives
		$appcountries = new Default_Model_AppCountries();
		$appcountries->filter->appid->equals($appid);
		$citems = $appcountries->items;
		foreach($citems as $i){
			$countries[] = $i->id;
		}
		$users = new Default_Model_Researchers();
		$users->filter->positiontypeid->equals(6)->and($users->filter->countryid->in($countries));
		$uitems = $users->items;
		echo "<group name='National Representatives'>";
		foreach($uitems as $i){
			echo $this->messageRecipientToXML($i);
		}
		echo "</group>";
		
		//Add Managers
		$users = new Default_Model_Researchers();
		$users->filter->positiontypeid->equals(7);
		$uitems = $users->items;
		echo "<group name='Managers'>";
		foreach($uitems as $i){
			echo $this->messageRecipientToXML($i);
		}
		echo "</group>";
		
		echo "</response>";
    }

    public function historyAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
    }

	public function validateappAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		header('Content-type: text/xml');
		
		$uid = $this->session->userid;
		
		//Validate user input data
		$err = "";
		if ( is_null($uid) ) {
			$err = 'Must be logged in';
		} else if ( isset($_POST["id"]) == false ) {
			$err = 'Software id is required';
		} else if ( is_numeric($_POST["id"]) ==  false ) {
			$err = 'Software id is not valid';
		}
		
		//If provided data are valid
		if( $err == "" ) {
			$appid = intval($_POST["id"]);
			$apps = new Default_Model_Applications();
			$apps->filter->id->equals($appid);
			$apps = $apps->items;
			//Check if application exists
			if( count($apps) == 1 ) {
				$app = $apps[0];
				$ownerid = $app->ownerID;
				if ( isnull($ownerid) ) $ownerid = $app->addedBy;
				//Check if user has permissions to validate application (owner,manager,administrator)
				if( ($ownerid == $uid) || userIsAdminOrManager($uid) ) {
					$app->lastupdated = date('Y-m-d');
					$app->save();
				} else {
					$err = "User has no permissions to validate the software data";
				}
			} else {
				$err = "The software is not found";
			}
		}
		
		if( $err !== "" ) {
			echo "<response error='".$err."'>error</response>";
		} else {
			echo "<response>success</response>";
		}
	}
	public function dispatchoutdatedmailsAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		// Prevent malicious calls
		if ( localRequest() ) {
			$isReminder = false;
			if(isset($_GET["reminder"])){
				$isReminder = true;
			}
			OutdatedApplication::sendMessages($isReminder);
		}
		
		
	}
	
	public function alphanumericreportAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		header('Content-type: text/xml');
		$flt = "";
		$subtype = null;
		if( isset($_GET["flt"]) && trim($_GET["flt"]) !== "") {
			$flt = $_GET["flt"];
		}
		if( isset($_GET["subtype"]) && trim($_GET["subtype"]) !== "") {
			$subtype = $_GET["subtype"];
		}
		$r = getAlphnumericReport("applications", $flt, $subtype);
		$len = count($r);
		echo "<report count='".$len."'>";
		for( $i = 0; $i < $len; $i+=1 ) {
			echo "<item count='" . $r[$i]["cnt"] . "' value='" . $r[$i]["typechar"] . "' />";
		}
		echo "</report>";
	}
	
	public function syncreleaseAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		header('Access-Control-Allow-Origin: *');
		
		//Retrieve request data
		$data = array(
			"swid" => trim( ( ( isset($_POST["swid"]) )?$_POST["swid"]:"" ) ),
			"releaseid" => trim( ( ( isset($_POST["releaseid"]) )?$_POST["releaseid"]:"" ) ),
			"state" => trim( ( ( isset($_POST["state"]) )?$_POST["state"]:"1" ) ),
			"manager" => trim( ( ( isset($_POST["manager"]) )?$_POST["manager"]:"0" ) ),
			"series" => trim( ( ( isset($_POST["series"]) )?$_POST["series"]:"" ) ),
			"release" => trim( ( ( isset($_POST["release"]) )?$_POST["release"]:"" ) ),
			"addedon" => trim( ( ( isset($_POST["addedon"]) )?$_POST["addedon"]:"" ) ),
			"publishedon" => trim( ( isset($_POST["publishedon"]) )?$_POST["publishedon"]:"" ),
			"lastupdated" => trim( ( isset($_POST["lastupdated"]) )?$_POST["lastupdated"]:"" ),
			"action" => trim( ( ( isset($_POST["action"]) )?$_POST["action"]:"insert" ) )
		);
		
		//Validate request
		if( localRequest() == false || 
			$_SERVER["REQUEST_METHOD"] != "POST" ||
			is_numeric( $data["releaseid"] ) == false ||
			in_array($data["action"], array("insert","delete","update")) == false			
		){
			return;
		}
		
		//Execute request
		$result = CommunityRepository::syncSoftwareRelease($data);
		$result = ( ($result === true)?"ok":$result );
		echo $result;
	}
	
	public function togglefollowAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		$uid = $this->session->userid;
		$id = ( isset($_GET["id"])?$_GET["id"]:"" );
		$entryid = ( isset($_GET["entryid"])?$_GET["entryid"]:"" );
		if( is_numeric($id) == false ){
			$id = ( isset($_GET["entryid"])?$_GET["entryid"]:"" );
		}
		if( $_SERVER['REQUEST_METHOD'] !== "GET" ||
			is_numeric($uid) == false ||
			is_numeric($id) == false 
		){
			header("Status: 404 Not Found");
			return;
		}
		
		//Check if user wants to unsubscribe.
		//First check if given id is a mail subscription
		$subsc = null;
		$subscriptions = new Default_Model_MailSubscriptions();
		$subscriptions->filter->id->equals($id)->and($subscriptions->filter->researcherid->equals($uid));
		if( count($subscriptions->items) == 0 ){
			//else check if there is a subscription by application id
			$subscriptions = new Default_Model_MailSubscriptions();
			$flt = "=application.id:" . $id . " id:SYSTAG_FOLLOW";
			$subscriptions->filter->flt->ilike($flt)->and($subscriptions->filter->researcherid->equals($uid));
		}
		//Check if subscription is found and unsubscribe
		if( count($subscriptions->items) > 0 ){
			$subsc = $subscriptions->items[0];
			$_GET["id"] = $subsc->id;
			$_GET["pwd"] = md5($subsc->unsubscribePassword);
			$_GET["src"] = "ui";
			require_once("NewsController.php");
			$news = new NewsController($this->getRequest(), $this->getResponse(), $this->getInvokeArgs());
			$news->unsubscribeAction();
			return;
		}
		
		//User wants to subscribe an application
		//Check if application exists
		$apps = new Default_Model_Applications();
		$id = ( is_numeric($entryid)?$entryid:$id );
		$apps->filter->id->equals($id);
		if( count($apps->items) == 0 ){
			header('Content-type: text/xml');
			echo "<response error='Software not found'>error</response>";
			return;
		}
		//Application exists. Proceed with subscription
		$app = $apps->items[0];
		unset($_GET["id"]);
		$_GET["flt"] = base64_encode("=application.id:" . $id . " id:SYSTAG_FOLLOW");
		$_GET["name"] = $app->name . " Subscription";
		$_GET["subjecttype"] = "app-entry";
		$_GET["delivery"] = "2";
		$_GET["events"] = "30";
		require_once("NewsController.php");
		$news = new NewsController($this->getRequest(), $this->getResponse(), $this->getInvokeArgs());
		$news->subscribeAction();
		return;
	}
	
	public function checkurlAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		header('Content-type: text/xml');
		$url = ( isset($_GET["url"])?$_GET["url"]:"" );
		$mimetype = ( isset($_GET["mime"])?$_GET["mime"]:"" );
		if( trim($url) === "" ){
			echo "<response result='error' message='No url specified' ></response>";
			return;
		}
		$url = @base64_decode($url);
		if( !is_string($url) || trim($url) === "" ){
			echo "<response result='error' message='No url specified' ></response>";
			return;
		}
		if( !filter_var($url, FILTER_VALIDATE_URL) ) {
			echo "<response result='error' message='Invalid url format' ></response>";
			return;
		}
		
		$file_headers = @get_headers($url,1);
		
		if(  count($file_headers) === 0  || !isset($file_headers[0]) || trim( $file_headers[0] ) === ""  ) {
			echo "<response result='error' message='Resource could not be found' ></response>";
			return;
		}else{
			foreach($file_headers as $k=>$v){
				if( is_numeric($k) ){
					list($httpversion,$httpstatus_code,$httpmsg) = explode(' ',$v, 3);
					$httpstatus_code = trim($httpstatus_code);
					if( !is_numeric($httpstatus_code) ){
						echo "<response result='error' message='Resource could not be found' ></response>";
						return;
					}
					$httpstatus_code = intval($httpstatus_code);
					if( $httpstatus_code < 200 || $httpstatus_code > 399 ){
						echo "<response result='error' message='Resource could not be found' ></response>";
						return;
					}
				}
			}
		}
		
		$warning = "";
		if( isset($file_headers["Content-Type"]) ){
			$ct = strtolower(trim($file_headers["Content-Type"]));
			$ct = explode(";", $ct);
			$ct = $ct[0];
			if( $mimetype === "binary"){
				switch($ct){
					case "application/x-7z-compressed":
					case "application/x-rar-compressed":
					case "application/x-stuffit":
					case "application/x-tar":
					case "application/x-zip":
					case "application/x-gzip":
					case "application/zip":
					case "application/gzip":
					case "application/octet-stream":
						break;
					default:
						if( strpos($ct, "application/x-") === false){
							$warning = "Resource is available but returns " . $ct . " instead of binary content.";
						}
						break;
				}
			}
		}
		if( $warning !== "" ){
			echo "<response result='warning' message='" . $warning . "' ></response>";
		}else{
			echo "<response result='success' message='Resource is available' ></response>";
		}
		
	}
	
	public function vmc2appdbAction(){
		$this->_helper->viewRenderer->setNoRender();
		if (ApplicationConfiguration::isProductionInstance() ) { 
			header('HTTP/1.0 404 Not Found');
			header("Status: 404 Not Found");
			return;
		}
		if( $_SERVER['REQUEST_METHOD'] === "GET" ){
			echo '<form action="/apps/vmc2appdb" id="vmc2appdb" name="vmc2appdb" method="post" target="_blank">';
			echo '<textarea rows="30" cols="100" name="data" id="data" ></textarea>';
			echo '<div><label for="appid">Application Id:</label><input type="text" value="" name="appdbid" id="appdbid"></div>';
			echo '<div><input type="submit" value="view transformed xml"></div>';
			echo '<div><input type="button" id="submitxml" value="Call API" ></div>';
			echo '<div class="reply"></div>';
			echo '</form>';
			echo '<script type="text/javascript">';
			echo 'appdb.utils.Vm2Appdb.init();';
			echo '</script>';
		}else{
			$this->_helper->layout->disableLayout();
			if( isset($_POST["data"]) === false ){
				echo '<html><head></head><body>';
				echo '<div>no data posted</div>';
				echo '</body></html>';
			}else{
				header('Content-type: text/xml');
				$data = $_POST["data"];
				echo VMCaster::transformXml($data);
			}
		}
	}
	
	public function sharecountreportAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		if ( localRequest() === false ) { 
			header('HTTP/1.0 404 Not Found');
			header("Status: 404 Not Found");
			return;
		}
		$recipients = array();
		if( isset($_GET["recipients"]) ){
			$recipients = trim($_GET["recipients"]);
			if( !is_numeric($recipients) && trim($recipients) !== "" ){
				$recipients = explode(";", $recipients);
			}else{
				$recipients = array();
			}
		}
		set_time_limit(1800); //set timeout of script to 30 minutes
		SocialReport::generateReports($recipients);
	}
	
	public function integritycheckAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		if( isset($_GET["versionid"]) && is_numeric($_GET["versionid"])){
			$res = VMCaster::statusIntegrityCheck($_GET["versionid"]);
			header('Content-type: application/json');
			echo json_encode($res,JSON_HEX_TAG | JSON_NUMERIC_CHECK );
		}else{
			header('HTTP/1.0 404 Not Found');
			header("Status: 404 Not Found");
		}
		
	}
	
	public function vappimageAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		$format =$this->getRequest()->getParam("format");
		$strict = ((isset($_GET["strict"]))?true:false);
		
		$guid = trim($this->getRequest()->getParam("guid"));
		if( $format === null || $format === "json"){
			header('Content-type: application/json');
		}else if( $format === "xml" ){
			header('Content-type: application/xml');
		}
		
		if( $guid !== ""){
			$imageid = null;
			if( strpos($guid, ":") !== false ){
				$tmp = explode(":",$guid);
				if( count($tmp) > 1 ){
					$guid = $tmp[0];
					$imageid = $tmp[1];
				}
			}
			if( $imageid !== null ){
				$result = VMCaster::getImageInfoById($imageid,$guid, $strict);
			}else{
				$result = VMCaster::getImageInfoByIdentifier($guid);
			}
			$canaccessvadata = false;
			$privs = null;
			$user = null;
			if( $result !== null ){
				$result["isprivateimage"] = false;
				$result["canaccessprivate"] = true;
				$vapp = $result["va"];
				if( $vapp->imglstprivate ){
					$result["isprivateimage"] = true;
					$result["canaccessprivate"] = false;
					
					$vapp = $result["va"];
					$app = $vapp->getApplication();
					$privs = null;
					$users = new Default_Model_Researchers();
					$users->filter->id->equals($this->session->userid);
					if( count($users->items) > 0 ){
						$user = $users->items[0];
						$privs = $user->getPrivs();
					}
					if( $privs !== null ){
						$canaccessvadata = $privs->canAccessVAPrivateData($app->guid);
					}
					
					$result["canaccessprivate"] = $canaccessvadata;
				}
			}
			
			if( $result !== null ){
				$result['sites'] = VMCaster::getSitesByVMI($guid, $imageid);
			}
			
			if( $result !== null && $format == null ){ //UI call
				$result["result"] = "success";
				$va = $result["va"];
				$app = $va->getApplication();
				$version = $result["version"];
				$image = $result["image"];
				
				$result["app"] = array("id"=>$app->id,"name"=>$app->name,"cname"=>$app->cname);
				$result["va"] = array("id"=>$va->id);
				$result["version"] = array("id"=>$version->id,"version"=>$version->version,"published"=>$version->published,"archived"=>$version->archived,"enabled"=>$version->enabled);
				$result["image"] = array("id"=>$image->id,"identifier"=>$image->guid);
				echo json_encode($result,JSON_HEX_TAG | JSON_NUMERIC_CHECK );
				return;
			}else if( $format !== null) {
				if( $result !== null ){
					$result = VMCaster::convertImage($result, $format);
				}
				if( $result !== null ){
					echo $result;
				}else{
					header('HTTP/1.0 404 Not Found');
					header("Status: 404 Not Found");
				}
				return;
			}
		}
		echo json_encode(array("result"=>"error", "message"=>"Image not found"));
	}
	
	public function privsAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		if( $_SERVER['REQUEST_METHOD'] !== "POST" ||
			$this->session->userid === null || 
			intval($this->session->userid)<=0 ||
			isset($_POST["data"]) === false )
		{
			header('HTTP/1.0 404 Not Found');
			header("Status: 404 Not Found");
			return;
		}
		
		try{
			$data = json_decode($_POST["data"],true,10);
			if( isset($data["targetid"]) === false || is_numeric($data["targetid"]) === false ) {
				throw new Exception("No target specified");
			}
			if( isset($data["privs"]) === false || is_array($data["privs"]) === false ){
				throw new Exception("No privileges defined");
			}
		}catch(Exception $e){
			echo "<response error='Invalid data' errormessage='" . $e->getMessage() . "'></response>";
			return;
		}
		
		$response = $this->setPrivs($this->session->userid, $data["targetid"], "software", $data["privs"] );
		
		if( $response !== true ){
			echo "<response error='Could not set privileges' errormessage='"+$response+"'></response>";
			return;
		}
		$this->refreshPrivs();
		echo "<response success='true'></response>";
	}
	private function refreshPrivs(){
		$clean = 1;
		$tries = 10;
		while( $clean !== 0 && $tries > 0){
			db()->setFetchMode(Zend_Db::FETCH_NUM);
			$res = db()->query("SELECT data FROM config WHERE var = 'permissions_cache_dirty'")->fetchAll();
			$val = $res[0];
			$clean = intval($val[0]);
			$tries -= 1;
			usleep(250000);
		}
		return true;
	}
	//$privs -> [ {suid: <userid>, grant: [<actionid>,<actionid>,...], revoke: [<actionid>,<actionid>...]}, { actorid:....},...]
	private function setPrivs($userid, $targetid, $targetType = "software", $privs= array() ){
		/*
		 * $cuser -> current user
		 * $cprivs -> current user's privileges
		 * $target -> current target 
		*/
		
		//Check current user's existence and privileges
		$users = new Default_Model_Researchers();
		$users->filter->id->equals($userid);
		if( count($users->items) === 0 ){
			return "Access denied for not authenticated users.";
		}
		$cuser = $users->items[0];
		$cprivs = $cuser->getPrivs();
		if( !$cprivs ){
			return "No privileges found for current user.";
		}
		
		switch( strtolower(trim($targetType)) ){
			case "software":
			case "vappliance":
			default:
				$targets = new Default_Model_Applications();
				$targets->filter->id->equals($targetid);
				if( count($targets->items) > 0) {
					$target = $targets->items[0];
				}
				break;
		}
		//Check if target is real
		if( $target === null ){
			return "Could not find target with id: " . $targetid;
		}
	
		//Check if current user can grant/revoke privileges for the given target
		if( $cprivs->canGrantPrivilege($target) === false ){
			return "No access to grant or revoke privileges for the given target.";
		}
		
		//Iterate through privileges
		for( $i=0; $i<count($privs); $i+=1 ){
			$p = $privs[$i];
			$actorsuid = ( (isset($p["suid"]) === true )?trim($p["suid"]):"" );
			
			if( $actorsuid === "" ) {
				continue;
			}
			
			$grantids = ( (isset($p["grant"]) === true && is_array($p["grant"]) )?$p["grant"]:array() );
			$revokeids = ( (isset($p["revoke"]) === true && is_array($p["revoke"]) )?$p["revoke"]:array() );
			
			if( count($grantids) > 0 ){
				$this->grantPrivs($cuser, $target, $actorsuid, $grantids);
			}
			
			if( count($revokeids) > 0 ){
				$this->revokePrivs($cuser, $target, $actorsuid, $revokeids);
			}
		}
		return true;
	}
	
	private function grantPrivs($user, $target, $actorsuid , $actionids=array()){
		$actors = new Default_Model_Researchers();
		$actors->filter->guid->equals($actorsuid);
		
		if( count($actors) === 0 ){
			return "Could not find actor with suid: " . $actorsuid;
		}
		
		$actor = $actors->items[0];
		$aprivs = $actor->getPrivs();
		
		db()->beginTransaction();
		for($i=0; $i<count($actionids); $i+=1){
			$actionid = $actionids[$i];
			if( is_numeric($actionid) === true ){
				$aprivs->grantAccess($actionid, $target->guid);
			}
		}
		db()->commit();
		return true;
	}
	
	private function revokePrivs($user, $target, $actorsuid, $actionids=array()){
		$actors = new Default_Model_Researchers();
		$actors->filter->guid->equals($actorsuid);
		
		if( count($actors) === 0 ){
			return "Could not find actor with suid: " . $actorsuid;
		}
		
		$actor = $actors->items[0];
		$aprivs = $actor->getPrivs();
		
		db()->beginTransaction();
		for($i=0; $i<count($actionids); $i+=1){
			$actionid = $actionids[$i];
			if( is_numeric($actionid) === true ){
				$aprivs->revokeAccess($actionid, $target);
			}
		}
		db()->commit();
		
		return true;
	}
	
	public function privacyAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$entityId = ( (isset($_GET["id"]))?$_GET["id"]:null );
		$entityId = ( ($entityId===null && isset($_POST["id"]) )?$_POST["id"]:$entityId );
		$isget = ($_SERVER['REQUEST_METHOD'] === "GET")?true:false;
		$ispost = ($_SERVER['REQUEST_METHOD'] === "POST")?true:false;
		$pdata = ( ( $ispost && isset($_POST["data"]))?$_POST["data"]:null );
		$data = array();
		
		if( $this->session->userid === null || 
			intval($this->session->userid)<=0 ||
			is_numeric($entityId) === false || 
			( $ispost === true && $pdata === null ) ||
			( !$ispost && !$isget )
		) {
			header('HTTP/1.0 404 Not Found');
			header("Status: 404 Not Found");
			return;
		}
		
		//Find item by given Id
		$apps = new Default_Model_Applications();
		$apps->filter->id->equals($entityId);
		if( count($apps->items) === 0 ){
			echo "<response error='Invalid data' errormessage='Could not find item'></response>";
			return;
		}
		$app = $apps->items[0];
		$vas = new Default_Model_VAs();
		$vas->filter->appid->equals($app->id);
		if( count($vas->items) === 0 ){
			echo "<response error='Invalid data' errormessage='Could not find item'></response>";
			return;
		}
		$va = $vas->items[0];
		
		//In case of GET return state
		if( $isget === true ){
			$isPrivate = filter_var($va->getImgLstPrivate(), FILTER_VALIDATE_BOOLEAN);
			$state = "public";
			if( $isPrivate === true ){
				$state = "private";
			}
			echo "<response state='" . $state . "'></response>";
			return;
		}
		
		//In case of POST set and return state
		if( $ispost === true){ 
			try{
				$data = json_decode($pdata,true,10);
			} catch (Exception $ex) {
				echo "<response error='Invalid data' errormessage='Could not parse given data'></response>";
				return;
			}
			
			if( isset($data["state"]) === false || in_array(strtolower( trim( $data["state"] ) ), array("private","public")) === false){
				echo "<response error='Invalid data' errormessage='Invalid data given'></response>";
				return;
			}
			$state = strtolower( trim( $data["state"] ) );
			if( $state === "private"){
				$va->imglstprivate = true;
			}else{
				$va->imglstprivate = false;
			}
			$va->save();
			echo "<response state='" . $state . "'></response>";
			return;
		}
	}
	
	public function sitesAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$entityId = ( (isset($_GET["id"]))?trim($_GET["id"]):null );
		if( is_numeric($entityId) === false ){
			header('HTTP/1.0 404 Not Found');
			header("Status: 404 Not Found");
			return;
		}
		header('Content-type: application/xml');
		echo VAProviders::getProductionImages($entityId);
	}
	
	public function contactvosAction(){
		$this->_helper->layout->disableLayout();
		$vappid = ( ( isset( $_GET["id"] ) && is_numeric( $_GET["id"] ) )?trim( $_GET["id"] ) : 0 );
		$isget = ($_SERVER['REQUEST_METHOD'] === "GET")?true:false;
		$ispost = ($_SERVER['REQUEST_METHOD'] === "POST")?true:false;
		$vappliance = VoAdmin::getVAppliance($vappid);
		$vappversion = VoAdmin::getVAppVersion($vappliance);
		$user = VoAdmin::getUser($this->session->userid);
		$canmanagevas = false;
		if( $user !== null && $vappliance !== null){
			$privs = $user->getPrivs();
			if( $privs !== null && $privs->canManageVAs($vappliance->guid)){
				$canmanagevas = true;
			}
		}
		
		if( $vappid <= 0 ||
			$vappliance === null ||
			$user === null ||
			$canmanagevas === false ||
			( $isget === false && $ispost === false )
		){
			$this->_helper->viewRenderer->setNoRender();
			header('HTTP/1.0 404 Not Found');
			header("Status: 404 Not Found");
			return;
		}
		if( $isget === true ){
			$this->view->volist = VoAdmin::getEndorsedVos($vappliance, true);
			return;
		}
		$this->_helper->viewRenderer->setNoRender();
		$notificationtype = ( ( isset( $_POST["notificationtype"] ) )?trim( $_POST["notificationtype"] ) : "" );
		$subject = ( ( isset( $_POST["subject"] ) )?trim( $_POST["subject"] ) : "" );
		$message = ( ( isset( $_POST["message"] ) )?trim( $_POST["message"] ) : "" );
		$vos = ( ( isset( $_POST["vos"] )  )?( $_POST["vos"] ) : "[]" );
		$vos = json_decode($vos);
		$preview = ( ( isset( $_POST["preview"] )  )?trim( $_POST["preview"] ) : "false" );
		$preview = ( ( $preview === "true" )?true:false );
		
		$output = array();
		if( $preview === true ){
			$result = VoContact::createVONotification($user, $vappliance, $notificationtype, $vos, $subject, $message, $output);
			if( $result !== true ){
				if( $result === false ){
					$result = "Could not create notification";
				}
				echo "<response error='" . $result . "' ></response>";
			}else{
				echo "<response success='true'>";
				echo "<message>". htmlentities($output["message"]) . "</message>";
				echo "<from name='" . htmlentities($output["username"]) . "' email='" . htmlentities($output["useremail"]) . "'></from>";
				if( userIsAdminOrManager($user->id) === true && isset($output["recipients"]) ){
					echo "<vorecipients>";
					foreach($output["recipients"] as $rec){
						echo "<vo id='" . htmlentities($rec["void"]) .  "' name='" . htmlentities($rec["vo"]) ."' >";
						foreach($rec["contacts"] as $cont){
							echo "<contact name='" . htmlentities($cont["name"]) . "' email='" . htmlentities($cont["email"]) . "' role='" . htmlentities($cont["role"]) . "' ></contact>";
						}
						echo "</vo>";
					}
					echo "</vorecipients>";
				}
				echo "</response>";
			}
		}else{
			$result = VoContact::sendVONotification($user, $vappliance, $notificationtype, $vos, $subject, $message);
			if( $result === true ){
				echo "<response success='true'></response>";
			}else{
				if( $result === false ){
					$result = "Could not create notification";
				}
				echo "<response error='" . $result . "' ></response>";
			}
		}
	}
	
	public function dispatchvaexpirationAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$days = ( ( isset( $_GET["days"] )  )?( $_GET["days"] ) : "none" );
		$dispatch = ( ( isset( $_GET["dispatch"] )  )?( $_GET["dispatch"] ) : "false" );
		$islocal = localRequest();
		$isAdmin = userIsAdminOrManager($this->session->userid);
		
		if(strtolower(trim($dispatch)) === "true"){
			$dispatch = true;
		}else{
			$dispatch = false;
		}
		
		if( is_numeric($days) === false || 
		  ( $dispatch === false && $isAdmin === false ) ||
		  ( $dispatch === true && $islocal === false )
		){
			header('HTTP/1.0 404 Not Found');
			header("Status: 404 Not Found");
			return;
		}
		if( $dispatch === false ){
			$res = VMCasterNotifications::getExpirationNotificationList($days);
			echo "<h2>Expiration of vappliances in " . $days . " days </h2><h4>(" . count($res) ." messages)</h4>";
			foreach ($res as $r){
				$user = $r["user"];
				echo "<div class='notification' style='border:1px solid #aaa;background-color:#f8f8f8;margin: 5px;margin-bottom:20px;padding:10px;'>";
				echo "<div class='recipient'>recipient:  <pre style='display:inline;'>" . $user["name"] . " &lt;" . $r["recipient"] ."&gt;</pre></div>";
				echo "<div class='subject'>subject:    <pre style='display:inline;'>" . $r["subject"] . "</pre></div>";
				echo "<div style='padding:5px;border:1px solid #bbb;background-color:#fefefe;margin-top:5px;padding:3px;'><pre style='padding:5px;'>" . htmlentities($r["message"]) . "</pre></div>";
				echo "</div>";
			}
		}else{
			VMCasterNotifications::sendExpirationNotificationList($days);
		}
	}
	public function dispatchswapplianceoutdatedAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$dispatch = ( ( isset( $_GET["dispatch"] )  )?( $_GET["dispatch"] ) : "false" );
		$islocal = localRequest();
		$isAdmin = userIsAdminOrManager($this->session->userid);
		
		if(strtolower(trim($dispatch)) === "true"){
			$dispatch = true;
		}else{
			$dispatch = false;
		}
		
		if( ( $dispatch === false && $isAdmin === false ) ||
			( $dispatch === true && $islocal === false  )
		){
			header('HTTP/1.0 404 Not Found');
			header("Status: 404 Not Found");
			return;
		}
		
		if( $dispatch === false ){
			$res = ContextualizationNotifications::getNotificationList();
			
			foreach ($res as $r){
				echo "<h1>" . $r["user"]["name"] . " [" . implode(",",$r["recipient"]) . "]</h1>";
				echo "<h2>Subject: " . $r["subject"] . "</h2>";
				echo "<h3 style='border:1px solid #aaa;background-color:#f8f8f8;margin: 5px;margin-bottom:20px;padding:10px;'><pre>";
				echo $r["message"];
				echo "</pre></h3><br/>";
			}
		}else{
			ContextualizationNotifications::sendNotificationList();
		}
	}
	public function cleararchivedvappversionsAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$islocal = localRequest();
		$appid = ( ( isset( $_GET["id"] )  )?( $_GET["id"] ) : "0" );
		$fromindex = ( ( isset( $_GET["fromindex"] )  )?( $_GET["fromindex"] ) : "200" );
		
		if(
			!is_numeric($appid) || 
			intval($appid) <=0 ||
			!$islocal
		){
			header('HTTP/1.0 404 Not Found');
			header("Status: 404 Not Found");
			return;
		}
		$appid = intval($appid);
		$fromindex = intval($fromindex);
		echo VMCaster::cleararchivedvappversions($appid, $fromindex);
	}
	
	public function vmicontextscriptAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$userid = $this->session->userid;
		$action = null;
		$ispseudocall = false;
		if($_SERVER['REQUEST_METHOD'] === "POST" ){
			$action = "set";
		}else if($_SERVER['REQUEST_METHOD'] === "DELETE" ){
			$action = "remove";
		}
		$vmiinstanceid = ( ( isset( $_GET["vmiid"] )  )?( $_GET["vmiid"] ) : 0 );
		if( $vmiinstanceid === "{id}" || $vmiinstanceid === 0){
			$vmiinstanceid = null;
		}
		$appid = ( ( isset( $_GET["appid"] )  )?( $_GET["appid"] ) : null );
		$formatid = ( ( isset( $_GET["formatid"] )  )?( $_GET["formatid"] ) : null );
		
		$url =  urldecode(trim( ( ( isset( $_GET["url"] )  )?( $_GET["url"] ) : "" ) ));
		
		if( is_numeric($userid) === false ||
			in_array($action, array("set","remove")) === false ||
			(is_numeric($vmiinstanceid)===false && $vmiinstanceid!==null)||
			$url === ""
		){
			header('HTTP/1.0 404 Not Found');
			header("Status: 404 Not Found");
			return;
		}
		
		$result = ContextualizationScripts::contextualizationScriptAction($userid, $action, $url, $vmiinstanceid, $appid, $formatid);
		if( is_string($result) ) {
			echo "<result error='".htmlspecialchars($result, ENT_QUOTES)."'></result>";
		}else if( $result === false ) {
			echo "<result error='Unknown error occured.'></result>";
		}else if( $result === true ) {
			echo "<result success='true'></result>";
		}else {
			$format = $result->getContextFormat();
			echo "<result>" ;
			echo "<contextscript id='" . $result->id . "' addedon='" . $result->addedon . "'>";
			echo "<name>" . htmlspecialchars($result->name, ENT_QUOTES) . "</name>";
			echo "<title>" . htmlspecialchars($result->title, ENT_QUOTES) . "</title>";
			echo "<url>" . urlencode($result->url) . "</url>";
			echo "<format id='".$result->formatid."'>" . $format->name . "</format>";
			echo "<checksum hashtype='" . $result->checksumfunc . "'>" . $result->checksum . "</checksum>";
			echo "<size>" . $result->size . "</size>";
			echo "</contextscript></result>";
		}
	}
	
	public function vapplianceusedversionsAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		header('Content-type: application/json');
		$appid = intval( $this->_getParam('appid') );
		if( $appid === 0 )
		{
			echo json_encode(array("result" => array("error" => "No vappliance id given")));
			return;
		}
		$versions = VApplianceVersionState::getVapplianceUsedVersions($appid);
		echo json_encode( array("result" => array("success" => true, "versions"=> $versions) ) );
	}
	
}
