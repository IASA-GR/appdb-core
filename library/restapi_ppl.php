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
* class RestPplXMLParser
* derived class for parsing people resources
*/
class RestPplXMLParser extends RestXMLParser {
    /**  
     * implementation of abstract parse() operation from RestXMLParser.
     *
     * @xml SimpleXMLElement the root element of the application XML representation
     * 
     * @return Default_Model_Researcher
     * @access public
     */
    public function parse($xml) {
		if ( ! is_null($this->_user) ) {
			$person = new Default_Model_Researcher();
			try {
				$xml = new SimpleXMLElement($xml);
			} catch (Exception $e) {
				$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
				$this->_extError = $e->getMessage();
				return $person;
			}
			$xmli = $xml->xpath('//person:person');
			if ( count($xmli) === 0 ) {
				$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
				return $person;
			}
			$xml = $xmli[0];
			if ( $this->_parent->getMethod() === RestMethodEnum::RM_POST ) {
				if ( $xml->attributes()->id ) {
					$person->id = strval($xml->attributes()->id);
				} else {
					$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
					$this->_extError = 'Resource ID missing';
					return $person;
				}
			}
			if ( $xml->attributes()->nodissemination ) $person->noDissemination = strval($xml->attributes()->nodissemination)==="true"?true:false;
			if ( $xml->attributes()->cname ) $person->cname = strval($xml->attributes()->cname);
			$firstname = $this->el($xml, "person:firstname");
			if ( ! is_null($firstname) && trim(strval($firstname)) !== "" ) $person->firstName = trim(strval($firstname));
			$lastname = $this->el($xml, "person:lastname");
			if ( ! is_null($lastname) && trim(strval($lastname)) !== "" ) $person->lastName = trim(strval($lastname));
            $gender = $this->el($xml, "person:gender");
            if ( ! is_null($gender) ) {
                if ( trim(strval($gender->attributes(RestAPIHelper::XMLNS_XSI())->nil)) === "true" ) {
                    $person->gender = 'n/a';
                } elseif ( trim(strval($gender)) !== "" ) {
                    if ( trim(strtolower(strval($gender))) === "male" ) {
                        $person->gender = "male";
                    } elseif ( trim(strtolower(strval($gender))) === "female" ) {
                        $person->gender = "female";
                    }
                }
			}
			if ( $this->_parent->getMethod() === RestMethodEnum::RM_PUT ) {
				$person->dateInclusion = date("Y-m-d");
				$person->addedByID = $this->_parent->getUser()->id;
			}
			$person->lastUpdated = date('Y-m-d');
			$institute = trim(strval($this->el($xml, "person:institute")));
			if ( ! is_null($institute) /*&& trim(strval($institute)) !== ""*/ ) $person->institution = trim(strval($institute));
			$country = $this->el($xml, "regional:country");
			if ( ! is_null($country) && trim(strval($country->attributes()->id)) !== "" ) $person->countryID = trim(strval($country->attributes()->id));
			$role = $this->el($xml, "person:role");
			if ( ! is_null($role) && trim(strval($role->attributes()->id)) !== "" ) $person->positionTypeID = trim(strval($role->attributes()->id));
            $image = $this->el($xml, "person:image");
            $removeImageCache = false;
            if ( ! is_null($image) ) {
                if ( trim(strval($image->attributes(RestAPIHelper::XMLNS_XSI())->nil)) === "true" ) {
                    $person->clearImage();
                    $removeImageCache = true;
                } else {
                    if ( ! is_null($image->attributes()->type) && trim(strval($image->attributes()->type)) === "base64" ) {
                        // image is given as byte64 encoded string
                        if ( trim(strval($image)) != '' ) {
                            $person->image = pg_escape_bytea(trim(strval($image)));
                            $removeImageCache = true;
                        }
                    } else {
                        // image is given as URL
                        if (trim(parse_url(strval($image), PHP_URL_SCHEME)) == '') {
                            // no URL scheme present; assume uploaded file though 
                            // portal's uploadimage action in AppsController
                            if ( trim(strval($image)) != '' ) {
                                try {
                                    $person->image = pg_escape_bytea(base64_encode(file_get_contents(APPLICATION_PATH."/../public/".trim(strval($image)))));
                                    $removeImageCache = true;
                                } catch (Exception $e) {
                                    $this->_error = RestErrorEnum::RE_BACKEND_ERROR;
                                    $this->_extError = $e->getMessage();
                                    return $person;
                                }    
                            }
                        } else {
                            // URL scheme present; assume remote file
                            if ( trim(strval($image)) != '' ) {
                                try {
                                    $person->image = pg_escape_bytea(base64_encode(file_get_contents(trim(strval($image)))));
                                    $removeImageCache = true;
                                } catch (Exception $e) {
                                    $this->_error = RestErrorEnum::RE_BACKEND_ERROR;
                                    $this->_extError = $e->getMessage();
                                    return $person;
                                }    
                            }
                        }
                    }
                }
            }
            if ( $removeImageCache === true ) if ( $person->id != '' && file_exists(APPLICATION_PATH . "/../cache/ppl-image-".$person->id.".png") ) unlink(APPLICATION_PATH . "/../cache/ppl-image-".$person->id.".png");
			$person->save();

			if ( $this->_parent->getMethod() === RestMethodEnum::RM_POST ) {
				//remove existing contact info
				$conts = new Default_Model_Contacts();
				$conts->filter->researcherid->equals($person->id);
				$conts->refresh();
				for ($i = count($conts->items)-1; $i>=0; $i--) {
					$conts->remove($conts->items[$i]);
				}
			}
			//add new contact info
			$cts = new Default_Model_ContactTypes();
			$cts->refresh();
			$xmli = $xml->xpath("//person:contact");
            $conts2 = new Default_Model_Contacts();
			foreach ($xmli as $x) {
				if ( trim(strval($x)) !== '' ) { 
					$cont = new Default_Model_Contact();
					$cont->researcherID = $person->id;
					$ct = trim(strval($x->attributes()->type));
					$ctid = null;
					for ( $i = 0; $i < count($cts->items); $i++ ) {
						if (strtolower($ct) == strtolower($cts->items[$i]->description) ) {
							$ctid = $cts->items[$i]->id;
							break;
						}
					}
					if ( ! is_null($ctid) ) {
						$cont->contactTypeID = $ctid;
					} else {
						$cont->contactTypeID = 7; //e-mail by default
					}
					$cont->data = trim(strval($x));
                    if ( strval($x->attributes()->primary) === "true" ) $cont->isPrimary = true;
                    $conts2->filter->data->equals($cont->data)->and($conts2->filter->contacttypeid->equals(7))->and($conts2->filter->researcherid->notequals($person->id));
                    $conts2->refresh("xml");
                    if ( count($conts2->items) == 0 ) {
    					$cont->save();
                    } else {
                        $this->_error = RestErrorEnum::RE_BACKEND_ERROR;
                        $this->_extError = "e-mail address `".$cont->data."' already exists";
                        return $person;
                    }
				}
			}
			
			if ( $this->_parent->getMethod() === RestMethodEnum::RM_POST ||  $this->_parent->getMethod() === RestMethodEnum::RM_PUT) {
				$xrels = $xml->xpath("person:relation");
				$ps = new Default_Model_Researchers();
				$ps->filter->id->equals($person->id);
				$p = null;
				if( count($ps->items) > 0 ){
					$p = $ps->items[0];
				}
				if( $p !== null ){
					$rels = array();
					if ( count($xml->xpath('person:relation[@xsi:nil="true"]')) === 0 ) {
						foreach ($xrels as $x) {
							$targuid = trim(strval($x->attributes()->targetguid));
							$subguid = trim(strval($x->attributes()->subjectguid));
							$rel = array(
								"id" => trim(strval($x->attributes()->id)),
								"parentid" => trim(strval($x->attributes()->parentid)) 
							);

							if( $targuid === "" ){
								$rel["subjectguid"] = $subguid;
							}else if( $subguid === "" ){
								$rel["targetguid"] = $targuid;
							}

							if( $rel["parentid"] === "" ){
								$rel["parentid"] = null;
							}

							$rels[] = $rel;
						}
					}
					try{
						$res = PersonRelations::syncRelations($p->guid, $this->_user->id, $rels);
					}catch(Exception $ex){
						$res = $ex->getMessage();
					}
					
					if( is_string($res)){
						$this->_error = RestErrorEnum::RE_BACKEND_ERROR;
						$this->_extError = $res;
						return $p;
					}
				}
			}
		}
		$this->_error = RestErrorEnum::RE_OK;
		return $person;
	}
}

