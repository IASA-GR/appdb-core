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

class RestAppReport extends RestROSelfAuthResourceList {
    /**
     * realization of getDataType from iRestResource
     */
	public function getDataType() {
        return "list";
    }

    protected function _list() {
		if (parent::_list() !== false) {
			$limit = $this->_pageLength;
			$offset = $this->_pageOffset;
			db()->setFetchMode(Zend_Db::FETCH_NUM);
			$res = db()->query("SELECT * FROM app_xml_report(?, ?, ?, 'listing')", array($this->getParam("id"), $this->_pageLength, $this->_pageOffset))->fetchAll();
			$ret = array();
			foreach ($res as $r) {
				$ret[] = $r[0];
			}
			return new XMLFragmentRestResponse($ret, $this);
		} else {
			return false;
		}
 }

    /**
     * overrides RestResource::get
     */
	public function get() {
		if (parent::get() !== false) {
			$limit = $this->_pageLength;
			$offset = $this->_pageOffset;
			db()->setFetchMode(Zend_Db::FETCH_NUM);
			$res = db()->query("SELECT * FROM app_xml_report(?, ?, ?, ?)", array($this->getParam("id"), $this->_pageLength, $this->_pageOffset, $this->_listMode))->fetchAll();
			$ret = array();
			foreach ($res as $r) {
				$ret[] = $r[0];
			}
			return new XMLFragmentRestResponse($ret, $this);
		} else {
			return false;
		}
	}

}

function normalizeAppID($resource, $paramName = "id") {
	$id = $resource->getParam($paramName);
	if ( ! is_numeric($id) ) {
		$id = "(SELECT id FROM applications WHERE cname ILIKE '" . pg_escape_string(substr($resource->getParam($paramName), 2)) . "' FETCH FIRST 1 ROWS ONLY)";
	}
	return $id;
}

/**
 * class RestAppXMLParser
 * derived class for parsing application resources
 */
class RestAppXMLParser extends RestXMLParser {
    /**
     * eliminate duplicate entries for properties with 0..* cardinality, so 
     * that they may not be inserted twice in the database
     *
     * @param string $data[] array of application property data
     *
     *
     */
    protected function noDupes($data) {
        $r = array();
        $d = array();
        foreach ($data as $k => $v) {
            $t = '';
            if ( substr(strtolower($k),0,12) == "disciplineid" ) $t = 'discipline';
            if ( substr(strtolower($k),0,10) == "categoryid" ) $t = 'category';
            if ( substr(strtolower($k),0,2) == "vo" ) $t = 'vo';
            if ( substr(strtolower($k),0,2) == "mw" ) $t = 'mw';
            if ( substr(strtolower($k),0,6) == "scicon" ) $t = 'scicon';
            if ( substr(strtolower($k),0,9) == "countryid" ) $t = 'country';
			if ( substr(strtolower($k),0,7) == "license" ) $t = 'license';
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

    /**
     * synchronize extra contact information about application contacts in the 
     * backend
     *
     * @param integer $appid the application id
     * @param string $data[] array of JSON data describing the extra contact 
     * information
     *
     *
     */
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
						$ff = new Default_Model_MiddlewaresFilter();
						$ff->id->equals($collection->items[$i]->itemID);
						$col2->filter->appid->equals($appid)->and($col2->filter->researcherid->equals($collection->items[$i]->researcherID));
						$col2->filter->chain($ff,"AND");
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
							for ($j=0; $j < count($mws->items); $j++) {
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
                        if ( $datum['itemid'] != '' && $datum['itemtype'] != '' ) {
    						error_log('warning: could not match to-be-inserted posted appContactItem to appropriate DB item. Possible data loss');
	    					error_log('posted appContactItem data: '.var_export($datum,true));
                        }
					}
				}
			}
		}
	}

//    /**
//     * synchronize data collections such as middlwares, disciplines, etc., about an 
//     * application in the backend
//     *
//     * @param string $masterName the name of the attribute that represents the 
//     * application id
//     * @param integer $masterID the application id
//     * @param string $slaveName the name of the attribute that represents the 
//     * collection item (slave) id
//     * @param string $collectionName the classname of the class that represents the 
//     * collection of items related to the application
//     * @param string $collectionItemName the classname of the class that represents idividual 
//     * items in such a collection
//     * @param string $data[] array of actual collection data
//     * @param string $dataSlaveName the array key used to retrieve relevant data from 
//     * the array above. If empty, it is considered to be equal to @slaveName
//     *
//     *
//     */
//    private function syncDBCollection($masterName, $masterID, $slaveName, $collectionName, $collectionItemName, &$data, $dataSlaveName = "") {
//        if ( is_null($data) ) return;
//
//		$data = $this->noDupes($data);
//		
//		if ( $dataSlaveName === "" ) $dataSlaveName = $slaveName;
//		$collectionName = "Default_Model_".$collectionName;
//		$collectionItemName = "Default_Model_".$collectionItemName;
//		$collection = new $collectionName();
//		$collection->filter->$masterName->equals($masterID);
//		for ( $i = $collection->count()-1; $i >= 0; $i-- ) {
//			$found = false;
//			foreach ( $data as $key => $value ) {
//				if ( strtolower(substr($key, 0, strlen($dataSlaveName))) === strtolower($dataSlaveName) ) {
//					if ( $dataSlaveName == "url" ) {
//						$urlData = json_decode($value, true);
//						$slaveID = $urlData['id'];
//					} elseif ( $dataSlaveName == "mw" ) {
//                        $mws = new Default_Model_Middlewares();
//						$value = json_decode($value)->name;
//						$mws->filter->name->equals($value);
//						if ( count($mws->items) > 0 ) {
//							$slaveID = $mws->items[0]->id;
//						} else $slaveID = $value;
//					} elseif ( $dataSlaveName == "license" ){
//						$v = json_decode($value);
//						if( intval($v->licenseid) == "0" ){
//							$slaveID = "-1";
//						}else{
//							$slaveID = $v->licenseid;
//						}
//					} else {
//						$slaveID = $value;
//					}
//					if ( $slaveID == $collection->items[$i]->$slaveName ) {
//						$found = true;
//						break;
//					}    
//				}    
//			}
//			if ( ! $found ) $collection->remove($i);
//		}    
//
//		$collection->refresh();
//		$j = 0;		// have a counter handy, needed in some cases
//		foreach ($data as $key => $value) {
//			if ( strtolower(substr($key,0,strlen($dataSlaveName))) === strtolower($dataSlaveName) ) {
//				$found = false;
//				$slaveID = null;
//				if ( $dataSlaveName == "url" ) {
//					$urlData = json_decode($value, true);
//					$slaveID = $urlData['id'];
//					// default to http:// if relative url is given
//					if (parse_url($urlData['url'], PHP_URL_SCHEME) == '') {
//						$urlData['url'] = 'http://'.$urlData['url'];
//					}
//				} elseif ( $dataSlaveName == "mw" ) {
//					$mws2 = new Default_Model_Middlewares();
//					$mws2->filter->name->equals(json_decode($value)->name);
//					if ($mws2->count()>0) {
//							$mwid = $mws2->items[0]->id;
//							$mwcomment = null;
//					} else {
//							$mwid = 5;
//							$mwcomment = json_decode($value)->name;
//					}
//					$slaveID = $mwid;
//				} elseif ( $dataSlaveName == "license" ) {
//					$licenseData = json_decode($value);
//					if( intval($licenseData->licenseid) < 1  ){
//						$slaveID = 0;
//					}else{
//						$slaveID = intval($licenseData->licenseid);
//					}
//				}else {
//					$slaveID = $value;
//				}
//				for ($i=$collection->count()-1; $i>=0; $i--) {
//					if ( $slaveID == $collection->items[$i]->$slaveName) {
//						if( $collectionItemName === "Default_Model_AppLicense"){
//							$lic = $collection->items[$i];
//							$lic->comment = $licenseData->comment;
//							$lic->save();
//						}
//						if ( $collectionItemName != "Default_Model_AppUrl" ) $found = true;
//						break;
//					}    
//				}
//				if ( ! $found ) {
//					$collectionItem = new $collectionItemName();
//					$collectionItem->$masterName = $masterID;
//					$collectionItem->$slaveName = $slaveID;
//					if ( $collectionItemName == "Default_Model_AppUrl" ) {
//						if ( $collectionItem->$slaveName == "" ) $collectionItem->$slaveName = null;
//						$collectionItem->url = $urlData['url'];
//						$collectionItem->description = $urlData['type'];
//						$collectionItem->title = $urlData['title'];
//						$collectionItem->ord = (string)$j;					
//					} elseif ( $collectionItemName == "Default_Model_AppMiddleware" ) {
//						$collectionItem->middlewareID = $mwid;
//						$collectionItem->comment = $mwcomment;
//						//FIXME: Quick and dirty fix for "other" MWs registered as "5" for some reason
//						if (($mwid == 5) && ($mwcomment == 5)) {
//							$mwcomment = null;
//						}
//						if ( ! is_null($mwcomment) ) {
//							error_log("mwlink: ". json_decode($value)->link);
//							$collectionItem->link = json_decode($value)->link;
//						}
//                    } elseif ( $collectionItemName == "Default_Model_AppLicense" ) {
//						$licenseData = json_decode($value);
//						$collectionItem->comment = $licenseData->comment;
//						if(intval($licenseData->licenseid) < 1 ){
//							$collectionItem->licenseid = 0;
//							$collectionItem->title = $licenseData->title;
//							if (parse_url($licenseData->url, PHP_URL_SCHEME) == '') {
//								$licenseData->url = 'http://'.$licenseData->url;
//							}
//							$collectionItem->link = $licenseData->url;
//						} else {
//							$collectionItem->licenseid = intval($licenseData->licenseid);
//						}
//					}
//					$collectionItem->save();
////					$collection->add($collectionItem);
//					$j++;
//				}    
//			}    
//		}   		
//	}

