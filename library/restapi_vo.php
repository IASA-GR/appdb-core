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

class RestVoReport extends RestROSelfAuthResourceList {
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
			$res = db()->query("SELECT * FROM ppl_vo_xml_report(?, ?, ?, 'listing')", array($this->getParam("id"), $this->_pageLength, $this->_pageOffset))->fetchAll();
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
			$res = db()->query("SELECT * FROM ppl_vo_xml_report(?, ?, ?, ?)", array($this->getParam("id"), $this->_pageLength, $this->_pageOffset, $this->_listMode))->fetchAll();
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

/**
 * class RestVOList
 * derived class for lists of VOs
 */
class RestVOList extends RestROResourceList {
    /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
    public function getDataType() {
        return "vo";
    }
    
    protected function _list() {
   		$ret = array();
		$this->_model->refresh();
		for ($i=0; $i < count($this->_model->items); $i++) {
			$ret[] = '<vo:vo xmlns:vo="' . RestAPIHelper::XMLNS_VO() . '" id="'.$this->_model->items[$i]->id.'" >'.$this->_model->items[$i]->name.'</vo:vo>';
		}
		return new XMLFragmentRestResponse($ret, $this);
    }

    /**
     * overrides RestResource::getModel()
     */
	protected function getModel() {
		$res = new Default_Model_VOs();
		if ( $this->getParam("flt") != "" ) {
			$res->filter = FilterParser::getVOs($this->getParam("flt"));
		}
		return $res;
	}
}

/**
 * class RestVOItem
 * derived class for individual VO items
 */
class RestVOItem extends RestROResourceItem {
    /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
    public function getDataType() {
        return "vo";
    }
    
    /**
     * overrides RestResource::getModel()
     */
	protected function getModel() {
		$res = new Default_Model_VOs();
		$id = $this->getParam("id");
		if ( is_numeric($id) ) {
	        $res->filter->id->equals($id);
		} else {
	        $res->filter->name->equals(preg_replace('/^s:/', '', $id));
		}
		return $res;
    }

//    public function get() {
//        if ( parent::get() !== false ) {
//            $res = new Default_Model_VOs();
//			// "id" parameter may either be ID in database or name
//			// keep database ID (numeric) in "nid" variable
//			// leave "id" variable for name
//            $id = $this->getParam("id");
//            $nid = "";
//            if ( is_numeric($id) ) {
//                $nid = $id;
//                $res->filter->id->equals($id);
//                if ( count($res->items) > 0 ) {
//                    $id = $res->items[0]->name;
//                } else $id = "";
//            } else {
//                $res->filter->name->equals($id);
//                if ( count($res->items) > 0 ) {
//                    $nid = $res->items[0]->id;
//                } else $nid = "";
//            }
//            if ( $id != "" && $nid != "") {
//				try {
//	                $xml = new SimpleXMLElement(file_get_contents(RestAPIHelper::getFolder(RestFolderEnum::FE_CACHE_FOLDER)."vos.xml"));
//				} catch (Exception $e) {
//					$this->setError(RestErrorEnum::RE_BACKEND_ERROR, $e->getMessage());
//				}
//                $vo = $xml->xpath("//VoDump/IDCard[translate(@Name,'abcdefghijklmnopqrstuvwxyz','ABCDEFGHIJKLMNOPQRSTUVWXYZ')=". xpath_quote(strtoupper($id)) ."]");
//                if ( count($vo) > 0 ) {
//                    $vo = $vo[0];
//                    if (strval($vo->children()->ValidationDate) != '') {
//                        $vo->children()->ValidationDate = str_replace(' ','T',$vo->children()->ValidationDate);
//                    }
//					$xml = xml_transform(RestAPIHelper::getFolder(RestFolderEnum::FE_XSL_FOLDER)."fixvo.xsl", $vo->asXML());
//					return new XMLFragmentRestResponse(str_replace("###PUT_VO_ID_HERE###", $nid, $xml), $this);
//                } else {
//                    $this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
//                    return false;
//                }
//            } else {
//                $this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
//                return false;
//            }
//        } else return false;
//    }
}

/**
 * class RestVOFilterNormalization
 * handles vo filter syntax normalization and validation
 */
class RestVOFilterNormalization extends RestROResourceItem {
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
			return new XMLFragmentRestResponse(validateFilterActionHelper($flt, FilterParser::NORM_VOS), $this);
		} else return false;
	}
}