/* class RestPplList
 * derived class for lists of person resources
 */
class RestPplList extends RestResourceList {
   
    protected function _options() {
        $options = array();
        $options[] = RestMethodEnum::RM_GET;
        if ( $this->userIsAdmin() ) $options[] = RestMethodEnum::RM_PUT;
        if ( $this->authenticate() ) $options[] = RestMethodEnum::RM_POST;
        return $options;
    }

    protected function _list() {
   		$ret = array();
		$this->_model->refresh();
		for ($i=0; $i < count($this->_model->items); $i++) {
			$ret[] = '<person:person xmlns:person="' . RestAPIHelper::XMLNS_PERSON() . '" id="'.$this->_model->items[$i]->id.'" >'.$this->_model->items[$i]->name.'</person:person>';
		}
		return new XMLFragmentRestResponse($ret, $this);
    }

  /*** Attributes ***/

    /**
     * internal reference to XML parser, set during initialization
     * @access private
     */
    private $_parser;


    /**
     * realization of getDataType() from iRestResource
     *
     * @return string
     * @access public
     */
    public function getDataType() {
        return "person";
    }

    /**
     * @overrides init() from RestResourceList
     */
    protected function init() {
        $this->_parser = new RestPplXMLParser($this);
    }

    /**
     * @overrides getModel from RestResource
     */
	protected function getModel() {
		$ppl = new Default_Model_Researchers();
		$ppl->filter = FilterParser::getPeople($this->getParam("flt"));
		return $ppl;
	}