//    /**
//     * build an array of collection data from XML, so that it may be fed to 
//     * syncDBCollection()
//     * if a single xsi:nil element is found, then return an empty array, 
//     * signifying deletion of all elements
//     * if no element is found, then return null, signifying no changes to 
//     * existing elements
//     * otherwise, return an array of new element so that they may be synced 
//     * with the existing ones (i.e. add/remove accordingly)
//     *
//     * @param SimpleXMLElement $xml the resource's XML representation root element
//     * @param string $path XPath to the elements that belong to the collection in 
//     * question
//     * @param string $key the key that will be used to put data in the array
//     *
//     * @return string[]
//     *
//     */
//    private function buildCollection($xml, $path, $key) {
//        $xmli = @$xml->xpath($path);
//        if ( $xmli === false ) return null;
//        if ( (count($xmli) === 1) && ($xmli[0]->attributes(RestAPIHelper::XMLNS_XSI())->nil) && (strval($xmli[0]->attributes(RestAPIHelper::XMLNS_XSI())->nil) == "true") ) {
//            return array();
//        } elseif ( count($xmli) === 0 ) {
//            return null;
//        } else $data = array();
//        $i = 0;
//        foreach ( $xmli as $xml ) {
//            if ( ($xml->attributes(RestAPIHelper::XMLNS_XSI())->nil && strval($xml->attributes(RestAPIHelper::XMLNS_XSI())->nil) != "true") || (is_null($xml->attributes(RestAPIHelper::XMLNS_XSI())->nil)) ) {
//                if ( $key == "mw" ) {
//                    $id = '{"name": "' . str_replace('"','\'',strval($xml)) . '", "link": "' . strval($xml->attributes()->link) . '"}';
//                } elseif ( $key == "url" ) {
//                    $id = '{"id": "'.strval($xml->attributes()->id).'", "type": "'.strval($xml->attributes()->type).'", "url": "'.strval($xml).'", "title": "'.strval($xml->attributes()->title).'"}';
//				}elseif ( $key == "license" ) {
//					$lic = array("licenseid"=> "", "name"=>"", "group"=>"","title"=>"","url"=>"","comment"=>"");
//					foreach($xml->attributes() as $lk=>$lv){
//						$lic[strval($lk)] = strval($lv);
//					}
//					$cv = $xml->attributes()->id;
//					$lic["licenseid"] = trim(strval($cv));
//					if( $lic["licenseid"] === "" ){
//						$lic["licenseid"] = "0";
//					}
//					$cv = $xml->xpath("./license:comment");
//					$lic["comment"] = ( ( count($cv)>0 )?(strval($cv[0])):"" );
//					$cv = $xml->xpath("./license:url");
//					$lic["url"] = ( ( count($cv)>0 )?(strval($cv[0])):"" );
//					$cv = $xml->xpath("./license:title");
//					$lic["title"] = ( ( count($cv)>0 )?(strval($cv[0])):"" );
//					$id = json_encode($lic);
//				} else {
//                    $id = strval($xml->attributes()->id);
//                }
//                if ( $id != "" ) {
//                    $data[$key.$i] = $id;
//                    $i = $i+1;
//                }
//            }
//		}
//		return $data;
//	}

    /**
     * helper function to get a publications property from XML. Useful due to 
     * PHP's syntax limitations
     *
     * @param SimpleXMLElement $xml the XML root element of the publication 
     * representation
     * @param string $prop the element name of the property in question
     * @param string $arr[] reference to the array that will be populated with the 
     * property
     *
     * @return string[]
     *
     */
	private function pubprop($xml,$prop,&$arr) {
		$tmp = $this->el($xml, "publication:".$prop);
		if ( ! is_null($tmp) && strval($tmp) != "" ) $arr[$prop] = strval($tmp);
	}

    /**
     * populate a Default_Model_AppDocument with data from array
     *
     * @param Default_Model_AppDocument $existing reference to the model where the 
     * array data shall be put
     * @param string $docdatum[] reference to the array that holds the publication 
     * data
     *
     * @return Default_Model_AppDocument
     *
     */
    private function &populateAppDoc(&$existing,&$docdatum) {
		if ( array_key_exists("url", $docdatum) ) $existing->url = $docdatum['url'];
		// Default to http:// if relative URL is given
		if (parse_url($existing->url, PHP_URL_SCHEME) == '') {
			$existing->url = 'http://'.$existing->url;
		}
		if (trim($existing->url) == 'http://') $existing->url = null;
		if ( array_key_exists("title", $docdatum) ) $existing->title = $docdatum['title'];
		if ( array_key_exists("conference", $docdatum) ) $existing->conference = $docdatum['conference'];
		if ( array_key_exists("proceedings", $docdatum) ) $existing->proceedings = $docdatum['proceedings'];
		if ( array_key_exists('journal', $docdatum) ) $existing->journal = $docdatum['journal'];
		if ( array_key_exists('isbn', $docdatum ) ) $existing->isbn = $docdatum['isbn'];
		if ( array_key_exists('volume', $docdatum) ) $existing->volume = $docdatum['volume'];
		if ( array_key_exists('startPage', $docdatum) ) $existing->pageStart = $docdatum['startPage'];
		if ( array_key_exists('endPage', $docdatum) ) $existing->pageEnd = $docdatum['endPage']; else $existing->pageEnd = null;
		if ( isnull($existing->pageStart) || ($existing->pageStart == '') || (! is_numeric($existing->pageStart)) ) $existing->pageStart = null;
		if ( isnull($existing->pageEnd) || ($existing->pageEnd == '') || (! is_numeric($existing->pageEnd)) ) $existing->pageEnd = null;
		if ( array_key_exists('year', $docdatum) ) $existing->year = $docdatum['year'];
		if ( isnull($existing->year) || ($existing->year == '') || (! is_numeric($existing->year)) ) $existing->year = null;
		if ( array_key_exists("publisher", $docdatum ) ) $existing->publisher = $docdatum['publisher'];
		if ( array_key_exists('typeID', $docdatum) ) $existing->docTypeID =  $docdatum['typeID'];
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

    /**
     * implementation of abstract parse() operation from RestXMLParser.
     * Notes:
     * - Simple application properties are set in the application model directly 
     * from the XML
     * - Simple collections of associations of the appliation to various other 
     * entities such as middlewares, discipline, etc. are synchronized using 
     * syncDBCollection(), by putting information from the XML into arrays 
     * (hash-tables) and then uniformly passing info from the array to models
     * - Complicated collections of associations such as application contact 
     * metadata and publication have dedicated functions, yet the XML -> array 
     * -> Model logic still stands
     *
     * @param SimpleXMLElement $xml the root element of the application XML representation
     * 
     * @return Default_Model_Application
     *
     */
    public function parse($xml) {
		global $application;

        if ( ! is_null($this->_user) ) {
			$app = new Default_Model_Application();
			try {
				$xml = new SimpleXMLElement($xml);
			} catch (Exception $e) {
				$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
				return $app;
            }
            $this->_xml = $xml;
			// basic properties
			$xmli = $xml->xpath('//application:application');
			if ( count($xmli) === 0 ) {
				$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
				return $app;
			}
			$xml = $xmli[0];
			if ( $this->_parent->getMethod() === RestMethodEnum::RM_POST ) {
				if ( $xml->attributes()->id ) {
					$app->id = strval($xml->attributes()->id);
					$db = $application->getBootstrap()->getResource('db');
					$db->setFetchMode(Zend_Db::FETCH_OBJ);
					$r = $db->query('SELECT guid FROM applications WHERE id = ' . $app->id)->fetchAll();
					if ( count($r) > 0 ) $app->guid = $r[0]->guid;
                } else {                    
					$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
					return $app;
				}
            }
            $sync = false;
            if ( $this->_parent->getParam('sync') === "true" ) {
			    if ( $this->_parent->getMethod() === RestMethodEnum::RM_POST ) {
    				if ( $xml->attributes()->id ) {
	    				$id = strval($xml->attributes()->id);
                        $apps = new Default_Model_Applications();
                        $apps->filter->id->equals($id);
                        if ( count($apps->items) > 0 ) {
                            if ( $this->_parent->canSync($apps->items[0]) ) $sync = true;
                        }
                    }
                } else {
                    if ( $this->_parent->canSync(null) ) $sync = true;
                }
            }
			if ( $xml->attributes()->tool ) $app->tool = strval($xml->attributes()->tool)==="true"?true:false;
            if ( ! $sync ) {
                if ( $this->_parent->getMethod() === RestMethodEnum::RM_PUT ) {
                    $app->addedBy = $this->_parent->getUser()->id;
                }
            } else {
                $addedby = $this->el($xml,"application:addedby");
                if ( $addedby && $addedby->attributes()->id ) {
                    $app->addedBy = strval($addedby->attributes()->id);
                } else $app->addedBy = $this->_parent->getUser()->id;
            }
            $owner = $this->el($xml,"application:owner");
            $ownerID = '';
            // IF NOT SYNCING:
            // if an owner is specified, and either the user has the permission
            // to set it or the user is a service adding a new application, then 
            // do set the owner, or else set the owner to be the same user as the
            // one that makes the request if this is a new application, otherwise
            // let it be
            // IF SYNCING:
            // set the owner as if specified. if not, set it to be the same as 
            // the addedby property
            if ( ! $sync ) {
                if ( ! is_null($owner) && ! is_null($owner->attributes()->id) ) {
                    if ( $this->_parent->getMethod() === RestMethodEnum::RM_PUT ) {
                        if ( $this->_user->accountType == 1 || ($this->_parent->userIsAdmin()) ) {
                            $ownerID = strval($owner->attributes()->id);
                        } else $ownerID = $app->addedBy;
                    } elseif ($this->_parent->getMethod() === RestMethodEnum::RM_POST) {
                        if ( $this->_user->privs->canGrantOwnership($app) ) $ownerID = strval($owner->attributes()->id);
                    }
                } else {
                    if ($this->_parent->getMethod() === RestMethodEnum::RM_PUT) $ownerID = $app->addedBy;
                }
            } else {
                if ( ! is_null($owner) && ! is_null($owner->attributes()->id) ) {
                    $ownerID = strval($owner->attributes()->id);
                } else {
                    if ( $this->_parent->getMethod() === RestMethodEnum::RM_PUT ) {
                        $ownerID = $app->addedBy;
                    }
                }
            }
            if ( $ownerID != '' ) $app->ownerID = $ownerID;
			if ( $xml->attributes()->tagPolicy ) {
				if ( $this->_parent->userIsAdmin() || $newapp || $this->_parent->getUser()->id === $app->addedBy || $this->_parent->getUser()->id === $app->ownerID ) {
    				$app->tagPolicy = strval($xml->attributes()->tagPolicy);
                }
            }
            $nameError = '';
            $nameReason = '';
			if ( $this->_user->privs->canModifyApplicationName($app) ) {
				if ( ! is_null($this->el($xml,"application:name"))) {
					if ( validateAppName(preg_replace("/-DELETED-.{8}-.{4}-.{4}-.{4}-.{12}/","",strval($this->el($xml,"application:name"))), $nameError, $nameReason, $this->_parent->getMethod() === RestMethodEnum::RM_POST ? $app->id : null) ) {
						$app->name = strval($this->el($xml,"application:name"));
					} else {
						$this->_error = RestErrorEnum::RE_BACKEND_ERROR;
						$this->_extError = "Invalid application name. ".strip_tags($nameReason);
						return $app;
					}
				}
			}
			if ( $this->_user->privs->canModifyApplicationDescription($app) ) $app->description = strval($this->el($xml,"application:description"));
			if ( $this->_user->privs->canModifyApplicationAbstract($app) ) $app->abstract = strval($this->el($xml,"application:abstract"));
			if ( $this->_user->privs->canModifyApplicationStatus($app) ) {
				$status = $this->el($xml,"application:status");
	            if ( $status && $status->attributes()->id) $app->statusID = strval($status->attributes()->id);
			}
            if ( ! $sync ) {
                if ( $this->_parent->getMethod() === RestMethodEnum::RM_PUT ) $app->dateAdded = date('Y-m-d H:i:s');
            } else {
                $dateAdded = $this->el($xml,"application:addedOn");
                if ( $dateAdded ) {
                    $app->dateAdded = strval($dateAdded);
                } else {
                    if ( $this->_parent->getMethod() === RestMethodEnum::RM_PUT ) $app->dateAdded = date('Y-m-d H:i:s');
                }
            }
            if ( ! $sync ) {
    			$app->lastUpdated = date('Y-m-d H:i:s');
            } else {
                $lastUpdated = $this->el($xml,"application:lastUpdated");
                if ( $lastUpdated ) {
                    $app->lastUpdated = strval($lastUpdated);
                } else $app->lastUpdated = date('Y-m-d H:i:s');
            }
            if ( $this->_parent->userIsAdmin() ) {
                if ( $sync ) {
                    if ( $xml->attributes()->moderated ) $app->moderated = strval($xml->attributes()->moderated);
                    if ( $app->moderated ) {
                        $app->modInfo->moddedBy = strval($this->el($xml,"application:moderator")->attributes()->id);
                        $app->modInfo->moddedOn = $xml->moderatedOn;
                        $app->modInfo->modReason = $xml->moderationReason;
                    }
                }
            }
            if ( $this->_parent->userIsAdmin() || $this->_user->privs->canDeleteApplication($app) ) {
                if ( $sync ) {
                    if ( $xml->attributes()->deleted ) $app->deleted = strval($xml->attributes()->deleted);
                    if ( $app->deleted ) {
                        $app->delInfo->deletedBy = strval($this->el($xml,"application:deleter")->attributes()->id);
                        $app->delInfo->deletedOn = $xml->deletedOn;
                    }
                }
            }
            // also set extended attributes, like deleted, moderated, tags, 
            // etc. when syncing, which would normaly be ignored since they are 
            // set via separate resources
//            if ( $sync ) {
//                TODO: add extra sync code
//            }
			if ( $this->_user->privs->canModifyApplicationLogo($app) ) {
				$logo = $this->el($xml, "application:logo");
				$removeLogoCache = false;
				if ( ! is_null($logo) ) {
					if ( $logo->attributes(RestAPIHelper::XMLNS_XSI())->nil === "true" ) {
						$app->clearLogo();
						$removeLogoCache = true;
					} else {
						if ( $logo->attributes()->type && strval($logo->attributes()->type) === "base64" ) {
							// logo is given as byte64 encoded string
							if ( strval($logo) != '' ) {
								$app->logo = pg_escape_bytea(strval($logo));
								$removeLogoCache = true;
							}
						} else {
							// logo is given as URL
							if (parse_url(strval($logo), PHP_URL_SCHEME) == '') {
								// no URL scheme present; assume uploaded file though 
								// portal's uploadlogo action in AppsController
								if ( strval($logo) != '' ) {
									try {
										$app->logo = pg_escape_bytea(base64_encode(file_get_contents(APPLICATION_PATH."/../public/".strval($logo)))); 
										$removeLogoCache = true;
									} catch (Exception $e) {
										$this->_error = RestErrorEnum::RE_BACKEND_ERROR;
										$this->_extError = $e->getMessage();
										return $app;
									}
								}
							} else {
								// URL scheme present; assume remote file
								if ( strval($logo) != '' ) {
									try {
										$app->logo = pg_escape_bytea(base64_encode(file_get_contents(strval($logo))));
										$removeLogoCache = true;
									} catch ( Exception $e ) {
										$this->_error = RestErrorEnum::RE_BACKEND_ERROR;
										$this->_extError = $e->getMessage();
										return $app;
									}
								}
							}
						}
					}
				}
				if ( $removeLogoCache === true ) {
					$logocachename = APPLICATION_PATH . "/../cache/app-logo-".$app->id.".png";
					if ( $app->id != '' && file_exists($logocachename) ) {
						// invalidate logo cache
						unlink($logocachename);
						// re-build logo cache
						$flogo = fopen($logocachename, "w");
						fwrite($flogo, base64_decode(pg_unescape_bytea($app->logo)));
						fclose($flogo);
						$logocachename2 = str_replace("/app-logo", "/55x55/app-logo",   $logocachename);
						$logocachename3 = str_replace("/app-logo", "/100x100/app-logo", $logocachename);
						$logocachename2 = str_replace(".png", ".jpg", $logocachename2);
						$logocachename3 = str_replace(".png", ".jpg", $logocachename3);
						`convert -background white -flatten -strip -interlace Plane -quality 80 -scale 55x55   $logocachename $logocachename2`;
						`convert -background white -flatten -strip -interlace Plane -quality 80 -scale 100x100 $logocachename $logocachename3`;
					}

				}
			}                    
			
			//Set metatype of application (0: software 1: vappliance 2:swappliance )
			if ( $this->_parent->getMethod() === RestMethodEnum::RM_PUT || $this->_parent->getMethod() === RestMethodEnum::RM_POST){
				if( is_numeric(strval($xml->attributes()->metatype)) && 
					intval(strval($xml->attributes()->metatype)) >=0 && 
					intval(strval($xml->attributes()->metatype)) < 3 ){
					$app->metatype = intval(strval($xml->attributes()->metatype));
				}else{
					$app->metatype = 0;
				}
			}
			
			if ( $this->_parent->getMethod() === RestMethodEnum::RM_PUT && (
				count($xml->xpath("//application:category")) == 0 ||
				count($xml->xpath("//discipline:discipline")) == 0 ||
				$app->abstract == '' ||
				$app->description == ''
			)) {
				$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
				$this->_extError = "One ore more required entities are missing or contain no data.";
				return $app;
			} else {
				$app->save();
			}

            // handle tags separetely through dedicated resource classes
            // thus avoiding re-implementing complicated permission checks
            if ( count($xml->xpath('//application:tag[@xsi:nil="true"]')) != 0 ) {
                // nil element specified; remove all tags
                try {
                    $taglist = new RestAppTagList(array('id' => $app->id));
                    $xml2 = new SimpleXMLElement(strval($taglist->get()));
                    foreach($xml2->xpath('//application:tag') as $tag) {
                        $t = new RestAppTagItem(array('tid' => strval($t->attributes()->id)), $taglist);
                        $t->delete();
                    }
                } catch (Exception $e) {
                    $this->_error = RestErrorEnum::RE_BACKEND_ERROR;
                    $this->_extError = $e->getMessage();
                    return $app;
                }
            } elseif ( count($xml->xpath('//application:tag')) > 0 ) {
                // non-nil element specified; synchronize tags
                try {
                    //add new
                    $newtags = array();
                    foreach($xml->xpath('//application:tag') as $tag) {
						if ( strval($tag->attributes()->system) !== "true" ) {
							$tagval = $tag->asXML();
							// TODO: formalize the appdb:appdb head in a constant
							// and search for other places in the API lib where this should
							// be done as well
							$tagval = '<appdb:appdb xmlns:appdb="' . RestAPIHelper::XMLNS_APPDB() . '" xmlns:application="' . RestAPIHelper::XMLNS_APPLICATION() . '">'.$tagval.'</appdb:appdb>';
	                        $taglist = new RestAppTagList(array_merge($this->_parent->getParams(), array('id' => $app->id, 'data' => $tagval)));
							$tagres = RestAPIHelper::wrapResponse(strval($taglist->get()));
							$tagxml = new SimpleXMLElement(strval($tagres));
							$tagxml = $tagxml->xpath("//application:tag");
							$found = false;
							$tval = new SimpleXMLElement($tagval);
							$tval = $tval->xpath("//application:tag");
							$tval = strval($tval[0]);
							foreach ( $tagxml as $tt ) {
								if ( strval($tt) === $tval ) {
									$found = true;
									break;
								}
							}
    	                    if ( ! $found ) {
								$taglist->put();
							}
							$newtags[] = strval($tag);
						}
                    }
                    //remove non-existent
                    $taglist = new RestAppTagList(array('id' => $app->id));
					$tagsxml = strval($taglist->get());
					$tagsxml = '<appdb:appdb xmlns:appdb="' . RestAPIHelper::XMLNS_APPDB() . '" xmlns:application="' . RestAPIHelper::XMLNS_APPLICATION() . '">'.$tagsxml.'</appdb:appdb>';
                    $xml2 = new SimpleXMLElement($tagsxml);
                    foreach($xml2->xpath('//application:tag[not(@system="true")]') as $tag) {
                        if (! in_array(strval($tag), $newtags) ) {
                            $t = new RestAppTagItem(array_merge($this->_parent->getParams(), array('tid' => strval($t->attributes()->id))), $taglist);
                            $t->delete();
                        }
                    }
                } catch (Exception $e) {
                    $this->_error = RestErrorEnum::RE_BACKEND_ERROR;
                    $this->_extError = $e->getMessage();
                    return $app;
                }
			} // if no application:tag element specified, then ignore any existing tags (insert xor partial update)
			
			if ( $this->_user->privs->canModifyApplicationCategory($app) ) {
				$data = $this->buildCollection($xml, "//application:category", "categoryID");
				$this->syncDBCollection("appid", $app->id, "categoryid", "AppCategories", "AppCategory", $data);
			}
			/* set primary category */
			if ( count($xml->xpath('//application:category[@primary="true"]')) > 0 ) {
				$catid = $xml->xpath('//application:category[@primary="true"]');
				$catid = strval($catid[0]->attributes()->id);
				// This is MUCH faster than using the model
				db()->query("UPDATE appcategories SET isprimary = TRUE WHERE categoryid = ? AND appid = ?", array($catid, $app->id));
//				$cats = new Default_Model_AppCategories();
//				$cats->filter->appid->equals($app->id)->and($cats->filter->categoryid->equals($catid));
//				if ( count($cats->items) > 0 ) {
//					$cat = $cats->items[0];
//					$cat->isPrimary = true;
//					$cat->save();
//				}
			}

			/* */
			if ( $this->_user->privs->canModifyApplicationLanguage($app) ) {
				$data = $this->buildCollection($xml, "//application:language", "proglangID");
				$this->syncDBCollection("appid", $app->id, "proglangid", "AppProgLangs", "AppProgLang", $data);
			}
			if ( $this->_user->privs->canModifyApplicationDiscipline($app) ) {
				$data = $this->buildCollection($xml, "//discipline:discipline", "disciplineID");
				$this->syncDBCollection("appid", $app->id, "disciplineid", "AppDisciplines", "AppDiscipline", $data);
			}
			if ( $this->_user->privs->canModifyApplicationVO($app) && trim($app->metatype) != "2" ) { //Do not allow editing for software appliances
				$data = $this->buildCollection($xml, "//vo:vo", "vo");
				$this->syncDBCollection("appid", $app->id, "void", "AppVOs", "AppVO", $data, "vo");
			}
			if ( $this->_user->privs->canModifyApplicationMiddleware($app) ) {
				$data = $this->buildCollection($xml, "//middleware:middleware", "mw");
				$this->syncDBCollection("appid", $app->id, "middlewareid", "AppMiddlewares", "AppMiddleware", $data, "mw");
			}

			if ( $this->_parent->method === RestMethodEnum::RM_POST && $this->_user->privs->canAssociatePersonToApplication($app) ) {
				$data = $this->buildCollection($xml, "//application:contact", "scicon");
				$this->syncDBCollection("appid", $app->id, "researcherid", "ResearchersApps", "ResearchersApp", $data, "scicon");
				$data = null;
				$i = 0;
                $xmli = $xml->xpath("//application:contact/application:contactItem");
                if ( count($xmli) != 0 ) {
                    $data = array();
                    foreach ( $xmli as $x ) {
                        if ( $x->attributes(RestAPIHelper::XMLNS_XSI())->nil === "true" ) {
                            $data = array();
                            break;
                        } else {
                            $xp = $x->xpath("parent::*");
                            $data['cntpnt'.$i] = '{"researcherid": "'.strval($xp[0]->attributes()->id).'", "itemtype": "'.strval($x->attributes()->type).'", "itemid": "'.strval($x->attributes()->id).'", "item": "'.strval($x).'"}';
                            $i = $i + 1;
                        }
                    }
                }
				if ( ! is_null($data) ) $this->syncAppContactItems($app->id, $data);
			}

			if ( $this->_user->privs->canModifyApplicationCountry($app) ) { 
				$data = $this->buildCollection($xml, "//regional:country", "countryid");
				$this->syncDBCollection("appid", $app->id, "countryid", "AppManualCountries", "AppManualCountry", $data); 
			}
			if ( $this->_user->privs->canModifyApplicationURLs($app) ) {
				$data = $this->buildCollection($xml, "//application:url", "url");
				$this->syncDBCollection("appid", $app->id, "id", "AppUrls", "AppUrl", $data, "url");
			}
			if( $this->_user->privs->canModifyApplicationLicenses($app) ){
				$data = $this->buildCollection($xml, "//application:license", "license");
				if ( is_null($data) ) $data = array();
				$this->syncDBCollection("appid", $app->id, "licenseid", "AppLicenses", "AppLicense", $data, "license");
			}

			if ( $this->_user->privs->canModifyApplicationDocuments($app) ) {
				$xmli = $xml->xpath("publication:publication");
                $docdata = null;
                if ( count($xmli) != 0 ) {
                    $docdata = array();
                    foreach($xmli as $x) {
                        if ( strval($x->attributes(RestAPIHelper::XMLNS_XSI())->nil) === "true" ) {
                            $docdata = array();
                            break;
                        }
                        $docdatum = array();
                        $docdatum['id'] = strval($x->attributes()->id);
                        $this->pubprop($x, "title", $docdatum);
                        $this->pubprop($x, "url", $docdatum);
                        $this->pubprop($x, "conference", $docdatum);
                        $this->pubprop($x, "proceedings", $docdatum);
                        $this->pubprop($x, "journal", $docdatum);
                        $this->pubprop($x, "isbn", $docdatum);
                        $this->pubprop($x, "volume", $docdatum);
                        $this->pubprop($x, "startPage", $docdatum);
                        $this->pubprop($x, "endPage", $docdatum);
                        $this->pubprop($x, "year", $docdatum);
                        $this->pubprop($x, "publisher", $docdatum);
                        $type = $this->el($x, "publication:type");
                        if ( ! is_null($type) ) $docdatum['typeID'] = strval($type->attributes()->id);
                        $intAuthors = array();
                        $extAuthors = array();
                        $authors = $x->xpath('publication:author[@type="internal"]');
                        foreach ( $authors as $a ) {
							if ( $this->el($a, "person:person") === null ) {
								$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
								$this->_extError = "expected 'person:person' element under 'publication:author[@type=\"internal\"]' not found";
								return $app;
							} else {
								$intAuthors[] = array(
		                            strval($this->el($a, "person:person")->attributes()->id),
			                        strval($a->attributes()->main)
								);
							}
                        }
                        $docdatum['intAuthors'] = $intAuthors;
                        $authors = $x->xpath('publication:author[@type="external"]');
                        foreach ( $authors as $a ) {
   							if ( $this->el($a, "publication:extAuthor") === null ) {
								$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
								$this->_extError = "expected 'publication:extAuthor' element under 'publication:author[@type=\"external\"]' not found";
								return $app;
							} else {
								$extAuthors[] = array(
									strval($this->el($a, "publication:extAuthor")),
									strval($a->attributes()->main)
								);
							}
                        }
                        $docdatum['extAuthors'] = $extAuthors;
                        $docdata[] = $docdatum;
                    }
                }
                if ( ! is_null($docdata) ) {
                    $docs = new Default_Model_AppDocuments();
                    $docs->filter->appid->equals($app->id);
                    $docs->refresh();                                                
                    $docCount = count($docs->items);
                    //handle existing and deleted entries
                    for ($i=$docCount-1; $i>=0; $i--) {
                        $existing=null;
                        if ( is_array($docdata) ) for($j=0; $j<count($docdata); $j++) {
                            $doc = $docs->items[$i];
                            if ( $doc->id == $docdata[$j]['id'] ) {
                                $existing = $this->populateAppDoc($doc,$docdata[$j]);
                                $docdata[$j]['PARSED'] = true;
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
                    if ( is_array($docdata) ) foreach($docdata as $docdatum) {
                        if ( ! isset($docdatum['PARSED']) || $docdatum['PARSED'] !== true ) {
                            $doc = new Default_Model_AppDocument();
                            //first time only main data is saved
                            $doc->appID = $app->id;
                            $doc = $this->populateAppDoc($doc,$docdatum);
                            $docs->add($doc);
                            //second time referenced data is saved
                            $doc = $this->populateAppDoc($doc,$docdatum);
                            $doc->save();
                        }
                    }	
                }
			}
			
			if ( $this->_parent->getMethod() === RestMethodEnum::RM_POST) {
				$xrels = $xml->xpath("application:relation");
				$ps = new Default_Model_Applications();
				$ps->filter->id->equals($app->id);
				$p = null;
				$res = true;
				if( count($ps->items) > 0 ){
					$p = $ps->items[0];
				}
				if( $p !== null ){
					$rels = array();
					if ( count($xml->xpath('application:relation[@xsi:nil="true"]')) === 0 ) {
						foreach ($xrels as $x) {
							$targuid = trim(strval($x->attributes()->targetguid));
							$subguid = trim(strval($x->attributes()->subjectguid));
							$rel = array(
								"id" => trim(strval($x->attributes()->id)),
								"parentid" => trim(strval($x->attributes()->parentid)) 
							);
							
							if( $targuid === "" ){
								$rel["subjectguid"] = $subguid;
							}elseif( $subguid === "" ){
								$rel["targetguid"] = $targuid;
							}
							
							if( $rel["parentid"] === "" ){
								$rel["parentid"] = null;
							}
							$rels[] = $rel;
						}
					}
					try{
						$res = ApplicationRelations::syncRelations($p->guid, $this->_user->id, $rels);
					}catch(Exception $ex){
						$res = $ex->getMessage();
						error_log($res);
					}
					if( is_string($res)){
						$this->_error = RestErrorEnum::RE_BACKEND_ERROR;
						$this->_extError = $res;
						return $p;
					}
				}
			}
			
			if ( $this->_parent->getMethod() === RestMethodEnum::RM_POST ) {
				$xrels = $xml->xpath("application:extrelation");
				$ps = new Default_Model_Applications();
				$ps->filter->id->equals($app->id);
				$p = null;
				if( count($ps->items) > 0 ){
					$p = $ps->items[0];
				}
				if( $p !== null && trim($app->metatype) !== "2"){
					$rels = array();
					if ( count($xml->xpath('application:extrelation[@xsi:nil="true"]')) === 0 ) {
						foreach ($xrels as $x) {
							$targuid = trim(strval($x->attributes()->targetguid));
							$subguid = trim(strval($x->attributes()->subjectguid));
							$rel = array(
								"id" => trim(strval($x->attributes()->id)),
								"hidden" => trim(strval($x->attributes()->hidden))
							);
							$rels[] = $rel;
						}
						try{
							$res = ApplicationRelations::hideExternalRelations($p->guid, $this->_user->id, $rels);
						}catch(Exception $ex){
							$res = $ex->getMessage();
							error_log($res);
						}
						if( is_string($res)){
							$this->_error = RestErrorEnum::RE_BACKEND_ERROR;
							$this->_extError = $res;
							return $p;
						}
					}
					
				}
			}
		}
		$this->_error = RestErrorEnum::RE_OK;
		return $app;	
	}
}

/* class RestAppList
 * derived class for lists of application resources
 */
class RestAppList extends RestResourceList {
   
    protected function _options() {
        $options = array();
        $options[] = RestMethodEnum::RM_GET;
        if ( $this->authenticate() ) $options[] = RestMethodEnum::RM_POST;
        if ( $this->authenticate() ) $options[] = RestMethodEnum::RM_PUT;
        return $options;
    }

    public static function __list($_this) {
   		$ret = array();
		$_this->_model->refresh("xml", "listing");
		return new XMLFragmentRestResponse($_this->_model->items, $_this);
//		$_this->_model->refresh();
//		for ($i=0; $i < count($_this->_model->items); $i++) {
//			$ret[] = '<application:application xmlns:application="' . RestAPIHelper::XMLNS_APPLICATION() . '" id="'.$_this->_model->items[$i]->id.'" >'.$_this->_model->items[$i]->name.'</application:application>';
//		}
//		return new XMLFragmentRestResponse($ret, $_this);
    }

    protected function _list() {
        return RestAppList::__list($this);
	}

    /*** Attributes ***/

    /**
     * internal reference to XML parser, set during initialization
     *
     */
	private $_parser;

    /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
    public function getDataType() {
        return "application";
    }

    /**
     * overrides RestResourceList::init()
     */
	protected function init() {
		$this->_parser = new RestAppXMLParser($this);
	}

    /**
     * overrides RestResource::getModel
     */
    protected function getModel() {
		// must call iserIsAdmin before instanciating a Default_Model_Applications, so that
		// $_GET['userid'] is properly set
		$isAdmin = $this->userIsAdmin();
		$res = new Default_Model_Applications();
		$res->filter = FilterParser::getApplications($this->getParam("flt"));
		// NEVER show moderated/deleted entries in app lists, except for the dedicated RestModAppList and RestDelAppList classes
		//if ( $isAdmin ) {
		//	$res->viewModerated = true;
		//}
		return $res;
    }

    /**
     * handles PUT and POST HTTP methods to REST requests
     *
     * @param integer $method the method enumeration according to RestMethodEnum
     *
     * @return iRestResponse
     *
     */
    private function putpost($method, $disposition = null) {
        $oldret = null;
        if ( $this->_logfile != '' ) {
            if ( $method === RestMethodEnum::RM_POST ) {
				try {
                   $id = $this->_parser->getID($this->getData(), "application:application");
                   $oldres = new RestAppItem(array("id" => $id), $this);
                   $oldret = $oldres->get();
                } catch (Exception $e) {
                    $oldret = null;
                }
            }
        }
        
		db()->beginTransaction();
		$app = $this->_parser->parse($this->getData());
        if ( $this->_parser->getError() !== RestErrorEnum::RE_OK ) {
			db()->rollBack();
			$this->setError($this->_parser->getError(), $this->_parser->getExtError(), false);
			$ret = false;
		} else {
			db()->commit();
			$res = new RestAppItem(array("id" => $app->id), $this);
			$ret = false;
			$retCount = 0;
			// sometimes fetching the new data might fail (race condition). Try up to 5 times
			while (($ret === false) && ($retCount<5)) {
				sleep(1);
				$ret = $res->get();
				if (($ret === false) && ($res->getError() !== RestErrorEnum::RE_ITEM_NOT_FOUND)) {
					// give up on errors other than item not found
					break;
				} else {
					$retCount += 1;
				}
			}
		}

        if ( $ret !== false && $this->_logfile != '') $this->logAction($method === RestMethodEnum::RM_POST ? "update" : "insert", $this->getDataType(), $app->id, ($oldret === null ? "" : $oldret), $ret, $disposition);

        return $ret;
	}

    /**
     * overrides RestResource::put()
     */
	public function put() {
		if (  parent::put() !== false ) {
            return new XMLFragmentRestResponse($this->putpost(RestMethodEnum::RM_PUT), $this);
		} else return false;
	}

    /**
     * overrides RestResource::post()
     */
	public function post($disposition = null) {
		if ( parent::post() !== false ) {
			return new XMLFragmentRestResponse($this->putpost(RestMethodEnum::RM_POST, $disposition), $this);
		} else return false;
	}
	
    /**
     * realization of authorize() from iRestAuthModule
     */
    public function authorize($method) {
        $res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
            $res = true;
            break;
        case RestMethodEnum::RM_PUT:
			$res = $this->authenticate() && $this->getUser()->privs->canInsertApplication();
			if ($res !== true) {
				$this->setError(RestErrorEnum::RE_ACCESS_DENIED);
				return false;
			}
			break;
        case RestMethodEnum::RM_POST:
			$id = $this->_parser->getID($this->getData(),"application:application");
			if ( $this->_parser->getError() === RestErrorEnum::RE_OK ) {
				if ( $id != "" ) {
					$apps = new Default_Model_Applications();
					$apps->filter->id->equals($id);
					if ( count($apps->items) > 0 ) {
						$app = $apps->items[0]; 
						$res = true;
					} else {
						$app = null;
						$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
						return false;
					}
				} else {
					$this->setError(RestErrorEnum::RE_INVALID_REPRESENTATION);
					return false;
				}
			} else {
				$this->setError($this->_parser->getError());
				return false;
			}
            $res = $res && (!is_null($app)) && $this->authenticate() && ($this->userIsAdmin() || $this->getUser()->id === $app->addedBy  || $this->_userid === $app->ownerid || $this->getUser()->privs->hasAccess($app));
			if ( ! $res ) {
                $this->setError(RestErrorEnum::RE_ACCESS_DENIED);
                return false;
            }
			break;
        case RestMethodEnum::RM_DELETE:
			$res = false;
			break;
        }
        return $res;
    }
}

class RestAppOpenAIREList extends RestROResourceList {
    /**
     * realization of getDataType from iRestResource
     */
    public function getDataType() {
        return "software";
    }

	protected function _list() {
        return $this->get();
    }
 
    /**
     * overrides RestResource::get()
     */
	public function get() {
		if ( parent::get() !== false ) {
//            if ( isset($this->_pars["flt"]) ) $flt = $this->_pars["flt"]; else $flt = "";
			if (isset($this->_pars["verb"]) XOR isset($this->_pars["resumptionToken"])) {
				if (isset($this->_pars["resumptionToken"]) OR ($this->_pars["verb"] == "ListRecords")) {
					if (isset($this->_pars["resumptionToken"]) XOR (isset($this->_pars["metadataPrefix"]) && ($this->_pars["metadataPrefix"] == "oai_datacite"))) {
						if (isset($this->_pars["resumptionToken"])) {						
							$token = $this->_pars["resumptionToken"];
						} else {
							$token = null;
						}
						if (isset($this->_pars["from"])) {						
							$from = $this->_pars["from"];
						} else {
							$from = null;
						}
						if (isset($this->_pars["until"])) {						
							$until = $this->_pars["until"];
						} else {
							$until = null;
						}
						db()->setFetchMode(Zend_Db::FETCH_BOTH);
						$s = db()->query("SELECT oai_app_cursor(?,?,?)", array($from, $until, $token))->fetchAll();
						if (count($s) > 0) {
							$s = $s[0];
							$s = json_decode($s[0], true);
							if (is_array($s)) {
								if (array_key_exists("error", $s)) {
									$this->setError(RestErrorEnum::RE_BACKEND_ERROR, $s["error"]);
									$this->_extError = $s["error"];
									return false;
								} else {
									//error_log(var_export($s, true));
									$body = base64_decode($s["payload"]);
									$header = base64_decode($s["header"]);
									$footer = base64_decode($s["footer"]);
									$s = $header . $body . $footer;
								}
							} else {
								$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
								return false;
							}
						} else {
							$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
							return false;
						};
						return new XMLRestResponse(
							$s, 
							$this
						);
					} else {
						$this->setError(RestErrorEnum::RE_BACKEND_ERROR, "cannotDisseminateFormat");
						$this->_extError = "cannotDisseminateFormat";
						return false;
					}
				} else {
					$this->setError(RestErrorEnum::RE_BACKEND_ERROR, "badVerb");
					$this->_extError = "badVerb";
					return false;
				}
			} else {
				$this->setError(RestErrorEnum::RE_BACKEND_ERROR, "badArgument");
				$this->_extError = "badArgument";
				return false;
			}
		} else return false;
	}
}

/**
 * class RestAppFollowedList
 * handles followed application list resources
 */
class RestAppFollowedList extends RestResourceList {

    protected function _options() {
        $options = array();
        if ( $this->authenticate() ) $options[] = RestMethodEnum::RM_GET;
        if ( $this->authenticate() ) $options[] = RestMethodEnum::RM_PUT;
        return $options;
    }

    protected function _list() {
        return RestAppList::__list($this);
    }

    /**
     * realization of getDataType() from iRestResource
     */
	public function getDataType() {
        return "application";
    }

    /**
     * overrides RestResource::init
     */
	protected function init() {
		$this->_cacheable = false;
		$this->_parser = new RestAppXMLParser($this);
	}

    /**
     * overrides RestResource::getModel
     */
	protected function getModel() {
		$res = new Default_Model_Applications();
		$res->filter = FilterParser::getApplications($this->getParam("flt"));
		$f = new Default_Model_ApplicationsFilter();
		$f->id->in("SELECT * FROM followed_app_ids(" . $this->getParam("id") . ") AS appid", false, false);
		$res->filter->chain($f, "AND");
		return $res;
/*
		$ids = array();
		$f = new Default_Model_MailSubscriptions();
		$f->filter->flt->like('%id:SYSTAG_FOLLOW')->and($f->filter->researcherid->equals($this->getParam("id")));
		if ( count($f->items) > 0 ) {
			foreach( $f->items as $i ) {
				$id = explode(" ", $i->flt);
				$id = explode(":", $id[0]);
				$ids[] = $id[1];
			}
			$res = new Default_Model_Applications();
			$res->filter->id->in($ids);
			if ( trim($this->getParam("flt")) != "" ) {
				$flt = FilterParser::getApplications($this->getParam("flt"));
				$res->filter->chain($flt, "AND");
			}
		} else $res = null;
		return $res;
 */
	}

    /**
     * overrides RestResource::put()
     */
	public function put() {
		if ( parent::put() !== false ) {
			$bm = new Default_Model_MailSubscription();
			$id = $this->_parser->getID($this->getData(),"application:application");
			if ( $this->_parser->getError() === RestErrorEnum::RE_OK ) {
				$apps = new Default_Model_Applications();
				$apps->filter->id->equals($id);
				if ( $apps->count() > 0 ) {
					$bm->flt = "=application.id:$id application.id:SYSTAG_FOLLOW";
					$bm->researcherid = $this->getParam("id");
					$bm->delivery = NewsDeliveryType::D_DAILY_DIGEST;
					$bm->events = NewsEventType::E_DELETE | NewsEventType::E_INSERT_COMMENT | NewsEventType::E_INSERT_CONTACT | NewsEventType::E_UPDATE;
					$bm->subjecttype = "app-entry";
					$bm->name = $apps->items[0]->name . " Subscription";
					$fhash = 0;
					FilterParser::filterNormalization($bm->flt, $fhash);
					$bm->flthash = num_to_string($fhash);
					try {
						$bm->save();
					} catch ( Exception $e) {
						$this->setError(RestErrorEnum::RE_BACKEND_ERROR, $e->getMessage());
						return false;
					}
					$res = new RestAppItem(array("id" => $id), $this);
					return $res->get();
				} else {
					$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
					return false;
				}
			} else {
				$this->setError($this->_parser->getError());
				return false;
			}
		} else return false;
	}
    
    /** 
     * realization of authorize from iRestAuthModule
     */
	public function authorize($method) {
        $res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
			$res = ($this->authenticate() && $this->getParam("id") == $this->getUser()->id) || $this->userIsAdmin();
			if ($res !== true) $this->setError(RestErrorEnum::RE_ACCESS_DENIED);
			break;
        case RestMethodEnum::RM_POST:
			break;
        case RestMethodEnum::RM_PUT:
			$res = ($this->authenticate() && $this->getParam("id") == $this->getUser()->id) || $this->userIsAdmin();
			if ($res !== true) $this->setError(RestErrorEnum::RE_ACCESS_DENIED);
			break;
        case RestMethodEnum::RM_DELETE:
            break;
        }
        return $res;
    }
}

/**
 * class RestAppFollowedItem
 * handles followed application individual items
 */
class RestAppFollowedItem extends RestResourceItem {
 
    protected function _options() {
        $options = array();
        if ( $this->authenticate() ) $options[] = RestMethodEnum::RM_DELETE;
        return $options;
    }

    /**
     * realization of getDataType from iRestResource
     */
	public function getDataType() {
		return "application";
	}

    /**
     * overrides RestResource::delete
     */
	public function delete() {
		if ( parent::delete() !== false ) {
			$bms = new Default_Model_MailSubscriptions();
			$appid = $this->getParam("appid");
			$bms->filter->flt->equals("=application.id:$appid application.id:SYSTAG_FOLLOW")->and($bms->filter->researcherid->equals($this->getParam("id")));
			if ( count($bms->items) > 0 ) {
				$bm = $bms->items[0];
				$bms->remove($bm);
				$res = new RestAppItem(array("id" => $appid), $this);
				return $res->get();
			} else {
				$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
				return false;
			}
		} else return false;
	}

    /**
     * realization of authorize() from iRestAuthModule
     */
	public function authorize($method) {
        $res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
        case RestMethodEnum::RM_POST:
        case RestMethodEnum::RM_PUT:
			break;
        case RestMethodEnum::RM_DELETE:
			$res = ($this->authenticate() && $this->getParam("id") == $this->getUser()->id) || $this->userIsAdmin();
			if ($res !== true) $this->setError(RestErrorEnum::RE_ACCESS_DENIED);
            break;
        }
        return $res;
    }
}

/**
 * class RestAppBookmarkList
 * handles application bookmark resources
 */
class RestAppBookmarkList extends RestResourceList {
   
    protected function _options() {
        $options = array();
        if ( $this->authenticate() ) $options[] = RestMethodEnum::RM_GET;
        if ( $this->authenticate() ) $options[] = RestMethodEnum::RM_PUT;
        return $options;
    }

    protected function _list() {
        return RestAppList::__list($this);
    }

    /**
     * realization of getDataType() from iRestResource
     */
	public function getDataType() {
        return "application";
    }

    /**
     * overrides RestResource::init
     */
	protected function init() {
		$this->_parser = new RestAppXMLParser($this);
	}

    /**
     * overrides RestResource::getModel
     */
	protected function getModel() {
		$res = new Default_Model_Applications();
		$res->filter = FilterParser::getApplications($this->getParam("flt"));
		$f = new Default_Model_AppBookmarksFilter();
		$f->researcherid->equals($this->getParam("id"));
		$res->filter->chain($f,"AND");
		return $res;
	}

    /**
     * overrides RestResource::put()
     */
	public function put() {
		if ( parent::put() !== false ) {
			$bm = new Default_Model_AppBookmark();
			$id = $this->_parser->getID($this->getData(),"application:application");
			if ( $this->_parser->getError() === RestErrorEnum::RE_OK ) {
				$apps = new Default_Model_Applications();
				$apps->filter->id->equals($id);
				if ( $apps->count() > 0 ) {
					$bm->appid = $id;
					$bm->researcherid = $this->getParam("id");
					try {
						$bm->save();
					} catch ( Exception $e) {
						$this->setError(RestErrorEnum::RE_BACKEND_ERROR, $e->getMessage());
						return false;
					}
					$res = new RestAppItem(array("id" => $id), $this);
					return $res->get();
				} else {
					$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
					return false;
				}
			} else {
				$this->setError($this->_parser->getError());
				return false;
			}
		} else return false;
	}
    
    /** 
     * realization of authorize from iRestAuthModule
     */
	public function authorize($method) {
        $res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
			$res = ($this->authenticate() && $this->getParam("id") == $this->getUser()->id) || $this->userIsAdmin();
			if ($res !== true) $this->setError(RestErrorEnum::RE_ACCESS_DENIED);
			break;
        case RestMethodEnum::RM_POST:
			break;
        case RestMethodEnum::RM_PUT:
			$res = ($this->authenticate() && $this->getParam("id") == $this->getUser()->id) || $this->userIsAdmin();
			if ($res !== true) $this->setError(RestErrorEnum::RE_ACCESS_DENIED);
			break;
        case RestMethodEnum::RM_DELETE:
            break;
        }
        return $res;
    }
}

/**
 * class RestAppBookmarkItem
 * handles application bookmark individual items
 */
class RestAppBookmarkItem extends RestResourceItem {
 
    protected function _options() {
        $options = array();
        if ( $this->authenticate() ) $options[] = RestMethodEnum::RM_DELETE;
        return $options;
    }

    /**
     * realization of getDataType from iRestResource
     */
	public function getDataType() {
		return "application";
	}

    /**
     * overrides RestResource::delete
     */
	public function delete() {
		if ( parent::delete() !== false ) {
			$bms = new Default_Model_AppBookmarks();
			$bms->filter->researcherid->equals($this->getParam("id"))->and($bms->filter->appid->equals($this->getParam("bmid")));
			if ( count($bms->items) > 0 ) {
				$bm = $bms->items[0];
				$bms->remove($bm);
				$res = new RestAppItem(array("id" => $this->getParam("bmid")), $this);
				return $res->get();
			} else {
				$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
				return false;
			}
		} else return false;
	}

