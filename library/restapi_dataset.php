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
 * class RestDSExchangeFormatList
 * derived class for lists of dataset exchange format
 */
class RestDSExchangeFormatList extends RestROResourceList {
     /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
    public function getDataType() {
        return "exchangeformat";
    }
    
    protected function _list() {
        return $this->get();
    }

    /**
     * overrides RestResource::getModel()
     */
	protected function getModel() {
		$res = new Default_Model_DatasetExchangeFormats();
		return $res;
	}
}

/**
 * class RestDSConnectionTypeList
 * derived class for lists of dataset connection types
 */
class RestDSConnectionTypeList extends RestROResourceList {
     /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
    public function getDataType() {
        return "connectiontype";
    }
    
    protected function _list() {
        return $this->get();
    }

    /**
     * overrides RestResource::getModel()
     */
	protected function getModel() {
		$res = new Default_Model_DatasetConnTypes();
		return $res;
	}
}

/**
 * class RestDSXMLParser
 * derived class for parsing dataset resources
 */
class RestDSXMLParser extends RestXMLParser {
    /**
     * implementation of abstract parse() operation from RestXMLParser.
     * @param SimpleXMLElement $xml the root element of the dataset XML representation
     * 
     * @return Default_Model_Dataset
     *
     */
    public function parse($xml) {
		global $application;

		if ( ! is_null($this->_user) ) {
			$ds = new Default_Model_Dataset();
			try {
				$xml = new SimpleXMLElement($xml);
			} catch (Exception $e) {
				$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
				return $ds;
            }
            $this->_xml = $xml;
			// basic properties
			$xmli = $xml->xpath('//dataset:dataset');
			if ( count($xmli) === 0 ) {
				$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
				return $ds;
			}
			$xml = $xmli[0];
			if ( $this->_parent->getMethod() === RestMethodEnum::RM_PUT && (
					(count($xml->xpath("//dataset:name")) == 0)  || 
					(strval($xml->attributes()->category) == "")
			)) {
				$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
				$this->_extError = "One ore more required entities are missing or contain no data.";
				return $ds;
			}
			if ( $this->_parent->getMethod() === RestMethodEnum::RM_POST ) {
				if ( $xml->attributes()->id ) {
					$ds->id = intval(strval($xml->attributes()->id));
					$db = $application->getBootstrap()->getResource('db');
					$db->setFetchMode(Zend_Db::FETCH_OBJ);
					$r = $db->query('SELECT guid FROM datasets WHERE id = ?', array($ds->id))->fetchAll();
					if ( count($r) > 0 ) $ds->guid = $r[0]->guid;
                } else {                    
					$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
					return $ds;
				}
            }
			if ( $this->_parent->getMethod() === RestMethodEnum::RM_PUT ) {
				$ds->addedByID = $this->_parent->getUser()->id;
			}

			$newParentID = null;
			if ( ! is_null($this->el($xml,'dataset:parent[@xsi:nil="true"]'))) {
				$newParentID = 0;
			} elseif ( ! is_null($this->el($xml,"dataset:parent"))) {
				$newParentID = $this->el($xml,"dataset:parent")->attributes()->id;
				if ($newParentID == "") {
					$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
					return $ds;
				} else {
					$datasets = new Default_Model_Datasets();
					$datasets->filter->id->numequals((int)$newParentID);
					if (
						(count($datasets->items) == 0) ||
						((count($datasets->items) > 0) && ($datasets->items[0]->parentID !== null)) // parent dataset must be primary dataset
					) {
						if (count($datasets->items) == 0) {
							$this->_error = RestErrorEnum::RE_ITEM_NOT_FOUND;
							$this->_extError = "Parent dataset not found";
						} else {
							$this->_error = RestErrorEnum::RE_BACKEND_ERROR;
							$this->_extError = "Parent dataset must be a primary dataset";
						}
						return $ds;
					}
				}
			}
			if ( ! is_null($newParentID) ) { // a parentid has been supplied in the XML representation; act upon it
				if ($this->_parent->getMethod() === RestMethodEnum::RM_PUT) {
					$ds->parentID = $newParentID;
				} elseif ($this->_parent->getMethod() === RestMethodEnum::RM_POST) { // only allow setting parent on update if parent was NULL
					$datasets = new Default_Model_Datasets();
					$datasets->filter->id->numequals((int)($ds->id));
					if ($datasets->items[0]->parentID === null) {
						$ds->parentID = $newParentID;
					} elseif ($datasets->items[0]->parentID == $newParentID) {
						$no_operation = ""; // do nothing
					} else {
						$this->_error = RestErrorEnum::RE_BACKEND_ERROR;
						$this->_extError = "Cannot modify existing parent dataset";
						return $ds;
					}
				}
			}
			if ($newParentID === null) {
				// no parent id has been provided in the XML; look up database if this is an update
				if ($this->_parent->getMethod() == RestMethodEnum::RM_POST) {
					$datasets = new Default_Model_Datasets();
					$datasets->filter->id->numequals((int)($ds->id));
					$effectiveParentID = $datasets->items[0]->parentID;
				} else {
					$effectiveParentID = null;
				}
			} else {
				$effectiveParentID = ($newParentID === 0 ? null : $newParentID);
			}

			if ( ! is_null($this->el($xml,"dataset:name"))) {
				$ds->name = strval($this->el($xml,"dataset:name"));
			}
			if ( ! is_null($this->el($xml,"dataset:description"))) {
				$ds->description = strval($this->el($xml,"dataset:description"));
			}
			if ( ! is_null($this->el($xml,'dataset:url[@type="homepage"]'))) {
				$ds->homepage = strval($this->el($xml,'dataset:url[@type="homepage"]'));
			}
			if ( ! is_null($this->el($xml,'dataset:url[@type="elixir"]'))) {
				$ds->elixirURL = strval($this->el($xml,'dataset:url[@type="elixir"]'));
			}
			if ( isset($xml->attributes()->tags) ) {
				$ds->tags = explode(" ", strval($xml->attributes()->tags));
			} 
			if ( isset($xml->attributes()->category) ) {
				$ds->category = strval($xml->attributes()->category);
			}

			$ds->save();

			// Discipline collection
			// only sync disciplines for primary datasets, assuming disciplines have been provided
			if (count($xml->xpath("//discipline:discipline")) > 0) {
				if ($effectiveParentID === null) {
					$data = $this->buildCollection($xml, "//discipline:discipline", "disciplineID");
					if ( is_null($data) ) $data = array();
					$this->syncDBCollection("datasetid", $ds->id, "disciplineid", "DatasetDisciplines", "DatasetDiscipline", $data);
				} else {
					$this->_error = RestErrorEnum::RE_BACKEND_ERROR;
					$this->_extError = "Cannot explicitly modify disciplines of derived dataset; disciplines are inherited by the parent dataset";
					return $ds;
				}
			}
			// License collection
			$data = $this->buildCollection($xml, "//dataset:license", "license");
			if ( is_null($data) ) $data = array();
			$this->syncDBCollection("datasetid", $ds->id, "licenseid", "DatasetLicenses", "DatasetLicense", $data, "license");

			// Also save versions if they exist
			if (count($xml->xpath('//dataset:version[@xsi:nil="true"]')) > 0) {
				db()->query("SELECT delete_dataset_version(id) FROM datasets WHERE datasetid = ?", array($ds->id))->fetchAll();
			} else {
				$xmlver = $xml->xpath("//dataset:version");
				if ( count($xmlver) > 0 ) {
					foreach($xmlver as $x) {
						$data = RestAPIHelper::responseHead("dataset", null, null, null, null, null, null, ! is_null($this->getUser())) . $x->asXML() .  RestAPIHelper::responseTail();
						$verres = new RestDatasetVersionList(array_merge($this->_parent->getParams(), array('data' => $data, 'id' => $ds->id)));
						if ($this->_parent->getMethod() === RestMethodEnum::RM_PUT) {
							$verres->put();
						} else {
							if (isset($x->attributes()->id)) {
								$verres->post();
							} else {
								$verres->put();
							};
						}
						$this->_error = $verres->getError();
						$this->_extError = $verres->getExtError();
						if ($this->_error != RestErrorEnum::RE_OK) {
							break;
						}
					}
				}
			}
		}
		return $ds;
	}
}

