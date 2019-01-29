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

class Api02actionController extends AbstractActionController
{
    public function __construct()
	{
		$this->view = new ViewModel();
        $this->session = new \Zend\Session\Container('base');
        $this->apisession = new \Zend\Session\Container('api');
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addActionContext('index', 'xml')
			->addActionContext('details', 'xml')
			->initContext();
		# this line is needed in order for moderated applications to be managed correctly by managers/admins
		if ( ! isset($_GET['userid']) ) if ( $this->session->userid !== null ) $_GET["userid"] = $this->session->userid;
    }

	private function accessDenied() {
		$this->getResponse()->getHeaders()->clearHeaders();
		header("HTTP/1.0 403 Forbidden");
	}

	public function relatedappsAction() {
   		$format = GET_REQUEST_PARAM($this, "format");
		if ( $format === "json" ) $format = "xml";
		$offset = GET_REQUEST_PARAM($this, 'ofs');
		$length = GET_REQUEST_PARAM($this, 'len');
		$paging = true;
		if ($length == "-1") $paging = false;
		$apps = new Default_Model_RelatedApplications(GET_REQUEST_PARAM($this, "id"));
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
		return DISABLE_LAYOUT($this);
	}
    
    public function myappsindexAction()	{
		if ( $this->view->isAdmin || ($this->view->Authenticated && $this->getRequest()->getParam['id'] == $_GET['id'] ) || ($this->session->userid !== null && $this->getRequest()->getParam['id'] == $this->session->userid) || userIsAdminOrManager($this->session->userid) )  {
			$format = GET_REQUEST_PARAM($this, "format");
			if ( $format === "json" ) $format = "xml";
			$this->view->subindex = "myappsindex";
			if ( $this->session->userid !== null ) {
				$this->appindex(2);
			} else {
				if ( $format == 'xml' ) {
					$this->session->userid = GET_REQUEST_PARAM($this, "id");
					$this->appindex(2, true, 'xml');
				}
			}
		} else {
			$this->accessDenied();
			DISABLE_LAYOUT($this);
			return SET_NO_RESPONSE($this, NULL, 403);
		}
		return DISABLE_LAYOUT($this);
    }   
     
    public function myownindexAction() {
		if ( $this->view->isAdmin || ($this->view->Authenticated && $this->getRequest()->getParam['id'] == $_GET['id'] ) || ($this->session->userid !== null && $this->getRequest()->getParam['id'] == $this->session->userid) || userIsAdminOrManager($this->session->userid) )  {
			$format = GET_REQUEST_PARAM($this, "format");
			if ( $format === "json" ) $format = "xml";
			$this->view->subindex = "myownindex";
			if ( $this->session->userid !== null ) {
				$this->appindex(1);
			} else {
				if ( $format == 'xml' ) {
					$this->session->userid = GET_REQUEST_PARAM($this, "id");
					$this->appindex(1, true, 'xml');
				}
			}
		} else {
			$this->accessDenied();
			DISABLE_LAYOUT($this);
			return SET_NO_RESPONSE($this, NULL, 403);
		}
		return DISABLE_LAYOUT($this);
    }    

    public function myeditindexAction() {
		if ( $this->view->isAdmin || ($this->view->Authenticated && $this->getRequest()->getParam['id'] == $_GET['id'] ) || ($this->session->userid !== null && $this->getRequest()->getParam['id'] == $this->session->userid) || userIsAdminOrManager($this->session->userid) )  {
			$format = GET_REQUEST_PARAM($this, "format");
			if ( $format === "json" ) $format = "xml";
			$this->view->subindex = "myeditindex";
			if ( $this->session->userid !== null ) {
				$this->appindex(4);
			} else {
				if ( $format == 'xml' ) {
					$this->session->userid = GET_REQUEST_PARAM($this, "id");
					$this->appindex(4, true, 'xml');
				}
			}
		} else {
			$this->accessDenied();
			DISABLE_LAYOUT($this);
			return SET_NO_RESPONSE($this, NULL, 403);
		}
		return DISABLE_LAYOUT($this);
    }