    /**
     * realization of authorize() from iRestAuthModule
     */
	public function authorize($method) {
        $res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
        case RestMethodEnum::RM_POST:
        case RestMethodEnum::RM_PUT:
			break;
        case RestMethodEnum::RM_DELETE:
			$res = ($this->authenticate() && $this->getParam("id") == $this->getUser()->id) || $this->userIsAdmin();
			if ($res !== true) $this->setError(RestErrorEnum::RE_ACCESS_DENIED);
            break;
        }
        return $res;
    }
}

/**
 * class RestEdtAppList
 * handles the list of editable applications for a certain user
 */
class RestEdtAppList extends RestROAuthResourceList {
    /**
     * realization of getDataType from iRestResource
     */
    protected function _list() {
        return RestAppList::__list($this);
    }

	public function getDataType() {
        return "application";
    }

    /**
     * overrides RestResource::getModel
     */
	protected function getModel() {
		$res = new Default_Model_Applications();
		$res->filter = FilterParser::getApplications($this->getParam("flt"));
		$f = new Default_Model_ApplicationsFilter();
		$f->id->in("SELECT * FROM editable_app_ids(" . $this->getParam("id") . ") AS appid", false, false);
		$res->filter->chain($f, "AND");
		return $res;
	}

    public function authorize($method) {
        $res = parent::authorize($method);
        $res = $res && (( $this->getParam("id") == $this->_userid ) || $this->userIsAdmin());
        if ( ! $res && $this->getError() == RestErrorEnum::RE_OK ) $this->setError(RestErrorEnum::ACCESS_DENIED);
        return $res;
    }
}

/**
 * class RestOwnAppList
 * handles the list of applications owned by a certain user
 */
class RestOwnAppList extends RestROAuthResourceList {
    /* realization of getDataType from iRestResource
     */
	public function getDataType() {
        return "application";
    }

    protected function _list() {
        return RestAppList::__list($this);
    }

    /**
     * overrides RestResource::getModel
     */
	protected function getModel() {
		$res = new Default_Model_Applications();
		$res->filter = FilterParser::getApplications($this->getParam("flt"));
		$f = new Default_Model_ApplicationsFilter();
		$f->addedby->equals($this->getParam("id"))->or($f->owner->equals($this->getParam("id")));
		$res->filter->chain($f,"AND");
		return $res;
    }

    public function authorize($method) {
        $res = parent::authorize($method);
        $res = $res && (( $this->getParam("id") == $this->_userid ) || $this->userIsAdmin());
        if ( ! $res && $this->getError() == RestErrorEnum::RE_OK ) $this->setError(RestErrorEnum::ACCESS_DENIED);
        return $res;
    }
}

/**
 * class RestAscAppList
 * handles the list of applications that are associated with a certain user
 */
class RestAscAppList extends RestROAuthResourceList {
    /**
     * realization of getDataType from iRestResource
     */
	public function getDataType() {
        return "application";
    }

    protected function _list() {
        return RestAppList::__list($this);
    }

    /**
     * overrides RestResource::getModel
     */
	protected function getModel() {
		$res = new Default_Model_Applications();
		$res->filter = FilterParser::getApplications($this->getParam("flt"));
		$f = new Default_Model_ResearchersFilter();
		$f->id->equals($this->getParam("id"));
//		$ff = new Default_Model_ApplicationsFilter();
//		$ff->owner->numequals($this->getParam("id"));
//		$res->filter->chain($f->chain($ff, "OR"),"AND");
		$res->filter->chain($f, "AND");
		return $res;
	}

    public function authorize($method) {
        $res = parent::authorize($method);
        $res = $res && (( $this->getParam("id") == $this->_userid ) || $this->userIsAdmin());
        if ( ! $res && $this->getError() == RestErrorEnum::RE_OK ) $this->setError(RestErrorEnum::ACCESS_DENIED);
        return $res;
    }
}

/**
 * class RestModAppList
 * handles the list of applications that have been marked as moderated
 *
 * NOTE: extends RO parent, but overrides authorize() to provide PUT access
 */
class RestModAppList extends RestROAdminResourceList {
    /**
     * realization of getDataType from iRestResource
     */
	public function getDataType() {
        return "application";
    }
    
    protected function _options() {
        $options = parent::_options();
        if ( $this->userIsAdmin() ) $options[] = RestMethodEnum::RM_PUT;
        return $options;
    }

    protected function _list() {
        return RestAppList::__list($this);
    }

    protected function init() {
        parent::init();
		$this->_parser = new RestAppXMLParser($this);
    }

    /**
     * overrides RestResource::getModel
     */
	protected function getModel() {
		$res = new Default_Model_Applications();
		$res->viewModerated = true;
		$res->filter->moderated->equals(true)->and($res->filter->deleted->equals(false));
		$f = FilterParser::getApplications($this->getParam("flt"));
		$res->filter->chain($f,"AND");
		return $res;
    }

    /**
     * overrides RestResourceList::authorize
     */
    public function put() {
        if ( parent::put() !== false ) {
            $id = $this->_parser->getID($this->getData(), "application:application");
            $apps = new Default_Model_Applications();
            $apps->filter->id->equals($id);
            if ( count($apps->items) === 0 ) {
                $this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
                return false;
            } else {
                $app = $apps->items[0];
                $app->moderated = true;
//                $app->name = $app->name . '-MODERATED-' . $app->guid;
                $app->modInfo->moddedBy = $this->_userid;
                $app->modInfo->moddedOn = date('Y-m-d H:i:s');
                try {
					$s=$this->getData();
                    $xml = new SimpleXMLElement($s);
                    $xml = $xml->xpath("//application:moderationReason");
                    if ( count($xml) != 0 ) {
                        $app->modInfo->modReason = strval($xml[0]);
                    }
                } catch (Exception $e) {
                    $this->setError(RestErrorEnum::RE_INVALID_REPRESENTATION);
                    return false;
                }
                db()->beginTransaction();
                try {
                    $app->save();
                    db()->commit();
                    $res = new RestAppItem(array("id" => $id), $this);
                    return $res->get();
                } catch (Exception $e) {
                    db()->rollBack();
                    $this->setError(RestErrorEnum::RE_BACKEND_ERROR, $e->getMessage(), true);
                    return false;
                }
            }
        } else return false;
    }

    /**
     * overrides RestROAdminResourceList::authorize
     */
    public function authorize($method) {
        if ( $method === RestMethodEnum::RM_PUT ) {
            return $this->userIsAdmin();
        } else return parent::authorize($method);
    }
}

/**
 * class RestModAppItem
 * handles individual entry of application that have been marked as moderated
 *
 * NOTE: extends RO parent, but overrides authorize() to provide PUT access
 */
class RestModAppItem extends RestROAdminResourceItem {
    /**
     * realization of getDataType from iRestResource
     */
	public function getDataType() {
        return "application";
    }
    
    protected function _options() {
        $options = array();
        if ( $this->userIsAdmin() ) $options[] = RestMethodEnum::RM_DELETE;
        return $options;
    }

    /**
     * overrides RestROAdminResourceItem::authorize
     */
    public function authorize($method) {
        if ( $method === RestMethodEnum::RM_DELETE ) {
            if( $this->userIsAdmin() ) {
				return true;
			} else {
				$this->setError(RestErrorEnum::RE_ACCESS_DENIED);
				return false;
			}
        } else return false;
    }
	
	/**
	 * overrides RestROAdminResourceItem::delete
	 */
	public function delete(){
		if( parent::delete() !== false ) {
			$apps = new Default_Model_Applications();
			$apps->viewModerated = true;
			$apps->filter->moderated->equals(true)->and($apps->filter->id->equals($this->getParam("id")));
			if( count($apps->items) !== 0 ) {
				$app = $apps->items[0];
				$app->moderated = false;
				$app->save();
				$res = new RestAppItem(array(), $this);
				return new XMLFragmentRestResponse($res->get(), $this);
			} else {
				$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
				return false;
			}
		} else return false;
	}
}

/**
 * class RestDelAppList
 * handles the list of applications that have been marked as deleted
 */
class RestDelAppList extends RestROAdminResourceList {
    /**
     * realization of getDataType from iRestResource
     */
	public function getDataType() {
        return "application";
    }
    
    protected function _list() {
        return RestAppList::__list($this);
    }

    /**
     * overrides RestResource::getModel
     */
	protected function getModel() {
		$res = new Default_Model_Applications();
		$res->viewModerated = true;
		$res->filter->deleted->equals(true);
		$f = FilterParser::getApplications($this->getParam("flt"));
		$res->filter->chain($f,"AND");
		return $res;
	}
}

/**
 * class RestRelAppList
 * handles the list of applications that are semantically related to a certain 
 * other application
 */
class RestRelAppList extends RestROResourceList {
    /**
     * realization of getDataType from iRestResource
     */
    public function getDataType() {
        return "relatedapp";
    }
    
    protected function _list() {
        return RestAppList::__list($this);
    }

    /**
     * overrides RestResource::getModel
     */
	protected function getModel() {
		$id = normalizeAppID($this);
		$res = new Default_Model_RelatedApplications($id);
		$res->limit = $this->_pageLength;
		$res->offset = $this->_pageOffset;
		$res->filter = FilterParser::getApplications($this->getParam("flt"));
		return $res;
    }
}

/**
 * class RestAppItem
 * handles individual application entries
 */
class RestAppItem extends RestResourceItem {
   
    protected function _options() {
        $options = array();
        $options[] = RestMethodEnum::RM_GET;
        if ( $this->authenticate() ) $options[] = RestMethodEnum::RM_DELETE;
        return $options;
    }

    /*** Attributes ***/

    /**
     * reference to model representing the requested resource item
     *
     */
    private $_res;
    /**
     * reference to parent collection of the model representing the requested resource item
     *
     */
    private $_resParent;
    /**
     * reference to XML parser
     *
     */
	private $_parser;
	private $_logged;
		
    /**
     * realization of getDataType from iRestResource
     */
    public function getDataType() {
        return "application";
    }

    /**
     * overrides RestResource::init()
     */
	protected function init() {
		$this->_logged = false;
		// must call iserIsAdmin before instanciating a Default_Model_Applications, so that
		// $_GET['userid'] is properly set
		$isAdmin = $this->userIsAdmin();
		$this->_resParent = new Default_Model_Applications();
		if ( $isAdmin ) $this->_resParent->viewModerated = true;
		if( substr($this->getParam("id") , 0, 2) === "s:" ) {
			$s_name = substr(trim($this->getParam("id")), 2);
			$this->_resParent->filter->cname->ilike($s_name);
		} else {
			$this->_resParent->filter->id->equals($this->getParam("id"));
		}
		if ( $isAdmin ) {
			$this->_resParent->viewModerated = true;
		}
		if ( count($this->_resParent->items) > 0 ) {
			$this->_res = $this->_resParent->items[0];
			for ($iii = 0; $iii < count($this->_resParent->items); $iii++) {
				$ddd = filter_var($this->_resParent->items[$iii]->deleted, FILTER_VALIDATE_BOOLEAN);
				$mmm = filter_var($this->_resParent->items[$iii]->moderated, FILTER_VALIDATE_BOOLEAN);
				if ( (! $ddd) && (! $mmm) ) {
					$this->_res = $this->_resParent->items[$iii];
					break;
				}
			}
			$this->_resParent->items = array($this->_res);
		} else {
			$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
			$this->_res = null;
		}
		$this->_parser = new RestAppXMLParser($this);
	}
    
    /**
     * overrides RestResource::getModel()
     */
    protected function getModel() {
		if ( ($this->getMethod() == RestMethodEnum::RM_GET) && (! $this->_logged) ) {
			$id = normalizeAppID($this);
			$cid = $this->getParam("cid"); if ( $cid == '' ) $cid = NULL;
			$src = base64_decode($this->getParam("src")); if ( $src == '' ) $src = NULL; //else $src = "'" . $src . "'";
			$userid = isset($this->_userid) ? $this->_userid : NULL;
			if ( $userid == 0 ) $userid = NULL;
			if ( ($id != '') && (! isnull($id)) ) {
				try {
					$sql = "NOTIFY api_log, '" . pg_escape_string(json_encode(array(
						"tbl" => 'app',
						"appid" => $id,
						"researcherid" => $userid,
						"source" => $cid,
						"ip" => $src
					))) . "';";
					db()->query($sql)->fetchAll();
				} catch (Exception $e) { /*ignore logging errors in case id or name not found*/ }
			}
			$this->_logged = true;
		}
		return $this->_resParent;
    }

    /**
     * overrides RestResource::delete()
     */
	public function delete() {
		if ( parent::delete() !== false ) {
			$ret = $this->get();
			if ( ! $this->_res->deleted ) {
				$this->_res->deleted = true;
                $this->_res->name = $this->_res->name.'-DELETED-'.$this->_res->guid;
                $this->_res->delInfo->deletedBy = $this->_userid;
                $this->_res->delInfo->deletedOn = date('Y-m-d H:i:s');
				$this->_res->save();
                if ( $ret !== false ) $this->logAction("delete", $this->getDataType(), $this->_res->id, $ret, null);
			}
			return $ret;
		} else return false;
	}

    /**
     * realization of authorize() from iRestAuthModule
     */
    public function authorize($method) {
        $res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
            $res = true;
            break;
        case RestMethodEnum::RM_POST:
        case RestMethodEnum::RM_PUT:
            $res = false;
			break;
        case RestMethodEnum::RM_DELETE:
			if ( ! is_null($this->_res) ) {
				if (  ! is_null($this->getUser()) ) {
					if ( $this->getUser()->privs->canDeleteApplication($this->_res) ) {
						$res = true;
					} else {
						$this->setError(RestErrorEnum::RE_ACCESS_DENIED);
						return false;
					}
				} else {
					$this->setError(RestErrorEnum::RE_ACCESS_DENIED);
					return false;
				}
			} else {
				$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
				return false;
			}
            break;
        }
        return $res;
    }
}

/**
 * class RestAppFilterNormalization
 * handles application filter syntax normalization and validation
 */
class RestAppFilterNormalization extends RestROResourceItem {
    /**
     * realization of getDataType from iRestResource
     */
    public function getDataType() {
        return "filter";
    }
    
    /**
     * overrides RestResource::get()
     */
	public function get() {
		if (  parent::get() !== false ) {
            if ( isset($this->_pars["flt"]) ) $flt = $this->_pars["flt"]; else $flt = "";
			return new XMLFragmentRestResponse(validateFilterActionHelper($flt, FilterParser::NORM_APP), $this);
		} else return false;
	}
}

/**
 * class RestAppFilterReflection
 * handles application filter reflection requests
 */
class RestAppFilterReflection extends RestROResourceItem {
    /**
     * realization of getDataType from iRestResource
     */
    public function getDataType() {
        return "filter";
    }
    
    /**
     * overrides RestResource::get()
     */
	public function get() {
		if ( parent::get() !== false ) {
			$s = '<application:filter>';
			$s .= FilterParser::fieldsToXML("any application person country vo discipline middleware category license", "application");
			$s .= '</application:filter>';
			return new XMLFragmentRestResponse($s, $this);
		} else return false;
    }
}

/**
 * class RestAppRatingReport
 * handles requests for an application's rating report
 */
class RestAppRatingReport extends RestROResourceItem {
    /**
     * realization of getDataType from iRestResource
     */
    public function getDataType() {
        return "ratingreport";
    }

    /**
     * overrides RestResource::get()
     */
	public function get() {
		if ( parent::get() !== false ) {
			global $application;
			$t = $this->getParam("type");
			switch($t){
				case "external":
					$t=2;
					break;
				case "internal":
					$t=1;
					break;
				default:
					$t=0;
					break;
			}
			$id = normalizeAppID($this);
			$p = $id . ($t != '' ? ",$t" : "");
			$db = $application->getBootstrap()->getResource('db');
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$r = $db->query('SELECT apprating_report_to_xml('.$p.');')->fetchAll();
			return new XMLFragmentRestResponse($r[0]->apprating_report_to_xml, $this);
		} else return false;
	}
}

/**
 * class RestAppRatingList
 * handles requests for an application's rating list of entries
 */
class RestAppRatingList extends RestROResourceList {
    /**
     * realization of getDataType from iRestResource
     */
    public function getDataType() {
        return "ratingreport";
    }

    protected function _list() {
        $ret = array();
		$this->_model->refresh();
		for ($i=0; $i < count($this->_model->items); $i++) {
            $ret[] = '<application:rating xmlns:application="' . RestAPIHelper::XMLNS_APPLICATION() . '" id="'.$this->_model->items[$i]->id.'" >'.$this->_model->items[$i]->rating.'</application:rating>';
        }
		return new XMLFragmentRestResponse($ret, $this);
    }
    
    /**
     * overrides RestResource::getModel()
     */
    protected function getModel() {
		$res = new Default_Model_AppRatings();
		$id = normalizeAppID($this);
		$res->filter->appid->numequals($id);            
		return $res;
    }
}

/**
 * class RestAppRatingItem
 * handles requests for an application's rating list of entries
 */
class RestAppRatingItem extends RestROResourceItem{
    /**
     * realization of getDataType from iRestResource
     */
    public function getDataType() {
        return "ratingreport";
    }
    
    /**
     * overrides RestResource::getModel()
     */
    protected function getModel() {
		$res = new Default_Model_AppRatings();
		$res->filter->id->equals($this->_pars["rid"]);            
		return $res;
    }
}

/**
 * class RestAppHistoryList
 * handles requests for an application's history list of entries
 */
class RestAppHistoryList extends RestROResourceList {
    /**
     * realization of getDataType from iRestResource
     */
    public function getDataType() {
        return "history";
    }
    
    protected function _list() {
        return $this->get();
    }

	/**
     * overrides RestResource::get()
     */
	public function get() {
		return $this->getFromDB();
	}


    public function getFromDB() {
		if ( parent::get() !== false ) {
			try {
				$oldvalue = '';
				$newvalue = '';
				$list = array();
				if ( is_numeric($this->getParam('id')) ) {
					$id = $this->getParam('id');
				} elseif ( substr($this->getParam('id'),0,2) === "s:" ) {
					db()->setFetchMode(Zend_Db::FETCH_BOTH);
					$id = db()->query("SELECT id FROM applications WHERE cname ILIKE '" . pg_escape_string(substr($this->getParam('id'), 2)) . "' FETCH FIRST 1 ROWS ONLY")->fetchAll();
					try {
						$id = $id[0][0];
						$this->_pars["id"] = $id;
					} catch (Exception $e) {
						debug_log('could not find ID for application with cname `' . pg_escape_string(substr($this->getParam('id'), 2)) . "'");
					}
				}					
				db()->setFetchMode(Zend_Db::FETCH_OBJ);
				$rs = db()->query("SELECT (applications.history).*, (applications.history).nextid, (applications.history).previd FROM applications WHERE applications.id = $id ORDER BY (applications.history).tstamp DESC")->fetchAll();
				foreach ($rs as $r) {
					$event = $r->event;
					$userid = $r->userid;
					$username = $r->username;
					$usercontact = $r->usercontact;
					$apiver = $r->apiver;
					$timestamp = $r->tstamp;
					$disposition = $r->disposition;
					if ( $timestamp != '' ) {
						$timestamp = str_replace(' ','T', $timestamp);
	//					$timestamp = substr($timestamp,0,4)."-".substr($timestamp,6,2)."-".substr($timestamp,4,2).substr($timestamp,8);
					}
					if ( $userid !== '' ) {
						$ppl = new Default_Model_Researchers();
						// retreive the user's CName even if the record has been marked as deleted
						$ppl->viewModerated = true; 
						$ppl->filter->id->numequals($userid);
						if (count($ppl->items) > 0) {
							$userCname = $ppl->items[0]->cname;
						} else {
							$userCname = '';
						}
					}
	//				$oldvalue = str_replace('\012','',str_replace('\011','',$r->oldval));
	//				$newvalue = str_replace('\012','',str_replace('\011','',$r->newval));
					$oldvalue = '<history:oldvalue xmlns:history="' . RestAPIHelper::XMLNS_HISTORY() . '">' . $r->oldval . '</history:oldvalue>';
					$newvalue = '<history:newvalue xmlns:history="' . RestAPIHelper::XMLNS_HISTORY() . '">' . $r->newval . '</history:newvalue>';
					$list[] = '<history:history xmlns:history="' . RestAPIHelper::XMLNS_HISTORY() . '"'.' id="'. $r->id .'" ' . ($r->previd != '' ? 'previd="' . $r->previd .'" ' : '') . ($r->nextid != '' ? 'nextid="' . $r->nextid .'" ' : '') . 'event="'.$event. '"' . ($disposition != '' ? ' disposition="' . $disposition . '"' : '') . ' userid="'.$userid.'" usercname="'.$userCname.'" username="'.$username.'" usercontact="'.$usercontact.'" apiver="'.$apiver.'" timestamp="'.$timestamp.'">'.$oldvalue.$newvalue.'</history:history>';
				}
				$this->_total = count($list);
				return new XMLFragmentRestResponse($list, $this);
			} catch (Exception $e) {
				$this->setError(RestErrorEnum::RE_BACKEND_ERROR, $e->getMessage());
				return false;
			}
		}
	}

    public function getFromFile() {
        if ( parent::get() !== false ) {
            if ( $this->_logfile != '' ) {
                try {
                    $oldvalue = '';
                    $newvalue = '';
					$list = array();
                    if ( is_numeric($this->getParam('id')) ) {
                        $id = $this->getParam('id');
                    } elseif ( substr($this->getParam('id'),0,2) === "s:" ) {
                        db()->setFetchMode(Zend_Db::FETCH_BOTH);
                        $id = db()->query("(SELECT id FROM applications WHERE cname ILIKE '" . pg_escape_string(substr($this->getParam('id'), 2)) . "
' FETCH FIRST 1 ROWS ONLY)")->fetchAll();
                        try {
                            $id = $id[0][0];
                            $this->_pars["id"] = $id;
                        } catch (Exception $e) {
                            debug_log('could not find ID for application with cname `' . pg_escape_string(substr($this->getParam('id'), 2)) . "'");
                        }
                    }					
					$log = '';
					$f = fopen($this->_logfile, "r");
					if ( $f !== false ) {
						error_log('Acquiring shared lock on logfile "' . $this->_logfile . '"');
						if ( flock($f, LOCK_SH) ) {
							error_log('Shared lock on logfile "' . $this->_logfile . '" acquired');
							$log = file_get_contents($this->_logfile);
//							error_log('Logfile "' . $this->_logfile . '" loaded as text');
							flock($f, LOCK_UN);
						} else error_log('Could not acquire shared lock on logfile "' . $this->_logfile . '"');
						fclose($f);
					}
					if ( $log == '' ) {
	                    $this->setError(RestErrorEnum::RE_BACKEND_ERROR, "Could not access history database.");
		                return false;
					}
                    $log = "<log>".$log."</log>";
                    //$log = new SimpleXMLElement($log);
					$log = simpledom_load_string($log);
//					error_log("Logfile loaded as XML");
					$xpath = $log->sortedXPath('action[@target="application" and @id="'.$id.'" and (@event="update" or @event="delete")]','@timestamp');
//					error_log("Got sorted XPath from XML log");
                    //$xpath = $log->xpath('action[@target="application" and @id="'.$id.'"]');
                    $counter = 0;
					foreach ($xpath as $x) {
                        $counter = $counter + 1;
                        if ( $x->attributes()->event) $event= strval($x->attributes()->event); else $event= '';
                        if ( $x->attributes()->userid ) $userid = strval($x->attributes()->userid); else $userid = '';
                        if ( $x->attributes()->username ) $username = strval($x->attributes()->username); else $username = '';
                        if ( $x->attributes()->usercontact ) $usercontact = strval($x->attributes()->usercontact); else $usercontact = '';
                        if ( $x->attributes()->apiver ) $apiver = strval($x->attributes()->apiver); else $apiver= '1.0';
                        if ( $x->attributes()->disposition ) $disposition = strval($x->attributes()->disposition); else $disposition = '';
                        if ( $x->attributes()->timestamp ) $timestamp = strval($x->attributes()->timestamp); else $timestamp = '';
                        if ( $timestamp != '' ) {
                            $timestamp = str_replace(' ','T', $timestamp);
                            $timestamp = substr($timestamp,0,4)."-".substr($timestamp,6,2)."-".substr($timestamp,4,2).substr($timestamp,8);
						}
						if ( $userid !== '' ) {
							$ppl = new Default_Model_Researchers();
							// retreive the user's CName even if the record has been marked as deleted
							$ppl->viewModerated = true; 
							$ppl->filter->id->numequals($userid);
							if (count($ppl->items) > 0) {
								$userCname = $ppl->items[0]->cname;
							} else {
								$userCname = '';
							}
						}
                        $counter = str_replace(array("-",":","T"),"",$timestamp).$userid.substr($event,0,1);
                        $value = $x->xpath('oldvalue');
                        if ( count($value) === 1 ) {
                            $value = $value[0];
                            $value = base64_decode($value);
                            $value = bzdecompress($value);
                            $oldvalue = '<history:oldvalue xmlns:history="' . RestAPIHelper::XMLNS_HISTORY() . '">'.
                                $value.'</history:oldvalue>';
                        }
                        $value = $x->xpath('newvalue');
                        if ( count($value) === 1 ) {
                            $value = $value[0];
                            $value = base64_decode($value);
                            $value = bzdecompress($value);
                            $newvalue = '<history:newvalue xmlns:history="' . RestAPIHelper::XMLNS_HISTORY() . '">'.
                                $value.'</history:newvalue>';
                        }
                        if ( $oldvalue != '' && $newvalue != '' ) { 
                            if ( $this->_listMode === RestListModeEnum::RL_NORMAL ) {
                                $list[] = '<history:history xmlns:history="' . RestAPIHelper::XMLNS_HISTORY() . '"'.' id="'.$counter.'" event="'.$event. '"' . ($disposition != '' ? ' disposition="' . $disposition . '"' : '') . ' userid="'.$userid.'" usercname="'.$userCname.'" username="'.$username.'" usercontact="'.$usercontact.'" apiver="'.$apiver.'" timestamp="'.$timestamp.'">'.$oldvalue.$newvalue.'</history:history>';
                            } elseif ( $this->_listMode === RestListModeEnum::RL_LISTING ) {
                                $list[] = '<history:history xmlns:history="' . RestAPIHelper::XMLNS_HISTORY() . '"'.' id="'.$counter.'" event="'.$event.'" userid="'.$userid.'" usercname="'.$userCname.'" username="'.$username.'" usercontact="'.$usercontact.'" apiver="'.$apiver.'" timestamp="'.$timestamp.'"/>';
                            }
                        }
                    }
                    $this->_total = count($list);
                } catch (Exception $e) {
                    $this->setError(RestErrorEnum::RE_BACKEND_ERROR, $e->getMessage());
                    return false;
				}
//				error_log("XML logfile parsed successfully");
				return new XMLFragmentRestResponse($list, $this);
            } else return new XMLFragmentRestResponse("", $this);
        } else return false;
	}
}

/**
 * class RestAppHistoryItem
 * handles requests an application's historical representation
 */
class RestAppHistoryItem extends RestROResourceItem {
    /**
     * realization of getDataType from iRestResource
     */
    public function getDataType() {
        return "history";
    }
   
    /**
     * overrides RestResource::get()
     */
    public function get() {
        if ( parent::get() !== false ) {
            $res = new RestAppHistoryList($this->_pars); 
			$res->startLogging($this->_logfile);
            $res = $res->get();
            if ( $res !== false ) {
                $res = $res->getData();
                $hid = $this->getParam("hid");
				$found = false;
                foreach ($res as $r) {
                    $rr = strval(RestAPIHelper::wrapResponse($r, $this->getDataType()));
                    try {
                        $xml = new SimpleXMLElement($rr);
                        $xml = $xml->xpath("//history:history");
                    } catch (Exception $e) {
                        debug_log('RestAppHistoryItem::get(): Error while loading app history XML representation');
                    }
                    if ( count($xml) > 0 ) {
                        if ( strval($xml[0]->attributes()->id) === $hid ) {
                            $found = $r;
                            break;
                        }
                    }
                }
                if ( $found !== false ) {
                    return new XMLFragmentRestResponse($found, $this);
                } else {
                    $this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
                    return false;
                }
            } else {
                $this->setError($res->getError());
                return false;
            }
        } else return false;
    }
}

class RestAppHistoryDiffItem extends RestROResourceItem {
    /**
     * realization of getDataType from iRestResource
     */
    public function getDataType() {
        return "diff";
    }
   
