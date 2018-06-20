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

class Api02actionController extends Zend_Controller_Action
{
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

	private function accessDenied() {
		$this->getResponse()->clearAllHeaders();
		header("HTTP/1.0 403 Forbidden");
	}

	public function relatedappsAction() {
   		$format = $this->_getParam("format");
		if ( $format === "json" ) $format = "xml";
		$this->_helper->layout->disableLayout();
		$offset = $this->_getParam('ofs');
		$length = $this->_getParam('len');
		$paging = true;
		if ($length == "-1") $paging = false;
		$apps = new Default_Model_RelatedApplications($this->_getParam("id"));
		$total = $apps->count();
		if ( $paging ) {
			if ($length != '') $apps->limit = $length+1;
			if ($offset != '') $apps->offset = $offset;
		}
		$this->view->entries = $apps->refresh($format)->items;
		if ( $paging ) {
			$this->view->offset = $offset;
			$this->view->length = $length;
			$this->view->pageCount = ceil($total / ($length+1));
			$this->view->currentPage = floor($offset / ($length+1));
		} else {
			$this->view->offset = 0;
			$this->view->length = $this->view->total;
			$this->view->pageCount=1;
			$this->view->currentPage=1;
		}
		$this->view->total = $total;
	}
    
    public function myappsindexAction()
    {
		if ( $this->view->isAdmin || ($this->view->Authenticated && $this->_getParam['id'] == $_GET['id'] ) || ($this->session->userid !== null && $this->_getParam['id'] == $this->session->userid) || userIsAdminOrManager($this->session->userid) )  {
			$format = $this->_getParam("format");
			if ( $format === "json" ) $format = "xml";
			$this->_helper->layout->disableLayout();
			$this->view->subindex = "myappsindex";
			if ( $this->session->userid !== null ) {
				$this->appindex(2);
			} else {
				if ( $format == 'xml' ) {
					$this->session->userid = $this->_getParam("id");
					$this->appindex(2, true, 'xml');
				}
			}
		} else $this->accessDenied();
    }   
     
    public function myownindexAction() {
		if ( $this->view->isAdmin || ($this->view->Authenticated && $this->_getParam['id'] == $_GET['id'] ) || ($this->session->userid !== null && $this->_getParam['id'] == $this->session->userid) || userIsAdminOrManager($this->session->userid) )  {
			$format = $this->_getParam("format");
			if ( $format === "json" ) $format = "xml";
			$this->_helper->layout->disableLayout();
			$this->view->subindex = "myownindex";
			if ( $this->session->userid !== null ) {
				$this->appindex(1);
			} else {
				if ( $format == 'xml' ) {
					$this->session->userid = $this->_getParam("id");
					$this->appindex(1, true, 'xml');
				}
			}
		} else $this->accessDenied();
    }    

    public function myeditindexAction()
    {
		if ( $this->view->isAdmin || ($this->view->Authenticated && $this->_getParam['id'] == $_GET['id'] ) || ($this->session->userid !== null && $this->_getParam['id'] == $this->session->userid) || userIsAdminOrManager($this->session->userid) )  {
			$format = $this->_getParam("format");
			if ( $format === "json" ) $format = "xml";
			$this->_helper->layout->disableLayout();
			$this->view->subindex = "myeditindex";
			if ( $this->session->userid !== null ) {
				$this->appindex(4);
			} else {
				if ( $format == 'xml' ) {
					$this->session->userid = $this->_getParam("id");
					$this->appindex(4, true, 'xml');
				}
			}
		} else $this->accessDenied();
    }

    public function modindexAction()
    {
		if ( $this->view->isAdmin || userIsAdminOrManager($this->session->userid) ) {
			$format = $this->_getParam("format");
			if ( $format === "json" ) $format = "xml";
			$this->_helper->layout->disableLayout();
			$this->view->subindex = "modindex";
			if ( $this->session->userid !== null ) {
				$this->appindex(5);
			} else {
				if ( $format == 'xml' ) {
					$this->appindex(5, true, 'xml');
				}
			}
		} else $this->accessDenied();
    }

    public function delindexAction()
    {
		if ( $this->view->isAdmin || userIsAdminOrManager($this->session->userid) ) {
			$format = $this->_getParam("format");
			if ( $format === "json" ) $format = "xml";
			$this->_helper->layout->disableLayout();
			$this->view->subindex = "delindex";
			if ( $this->session->userid !== null ) {
				$this->appindex(6);
			} else {
				if ( $format == 'xml' ) {
					$this->appindex(5, true, 'xml');
				}
			}
		} else $this->accessDenied();
    }