    public function modindexAction() {
		if ( $this->view->isAdmin || userIsAdminOrManager($this->session->userid) ) {
			$format = GET_REQUEST_PARAM($this, "format");
			if ( $format === "json" ) $format = "xml";
			$this->view->subindex = "modindex";
			if ( $this->session->userid !== null ) {
				$this->appindex(5);
			} else {
				if ( $format == 'xml' ) {
					$this->appindex(5, true, 'xml');
				}
			}
		} else {
			$this->accessDenied();
			DISABLE_LAYOUT($this);
			return SET_NO_RESPONSE($this, NULL, 403);
		}
		return DISABLE_LAYOUT($this);
    }

    public function delindexAction() {
		if ( $this->view->isAdmin || userIsAdminOrManager($this->session->userid) ) {
			$format = GET_REQUEST_PARAM($this, "format");
			if ( $format === "json" ) $format = "xml";
			$this->view->subindex = "delindex";
			if ( $this->session->userid !== null ) {
				$this->appindex(6);
			} else {
				if ( $format == 'xml' ) {
					$this->appindex(5, true, 'xml');
				}
			}
		} else {
			$this->accessDenied();
   			DISABLE_LAYOUT($this);
			return SET_NO_RESPONSE($this, NULL, 403);
		}
		return DISABLE_LAYOUT($this);
	}

    public function bmindexAction() {
		if ( $this->view->isAdmin || ($this->view->Authenticated && $this->getRequest()->getParam['id'] == $_GET['id'] ) || ($this->session->userid !== null && $this->getRequest()->getParam['id'] == $this->session->userid) || userIsAdminOrManager($this->session->userid) )  {
			$format = GET_REQUEST_PARAM($this, "format");
			if ( $format === "json" ) $format = "xml";
			$this->view->subindex = "bmindex";
			if ( $this->session->userid !== null ) {
				$this->appindex(3);
			} else {
				if ( $format == 'xml' ) {
					if ( GET_REQUEST_PARAM($this, "id") != '' ) {
						$this->session->userid = GET_REQUEST_PARAM($this, "id");
						$this->appindex(3, true, 'xml');
					}
				}
			}
		} else {
			$this->accessDenied();
   			DISABLE_LAYOUT($this);
			return SET_NO_RESPONSE($this, NULL, 403);
		}
		return DISABLE_LAYOUT($this);
    }

	private function getFltStr() {
		$f = GET_REQUEST_PARAM($this, 'flt');
		/*if ( $f === null ) $f = $this->session->appFlt; else*/ $f = trim($f);
		return $f;
	}
	
	private function cachelogos($items) {
		foreach ($items as $item) {
			$f = fopen($_SERVER['APPLICATION_PATH'] . "/../cache/app-logo-".$item->id.".png","w");
			if (!isnull($item->logo)) {
				$logo = base64_decode(pg_unescape_bytea($item->logo));
				fwrite($f, $logo);
			} else {
				fwrite($f, 'NULL');
			}
			fclose($f);
		}
	}

	private function appindex($mode, $paging = true, $format = '') {
		if ( $format == '') $format = GET_REQUEST_PARAM($this, "format");
		if ( $format == "json" ) $format="xml";
		if ($this->session->viewMode === null) $this->session->viewMode = 1;
		$this->view->viewMode = $this->session->viewMode;
		$offset = GET_REQUEST_PARAM($this, 'ofs');
		$length = GET_REQUEST_PARAM($this, 'len');
		if ($length == "-1") $paging = false;
		if ( $offset === null) $offset = 0;
		if ( $length === null ) $length = 23;
		if ( array_key_exists("filter", $_GET )) $this->session->appFlt = null;
		$fltstr = $this->getFltStr();
		$apps = new Application\Model\Applications();
		$this->session->appFlt = $fltstr;
		$apps->filter = FilterParser::getApplications($fltstr,(GET_REQUEST_PARAM($this, "fuzzySearch") == '1'));
		if ( $mode == 1 ) {
			$f = new Application\Model\ApplicationsFilter();
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
			$f = new Application\Model\ApplicationsFilter();
			$f->moderated->equals(true)->and($f->deleted->equals(false));
			$apps->filter->chain($f,"AND");
		}
		if ( $mode == 6 ) {
			$f = new Application\Model\ApplicationsFilter();
			$f->deleted->equals(true);
			$apps->filter->chain($f,"AND");
		}
		if ( GET_REQUEST_PARAM($this, "orderby") != '' ) $orderby = GET_REQUEST_PARAM($this, "orderby"); else $orderby = "name";
		if ( GET_REQUEST_PARAM($this, "orderbyOp") != '' ) $orderbyop = GET_REQUEST_PARAM($this, "orderbyOp"); else $orderbyop = "ASC";
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
		$apps->refresh($format, GET_REQUEST_PARAM($this, 'details'));
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
		if (GET_REQUEST_PARAM($this, "fuzzySearch") == '1') $this->view->fuzzySearch = '1';
		return $apps->items;
	}

