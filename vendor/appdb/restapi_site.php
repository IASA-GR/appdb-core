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

/**
 * class RestSiteList
 * derived class for lists of sites
 */
class RestSiteList extends RestROResourceList {
     /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
    public function getDataType() {
        return "site";
    }
    
    protected function _list() {
        return $this->get();
    }

    /**
     * overrides RestResource::getModel()
     */
	protected function getModel() {
		$res = new \Application\Model\Sites();
		if ( ! is_null($this->getParam("orderby")) ) {
			$res->filter->orderBy($this->getParam("orderby"));
		}
		if ( $this->getParam("flt") != "" ) {
			$res->filter = FilterParser::getSites($this->getParam("flt"));
		}
		return $res;
	}
}

/** class RestSiteItem
 * derived class for details on sites
 */
class RestSiteItem extends RestROResourceItem {
    /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
    public function getDataType() {
        return "site";
	}

    /**
     * overrides RestResource::get()
     */
	public function get() {
		if ( parent::get() !== false ) {
			$res = new \Application\Model\Sites();
			if ( substr($this->getParam("id"), 0, 2) === "s:" ) {
				$res->filter->name->ilike(substr($this->getParam("id"), 2));
			} elseif ( substr($this->getParam("id"), 0, 2) === "g:" ) {
				$res->filter->guid->equals(substr($this->getParam("id"), 2));
			} elseif ( is_string($this->getParam("id")) && trim($this->getParam("id")) !== "") {
				$res->filter->id->equals($this->getParam("id"));
			} else {
				return false;
			}
			$res->refresh("xml", true);
			return new XMLFragmentRestResponse($res->items, $this);
		} else return false;
	}
    
}

/**
 * class RestSiteFilterReflection
 * handles application filter reflection requests
 */
class RestSiteFilterReflection extends RestROResourceItem {
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
			$s = '<site:filter>';
			$s .= FilterParser::fieldsToXML("any site application country vo discipline middleware category", "site");
			$s .= '</site:filter>';
			return new XMLFragmentRestResponse($s, $this);
		} else return false;
    }
}

/**
 * class RestSiteFilterNormalization
 * handles application filter syntax normalization and validation
 */
class RestSiteFilterNormalization extends RestROResourceItem {
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
			return new XMLFragmentRestResponse(validateFilterActionHelper($flt, FilterParser::NORM_SITE), $this);
		} else return false;
	}
}


/**
 * class RestSiteLogistics
 * handles sites counting per various properties
 */
class RestSiteLogistics extends RestROResourceItem {
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
			$isAdmin = $this->userIsAdmin();
			$mapper = new \Application\Model\SitesMapper();
			$flt = $this->getParam("flt");
			$select = $mapper->getDbTable()->getAdapter()->select()->distinct()->from('sites');
			$from = '';
			$where = '';
			$orderby = '';
			$limit = '';
			$filter = FilterParser::getSites($flt);
			if ( is_array($filter->expr()) ) {
				$ex = implode(" ", $filter->expr()); 
			} else {
				$ex = $filter->expr();
			}
			$fltexp = $filter->expr();
			if ( ! is_array($fltexp) ) $fltexp = array($fltexp);
			foreach($fltexp as $x) {
				getZendSelectParts($select, $from, $where, $orderby, $limit);
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

			$rs = $db->query('SELECT * FROM site_logistics(?,?,?)', array($flt, $from, $where))->toArray();
			if ( count($rs) > 0 ) {
				$rs = $rs[0];
				$x = $rs['site_logistics'];
			} else {
				$x = '';
			}
			return new XMLFragmentRestResponse($x, $this);
		} else return false;
	}
}