    /**
     * overrides RestResource::get()
     */
    public function get() {
		if ( parent::get() !== false ) {
			$hid = $this->getParam("hid");
			db()->setFetchMode(Zend_Db::FETCH_BOTH);
			$x = db()->query("SELECT (actions).diff FROM apilog.actions WHERE id = '" . pg_escape_string($hid) . "'")->fetchAll();
			$x = $x[0];
			$x = $x[0];
			$xml = RestAPIHelper::wrapResponse('<appdb:diff><![CDATA[' . $x . ']]></appdb:diff>', $this->getDataType());
			return $xml;
        } else return false;
    }
}

/**
 * class RestAppHistoryRBItem
 * handles requests an application's historical representation
 */
class RestAppHistoryRBItem extends RestROAuthResourceItem {
    /**
     * realization of getDataType from iRestResource
     */
    public function getDataType() {
        return "application";
    }
   
    /**
     * overrides RestResource::get()
     */
	public function get() {
		if ( parent::get() !== false ) {
			$pars = $this->_pars;
			$res = new RestAppHistoryItem($this->_pars, $this);
			$res->startLogging($this->_logfile);
			$xml = $res->get();
			if ( $xml != '' ) {
				$xml = new SimpleXMLElement(strval($xml->finalize()));
				$xml = $xml->xpath('//history:oldvalue/application:application');
				if ( count($xml) > 0 ) {
					$this->_pars['routeXslt'] = 'applications';
					$xml = RestAPIHelper::wrapResponse($xml[0]->asXML(), $this->getDataType());
					$this->_pars['data'] = $xml;
					$res = new RestAppList($this->_pars);
					$res->startLogging($this->_logfile);
					$ret = $res->post("rollback");
					if ( $res->getError() !== RestErrorEnum::RE_OK ) {
						$this->setError($res->getError());
					}
					return $ret;
				} else {
					$this->setError(RestErrorEnum::RE_BACKEND_ERROR);
					return false;
				}
			} else {
				if ( $res->getError() !== RestErrorEnum::RE_OK ) {
					$this->setError($res->getError());
				} else {
					$this->setError(RestErrorEnum::RE_BACKEND_ERROR);
				}
				return false;
			}
		} else return false;
	}

}

/**
 * class RestAppTagXMLParser
 * derived class for parsing application resources
 */
class RestAppTagXMLParser extends RestXMLParser {
    public function parse($xml) {
		if ( ! is_null($this->_user) ) {
			$tag = new Default_Model_AppTag();
			try {
				$xml = new SimpleXMLElement($xml);
			} catch (Exception $e) {
				$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
				return $tag;
			}
			// basic properties
			$xmli = $xml->xpath('//application:tag');
			if ( count($xmli) === 0 ) {
				$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
				return $tag;
			}
            $xml = $xmli[0];
            $tagval = strval($xml);
            if ( $tagval != '' ) {
                $tag->tag = $tagval;
                $tag->researcherid = $this->_parent->getUser()->id;
                $tag->appid = $this->_parent->getParam('id');
                try {
                    $tag->save();
                } catch (Exception $e) {
                    $this->_error = RestErrorEnum::RE_BACKEND_ERROR;
                    $this->_extError = 'Invalid tag name or tag already present';
                    return $tag;
                }
            } else {
                $this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
				return $tag;
            }
        }
        $this->_error = RestErrorEnum::RE_OK;
        return $tag;
    }
}


/**
 * class RestAppTagList
 * handles requests for application tag lists
 */
class RestAppTagList extends RestResourceList {
    /**
     * realization of getDataType from iRestResource
     */
    public function getDataType() {
        return "tag";
    }

    protected function _options() {
        $options = array();
        $options[] = RestMethodEnum::RM_GET;
        if ( $this->authenticate() ) $options[] = RestMethodEnum::RM_PUT;
        return $options;
    }

    protected function _list() {
        return $this->get();
    }

    protected function init() {
        $this->_parser = new RestAppTagXMLParser($this);
    }

    /**
     * overrides RestResource::get()
     */
    public function get() {
        if ( parent::get() !== false ) {
            if ( $this->_listMode === RestListModeEnum::RL_NORMAL ) {
				$res = new Default_Model_AppTags();
				$id = normalizeAppID($this);
                $res->filter->appid->numequals($id);
                $res->refresh();
                $xml = array();
                foreach ($res->items as $item) {
                    $xml[] = '<application:tag xmlns:application="' . RestAPIHelper::XMLNS_APPLICATION() . '" id="'.$item->id.'" system="'.(is_null($item->researcherID) ? 'true' : 'false').'" '.(is_null($item->researcherID) ? '' : 'ownerid="'.$item->researcherID.'"').'>'.$item->tag.'</application:tag>'."\n";
                }
				$this->_total = count($res->items);
                return new XMLFragmentRestResponse($xml, $this);
            } elseif ( $this->_listMode === RestListModeEnum::RL_LISTING ) {
                $this->_listMode = RestListModeEnum::RL_NORMAL;
                return $this->_list();
            }
        } else return false;
    }

    public function put() {
        if ( parent::put() !== false ) {
            $tag = $this->_parser->parse($this->getData());
            if ( $this->_parser->getError() !== RestErrorEnum::RE_OK ) {
    			$this->setError($this->_parser->getError(), $this->_parser->getExtError(), false);
                return false;
			} else {
				// for some weird reason, the real ID is the ID + 1 (maybe due to the INSTEAD rule)
                $res = new RestAppTagItem(array("tid" => $tag->id + 1), $this);
                return $res->get();
            }
        } else return false;
    }

    public function authorize($method) {
        $res = false;
        switch($method) {
        case(RestMethodEnum::RM_GET):
            $res = true;
            break;
        case(RestMethodEnum::RM_PUT):
			$apps = new Default_Model_Applications();
			$id = normalizeAppID($this);
            $apps->filter->id->numequals($id);
            if ( count($apps->items) > 0 ) {
                switch($apps->items[0]->tagPolicy) {
                case(0):
                    $res = $this->userIsAdmin() || ( $this->authenticate() && ( $this->_userID === $apps->items[0]->ownerid || $this->_userID === $apps->items[0]->addedby || $this->getUser()->privs->canModifyApplicationTags($apps->items[0]) ) );
                    break;
                case(1):
                    $associated = false;
                    foreach($apps->items[0]->researchers as $r) {
                        if ( $r->id === $this->_userID ) {
                            $associated = true;
                            break;
                        }
                    }
                    $res = $this->userIsAdmin() || ( $this->authenticate() && ( $this->_userID === $apps->items[0]->ownerid || $this->_userID === $apps->items[0]->addedby || $this->getUser()->privs->canModifyApplicationTags($apps->items[0]) || $associated ) );
                    break;
                case(2):
                    $res = $this->authenticate();
                    break;
                }
				if ( ! $res ) {
					$this->setError(RestErrorEnum::RE_ACCESS_DENIED);
					return false;
				}
            } else {
                $this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
                return false;
            }
            break;
        }
        return $res;
    }
}

/**
 * class RestAppTagItem
 * handles requests for a specific application tag
 */
class RestAppTagItem extends RestResourceItem {
    /*** Attributes ***/
    private $_res;

    /**
     * realization of getDataType from iRestResource
     */
    public function getDataType() {
        return "tag";
    }

    protected function init() {
        $res = new Default_Model_AppTags();
        $res->filter->id->equals($this->getParam('tid'));
        $res->refresh();
        if ( count($res->items) > 0 ) {
            $this->_res = $res->items[0];
        } else {
            $this->_res = null;
            $this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
        }
    }

    protected function _options() {
        $options = array();
        $options[] = RestMethodEnum::RM_GET;
        if ( $this->authenticate() ) $options[] = RestMethodEnum::RM_DELETE;
        return $options;
    }

    protected function _list() {
        return $this->get();
    }
   
    /**
     * overrides RestResource::get()
     */
    public function get() {
        if ( parent::get() !== false ) {
            if ( ! is_null($this->_res) ) {
                $item = $this->_res;
                $xml = '<application:tag xmlns:application="' . RestAPIHelper::XMLNS_APPLICATION() . '" id="'.$item->id.'" system="'.(is_null($item->researcherID) ? 'true' : 'false').'" '.(is_null($item->researcherID) ? '' : 'ownerid="'.$item->researcherID.'"').'>'.$item->tag.'</application:tag>'."\n";
                return new XMLFragmentRestResponse($xml, $this);
            } else {
                $this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
                return false;
            }
        } else return false;
    }

    public function delete() {
        if ( parent::delete() !== false ) {
            if ( ! is_null($this->_res) ) {
                $ret = $this->get();
                $tags = new Default_Model_AppTags();
                $tags->remove($this->_res);
                return $ret;
            } else {
                $this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
                return false;
            }
        } else return false;
    }

    public function authorize($method) {
        $res = false;
        switch($method) {
        case(RestMethodEnum::RM_GET):
            $res = true;
            break;
        case(RestMethodEnum::RM_DELETE):
            if ( ! is_null($this->_res) ) {
                $res = $this->userIsAdmin() || ($this->authenticate() && ($this->_res->application->addedby === $this->_userid || $this->_res->application->ownerid === $this->_userid || $this->_res->researcherid === $this->_userid || $this->getUser()->privs->canModifyApplicationTags($this->_res->application) ) );
                $res = $res && ! is_null($this->_res->researcherid);
				if ( ! $res ) {
					$this->setError(RestErrorEnum::RE_ACCESS_DENIED);
					return false;
				}
            } else {
                $this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
                return false;
            }
            break;
        }
        return $res;
    }
}

/**
 * class RestAppPubList
 * handles requests for a specific application tag
 */
class RestAppPubList extends RestResourceList {
    /*** Attributes ***/
    private $_res;

    /**
     * realization of getDataType from iRestResource
     */
    public function getDataType() {
        return "publication";
    }

    public function init() {
		$this->_res = new Default_Model_Applications();
		$id = normalizeAppID($this);
		$this->_res->filter->id->numequals($id);
        $this->_res->refresh();
		if ( count($this->_res->items) > 0 ) {
            $this->_res = $this->_res->items[0];
        } else {
            $this->_res = null;
            $this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
        }
    }

    protected function _options() {
        $options = array();
        $options[] = RestMethodEnum::RM_GET;
        if ( $this->authenticate ) $options[] = RestMethodEnum::RM_PUT;
        if ( $this->authenticate ) $options[] = RestMethodEnum::RM_POST;
        return $options;
    }

    protected function _list() {
        $res = new Default_Model_AppDocuments();
        $res->filter->appid->equals($this->getParam("id"));
		$items = array();
		foreach($res->items as $i) {
			$items[] = '<publication:publication id="'.$i->id.'">'.$i->title.'</publication:publication>';
		}
		return new XMLFragmentRestResponse($items, $this);
    }

	private function putpost($method) {
        
        if ( $this->_res === null ) return false;

        $data = $this->getData();
        if ($method === RestMethodEnum::RM_POST) {
            try {
                $xml = new SimpleXMLElement($data);
            } catch (Exception $e) {
                $this->setError(RestErrorEnum::RE_INVALID_REPRESENTATION);
                return false;
            }
            $xp = $xml->xpath("publication:publication");                
            if ( count($xp) > 0 ) {
                $id = strval($xp[0]->attributes()->id);
            }
			if ( $id == '' ) {
                $this->setError(RestErrorEnum::RE_INVALID_REPRESENTATION);
                return false;
			}
            try {
                $xml = new DOMDocument();
                $xml->loadXML(strval(RestAPIHelper::wrapResponse(strval($this->get()))));
                $xpath = new DOMXPath($xml);
                $xpres = $xpath->query('//publication:publication[@id="'.$id.'"]');
            } catch (Exception $e) {
                $this->setError(RestErrorEnum::RE_INVALID_REPRESENTATION);
                return false;
            }
            if ( $xpres !== false ) {
                $xnode = $xpres->item(0);
                $xparent = $xnode->parentNode;
                $xparent->removeChild($xpres->item(0));
                try {
                    $xml = new SimpleXMLElement($xml->saveXML());
                } catch (Exception $e) {
                    $this->setError(RestErrorEnum::RE_INVALID_REPRESENTATION);
                    return false;
                }
            } else {
                $this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
                return false;
            }
        } else {
            try {
                $xml = new SimpleXMLElement(strval(RestAPIHelper::wrapResponse($this->get())));
            } catch (Exception $e) {
                $this->setError(RestErrorEnum::RE_INVALID_REPRESENTATION);
                return false;
            }
        }
		try {
			$data = new SimpleXMLElement($data);
			$data = $data->xpath("//publication:publication");
		} catch (Exception $e) {
             $this->setError(RestErrorEnum::RE_INVALID_REPRESENTATION);
             return false;
		}
		if ( count($data) > 0 ) {
			$data = $data[0]->asXML();
		} else {
			$this->setError(RestErrorEnum::RE_INVALID_REPRESENTATION);
			return false;
		}
        $data = array($data);
        $xp = $xml->xpath("publication:publication");
        foreach($xp as $x) {
            $data[] = $x->asXML();
        }
        $data = '<application:application id="'.$this->_res->id.'">'.implode($data).'</application:application>';
		$data = strval(RestAPIHelper::wrapResponse($data));
		$this->_pars['data'] = $data;
		$res = new RestAppList($this->_pars);
		$ret = $res->post();
		$ret = new SimpleXMLElement(strval($ret->finalize()));
		if ( $method === RestMethodEnum::RM_POST ) {
			$xp = $ret->xpath('//publication:publication[@id="'.$id.'"]');
		} else {
			// try to find the publication with the max id attribute, which should be the newest
			$xp = $ret->xpath('//publication:publication[not(@id <= preceding-sibling::publication:publication/@id) and not(@id <= following-sibling::publication:publication/@id)]');
			// if this fails, try to find the last publication, which still should be the newest, 
			// since results are returned in DB (natural) order
			if ( count($xp) == 0 || ! is_array($xp) ) $xp = $ret->xpath('//publication:publication[last()]');
		}
		$ret = array();
		foreach ($xp as $x) {
			$ret[] = $x->asXML();
		}
		return new XMLFragmentRestResponse($ret);
	}

	public function put() {
		if ( parent::put() !== false) {
			return $this->putpost(RestMethodEnum::RM_PUT);
		} else return false;
	}

	public function post() {
		if ( parent::post() !== false) {
			return $this->putpost(RestMethodEnum::RM_POST);
		} else return false;
	}
   
    /**
     * overrides RestResource::get()
     */
    protected function getModel() {
		$res = new Default_Model_AppDocuments();
		$id = normalizeAppID($this);
        $res->filter->appid->numequals($id);
        return $res;
    }

    public function authorize($method) {
        $res = false;
        switch($method) {
        case RestMethodEnum::RM_GET:
            $res = true;
            break;
        case RestMethodEnum::RM_PUT:
        case RestMethodEnum::RM_POST:
            $res = $this->userIsAdmin() || ( 
                $this->authenticate() && (
                    $this->getUser->privs->canModifyApplicationDocuments($this->_res) ||
                    $this->_userid === $this->_res->ownerid ||
                    $this->_userid === $this->_res->addedby
                )
            );
			if ( ! $res ) $this->setError(RestErrorEnum::RE_ACCESS_DENIED);
        }
        return $res;
    }
}

/**
 * class RestAppPubItem
 * handles requests for a specific application tag
 */
class RestAppPubItem extends RestResourceItem {
    /*** Attributes ***/
    private $_res;

    /**
     * realization of getDataType from iRestResource
     */
    public function getDataType() {
        return "publication";
    }

    protected function init() {
		$this->_res = new Default_Model_Applications();
		$id = normalizeAppID($this);
        $this->_res->filter->id->numequals($id);
        $this->_res->refresh();
		if ( count($this->_res->items) > 0 ) {
            $this->_res = $this->_res->items[0];
			$res = new Default_Model_AppDocuments();
			$res->filter->id->equals($this->getParam('pid'))->and($res->filter->appid->numequals($id));
			$res->refresh();
			if ( ! (count($res->items) > 0) ) {
				$this->_res = null;
				$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
			}
        } else {
            $this->_res = null;
            $this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
        }
    }

    protected function _options() {
        $options = array();
        $options[] = RestMethodEnum::RM_GET;
        if ( $this->authenticate() ) $options[] = RestMethodEnum::RM_DELETE;
        return $options;
    }

    /**
     * overrides RestResource::get()
     */
    protected function getModel() {
		if ( $this->_res !== null ) {
	        $res = new Default_Model_AppDocuments();
    	    $res->filter->id->equals($this->getParam("pid"));
        	return $res;
		} else return false;
    }

	public function delete() {
		if ( parent::delete() !== false ) {
			$ret = $this->get();
	        $res = new Default_Model_AppDocuments();
    	    $res->filter->id->equals($this->getParam("pid"));
			if ( count($res) > 0 ) {
				$i = $res->items[0];
				$res->remove($i);
			} else {
            	$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
				return false;
			}
			return $ret;
		} else return false;
	}

	public function authorize($method) {
        $res = false;
        switch($method) {
        case RestMethodEnum::RM_GET:
            $res = true;
            break;
        case RestMethodEnum::RM_DELETE:
            $res = $this->userIsAdmin() || ( 
                $this->authenticate() && (
                    $this->getUser->privs->canModifyApplicationDocuments($this->_res) ||
                    $this->_userid === $this->_res->ownerid ||
                    $this->_userid === $this->_res->addedby
                )
            );
			if ( ! $res ) $this->setError(RestErrorEnum::RE_ACCESS_DENIED);
        }
        return $res;
    }
}

/**
 * class RestAppLogistics
 * handles application counting per various properties
 */
class RestAppLogistics extends RestROResourceItem {
    /**
     * realization of getDataType from iRestResource
     */
    public function getDataType() {
        return "logistics";
    }
    
    /**
     * overrides RestResource::get()
     */
	public function get($extraFilter = null) {
		if ( parent::get() !== false ) {
			global $application;
			$isAdmin = $this->userIsAdmin();
			$mapper = new Default_Model_ApplicationsMapper();
			$db = $application->getBootstrap()->getResource('db');
			$flt = $this->getParam("flt");
			$select = $mapper->getDbTable()->getAdapter()->select()->distinct()->from('applications');
			$from = '';
			$where = '';
			$orderby = '';
			$limit = '';
			$filter = FilterParser::getApplications($flt);
			if ( is_array($filter->expr()) ) {
				$ex = implode(" ", $filter->expr()); 
			} else {
				$ex = $filter->expr();
			}
			if (
				( strpos($ex, 'applications.moderated) IS FALSE') === false ) ||
				( strpos($ex, 'applications.deleted) IS FALSE') === false )
			) {
				$f = new Default_Model_ApplicationsFilter();
				$f->moderated->equals(false)->and($f->deleted->equals(false));
				$filter->chain($f,"AND");
			}
			if ( is_array($filter->expr()) ) {
				foreach($filter->expr() as $x) {
					getZendSelectParts($select, $from, $where, $orderby, $limit);
					if ( (strpos($from, ' researchers ') !== false) && (strpos($ex, 'researchers.deleted) IS FALSE') === false) ) {
						$f = new Default_Model_ResearchersFilter();
						$f->deleted->equals(false);
						$filter->chain($f,"AND");
					}
					if ( (strpos($from, ' vos ') !== false) && (strpos($ex, 'vos.deleted) IS FALSE') === false) ) {
						$f = new Default_Model_VOsFilter();
						$f->deleted->equals(false);
						$filter->chain($f,"AND");
					}
				}
			} else {
				getZendSelectParts($select, $from, $where, $orderby, $limit);
				if ( (strpos($from, ' researchers ') !== false) && (strpos($ex, 'researchers.deleted) IS FALSE') === false) ) {
					$f = new Default_Model_ResearchersFilter();
					$f->deleted->equals(false);
					$filter->chain($f,"AND");
				}
				if ( (strpos($from, ' vos ') !== false) && (strpos($ex, 'vos.deleted) IS FALSE') === false) ) {
					$f = new Default_Model_VOsFilter();
					$f->deleted->equals(false);
					$filter->chain($f,"AND");
				}
			}
			if (! is_null($extraFilter)) {
				$filter->chain($extraFilter, "AND");
			}
			$mapper->joins($select, $filter);
			if ( is_array($filter->expr()) ) {
				$from = array();
				$where = array();
				foreach($filter->expr() as $x) {
					$s = clone $select;
					$s->where($x);
					getZendSelectParts($s, $f, $w, $orderby, $limit);
					$from[] = $f;
					$where[] = $w;
				}
				$flt = str_replace("''", "\'", php_to_pg_array($filter->fltstr, false));
				$from = str_replace("''", "\'", php_to_pg_array($from, false));
				$where = str_replace("''", "\'", php_to_pg_array($where, false));
			} else {
				$select->where($filter->expr());
				getZendSelectParts($select, $from, $where, $orderby, $limit);
			}
			$db->setFetchMode(Zend_Db::FETCH_BOTH);
//			debug_log($db->quoteInto('SELECT * FROM app_logistics(?,?,?)', array($flt, $from, $where)));
			$rs = $db->query('SELECT * FROM app_logistics(?,?,?)', array($flt, $from, $where))->fetchAll();
			if ( count($rs) > 0 ) {
				$rs = $rs[0];
				$x = $rs['app_logistics'];
			} else {
				$x = '';
			}
			return new XMLFragmentRestResponse($x, $this);
		} else return false;
	}
}

class RestOwnAppLogistics extends RestAppLogistics {
    /**
     * overrides RestResource::get()
     */
	public function get($extraFilter = NULL) {
		if ( trim($this->getParam("flt")) != "" ) {
			$this->_pars["flt"] = "application.addedby:" . $this->getParam("id") . " application.owner:" . $this->getParam("id") . " | " . $this->getParam("flt");
		} else {
			$this->_pars["flt"] = "application.addedby:" . $this->getParam("id") . " application.owner:" . $this->getParam("id");
		}
		return parent::get();
	}

    /**
     * overrides of authorize() from RestAppLogistics
     */
	public function authorize($method) {
		$wrapper = new RestOwnAppList($this->_pars);
		$res = $wrapper->authorize($method);
		$this->_userid = $wrapper->_userid;
		return $res;
	}

}

class RestEdtAppLogistics extends RestAppLogistics {
   	/**
     * overrides RestAppLogistics::get()
     */
	public function get($extraFilter = NULL) {
		$f = new Default_Model_PermissionsFilter();
		$f->actor->numequals("(SELECT guid FROM researchers WHERE id = " . $this->getParam("id") . ")")->and($f->actionid->any("app_metadata_actions()", false, false));
		return parent::get($f);
	}

 /**
     * overrides of authorize() from RestAppLogistics
     */
	public function authorize($method) {
		$wrapper = new RestEdtAppList($this->_pars);
		$res = $wrapper->authorize($method);
		$this->_userid = $wrapper->_userid;
		return $res;
	}
}

class RestAscAppLogistics extends RestAppLogistics {
	/**
     * overrides RestAppLogistics::get()
     */
	public function get($extraFilter = NULL) {
		$f = new Default_Model_ResearchersFilter();
		$f->id->equals($this->getParam("id"));
		return parent::get($f);
	}

	/**
     * overrides of authorize() from RestAppLogistics
     */
	public function authorize($method) {
		$wrapper = new RestAscAppList($this->_pars);
		$res = $wrapper->authorize($method);
		$this->_userid = $wrapper->_userid;
		return $res;
	}
}

class RestAppBookmarkLogistics extends RestAppLogistics {
	/**
     * overrides RestAppLogistics::get()
     */
	public function get($extraFilter = NULL) {
		$f = new Default_Model_AppBookmarksFilter();
		$f->researcherid->equals($this->getParam("id"));
		return parent::get($f);
	}

	/**
     * overrides of authorize() from RestAppLogistics
     */
	public function authorize($method) {
		$wrapper = new RestAppBookmarkList($this->_pars);
		$res = $wrapper->authorize($method);
		$this->_userid = $wrapper->_userid;
		return $res;
	}
}

class RestAppFollowedLogistics extends RestAppLogistics {
	/**
     * overrides RestAppLogistics::get()
     */
	public function get($extraFilter = NULL) {
		$ids = array();
		$f = new Default_Model_MailSubscriptions();
		$f->filter->flt->like('%id:SYSTAG_FOLLOW')->and($f->filter->researcherid->equals($this->getParam("id")));
		if ( count($f->items) > 0 ) {
			foreach( $f->items as $i ) {
				$id = explode(" ", $i->flt);
				$id = explode(":", $id[0]);
				$ids[] = $id[1];
			}
			$res = new Default_Model_Applications();
			$res->filter->id->in($ids);
			$f = $res->filter;
		} else $f = null;
		return parent::get($f);
	}

	/**
     * overrides of authorize() from RestAppLogistics
     */
	public function authorize($method) {
		debug_log("A");
		$wrapper = new RestAppFollowedList($this->_pars);
		$res = $wrapper->authorize($method);
		$this->_userid = $wrapper->_userid;
		debug_log("ans: " . $res);
		return $res;
	}
}

class RestRelAppLogistics extends RestAppLogistics {
    /**
     * overrides RestResource::get()
     */
	public function get($extraFilter = NULL) {
		if ( trim($this->getParam("flt")) != "" ) {
			$this->_pars["flt"] = "=application.relatedto:" . $this->getParam("id") . " | " . $this->getParam("flt");
		} else {
			$this->_pars["flt"] = "=application.relatedto:" . $this->getParam("id");
		}
		return parent::get();
	}

    /**
     * overrides of authorize() from RestAppLogistics
     */
	public function authorize($method) {
		$wrapper = new RestRelAppList($this->_pars);
		$res = $wrapper->authorize($method);
		$this->_userid = $wrapper->_userid;
		return $res;
	}

}

class RestAppVAXMLParser extends RestXMLParser {
	CONST VA_VERSION_MIN_SIZE = 1;
	CONST VA_VERSION_MAX_SIZE = 20;
	CONST VA_NOTES_MIN_SIZE = 1;
	CONST VA_NOTES_MAX_SIZE = 1000;
	CONST VA_DESCR_MIN_SIZE = 1;
	CONST VA_DESCR_MAX_SIZE = 1000;
	CONST VA_GROUP_MIN_SIZE = 1;
	CONST VA_GROUP_MAX_SIZE = 50;
	CONST VA_TITLE_MIN_SIZE = 0;
	CONST VA_TITLE_MAX_SIZE = 1000;
        CONST VA_VMI_ACCELERATORS_MIN_SIZE = 0;
        CONST VA_VMI_ACCELERATORS_MAX_SIZE = 32;

	private $vappid = -1;
	private $vappversionid = -1;
	private $appid = -1;
	private $appname = "";
	private $vappversion_state=null;
	private $vappliance_service=null;
	private $isexternalrequest=false;
	public $HTTPMETHOD = null;
	
	private function validateNotes($d="", $type=""){
		if( is_string($d) ) {
			$l = strlen( trim($d) );
			if( $type === "vmi"){
				return ( $l <= RestAppVAXMLParser::VA_NOTES_MAX_SIZE );
			}
			return ( $l >= RestAppVAXMLParser::VA_NOTES_MIN_SIZE && $l <= RestAppVAXMLParser::VA_NOTES_MAX_SIZE );
		}
		return false;
	}
	private function validateVersion($d=""){
		if( is_string($d) ) {
			$l = strlen( trim($d) );
			return ( $l >= RestAppVAXMLParser::VA_VERSION_MIN_SIZE && $l <= RestAppVAXMLParser::VA_VERSION_MAX_SIZE );
		}
		return false;
	}
	private function validateAccelerators($d=""){
                if( is_numeric($d) ) {
			$l = intval( trim($d) );
			return ( $l >= RestAppVAXMLParser::VA_VMI_ACCELERATORS_MIN_SIZE && $l <= RestAppVAXMLParser::VA_VMI_ACCELERATORS_MAX_SIZE );
		}
		return false;
        }
        private function normalizePortRanges($port_ranges) {
            $ranges = explode(';', trim($port_ranges));
            $res = array();
            foreach($ranges as $range) {
                $r = explode(':', $range);
                if (count($r) === 1) {
                    $from = intval($r[0]);
                    $to = intval($r[0]);
                } else if (count($r) === 2) {
                    $from = intval($r[0]);
                    $to = intval($r[1]);
                } else {
                    return "Invalid port range format given for VMI network traffic rule";
                }

                if ($from <=0 || $to <=0 || $from  > 65535 || $to > 65535) {
                    return "Port range of a VMI network traffice rule must be a number between 1 and 65535";
                }

                if ($from > $to) {
                    return "Invalid port range values given for VMI network traffic rule. Starting port must be less or equal to ending port.";
                }

                $res[] = trim('' . $from . ':' . $to);
            }

            return $res;
        }
	private function isUsedVaVersion($appid, $d="") {
		$usedversions = VApplianceVersionState::getVapplianceUsedVersions($appid);
		return in_array(trim($d), $usedversions);
	}
	private function validateDescription($d=""){
		if( is_string($d) ) {
			$l = strlen( trim($d) );
			return ( $l >= RestAppVAXMLParser::VA_DESCR_MIN_SIZE && $l <= RestAppVAXMLParser::VA_DESCR_MAX_SIZE );
		}
		return false;
	}
	private function validateIdentifier($guid){
		//TODO: Add valid identifier regex compatible with vmcasters
		return true;
		if (preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/', $guid)) {
			return true;
		}
		return false;
	}
	private function validateUrl($d=""){
		if( strlen(trim($d)) > 0 && filter_var($d, FILTER_VALIDATE_URL)) {
			return true;
		}
		return false;
	}
	private function validateGroupname($d=""){
		if( is_string($d) ) {
			$l = strlen( trim($d) );
			return ( $l >= RestAppVAXMLParser::VA_GROUP_MIN_SIZE && $l <= RestAppVAXMLParser::VA_GROUP_MAX_SIZE );
		}
		return false;
	}
	private function validateTitle($d=""){
		if( is_string($d) ) {
			$l = strlen( trim($d) );
			return ( $l >= RestAppVAXMLParser::VA_TITLE_MIN_SIZE && $l <= RestAppVAXMLParser::VA_TITLE_MAX_SIZE );
		}
		return false;
	}
	