    public function appindexAction() {
		$this->appindex(0);
		return DISABLE_LAYOUT($this);
    }

	public function ratingsAction() {
   		$format = GET_REQUEST_PARAM($this, "format");
		if ( $format === "json" ) $format = "xml";
		$ratings = new Default_Model_AppRatings();
		$ratings->filter->appid->equals(GET_REQUEST_PARAM($this, "id"));
		$this->view->entries = $ratings->refresh($format)->items;
		return DISABLE_LAYOUT($this);
	}

	public function ratingsreportAction() {
		global $application;
		$t = GET_REQUEST_PARAM($this, "type");
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
		$id = GET_REQUEST_PARAM($this, "id");
		$p = $id.($t != ''?",$t":"");
		$db = $application->getBootstrap()->getResource('db');
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$r = $db->query('SELECT apprating_report_to_xml('.$p.');')->fetchAll();
		$this->view->entries = $r[0]->apprating_report_to_xml;
		return DISABLE_LAYOUT($this);
	}

    public function appdetailsAction() {
		$appID = GET_REQUEST_PARAM($this, "id");
		$format = GET_REQUEST_PARAM($this, "format");
		if ( $format === "json" ) $format = "xml";
		if ( ($appID == '') ) $appID = $this->session->lastAppID;
		if ( $appID == "0" ) {
			$this->view->entry = new Application\Model\Application();
			$this->view->entry->name = 'New Application/Tool';
			$this->view->entry->description = '';
			$this->view->entryid = 0;
		} else {
			if ( $format === "xml" ) {
				$apps = new Application\Model\Applications();
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
		return DISABLE_LAYOUT($this);
    }
   
	public function validateappfilterAction() {
		$this->view->entries = validateFilterActionHelper(GET_REQUEST_PARAM($this, "flt"), FilterParser::NORM_APP, "0.2");
		return DISABLE_LAYOUT($this);
	}

	public function validatevosfilterAction() {
		$this->view->entries = validateFilterActionHelper(GET_REQUEST_PARAM($this, "flt"), FilterParser::NORM_VOS, "0.2");
		return DISABLE_LAYOUT($this);
	}

	public function reflectvosfilterAction() {
		$s = '<vo:filter xmlns:vo="http://appdb.egi.eu/api/0.2/vo" xmlns:filter="http://appdb.egi.eu/api/0.2/filter">';
		$s .= FilterParser::fieldsToXML("any application person country vo discipline subdiscipline middleware", "vo");
		$s .= '</vo:filter>';
		$this->view->entries = $s;
		return DISABLE_LAYOUT($this);
	}

	public function reflectappfilterAction() {
		$s = '<application:filter xmlns:application="http://appdb.egi.eu/api/0.2/application" xmlns:filter="http://appdb.egi.eu/api/0.2/filter">';
		$s .= FilterParser::fieldsToXML("any application person country vo discipline subdiscipline middleware", "application");
		$s .= '</application:filter>';
		$this->view->entries = $s;
		return DISABLE_LAYOUT($this);
	}

    public function showimageAction() {
		$ppl = new Default_Model_Researchers();
		$ppl->filter->id->equals(GET_REQUEST_PARAM($this, "id"));
		if ( count($ppl->items) > 0 ) {
			$person = $ppl->items[0];
			if ( isnull($person->image) ) {
				$this->view->image = "/images/" . "person.png";
			} else {
				$this->view->image = "/people/getimage?id=".$person->id."&req=".urlencode($person->lastUpdated);
			}
		} else $this->view->image = '';
		return DISABLE_LAYOUT($this);
    }

 	private function cacheimages($items) {
		foreach ($items as $item) {
			$f = fopen($_SERVER['APPLICATION_PATH'] . "/../cache/ppl-image-".$item->id.".png","w");
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
		$this->pplindex();
		return DISABLE_LAYOUT($this);
	}

	public function pplindex( $paging = true, $format = '' ) {
		if ($format == '' ) $format = GET_REQUEST_PARAM($this, "format");
		$offset = GET_REQUEST_PARAM($this, 'ofs');
		$length = GET_REQUEST_PARAM($this, 'len');
		if ( $length == "-1" ) $paging = false;
		if ( $offset === null) $offset = 0;
		if ( $length === null ) $length = 23;
		if ( array_key_exists("filter", $_GET )) $this->session->pplFlt = null;
		$fltstr = $this->getFltStr();
		$ppl = new Default_Model_Researchers();
		$this->session->pplFlt = $fltstr;
		$ppl->filter = FilterParser::getPeople($fltstr, (GET_REQUEST_PARAM($this, "fuzzySearch") == '1'));
		if ( GET_REQUEST_PARAM($this, "orderby") != '' ) $orderby = GET_REQUEST_PARAM($this, "orderby"); else $orderby = "firstname";
		if ( GET_REQUEST_PARAM($this, "orderbyOp") != '' ) $orderbyop = GET_REQUEST_PARAM($this, "orderbyOp"); else $orderbyop = "ASC";
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
		$listType = GET_REQUEST_PARAM($this, "details");
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
		if (GET_REQUEST_PARAM($this, "fuzzySearch") == '1') $this->view->fuzzySearch = '1';
		return $ppl->items;
    }

    public function ppldetailsAction() {
        $pplID = GET_REQUEST_PARAM($this, "id");
        if ( $pplID == '' ) $pplID = $this->session->lastPplID;
        $apps = new Default_Model_Researchers();
        if ( GET_REQUEST_PARAM($this, "id") == "0" ) {
                $this->view->entry = new Default_Model_Researcher();
                $this->view->entry->countryID = '0';
        } else {
				$apps->filter->id->equals($pplID);
				$apps->refresh(GET_REQUEST_PARAM($this, 'format'), true, GET_REQUEST_PARAM($this, 'userid'));
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
		return DISABLE_LAYOUT($this);
    }
	
  	public function reflectpplfilterAction() {
		$s = '<person:filter xmlns:person="http://appdb.egi.eu/api/0.2/person" xmlns:filter="http://appdb.egi.eu/api/0.2/filter">';
		$s .= FilterParser::fieldsToXML("any person country application vo discipline subdiscipline middleware", "person");
		$s .= '</person:filter>';
		$this->view->entries = $s;
		return DISABLE_LAYOUT($this);
	}

	public function validatepplfilterAction() {
		$this->view->entries = validateFilterActionHelper(GET_REQUEST_PARAM($this, "flt"), FilterParser::NORM_PPL, "0.2");
		return DISABLE_LAYOUT($this);
	}
	
	public function disseminationlogAction() {
		if ( $this->view->isAdmin || userIsAdminOnManager($this->session->userid) ) {
			$ds = new Default_Model_Dissemination();
			if ( GET_REQUEST_PARAM($this, "orderby") == "" ) {
				$ds->filter->orderBy("senton DESC");
			} else {
				$ds->filter->orderBy(GET_REQUEST_PARAM($this, "orderby"));
			}
			if ( GET_REQUEST_PARAM($this, "id") != "" ) $ds->filter->id = GET_REQUEST_PARAM($this, "id");
			$ds->refresh('xml');
			$this->view->entries = $ds->items;
		} else {
			$this->accessDenied();
   			DISABLE_LAYOUT($this);
			return SET_NO_RESPONSE($this, NULL, 403);
		}
		return DISABLE_LAYOUT($this);
	}
}