/**
 * class RestVOFilterReflection
 * handles vo filter reflection requests
 */
class RestVOFilterReflection extends RestROResourceItem {
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
			$s = '<vo:filter>';
			$s .= FilterParser::fieldsToXML("any application country vo discipline middleware category", "vo");
			$s .= '</vo:filter>';
			return new XMLFragmentRestResponse($s, $this);
		} else return false;
    }
}

class RestVOMemberList extends RestROSelfAuthResourceList {
	public function getDataType() {
		return "vo";
	}

	protected function _list() {
		return $this->get();
	}

	public function getModel() {
		$res = new Default_Model_VOMembers();
		if ( array_key_exists("orderby", $this->_pars) ) {
			$this->_pars["orderby"] = "unsorted";
		}
		if ( $this->getParam("flt") != "" ) {
			$res->filter = FilterParser::getVOs($this->getParam("flt"));
		}
		$f = new Default_Model_VOMembersFilter();
		$f->researcherid->equals($this->getParam("id"));
		$res->filter->chain($f, "AND");
		return $res; 
	}
}

class RestVOContactList extends RestROResourceList {
	protected $_contactType = null;

	protected function init($contactType = null) {
		if (parent::init()) {
			$this->_contactType = $contactType;
			return true;
		} else {
			return false;
		}
	}
	public function getDataType() {
		return "vo";
	}

	protected function _list() {
		return $this->get();
	}

	public function getModel() {
		$res = new Default_Model_VOContacts();
		$role = "VO " . strtoupper($this->_contactType);
		if ( array_key_exists("orderby", $this->_pars) ) {
			$this->_pars["orderby"] = "unsorted";
		}
		if ( $this->getParam("flt") != "" ) {
			$res->filter = FilterParser::getVOs($this->getParam("flt"));
		}
		$f = new Default_Model_VOContactsFilter();
		if ( ! is_null($this->_contactType) ) {			
			$f->researcherid->equals($this->getParam("id"))->and($f->role->equals($role));
		} else {
			$f->researcherid->equals($this->getParam("id"));
		}
		$res->filter->chain($f, "AND");
		return $res; 
	}
}

class RestVOManagerList extends RestVOContactList {
	protected function init($contactType = "manager") {
		parent::init($contactType);
	}
}

class RestVODeputyList extends RestVOContactList {
	protected function init($contactType = "deputy") {
		parent::init($contactType);
	}
}

class RestVOExpertList extends RestVOContactList {
	protected function init($contactType = "expert") {
		parent::init($contactType);
	}
}

class RestVOShifterList extends RestVOContactList {
	protected function init($contactType = "shifter") {
		parent::init($contactType);
	}
}

class RestPplVOList extends RestROSelfAuthResourceList {
	public function getDataType() {
		return "vo";
	}

	protected function _list() {
		return $this->get();
	}

	public function get() {
		$res1 = new RestVOMemberList($this->getParams());
		$ret1 = $res1->get();
		$res2 = new RestVOContactList($this->getParams());
		$ret2 = $res2->get();
		$data = array_merge($ret1->getData(), $ret2->getData());
		$this->total = $res1->total + $res2->total;
		return new XMLFragmentRestResponse($data, $this);
	}
}

/**
 * class RestPplLogistics
 * handles people counting per various properties
 */
class RestVOLogistics extends RestROResourceItem {
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
			$mapper = new Default_Model_VOsMapper();
			$db = $application->getBootstrap()->getResource('db');
			$flt = $this->getParam("flt");
			$select = $mapper->getDbTable()->getAdapter()->select()->distinct()->from('vos');
			$from = '';
			$where = '';
			$orderby = '';
			$limit = '';
			$filter = FilterParser::getVOs($flt);
			if ( is_array($filter->expr()) ) {
				$ex = implode(" ", $filter->expr()); 
			} else {
				$ex = $filter->expr();
			}
			$fltexp = $filter->expr();
			if ( ! is_array($fltexp) ) $fltexp = array($fltexp);
			foreach($fltexp as $x) {
				getZendSelectParts($select, $from, $where, $orderby, $limit);
				if ( ! $isAdmin ) {
					if ( (strpos($from, ' applications ') !== false) && (
						( strpos($ex, 'applications.moderated) IS FALSE') === false ) ||
						( strpos($ex, 'applications.deleted) IS FALSE') === false )
					)) {
						$f = new Default_Model_ApplicationsFilter();
						$f->moderated->equals(false)->and($f->deleted->equals(false));
						$filter->chain($f,"AND");
					}
					if ( (strpos($from, ' researchers ') !== false) && (strpos($ex, 'researchers.deleted) IS FALSE') === false) ) {
						$f = new Default_Model_ResearchersFilter();
						$f->deleted->equals(false);
						$filter->chain($f,"AND");
					}
				}
				if ( (strpos($ex, 'vos.deleted) IS FALSE') === false) ) {
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
			$rs = $db->query('SELECT * FROM vo_logistics(?,?,?)', array($flt, $from, $where))->fetchAll();
			if ( count($rs) > 0 ) {
				$rs = $rs[0];
				$x = $rs['vo_logistics'];
			} else {
				$x = '';
			}
			return new XMLFragmentRestResponse($x, $this);
		} else return false;
	}
}