    public function bmindexAction()
    {
		if ( $this->view->isAdmin || ($this->view->Authenticated && $this->_getParam['id'] == $_GET['id'] ) || ($this->session->userid !== null && $this->_getParam['id'] == $this->session->userid) || userIsAdminOrManager($this->session->userid) )  {
			$format = $this->_getParam("format");
			if ( $format === "json" ) $format = "xml";
			$this->_helper->layout->disableLayout();
			$this->view->subindex = "bmindex";
			if ( $this->session->userid !== null ) {
				$this->appindex(3);
			} else {
				if ( $format == 'xml' ) {
					if ( $this->_getParam("id") != '' ) {
						$this->session->userid = $this->_getParam("id");
						$this->appindex(3, true, 'xml');
					}
				}
			}
		} else $this->accessDenied();
    }

	private function getFltStr() {
		$f = $this->_getParam('flt');
		/*if ( $f === null ) $f = $this->session->appFlt; else*/ $f = trim($f);
		return $f;
	}
	
	private function cachelogos($items) {
		foreach ($items as $item) {
			$f = fopen(APPLICATION_PATH . "/../cache/app-logo-".$item->id.".png","w");
			if (!isnull($item->logo)) {
				$logo = base64_decode(pg_unescape_bytea($item->logo));
				fwrite($f, $logo);
			} else {
				fwrite($f, 'NULL');
			}
			fclose($f);
		}
	}

	private function appindex($mode, $paging = true, $format = '')
	{
		if ( $format == '') $format = $this->_getParam("format");
		if ( $format == "json" ) $format="xml";
		if ($this->session->viewMode === null) $this->session->viewMode = 1;
		$this->view->viewMode = $this->session->viewMode;
		$this->_helper->layout->disableLayout();
		$offset = $this->_getParam('ofs');
		$length = $this->_getParam('len');
		if ($length == "-1") $paging = false;
		if ( $offset === null) $offset = 0;
		if ( $length === null ) $length = 23;
		if ( array_key_exists("filter", $_GET )) $this->session->appFlt = null;
		$fltstr = $this->getFltStr();
		$apps = new Default_Model_Applications();
		$this->session->appFlt = $fltstr;
		$apps->filter = FilterParser::getApplications($fltstr,($this->_getParam("fuzzySearch") == '1'));
		if ( $mode == 1 ) {
			$f = new Default_Model_ApplicationsFilter();
			$f->addedby->equals($this->session->userid);
			$apps->filter->chain($f,"AND");
		}
		if ( $mode == 2 ) {
			$f = new Default_Model_ResearchersFilter();
			$f->id->equals($this->session->userid);
			$apps->filter->chain($f,"AND");
		}
		if ( $mode == 3 ) {
			$f = new Default_Model_AppBookmarksFilter();
			$f->researcherid->equals($this->session->userid);
			$apps->filter->chain($f,"AND");		
		}
		if ( $mode == 4 ) {
			$f = new Default_Model_PermissionsFilter();
			$f->actor->equals($this->session->userguid)->and($f->actionid->between(array(3,17))->or($f->actionid->equals(20)));
			$apps->filter->chain($f,"AND");
		}
		if ( $mode == 5 ) {
			$f = new Default_Model_ApplicationsFilter();
			$f->moderated->equals(true)->and($f->deleted->equals(false));
			$apps->filter->chain($f,"AND");
		}
		if ( $mode == 6 ) {
			$f = new Default_Model_ApplicationsFilter();
			$f->deleted->equals(true);
			$apps->filter->chain($f,"AND");
		}
		if ( $this->_getParam("orderby") != '' ) $orderby = $this->_getParam("orderby"); else $orderby = "name";
		if ( $this->_getParam("orderbyOp") != '' ) $orderbyop = $this->_getParam("orderbyOp"); else $orderbyop = "ASC";
		if ( $orderby != '' ) {
			if ($orderby != "unsorted") {
				$apps->filter->orderBy($orderby." ".$orderbyop);
			}
			$this->view->orderby = $orderby;
			$this->view->orderbyOp = $orderbyop;
		}
		if ( $paging ) {
			$apps->filter->limit($length+1);
			$apps->filter->offset($offset);
		}
		$t1 = microtime(true);
		$total = $apps->count();
		$apps->refresh($format, $this->_getParam('details'));
		$entries = $apps->items;
		if ( $format == '' ) $this->cachelogos($entries);
		if ( $paging ) {
			$this->view->offset = $offset;
			$this->view->length = $length;
			$this->view->pageCount = ceil($total / ($length+1));
			$this->view->currentPage = floor($offset / ($length+1));
		} else {
			$this->view->offset = 0;
			$this->view->length = $total;
			if ($total != 0) {
				$this->view->pageCount = ceil($total / ($total)); 
				$this->view->currentPage = floor($offset / ($total));
			} else {
				$this->view->pageCount = 1;
				$this->view->currentPage = 1;
			}
		}
		$this->view->entries = $entries;
		$this->view->total = $total;
		$this->view->gid = null;
		$t2 = microtime(true);
		$dt = $t2 - $t1;
		$this->view->searchTime = round($dt,2);
		if ($this->_getParam("fuzzySearch") == '1') $this->view->fuzzySearch = '1';
		return $apps->items;
	}