	public function getVApplianceService(){
		return $this->vappliance_service;
	}
	private function createVApplianceService(){
		if( $this->vappversion_state !== null ){
			$userid = null;
			if (! is_null($this->_parent)) {
				$user = $this->_parent->getUser();
				if (! is_null($user)) {
					$userid = $user->id;
				} else {
					error_log("[RestAppVAXMLParser::createVApplianceService] Error: _parent->_user is NULL");
				}
			} else {
				error_log("[RestAppVAXMLParser::createVApplianceService] Error: _parent is NULL");
			}
			$this->vappliance_service = new VApplianceService($this->vappversion_state, $userid);
		}
		return $this->vappliance_service;
	}
	private function validateStatusChange($vaversion, $newstatus){
		//todo: add valid status flow
		if( $vaversion->status === $newstatus ){
			return true;
		}elseif( $newstatus === "verify" || $newstatus === "verifypublish" ){
			return true;
		}
		if( (trim($vaversion->status) === '' || $vaversion->status === "verifing" || $vaversion->status === "verifingpublish" || $vaversion->status === "canceled" || $vaversion->status === "failed" || $vaversion->status === "verified" ) && $newstatus === "init"){ //canceling running integrity check
			return true;
		}
		return false;
	}
	//Check if identifier is used inside a VO wide image list
	private function VOGuidExists($guid){
		$vowideimagelists = new Default_Model_VOWideImageLists();
		$vowideimagelists->fitler->guid->equals($guid);
		if( count($vowideimagelists->items) > 0 ){
			$voimagelist = $vowideimagelists->items[0];
			$vo = $voimagelist->getVo();
			if( $vo ){
				return $vo->name;
			}
			return true;
		}
		$vowideimagelistimages = new Default_Model_VOWideImageListImages();
		$vowideimagelistimages->filter->guid->equals($guid);
		if( count($vowideimagelistimages->items) > 0 ){
			$vowideimagelistimage = $vowideimagelistimages->items[0];
			if( $vowideimagelistimage ){
				$vowideimagelist = $vowideimagelistimage->getVowideImageList();
				if( $vowideimagelist ){
					$vo = $vowideimagelist->getVo();
					if( $vo ){
						return $vo->name;
					}
					return true;
				}
			}
			return true;
		}
		
		return false;
	}
	private function isGroupNameUnique( $vmi, $vmiversion ){
		if( trim($vmi->groupname) === "" ) return true;
		$vappviews = new Default_Model_Vappviews();
		$f1 = new Default_Model_VappviewsFilter();
		$f2 = new Default_Model_VappviewsFilter();
		$f1->vappversionid->equals($vmiversion->id);
		$f2->vmigroupname->ilike($vmi->groupname);
		$vappviews->filter->chain($f1, 'AND');
		$vappviews->filter->chain($f2, 'AND');
		
		if( is_numeric($vmi->id) && intval($vmi->id) > 0 ){
			$f3 = new Default_Model_VappviewsFilter();
			$f3->vmiid->notequals($vmi->id);
			$vappviews->filter->chain($f3, 'AND');
		}
		if( count($vappviews->items) > 0 ){
			return false;
		}
		return true;
	}
        /**
         * Returns a VA Version expiration date as long as it is in less than a year.
         * Otherwise it returns the date 1 year from now
         *
         * @param string $param $expireson
         * @return string
         */
	private function getValidVAVersionExpirationDate($expireson) {
                $expireson = trim($expireson);
                $expireson = explode('T', $expireson);
                $expireson = trim($expireson[0]);

                if (!$expireson) {
                        return date('Y-m-d', strtotime('+1 years'));
                }

                $expiresontime = date_parse_from_format('Y-m-d', $expireson);

                if ($expiresontime['warning_count'] > 0 || $expiresontime['error_count'] > 0) {
                        return date('Y-m-d', strtotime('+1 years'));
                }

                $expiresontimeval = intval($expiresontime['year'] . '' . $expiresontime['month'] . '' . $expiresontime['day']);
                $yearfromnow = date_parse(date('Y-m-d', strtotime('+1 years')));
                $yearfromnow = intval($yearfromnow['year'] . '' . $yearfromnow['month'] . '' . $yearfromnow['day']);

                if ($expiresontimeval > $yearfromnow) {
                        return date('Y-m-d', strtotime('+1 years'));
                }

                return $expiresontime['year'] . "-" . $expiresontime['month'] . "-" . $expiresontime['day'];
        }
	private function canIncludeImageInstance($instance,$parent){
		$m = null;
		$insts = new Default_Model_VMIinstances();
		$imageid = null;
		$imageguid = null;
		$version = null;
		//Find instance
		if( !is_numeric($instance->id) || intval($instance->id) < 0 ){
			if( trim($instance->guid) !== "" ){
				$imageguid = $instance->guid;
				$insts->filter->guid->equals($instance->guid);
				if( count($insts->items) > 0 ){
					for($i=0; $i<count($insts->items); $i+=1){
						$item = $insts->items[$i];
						$version = $item->getVAVersion();
						if( $version!== null && $version->published == true && $version->archived == false ){
							$m = $item;
						}
					}
					if( $m === null ){
						$m = $insts->items[0];
					}
				}
			}
		}else{
			$imageid = $instance->id;
			$insts->filter->id->equals($instance->id);
			if( count($insts->items) > 0 ){
				$m = $insts->items[0];
			}
		}
		
		//if instance not found then it can be included
		if( $m === null ){
			return true;
		}
		if( $version === null ){
			$version = $m->getVAVersion();
		}
		//if instance does not belong to any version or belongs to current vappliance version then it can be included
		if( $version === null || $version->id === $this->vappversionid || $version->vappid == $this->vappid){
			return true;
		}
		//Check if this is an update of latest version of the same virtual appliance
		if( $version->published === true && $version->archived === false && $version->status === "verified" && $version->vappid == $this->vappid){
			return true;
		}
		
		//Output error
		if( $imageid !== null){
			return "Cannot include image instance with id: " . $imageid .". is already in use by another virtual appliance version." ;
		}elseif( $imageguid !== null ){
			return "Cannot include image instance with guid: " . $imageguid .". is already in use by another virtual appliance version." ;
		}else{
			return "Cannot include image instance that is already in use by another virtual appliance version." ;
		}
		
	}
	/* UNUSED 
	private function needsVerification(){
		$instances = new Default_Model_Vappviews();
		$instances->filter->vappversionid->equals($this->vappversionid);
		if( count($instances->items) > 0  ){
			for($i=0; $i<count($instances->items); $i+=1){
				$instance = $instances->items[$i];
				if( $instance->integrity === true ){
					return true;
				}
			}
		}else{
			return false;
		}
	}
	 */
	/* UNUSED 
	private function canPublishVersion(){
		$res = false;
		$vaversions = new Default_Model_VAversions();
		$vaversions->filter->id->equals($this->vappversionid);
		if( count($vaversions->items) > 0 ){
			$vaversion = $vaversions->items[0];
			if( $vaversion->published === false && $vaversion->enabled === true && in_array( $vaversion->status, array( "init", "verified" ) ) ){
				$res = true;
			}
		}
		return $res;
	}
	 */

	/*
	 * Helper function to set errors for the API call
	 */
	private function _setErrorMessage($msg, $type = RestErrorEnum::RE_INVALID_REPRESENTATION){
		$this->_error = $type;
		$this->_extError = $msg;
		return false;
	}
	/*
	 * Check if is PUT request
	 */
	private function isPUT(){
		return ( $this->HTTPMETHOD === RestMethodEnum::RM_PUT )?true:false;
	}
	/*
	 * Checks XML data of a given item type and retrieves any
	 * existing item. If none is found it returns a new empty item.
	 */
	public function getItem($itemtype, $xml){
		$itemtype = strtolower($itemtype);
		$xid = strval($xml->attributes()->id);
		if( !is_numeric($xid) || intval($xid) <= 0 ){
			$xid = null;
		}
		$xidentifier = null;
		if( count( $xml->xpath('./virtualization:identifier') ) > 0 ){
			$xidentifier = $xml->xpath('./virtualization:identifier');
			$xidentifier = (string) $xidentifier[0];
			if( strlen( trim( $xidentifier ) ) === 0 ){
				$xidentifier = null;
			}
		}else{
			$xidentifier = null;
		}
		
		switch($itemtype){
			case "va": //check: id, appid, appname
				if( $xid ){
					$m = new Default_Model_VAs();
					$m->filter->id->equals($xid);
					if( count($m->items) > 0 ){
						return $m->items[0];
					}
				}
				//check by application id
				$appid = strval($xml->attributes()->appid);
				if( is_numeric($appid) && intval($appid) > 0 ){
					$m = new Default_Model_VAs();
					$m->filter->appid->equals($appid);
					if( count($m->items) > 0 ){
						return $m->items[0];
					}
				}
				//check by application name
				$appname = $xml->attributes()->name;
				if( $appname && strlen( trim( $appname ) ) > 0 ){
					$m = new Default_Model_Applications();
					$m->filter->name->equals($appname);
					if( count($m->items) > 0 ){
						$app = $m->items[0];
						$m = new Default_Model_VAs();
						$m->filter->appid->equals($app->id);
						if( count($m->items) > 0 ){
							return $m->itemsvmi[0];
						}
					}
				}
				$m = new Default_Model_VA();
				if( is_numeric($appid) && intval($appid) > 0 ){
					$m->appid = $appid;
				}
				return $m;
				//virtualization:appliance/
			case "vaversion":  //check: id, identifier
				if( $xid ){
					$m = new Default_Model_VAversions();
					$m->filter->id->equals($xid);
					if( count($m->items) > 0 ){
						return $m->items[0];
					}
				}
				if( $xidentifier ){
					$m = new Default_Model_VAversions();
					$m->filter->guid->equals($xidentifier)->and($m->filter->status->notequals("deleted")->and($m->filter->archived->equals(false)));
					if( count($m->items) > 0 ){
						return $m->items[0];
					}
				}elseif( $this->vappid > -1 ){
					$vas = new Default_Model_VAs();
					$vas->filter->id->equals($this->vappid);
					if( count($vas->items) > 0 ){
						$va = $vas->items[0];
						$m = new Default_Model_VAversion();
						$m->guid = $va->guid;
						return $m;
					}
				}
				return new Default_Model_VAversion();
				//virtualization:appliance/virtualization:instance/
			case "vmi": //check: id, identifier
				if( $xid ){
					$m = new Default_Model_VMIs();
					$m->filter->id->equals($xid);
					if( count($m->items) > 0 ){
						return $m->items[0];
					}
					
				}
				return new Default_Model_VMI();
				//virtualization:appliance/virtualization:instance/virtualization:image
			case "vmiinstance"://check: id, identifier
				if( $xid ){
					$m = new Default_Model_VMIinstances();
					$m->filter->id->equals($xid);
					if( count($m->items) > 0 ){
						return $m->items[0];
					}
				}
				return new Default_Model_VMIinstance();
				//virtualization:appliance/virtualization:instance/virtualization:image/virtualization:instance
			case "vmiflavour"://check: flavourid
				//FIND FLAVOUR DATA MATCHES
				$id = strval($xml->attributes()->flavourid);
				if( is_numeric($id) && intval($id) > 0 ){
					$m = new Default_Model_VMIflavourBase();
					$m->filter->id->equals($id);
					if( count($m->items) > 0 ){
						return $m->items[0];
					}
				}
				return new Default_Model_VMIflavourBase();
				//virtualization:appliance/virtualization:instance/virtualization:image/virtualization:instance
			case "arch": //check: id
				$xp = $xml->xpath('./virtualization:arch');
				if( count($xp) > 0 ){
					$xp = $xp[0];
					$id = strval($xp->attributes()->id);
					$val = trim(strval($xp));
					$m = new Default_Model_Archs();
					if( is_numeric($id) && intval($id) > 0 ){
						$m->filter->id->equals($id);
					}elseif( $val !== ""){
						$m->filter->name->ilike($val);
					}
					if( count($m->items) > 0 ){
						return $m->items[0];
					}
				}
				return new Default_Model_Arch();
				//virtualization:appliance/virtualization:instance/virtualization:image/virtualization:instance/arch
			case "os"://check: id
				$xp = $xml->xpath('./virtualization:os');
				if( count($xp) > 0 ){
					$xp = $xp[0];
					$id = strval($xp->attributes()->id);
					$val = trim(strval($xp));
					$m = new Default_Model_OSes();
					if( is_numeric($id) && intval($id) > 0 ){
						$m->filter->id->equals($id);
					}elseif($val !== ""){
						$m->filter->name->ilike($val);
					}
					if( count($m->items) > 0 ){
						return $m->items[0];
					}
				}
				return new Default_Model_OS();
				//virtualization:appliance/virtualization:instance/virtualization:image/virtualization:instance/os
			case "osversion":
				$xp = $xml->xpath('./virtualization:os');
				if( count($xp) > 0 ){
					$xp = $xp[0];
					$osver = strval($xp->attributes()->version);
					if( $osver && strlen( trim ($osver) ) > 0 ){
						return $osver;
					}
				}
				return "";
				//virtualization:appliance/virtualization:instance/virtualization:image/virtualization:instance/os/@osverion
			case "hypervisor"://check: id
				$xp = $xml->xpath('./virtualization:hypervisor');
				if( count($xp) > 0 ){
					$xp = $xp[0];
					$id = strval($xp->attributes()->id);
					$val = trim(strval($xp));
					$m = new Default_Model_Hypervisors();
					if( is_numeric($id) && intval($id) > 0 ){
						$m->filter->id->equals($id);
					}elseif($val !== ""){
						$val = trim(str_replace(",", "-", $val));
						$m->filter->name->ilike($val);
					}
					if( count($m->items) > 0 ){
						return $m->items[0];
					}
				}
				return new Default_Model_Hypervisor();
				//virtualization:appliance/virtualization:instance/virtualization:image/virtualization:instance/format
			case "addedby":
				$u = $this->_parent->getUser();
				if( $u ){
					return $u;
				}
				return null;
				//virtualization:appliance/virtualization:instance/virtualization:image/virtualization:instance/addedby/@id			
		}
		return false;
	}
	/*
	 * Deletes any existing relations f VAVersion with VMIInstances
	 * and replaces it with the new ones given as $vmiinstances parameter
	 */
	private function setupVAppList($vappversion, $vmiinstances = array()){
		$versionid = $vappversion->id;
		$instids = array();
		//Get stored vmi instances in vapplists for specific version
		$vapplists = new Default_Model_VALists();
		$vapplists->filter->vappversionid->equals($versionid);
		if( count($vapplists->items) > 0 ){
			for( $i=0; $i<count($vapplists->items); $i+=1 ){
				$inst = $vapplists->items[$i];
				$instids[] = $inst->vmiinstanceid;
			}
		}
		//store new vmi instances in vapplists
		for( $i=0; $i<count($vmiinstances); $i+=1 ){
			$item = $vmiinstances[$i];
			if( in_array($item->id, $instids) === false ){
				$listitem = new Default_Model_VAList();
				$listitem->vappversionid = $versionid;
				$listitem->vmiinstanceid = $item->id;
				$listitem->save();
			}
		}
	}
	/*
	 * Checks if there is another virtual appliance image instance 
	 * with the same image file url under the same VMI.
	 */
	private function VMIInstanceUrlExists($instance, $image){
		$flavour = $instance->getFlavour();
		$hypervisors =  $flavour->getHypervisors();
		if(is_string($hypervisors) ){
			$hypervisors = pg_to_php_array($hypervisors);
		}
		$vmi = $flavour->getVmi();
		$url = strtolower( trim($instance->uri) );
		$instid = $instance->id;
		if( $vmi ){
			$instances = $vmi->getVMIInstances();
			for( $i=0; $i<count($instances); $i+=1 ){
				$item = $instances[$i];
				if( $item->id == $instid ) continue;
				if( strtolower( trim($item->uri) ) == $url ){
					$itemflavour = $item->getFlavour();
					$itemhypers = $itemflavour->getHypervisors();
					if( is_string($itemhypers) ){
						$itemhypers = pg_to_php_array($itemhypers);
					}
					$itemhypers = implode(",",$itemhypers);
					$hypers = "";
					if( is_array($hypervisors) ){
						$hypers = implode(",", $hypervisors);
					}else{
						$hypers = $hypervisors;
					}
					if( trim($hypers) === trim($itemhypers) ){
						return true;
					}
				}
			}
		}
		return false;
	}
	private function versionHasUniqueUrls(){
		$collect = array();
		$vapplists = new Default_Model_VALists();
		$vapplists->filter->vappversionid->equals($this->vappversionid);
		if( count($vapplists->items) === 0 ) return true;
		for($i=0; $i<count($vapplists->items); $i+=1){
			$vapplist = $vapplists->items[$i];
			$inst = $vapplist->getVMIinstance();
			$flav = $inst->getFlavour();
			$hyper = $flav->getHypervisors();
			if( is_string($hyper) ){
				$hyper = pg_to_php_array($hyper);
			}
			$hypers = implode("$", $hyper);
			$collect[$i] = "".trim($inst->uri) .":::" . trim($hypers);
		}
		$doubles = array_count_values($collect);
		foreach($doubles as $k=>$v){
			if( intval($v) > 1 ){
				return false;
			}
		}
		return true;
	}
	/*
	 * Checks if there is another VMI with the same groupname(group) 
	 * value under the same virtual appliance
	 */
	/* UNUSED
	private function VMIExists($vmi){
		$vmiid = $vmi->id;
		$vmidescr = strtolower( trim($vmi->groupname) );
		$vmis = new Default_Model_VMIs();
		$vmis->filter->vappid->equals($vmi->vappid);
		if( count($vmis->items) > 0 ){
			for( $i=0; $i<count($vmis->items); $i+=1 ){
				$item = $vmis->items[$i];
				if( $vmiid == $item->id ){
					continue;
				}
				if( strtolower(trim($item->groupname)) == $vmidescr ){
					return true;
				}
			}
		}
		return false;
	}
	 */

	/*
	 * Checks if there is another VaVersion item with the same 
	 * version value under the same Virtual Appliance.
	 */
	private function getCurrentVAVersion() {
		if( intval($this->vappversionid) > 0){
			$vaversions = new Default_Model_VAversions();
			$vaversions->filter->id->numequals($this->vappversionid);
			if( count($vaversions->items) > 0 ) {
				return $vaversions->items[0];
			}
		}
		return null;
	}
	/* UNUSED
	private function VAVersionExists($vaversion){
		$vaversionid = $vaversion->id;
		$vaversionver = strtolower( trim($vaversion->version) );
		$vappid = $vaversion->vappid;
		$vaversions = new Default_Model_VAversions();
		$vaversions->filter->vappid->equals($vappid);
		if( count($vaversions->items) > 0 ){
			for( $i=0; $i<count($vaversions->items); $i+=1 ){
				$item = $vaversions->items[$i];
				if( $vaversionid == $item->id ){
					continue;
				}
				if( strtolower($item->version) === $vaversionver ){
					return true;
				}
			}
		}
		return false;
	}
	 */
	private function versionHasImages($version = null){
		if( $version == null ){
			return false;
		}
		$vapplists = new Default_Model_VALists();
		$vapplists->filter->vappversionid->equals($version->id);
		if( count($vapplists->items) === 0 ){
			return false;
		}
		return true;
	}
	private function deleteVMIs($ids){
		$vmis = new Default_Model_VMIs();
		$vmis->filter->id->in($ids);
		if( count($vmis->items) > 0 ){
			for( $i=0; $i<count($vmis->items); $i+=1 ){
				$item = $vmis->items[$i];
				$item->delete();
			}
		}
	}

	/* UNUSED	
	private function getPublishedVersion(){
		$versions = new Default_Model_VAversions();
		$versions->filter->vappid->equals($this->vappid);
		if( count($versions->items) > 0 ){
			for( $i=0; $i<count($versions->items); $i+=1 ) {
				$item = $versions->items[$i];
				if( $item->published == true && $item->archived == false && $item->status != "init" ){
					return $item;
				}
			}
		}
		return null;
	}
	 */

