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
 * class RestSciClassList
 * derived class for lists of SciClasss
 */
class RestSciClassList extends RestROResourceList {
    /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
    public function getDataType() {
        return "classification";
    }
    
	protected function _list() {
		return $this->get();
    }

    /**
     * overrides RestResource::getModel()
     */
	public function get() {
		if (parent::get() !== false) {
			db()->setFetchMode(Zend_Db::FETCH_OBJ);			
			$res = db()->query("SELECT sci_class.toxml(version) as x FROM sci_class.cverids")->fetchAll();
			$x = array();
			foreach ($res as $r) {
				$x[] = $r->x;
			}
//			if ( $this->getParam("format") === "json" ) {
//				return new JSONRestResponse(new XMLFragmentRestResponse($x, $this), $this);
//			} else {
				return new XMLFragmentRestResponse($x, $this);
//			}
		} else return false;
	}
}

/**
 * class RestSciClassItem
 * derived class for individual SciClass items
 */
class RestSciClassItem extends RestROResourceItem {
    /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
    public function getDataType() {
        return "classification";
    }

    public function get() {
        if ( parent::get() !== false ) {
			db()->setFetchMode(Zend_Db::FETCH_OBJ);
			if (substr($this->getParam("id"), 0, 2) === "s:") {
				$id = substr($this->getParam("id"), 2);
				$idtype = "::text";
			} elseif (is_numeric($this->getParam("id"))) {
				$id = intval($this->getParam("id"));
				$idtype = "::int";
			} else {
				return false;
			}
			$res = db()->query("SELECT sci_class.toxmlext(?$idtype) as x", array($id))->fetchAll();
			$x = array();
			foreach ($res as $r) {
				$x[] = $r->x;
			}
			if ( $this->getParam("format") === "json" ) {
				return new JSONRestResponse(new XMLFragmentRestResponse($x, $this), $this);
			} else {
				return new XMLFragmentRestResponse($x, $this);
			}
     } else return false;
    }
}