    public function appindexAction()
    {
		$this->_helper->layout->disableLayout();
		$this->appindex(0);
    }

	public function ratingsAction() {
   		$format = $this->_getParam("format");
		if ( $format === "json" ) $format = "xml";
		$this->_helper->layout->disableLayout();
		$ratings = new Default_Model_AppRatings();
		$ratings->filter->appid->equals($this->_getParam("id"));
		$this->view->entries = $ratings->refresh($format)->items;
	}

	public function ratingsreportAction(){
		global $application;
		$this->_helper->layout->disableLayout();
		$t = $this->_getParam("type");
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
		$id = $this->_getParam("id");
		$p = $id.($t != ''?",$t":"");
		$db = $application->getBootstrap()->getResource('db');
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$r = $db->query('SELECT apprating_report_to_xml('.$p.');')->fetchAll();
		$this->view->entries = $r[0]->apprating_report_to_xml;
	}

    public function appdetailsAction()
	{
		$this->_helper->layout->disableLayout();
		$appID = $this->_getParam("id");
		$format = $this->_getParam("format");
		if ( $format === "json" ) $format = "xml";
		if ( ($appID == '') ) $appID = $this->session->lastAppID;
		if ( $appID == "0" ) {
			$this->view->entry = new Default_Model_Application();
			$this->view->entry->name = 'New Application/Tool';
			$this->view->entry->description = '';
			$this->view->entryid = 0;
		} else {
			if ( $format === "xml" ) {
				$apps = new Default_Model_Applications();
				$apps->filter->id->equals($appID);
				$apps->refresh($format, true);
				if ($apps->count() > 0) $this->view->entry = $apps->items[0];
			} else {
				$this->view->entry = null; //$app;
				$this->view->entryid = $appID;
			}
		}
		$this->view->dialogCount=$_GET['dc'];	
		if ( $this->session->username !== null ) {
		} else $this->view->user = null;
		$this->view->session = $this->session;
    }
   
	public function validateappfilterAction() {
		$this->view->entries = validateFilterActionHelper($this->_getParam("flt"), FilterParser::NORM_APP, "0.2");
	}

	public function validatevosfilterAction() {
		$this->view->entries = validateFilterActionHelper($this->_getParam("flt"), FilterParser::NORM_VOS, "0.2");
	}

	public function reflectvosfilterAction() {
		$s = '<vo:filter xmlns:vo="http://appdb.egi.eu/api/0.2/vo" xmlns:filter="http://appdb.egi.eu/api/0.2/filter">';
		$s .= FilterParser::fieldsToXML("any application person country vo discipline subdiscipline middleware", "vo");
		$s .= '</vo:filter>';
		$this->view->entries = $s;
	}

	public function reflectappfilterAction() {
		$s = '<application:filter xmlns:application="http://appdb.egi.eu/api/0.2/application" xmlns:filter="http://appdb.egi.eu/api/0.2/filter">';
		$s .= FilterParser::fieldsToXML("any application person country vo discipline subdiscipline middleware", "application");
		$s .= '</application:filter>';
		$this->view->entries = $s;
	}

    public function showimageAction()
    {
		$this->_helper->layout->disableLayout();
		$ppl = new Default_Model_Researchers();
		$ppl->filter->id->equals($this->_getParam("id"));
		if ( count($ppl->items) > 0 ) {
			$person = $ppl->items[0];
			if ( isnull($person->image) ) {
				$this->view->image = "/images/" . "person.png";
			} else {
				$this->view->image = "/people/getimage?id=".$person->id."&req=".urlencode($person->lastUpdated);
			}
		} else $this->view->image= '';
    }

 	private function cacheimages($items) {
		foreach ($items as $item) {
			$f = fopen(APPLICATION_PATH . "/../cache/ppl-image-".$item->id.".png","w");
			$image = base64_decode(pg_unescape_bytea($item->image));
			if (is_string($image)) {
				fwrite($f, $image);
			} else {
				fwrite($f, 'NULL');
			}
			fclose($f);
		}
	}
	
	public function pplindexAction() {
		$this->_helper->layout->disableLayout();
		$this->pplindex();
	}