	private function getWorkingVersion(){
		$versions = new Default_Model_VAversions();
		$versions->filter->vappid->equals($this->vappid);
		if( count($versions->items) > 0 ){
			for( $i=0; $i<count($versions->items); $i+=1 ) {
				$item = $versions->items[$i];
				if( $item->published == false && $item->archived == false && $item->enabled == true && $item->status !== "deleted" ){
					return $item;
				}
			}
		}
		return null;
	}
	/*
	 * Checks the XML data to retreive an identical flavour item.
	 * If none is found it crteates and returns a new flavour 
	 * filled with the XML data.
	 * !!!Flavour->Id is ignored!!!
	 */
	private function createVersionState($version){
		$old = new Default_Model_VAversion();
		$old->id = $version->id;
		$old->version = $version->version;
		$old->guid = $version->guid;
		$old->notes = $version->notes;
		$old->vappid = $version->vappid;
		if(!is_numeric($version->id) || intval($version->id)<=0) {
			$old->published = false;
			$old->enabled = true;
			$old->archived = false;
			$old->status = 'init';	
		}else{
			$old->published = $version->published;
			$old->enabled = $version->enabled;
			$old->archived = $version->archived;
			$old->status = $version->status;	
		}
		$this->vappversion_state = new VApplianceVersionState($old);
	}
	private function getContextScriptById($id){
		$scripts = new Default_Model_ContextScripts();
		$scripts->filter->id->numequals(intval($id));
		if( count($scripts->items) > 0 ){
			return $scripts->items[0];
		}
		return null;
	}
	private function findContextScriptByUrl($url){
		$scripts = new Default_Model_ContextScripts();
		$scripts->filter->url->equals(trim($url));
		if( count($scripts->items) > 0 ){
			return $scripts->items[0];
		}
		return null;
	}
	private function createContextScript($contextscriptxml){
		//create new context script
		$contextscript = new Default_Model_ContextScript();

		if( $contextscriptxml->xpath("./virtualization:url") > 0 ){
			$csp = $contextscriptxml->xpath("./virtualization:url");
			$csp = trim( strval( $csp[0] ) );
			$contextscript->url = $csp;
		}

		if( !$contextscript->url || trim($contextscript->url) === "" ){
			return "Url is mandatory for contextualization scripts";
		}

		if( $contextscriptxml->xpath("./virtualization:checksum") > 0 ){
			$csp = $contextscriptxml->xpath("./virtualization:checksum");
			$csp = $csp[0];
			$hashtype = trim( strval($csp->attributes()->hashtype) );
			if( $hashtype === "" ){
				$hashtype = "md5";
			}
			$contextscript->checksumfunc = $hashtype;
			$csp = trim( strval($csp) );
			$contextscript->checksum = $csp;
		}

		if( trim($contextscript->checksum) === "" ){
			return "Cannot create contextscript without checksum";
		}

		if( $contextscriptxml->xpath("./virtualization:format") > 0 ){
			$csp = $contextscriptxml->xpath("./virtualization:format");
			$csp = $csp[0];
			$csp = trim( strval( $csp->attributes()->id ) );
			if( $csp === "" || (is_numeric($csp) && intval($csp)<=0)){
				$csp = "1";
			}
			$contextscript->formatid = intval($csp);
		}

		if( $contextscriptxml->xpath("./virtualization:name") > 0 ){
			$csp = $contextscriptxml->xpath("./virtualization:name");
			$csp = trim( strval( $csp[0] ) );
			$contextscript->name = $csp;
		}else{
			$contextscript->name = "";
		}

		if( $contextscriptxml->xpath("./virtualization:title") > 0 ){
			$csp = $contextscriptxml->xpath("./virtualization:title");
			$csp = trim( strval( $csp[0] ) );
			$contextscript->title = $csp;
		}
		
		if( $contextscriptxml->xpath("./virtualization:size") > 0 ){
			$csp = $contextscriptxml->xpath("./virtualization:size");
			$csp = trim( strval( $csp[0] ) );
			$contextscript->size = $csp;
		}
		
		try{
			$contextscript->save();
		} catch (Exception $ex) {
			return $ex->getMessage();
		}

		return $contextscript;
	}
	private function deleteContextScriptRelation($vmiinstanceid){
		if( $vmiinstanceid ){
			$scriptids = array();
			$vmiscripts = new Default_Model_VMIinstanceContextScripts();
			$vmiscripts->filter->vmiinstanceid->numequals($vmiinstanceid);
			if( count($vmiscripts->items) > 0 ){
				foreach($vmiscripts->items as $item){
					$scriptids[] = $item->contextscriptid;
					$vmiscripts->remove($item);
				}
			}
			$scriptids = array_unique($scriptids);
			//check if the referenced scripts have relations
			//if no relation found remove them from db.
			foreach($scriptids as $id){
				$vmiscripts = new Default_Model_VMIinstanceContextScripts();
				$vmiscripts->filter->contextscriptid->numequals($id);
				if( count($vmiscripts->items) === 0 ){
					$scripts = new Default_Model_ContextScripts();
					$scripts->filter->id->numequals($id);
					if( count($scripts->items) > 0 ){
						VapplianceStorage::remove($scripts->items[0], $vmiinstanceid);
						$scripts->remove($scripts->items[0]);
						
					}
				}
			}
		}
	}
	private function syncContextScript($contextscriptxml, $vmiinstance){
		if( $contextscriptxml === null || !$vmiinstance){
			return true;
		}
		
		$contextscript = null;
		$url = $contextscriptxml->xpath("./virtualization:url");
		if( count($url) > 0 ){
			$url = trim( strval( $url[0] ) );
		}else{
			$url = "";
		}
		$id = trim( strval($contextscriptxml->attributes()->id) );
		if( $id === "" || (is_numeric($id) && intval($id) <=0) ){
			if( $url !== "" ){
				$contextscript = $this->findContextScriptByUrl($url);
			}
			if( $contextscript === null ){
				$contextscript = $this->createContextScript($contextscriptxml);
				if( $contextscript !== false && is_string($contextscript) === false )
				{
					VapplianceStorage::store($contextscript, $vmiinstance->id, $contextscript->addedbyid);
				}
			}
		} elseif( is_numeric($id) && intval($id) > 0 ){
			$contextscript = $this->getContextScriptById($id);
		}
		
		if( is_string($contextscript) ){
			return $contextscript;
		}
		
		try{
			//Clear existing relations to context scripts for this vmi instance
			$vmiinstacnescripts = new Default_Model_VMIinstanceContextScripts();
			$vmiinstacnescripts->filter->vmiinstanceid = $vmiinstance->id;
			if( count($vmiinstacnescripts->items) > 0 ){
				foreach($vmiinstacnescripts->items as $item){
					$vmiinstacnescripts->remove($item);
				}
			}

			//create new relation to context scripts for this vmi instance
			$user = $this->_parent->getUser();
			$vmiinstancecontextscript = new Default_Model_VMIinstanceContextScript();
			$vmiinstancecontextscript->vmiinstanceid = $vmiinstance->id;
			$vmiinstancecontextscript->contextscriptid = $contextscript->id;
			if( $user ){
				$vmiinstancecontextscript->addedbyid = $user->id;
			}
			
			$vmiinstancecontextscript->save();
		}catch(Exception $ex){
			return $ex->getMessage();
		}
		return true;
	}
	private function getOsInformation($xml){
		$osversion = $this->getItem("osversion", $xml);
		$osversion = ( (trim($osversion) === "")?null:trim($osversion) );
		$os = null;
		$osfamily = null;
		//Get OS (name or id) from xml
		$xp = $xml->xpath('./virtualization:os');
		if( count($xp) > 0 ){
			$xp = $xp[0];
			$osid = trim( strval($xp->attributes()->id) );
			$osval = trim(strval($xp));
			$os = ( ($osid === "")?$osval:$osid );
			$os = ( ($os === "")?null:$os );
			$osfamily = trim( strval($xp->attributes()->familyid) );
			$osfamily = ( ($osfamily === "")?null:$osfamily );
		}
		$info = VMCasterOsSelector::getOsInfo($osfamily, $os, $osversion);
		return $info;
	}
	private function parseVAppFlavour($xml, $parent = null){
		$hypervisor = $this->getItem("hypervisor", $xml);
		if( !is_numeric($hypervisor->id) || intval($hypervisor->id) <0 ){
			return $this->_setErrorMessage('Invalid hypervisor for VMI Instance.');
		}
		
		//Retrieve OS information for given data. In case of external 
		//request os data normalization take effect.
		$osinfo = $this->getOsInformation($xml);
		if( is_string($osinfo) === true ){
			//An error returned 
			return $this->_setErrorMessage($osinfo);
		} elseif( $osinfo === false || $osinfo === null || !$osinfo ){
			//Something went wrong. Unhandled error.
			return $this->_setErrorMessage('Invalid OS information given for VMI Instance.');
		} elseif( is_array($osinfo) === true ){
			//We can ignore the os family in osinfo. It will always be relative to the os.
			if( isset($osinfo["os"])===false || $osinfo["os"] === null ){
				return $this->_setErrorMessage('Could not identify os for VMI Instance.');
			}else{
				$os = $osinfo["os"];
			}
			if( isset($osinfo["osversion"])===false || $osinfo["osversion"] === null || trim($osinfo["osversion"]) === ""){
				return $this->_setErrorMessage('Invalid os version for VMI Instance.');
			}else{
				$osversion = $osinfo["osversion"];
			}
		} else {
			//If nothing of the above, fail back to old OS ifnormation retrieval
			$osversion = $this->getItem("osversion", $xml);
			if( strlen( trim( $osversion ) ) == 0 ){
				return $this->_setErrorMessage('Invalid OS version for VMI Instance.');
			}
			$os = $this->getItem("os", $xml);
			if( !is_numeric($os->id) || intval($os->id) <=0 ){
				//in case of external request
				//and unkown os set by default linux
				$os = new Default_Model_OSes();
				$os->filter->name->ilike("Linux");
				if(count($os->items) > 0 ){
					$os = $os->items[0];
				}else{
					return $this->_setErrorMessage('Invalid operating system for VMI Instance.');
				}
			}
		}
		
		$arch = $this->getItem("arch", $xml);
		if( !is_numeric($arch->id) || intval($arch->id) <=0 ){
			return $this->_setErrorMessage('Invalid architecture for VMI Instance.');
		}
		if( count($xml->xpath('./virtualization:format')) > 0 ){
			$format = $xml->xpath('./virtualization:format');
			$format = strval( $format[0] );
			if( trim($format) === "" ){
				return $this->_setErrorMessage('Invalid file format for VMI Instance.');
			}
		}
		
		//Get existing flavour
		$flavours = new Default_Model_VMIflavoursBase();
		$f = $flavours->filter;
		$f->vmiid->equals($parent->id)->and($f->archid->equals($arch->id)->and($f->osid->equals($os->id)->and($f->format->equals($format)->and($f->hypervisors->equals('{'.$hypervisor->name.'}')->and($f->osversion->equals($osversion))))));
		if( count($flavours->items) > 0 ){
			$flavour = $flavours->items[0];
		} else { //Create new flavour
			$flavour = new Default_Model_VMIflavourBase();
			$flavour->vmiid = $parent->id;
			$flavour->hypervisors = '{'.$hypervisor->name.'}';
			$flavour->archid = $arch->id;
			$flavour->osid = $os->id;
			$flavour->osversion = $osversion;
			$flavour->format = $format;
			$flavour->save();
			if( !is_numeric($flavour->id) || intval($flavour->id) <= 0 ){
				return $this->_setErrorMessage("Could not save VMI Instance flavour.");
			}
		}
		return $flavour;
	}
	/*
	 * Creates or updates a VMIInstance item 
	 * based on the given XML data.
	 * Returns a Default_Model_VMIinstance item.
	 */
	private function parseVAppImageInstance($xml, $parent = null){
		$deferredNetTraf = array();
		$deferredCFs = array();
		$isupdated = false;
		$contextscript = null;
		$flavour = $this->parseVAppFlavour($xml, $parent);
		if( $flavour === false ){
			return false;
		}
		$m = $this->getItem("vmiinstance", $xml);
		
		$version = strval($xml->attributes()->version);
		if( Supports::singleVMIPolicy() ) {
			//IN case of single VMI policy 
			//set VAVersion version as VMI version 
			//and VAVersion description as VMI description
			$wver = $this->getCurrentVAVersion();
			if( $wver !== NULL ) {
				$m->version = $wver->version;
				$m->description = $wver->notes;
			}
		} elseif( $this->validateVersion($version) === false ){
			return $this->_setErrorMessage("Invalid version value for VMI Instance.");
		}else{
			$m->version = $version;
		}
		
		if( !$m->guid ){
			if( count( $xml->xpath('./virtualization:identifier') ) >0 ){
				$tmpguid = $xml->xpath('./virtualization:identifier');
				$tmpguid = strval($tmpguid[0]);
				if(strlen(trim($tmpguid)) > 0 ){
					$m->guid = $tmpguid;
				}
				if( $this->validateIdentifier($m->guid) === false ){
					return $this->_setErrorMessage("Invalid identifier value for VMI Instance");
				}
			}
		}
		
		if( intval($m->vmiflavourid) !== intval($flavour->id) ){
			$isupdated = true;
		}
		$m->vmiflavourid = $flavour->id;
		//Check if existing image instance can be included in 
		//specific vmi. (belongs in the same version, vappliance etc)
		$caninclude = $this->canIncludeImageInstance($m,$parent);
		if( $caninclude !== true ){
			return $this->_setErrorMessage($caninclude);
		}
		//check if urls are in valid format
		if( count( $xml->xpath('./virtualization:url') ) > 0 ){
			$xuri = $xml->xpath('./virtualization:url');
			$xuri = strval($xuri[0]); 
			if( trim($m->uri) !== trim($xuri) ){
				$m->integrityStatus = "";
				$m->integrityMessage = "";
			} 
			if( trim($m->uri) !== "" && trim($m->uri) != trim($xuri) ){
				$isupdated = true;
				debug_log("last updated  url");
			}
			$m->uri = $xuri;
			if( $this->validateUrl($m->uri) === false ){
				return $this->_setErrorMessage("Invalid URL value for VMI Instance.");
			}
		}
		if( count( $xml->xpath('./virtualization:cores') ) > 0 ){
			$cores = $xml->xpath('./virtualization:cores');
			$cores = $cores[0];
			if( strlen( trim( strval($cores->attributes()->minimum) ) ) > 0 ){
				$coresmin = strval($cores->attributes()->minimum);
				if( is_numeric($coresmin) && intval($coresmin) >= 0 ){
					if( trim($m->coreminimum) !== "" && intval($m->coreminimum) != intval($coresmin) ){
						$isupdated = true;
						debug_log("last updated  core min");
					}
					$m->coreminimum = intval($coresmin);
				}else{
					return $this->_setErrorMessage("Minimum cores value must be a positive number");
				}
			}
			if( strlen( trim( strval($cores->attributes()->recommended) ) ) > 0 ){
				$coresrecom = strval($cores->attributes()->recommended);
				if( is_numeric($coresrecom) && intval($coresrecom) >= 0 ){
					if( trim($m->coreRecommend) !== "" && intval($m->coreRecommend) != intval($coresrecom) ){
						$isupdated = true;
						debug_log("last updated  core recom");
					}
					$m->coreRecommend = intval($coresrecom);
				}else{
					return $this->_setErrorMessage("Recommended cores value must be a positive number");
				}
			}
		}
		if (count( $xml->xpath('./virtualization:contextformat') ) == 0) {
			debug_log("[RestAppVAXMLParser::parseVAppImageInstance] No <virtualization:contextformat> given in put/post, defaulting to Cloud-Init");
			// default to Cloud-Init if unspecified
			$CFXMLSTUB = '<virtualization:contextformat id="1" name="Cloud-Init" supported="true" />';
			$CFXMLSTUB = strval(RestAPIHelper::wrapResponse($CFXMLSTUB));
			$cfxml=new SimpleXMLElement($CFXMLSTUB);
			$cformats = $cfxml->xpath('./virtualization:contextformat');
		} elseif (count( $xml->xpath('./virtualization:contextformat') ) > 0) {
			$cformats = $xml->xpath('./virtualization:contextformat');
		}
		if (count($cformats) > 0) {
			if ( (count($cformats) === 1) && ($cformats[0]->attributes(RestAPIHelper::XMLNS_XSI())->nil) && (strval($cformats[0]->attributes(RestAPIHelper::XMLNS_XSI())->nil) == "true") ) {
				$cfs = new Default_Model_VMISupportedContextFormats();
				foreach ($m->ContextFormats as $cformat) {
					$cfs->remove($cformat);
				}
			} else {
				foreach ($cformats as $cformat) {
					$supported = strval($cformat->attributes()->supported);
					$fmtid = strval($cformat->attributes()->id);
					if (($supported = "true") && ($fmtid != "")) {
						if (! is_numeric($fmtid)) {
							return $this->_setErrorMessage("Invalid id attribute \`$fmtid' for supported context format element \`virtualization:contextformat'");
						}
						$cf = new Default_Model_VMISupportedContextFormat();
						$cf->vmiinstanceID = $m->id;
						$cf->fmtid = strval($cformat->attributes()->id);
						try {
							// if this is an update (we have a VMI instance id),save network traffic now, or else defer it for after saving the VMI instance
							if( trim($m->id) == "" || trim($m->id)==="-1"  ) {
								$deferredCFs[] = $cf;
							} else {
								$cf->save();
							}
						} catch (Exception $e) {
							error_log($e);
							return $this->_setErrorMessage("Invalid id attribute \`$fmtid' for supported context format element \`virtualization:contextformat'", RestErrorEnum::RE_BACKEND_ERROR);
						}
					}
				}
			}
		}
		if (count( $xml->xpath('./virtualization:accelerators') ) > 0) {
			$accelerators = $xml->xpath('./virtualization:accelerators');
			if ( (count($accelerators) === 1) && ($accelerators[0]->attributes(RestAPIHelper::XMLNS_XSI())->nil) && (strval($accelerators[0]->attributes(RestAPIHelper::XMLNS_XSI())->nil) == "true") ) {
				$m->deleteAccel();
			}  elseif (count($accelerators) > 1) {
				return $this->_setErrorMessage("Cardinality error for \`virtualization::accelerator' element");
			} else {
				$accelerators = $accelerators[0];
				if( strlen( trim( strval($accelerators->attributes()->minimum) ) ) > 0 ) {
                                        $acceleratorsmin_error_messsage = "Minimum accelerators value must be a positive number between " . RestAppVAXMLParser::VA_VMI_ACCELERATORS_MIN_SIZE . " and " . RestAppVAXMLParser::VA_VMI_ACCELERATORS_MAX_SIZE;
					$acceleratorsmin = strval($accelerators->attributes()->minimum);
					if( is_numeric($acceleratorsmin) && intval($acceleratorsmin) >= 0 ){
                                                if ($this->validateAccelerators($acceleratorsmin) === false) {
                                                    return $this->_setErrorMessage($acceleratorsmin_error_messsage);
                                                }
						if( trim($m->accelMinimum) !== "" && intval($m->accelMinimum) != intval($acceleratorsmin) ){
							$isupdated = true;
							debug_log("last updated  accel min");
						}
						$m->accelMinimum = intval($acceleratorsmin);
					}else{
						return $this->_setErrorMessage($acceleratorsmin_error_messsage);
					}
				}
				if( strlen( trim( strval($accelerators->attributes()->recommended) ) ) > 0 ){
                                        $acceleratorsrecom_error_message = "Recommended accelerators value must be a positive number between " . RestAppVAXMLParser::VA_VMI_ACCELERATORS_MIN_SIZE . " and " . RestAppVAXMLParser::VA_VMI_ACCELERATORS_MAX_SIZE;
					$acceleratorsrecom = strval($accelerators->attributes()->recommended);
					if( is_numeric($acceleratorsrecom) && intval($acceleratorsrecom) >= 0 ){
                                                if ($this->validateAccelerators($acceleratorsrecom) === false) {
                                                    return $this->_setErrorMessage($acceleratorsrecom_error_message);
                                                }
						if( trim($m->accelRecommend) !== "" && intval($m->accelRecommend) != intval($acceleratorsrecom) ){
							$isupdated = true;
							debug_log("last updated  accel recom");
						}
						$m->accelRecommend = intval($acceleratorsrecom);
					}else{
						return $this->_setErrorMessage($acceleratorsrecom_error_message);
					}
				}
				if( strlen( trim( strval($accelerators->attributes()->type) ) ) > 0 ){
					$validAccels = array();
					db()->setFetchMode(Zend_Db::FETCH_NUM);
					$validAccelRS = db()->query("SELECT value::text FROM accelerators")->fetchAll();
					foreach ($validAccelRS as $validAccel) {
						$validAccels[] = $validAccel[0];
					}
					$acceleratorstype = strval($accelerators->attributes()->type);
					if( in_array($acceleratorstype, $validAccels) ){
						if( (trim($m->accelType) !== "") && ($m->accelType != $acceleratorstype) ){
							$isupdated = true;
							debug_log("last updated  accel type");
						}
						$m->accelType = $acceleratorstype;
					}else{
						return $this->_setErrorMessage("\`type' attribute of \`virtualization::accelerator' must be one of: \`" . implode(", ", $validAccels) . "'");
					}
				}
			}
		}
		if ( count($xml->xpath('./virtualization:network_traffic[@xsi:nil="true"]')) != 0 ) {
			$m->deleteNetworkTraffic();
		} elseif ( count( $xml->xpath('./virtualization:network_traffic') ) > 0 ) {
			$m->deleteNetworkTraffic();
			$nts = $xml->xpath('./virtualization:network_traffic');
			foreach ($nts as $nt) {
				$mnt = new Default_Model_VMINetworkTrafficEntry();
				$mnt->VMIinstanceID = $m->id;
				if (strlen(trim(strval($nt->attributes()->protocols))) > 0) {
					try {
						$mnt->netProtocols = trim(strval($nt->attributes()->protocols));
					} catch (Exception $e) {
						error_log($e->getMessage());
						return $this->_setErrorMessage($e->getMessage());
					}
				}
				if (strlen(trim(strval($nt->attributes()->direction))) > 0) {
					try {		
						$mnt->flow = trim(strval($nt->attributes()->direction));
					} catch (Exception $e) {
						error_log($e->getMessage());
						return $this->_setErrorMessage($e->getMessage());
					}
				} else {
					return $this->_setErrorMessage("Required entity virtualization:network_traffic@direction is missing");
				}
				if (strlen(trim(strval($nt->attributes()->ip_range))) > 0) {
					$mnt->ipRange = trim(strval($nt->attributes()->ip_range));
				} else {
                                        //Set default IP range if none given
                                        $mnt->ipRange = "0.0.0.0/0";
                                }
                                //If protocol is not ICMP check for port ranges
                                if (strtoupper(trim(implode('', $mnt->netProtocols))) !== 'ICMP') {
                                        $normalizedPortRanges = array();
                                        if (strlen(trim(strval($nt->attributes()->port_range))) > 0) {
                                                //Validate and normalize port ranges
                                                $normalizedPortRanges = $this->normalizePortRanges(trim(strval($nt->attributes()->port_range)));
                                        }
                                        //If validation did not return an array of normalized port ranges
                                        //it means an error occured.
                                        if (is_string($normalizedPortRanges)) {
                                                return $this->_setErrorMessage($mnt->ports);
                                        } else if (is_array($normalizedPortRanges)) {
                                                //Check if any valid port range is given
                                                if (count($normalizedPortRanges) === 0) {
                                                   return $this->_setErrorMessage('Port ranges must be provided for ' . implode(' ', $mnt->netProtocols) . ' in VMI network traffic rule');
                                                }
                                                //Port ranges are valid
                                                $mnt->ports = implode(';', $normalizedPortRanges);
                                        } else {
                                                //Unhandled validation error
                                                return $this->_setErrorMessage('Invalid port ranges given for VMI network traffic rule');
                                        }
                                }
				// if this is an update (we have a VMI instance id),save network traffic now, or else defer it for after saving the VMI instance
				if( !is_numeric($m->id) || intval($m->id) <=0 ){ /*new instance*/
                                    $deferredNetTraf[] = $mnt;
				} else {
                                    $mnt->save();
				}
			}
		}
		if( count( $xml->xpath('./virtualization:ram') ) > 0 ){
			$ram = $xml->xpath('./virtualization:ram');
			$ram = $ram[0];
			if( strlen( trim( strval($ram->attributes()->minimum) ) ) > 0 ){
				$rammin = strval($ram->attributes()->minimum);
				if( is_numeric($rammin) && intval($rammin) >= 0 ){
					if( trim($m->RAMminimum) !== "0" && intval($m->RAMminimum) != intval($rammin) ){
						$isupdated = true;
					}
					$m->RAMminimum = intval($rammin);
				}else{
					return $this->_setErrorMessage("Minimum RAM value must be a positive number");
				}
			}
			if( strlen( trim( strval($ram->attributes()->recommended) ) ) > 0 ){
				$ramrecom = strval($ram->attributes()->recommended);
				if( is_numeric($ramrecom) && intval($ramrecom) >= 0 ){
					if( trim($m->RAMrecommend) !== "" && intval($m->RAMrecommend) != intval($ramrecom) ){
						$isupdated = true;
					}
					$m->RAMrecommend = intval($ramrecom);
				}else{
					return $this->_setErrorMessage("Recommended RAM value must be a positive number");
				}
			}
		}
		if( count( $xml->xpath('./virtualization:title') ) > 0 ){
			$title = $xml->xpath('./virtualization:title');
			$title = (string) $title[0];
			$title = strip_tags($title);
			if( strlen( trim( $title ) )  > 0 ){
				if($this->validateTitle($title) === false ){
					return $this->_setErrorMessage("Invalid title value for VMI Instance.");
				}else{
					if( trim($m->title) != trim($title) ){
						$isupdated = true;
					}
					$m->title = trim($title);
				}
			}
		}else{
			$m->title = "";
		}
		if( Supports::singleVMIPolicy() === false && count( $xml->xpath('./virtualization:description') ) > 0 ){
			$description = $xml->xpath('./virtualization:description');
			$description = (string) $description[0];
			$description = strip_tags($description);
			if( strlen( trim( $description ) )  > 0 ){
				 if( $this->validateNotes($description) === false ){
					return $this->_setErrorMessage("Invalid description value for VMI Instance.");
				 }
			}
			if( trim($m->description) !== trim($description) ){
				$isupdated = true;
			}
			$m->description = $description;
		}
		if( count( $xml->xpath('./virtualization:notes') ) > 0 ){
			$notes = $xml->xpath('./virtualization:notes');
			$notes = (string) $notes[0];
			$notes = strip_tags($notes);
			if( strlen( trim( $notes ) )  > 0 ){
				if( $this->validateNotes($notes) === false ){
					return $this->_setErrorMessage("Invalid comments value for VMI Instance.");
				}
			}
			if( trim($m->notes) !== trim($notes) ){
				$isupdated = true;
			}
			$m->notes = $notes;
		}
		
		
		//Check if user disabled image file integrity checks.
		$checkintegrity = true;
		if( strlen( trim( strval($xml->attributes()->integrity) ) ) > 0 ){
			if( strval($xml->attributes()->integrity) == "false" ){
				$checkintegrity = false;
			}else{
				$checkintegrity = true;
			}
		}
		//If integrity checks are disabled then 
		//user must send file size and checksums
		//else raise error.
		if( $checkintegrity === false ){
			if( count( $xml->xpath('./virtualization:size') ) > 0 ){
				$size = $xml->xpath('./virtualization:size');
				$size = (string) $size[0];
				if( !is_numeric($size) || intval($size)<=0 ){
					return $this->_setErrorMessage("Invalid image file size value for VMI Instance.");
				}
			}
			$checksum = "";
			if( count($xml->xpath('./virtualization:checksum')) > 0 ){
				$checksum = $xml->xpath('./virtualization:checksum');
				$checksum = $checksum[0];
			}
			if( strlen( trim( strval($checksum) )  ) > 0 ){
				$checksum = $xml->xpath('./virtualization:checksum');
				$checksum = $checksum[0];
				if( $m->checksum !== strval($checksum) || $m->size !== intval($size) ){
					$m->integrityStatus = "";
					$m->integrityMessage = ""; 
				}
				if( trim($m->size) !== "" && intval($m->size) !== intval($size) ){
					$isupdated = true;
				}
				$m->size = $size;
				if( trim($m->checksum) !== "" && trim($m->checksum) !== trim($checksum) ){
					$isupdated = true;
				}
				$m->checksum = strval($checksum);
				$m->checksumfunc = strval($checksum->attributes()->hash);
				$m->checksumfunc = strtolower(trim($m->checksumfunc));
				
				if( strlen( trim($m->checksum) ) == 0 ){
					return $this->_setErrorMessage("Invalid checksum value for VMI Instance.");
				}
				if( strlen( trim($m->checksumfunc) ) == 0 || in_array($m->checksumfunc, array("sha512","sha384","sha256","sha224","sha1","md5")) === false ){
					$m->checksumfunc = "sha512";
				}
			}
		}else{
			$m->size = 0;
			$m->checksum = '';
			$m->checksumfunc = 'sha512';
		}
		$m->autointegrity = $checkintegrity;
		
		if( !is_numeric($m->id) || intval($m->id) <=0 ){ /*new instance*/
			$addedby = $this->_parent->getUser();
			if( $addedby && intval($addedby->id) > 0 ){
				$m->addedbyid = $addedby->id;
			}
			$m->initialchecksum = $m->checksum;
			$m->initialsize = $m->size;
		}elseif($isupdated === true ){
			$lastupdatedby = $this->_parent->getUser();
			if( $lastupdatedby && intval($lastupdatedby->id) > 0 ){
				$m->lastUpdatedByID = $lastupdatedby->id;
				$m->lastUpdatedOn = 'NOW()';
			}
		}
		
		//Save optional default access string
		if( count( $xml->xpath('./virtualization:defaultaccess') ) > 0 ){
			$defacc = $xml->xpath('./virtualization:defaultaccess');
			$defacc = strval($defacc[0]);
		}else{
			$defacc = "None";
		}
		$m->defaultAccess = $defacc;

		//Save optional Ovf file url
		if( count( $xml->xpath('./virtualization:ovf') ) > 0 ){
			$ovf = $xml->xpath('./virtualization:ovf');
			$ovf = $ovf[0];
			if( strlen( trim( strval($ovf->attributes()->url) ) ) > 0 ){
				$m->ovfurl = trim( strval($ovf->attributes()->url) );
			}else{
				$m->ovfurl = null;
			}
		}else{
			$m->ovfurl = "";
		}
		//Save optional Context Script 
		//Only in case of a new version
		if( !is_numeric($m->id) || intval($m->id) <=0 ){ /*new instance*/
			if( count( $xml->xpath('./virtualization:contextscript') ) > 0 ){
				$contextscript = $xml->xpath('./virtualization:contextscript');
				$contextscript = $contextscript[0];
			}
		}
		//Set default title in case of empty title
		if( trim($m->title) === "" ){
			$os = $flavour->getOs();
			$osversion = $flavour->osversion;
			$hypervisors = $flavour->getHypervisors();
			if( is_string($hypervisors) ){
				$hypervisors = pg_to_php_array($hypervisors);
			}
			$hypers = implode(",", $hypervisors);
			$deftitle = "Image for " . $this->appname . " [" . trim($os->name) . "/" . trim($osversion) . "/" . trim($hypers) . "]";
			$m->title = trim($deftitle);
		}
		
		$m->save();
		// save deferred network traffic data and other stuff
		foreach ($deferredNetTraf as $d) {
			$d->VMIinstanceID = $m->id;
			$d->save();
		}
		foreach ($deferredCFs as $d) {
			$d->vmiinstanceID = $m->id;
			try {
				$d->save();
			} catch (Exception $e) {
				error_log($e);
				return $this->_setErrorMessage("Invalid id attribute \`" . $d->fmtid . "' for supported context format element \`virtualization:contextformat'", RestErrorEnum::RE_BACKEND_ERROR);
			}
		}
		
		$synccontextscript = $this->syncContextScript($contextscript, $m);
		if( $synccontextscript === null || $synccontextscript === false){
			return $this->_setErrorMessage("Could not relate context scripts to vmi instance " .  $m->id);
		}elseif(is_string($synccontextscript) ){
			return $this->_setErrorMessage($synccontextscript);
		}
		
		return $m;
	}
	/*
	 * Creates or updates a VMI item 
	 * based on the given XML data.
	 * Returns a Default_Model_VMI item.
	 */
	private function parseVAppImage($xml, $parent = null){
		$m = $this->getItem("vmi",$xml );
		
		if( count($xml->xpath('./virtualization:notes')) > 0 ){
			$m->notes = $xml->xpath('./virtualization:notes');
			$m->notes = (string) $m->notes[0];
			$m->notes = strip_tags($m->notes);
			if( $this->validateNotes($m->notes,"vmi") === false ){
				return $this->_setErrorMessage("Invalid notes value for VMI.");
			}
		}
		if( count($xml->xpath('./virtualization:description')) > 0 ){
			$m->description = $xml->xpath('./virtualization:description');
			$m->description = (string) $m->description[0];
			$m->description = strip_tags($m->description);
			if( $this->validateDescription($m->description) === false ){
				return $this->_setErrorMessage("Invalid description value for virtual appliance version.");
			}
		}else{
			$m->description = "";
		}
		if( count($xml->xpath('./virtualization:group')) > 0 ){
			$m->groupname = $xml->xpath('./virtualization:group');
			$m->groupname = (string) $m->groupname[0];
			$m->groupname = strip_tags($m->groupname);
			if( $this->validateGroupname($m->groupname) === false ){
				return $this->_setErrorMessage("Invalid group value for VMI.");
			}
			
			if( $this->isexternalrequest === false ){
				$isunique = $this->isGroupNameUnique($m, $parent);
				if( $isunique === false ){
					return $this->_setErrorMessage("VMI Group values are used by other VMI groups in this version.");
				}
			}
		}
		
		//In case of a new vmi instance check if the user has given an identifier
		if( !$m->guid ){
			if( count($xml->xpath('./virtualization:identifier')) > 0 ){
				$guid = $xml->xpath('./virtualization:identifier');
				$guid = (string) $guid[0];
				$guid = trim($guid);
				if( strlen( $guid ) > 0 ){
					$m->guid = $guid;
				}
			}
			//check if user-defined identifier is in use from other entities(images, other application's vappliance versions etc
			if( $m->guid ){
				//check if user-defined identifier is in use from other entities(images, other application's vappliance versions etc
				$vaversions = new Default_Model_VAversions;
				$vaversions->filter->guid->equals($m->guid);
				if( count($vaversions) > 0 ){
					$vaversion = $vaversions->items[0];
					$va = $vaversion->getVa();
					if( $va ){
						$app = $va->getApplication();
						if( $app ) {
							return $this->_setErrorMessage("Image identifier " . $m->guid . " is already used by " . $app->name . " virtual appliance.");
						}
					}
					return $this->_setErrorMessage("Image identifier " . $m->guid . " is already used by another virtual appliance version.");
				}
				$vmis = new Default_Model_Vmis();
				$vmis->filter->guid->equals($m->guid);
				if( count($vmis->items) > 0 ){
					return $this->_setErrorMessage("Image identifier " . $m->guid . " is already used by another virtual appliance image group.");
				}
				$vminstances = new Default_Model_VMIinstances();
				$vminstances->filter->guid->equals($m->guid);
				if( count( $vminstances->items ) > 0 ){
					return $this->_setErrorMessage("Image identifier " . $m->guid . " is already used by another virtual appliance image.");
				}
				//Check if identifier is used inside a VO wide image list
				$voexists = $this->VOGuidExists($m->guid);
				if( $voexists === true ){
					return $this->_setErrorMessage("Image identifier " . $m->guid . " is already used in a virtual organization image list.");
				}elseif( is_string($voexists) ){
					return $this->_setErrorMessage("Image identifier " . $m->guid . " is already used by " . $voexists . " virtual organization image list.");
				}
			}
		}
		
		if( count($xml->xpath('./virtualization:description')) > 0 ){
			$m->description = $xml->xpath('./virtualization:description');
			$m->description = (string) $m->description[0];
			$m->description = strip_tags($m->description);
			if( $this->validateDescription($m->description) === false ){
				return $this->_setErrorMessage("Invalid description value for VMI.");
			}
		}
		
		$m->vappid = $parent->vappid;
		
		$m->name = $this->appname;
		$m->save();
		
		//Retrieve current VMI Instances ids.
		$oldvmiinstanceids = $m->getWMIInstanceIds();
		
		//Get VMIInstances of VMI
		$xinstances = $xml->xpath("./virtualization:instance");
		$vmiinstances = array();
		if( count($xinstances) > 0 ){
			for( $i = 0; $i < count($xinstances); $i+=1 ){
				$v = $this->parseVAppImageInstance($xinstances[$i], $m);
				if( $v === false ){
					return false;
				}
				$vmiinstances[] = $v;
			}
		}
		
		if( Supports::singleVMIPolicy() && count($vmiinstances) > 1 ) {
			return $this->_setErrorMessage("Only one VMI image per version is allowed");
		}
				
		/* Delete old undefined vmi instances */
		for( $i=0; $i<count($vmiinstances); $i+=1 ){
			$item = $vmiinstances[$i];
			$index = -1;
			//find in old values
			for( $j=0; $j<count($oldvmiinstanceids); $j+=1 ){
				if( $item->id == $oldvmiinstanceids[$j] ){
					$index = $j;
					break;
				}
			}
			//Remove current vmi instance item from old values array
			if( $index > -1 ){
				unset($oldvmiinstanceids[$index]);
				$oldvmiinstanceids = array_values($oldvmiinstanceids);
			}
		}
		
		/*Remove old vmi instances that are no longer defined in the request xml */
		$oldvmiinstances = new Default_Model_VMIinstances();
		$oldvmiinstances->filter->id->in($oldvmiinstanceids);
		if( count($oldvmiinstances->items) > 0 ){
			for( $i=0; $i<count($oldvmiinstances->items); $i+=1 ){
				$item = $oldvmiinstances->items[$i];
				$this->deleteContextScriptRelation($item->id);
				$item->delete();
			}
		}
		
		//Check if remaining vmiinstances have duplicate urls
		for( $i = 0; $i < count($vmiinstances); $i+=1 ){
			$vminst = $vmiinstances[$i];
			//do not check url in deleted vmi instances
			if( in_array($vminst->id, $oldvmiinstanceids) ) {
				continue;
			}
			
			$urlexists = $this->VMIInstanceUrlExists($vminst, $m);
			if( $urlexists === true ){
				return $this->_setErrorMessage("Duplicate url values in the same VMI group");
			}
		}
		
		/*Associate VMIInstances with current VAVersion*/
		$this->setupVAppList($parent, $vmiinstances);
		
		return $m;
	}
	/*
	 * Creates or updates a VAVersion item 
	 * based on the given XML data.
	 * Returns a Default_Model_VAVersion item.
	 */
	private function parseVAppVersion($xml, $parent = null){
		$userid = null;
		if (! is_null($this->_parent)) {
			$user = $this->_parent->getUser();
			if (! is_null($user)) {
				$userid = $user->id;
			} else {
				error_log("[RestAppVAXMLParser::parseVAppVersion] Error: _parent->_user is NULL");
			}
		} else {
			error_log("[RestAppVAXMLParser::parseVAppVersion] Error: _parent is NULL");
		}

		$m = $this->getItem("vaversion", $xml);
		
		if( !$this->isPUT() && $this->isexternalrequest===false && ( !is_numeric($m->id) || intval($m->id)<=0 )  ){
			return $this->_setErrorMessage("No virtual appliance version given");
		}
		
		//Check if retrieved version
		if( is_numeric($m->id) && intval($m->id) > 0 ){
			//if version belongs to this vappliance
			if( $m->vappid !== $this->vappid ){
				return $this->_setErrorMessage("Given version does not belong in this virtual appliance");
			}
			//check if is to toogle enable
			if( strlen( trim(strval($xml->attributes()->enabled)) ) > 0 && $this->isPUT() === false){
				$enabled = strtolower(trim(strval($xml->attributes()->enabled)));
				if( $enabled == "true" && $m->enabled === false ){
					$this->createVersionState($m);
					$m->enabled = true;
					$m->enabledByID = $userid;
					$m->save();
					$this->vappversion_state->setVersionNewState($m);
					return $m;
				}elseif( $enabled == "false" && $m->enabled === true){
					$this->createVersionState($m);
					$m->enabled = false;
					$m->enabledByID = $userid;
					$m->save();
					$this->vappversion_state->setVersionNewState($m);
					return $m;
				}
			}
			//Check if published
			if( $m->published == true && $m->archived == false && $m->status == "verified" ){
				if( $this->isexternalrequest === true || $this->isPUT() === true){
					$ident = $m->guid;					
					$m = new Default_Model_VAversion();
					$m->publishedByID = $userid;
					$m->guid = $ident;
				}else{
					return $this->_setErrorMessage("Cannot edit published version");
				}
			}
			$workingver = $this->getWorkingVersion();
			//Compare to working
			if( $workingver !== null ){
				//if not the same as working then archive working version 
				if( $workingver->id !== $m->id ){
					$delver = VMCaster::deleteVersion($workingver);
					if( $delver !== true ){
						if( $delver === false ){
							$delver = "";
						}
						return $this->_setErrorMessage("Could not delete working version." . $delver);
					}
				}
			}
		}
		
		if( strlen( trim(strval($xml->attributes()->version)) ) > 0 ){
			$m->version = trim( strval($xml->attributes()->version) );
			$m->version = strip_tags($m->version);
			if( $this->validateVersion($m->version) === false ){
				return $this->_setErrorMessage("Invalid version value for virtual appliance version.");
			}
			if( $this->isUsedVaVersion($this->appid, $m->version) ) {
				return $this->_setErrorMessage("Version value is already used in a previous version of the virtual appliance.");
			}
		}
		/*
		 * Create old data set and create VApplianceVersionState manager
		 */
		$this->createVersionState($m);
		
		if( is_numeric($m->id) && intval($m->id)>0 ){
			if( strlen( trim(strval($xml->attributes()->enabled)) ) > 0 ){
				$enabled = strtolower(trim(strval($xml->attributes()->enabled)));
				if( $enabled == "true" ){
					$m->enabled = true;
				}elseif( $m->enabled == "false" ){
					$m->enabled = false;
				}
			}
		}
		
		if( strlen( trim(strval($xml->attributes()->status)) ) > 0 ){
			$status = strtolower(trim(strval($xml->attributes()->status)));
			if( $this->isPUT() && !$m->status ){
				$m->status = 'init';
			}
			$validstatus = $this->validateStatusChange($m, $status);
			if( $validstatus === true ){
				$m->status = $status;
			}elseif ( strlen(trim(strval($validstatus))) > 0 ) {
				return $this->_setErrorMessage($validstatus);
			}else{
				return $this->_setErrorMessage("Invalid status value given.");
			}
		}
		
		if( strlen( trim(strval($xml->attributes()->published)) ) > 0 ){
			$published = strtolower(trim(strval($xml->attributes()->published)));
			$m->published = ($published==="true")?true:false;
		}
		
		if( count($xml->xpath('./virtualization:notes')) > 0 ){
			$m->notes = $xml->xpath('./virtualization:notes');
			$m->notes = (string) $m->notes[0];
			if( $this->validateNotes($m->notes) === false ){
				return $this->_setErrorMessage("Invalid notes value for virtual appliance version.");
			}
		}
		
		if( strlen( trim(strval($xml->attributes()->createdon)) ) > 0 ){
			$m->createdon = strval($xml->attributes()->createdon);
		}

		// check for expireson attribute. If it does not exist, fall back to expiresin (i.e. give priority to expireon)
		// if neither expireson nor expiresin exist, impose a default of 1y
		if(strlen(trim(strval($xml->attributes()->expireson))) > 0) {
			$m->expireson = strval($xml->attributes()->expireson);
			$m->expireson = $this->getValidVAVersionExpirationDate($m->expireson);
		} elseif (strlen(trim(strval($xml->attributes()->expiresin))) > 0) {
			$expiresin = trim(strval($xml->attributes()->expiresin));
			$m->expireson = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s", strtotime(date('Y-m-d H:i:s'))) . " + $expiresin"));
			$m->expireson = $this->getValidVAVersionExpirationDate($m->expireson);
		} else {
			$m->expireson = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s", strtotime(date('Y-m-d H:i:s'))) . " + 1 year"));
			$m->expireson = $this->getValidVAVersionExpirationDate($m->expireson);
		}
		//In case of a new vaversion check if user has given an identifier
		if( !$m->guid ){
			if( count($xml->xpath('./virtualization:identifier')) > 0 ){
				$guid = $xml->xpath('./virtualization:identifier');
				$guid = (string) $guid[0];
				$guid = trim( $guid );
				if( strlen( $guid ) > 0 ){
					$m->guid = $guid;
				}
			}
			//check if user-defined identifier is in use from other entities(images, other application's vappliance versions etc
			if( $m->guid ){ 
				//check that identifier is not used by other images anywhere
				$images = new Default_Model_VMIinstances();
				$images->filter->guid->equals($m->guid);
				if( count($images->items) > 0 ){
					return $this->_setErrorMessage("Version identifier " . $m->guid . " is already in use by another virtual appliance  image.");
				}
				//check that identifier is not used by other VMI
				$vmis = new Default_Model_VMIs();
				$vmis->filter->guid->equals($m->guid);
				if( count($vmis->items) > 0 ){
					return $this->_setErrorMessage("Version identifier " . $m->guid . " is already in use by another virtual appliance image group.");
				}
				//check that identifier is not used by other vaversions outside this application
				$vaversions = new Default_Model_VAversions();
				$vaversions->filter->guid->equals($m->guid);
				if( count($vaversions->items) > 0 ){
					$vaversion = $vaversions->items[0];
					$va = $vaversion->getVa();
					if( $va && $va->id !== $this->vappid ){
						$app = $va->getApplication();
						if( $app ) {
							return $this->_setErrorMessage("Version identifier " . $m->guid . " is already used by " . $app->name . " virtual appliance.");
						}
					}
					return $this->_setErrorMessage("Version identifier " . $m->guid . " is already used by another version.");
				}
				//Check if identifier is used inside a VO wide image list
				$voexists = $this->VOGuidExists($m->guid);
				if( $voexists === true ){
					return $this->_setErrorMessage("Version identifier " . $m->guid . " is already used in a virtual organization image list.");
				}elseif( is_string($voexists) ){
					return $this->_setErrorMessage("Version identifier " . $m->guid . " is already used by " . $voexists . " virtual organization image list.");
				}
			}
		}
		
		if( !$this->validateVersion($m->version) ){
			return $this->_setErrorMessage("Invalid version value for virtual appliance version.");
		}
		
		if( !$this->validateNotes($m->notes) ){
			return $this->_setErrorMessage("Invalid notes value for virtual appliance version.");
		}
		
		if( strlen( trim($m->guid) ) > 0 && $this->validateIdentifier($m->guid)===false ){
			return $this->_setErrorMessage("Invalid identifier value for virtual appliance version.");
		}
		if( $this->isexternalrequest === true ){
			if( strlen( trim(strval($xml->attributes()->submissionid)) ) > 0 ){
				$submissionid = strtolower(trim(strval($xml->attributes()->submissionid)));
				$m->submissionID = intval($submissionid);
			}
			$m->isexternal = true;
			$m->status = 'init';
		}
		$m->vappid = $this->vappid;
		$m->save();
				
		//Check virtual appliance version state (eg publish,enabled etc)
		$validversionstate = $this->vappversion_state->setVersionNewState($m);
		if( $validversionstate !== true ){
			if( $validversionstate !== false){
				return $this->_setErrorMessage($validversionstate);
			}
			return $this->_setErrorMessage("Invalid state for virtual appliance version");
		}
		
		//Store locally for future use
		$this->vappversionid = $m->id;
		
		//Retrieve current VMIs ids.
		$oldvmiids = $m->getVMIIds();
		
		//Check if user requests to delete all vmis and their contents (vmi instances etc)
		if ( count($xml->xpath('/virtualization:image[@xsi:nil="true"]')) != 0 ) {
			$this->deleteVMIs($oldvmiids);
			return $m;
		}
		
		//Get VMIs of VA Version.
		$ximages = $xml->xpath("./virtualization:image");
		$vmis = array();
		if( count($ximages) > 0 ){
			for( $i = 0; $i < count($ximages); $i+=1 ){
				$v = $this->parseVAppImage($ximages[$i], $m);
				if( $v === false ){
					return false;
				}
				$vmis[] = $v;
			}
		}
		
		/*Check for VMIs for deletion*/
		for( $i=0; $i<count($vmis); $i+=1 ){
			$item = $vmis[$i];
			$index = -1;
			//find in old values
			for( $j=0; $j<count($oldvmiids); $j+=1 ){
				if( $item->id == $oldvmiids[$j] ){
					$index = $j;
					break;
				}
			}
			//Remove current vmi item from old values array
			if( $index > -1 ){
				unset($oldvmiids[$index]);
				$oldvmiids = array_values($oldvmiids);
			}
		}
		/*Remove old vmis that are no longer defined in the request xml */
		$this->deleteVMIs($oldvmiids);
		
		if( $this->versionHasImages($m) === false ){
			return $this->_setErrorMessage("Version must contain at least one image");
		}
		if( $this->versionHasUniqueUrls() === false){
			return $this->_setErrorMessage("Version contains images with same url");
		}
		return $m;
	}
	/*
	 * Creates or updates a VA item 
	 * based on the given XML data.
	 * Returns a Default_Model_VA item.
	 */
	private function parseVAppliance($xml){
		$m = $this->getItem("va",$xml);
		$isput = $this->isPUT();
		
		if( $m == null ){
			return $this->_setErrrorMessage("Could not parse virtual appliance xml");
		}
		
		if( is_numeric($m->id) && $m->id > 0 && $isput ){ //PUT with ID
			//return $this->_setErrorMessage('Cannot add existing virtual appliance.');
		}elseif( !$isput && (!is_numeric($m->id) || intval($m->id) <= 0 ) ){ //POST without ID
			return $this->_setErrorMessage('No virtual appliance id given for update action.');
		}elseif( $isput && ( !is_numeric($m->appid) || intval($m->appid) <=0 ) ){//NO APPID
			return $this->_setErrorMessage('No software id given to create new virtual appliance.');
		}
		
		$apps = new Default_Model_Applications();
		$apps->filter->id->equals($m->appid);
		if( count($apps->items) === 0 ){
			return $this->_setErrorMessage('Software with id: ' . $m->appid . ' not found.');
		}
		$app = $apps->items[0];
		if ( ! $this->_user->privs->canManageVAs($app)){
			$this->setError(RestErrorEnum::RE_ACCESS_DENIED);
			return false;
		}
		//Create VAppliance if it doesn't exist
		if( intval($m->id) <= 0 && $isput === true ){
			$vas = new Default_Model_VAs();
			$vas->filter->appid->equals($app->id);
			if( count($vas->items) === 0 ){
				$m->appid = $app->id;
				$m->name = $app->name;
				$m->save();
			}else{
				$m = $vas->items[0];
			}
		}
		
		//Store locally for future use
		$this->vappid = $m->id;
		$this->appid = $app->id;
		$this->appname = $app->name;
		
		$xversion = $xml->xpath('./virtualization:instance');
		if( count($xversion) == 0 ){
			return $this->_setErrorMessage('No virtual appliance version given.');
		}
		//Only one version per request is processed
		$xversion = $xversion[0];
		$v = $this->parseVAppVersion($xversion, $m);
		if( $v === false ){
			//something went wrong, return.
			return false;
		}
		return $m;
		
	}
	public function parse($xml) {
		$vapp = null;
		$app = null;
		$originalxml = ''.$xml;
		if( !is_null($this->_user) ){
			try {
				$xml = new SimpleXMLElement($xml);
				$xml->registerXPathNamespace('virtualization','http://appdb.egi.eu/api/1.0/virtualization');
			} catch (Exception $e) {
				$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
				$this->_extError = 'Could not parse XML: ' . $e->getMessage();
				return new Default_Model_VA();
            }
            $this->_xml = $xml;
			if( count($xml->xpath("//vmc2appdb")) > 0 ){
				$xml = VMCaster::transformXml($originalxml);
				try {
					$xml = new SimpleXMLElement($xml);
					$xml->registerXPathNamespace('virtualization','http://appdb.egi.eu/api/1.0/virtualization');
					$this->isexternalrequest = true;
				} catch (Exception $e) {
					$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
					$this->_extError = 'Could not parse XML: ' . $e->getMessage();
					return new Default_Model_VA();
				}
			}
			if( count($xml->xpath("./virtualization:appliance")) > 0 ){
				$xml = $xml->xpath("./virtualization:appliance");
				$xml = $xml[0];
			}
			$vapp = $this->parseVAppliance($xml);
			if( $vapp === false ){
				return false;
			}
			/*
			 * Check if an external action is required, such as publishing a version
			 */
			$service = $this->createVApplianceService();
			$result = $service->dispatch();
			if( $result !== true ){
				$this->_setErrorMessage($result);
				return false;
			}
			return $vapp;
		} else {
			$this->_error = RestErrorEnum::RE_ACCESS_DENIED;
			return false;
		}
	}
}

class RestAppVAList extends RestResourceList {
    /*** Attributes ***/