class RestVOAppStatsList extends RestROResourceList {
	public function getDataType() {
		return "app_vo_stats";
	}

	protected function _doget() {
		if ( parent::get() !== false ) {
			global $application;
			if ( is_numeric($this->getParam("id")) ) {
				$void = $this->getParam("id");
			} else {
				db()->setFetchMode(Zend_Db::FETCH_BOTH);
				$void = db()->query("SELECT id FROM normalized_vos WHERE name = ? AND NOT deleted", array(preg_replace('/^s:/', '', $this->getParam("id"))))->fetchAll();
				if (is_array($void) && (count($void) > 0)) {
					$void = $void[0]; //row
					$void = $void[0]; //column
				} else {
					$void = null;
				}
			}
			if ($void == "") {
				$void = "NULL";
			} else {
				$void = (int)$void;
			}
			$from = $this->getParam("from");
			if ($from == "") {
				$from = "NULL";
			} else {
				if (validateISODate($from)) {
					$from = "'$from'::date";
				} else {
					$this->setError(RestErrorEnum::RE_INVALID_RESOURCE);
					$this->_extError = "Invalid `from' date. Valid date format is YYYY-MM-DD";
					return false;
				}
			}
			$to = $this->getParam("to");
			if ($to == "") {
				$to = "NULL";
			} else {
				if (validateISODate($to)) {
					$to = "'$to'::date";
				} else {
					$this->setError(RestErrorEnum::RE_INVALID_RESOURCE);
					$this->_extError = "Invalid `to' date. Valid date format is YYYY-MM-DD";
					return false;
				}
			}
			db()->setFetchMode(Zend_Db::FETCH_NUM);
			$res = db()->query("SELECT * FROM app_vo_stats_to_xml($void, $from, $to)")->fetchAll();
			$ret = array();
			foreach ($res as $r) {
				if (is_array($r) && (count($r) > 0)) {
					$ret[] = $r[0];
				}
			}
			if ($this->getParam("listmode") == "listing") {
				$wantsDaily = false;
			} else {
				$wantsDaily = true;
			}
			$flt = $this->getParam("flt");
			if (preg_match('/(^| )omitzerocounts[[:space:]]*:"{0,1}[[:space:]]*(1|true)/i', $flt)) {
				$wantsZeroCounts = false;
			} else {
				$wantsZeroCounts = true;
			}
			if (preg_match('/(^| )omitunchanged[[:space:]]*:"{0,1}[[:space:]]*(1|true)/i', $flt)) {
				$wantsUnchanged = false;
			} else {
				$wantsUnchanged = true;
			}
			if (! $wantsDaily) {
				$ret = preg_grep('/[[:space:]]+stats="daily"[[:space:]]+/', $ret, PREG_GREP_INVERT);
			}
			if (! $wantsZeroCounts) {
				$ret = preg_grep('/[[:space:]]+count="0"[[:space:]]/', $ret, PREG_GREP_INVERT);
			}
			if (! $wantsUnchanged) {
				$ret = preg_grep('/[[:space:]]+additions="0"[[:space:]]+removals="0"[[:space:]]+vmi_updates="0"[[:space:]]/', $ret, PREG_GREP_INVERT);
				$ret = preg_grep('/[[:space:]]+additions="0"[[:space:]]+removals="0"[[:space:]]+type/', $ret, PREG_GREP_INVERT);
			}
			$this->total = count($ret);
			return new XMLFragmentRestResponse($ret, $this);
		} else return false;
	}
	protected function _list() {
		return $this->_doget();
	}

	public function get() {
		return $this->_doget();
	}

}
