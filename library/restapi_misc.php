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

/* class RestPplList
 * derived class for lists of REST XML schemata
 */
class RestSchemaList extends RestROResourceList {
    /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */	
	public function getDataType() {
		return "schema";
    }

    protected function _list() {
        return $this->get();
    }

    /**
     * overrides RestResource::get()
     */
	public function get() {
		if ( parent::get() !== false ) {
			$results = array();
            $handle = @opendir(RestAPIHelper::getFolder(RestFolderEnum::FE_XSD_FOLDER));
            if ($handle) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && (substr($file, strlen($file) - strlen("xsd"))) == "xsd") {
                        $results[] = "<appdb:schema name='" . str_replace(".xsd", "", $file) . "' uri='http://" . $_SERVER["APPLICATION_API_HOSTNAME"]  . "/rest/"  . RestAPIHelper::getVersion() . "/schema/" . str_replace(".xsd", "", $file) . "'/>";
                    }
                }       
                closedir($handle);
            }
			$this->_pageOffset = 0;
			$this->_pageLength = count($results);
			$this->_total = count($results);
            return new XMLFragmentRestResponse($results, $this);
		} else return false;
	}
}

/**
 * class RestPplItem
 * handles individual REST XML schema entries
 */
class RestSchemaItem extends RestROResourceItem {
    /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
	public function getDataType() {
		return "schema";
	}

    /**
     * overrides RestResource::get()
     */
	public function get() {
		if ( parent::get() !== false ) {
			$xsdname = $this->getParam("xsdname");
			$file =  RestAPIHelper::getFolder(RestFolderEnum::FE_XSD_FOLDER) . $xsdname . ".xsd";
            if(file_exists($file)){
                return new XMLRestResponse(file_get_contents($file), $this);
            }else{
                $this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
                return false;
            }
		} else return false;
	}
}

/**
 * class RestMWList
 * derived class for lists of middlewares
 */
class RestMWList extends RestROResourceList {
    /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
	public function getDataType() {
        return "middleware";
    }
    
    protected function _list() {
        return $this->get();
    }

//	public function get() {
//		if ( parent::get() !== false ) {
//			$res1 = new Default_Model_Middlewares();
//			$res2 = new Default_Model_AppMiddlewares();
//			$res1->refresh("xml");
//			$res2->refresh("xml");
//			$items = array_merge($res1->items, $res2->items);
//			$this->_total = count($items);
//			return new XMLFragmentRestResponse($items, $this);
//		} else return false;
//	}

    /**
     * overrides RestResource::getModel()
     */
	protected function getModel() {
		return new Default_Model_Middlewares();
	}
}

/**
 * class RestStatusList
 * derived class for lists of status types
 */
class RestStatusList extends RestROResourceList {
    /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
	public function getDataType() {
        return "status";
    }
    
    protected function _list() {
        return $this->get();
    }

    /**
     * overrides RestResource::getModel()
     */
	protected function getModel() {
		return new Default_Model_Statuses();
	}
}

/**
 * class RestRoleList
 * derived class for lists of role types
 */
class RestRoleList extends RestROResourceList {
    /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
    public function getDataType() {
        return "role";
    }
    
    protected function _list() {
        return $this->get();
    }

    /**
     * overrides RestResource::getModel()
     */
	protected function getModel() {
		$res = new Default_Model_PositionTypes();
		$res->filter->orderBy('ord');
		return $res;
	}
}

/**
 * class RestContactTypeList
 * derived class for lists of contact types
 */
class RestContactTypeList extends RestROResourceList {
    /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
    public function getDataType() {
        return "contact";
    }
    
    protected function _list() {
        return $this->get();
    }

    /**
     * overrides RestResource::getModel()
     */
	protected function getModel() {
		return new Default_Model_ContactTypes();
	}
}

/**
 * class RestCategoryList
 * derived class for lists of disciplines
 */