    /**
     * internal reference to XML parser, set during initialization
     *
     */
	private $_parser;
	const VALIST_VERSION_LIMIT = 102;
	public function getDataType() {
		return "virtualization";
	}

	public function _options() {
		$options = array();
		$options[] = RestMethodEnum::RM_GET;
		if ( $this->authenticate() ) {
			$options[] = RestMethodEnum::RM_PUT;
			$options[] = RestMethodEnum::RM_POST;
		}
		return $options;
	}

	/**
     * overrides RestResourceList::init()
     */
	protected function init() {
		$this->_parser = new RestAppVAXMLParser($this);
	}


	public function _list() { 
		return $this->get();
	}

	public function get() {
		if (parent::get() !== false) {
			db()->setFetchMode(Zend_Db::FETCH_OBJ);
			$locked = true;
			$cnt = 0;
			while ($locked) {
				$cnt = $cnt + 1;
				if ($cnt > 30) {
	//				$this->setError(RestErrorEnum::RE_BACKEND_ERROR);
	//				$this->_extError = "Item locked. Please try again later";
					error_log("Rest API: Timeout on VA (" . $this->getParam("vappid") . ") lock. Breaking");
	//				return false;
					break;
				}
				$locked = db()->query("SELECT pg_locks.granted FROM pg_locks WHERE pg_locks.locktype = 'advisory' AND pg_locks.granted AND pg_locks.objid = CRC32('vav" . $this->getParam("id") . "')")->fetchAll();
				$locked = (count($locked) > 0);
				if ($locked) {
					error_log("Rest API: VA for APP (" . $this->getParam("id") . ") locked (try: $cnt / 30)");
					sleep(1);
				}
			}

			db()->setFetchMode(Zend_Db::FETCH_OBJ);
			$appid = $this->getParam("id");
			if ( ! is_numeric($appid) ) {
				$appid = "(SELECT id FROM applications WHERE cname ILIKE '" . pg_escape_string(substr($this->getParam("id"), 2)) . "' FETCH FIRST 1 ROWS ONLY)";
			}
			$limit = "";
			if( RestAppVAList::VALIST_VERSION_LIMIT > 0 ){
				$limit = " LIMIT " .  RestAppVAList::VALIST_VERSION_LIMIT;
			}
			$res = db()->query("SELECT vapp_to_xml(" . $appid . ", 'applications') AS xml" . $limit . ";")->fetchAll();
			$x = array();
			foreach($res as $r) {
				$x[] = $r->xml;
			}
			return new XMLFragmentRestResponse($x, $this);
		} else {
			return false;
		}
	}

	public function hidePrivateData($data) {
		$xf = RestAPIHelper::getFolder(RestFolderEnum::FE_XSL_FOLDER).'virtualization.private.xsl';
		$xsl = new DOMDocument();
		$xsl->load($xf);
		$proc = new XSLTProcessor();
		$proc->registerPHPFunctions();
		$proc->importStylesheet($xsl);
		$xml = new DOMDocument();
		$xml->loadXML($data, LIBXML_NSCLEAN | LIBXML_COMPACT);
		$res = $proc->transformToXml($xml);
		return $res;
	}

	public function hideWorkingVersion($data) {
		$xf = RestAPIHelper::getFolder(RestFolderEnum::FE_XSL_FOLDER).'virtualization.limited.xsl';
		$xsl = new DOMDocument();
		$xsl->load($xf);
		$proc = new XSLTProcessor();
		$proc->registerPHPFunctions();
		$proc->importStylesheet($xsl);
		$xml = new DOMDocument();
		$xml->loadXML($data, LIBXML_NSCLEAN | LIBXML_COMPACT);
		$res = $proc->transformToXml($xml);
		return $res;
	}

	private function getPrivateVA() {
		if ( ! isset($this->_pars["id"]) ) {
			return null;
		} else {
			$id = normalizeAppID($this);
			$vas = new Default_Model_VAs();
			$vas->filter->appid->numequals($id);
			if (count($vas->items) > 0) {
				$va = $vas->items[0];
				if (filter_var($va->imglstprivate, FILTER_VALIDATE_BOOLEAN)) {
					return $va;
				} else {
					return null;
				}
			}
		}
	}

	public function canAccessPrivateData() {
		$canManage = false;
		$canAccess = false;
		$va = $this->getPrivateVA();
		if (! is_null($va)) {
			if (! is_null($this->getUser())) {
				$app = $va->getApplication();
				if (! is_null($app)) {
					$uprivs = $this->getUser()->privs;
					if (! is_null($uprivs)) {
						$canManage = $uprivs->canManageVAs($app->guid);
						if ($canManage) {
							$canAccess = true;
						} else {
							$canAccess = $uprivs->canAccessVAPrivateData($app->guid);
						}
					}
				}
			}
		} else {
			$canAccess = true;
		}
		return $canAccess === true;
	}

	public function canManageVAs() {
		$canManage = false;
		if (! is_null($this->getUser())) {
			$app = new Default_Model_Applications();
			$id = normalizeAppID($this);
			$app->filter->id->numequals($id);
			if (count($app->items) > 0) {
				$app = $app->items[0];
				$uprivs = $this->getUser()->privs;
				if (! is_null($uprivs)) {
					$canManage = $uprivs->canManageVAs($app->guid);
				}
			} 
		}
		return $canManage === true;
	}

	public function authorize($method) {
		$res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
            $res = true;
            break;
        case RestMethodEnum::RM_PUT:
        case RestMethodEnum::RM_POST:
			if( is_null($this->getUser()) ){
				$this->setError(RestErrorEnum::RE_ACCESS_DENIED);
				return false;
			}
			$res = true;
			break;
        case RestMethodEnum::RM_DELETE:
			$res = false;
			break;
		}
		return $res;
	}

	private function putpost($method) {
		db()->beginTransaction();
		$this->_parser->HTTPMETHOD = $method;
		$vapp = $this->_parser->parse($this->getData());
		$vapperror = $this->_parser->getExtError();
		if( trim($vapperror) !== ""){
			error_log("[VAPP API ERROR]: " . $this->_parser->getExtError());
		}
        if ( $this->_parser->getError() !== RestErrorEnum::RE_OK || $vapp === false) {
			db()->rollBack();
			$this->setError($this->_parser->getError(), $this->_parser->getExtError(), false);
			$ret = false;
		} else {
			db()->commit();
			//Perform actions to external services that use db
			$service = $this->_parser->getVApplianceService();
			if( $service !== null ){
				@$service->postDispatch();
			}
			$res = new RestAppVAItem(array("vappid" => $vapp->id), $this);
			$ret = false;
			$retCount = 0;
			// sometimes fetching the new data might fail (race condition). Try up to 5 times
			while (($ret === false) && ($retCount<5)) {
				sleep(1);
				$ret = $res->get();
				if (($ret === false) && ($res->getError() !== RestErrorEnum::RE_ITEM_NOT_FOUND)) {
					// give up on errors other than item not found
					break;
				} else {
					$retCount += 1;
				}
			}
		}
        return $ret;
	}

	public function put() {
		if (  parent::put() !== false ) {
            return new XMLFragmentRestResponse($this->putpost(RestMethodEnum::RM_PUT), $this);
		} else return false;
	}

	public function post() {
		if (  parent::put() !== false ) {
            return new XMLFragmentRestResponse($this->putpost(RestMethodEnum::RM_POST), $this);
		} else return false;
	}

}

class RestAppVAItem extends RestResourceItem {
    /*** Attributes ***/

    /**
     * reference to model representing the requested resource item
     *
     */
    private $_res;

	/**
     * reference to parent collection of the model representing the requested resource item
     *
     */
	private $_resParent;

	protected function init() {
		// must call iserIsAdmin before instanciating a Default_Model_Applications, so that
		// $_GET['userid'] is properly set
		$isAdmin = $this->userIsAdmin();
		$this->_resParent = new Default_Model_VAs();
		$this->_resParent->filter->id->equals($this->getParam("vappid"));
		if ( count($this->_resParent->items) > 0 ) {
			$this->_res = $this->_resParent->items[0];
		} else {
			$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
			$this->_res = null;
		}
		$this->_parser = new RestAppVAXMLParser($this);
	}

	public function getDataType() {
		return "virtualization";
	}

	public function _options() {
		$options = array();
		$options[] = RestMethodEnum::RM_GET;
		if ( $this->authenticate() ) {
			$options[] = RestMethodEnum::RM_DELETE;
		}
		return $options;
	}

	public function get() {
		if (parent::get() !== false) {
			$res = db()->query("SELECT vapp_to_xml(" . $this->getParam("vappid") . ", 'vapplications') AS xml;")->fetchAll();
			$x = array();
			foreach($res as $r) {
				if (is_object($r)) {
					$x[] = $r->xml;
				} elseif (is_array($r)) {
					if (array_key_exists("xml", $r)) {
						$x[] = $r["xml"];
					} else {
						error_log("[RestAppVAItem::get] WARNING: r is an array but has no \`xml' key!");
					}
				} else {
					error_log("[RestAppVAItem::get] WARNING: r is not an object!");
				}
			}
/*			$reply = new XMLFragmentRestResponse($x, $this);
			
			if( $this->canAccessPrivateData() === false ){
				$reply = $this->hidePrivateData($reply);
				$reply = new XMLRestResponse($reply, $this);
				return $reply;
			}
	
			return $reply;
*/
			return $reply = new XMLFragmentRestResponse($x, $this);
		} else {
			return false;
		}
	}
	
	public function hidePrivateData($data){

		return $data;

		$xf = RestAPIHelper::getFolder(RestFolderEnum::FE_XSL_FOLDER).'virtualization.private.xsl';
		$xsl = new DOMDocument();
		$xsl->load($xf);
		$proc = new XSLTProcessor();
		$proc->registerPHPFunctions();
		$proc->importStylesheet($xsl);
		$xml = new DOMDocument();
		$xml->loadXML($data, LIBXML_NSCLEAN | LIBXML_COMPACT);
		$res = $proc->transformToXml( $xml );
		return $res;
	}
	
	public function canAccessPrivateData(){
		if( $this->_res === null || $this->_res->imglstprivate == false) {
			return true;
		}elseif( $this->getUser() === null ) {
			return false;
		}
		
		$app = $this->_res->getApplication();
		if( $app === null ){
			return false;
		}
		
		$uprivs = $this->getUser()->privs;
		if( $uprivs === null ){
			return false;
		}
		
		$canManage = $uprivs->canManageVAs($app->guid);
		if( $canManage === true ){
			return true;
		}
		
		$canAccess = $uprivs->canAccessVAPrivateData($app->guid);
		return $canAccess;
	}
    /**
     * overrides RestResource::delete()
     */
	public function delete() {
		// We do not want to allow removing the vappliance (and all it's versions) at once.
		// Users should delete individual VA versions, where applicable.
		$this->setError(RestErrorEnum::RE_INVALID_OPERATION);
		return false;
		/*
		if ( parent::delete() !== false ) {
			$ret = $this->get();
			$this->_resParent->remove($this->_res);
			return $ret;
		} else return false;
		*/
	}

	/**
     * realization of authorize() from iRestAuthModule
     */
	public function authorize($method) {
		$res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
            $res = true;
            break;
        case RestMethodEnum::RM_PUT:
        case RestMethodEnum::RM_POST:
			$res = false;
			break;
        case RestMethodEnum::RM_DELETE:
			if ( ! is_null($this->_res) ) {
				if (  ! is_null($this->getUser()) ) {
					if ( $this->getUser()->privs->canDeleteApplication($this->_res->application) ) {
						$res = true;
					} else {
						$this->setError(RestErrorEnum::RE_ACCESS_DENIED);
						return false;
					}
				} else {
					$this->setError(RestErrorEnum::RE_ACCESS_DENIED);
					return false;
				}
			} else {
				$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
				return false;
			}
			break;
		}
		return $res;
	}
}