	public function pplindex( $paging = true, $format = '' )
    {
		if ($format == '' ) $format = $this->_getParam("format");
		$offset = $this->_getParam('ofs');
		$length = $this->_getParam('len');
		if ( $length == "-1" ) $paging = false;
		if ( $offset === null) $offset = 0;
		if ( $length === null ) $length = 23;
		if ( array_key_exists("filter", $_GET )) $this->session->pplFlt = null;
		$fltstr = $this->getFltStr();
		$ppl = new Default_Model_Researchers();
		$this->session->pplFlt = $fltstr;
		$ppl->filter = FilterParser::getPeople($fltstr, ($this->_getParam("fuzzySearch") == '1'));
		if ( $this->_getParam("orderby") != '' ) $orderby = $this->_getParam("orderby"); else $orderby = "firstname";
		if ( $this->_getParam("orderbyOp") != '' ) $orderbyop = $this->_getParam("orderbyOp"); else $orderbyop = "ASC";
		if ( $orderby != '' ) {
			if ($orderby != "unsorted") {
				if ( $orderby == "firstname" ) $ppl->filter->orderBy(array("firstname ".$orderbyop,"lastname ".$orderbyop));
				elseif ( $orderby == "lastname" ) $ppl->filter->orderBy(array("lastname ".$orderbyop,"firstname ".$orderbyop));
				else $ppl->filter->orderBy($orderby." ".$orderbyop);
			}
			$this->view->orderby = $orderby;
			$this->view->orderbyOp = $orderbyop;
		}
		if ( $paging ) {
			$ppl->filter->limit($length+1);
			$ppl->filter->offset($offset);
		}
		$t1 = microtime(true);
		$total = $ppl->count();
		$listType = $this->_getParam("details");
		$ppl->refresh($format, '', $listType);
		$entries = $ppl->items;
		if ( $format == '' ) $this->cacheimages($entries);
		if ( $paging ) {
			$this->view->offset = $offset;
			$this->view->length = $length;
			$this->view->pageCount = ceil($total / ($length+1));
			$this->view->currentPage = floor($offset / ($length+1));
		} else {
			$this->view->offset = 0;
			$this->view->length = $total;
			if ($total != 0) { 
				$this->view->pageCount = ceil($total / ($total)); 
				$this->view->currentPage = floor($offset / ($total));
			} else {
				$this->view->pageCount = 1; 
				$this->view->currentPage = 1; 
			}    
		}
		$this->view->entries = $entries;
		$this->view->total = $total;
		include 'pplaccounting.php';
		$t2 = microtime(true);
		$dt = $t2 - $t1;
		$this->view->searchTime = round($dt,2);
		if ($this->_getParam("fuzzySearch") == '1') $this->view->fuzzySearch = '1';
		return $ppl->items;
    }

    public function ppldetailsAction()
    {
        $pplID = $this->_getParam("id");
        if ( $pplID == '' ) $pplID = $this->session->lastPplID;
        $this->_helper->layout->disableLayout();
        $apps = new Default_Model_Researchers();
        if ( $this->_getParam("id") == "0" ) {
                $this->view->entry = new Default_Model_Researcher();
                $this->view->entry->countryID = '0';
        } else {
				$apps->filter->id->equals($pplID);
				$apps->refresh($this->_getParam('format'), true, $this->_getParam('userid'));
				$this->view->entry = $apps->items[0];
				$this->showimageAction();
        }
        $this->view->dialogCount=$_GET['dc'];
		$this->view->positionTypes = new Default_Model_PositionTypes();
		$this->view->positionTypes->filter->orderBy('ord');
		$this->view->countries = new Default_Model_Countries();
		$this->view->countries->filter->orderBy('name');
		$this->view->contactTypes = new Default_Model_ContactTypes();
		$this->view->session = $this->session;
	    if ( ($this->session->username !== null) && ($this->session->userid !== null) ) {
            $users = new Default_Model_Researchers();
            $users->filter->id->equals($this->session->userid);
            $this->view->user = $users->items[0];
        } else $this->view->user = null;
    }
	
  	public function reflectpplfilterAction() {
		$s = '<person:filter xmlns:person="http://appdb.egi.eu/api/0.2/person" xmlns:filter="http://appdb.egi.eu/api/0.2/filter">';
		$s .= FilterParser::fieldsToXML("any person country application vo discipline subdiscipline middleware", "person");
		$s .= '</person:filter>';
		$this->view->entries = $s;
	}

	public function validatepplfilterAction() {
		$this->view->entries = validateFilterActionHelper($this->_getParam("flt"), FilterParser::NORM_PPL, "0.2");
	}
	
	public function disseminationlogAction() {
        $this->_helper->layout->disableLayout();
		if ( $this->view->isAdmin || userIsAdminOnManager($this->session->userid) ) {
			$ds = new Default_Model_Dissemination();
			if ( $this->_getParam("orderby") == "" ) {
				$ds->filter->orderBy("senton DESC");
			} else {
				$ds->filter->orderBy($this->_getParam("orderby"));
			}
			if ( $this->_getParam("id") != "" ) $ds->filter->id = $this->_getParam("id");
			$ds->refresh('xml');
			$this->view->entries = $ds->items;
		} else $this->accessDenied();
	}
}