class RestCategoryList extends RestROResourceList {
    /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
    public function getDataType() {
        return "category";
    }
    
    protected function _list() {
        return $this->get();
    }

    /**
     * overrides RestResource::getModel()
     */
	protected function getModel() {
		$res = new Default_Model_Categories();
		return $res;
	}
}

/** class RestCategoryItem
 * derived class for details on categories
 */
class RestCategoryItem extends RestROResourceItem {
    /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
    public function getDataType() {
        return "category";
	}

    /**
     * overrides RestResource::get()
     */
	public function get() {
		if ( parent::get() !== false ) {
			$res = new Default_Model_Categories();
			if ( is_numeric($this->getParam("id")) ) {
				$res->filter->id->numequals($this->getParam("id"));
			} elseif ( substr($this->getParam("id"), 0, 2) === "s:" ) {
				$res->filter->name->ilike(substr($this->getParam("id"), 2));
			} else {
				return false;
			}
			$res->refresh("xml", true);
			return new XMLFragmentRestResponse($res->items, $this);
		} else return false;
	}
}

class RestCategoryAppStatsList extends RestROResourceList {
	public function getDataType() {
		return "app_category_stats";
	}

	protected function _doget() {
		if ( parent::get() !== false ) {
			global $application;
			if ( is_numeric($this->getParam("id")) ) {
				$void = $this->getParam("id");
			} else {
				db()->setFetchMode(Zend_Db::FETCH_BOTH);
				$void = db()->query("SELECT id FROM categories WHERE name = ?", array(preg_replace('/^s:/', '', $this->getParam("id"))))->fetchAll();
				if (is_array($void) && (count($void) > 0)) {
					$void = $void[0]; //row
					$void = $void[0]; //column
				} else {
					$void = null;
				}
			}
			if ($void == "") {
				$void = "NULL";
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
			$res = db()->query("SELECT * FROM app_cat_stats_to_xml($void, $from, $to)")->fetchAll();
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
/**
 * class RestDisciplineList
 * derived class for lists of disciplines
 */
class RestDisciplineList extends RestROResourceList {
    /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
    public function getDataType() {
        return "discipline";
    }
    
    protected function _list() {
        return $this->get();
    }

    /**
     * overrides RestResource::getModel()
     */
	protected function getModel() {
		$res = new Default_Model_Disciplines();
		if ( ! is_null($this->getParam("orderby")) ) {
			$res->filter->orderBy($this->getParam("orderby"));
		}
		return $res;
	}
}

/** class RestDisciplineItem
 * derived class for details on disciplines
 */
class RestDisciplineItem extends RestROResourceItem {
    /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
    public function getDataType() {
        return "discipline";
	}

    /**
     * overrides RestResource::get()
     */
	public function get() {
		if ( parent::get() !== false ) {
			$res = new Default_Model_Disciplines();
			if ( is_numeric($this->getParam("id")) ) {
				$res->filter->id->numequals($this->getParam("id"));
			} elseif ( substr($this->getParam("id"), 0, 2) === "s:" ) {
				$res->filter->name->ilike(substr($this->getParam("id"), 2));
			} else {
				return false;
			}
			$res->refresh("xml", true);
			return new XMLFragmentRestResponse($res->items, $this);
		} else return false;
	}
    
}

class RestDisciplineVOStatsList extends RestROResourceList {
	public function getDataType() {
		return "app_discipline_stats";
	}

	protected function _doget() {
		if ( parent::get() !== false ) {
			global $application;
			if ( is_numeric($this->getParam("id")) ) {
				$void = $this->getParam("id");
			} else {
				db()->setFetchMode(Zend_Db::FETCH_BOTH);
				$void = db()->query("SELECT id FROM disciplines WHERE name = ?", array(preg_replace('/^s:/', '', $this->getParam("id"))))->fetchAll();
				if (is_array($void) && (count($void) > 0)) {
					$void = $void[0]; //row
					$void = $void[0]; //column
				} else {
					$void = null;
				}
			}
			if ($void == "") {
				$void = "NULL";
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
			$res = db()->query("SELECT * FROM vo_disc_stats_to_xml($void, $from, $to)")->fetchAll();
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
				$ret = preg_grep('/[[:space:]]+additions="0"[[:space:]]+removals="0"[[:space:]]*/', $ret, PREG_GREP_INVERT);
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

class RestDisciplineAppStatsList extends RestROResourceList {
	public function getDataType() {
		return "app_discipline_stats";
	}

	protected function _doget() {
		if ( parent::get() !== false ) {
			global $application;
			if ( is_numeric($this->getParam("id")) ) {
				$void = $this->getParam("id");
			} else {
				db()->setFetchMode(Zend_Db::FETCH_BOTH);
				$void = db()->query("SELECT id FROM disciplines WHERE name = ?", array(preg_replace('/^s:/', '', $this->getParam("id"))))->fetchAll();
				if (is_array($void) && (count($void) > 0)) {
					$void = $void[0]; //row
					$void = $void[0]; //column
				} else {
					$void = null;
				}
			}
			if ($void == "") {
				$void = "NULL";
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
			$res = db()->query("SELECT * FROM app_disc_stats_to_xml($void, $from, $to)")->fetchAll();
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

/**
 * class RestRegionalList
 * derived class for lists of regional entities (countries, regions, and NGIs)
 */
class RestRegionalList extends RestROResourceList {
    /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
    public function getDataType() {
        return "regional";
    }
    
    protected function _list() {
   		$ret = array();
        $res1 = new Default_Model_Countries();
        $res2 = new Default_Model_Regions();
        $res3 = new Default_Model_NGIs();
        $res1->refresh();
        $res2->refresh();
        $res3->refresh();
		for ($i=0; $i < count($res1->items); $i++) {
			$ret[] = '<regional:country xmlns:regional="' . RestAPIHelper::XMLNS_REGIONAL() . '" id="'.$res1->items[$i]->id.'" >'.htmlentities($res1->items[$i]->name, ENT_XML1).'</regional:country>';
		}
		for ($i=0; $i < count($res2->items); $i++) {
			$ret[] = '<regional:region xmlns:regional="' . RestAPIHelper::XMLNS_REGIONAL() . '" id="'.$res2->items[$i]->id.'" >'.htmlentities($res2->items[$i]->name, ENT_XML1).'</regional:region>';
		}
		for ($i=0; $i < count($res3->items); $i++) {
			$ret[] = '<regional:provider xmlns:regional="' . RestAPIHelper::XMLNS_REGIONAL() . '" id="'.$res3->items[$i]->id.'" >'.htmlentities($res3->items[$i]->name, ENT_XML1).'</regional:provider>';
        }
		return new XMLFragmentRestResponse($ret, $this);
    }

    /**
     * overrides RestResource::get()
     */
	public function get() {
		if ( parent::get() !== false ) {
            if ( $this->_listMode === RestListModeEnum::RL_NORMAL ) {
                $res1 = new Default_Model_Countries();
                $res2 = new Default_Model_Regions();
                $res3 = new Default_Model_NGIs();
                $res1->refresh("xml");
                $res2->refresh("xml");
                $res3->refresh("xml");
                $items =array_merge(array_merge($res1->items, $res2->items), $res3->items);
                $this->_total = count($items);
                return new XMLFragmentRestResponse($items, $this);
            } elseif ( $this->_listMode === RestListModeEnum::RL_LISTING ) {
                $this->_listMode = RestListModeEnum::RL_NORMAL;
                return $this->_list();
            }
		} else return false;
	}
}

/**
 * class RestDisseminationList
 * derived class for lists of displatched dissemination messages
 */
class RestDisseminationList extends RestResourceList {
   
    protected function _options() {
        $options = array();
        if ( $this->userIsAdmin() ) $options[] = RestMethodEnum::RM_GET;
        return $options;
    }

    protected function _list() {
   		$ret = array();
		$this->_model->refresh();
		for ($i=0; $i < count($this->_model->items); $i++) {
			$ret[] = '<dissemination:dissemination xmlns:dissemination="' . RestAPIHelper::XMLNS_DISSEMINATION() . '" id="'.$this->_model->items[$i]->id.'" >'.htmlentities($this->_model->items[$i]->subject).'</dissemination:dissemination>';
		}
		return new XMLFragmentRestResponse($ret, $this);
    }

    /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
    public function getDataType() {
        return "dissemination";
    }
    
    /**
     * overrides RestResource::getModel()
     */
	protected function getModel() {
		$res = new Default_Model_Dissemination();
		$res->filter = FilterParser::getDissemination($this->getParam("flt"));
		if ( is_null($this->getParam("orderby")) ) {
			$res->filter->orderBy("senton DESC");
		} else {
			$res->filter->orderBy($this->getParam("orderby"));
		}
		return $res;
	}
	
    /**
     * realization of authorize() from iRestAuthModule
     */
	public function authorize($method) {
        $res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
            $res = $this->getUser()->privs->canUseDisseminationTool();
            if ( ! $res ) $this->setError(RestErrorEnum::RE_ACCESS_DENIED);
            break;
        case RestMethodEnum::RM_POST:
        case RestMethodEnum::RM_PUT:
        case RestMethodEnum::RM_DELETE:
            $res = false;
            break;
        }
        return $res;
    }
}

/**
 * class RestDisseminationItem
 * derived class for individual dissemination messages
 */
class RestDisseminationItem extends RestResourceItem {
   
    protected function _options() {
        $options = array();
        if ( $this->userIsAdmin() ) $options[] = RestMethodEnum::RM_GET;
        return $options;
    }

    /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
    public function getDataType() {
        return "dissemination";
    }
    
    /**
     * overrides RestResource::get()
     */
	public function get() {
		if ( parent::get() !== false ) {	
			$res = new Default_Model_Dissemination();
			if ( is_null($this->getParam("orderby")) ) {
				$res->filter->orderBy("senton DESC");
			} else {
				$res->filter->orderBy($this->getParam("orderby"));
			}
			$res->filter->id->equals($this->getParam("id"));
			$res->refresh("xml");
			return new XMLFragmentRestResponse($res->items);
		} else return false;
	}
	
    /**
     * realization of authorize() from iRestAuthModule
     */
	public function authorize($method) {
        $res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
            $res = $this->getUser()->privs->canUseDisseminationTool();
            break;
        case RestMethodEnum::RM_POST:
        case RestMethodEnum::RM_PUT:
        case RestMethodEnum::RM_DELETE:
            $res = false;
            break;
        }
        return $res;
    }
}

/**
 * class RestDisseminationFilterNormalization
 * handles application filter syntax normalization and validation
 */
class RestDisseminationFilterNormalization extends RestROResourceItem {
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
			return new XMLFragmentRestResponse(validateFilterActionHelper($flt, FilterParser::NORM_DISSEMINATION), $this);
		} else return false;
	}
}

/**
 * class RestDisseminationFilterReflection
 * handles application filter reflection requests
 */
class RestDisseminationFilterReflection extends RestROResourceItem {
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
			$s = '<dissemination:filter>';
			$s .= FilterParser::fieldsToXML("any dissemination application sender country vo discipline middleware category", "dissemination");
			$s .= '</dissemination:filter>';
			return new XMLFragmentRestResponse($s, $this);
		} else return false;
    }
}

/**
 * class RestTagList
 * derived class for list of registered application tags
 */
class RestTagList extends RestROResourceList {
    /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
	public function getDataType() {
		return "tag";
    }

    protected function _list() {
        return $this->get();
    }
	
    /**
     * overrides RestResource::getModel()
     */
	protected function getModel() {
		$res = new Default_Model_AppTags();
//		if (!is_null($this->getParam("appid"))) $res->filter->appid->equals($this->getParam("appid"));
		return $res;
	}
}


class RestBroker extends RestResourceList {
    /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
	public function getDataType() {
		return "request";		
	}

    protected function _list() {
        return $this->get();
    }

    protected function _options() {
        $options = array();
        $options[] = RestMethodEnum::RM_PUT;
        return $options;
	}

	public static function resourceParamMatches($xparams, $res) {
		$matches = false;
		foreach ($xparams as $xparam) {
			$param = strval($xparam);
			$name = strval($xparam->attributes()->name);
			$fmt = strval($xparam->attributes()->fmt);
			if (preg_match("/^" . $fmt . "$/", $res)) {
				$matches = true;
				break;
			}
		}
		return $matches;
	}

	public static function matchResource($res, $apiroutes, &$pars) {
		$ret = null;
		$routes = $apiroutes->xpath("route");
		if ( count($routes) > 0 ) {
			if ( substr($res, 0, 1) == "/" ) $res = substr($res, 1);
			if ( substr($res, -1) == "/" ) $res = substr($res, 0, strlen($res) - 1);
			$res = preg_replace("#/+#", "/" , $res);
			$res = explode("/", $res);
			foreach ( $routes as $xroute ) {
				$xparams = $xroute->xpath("param");
				$match = false;
				$route = strval($xroute->attributes()->url);
				if ( $route != '' ) {
					$route = trim($route);
					if ( substr($route, 0, 1) == "/" ) $route = substr($route, 1);
					if ( substr($route, -1) == "/" ) $route = substr($route, 0, strlen($route) - 1);
					$route = preg_replace("#/+#", "/" , $route);
					$route = preg_replace("#^:version/#", "" , $route);
					$route = explode("/", $route);
					if ( (count($route) > 0) && (count($res) == count($route)) ) {
						$match = true;
						for ($i=0; $i<count($res); $i++) {
							$match = $match && ( ($route[$i] == $res[$i]) || ((substr($route[$i], 0, 1) == ":") && RestBroker::resourceParamMatches($xparams, $res[$i])));
							if ( ! $match ) break;
							if ( substr($route[$i], 0, 1) == ":" ) {
								$pars[substr($route[$i], 1)] = $res[$i];
							}
						}
					}
					if ( $match ) {
						$ret = $xroute;
						break;
					}
				}
			}
		} 
		return $ret;
	}

	public function post() {
		return $this->put();
	}

	public function put() {
		if ( parent::put() != false ) {
			try {
				$xml = new SimpleXMLElement($this->getData());
			} catch (Exception $e) {
				$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
				$this->_extError = $e->getMessage();
				return false;
			}
			$xmli = $xml->xpath('//appdb:request');
			if ( count($xmli) === 0 ) {
				$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
				$this->_extError = "No request element provided";
				return false;
			}
			$apiroutes = new SimpleXMLElement(APPLICATION_PATH . "/apiroutes.xml", 0, true);
			$ret = array();
			foreach ($xmli as $x) {
				$routeXslt = null;
				$username = null;
				$userid = null;
				$passwd = null;
				$apikey = null;
				$sessionid = null;
				$src = null;
				$srv = null;
				$cid = null;
				if (trim($apikey) == '') { $apikey = $this->getParam("apikey"); }
				$method = strval($x->attributes()->method);
				switch(strtolower($method)) {
					case "get":
						$method = RestMethodEnum::RM_GET;
						break;
					case "put":
						$method = RestMethodEnum::RM_PUT;
						break;
					case "post":
						$method = RestMethodEnum::RM_POST;
						break;
					case "delete":
						$method = RestMethodEnum::RM_DELETE;
						break;
					case "options":
						$method = RestMethodEnum::RM_OPTIONS;
						break;
					default:
						$method = false;
						break;
				}
				if ( $method === false ) {
					$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
					$this->_extError = "Invalid value in request `method' attribute";
					return false;
				}
				$reqID = strval($x->attributes()->id);
				if ( $reqID == "" ) {
					$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
					$this->_extError = "Missing request `id' attribute";
					return false;
				}				
				$username = strval($x->attributes()->username);
				if (trim($username) == '') { $username = $this->getParam("username"); }
				$userid = strval($x->attributes()->userid);
				if (trim($userid) == '') { $userid = $this->getParam("userid"); }
				$passwd = strval($x->attributes()->passwd);
				if (trim($passwd) == '') { $passwd = $this->getParam("passwd"); }
				$apikey = strval($x->attributes()->apikey);
				if (trim($apikey) == '') { $apikey = $this->getParam("apikey"); }
				$sessionid = $this->getParam("sessionid");
				$src = $this->getParam("src");
				$cid = $this->getParam("cid");
				$srv = $this->getParam("remoteaddr");
				$res = strval($x->attributes()->resource);
				if ( $res != '' ) {
					if ( substr($res, 0, 1) == "/" ) $res = substr($res, 1);
					$pars = array();
					$rx = RestBroker::matchResource($res, $apiroutes, $pars);
					if ( ! is_null($rx) ) {
						try {
							$resclass = strval($rx->resource);
							if ( $username != '' ) $pars["username"] = $username;
							if ( $userid != '' ) $pars["userid"] = $userid;
							if ( $passwd != '' ) $pars["passwd"] = $passwd;
							if ( $apikey != '' ) $pars["apikey"] = $apikey;
							if ( $sessionid != '' ) $pars["sessionid"] = $sessionid;
							if ( $src != '' ) $pars["src"] = $src;
							if ( $cid != '' ) $pars["cid"] = $cid;
							if ( $srv != '' ) $pars["remoteaddr"] = $srv;
							$xparams = $x->xpath("appdb:param");
							foreach ( $xparams as $xparam ) {
								$pname = strval($xparam->attributes()->name);
								if ( $pname != '' ) {
									$pars[$pname] = strval($xparam);
								} else {
									$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
									$this->_extError = "Missing `name' attribute in `param' element for request `" . $reqID . "'";
									return false;
								}
							}
							$res = new $resclass($pars);
							$fmt = $rx->xpath("format");
							if ( count($fmt) > 0 ) {
								foreach ( $fmt as $f ) {
									if ( strval($f) === "xml" ) {
										if ( strval($f->attributes()->xslt) != '' ) $routeXslt = strval($f->attributes()->xslt);
										break;
									}
								}
							}
						} catch (Exception $e) {
							$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
							$this->_extError = "Error initializing resource specified for request `" . $reqID . "'";
							return false;
						}
					} else {
						$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
						$this->_extError = "Invalid resource specified for request `" . $reqID . "'";
						return false;
					}
				} else {
					$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
					$this->_extError = "No resource of empty resource specified for request `" . $reqID . "'";
					return false;
				}
				$s_method = strtolower(RestMethodEnum::toString($method));
				$_res = $res->$s_method();
				if ( $_res !== false ) {
					if ( $_res->isFragment() ) {
						$res = $_res->finalize();
					} else {
						$res = $_res;
					}
				} else {
					$this->_error = $res->_error;
					$this->_extError = $res->_extError;
					return false;
				}
				if ( ! is_null($routeXslt) ) $res = $res->transform(RestAPIHelper::getFolder(RestFolderEnum::FE_XSL_FOLDER).$routeXslt);
				$ret[] = '<appdb:reply id="' . $reqID . '">' . "\n" . $res . "\n" . '</appdb:reply>';
			}
			$ret = new XMLRestResponse($ret, $this);
			$ret = '<appdb:broker ' . implode(" ", RestAPIHelper::namespaces()) . ' ' . '>' . "\n" . $ret . "\n" . '</appdb:broker>';
			return new XMLRestResponse($ret, $this);
		} else return false;
	}

	public function get() {
		return parent::get();
	}

    /**
     * realization of authorize() from iRestAuthModule
     */
	public function authorize($method) {
        $res = false;
        switch ($method) {
        case RestMethodEnum::RM_PUT:
        case RestMethodEnum::RM_POST:
			$res = true;
            break;
        case RestMethodEnum::RM_GET:
        case RestMethodEnum::RM_DELETE:
            $this->setError(RestErrorEnum::RE_INVALID_METHOD);
            break;
        }
        return $res;
    }
}

class RestAppDBResourceList extends RestROResourceList {
	/**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
	public function getDataType() {
		return "resource";
	}

	public function get() {
		if ( parent::get() !== false ) {
			$s = file_get_contents(APPLICATION_PATH . "/apiroutes.xml");
			$s = str_replace("/:version/", "/" . RestAPIHelper::VERSION . "/", $s);
			$s = str_replace("\t", "" , $s);
			$s = preg_replace('/>\s+/', '>', $s);
			$s = preg_replace('/<\s+/', '<', $s);
			$xslt = RestAPIHelper::getFolder(RestFolderEnum::FE_XSL_FOLDER) . "/apiroutes.xsl";
			$xsl = new DOMDocument();
			$xsl->load($xslt);
			$xml = new DOMDocument();
			$xml->loadXML($s, LIBXML_NSCLEAN | LIBXML_COMPACT);
			$proc = new XSLTProcessor();
			$proc->registerPHPFunctions();
			$proc->importStylesheet($xsl);
			$s = $proc->transformToXml($xml);
			$xml = new SimpleXMLElement($s);
			$xml->registerXPathNamespace('appdb','http://appdb.egi.eu/api/' . RestAPIHelper::VERSION . '/appdb');
			$x = $xml->xpath("//appdb:resource");
			$s = array();
			while ( list(, $node) = each($x) ) {
				$s[] = trim($node->asXML());
			}
			$this->_pageOffset = 0;
			$this->_pageLength = count($s);
			$this->_total = count($s);
			$s = RestAPIHelper::wrapResponse($s, "resource", "list", count($s), null, null, null, null, ! is_null($this->getUser()));
			return new XMLRestResponse($s, $this);
		} else return false;
	}

	protected function _list() {
        return $this->get();
    }

}

class RestLangList extends RestROResourceList {
	public function getDataType() {
		return "language";
	}

	public function _list() {
		return $this->get();
	}

	public function getModel() {
		return new Default_Model_ProgLangs();
	}
}

class RestOSList extends RestROResourceList {
	public function getDataType() {
		return "os";
	}

	public function _list() {
		return $this->get();
	}

	public function get() {
		if ( parent::get() !== false ) {
			$res2 = new Default_Model_OSes();
			$res1 = new Default_Model_OSFamilies();
			$xml = array();
			foreach ($res1->items as $i) {
				$xml[] = $i->toXML();
			}		
			foreach ($res2->items as $i) {
				$xml[] = $i->toXML();
			}		
			return new XMLFragmentRestResponse($xml, $this);
		}	
		else {
			return false;
		}
	}

//	public function getModel() {
//		return new Default_Model_OSes();
//	}
}

class RestLicenseList extends RestROResourceList {
	public function getDataType() {
		return "license";
	}

	public function _list() {
		return $this->get();
	}

	public function getModel() {
		return new Default_Model_Licenses();
	}
}

class RestArchList extends RestROResourceList {
	public function getDataType() {
		return "arch";
	}

	public function _list() {
		return $this->get();
	}

	public function getModel() {
		return new Default_Model_Archs();
	}
}

class RestHVList extends RestROResourceList {
	public function getDataType() {
		return "hypervisor";
	}

	public function _list() {
		return $this->get();
	}

	public function getModel() {
		return new Default_Model_Hypervisors();
	}
}

class RestVMIFmtList extends RestROResourceList {
	public function getDataType() {
		return "vmiformat";
	}

	public function _list() {
		return $this->get();
	}

	public function getModel() {
		return new Default_Model_VMIformats();
	}
}

class RestVAProvidersList extends RestROResourceList {
	/**
     * overrides RestResource::init()
     */
	protected function init() {
		parent::init();
		$this->_cacheLife = 900; // 15 minutes
		$this->_cacheable = true;
	}

	public function getDataType() {
		return "virtualization";
	}

	public function _list() {
		return $this->get();
	}

	public function getModel() {
		return new Default_Model_VaProviders();
	}
}
class RestVAProviderItem extends RestROResourceItem {
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

	public function getDataType() {
		return "virtualization";
	}

	public function getModel() {
		return $this->_resParent;
	}

	/**
     * overrides RestResource::init()
     */
    protected function init() {
		$this->_logged = false;
		$this->_resParent = new Default_Model_VaProviders();
		$this->_resParent->filter->id->equals($this->getParam("id"));
        if ( count($this->_resParent->items) > 0 ) {
            $this->_res = $this->_resParent->items[0];
        } else {
            $this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
            $this->res = null;
        }
    }
}
class RestAccessGroupList extends RestROResourceList {
	public function getDataType() {
		return "accessgroup";
	}

	public function _list() {
		return $this->get();
	}

	public function getModel() {
		$res = new Default_Model_ActorGroups();
		$res->filter->orderBy("name");
		return $res;
	}
}

class RestAccessGroupItem extends RestROResourceItem {
	public function getDataType() {
		return "accessgroup";
	}

	public function get() {
		if ( parent::get() !== false ) {
			db()->setFetchMode(Zend_Db::FETCH_NUM);
			$res = db()->query("SELECT privgroup_to_xml(" . pg_escape_string($this->getParam("id")) . ")")->fetchAll();
			if ( count($res) > 0 ) {
				$ret = array();
				foreach ($res as $r) {
					$ret[] = $r[0];
				}
				return new XMLFragmentRestResponse($ret, $this);
			} else {
				$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
				return false;
			}
		} else return false;
	}
}

class RestRelationTypeList extends RestROResourceList {
	public function getDataType() {
		return "relationtype";
	}

	public function _list() {
		return $this->get();
	}

	
	public function get(){
		if ( parent::get() !== false ) {
			db()->setFetchMode(Zend_Db::FETCH_NUM);
			$res = db()->query("SELECT relation_types_to_xml()")->fetchAll();
			if ( count($res) > 0 ) {
				$ret = array();
				foreach ($res as $r) {
					$ret[] = $r[0];
				}
				return new XMLFragmentRestResponse($ret, $this);
			} else {
				$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
				return false;
			}
		} else return false;
	}
}

class RestContextScriptFormatList extends RestROResourceList{
	public function getDataType() {
		return "contextualization";
	}
	
	public function get(){
		if ( parent::get() !== false ) {
			$res = new Default_Model_ContextFormats();
			$xml = array();
			foreach ($res->items as $i) {
				$xml[] = "<contextualization:format id='" . $i->id . "' name='".$i->name."'>" . htmlspecialchars($i->description, HTML_SPECIALCHARS) . "</contextualization:format>";
			}
			return new XMLFragmentRestResponse($xml, $this);
		}	
		else {
			return false;
		}
	}

	public function _list() {
		return $this->get();
	}

	public function getModel() {
		return new Default_Model_ContextFormats();
	}
}

class RestStoreStatsList extends RestROResourceList {
	public function getDataType() {
		return "store_stats";
	}

	protected function _doget() {
		if ( parent::get() !== false ) {
			global $application;
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
			$res = db()->query("SELECT * FROM store_stats_to_xml($from, $to)")->fetchAll();
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