class RestAppContextScriptXMLParser extends RestXMLParser {
	private function _setErrorMessage($msg, $type = RestErrorEnum::RE_INVALID_REPRESENTATION){
		$this->_error = $type;
		$this->_extError = $msg;
		return false;
	}
	public function parse($xml) {
		if( !is_null($this->_user) ){
			try {
				//error_log($xml);
				$this->_xml = $xml;
				$xml = new SimpleXMLElement($xml);
				$xml->registerXPathNamespace('virtualization','http://appdb.egi.eu/api/1.0/virtualization');
				$xml->registerXPathNamespace('contextualization','http://appdb.egi.eu/api/1.0/contextualization');
			} catch (Exception $e) {
				return $this->_setErrorMessage("Could not parse XML: " . $e->getMessage() ,RestErrorEnum::RE_INVALID_REPRESENTATION);
            }
            
			$cs = ContextScriptXMLParser::parse($xml);
			if( $cs === false ){
				return $this->_setErrorMessage("Could not parse XML", RestErrorEnum::RE_INVALID_REPRESENTATION);
			} elseif( $cs !== true && is_string($cs) ){
				return $this->_setErrorMessage($cs, RestErrorEnum::RE_BACKEND_ERROR);
			}
			return $cs;
		} else {
			$this->_error = RestErrorEnum::RE_ACCESS_DENIED;
			return false;
		}
	}
}
class RestAppContextXMLParser extends RestXMLParser{
	private function _setErrorMessage($msg, $type = RestErrorEnum::RE_INVALID_REPRESENTATION){
		$this->_error = $type;
		$this->_extError = $msg;
		return false;
	}
	public function parse($xml) {
		if( !is_null($this->_user) ){
			try {
				$this->_xml = $xml;
				$xml = new SimpleXMLElement($xml);
				$xml->registerXPathNamespace('virtualization','http://appdb.egi.eu/api/1.0/virtualization');
				$xml->registerXPathNamespace('contextualization','http://appdb.egi.eu/api/1.0/contextualization');
			} catch (Exception $e) {
				return $this->_setErrorMessage("Could not parse XML: " . $e->getMessage(), RestErrorEnum::RE_INVALID_REPRESENTATION);
            }
            
			$cs = ContextXMLParser::parse($xml);
			if( $cs === false ){
				return $this->_setErrorMessage("Could not parse XML", RestErrorEnum::RE_INVALID_REPRESENTATION);
			} elseif( $cs !== true && is_string($cs) ){
				return $this->_setErrorMessage($cs, RestErrorEnum::RE_BACKEND_ERROR);
			}
			return $cs;
		} else {
			$this->_error = RestErrorEnum::RE_ACCESS_DENIED;
			return false;
		}
	}
}

class RestAppContext extends RestResourceList{
	private $_app;
	private function _setErrorMessage($msg, $type = RestErrorEnum::RE_INVALID_REPRESENTATION){
		$this->_error = $type;
		$this->_extError = $msg;
		return false;
	}
	protected function init() {
		$m = new Default_Model_Applications();
		$m->filter->id->numequals($this->getParam("id"));
		if( count($m->items) > 0 ){
			$this->_app = $m->items[0];
		}else{
			$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND,"Software appliance not found");
			$this->_app = null;
			return;
		}
		$this->_parser = new RestAppContextXMLParser($this);
	}
	public function getDataType() {
		return "contextualization";
	}
	public function get() {
		if (parent::get() !== false) {
			db()->setFetchMode(Zend_Db::FETCH_OBJ);
			$appid = $this->getParam("id");
			if ( ! is_numeric($appid) ) {
				$appid = "(SELECT id FROM applications WHERE cname ILIKE '" . pg_escape_string(substr($this->getParam("id"), 2)) . "' FETCH FIRST 1 ROWS ONLY)";
			}
			$res = db()->query("SELECT context_to_xml(" . $appid . ") AS xml;")->fetchAll();
			$x = array();
			foreach($res as $r) {
				$x[] = $r->xml;
			}
			return new XMLFragmentRestResponse($x, $this);
		} else {
			return false;
		}
	}
	public function _list() { 
		return $this->get();
	}
	public function post() {
		if (  parent::post() !== false ) {
            return new XMLFragmentRestResponse($this->action(RestMethodEnum::RM_POST), $this);
		} else return false;
	}
	public function _options() {
		$options = array();
		$options[] = RestMethodEnum::RM_GET;
		if ( $this->authenticate() ) {
			$options[] = RestMethodEnum::RM_POST;
		}
		return $options;
	}
	public function authorize($method) {
		$res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
            $res = true;
            break;
        case RestMethodEnum::RM_PUT:
        case RestMethodEnum::RM_POST:			
        case RestMethodEnum::RM_DELETE:
			if ( ! is_null($this->_app) ) {
				if (  ! is_null($this->getUser()) ) {
					if ( $this->getUser()->privs->canManageContextScripts($this->_app) ) {
						$res = true;
					} else {
						$this->setError(RestErrorEnum::RE_ACCESS_DENIED);
						return false;
					}
				} else {
					$this->setError(RestErrorEnum::RE_ACCESS_DENIED);
					return false;
				}
			} else {
				$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
				return false;
			}
			break;
		}
		return $res;
	}
	
	public function action($method){
		db()->beginTransaction();
		$context = Contextualization::initContextualization($this->_app, $this->getUser());
		if( !($context instanceof Contextualization) ){
			if( $context === false ){
				$context = "Could not initialize contextualization";
			}
			$this->setError(RestErrorEnum::RE_BACKEND_ERROR, $context);
			return;
		}
		$data = $this->_parser->parse($this->getData());
        if ( $this->_parser->getError() !== RestErrorEnum::RE_OK ) {
			db()->rollBack();
			$this->setError($this->_parser->getError(), $this->_parser->getExtError(), false);
			$ret = false;
		} elseif( !is_array ($data)) {
			db()->rollBack();
			return $this->_setErrorMessage($data,RestErrorEnum::RE_BACKEND_ERROR);
		} else {
			try{
				$result = $context->action("updatemetadata", $data);
				if( $result === false ){
					db()->rollBack();
					return $this->_setErrorMessage("Could not complete request",RestErrorEnum::RE_BACKEND_ERROR);
				}elseif( is_string($result) ){
					db()->rollBack();
					return $this->_setErrorMessage($result, RestErrorEnum::RE_BACKEND_ERROR);
				}
			} catch (Exception $ex) {
				db()->rollBack();
				return $this->_setErrorMessage($ex->getMessage(),RestErrorEnum::RE_BACKEND_ERROR);
			}
			db()->commit();
			$ret = false;
			$retCount = 0;
			// sometimes fetching the new data might fail (race condition). Try up to 5 times
			while (($ret === false) && ($retCount<5)) {
				sleep(1);
				$ret = $this->get();
				if (($ret === false) && ($res->getError() !== RestErrorEnum::RE_ITEM_NOT_FOUND)) {
					// give up on errors other than item not found
					break;
				} else {
					$retCount += 1;
				}
			}
		}

        return $ret;
	}
}

class RestAppContextScriptItem extends RestResourceItem{
	private $_app;
	private $_contextscript;
	private function _setErrorMessage($msg, $type = RestErrorEnum::RE_INVALID_REPRESENTATION){
		$this->_error = $type;
		$this->_extError = $msg;
		return false;
	}
	protected function init() {
		$m = new Default_Model_Applications();
		$m->filter->id->numequals($this->getParam("id"));
		if( count($m->items) > 0 ){
			$this->_app = $m->items[0];
		}else{
			$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND,"Software appliance not found");
			$this->_app = null;
			return;
		}
		
		$m = new Default_Model_ContextScripts();
		$m->filter->id->numequals($this->getParam("scriptid"));
		if( count($m->items) > 0 ){
			$this->_contextscript = $m->items[0];
		}else{
			$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND,"Contextualization script not found");
			$this->_contextscript = null;
			return;
		}		
	}
	public function getDataType() {
		return "contextualization";
	}

	public function _options() {
		$options = array();
		$options[] = RestMethodEnum::RM_GET;
		if ( $this->authenticate() ) {
			$options[] = RestMethodEnum::RM_DELETE;
			$options[] = RestMethodEnum::RM_POST;
			//$options[] = RestMethodEnum::RM_PUT;
		}
		return $options;
	}
	private function getNamedAction($method){
		$action = strtolower(trim($this->getParam("act")));
		switch($method){
			case RestMethodEnum::RM_POST:
				if( $action === "updateimages" || $action === "updateurl" ){
					$namedaction = $action;
				}else{
					$namedaction = "update";
				}
				break;
			case RestMethodEnum::RM_PUT:
				$namedaction = "insert";
				break;
			case RestMethodEnum::RM_DELETE:
				$namedaction = "remove";
				break;
			case RestMethodEnum::RM_GET:
			default:
				$namedaction = "retrieve";
				break;
		}
		return $namedaction;
	}
	private function getActionArguments($method){
		$result = array();
		$namedaction = $this->getNamedAction($method);
		switch($namedaction){
			case "updateimages":
				$apps = $this->getParam("ids");
				$result["applications"] = explode(",", $apps);
				break;
			default:
				break;
		}
		
		return $result;
	}
	public function get() {
		if (parent::get() !== false) {
			db()->setFetchMode(Zend_Db::FETCH_OBJ);
			$res = db()->query("SELECT contextscript_to_xml(?) AS xml;", array($this->getParam("scriptid")))->fetchAll();
			$x = array();
			foreach($res as $r) {
				$x[] = $r->xml;
			}
			return $reply = new XMLFragmentRestResponse($x, $this);
		} else {
			return false;
		}
	}
	public function delete(){
		return $this->action(RestMethodEnum::RM_DELETE);
	}
	public function post(){
		return $this->action(RestMethodEnum::RM_POST);
	}
	public function action($method){
		db()->beginTransaction();
		$context = Contextualization::initContextualization($this->_app, $this->getUser());
		if( !($context instanceof Contextualization) ){
			if( $context === false ){
				$context = "Could not initialize contextualization";
			}
			$this->setError(RestErrorEnum::RE_BACKEND_ERROR, $context);
			return;
		}
		try{
			$data = array_merge(array(
					"parentid" => $this->_app->id, 
					"id" => $this->_contextscript->id
				), $this->getActionArguments($method) );
			$result = $context->action($this->getNamedAction($method), $data);
			if( $result === false ){
				db()->rollBack();
				return $this->_setErrorMessage("Could not complete request",RestErrorEnum::RE_BACKEND_ERROR);
			}elseif( is_string($result) ){
				db()->rollBack();
				return $this->_setErrorMessage($result, RestErrorEnum::RE_BACKEND_ERROR);
			}
			db()->commit();
		} catch (Exception $ex) {
			db()->rollBack();
			return $this->_setErrorMessage($ex->getMessage(),RestErrorEnum::RE_BACKEND_ERROR);
		}
        return $this->get();
	}
	public function authorize($method) {
		$res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
            $res = true;
            break;
        case RestMethodEnum::RM_PUT:
        case RestMethodEnum::RM_POST:			
        case RestMethodEnum::RM_DELETE:
			if ( ! is_null($this->_app) ) {
				if (  ! is_null($this->getUser()) ) {
					if ( $this->getUser()->privs->canManageContextScripts($this->_app) ) {
						$res = true;
					} else {
						$this->setError(RestErrorEnum::RE_ACCESS_DENIED);
						return false;
					}
				} else {
					$this->setError(RestErrorEnum::RE_ACCESS_DENIED);
					return false;
				}
			} else {
				$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
				return false;
			}
			break;
		}
		return $res;
	}
}

class RestAppContextScriptList extends RestResourceList{
	private $_app;
	private function _setErrorMessage($msg, $type = RestErrorEnum::RE_INVALID_REPRESENTATION){
		$this->_error = $type;
		$this->_extError = $msg;
		return false;
	}
	protected function init() {
		$m = new Default_Model_Applications();
		$m->filter->id->numequals($this->getParam("id"));
		if( count($m->items) > 0 ){
			$this->_app = $m->items[0];
		}else{
			$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND,"Software appliance not found");
			$this->_app = null;
			return;
		}
		$this->_parser = new RestAppContextScriptXMLParser($this);
	}
	public function _list() { 
		return $this->get();
	}
	private function getNamedAction($method){
		$action = strtolower(trim($this->getParam("act")));
		switch($method){
			case RestMethodEnum::RM_POST:
				if( $action === "updateimages" || $action === "updateurl" ){
					$namedaction = $action;
				}else{
					$namedaction = "update";
				}
				break;
			case RestMethodEnum::RM_PUT:
				$namedaction = "insert";
				break;
			case RestMethodEnum::RM_DELETE:
				$namedaction = "remove";
				break;
			case RestMethodEnum::RM_GET:
			default:
				$namedaction = "retrieve";
				break;
		}
		return $namedaction;
	}
	public function action($method){
		db()->beginTransaction();
		$context = Contextualization::initContextualization($this->_app, $this->getUser());
		if( !($context instanceof Contextualization) ){
			if( $context === false ){
				$context = "Could not initialize contextualization";
			}
			$this->setError(RestErrorEnum::RE_BACKEND_ERROR, $context);
			return;
		}
		$data = $this->_parser->parse($this->getData());
        if ( $this->_parser->getError() !== RestErrorEnum::RE_OK ) {
			db()->rollBack();
			$this->setError($this->_parser->getError(), $this->_parser->getExtError(), false);
			$ret = false;
		} elseif( !is_array ($data)) {
			db()->rollBack();
			return $this->_setErrorMessage($data,RestErrorEnum::RE_BACKEND_ERROR);
		} else {
			try{
				$result = $context->action($this->getNamedAction($method), $data);
				if( $result === false ){
					db()->rollBack();
					return $this->_setErrorMessage("Could not complete request",RestErrorEnum::RE_BACKEND_ERROR);
				}elseif( is_string($result) ){
					db()->rollBack();
					return $this->_setErrorMessage($result, RestErrorEnum::RE_BACKEND_ERROR);
				}
			} catch (Exception $ex) {
				db()->rollBack();
				return $this->_setErrorMessage($ex->getMessage(),RestErrorEnum::RE_BACKEND_ERROR);
			}
			db()->commit();
			
			$ret = false;
			$retCount = 0;
			// sometimes fetching the new data might fail (race condition). Try up to 5 times
			while (($ret === false) && ($retCount<5)) {
				sleep(1);
				$ret = $this->get();
				if (($ret === false) && ($res->getError() !== RestErrorEnum::RE_ITEM_NOT_FOUND)) {
					// give up on errors other than item not found
					break;
				} else {
					$retCount += 1;
				}
			}
		}

        return $ret;
	}
	public function delete(){
		 return $this->action(RestMethodEnum::RM_DELETE);
	}
	private function putpost($method) {
        return $this->action($method);
	}
	public function getDataType() {
		return "contextualization";
	}

	public function _options() {
		$options = array();
		$options[] = RestMethodEnum::RM_GET;
		if ( $this->authenticate() ) {
			$options[] = RestMethodEnum::RM_PUT;
			$options[] = RestMethodEnum::RM_POST;
			$options[] = RestMethodEnum::RM_DELETE;
		}
		return $options;
	}
	
	public function get() {
		if (parent::get() !== false) {
			db()->setFetchMode(Zend_Db::FETCH_OBJ);
			$appid = $this->getParam("id");
			if ( ! is_numeric($appid) ) {
				$appid = "(SELECT id FROM applications WHERE cname ILIKE '" . pg_escape_string(substr($this->getParam("id"), 2)) . "' FETCH FIRST 1 ROWS ONLY)";
			}
			$res = db()->query("SELECT context_to_xml(" . $appid . ") AS xml;")->fetchAll();
			$x = array();
			foreach($res as $r) {
				$x[] = $r->xml;
			}
			return new XMLFragmentRestResponse($x, $this);
		} else {
			return false;
		}
	}
	public function authorize($method) {
		$res = false;$res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
            $res = true;
            break;
        case RestMethodEnum::RM_PUT:
        case RestMethodEnum::RM_POST:
        case RestMethodEnum::RM_DELETE:
			if ( ! is_null($this->_app) ) {
				if (  ! is_null($this->getUser()) ) {
					if ( $this->getUser()->privs->canManageContextScripts($this->_app) ) {
						$res = true;
					} else {
						$this->setError(RestErrorEnum::RE_ACCESS_DENIED);
						return false;
					}
				} else {
					$this->setError(RestErrorEnum::RE_ACCESS_DENIED);
					return false;
				}
			} else {
				$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
				return false;
			}
			break;
		}
		return $res;
        switch ($method) {
        case RestMethodEnum::RM_GET:
            $res = true;
            break;
        case RestMethodEnum::RM_PUT:
        case RestMethodEnum::RM_POST:
        case RestMethodEnum::RM_DELETE:
			if ( ! is_null($this->_app) ) {
				if (  ! is_null($this->getUser()) ) {
					if ( $this->getUser()->privs->canManageContextScripts($this->_app) ) {
						$res = true;
					} else {
						$this->setError(RestErrorEnum::RE_ACCESS_DENIED);
						return false;
					}
				} else {
					$this->setError(RestErrorEnum::RE_ACCESS_DENIED);
					return false;
				}
			} else {
				$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
				return false;
			}
			break;
		}
		return $res;
	}
	
	public function put() {
		if (  parent::put() !== false ) {
            return new XMLFragmentRestResponse($this->putpost(RestMethodEnum::RM_PUT), $this);
		} else return false;
	}

	public function post() {
		if (  parent::put() !== false ) {
            return new XMLFragmentRestResponse($this->putpost(RestMethodEnum::RM_POST), $this);
		} else return false;
	}
}

class RestAppVAVersionItem extends RestAppVAItem {
    /*** Attributes ***/

    /**
     * reference to model representing the requested resource item
     *
     */
    private $_res;

	/**
     * reference to parent collection of the model representing the requested resource item
     *
     */
	private $_resParent;

	protected function init() {
		// must call iserIsAdmin before instanciating a Default_Model_Applications, so that
		// $_GET['userid'] is properly set
		$this->_resParent = new Default_Model_VAversions();
		$this->_resParent->filter->id->equals($this->getParam("versionid"))->and($this->_resParent->filter->vappid->equals($this->getParam("vappid")));
		if ( count($this->_resParent->items) > 0 ) {
			$this->_res = $this->_resParent->items[0];
		} else {
			$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
			$this->_res = null;
		}
		$this->_parser = new RestAppVAXMLParser($this);
	}

	public function getDataType() {
		return "virtualization";
	}

	public function _options() {
		$options = array();
		$options[] = RestMethodEnum::RM_GET;
		if ( $this->authenticate() ) {
			$options[] = RestMethodEnum::RM_DELETE;
		}
		return $options;
	}

	public function get() {
		if (parent::get() !== false) {
			db()->setFetchMode(Zend_Db::FETCH_OBJ);
			$res = db()->query("SELECT vapp_to_xml(" . $this->getParam("vappid") . ", 'vapplications') AS xml;")->fetchAll();
			$x = array();
			foreach($res as $r) {
				if (strpos($r->xml, 'vaversionid="'. $this->getParam("versionid") . '"') !== false) {
					$x[] = $r->xml;
				}
			}
			return new XMLFragmentRestResponse($x, $this);
		} else {
			return false;
		}
	}

    /**
     * overrides RestResource::delete()
     */
	public function delete() {
		if (! is_null($this->_res)) {
			$ret = $this->get();
			db()->beginTransaction();
			try{
				if ($this->_res->published) {
					$this->setError(RestErrorEnum::RE_INVALID_OPERATION, "Removing a VA version that has been published is not allowed", false);
					return false;
				} else {
					$vapplists = new Default_Model_VALists();
					$vapplists->filter->vappversionid->equals($this->_res->id);
					if( count($vapplists->items) > 0 ){
						for($i=0; $i<count($vapplists->items); $i+=1){
							$vapplist = $vapplists->items[$i];
							$this->deleteVALists($vapplist);
						}
					}
					$this->_res->delete();
				}
				db()->commit();
			}catch(Exception $e){
				db()->rollBack();
				error_log($e->getMessage());
				$this->setError(RestErrorEnum::RE_BACKEND_ERROR, $e->getMessage(), false);
				return false;
			}
			return $ret;
		} else {
			return false;
		}
	}

	private function deleteVALists($item) {
		$inst = $item->getVMIInstance();
		$this->deleteVMIInstance($inst);
		$item->delete();
	}

	private function deleteVMIInstance($item) {
		$this->deleteContextFormats($item->id);
		$this->deleteContextScripts($item->id);

		// also delete the VMI instance's flavor, if not in use by other instances (flavors are shared)
		$instances = new Default_Model_VMIInstances();
		$instances->filter->vmiflavourid->equals($item->vmiflavourid)->and($instances->filter->id->notequals($item->id));
		if( count($instances->items) === 0 ) {
			$this->deleteFlavour($item->getFlavour(),$item);
		}

		$item->deleteNetworkTraffic();
		$item->delete();
		
	}

	private function deleteContextFormats($vmiinstanceid) {
		$scriptids = array();
		$vmicfs = new Default_Model_VMISupportedContextFormats();
		$vmicfs->filter->vmiinstanceid->numequals($vmiinstanceid);
		if( count($vmicfs->items) > 0 ){
			foreach($vmicfs->items as $item){
				$cfids[] = $item->fmtid;
				$vmicfs->remove($item);
			}
		}
	}
	private function deleteContextScripts($vmiinstanceid){
		$scriptids = array();
		$vmiscripts = new Default_Model_VMIinstanceContextScripts();
		$vmiscripts->filter->vmiinstanceid->numequals($vmiinstanceid);
		if( count($vmiscripts->items) > 0 ){
                        foreach($vmiscripts->items as $item){
				$scriptids[] = $item->contextscriptid;
                                $vmiscripts->remove($item);
			}
		}
		$scriptids = array_unique($scriptids);
		//check if the referenced scripts have relations
		//if no relation found remove them from db.
		foreach($scriptids as $id){
			$vmiscripts = new Default_Model_VMIinstanceContextScripts();
			$vmiscripts->filter->contextscriptid->numequals($id);
			if( count($vmiscripts->items) === 0 ){
                                $scripts = new Default_Model_ContextScripts();
				$scripts->filter->id->numequals($id);
				if( count($scripts->items) > 0 ){
					$scripts->remove($scripts->items[0]);
				}
			}
		}
	}
	private function deleteFlavour($item,$parent){
		$instances = new Default_Model_VMIflavours();
		$instances->filter->vmiid->equals($item->vmiid)->and($instances->filter->id->notequals($parent->id));
		if( count($instances->items) === 0 ){
			$this->deleteVMI($item->getVMI());
			$item->delete();
		}
	}
	private function deleteVMI($item, $parent = null){
		$item->delete();
	}
	/**
     * realization of authorize() from iRestAuthModule
     */
	public function authorize($method) {
		$res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
            $res = true;
            break;
        case RestMethodEnum::RM_PUT:
        case RestMethodEnum::RM_POST:
			$res = false;
			break;
        case RestMethodEnum::RM_DELETE:
			if ( ! is_null($this->_res) ) {
				if (  ! is_null($this->getUser()) ) {
					if ( $this->getUser()->privs->canManageVAs($this->_res->application) ) {
						$res = true;
					} else {
						$this->setError(RestErrorEnum::RE_ACCESS_DENIED);
						return false;
					}
				} else {
					$this->setError(RestErrorEnum::RE_ACCESS_DENIED);
					return false;
				}
			} else {
				$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
				return false;
			}
			break;
		}
		return $res;
	}
}

class RestAppVAVersionIntegrityItem extends RestResourceItem {
    /*** Attributes ***/

    /**
     * reference to model representing the requested resource item
     *
     */
    private $_res;

	/**
     * reference to parent collection of the model representing the requested resource item
     *
     */
	private $_resParent;

	protected function init() {
		// must call iserIsAdmin before instanciating a Default_Model_Applications, so that
		// $_GET['userid'] is properly set
		$this->_resParent = new Default_Model_VAversions();
		$this->_resParent->filter->id->equals($this->getParam("versionid"));
		if ( count($this->_resParent->items) > 0 ) {
			$this->_res = $this->_resParent->items[0];
		} else {
			$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
			$this->_res = null;
		}
		//$this->_parser = new RestAppVAXMLParser($this);
	}

	public function getDataType() {
		return "virtualization";
	}

	public function _options() {
		$options = array();
		$options[] = RestMethodEnum::RM_GET;
		if ( $this->authenticate() ) {
			$options[] = RestMethodEnum::RM_DELETE;
		}
		return $options;
	}

	public function get() {
		if (parent::get() !== false) {
			error_log("STUB: RestAppVAVersionIntegrityItem::get()");
			//error_log("[RestAppVAVersionIntegrityItem]: INTEGRITY CHECK FOR vappid: " . $this->_res->vappid . " versionid: " . $this->_res->id);
			//db()->setFetchMode(Zend_Db::FETCH_OBJ);
			//$res = db()->query("SELECT vapp_to_xml(" . $this->getParam("vappid") . ", 'vapplications') AS xml;")->fetchAll();
			$x = array();
			/*foreach($res as $r) {
				$x[] = $r->xml;
			}*/
			//$x[] = "<virtualization:lala>lolo</virtualization:lala>";
			//return new XMLRestResponse($x,$this->_parent);
			return new XMLFragmentRestResponse("<virtualization:integrity>not implemented yet</virtualization:integrity>",$this);
			//return new XMLFragmentRestResponse($x, $this);
		} else {
			return false;
		}
	}

    /**
     * overrides RestResource::delete()
     */
	public function delete() {
		if (parent::delete() !== false) {
			$ret = $this->get();
			// do the actual "DELETE" stuff... (i.e. cancel integrity check)
			error_log("STUB: RestAppVAVersionIntegrityItem::delete()");
			return $ret;
		} else return false;
	}
	
	/**
     * realization of authorize() from iRestAuthModule
     */
	public function authorize($method) {
		$res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
            $res = true;
            break;
        case RestMethodEnum::RM_PUT:
        case RestMethodEnum::RM_POST:
			$res = false;
			break;
        case RestMethodEnum::RM_DELETE:
			if ( ! is_null($this->_res) ) {
				if (  ! is_null($this->getUser()) ) {
					if ( $this->getUser()->privs->canManageVAs($this->_res->application) ) {
						$res = true;
					} else {
						$this->setError(RestErrorEnum::RE_ACCESS_DENIED);
						return false;
					}
				} else {
					$this->setError(RestErrorEnum::RE_ACCESS_DENIED);
					return false;
				}
			} else {
				$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
				return false;
			}
			break;
		}
		return $res;
	}
}

class RestAppPrivList extends RestROAuthResourceList {
	public function get() {
		if ( parent::get() !== false ) {
			db()->setFetchMode(Zend_Db::FETCH_NUM);
			$res = db()->query("SELECT app_target_privs_to_xml(?, ?)", array($this->getParam("id"), $this->_userid))->fetchAll();
			$ret = array();
			foreach ($res as $r) {
				$ret[] = $r[0];
			}
			return new XMLFragmentRestResponse($ret, $this);
		} else return false;
	}	

	protected function _list() {
		return $this->get();
	}

	public function getDataType() {
		return "privileges";
	}
}

class RestVAImageList extends RestROResourceList {
    /**
     * realization of getDataType from iRestResource
     */
    public function getDataType() {
        return "virtualization";
    }
    
    protected function _list() {
        return $this->get();
    }

    /**
     * overrides RestResource::get
     */
	public function get() {
		if (parent::get() !== false) {
			$id = $this->getParam("id");
			if ( ! is_numeric($id) ) {
				$id = "(SELECT id FROM applications WHERE cname ILIKE '" . pg_escape_string(substr($this->getParam("id"), 2)) . "' FETCH FIRST 1 ROWS ONLY)";
			}
			db()->setFetchMode(Zend_Db::FETCH_OBJ);
			//error_log("SELECT vapp_image_providers_to_xml($id) AS xml");
			$res = db()->query("SELECT vapp_image_providers_to_xml($id) AS xml")->fetchAll();
			$ret = array();
			//error_log("c:" . count($res));
			foreach ($res as $r) {
				//error_log("r:" . var_export($r,true));
				$ret[] = $r->xml;
			}
			return new XMLFragmentRestResponse($ret, $this);
		} else {
			return false;
		}
    }
}

class RestSWAppImageList extends RestROResourceList {
    /**
     * realization of getDataType from iRestResource
     */
    public function getDataType() {
        return "contextualization";
    }
    
    protected function _list() {
        return $this->get();
    }

    /**
     * overrides RestResource::get
     */
	public function get() {
		if (parent::get() !== false) {
			$id = $this->getParam("id");
			if ( ! is_numeric($id) ) {
				$id = "(SELECT id FROM applications WHERE cname ILIKE '" . pg_escape_string(substr($this->getParam("id"), 2)) . "' FETCH FIRST 1 ROWS ONLY)";
			}
			db()->setFetchMode(Zend_Db::FETCH_OBJ);
			$res = db()->query("SELECT swapp_image_providers_to_xml($id) AS xml")->fetchAll();
			$ret = array();
			foreach ($res as $r) {
				$ret[] = $r->xml;
			}
			return new XMLFragmentRestResponse($ret, $this);
		} else {
			return false;
		}
    }
}

class RestVAppSWAppList extends RestROResourceList{
	/**
     * realization of getDataType from iRestResource
     */
    public function getDataType() {
        return "contextualization";
    }
    
    protected function _list() {
        return $this->get();
    }

    /**
     * overrides RestResource::get
     */
	public function get() {
		if (parent::get() !== false) {
			db()->setFetchMode(Zend_Db::FETCH_OBJ);
			$res = db()->query("SELECT vapps_of_swapps_to_xml.xml from vapps_of_swapps_to_xml")->fetchAll();
			$ret = array();
			foreach ($res as $r) {
				$ret[] = $r->xml;
			}
			return new XMLFragmentRestResponse($ret, $this);
		} else {
			return false;
		}
    }
}