/**
 * class RestDatasetList
 * derived class for lists of dataset connection types
 */
class RestDatasetList extends RestResourceList {
    /**
     * internal reference to XML parser, set during initialization
     *
     */
	private $_parser;

	public function getParser() {
		return $this->_parser;
	}

	/**
     * overrides RestResourceList::init()
     */
	protected function init() {
		$this->_parser = new RestDSXMLParser($this);
	}

     /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
    public function getDataType() {
        return "dataset";
    }
    
    protected function _list() {
   		$ret = array();
		$this->_model->refresh("xml", "listing");
		return new XMLFragmentRestResponse($this->_model->items, $this);
    }

    /**
     * overrides RestResource::getModel()
     */
	protected function getModel() {
		$res = new Default_Model_Datasets();
		$res->flat = strtolower($this->getParam("flat")) == "true" ? true : null;
		if ( ! is_null($this->getParam("orderby")) ) {
			$res->filter->orderBy($this->getParam("orderby"));
		}
		return $res;
	}

    public function authorize($method) {
        $res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
            $res = true;
            break;
        case RestMethodEnum::RM_PUT:
			$res = $this->authenticate() && $this->getUser()->privs->canManageDatasets();
			if ($res !== true) {
				$this->setError(RestErrorEnum::RE_ACCESS_DENIED);
				return false;
			}
			break;
        case RestMethodEnum::RM_POST:
			$id = $this->_parser->getID($this->getData(),"dataset:dataset");
			if ( $this->_parser->getError() === RestErrorEnum::RE_OK ) {
				if ( $id != "" ) {
					$dss = new Default_Model_Datasets();
					$dss->filter->id->equals($id);
					if ( count($dss->items) > 0 ) {
						$ds = $dss->items[0]; 
						$res = true;
					} else {
						$ds = null;
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
            $res = $res && (!is_null($ds)) && $this->authenticate() && ($this->userIsAdmin() || $this->getUser()->id === $ds->addedByID || $this->getUser()->privs->hasAccess($app));
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

	protected function _options() {
        $options = array();
        if ( $this->authenticate() ) $options[] = RestMethodEnum::RM_GET;
        if ( $this->authenticate() ) $options[] = RestMethodEnum::RM_PUT;
        if ( $this->authenticate() ) $options[] = RestMethodEnum::RM_POST;
        return $options;
    }
    /**
     * handles PUT and POST HTTP methods to REST requests
     *
     * @param integer $method the method enumeration according to RestMethodEnum
     *
     * @return iRestResponse
     *
     */
    private function putpost($method) {
		db()->beginTransaction();
		$ds = $this->_parser->parse($this->getData());
        if ( $this->_parser->getError() !== RestErrorEnum::RE_OK ) {
			db()->rollBack();
			$this->setError($this->_parser->getError(), $this->_parser->getExtError(), false);
			$ret = false;
		} else {
			db()->commit();
			$res = new RestDatasetItem(array("id" => $ds->id), $this);
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
	public function post() {
		if ( parent::post() !== false ) {
			return new XMLFragmentRestResponse($this->putpost(RestMethodEnum::RM_POST), $this);
		} else return false;
	}
	
}

/** class RestSiteItem
 * derived class for details on sites
 */
class RestDatasetItem extends RestROResourceItem {
    /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
    public function getDataType() {
        return "dataset";
	}

     /**
     * overrides RestResource::getModel()
     */
	protected function getModel() {
		$res = new Default_Model_Datasets();
		$id = $this->getParam("id");
		if ( is_numeric($id) ) {
	        $res->filter->id->equals($id);
		} else {
	        $res->filter->guid->ilike($id);
		}
		if ( ! is_null($this->getParam("orderby")) ) {
			$res->filter->orderBy($this->getParam("orderby"));
		}
		return $res;
	}
    /**
     * overrides RestResource::delete()
     */
	public function delete() {
		if ( parent::delete() !== false ) {
			$datasets = new Default_Model_Datasets();
			$datasets->filter->parentid->numequals((int)($this->getParam("id")));
			if (count($datasets->items) > 0) {
				$this->_error = RestErrorEnum::RE_BACKEND_ERROR;
				$this->_extError = "Cannot delete a dataset which is referenced by other datasets as their parent";
				return false;
			}
			$ret = $this->get();
			db()->query("SELECT delete_dataset(?)", array($this->getParam("id")))->fetchAll();
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
			$item = new Default_Model_Datasets();
			$item->filter->id->numequals((int)($this->getParam("id")));
			if (count($item->items) == 1) {
				$item = $item->items[0];
			} else {
				$item = null;
			}
			if ( ! is_null($item) ) {
				if ( ! is_null($this->getUser()) ) {
					if ( $this->getUser()->privs->canManageDatasets($item) ) {
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
 * class RestDSVerXMLParser
 * derived class for parsing dataset version resources
 */
class RestDSVerXMLParser extends RestXMLParser {
    /**
     * implementation of abstract parse() operation from RestXMLParser.
     * @param SimpleXMLElement $xml the root element of the dataset XML representation
     * 
     * @return Default_Model_Dataset
     *
     */
    public function parse($xml) {
		global $application;
		$ds = null;
		if ( ! is_null($this->_user) ) {
			$ds = new Default_Model_DatasetVersion();
			try {
				$xml = new SimpleXMLElement($xml);
			} catch (Exception $e) {
				$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
				return $ds;
            }
            $this->_xml = $xml;
			// basic properties
			$xmli = $xml->xpath('//dataset:version');
			if ( count($xmli) === 0 ) {
				$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
				return $ds;
			}
			$xml = $xmli[0];
			if ( 
				$this->_parent->getMethod() === RestMethodEnum::RM_PUT && (
					(strval($xml->attributes()->version) == "") || 
					(
						(strval($xml->attributes()->datasetid) == "") &&
						($this->_parent->getParam("id") == "")
					)
				)
			) {
				$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
				$this->_extError = "One ore more required entities or attributes are missing or contain no data.";
				return $ds;
			}

			if ($this->_parent->getMethod() === RestMethodEnum::RM_PUT) {
				// do not modify version string, parent dataset for existing entry; set only for PUT (insert)
				$ds->version = strval($xml->attributes()->version);
				if ($this->_parent->getParam("id") == "") {
					$ds->datasetID = intval(strval($xml->attributes()->datasetid));
				} else {
					$ds->datasetID = intval($this->_parent->getParam("id"));
				}
			}

			if ( $this->_parent->getMethod() === RestMethodEnum::RM_POST ) {
				if ( $xml->attributes()->id ) {
					$ds->id = intval(strval($xml->attributes()->id));
					$db = $application->getBootstrap()->getResource('db');
					$db->setFetchMode(Zend_Db::FETCH_OBJ);
					$r = $db->query('SELECT guid FROM dataset_versions WHERE id = ?', array($ds->id))->fetchAll();
					if ( count($r) > 0 ) $ds->guid = $r[0]->guid;
				} else {                    
					$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
					return $ds;
				}
			}
			if ( $this->_parent->getMethod() === RestMethodEnum::RM_PUT ) {
				$ds->addedByID = $this->_parent->getUser()->id;
			}
			if ( ! is_null($this->el($xml,"dataset:size"))) {
				$ds->size = strval($this->el($xml,"dataset:size"));
				if ($ds->size == "") $ds->size = null;
			}
			if ( ! is_null($this->el($xml,"dataset:notes"))) {
				$ds->notes = strval($this->el($xml,"dataset:notes"));
			}
			if ( ! is_null($this->el($xml,'dataset:parent_version[@xsi:nil="true"]'))) {
				$ds->parentID = 0;
			} elseif ( ! is_null($this->el($xml,"dataset:parent_version"))) {
				$ds->parentID = $this->el($xml,"dataset:parent_version")->attributes()->id;
				if ($ds->parentID == "") {
					$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
					return $ds;
				} else {
					$datasets = new Default_Model_DatasetVersions();
					$datasets->filter->id->numequals((int)($ds->parentID));
					if (count($datasets->items) == 0) {
						$this->_error = RestErrorEnum::RE_ITEM_NOT_FOUND;
						$this->_extError = "Parent dataset version not found";
						return $ds;
					}
				}
			}

			$ds->save();

			// Also save locations if they exist
			if (count($xml->xpath('//dataset:location[@xsi:nil="true"]')) >0) {
				if ($ds->id != "") {
					$locations = new Default_Model_DatasetLocations();
					$locations->filter->dataset_version_id->numequals((int)($ds->id));
					if( count($locations->items) > 0 ) {
						foreach($locations->items as $loc) {
							$locres = new RestDatasetLocationItem(array_merge($this->_parent->getParams(), array('data' => $data, 'vid' => $ds->id, 'lid' => $loc->id)));
							$locres->delete();
						}
					}
				}
			} else {
				$xmlloc = $xml->xpath("//dataset:location");
				if ( count($xmlloc) > 0 ) {
					if ($ds->id != "") {
						// keep track of existing locations
						$db = $application->getBootstrap()->getResource('db');
						$db->setFetchMode(Zend_Db::FETCH_OBJ);
						$existing = $db->query("SELECT id FROM dataset_locations WHERE dataset_version_id = ?", array($ds->id))->fetchAll();
						$eids = array();
						foreach ($existing as $e) {
							$eids[] = $e->id;
						}
					}
					foreach($xmlloc as $x) {
						$data = RestAPIHelper::responseHead("dataset", null, null, null, null, null, null, ! is_null($this->_parent->getUser())) . $x->asXML() . RestAPIHelper::responseTail();
						$locres = new RestDatasetLocationList(array_merge($this->_parent->getParams(), array('data' => $data, 'vid' => $ds->id)));
						
						if (isset($x->attributes()->id)) {
							for ($eiter = count($eids) - 1; $eiter >=0; $eiter = $eiter - 1) {
								// remove submitted locations from array of existing locations
								if (isset($eids[$eiter])) {
									if ($eids[$eiter] == $x->attributes()->id) {
										// DO NOT USE "unset"; does not work properly
										$eids[$eiter] = -1;
									}
								}
							}
						}
	
						if ($this->_parent->getMethod() === RestMethodEnum::RM_PUT) {
							$locres->put();
						} else {
							if (isset($x->attributes()->id)) {
								$locres->post();
							} else {
								$locres->put();
							}
						}
						$this->_error = $locres->getError();
						$this->_extError = $locres->getExtError();
						if ($this->_error != RestErrorEnum::RE_OK) {
							break;
						}
					}
					if ($ds->id != "") {
						// remove remaining existing locations, they were not submitted
						foreach ($eids as $e) {
							if ($e != "" && $e != -1) {
								$locres = new RestDatasetLocationItem(array_merge($this->_parent->getParams(), array('data' => $data, 'vid' => $ds->id, 'lid' => $e)));
								$locres->delete();
							}
						}
					}
				}
			}
		}
		return $ds;
	}
}

/**
 * class RestDatasetVersionList
 * derived class for lists of dataset connection types
 */
class RestDatasetVersionList extends RestResourceList {

	private $_parser;

	public function getParser() {
		return $this->_parser;
	}

	protected function _options() {
        $options = array();
        if ( $this->authenticate() ) $options[] = RestMethodEnum::RM_GET;
        if ( $this->authenticate() ) $options[] = RestMethodEnum::RM_PUT;
        if ( $this->authenticate() ) $options[] = RestMethodEnum::RM_POST;
        return $options;
    }

	/**
     * overrides RestResourceList::init()
     */
	protected function init() {
		$isAdmin = $this->userIsAdmin();
		$this->_parser = new RestDSVerXMLParser($this);
	}

     /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
    public function getDataType() {
        return "dataset";
    }
    
    protected function _list() {
        return $this->get();
    }

    /**
     * overrides RestResource::getModel()
     */
	protected function getModel() {
		$res = new Default_Model_DatasetVersions();
    	$res->filter->datasetid->numequals((int)($this->getParam("id")));
		if ( ! is_null($this->getParam("orderby")) ) {
			$res->filter->orderBy($this->getParam("orderby"));
		}
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
    private function putpost($method) {
		$inTrans = false;
		try {
			db()->beginTransaction();
			$inTrans = true;
		} catch (Exception $e) {
			if ($e->getMessage() != "There is already an active transaction") {
				$this->setError(RestErrorEnum::RE_BACKEND_ERROR, $e->getMessage(), false);
				return false;
			}
		}
		$ds = $this->_parser->parse($this->getData());
        if ( $this->_parser->getError() !== RestErrorEnum::RE_OK ) {
			if ($inTrans) db()->rollBack();
			$this->setError($this->_parser->getError(), $this->_parser->getExtError(), false);
			$ret = false;
		} else {
			if ($inTrans) {
				db()->commit();
				$res = new RestDatasetVersionItem(array("vid" => $ds->id), $this);
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
			} else {
				$ret = true;
			}
		}
        return $ret;
	}

    /**
     * overrides RestResource::put()
     */
	public function put() {
		if ( parent::put() !== false ) {
            return new XMLFragmentRestResponse($this->putpost(RestMethodEnum::RM_PUT), $this);
		} else return false;
	}

    /**
     * overrides RestResource::post()
     */
	public function post() {
		if ( parent::post() !== false ) {
			return new XMLFragmentRestResponse($this->putpost(RestMethodEnum::RM_POST), $this);
		} else return false;
	}
	
    public function authorize($method) {
        $res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
            $res = true;
            break;
        case RestMethodEnum::RM_PUT:
			$res = $this->authenticate() && $this->getUser()->privs->canManageDatasets();
			if ($res !== true) {
				$this->setError(RestErrorEnum::RE_ACCESS_DENIED);
				return false;
			}
			break;
        case RestMethodEnum::RM_POST:
			$id = $this->_parser->getID($this->getData(),"dataset:version");
			if ( $this->_parser->getError() === RestErrorEnum::RE_OK ) {
				if ( $id != "" ) {
					$dss = new Default_Model_DatasetVersions();
					$dss->filter->id->equals($id);
					if ( count($dss->items) > 0 ) {
						$ds = $dss->items[0]; 
						$res = true;
					} else {
						$ds = null;
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
            $res = $res && (!is_null($ds)) && $this->authenticate() && ($this->userIsAdmin() || $this->getUser()->id === $ds->addedByID || $this->getUser()->privs->hasAccess($app));
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

/** class RestDatasetVersionItem
 * derived class for details on sites
 */
class RestDatasetVersionItem extends RestROResourceItem {
    /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
    public function getDataType() {
        return "dataset";
	}

     /**
     * overrides RestResource::getModel()
     */
	protected function getModel() {
		$res = new Default_Model_DatasetVersions();
		$id = $this->getParam("vid");
		if ( is_numeric($id) ) {
	        $res->filter->id->numequals((int)$id)->and($res->filter->datasetid->numequals((int)($this->getParam("id"))));
		} else {
	        $res->filter->guid->ilike($id)->and($res->filter->datasetid->numequals((int)($this->getParam("id"))));
		}
		if ( ! is_null($this->getParam("orderby")) ) {
			$res->filter->orderBy($this->getParam("orderby"));
		}
		return $res;
	}

	/**
     * overrides RestResource::delete()
     */
	public function delete() {
		if ( parent::delete() !== false ) {
			$dataset_versions = new Default_Model_DatasetVersions();
			$dataset_versions->filter->parentid->numequals((int)($this->getParam("id")));
			if (count($dataset_versions->items) > 0) {
				$this->_error = RestErrorEnum::RE_BACKEND_ERROR;
				$this->_extError = "Cannot delete a dataset version which is referenced by other dataset versions as their parent";
				return false;
			}
			$ret = $this->get();
			db()->query("SELECT delete_dataset_version(?)", array($this->getParam("vid")))->fetchAll();
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
			$item = new Default_Model_DatasetVersions();
			$item->filter->id->numequals((int)($this->getParam("vid")));
			if (count($item->items) == 1) {
				$item = $item->items[0];
			} else {
				$item = null;
			}
			if ( ! is_null($item) ) {
				if ( ! is_null($this->getUser()) ) {
					if ( $this->getUser()->privs->canManageDatasets($item) ) {
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
 * class RestDSLocXMLParser
 * derived class for parsing dataset location resources
 */
class RestDSLocXMLParser extends RestXMLParser {
    /**
     * implementation of abstract parse() operation from RestXMLParser.
     * @param SimpleXMLElement $xml the root element of the dataset XML representation
     * 
     * @return Default_Model_Dataset
     *
     */
    public function parse($xml) {
		global $application;
		if ( ! is_null($this->_user) ) {
			$ds = new Default_Model_DatasetLocation();
			try {
				$xml = new SimpleXMLElement($xml);
			} catch (Exception $e) {
				error_log($xml);
				$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
				return $ds;
            }
            $this->_xml = $xml;
			// basic properties
			$xmli = $xml->xpath('//dataset:location');
			if ( count($xmli) === 0 ) {
				$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
				return $ds;
			}
			$xml = $xmli[0];
			if ( 
				$this->_parent->getMethod() === RestMethodEnum::RM_PUT && (
					is_null($this->el($xml,"dataset:uri")) || (
						(strval($xml->attributes()->datasetversionid) == "") && 
						($this->_parent->getParam("vid") == "")
					) || 
					(count($xml->xpath("//dataset:exchange_format")) == 0) || 
					(count($xml->xpath("//dataset:interface")) == 0)
				) 
			) {
				$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
				$this->_extError = "One ore more required entities or attributes are missing or contain no data.";
				return $ds;
			}

			if ( $this->_parent->getMethod() === RestMethodEnum::RM_PUT ) {
				if ($this->_parent->getParam("vid") == "") {
					$ds->datasetVersionID = strval($xml->attributes()->datasetversionid);
				} else {
					$ds->datasetVersionID = $this->_parent->getParam("vid");
				}
				$ds->addedByID = $this->_parent->getUser()->id;
			} elseif ( $this->_parent->getMethod() === RestMethodEnum::RM_POST ) {
				if ( $xml->attributes()->id ) {
					$ds->id = strval($xml->attributes()->id);
				} else {                    
					$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
					return $ds;
				}
			}

			if ( ! is_null($this->el($xml,"dataset:uri"))) {
				$ds->uri = strval($this->el($xml,"dataset:uri"));
			}
			if ( ! is_null($this->el($xml,"dataset:notes"))) {
				$ds->notes = strval($this->el($xml,"dataset:notes"));
			}
			if ( ! is_null($this->el($xml,"dataset:interface"))) {
				$ds->connectionTypeID = strval($this->el($xml,"dataset:interface")->attributes()->id);
				if ($ds->connectionTypeID == "") {
					$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
					return $ds;
				}
			}
			if ( ! is_null($this->el($xml,"dataset:exchange_format"))) {
				$ds->exchangeFormatID = strval($this->el($xml,"dataset:exchange_format")->attributes()->id);
				if ($ds->exchangeFormatID == "") {
					$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
					return $ds;
				}
			}
			
			$ds->save();
			
			if (count($xml->xpath('//dataset:organization[@xsi:nil="true"]')) > 0) {
				//$ds->organizationID = "0"; // N.B.: mapper overriden to nullify "0" values
				if ($ds->id != "") {
					db()->query("DELETE FROM dataset_location_organizations WHERE dataset_location_id = ?", array($ds->id))->fetchAll();
				}
			} else if ( ! is_null($this->el($xml,"dataset:organization"))) {
				if ($ds->id != "") {
					db()->query("DELETE FROM dataset_location_organizations WHERE dataset_location_id = ?", array($ds->id))->fetchAll();
				}
				//$xorg = $this->el($xml,"dataset:organization");
				foreach($xml->xpath("//dataset:organization") as $xorg) {
					$orgitem = null;
					$orgid = strval($xorg->attributes()->id);

					if( is_string($orgid) && trim($orgid) !== '' ) {
						$orgitem = HarvestOrganizations::getImportedOrganization($orgid);
					}

					if( $orgitem === null ) {
						$orgitem = HarvestOrganizations::import($orgid);
					}

					if( $orgitem ) {
						$orgid = $orgitem->id;
					} else {
						$orgid = "";
					}

					$dss = new Default_Model_DatasetLocationOrganization();
					$dss->organizationID = $orgid; //strval($xorg->attributes()->id);
					if ($dss->organizationID == "") {
						$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
						return $ds;
					}
					$dss->datasetLocationID = $ds->id;
					$dss->save();
				}
			}
			if (count($xml->xpath('//appdb:site[@xsi:nil="true"]')) > 0) {
				//$ds->siteID = "0"; // N.B.: mapper overriden to nullify "0" values
				if ($ds->id != "") {
					db()->query("DELETE FROM dataset_location_sites WHERE dataset_location_id = ?", array($ds->id))->fetchAll();
				}
			} elseif ( ! is_null($this->el($xml,"appdb:site"))) {
				//$xsite = $this->el($xml,"appdb:site");
				if ($ds->id != "") {
					db()->query("DELETE FROM dataset_location_sites WHERE dataset_location_id = ?", array($ds->id))->fetchAll();
				}
				foreach($xml->xpath("//appdb:site") as $xsite) {
					$dss = new Default_Model_DatasetLocationSite();
					$dss->siteID = strval($xsite->attributes()->id);
					if ($dss->siteID == "") {
						$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
						return $ds;
					}
					$dss->datasetLocationID = $ds->id;
					$dss->save();
				}
			}
			if (isset($xml->attributes()->master)) {
				$ds->isMaster = filter_var($xml->attributes()->master, FILTER_VALIDATE_BOOLEAN);
			}
			if (isset($xml->attributes()->public)) {
				$ds->isPublic = filter_var($xml->attributes()->public, FILTER_VALIDATE_BOOLEAN);
			}

			$ds->save();
		}
		return $ds;
	}
}


/**
 * class RestDatasetLocationList
 * derived class for lists of dataset connection types
 */
class RestDatasetLocationList extends RestResourceList {
	private $_parser;

	public function getParser() {
		return $this->_parser;
	}

	protected function _options() {
        $options = array();
        if ( $this->authenticate() ) $options[] = RestMethodEnum::RM_GET;
        if ( $this->authenticate() ) $options[] = RestMethodEnum::RM_PUT;
        if ( $this->authenticate() ) $options[] = RestMethodEnum::RM_POST;
        return $options;
    }

	/**
     * overrides RestResourceList::init()
     */
	protected function init() {
		$this->_parser = new RestDSLocXMLParser($this);
	}

     /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
    public function getDataType() {
        return "dataset";
    }
    
    protected function _list() {
        return $this->get();
    }

    /**
     * overrides RestResource::getModel()
     */
	protected function getModel() {
		$res = new Default_Model_DatasetLocations();
    	$res->filter->dataset_version_id->numequals((int)($this->getParam("vid")));
		if ( ! is_null($this->getParam("orderby")) ) {
			$res->filter->orderBy($this->getParam("orderby"));
		}
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
    private function putpost($method) {
		$inTrans = false;
		try {
			db()->beginTransaction();
			$inTrans = true;
		} catch (Exception $e) {
			if ($e->getMessage() != "There is already an active transaction") {
				$this->setError(RestErrorEnum::RE_BACKEND_ERROR, $e->getMessage(), false);
				return false;
			}
		}
		$ds = $this->_parser->parse($this->getData());
        if ( $this->_parser->getError() !== RestErrorEnum::RE_OK ) {
			if ($inTrans) db()->rollBack();
			$this->setError($this->_parser->getError(), $this->_parser->getExtError(), false);
			$ret = false;
		} else {
			if ($inTrans) {
				db()->commit();
				$res = new RestDatasetLocationItem(array("lid" => $ds->id), $this);
				$ret = false;
				$retCount = 0;
				// sometimes fetching the new data might fail (race condition). Try up to 5 times
				while (($ret === false) && ($retCount<5)) {
					sleep(1);
					$ret = $res->get();
					if (($ret === false) && ($res->getError() !== RestErrorEnum::RE_ITEM_NOT_FOUND)) {
						// give up on erinrors other than item not found
						break;
					} else {
						$retCount += 1;
					}
				}
			} else {
				$ret = true;
			}
		}
        return $ret;
	}

    /**
     * overrides RestResource::put()
     */
	public function put() {
		if ( parent::put() !== false ) {
            return new XMLFragmentRestResponse($this->putpost(RestMethodEnum::RM_PUT), $this);
		} else return false;
	}

    /**
     * overrides RestResource::post()
     */
	public function post() {
		if ( parent::post() !== false ) {
			return new XMLFragmentRestResponse($this->putpost(RestMethodEnum::RM_POST), $this);
		} else return false;
	}


    public function authorize($method) {
        $res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
            $res = true;
            break;
        case RestMethodEnum::RM_PUT:
			$res = $this->authenticate() && $this->getUser()->privs->canManageDatasets();
			if ($res !== true) {
				$this->setError(RestErrorEnum::RE_ACCESS_DENIED);
				return false;
			}
			break;
        case RestMethodEnum::RM_POST:
			$id = $this->_parser->getID($this->getData(),"dataset:location");
			if ( $this->_parser->getError() === RestErrorEnum::RE_OK ) {
				if ( $id != "" ) {
					$dss = new Default_Model_DatasetLocations();
					$dss->filter->id->equals($id);
					if ( count($dss->items) > 0 ) {
						$ds = $dss->items[0]; 
						$res = true;
					} else {
						$ds = null;
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
            $res = $res && (!is_null($ds)) && $this->authenticate() && ($this->userIsAdmin() || $this->getUser()->id === $ds->addedByID || $this->getUser()->privs->hasAccess($app));
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

/** class RestDatasetLocationItem
 * derived class for details on sites
 */
class RestDatasetLocationItem extends RestROResourceItem {
    /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     *
     */
    public function getDataType() {
        return "dataset";
	}

     /**
     * overrides RestResource::getModel()
     */
	protected function getModel() {
		$res = new Default_Model_DatasetLocations();
		$id = $this->getParam("lid");
		if ( is_numeric($id) ) {
	        $res->filter->id->equals($id)->and($res->filter->dataset_version_id->numequals((int)($this->getParam("vid"))));
		} else {
	        $res->filter->guid->ilike($id)->and($res->filter->dataset_version_id->numequals((int)($this->getParam("vid"))));
		}
		if ( ! is_null($this->getParam("orderby")) ) {
			$res->filter->orderBy($this->getParam("orderby"));
		}
		return $res;
	}

	/**
     * overrides RestResource::delete()
     */
	public function delete() {
		if ( parent::delete() !== false ) {
			$ret = $this->get();
			$locs = new Default_Model_DatasetLocations();
			$lid = $this->getParam("lid");
			$locs->filter->id->numequals((int)$lid);
			if (count($locs->items) == 1) {
				$loc = $locs->items[0];
				db()->query("DELETE FROM dataset_location_organizations WHERE dataset_location_id = ?", array($lid))->fetchAll();
				db()->query("DELETE FROM dataset_location_sites WHERE dataset_location_id = ", array($lid))->fetchAll();
				$locs->remove($loc);
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
			$item = new Default_Model_DatasetLocations();
			$item->filter->id->numequals((int)($this->getParam("lid")));
			if (count($item->items) == 1) {
				$item = $item->items[0];
			} else {
				$item = null;
			}
			if ( ! is_null($item) ) {
				if ( ! is_null($this->getUser()) ) {
					if ( $this->getUser()->privs->canManageDatasets($item) ) {
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