    /**
     * handles PUT and POST HTTP methods to REST requests
     *
     * @method integer the method enumeration according to RestMethodEnum
     *
     * @return iRestResponse
     * @access private
     */
	private function putpost($method) {
		db()->beginTransaction();
		$person = $this->_parser->parse($this->getData());
		if ( $this->_parser->getError() !== RestErrorEnum::RE_OK ) {
			db()->rollBack();
			$this->setError($this->_parser->getError(), $this->_parser->getExtError(), false);
			$ret = false;
		} else {
			db()->commit();
			$res = new RestPplItem(array("id" => $person->id), $this);
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
     * @overrides put() from RestResource
     */
	public function put() {
		if (  parent::put() !== false ) {
			return new XMLFragmentRestResponse($this->putpost(RestMethodEnum::RM_PUT), $this);
		} else return false;
	}

    /**
     * @overrides post() from RestResource
     */
	public function post() {
		if ( parent::post() !== false ) {
			return new XMLFragmentRestResponse($this->putpost(RestMethodEnum::RM_POST), $this);
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
			$id = $this->_parser->getID($this->getData(),"person:person");
			if ( $this->_parser->getError() === RestErrorEnum::RE_OK ) {
				if ( $id != "" ) {
					$researchers = new Default_Model_Researchers();
					$researchers->filter->id->equals($id);
					if ( count($researchers->items) > 0 ) {
						$researcher = $researchers->items[0];
						$res = $this->userIsAdmin() || $this->getUser()->id == $researcher->id || $this->getUser()->privs->canEditPersonProfile($researcher);
						if ( $res !== true ) $this->setError(RestErrorEnum::RE_ACCESS_DENIED);
					} else {
						$researcher = null;
						$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
						$res = false;
					}
				} else {
					$this->setError(RestErrorEnum::RE_INVALID_REPRESENTATION);
					$res = false;
				}
			} else {
				$this->setError($this->_parser->getError());
				$res = false;
			}
            break;
        case RestMethodEnum::RM_PUT:
			$res = $this->userIsAdmin();
            if ( $res !== true ) $this->setError(RestErrorEnum::RE_ACCESS_DENIED);
            break;
        case RestMethodEnum::RM_DELETE:
            $res = false;
            break;
        }
        return $res;
    }
}

/**
 * class RestPplItem
 * handles individual people entries
 */
class RestPplItem extends RestResourceItem {
   
    protected function _options() {
        $options = array();
        $options[] = RestMethodEnum::RM_GET;
        if ( $this->userIsAdmin() ) $options[] = RestMethodEnum::RM_DELETE;
        return $options;
    }

    /*** Attributes ***/

    /**
     * reference to model representing the requested resource item
     * @access private
     */
    private $_res;
    /**
     * reference to parent collection of the model representing the requested resource item
     * @access private
     */    
    private $_resParent;
    /**
     * reference to XML parser
     * @access private
     */
    private $_parser;
	private $_logged;

    /**
     * realization of getDataType from iRestResource
     */
    public function getDataType() {
        return "person";
    }

    /**
     * @overrides init() from RestResource
     */
    protected function init() {
		$this->_logged = false;
        // must call iserIsAdmin before instanciating a Default_Model_Researchers, so that
        // $_GET['userid'] is properly set
        $isAdmin = $this->userIsAdmin();
		$this->_resParent = new Default_Model_Researchers();
		if ( $isAdmin ) $this->_resParent->viewModerated = true;
		if( substr($this->getParam("name"), 0, 2) === "s:" ) {
			$s_name = substr($this->getParam("name"), 2);
			$s_name = trim($s_name);
			$this->_resParent->filter->cname->ilike($s_name);
		} else {
			$this->_resParent->filter->id->equals($this->getParam("id"));
		}
        if ( count($this->_resParent->items) > 0 ) {
            $this->_res = $this->_resParent->items[0];
        } else {
            $this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
            $this->res = null;
        }
        $this->_parser = new RestPplXMLParser($this);
    }

    /**
     * @overrides getModel() from RestResource
     */
    protected function getModel() {
		if ( ($this->getMethod() == RestMethodEnum::RM_GET) && (! $this->_logged) ) {
			$id = $this->getParam("id");
			if ( ! isset($id) ) {
				$id = "(SELECT id FROM researchers WHERE cname = '" . substr($this->getParam("name"), 2) . "')";
			}
			$cid = $this->getParam("cid"); if ( $cid == '' ) $cid = "NULL";
			$src = base64_decode($this->getParam("src")); if ( $src == '' ) $src = "NULL"; else $src = "'" . $src . "'";
			$userid = isset($this->_userid) ? $this->_userid : "NULL";
			if ( $userid == 0 ) $userid = "NULL";
			$sql = "INSERT INTO ppl_api_log (pplid, timestamp, researcherid, source, ip) VALUES (" . $id . ", NOW(), " . $userid . ", " . $cid . ", " . $src . ");";
			debug_log("$sql");
			try {
				db()->query($sql)->fetchAll();
			} catch (Exception $e) { /*ignore logging errors in case id or cname not found*/ }
			$this->_logged = true;
		}
		return $this->_resParent;
    }


//    /**
//     * @overrides delete() from RestResource
//     */
//	public function delete() {
//		if ( parent::delete() !== false ) {
//            $ret = $this->get();
//            db()->beginTransaction();
//            try {
//                $this->_resParent->remove($this->_res);
//                db()->commit();
//            } catch( Exception $e ) {
//                db()->rollBack();
//                $this->setError(RestErrorEnum::RE_BACKEND_ERROR, $e->getMessage(), true);
//                return false;
//            }
//            return $ret;
//		} else return false;
//    }

    /**
     * @overrides delete() from RestResource
     */
	public function delete() {
        if ( parent::delete() !== false ) {
            $apps = new Default_Model_Applications();
            $apps->filter->deleted->equals(false)->and($apps->filter->owner->equals($this->_res->id));
            if ( count($apps->items) > 0 ) {
                $this->setError(RestErrorEnum::RE_BACKEND_ERROR, "Cannot delete profile that owns application entries", false);
                return false;
            } else {
                $ret = $this->get();
                if ( ! $this->_res->deleted ) {
                    $this->_res->deleted = true;
                    $this->_res->delInfo->deletedBy = $this->_userid;
                    $this->_res->delInfo->deletedOn = date('Y-m-d H:i:s');
                    $this->_res->save();
                    if ( $ret !== false ) $this->logAction("delete", $this->getDataType(), $this->_res->id, $ret, null);
                }
                return $ret;
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
            $res = true;
            break;
        case RestMethodEnum::RM_POST:
        case RestMethodEnum::RM_PUT:
            break;
        case RestMethodEnum::RM_DELETE:
			$res = $this->userIsAdmin();
            if ( $res !== true ) $this->setError(RestErrorEnum::RE_ACCESS_DENIED);
            break;
        }
        return $res;
    }
}

/**
 * class RestProfile
 * returns a user profile when presented with a username and password. Useful 
 * for "login" purposes from client applications which initially do not know 
 * the userID
 */
class RestProfile extends RestROAuthResourceItem {
    /**
     * realization of getDataType from iRestResource
     */
    public function getDataType() {
        return "person";
    }

    /**
     * @overrides get() from RestResource
     */
    public function get() {
        if ( parent::get() !== false ) {
            $users = new Default_Model_Researchers();
            $username = $this->getParam("username");
//			if ( $username != '' ) {
//                $users->filter->username->equals($username);
//			} else {
//                $users->filter->id->equals($this->getParam("userid"));
//			}
			$users->filter->id->equals($this->_userid);			
            $users->refresh("xml");
            if ( count($users->items) > 0 ) {
                return new XMLFragmentRestResponse($users->items[0]);
            } else {
                // this should never happen, the call should have failed the 
                // authentication
                $this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
                return false;
            }
        } else return false;
    }
}

/**
 * class RestPplFilterNormalization
 * handles people filter syntax normalization and validation
 */
class RestPplFilterNormalization extends RestROResourceItem {
    /**
     * realization of getDataType from iRestResource
     */
    public function getDataType() {
        return "filter";
    }

    /**
     * @overrides get() from RestResource
     */
	public function get() { 
		if ( parent::get() !== false ) {
			if ( isset($this->_pars["flt"]) ) $flt = $this->_pars["flt"]; else $flt = "";
			return new XMLFragmentRestResponse(validateFilterActionHelper($flt, FilterParser::NORM_PPL), $this);
		} else return false;
	}
}


/**
 * class RestPplFilterReflection
 * handles people filter reflection requests
 */
class RestPplFilterReflection extends RestROResourceItem {
    /**
     * realization of getDataType from iRestResource
     */
    public function getDataType() {
        return "filter";
    }

    /**
     * @overrides get() from RestResource
     */
	public function get(){
		if ( parent::get() !== false ) {
			$s = '<person:filter>';
			$s .= FilterParser::fieldsToXML("any person country application vo discipline middleware category license accessgroup", "person");
			$s .= '</person:filter>';
			return new XMLFragmentRestResponse($s, $this);
		} else return false;
	}
}

/**
 * class RestDelPplList
 * handles the list of applications that have been marked as deleted
 */
class RestDelPplList extends RestROAdminResourceList {
    /**
     * realization of getDataType from iRestResource
     */
	public function getDataType() {
        return "person";
    }
    
    protected function _list() {
        return RestAppList::__list($this);
    }

    /**
     * @overrides getModel from RestResource
     */
	protected function getModel() {
		$res = new Default_Model_Researchers();
		$res->viewModerated = true;
		$res->filter = FilterParser::getPeople($this->getParam("flt"));
		$f = new Default_Model_ResearchersFilter();
		$f->deleted->equals(true);
        $res->filter->chain($f,"AND");
		return $res;
	}
}

/**
 * class RestPplLogistics
 * handles people counting per various properties
 */
class RestPplLogistics extends RestROResourceItem {
    /**
     * realization of getDataType from iRestResource
     */
    public function getDataType() {
        return "logistics";
    }
    
    /**
     * @overrides get() from RestResource
     */
	public function get($extraFilter = null) {
		if ( parent::get() !== false ) {
			global $application;
			$isAdmin = $this->userIsAdmin();
			$mapper = new Default_Model_ResearchersMapper();
			$db = $application->getBootstrap()->getResource('db');
			$flt = $this->getParam("flt");
			$select = $mapper->getDbTable()->getAdapter()->select()->distinct()->from('researchers');
			$from = '';
			$where = '';
			$orderby = '';
			$limit = '';
			$filter = FilterParser::getPeople($flt);
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
					if ( strpos($ex, 'researchers.deleted) IS FALSE') === false ) {
						$f = new Default_Model_ResearchersFilter();
						$f->deleted->equals(false);
						$filter->chain($f,"AND");
					}
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
			$rs = $db->query('SELECT * FROM ppl_logistics(?,?,?)', array($flt, $from, $where))->fetchAll();
			if ( count($rs) > 0 ) {
				$rs = $rs[0];
				$x = $rs['ppl_logistics'];
			} else {
				$x = '';
			}
			debug_log($from);
			debug_log($where);
			return new XMLFragmentRestResponse($x, $this);
		} else return false;
	}
}

class RestPplPrivList extends RestROAuthResourceList {
	public function get() {
		if ( parent::get() !== false ) {
			db()->setFetchMode(Zend_Db::FETCH_NUM);
			$res = db()->query("SELECT researcher_privs_to_xml(?, ?)", array($this->getParam("id"), $this->_userid))->fetchAll();
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
