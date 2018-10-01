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
 * Module providing base classes for RESTful, CRUDL compliant web API
 *
 * CRUDL Mapping:
 * Operation    |   HTTP Method     |   Notes
 * -------------------------------------------
 * Create       |   PUT             |   
 * Read         |   GET             |   listmode=details will return full 
 *              |                   |   information disclosure (full records)
 *              |                   |   listmode=normal, (or unset) will return 
 *              |                   |   basic information (no details for 
 *              |                   |   referenced objects).
 *              |                   |   default: normal
 * Update       |   POST            |
 * Delete       |   DELETE          |
 * List         |   GET             |   listmode=listing
 */

/**
 * interface iRestAPILogger
 */
interface iRestAPILogger {
    /**
     * indicate the intention to log API calls which make changes to the 
     * database
     *
     * @return void
     */
    public function startLogging($logfile);

    /**
     * indicate that logging is no longer wanted
     *
     * @return void
     */
    public function stopLogging();

    /**
     * Function to be called when an action that modifies data occurs, if 
     * logging has been requested. If @old and @new are the same, then no 
     * logging occurs. 
     * Logs are in partial XML format (no root node), and representations given 
     * in the @old and @new parameters are bzip2 compressed and stored as base64 
     * encoded node values
     *
     * @param string $event one of "insert", "update", "delete"
     * @param string $target the name of the resource that gets acted upon (e.g. 
     * "application", "person", etc., as specified by the datatype attribute of 
     * iRestResource
     * @param integer $id the id of the resource that gets modified
     * @param string $old the representation of the resource before the modification
     * @param string $new the representation of the resource after the modification
     *
     * @return void
     */
    public function logAction($event, $target, $id, $old, $new, $disposition = null);
    //public function logAction($action);
}

/**
 * interface for all REST API Helper utility classes
 */
interface iRestAPIHelper {
	static public function XMLNS_XSI();
	static public function XMLNS_XS();
	static public function getVersion();
	static public function getFolder($folder);
	static public function namespaces();
	static public function responseHead($datatype, $type = null, $count = null, $pageLength = null, $pageOffset = null, $error = null, $exterror = null, $authenticated = null);
	static public function responseTail();
	public static function wrapResponse($response, $datatype = null, $type = null, $count = null, $pageLength = null, $pageOffset = null, $error = null, $exterror = null, $authenticated = null);
}

/**
 * enumeration class for folder paths returned by helper classes
 */
class RestFolderEnum {
    const FE_CACHE_FOLDER = 0;
    const FE_XSD_FOLDER = 1;
    const FE_XSL_FOLDER = 2;
    const FE_BIN_FOLDER = 3;
}

/** 
 * class RestAPIHelper
 * utility class for REST API classes
 */
class RestAPIHelper implements iRestAPIHelper {
    const VERSION = '1.0';
    
    const XMLNS_APPDB_BASE = 'http://appdb.egi.eu/api/';
    public static function XMLNS_XSI() { return 'http://www.w3.org/2001/XMLSchema-instance'; }
    public static function XMLNS_XS() { return 'http://www.w3.org/2001/XMLSchema'; }
    public static function XMLNS_APPDB() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/appdb'; }
    public static function XMLNS_APPLICATION() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/application'; }
    public static function XMLNS_VIRTUALIZATION() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/virtualization'; }
	public static function XMLNS_CONTEXTUALIZATION() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/contextualization'; }
    public static function XMLNS_DISCIPLINE() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/discipline'; }
    public static function XMLNS_CATEGORY() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/category'; }
    public static function XMLNS_DISSEMINATION() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/dissemination'; }
    public static function XMLNS_FILTER() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/filter'; }
    public static function XMLNS_HISTORY() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/history'; }
    public static function XMLNS_RESOURCE() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/resource'; }
    public static function XMLNS_LOGISTICS() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/logistics'; }
    public static function XMLNS_MIDDLEWARE() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/middleware'; }
    public static function XMLNS_PERSON() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/person'; }
    public static function XMLNS_PRIVILEGE() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/privilege'; }
    public static function XMLNS_PERMISSION() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/permission'; }
    public static function XMLNS_PUBLICATION() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/publication'; }
    public static function XMLNS_RATING() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/rating'; }
    public static function XMLNS_RATINGREPORT() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/ratingreport'; }
    public static function XMLNS_REGIONAL() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/regional'; }
    public static function XMLNS_USER() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/user'; }
    public static function XMLNS_VO() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/vo'; }
    public static function XMLNS_LICENSE() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/license'; }
    public static function XMLNS_PROVIDER() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/provider'; }
    public static function XMLNS_PROVIDER_TEMPLATE() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/provider_template'; }
    public static function XMLNS_CLASSIFICATION() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/classification'; }
	public static function XMLNS_SITE() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/site'; }
	public static function XMLNS_ENTITY() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/entity'; }
	public static function XMLNS_ORGANIZATION() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/organization'; }
	public static function XMLNS_PROJECT() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/project'; }
	public static function XMLNS_DATASET() { return RestAPIHelper::XMLNS_APPDB_BASE . RestAPIHelper::VERSION . '/dataset'; }
	
	static public function getVersion() {
		return RestAPIHelper::VERSION;
	}

    static public function getFolder($folder) {
        switch ($folder) {
        case RestFolderEnum::FE_CACHE_FOLDER:
            return APPLICATION_PATH."/../cache/"; 
        case RestFolderEnum::FE_XSD_FOLDER:
            return APPLICATION_PATH."/configs/api/".RestAPIHelper::VERSION."/schema/";
        case RestFolderEnum::FE_XSL_FOLDER:
            return APPLICATION_PATH."/configs/api/".RestAPIHelper::VERSION."/xslt/";
        case RestFolderEnum::FE_BIN_FOLDER:
            return APPLICATION_PATH."/../bin/";
        default: 
            return null;
        }
    }

    static public function namespaces() {
        $ns = array();
        $ns[] = 'xmlns:xs="' . RestAPIHelper::XMLNS_XS() . '"';
        $ns[] = 'xmlns:xsi="' . RestAPIHelper::XMLNS_XSI() . '"';
        $ns[] = 'xmlns:appdb="' . RestAPIHelper::XMLNS_APPDB() . '"';
        $ns[] = 'xmlns:application="' . RestAPIHelper::XMLNS_APPLICATION() . '"';
        $ns[] = 'xmlns:discipline="' . RestAPIHelper::XMLNS_DISCIPLINE() . '"';
        $ns[] = 'xmlns:category="' . RestAPIHelper::XMLNS_CATEGORY() . '"';
        $ns[] = 'xmlns:dissemination="' . RestAPIHelper::XMLNS_DISSEMINATION() . '"';
        $ns[] = 'xmlns:filter="' . RestAPIHelper::XMLNS_FILTER() . '"';
        $ns[] = 'xmlns:history="' . RestAPIHelper::XMLNS_HISTORY() . '"';
        $ns[] = 'xmlns:logistics="' . RestAPIHelper::XMLNS_LOGISTICS() . '"';
        $ns[] = 'xmlns:resource="' . RestAPIHelper::XMLNS_RESOURCE() . '"';
        $ns[] = 'xmlns:middleware="' . RestAPIHelper::XMLNS_MIDDLEWARE() . '"';
        $ns[] = 'xmlns:person="' . RestAPIHelper::XMLNS_PERSON() . '"';
        $ns[] = 'xmlns:permission="' . RestAPIHelper::XMLNS_PERMISSION() . '"';
        $ns[] = 'xmlns:privilege="' . RestAPIHelper::XMLNS_PRIVILEGE() . '"';
        $ns[] = 'xmlns:publication="' . RestAPIHelper::XMLNS_PUBLICATION() . '"';
        $ns[] = 'xmlns:rating="' . RestAPIHelper::XMLNS_RATING() . '"';
        $ns[] = 'xmlns:ratingreport="' . RestAPIHelper::XMLNS_RATINGREPORT() . '"';
        $ns[] = 'xmlns:regional="' . RestAPIHelper::XMLNS_REGIONAL() . '"';
        $ns[] = 'xmlns:user="' . RestAPIHelper::XMLNS_USER() . '"';
        $ns[] = 'xmlns:vo="' . RestAPIHelper::XMLNS_VO() . '"';
        $ns[] = 'xmlns:virtualization="' . RestAPIHelper::XMLNS_VIRTUALIZATION() . '"';
		$ns[] = 'xmlns:contextualization="' . RestAPIHelper::XMLNS_CONTEXTUALIZATION() . '"';
        $ns[] = 'xmlns:license="' . RestAPIHelper::XMLNS_LICENSE() . '"';
        $ns[] = 'xmlns:provider="' . RestAPIHelper::XMLNS_PROVIDER() . '"';
        $ns[] = 'xmlns:provider_template="' . RestAPIHelper::XMLNS_PROVIDER_TEMPLATE() . '"';
        $ns[] = 'xmlns:classification="' . RestAPIHelper::XMLNS_CLASSIFICATION() . '"';
		$ns[] = 'xmlns:site="' . RestAPIHelper::XMLNS_SITE() . '"';
		$ns[] = 'xmlns:siteservice="' . RestAPIHelper::XMLNS_SITE() . '"';
		$ns[] = 'xmlns:entity="' .RestAPIHelper::XMLNS_ENTITY() . '"';
		$ns[] = 'xmlns:organization="' .RestAPIHelper::XMLNS_ORGANIZATION() . '"';
		$ns[] = 'xmlns:project="' .RestAPIHelper::XMLNS_PROJECT() . '"';
		$ns[] = 'xmlns:dataset="' .RestAPIHelper::XMLNS_DATASET() . '"';
        return $ns;
    }

	static public function responseHead($datatype, $type = null, $count = null, $pageLength = null, $pageOffset = null, $error = null, $exterror = null, $reqTime = null, $authenticated = null) {
		if (is_null($authenticated)) {
			$authenticated = false;
		}
		$resTime = microtime(true);
		if ( is_null($type) ) $type = "entry";
		db()->setFetchMode(Zend_Db::FETCH_NUM);
		try {
			$cacheState = db()->query("SELECT data FROM config WHERE var = 'cache_build_count';")->fetchAll();
			$cacheState = $cacheState[0];
			$cacheState = $cacheState[0];
		} catch (Exception $e) { /*do nothing*/ }
		try {
			$permsState = db()->query("SELECT data FROM config WHERE var = 'permissions_cache_dirty';")->fetchAll();
			$permsState = $permsState[0];
			$permsState = $permsState[0];
		} catch (Exception $e) { /*do nothing*/ }
		return '<appdb:appdb ' .
            implode(" ", RestAPIHelper::namespaces()) . ' ' .
            'datatype="'.$datatype.'" ' .
            'type="'.$type.'" ' . 
            ($type === "list" ? 'count="'.($count === null ? 0 : $count).'" ' : '' ) .
            ($type === "list" && ! is_null($pageLength) ? 'pagelength="'.$pageLength.'" ' : '' ) .
            ($type === "list" && ! is_null($pageOffset) ? 'pageoffset="'.$pageOffset.'" ' : '' ) .
			($error != "" ? 'error="'.RestErrorEnum::toString($error).'" ' : '' ) .
			($error != "" ? 'errornum="'.$error.'" ' : '' ) .
			($exterror != "" ? 'errordesc="'. htmlentities($exterror, ENT_QUOTES | ENT_XML1 | ENT_DISALLOWED, "UTF-8") .'" ' : '' ) .
			//($exterror != "" ? 'errordesc="'. htmlspecialchars($exterror, HTML_SPECIALCHARS, "UTF-8") .'" ' : '' ) .
            'host="'.$_SERVER['APPLICATION_UI_HOSTNAME'].'" ' .
			'apihost="'.$_SERVER['APPLICATION_API_HOSTNAME'].'" ' .
			'cacheState="' . $cacheState . '" ' .
			'permsState="' . $permsState . '" ' .
			'requestedOn="' . sprintf("%.3f", $reqTime) . '" ' .
			'deliveredOn="' . sprintf("%.3f", $resTime) . '" ' .
			'processingTime="' . sprintf("%.3f", $resTime - $reqTime) . '" ' .
			($authenticated ? 'authenticated="true" ' : '') .
            'version="'.RestAPIHelper::VERSION.'" >';
    }

    static public function responseTail() {
        return '</appdb:appdb>';
    }

	static public function wrapResponse($response, $datatype = null, $type = null, $count = null, $pageLength = null, $pageOffset = null, $error = null, $exterror = null, $authenticated = null) {
		if (is_null($authenticated)) {
			$authenticated = false;
		}
		$reqTime = null;
        $originalResponse = $response;
		$parent = null;
        if ( is_object($response) ) {
			$parent = $response->getParent();
			if (is_object($parent)) {
				$reqTime = $parent->getRequestTime();
			}
            if ( ! is_null($parent) ) {
                if ( is_null($error) ) {
					$error = $parent->error;
					if ( $parent->error != "" && $parent->extError != "" ) $exterror = $parent->extError;
				}
                if ( is_null($datatype) ) $datatype = $parent->dataType;
                if ( is_null($type) ) $type = ( $parent->total === -1 ? "entry" : "list" );
                if ( is_null($count) && $type === "list" ) $count = $parent->total;
                if ( is_null($pageLength) && $type === "list" ) $pageLength = $parent->pageLength;
                if ( is_null($pageOffset) && $type === "list" ) $pageOffset = $parent->pageOffset;
                $response = strval($response);
            } else {
                $response = $response->getData();
            }
        }
        if ( is_array($response) ) {
            if ( is_null($type) ) $type = "list";
            if ( is_null($count) ) $count = count($response);
            $response = implode("\n", $response);
        } else {
            if ( is_null($type) ) $type = "entry";
        }
        $response = array();
        $response[] = RestAPIHelper::responseHead($datatype, $type, $count, $pageLength, $pageOffset, $error, $exterror, $reqTime, $authenticated);
        if ( is_object($originalResponse) ) {
            $originalResponse = $originalResponse->getData();
        }
        if ( is_array($originalResponse) ) {
            $response = array_merge($response, $originalResponse); 
        } else {
            $response[] = $originalResponse;
        }
        $response[] = RestAPIHelper::responseTail();
        if ( ! is_array($originalResponse) ) $response = implode("\n", $response);
        return new XMLRestResponse($response, $parent);
	}

	static public function transformXMLtoJSON($x) {
		$json = $x;
		$xslt_path1 = RestAPIHelper::getFolder(RestFolderEnum::FE_XSL_FOLDER) . 'xml2js_preprocess.xsl';
		$xslt_path2 = RestAPIHelper::getFolder(RestFolderEnum::FE_XSL_FOLDER) . 'xml2js.xsl';
		if( file_exists($xslt_path1) && file_exists($xslt_path2) ) {
			try{
				//convert all attributes to elements
				$xsl = new DOMDocument();
				$xsl->load($xslt_path1);
				$xml = new DOMDocument();
				$xml->loadXML($x, LIBXML_NSCLEAN | LIBXML_COMPACT);
				$proc = new XSLTProcessor();
				$proc->registerPHPFunctions();
				$proc->importStylesheet($xsl);
				$json = $proc->transformToXml( $xml );
				
				//convert all attributes to json
				$xsl = new DOMDocument();
				$xsl->load($xslt_path2);
				$xml = new DOMDocument();
				$xml->loadXML($json, LIBXML_NSCLEAN | LIBXML_COMPACT);
				$proc = new XSLTProcessor();
				$proc->registerPHPFunctions();
				$proc->importStylesheet($xsl);
				$json = $proc->transformToXml( $xml );
				header('Content-type: application/json');
			}catch( Exception $e) {
				error_log('[Api::transformXmlToJson]: ' . $e->getMessage());
				return $x;
			}
		}
		
		return $json;
	}
}

/**
 *  interface iRestResponse
 *  Interface to be implemented by any class that is returned by REST methods
 */
interface iRestResponse {
    /**
     * returns the format of the REST method response
     */
    public function getFormat();

    /**
     * returns the body of the REST method response
     */
    public function getData();

    /**
     * specifies whether the REST method response is a fragment or not (e.g. 
     * full vs partial XML
     */
    public function isFragment();

    /**
     * string representation of the object
     */
    public function __toString();
}

/**
 * class RestResponse
 * Base class for all REST response classes
 */
abstract class RestResponse implements iRestResponse {
    /*** Attributes ***/
    /**
     * holds the response data which is set at construction time
     *
     */
    protected $data;
    protected $_parent;

    /**
     * constructor
     *
     * @param string $data the response body
     *
     * @return void
     *
     */
	public function __construct($data, $parent = null) {
        $this->data = $data;
        $this->_parent = $parent;
    }

    public function getParent() {
        return $this->_parent;
    }

    /**
     * getter function for data protected attribute
     *
     * @return string
     *
     */
    public function getData() {
	    return $this->data;
    }

    /**
     * realization of  __toString() from iRestResponse
     */
	public function __toString() {
        $data = $this->getData();
        if ( is_array($data) ) {
            $data = implode("\n", $data);
        }
        return $data;
    }
}

abstract class XMLRestResponseBase extends RestResponse {
	public function getFormat() {
		return "xml";
    }

    public function __toString() {
        $s = parent::__toString();
		$s = preg_replace('/<\/(\w+:)*\w+>/', "$0\n", $s);
		$s = preg_replace('/<(\w+:)*\w+\/>/', "$0\n", $s);
        return $s;
    }

	private function _transform($xslt, $data) {
		if ( $xslt != '' && $xslt != RestAPIHelper::getFolder(RestFolderEnum::FE_XSL_FOLDER) ) {
			if ( (! is_null($this->_parent)) && (is_a($this->_parent, "RestAppVAList") || is_a($this->_parent, "RestAppVAList")) ) {
				if ( ! $this->_parent->canAccessPrivateData() ) {
					debug_log("HIDING PRIVATE DATA");
					$data = $this->_parent->hidePrivateData($data);
				}
			}
			$xslt = $xslt . ".xsl";
			if (file_exists($xslt)) {
				$xsl = new DOMDocument();
				$xsl->load($xslt);
				$xml = new DOMDocument();
				$xml->loadXML($data, LIBXML_NSCLEAN | LIBXML_COMPACT);
				$proc = new XSLTProcessor();
				$proc->registerPHPFunctions();
				$proc->importStylesheet($xsl);
				$data = $proc->transformToXml( $xml );
				if ( is_null($this->_parent) || ( (! is_null($this->_parent)) && (! $this->_parent->authenticate()) ) ) {
					$xf = RestAPIHelper::getFolder(RestFolderEnum::FE_XSL_FOLDER).'person_sensitive.xsl';
					$xsl = new DOMDocument();
					$xsl->load($xf);
					$proc = new XSLTProcessor();
					$proc->registerPHPFunctions();
					$proc->importStylesheet($xsl);
					$xml = new DOMDocument();
					$xml->loadXML($data, LIBXML_NSCLEAN | LIBXML_COMPACT);
					$data = $proc->transformToXml( $xml );
				}
				if ( (! is_null($this->_parent)) && (is_a($this->_parent, "RestVOItem") || is_a($this->_parent, "RestVOList")) && (! $this->_parent->authenticate()) ) {
					$xf = RestAPIHelper::getFolder(RestFolderEnum::FE_XSL_FOLDER).'vo_sensitive.xsl';
					$xsl = new DOMDocument();
					$xsl->load($xf);
					$proc = new XSLTProcessor();
					$proc->registerPHPFunctions();
					$proc->importStylesheet($xsl);
					$xml = new DOMDocument();
					$xml->loadXML($data, LIBXML_NSCLEAN | LIBXML_COMPACT);
					$data = $proc->transformToXml( $xml );
				}
				if ( (! is_null($this->_parent)) && (is_a($this->_parent, "RestSiteItem") || is_a($this->_parent, "RestSiteList")) && (! $this->_parent->authenticate()) ) {
					$xf = RestAPIHelper::getFolder(RestFolderEnum::FE_XSL_FOLDER).'site_sensitive.xsl';
					$xsl = new DOMDocument();
					$xsl->load($xf);
					$proc = new XSLTProcessor();
					$proc->registerPHPFunctions();
					$proc->importStylesheet($xsl);
					$xml = new DOMDocument();
					$xml->loadXML($data, LIBXML_NSCLEAN | LIBXML_COMPACT);
					$data = $proc->transformToXml( $xml );
				}
				if ( (! is_null($this->_parent)) && (is_a($this->_parent, "RestAppVAList") || is_a($this->_parent, "RestAppVAList")) ) {
					if ( ! $this->_parent->canManageVAs() ) {
						debug_log("HIDING WORKING VERSION");
						$data = $this->_parent->hideWorkingVersion($data);
					}
				}
				$data = str_replace('<'. '?xml version="1.0"?'.'>', '', $data);
			} else {
				error_log('Cannot find '.$xslt);
			}
		}
        return $data;
    }

    public function transform($xslt) {
        $class = get_class($this);
        return new $class($this->_transform($xslt, strval($this)), $this->getParent());
//
//        if ( is_array($this->getData()) ) {
//            $data = array();
//            foreach($this->data as $datum) {
//                $data[] = $this->_transform($xslt, $datum);
//            }
//            $this->data = $data;
//        } else {
//            $this->data = $this->_transform($xslt, $this->getData());
//        }
    }    
}

/**
 * class XMLRestResponse
 * XML REST response class
 */
class XMLRestResponse extends XMLRestResponseBase {
	public function isFragment() {
		return false;
	}

	public function __toString() {
		$s = parent::__toString();
		if ( isset($this->_parent) ) {
			if ($this->_parent->isCacheable()) {
				try {
					$cachefile = $this->_parent->cachefile();
					if ( ! file_exists($cachefile) ) {
						debug_log("caching API response in '$cachefile'");
						$f = @fopen($cachefile, "w");
						if (! is_null($f)) {
							$ss = str_replace('<appdb:appdb ', '<appdb:appdb cached="' . time() . '" cachekey="' . $this->_parent->cachekey() . '" ', $s);
							fwrite($f, $ss);
							fclose($f);
						} else {
							error_log("Could not open file to cache API response (Permission denied?)");
						}
					}
				} catch (Exception $e) {
					error_log($e);
				}
// NOTE: Uncomment when/if cache is re-enabled
//			} else {
//				debug_log("Resource " . get_class($this->_parent) . " has no-cache intent. Result cache not stored");
			}
		}
		return $s;
	}
}

/**
 * class XMLFragmentRestResponse
 * XML fragment (i.e. partial XML) REST response class
 */
class XMLFragmentRestResponse extends XMLRestResponseBase {
    /**
     * @param XMLRestResponse $overrides::isFragment()
     */
	public function isFragment() {
		return true;
    }

    public function finalize() {
	$authed = false;
	if (! is_null($this->_parent)) {
		try {
			$authed = $this->_parent->getUser();
			if (! is_null($authed)) {
				$authed = true;
			} else {
				$authed = false;
			}
		} catch (Exception $e) {
			$authed = false;
		}
	//} else {
		//error_log("Response parent is NULL");
	}
        return RestAPIHelper::wrapResponse($this, null, null, null, null, null, null, null, $authed);
    }
}

/**
 * class JSONRestResponse
 * JSON REST response class
 */
class JSONRestResponse extends RestResponse {
	public function getFormat() {
		return "json";
	}

	public function isFragment() {
		return false;
	}

	public function __construct($data, $parent = null) {
		if ( $data instanceof XMLRestResponseBase ) {
			$datatype = $data->getParent()->datatype;
			if ( $data->isFragment() ) {
				$data = $data->finalize();
			}
			$result = $data->getData();
			if ( is_array($result) ) {
				$result = implode('', $result);
			}
			exec("bash " . RestAPIHelper::getFolder(RestFolderEnum::FE_XSL_FOLDER) . "group.xsl.template $datatype", $xf);
			$xf = implode("\n", $xf);
			$xsl = new DOMDocument();
			$xsl->loadXML($xf);
			$proc = new XSLTProcessor();
			$proc->registerPHPFunctions();
			$proc->importStylesheet($xsl);
			$xml = new DOMDocument();
			$xml->loadXML($result, LIBXML_NSCLEAN | LIBXML_COMPACT);
			$result = $proc->transformToXml( $xml );

			$xf = RestAPIHelper::getFolder(RestFolderEnum::FE_XSL_FOLDER) . 'strip_prefix.xsl';
			$xsl = new DOMDocument();
			$xsl->load($xf);
			$proc = new XSLTProcessor();
			$proc->registerPHPFunctions();
			$proc->importStylesheet($xsl);
			$xml = new DOMDocument();
			$xml->loadXML($result, LIBXML_NSCLEAN | LIBXML_COMPACT);
			$result = $proc->transformToXml( $xml );

			$xf = RestAPIHelper::getFolder(RestFolderEnum::FE_XSL_FOLDER) . 'text2att.xsl';
			$xsl = new DOMDocument();
			$xsl->load($xf);
			$proc = new XSLTProcessor();
			$proc->registerPHPFunctions();
			$proc->importStylesheet($xsl);
			$xml = new DOMDocument();
			$xml->loadXML($result, LIBXML_NSCLEAN | LIBXML_COMPACT);
			$result = $proc->transformToXml( $xml );

			$xf = RestAPIHelper::getFolder(RestFolderEnum::FE_XSL_FOLDER) . 'att2elem.xsl';
			$xsl = new DOMDocument();
			$xsl->load($xf);
			$proc = new XSLTProcessor();
			$proc->registerPHPFunctions();
			$proc->importStylesheet($xsl);
			$xml = new DOMDocument();
			$xml->loadXML($result, LIBXML_NSCLEAN | LIBXML_COMPACT);
			$result = $proc->transformToXml( $xml );

			$xf = RestAPIHelper::getFolder(RestFolderEnum::FE_XSL_FOLDER) . 'remove_empty_nodes.xsl';
			$xsl = new DOMDocument();
			$xsl->load($xf);
			$proc = new XSLTProcessor();
			$proc->registerPHPFunctions();
			$proc->importStylesheet($xsl);
			$xml = new DOMDocument();
			$xml->loadXML($result, LIBXML_NSCLEAN | LIBXML_COMPACT);
			$result = $proc->transformToXml( $xml );

			$xf = RestAPIHelper::getFolder(RestFolderEnum::FE_XSL_FOLDER) . 'xml2json.xsl';
			$xsl = new DOMDocument();
			$xsl->load($xf);
			$proc = new XSLTProcessor();
			$proc->registerPHPFunctions();
			$proc->importStylesheet($xsl);
			$xml = new DOMDocument();
			$xml->loadXML($result, LIBXML_NSCLEAN | LIBXML_COMPACT);
			$result = $proc->transformToXml( $xml );
			$data = $result;
		}
		parent::__construct($data, $parent);
	}
}

/**
 * class RestListModeEnum
 * enumeration class for REST listing mode
 */
class RestListModeEnum {
    const RL_NORMAL=0;
    const RL_LISTING=1;
    const RL_DETAILS=2;

    private $_state;

    public function __construct($state) {
        $this->_state = $state;
    }

    public static function toString($e) {
        switch($e) {
            case(RestListModeEnum::RL_NORMAL):
                return "normal";
            case(RestListModeEnum::RL_LISTING):
                return "listing";
            case(RestListModeEnum::RL_DETAILS):
                return "details";
            default:
                return null;
        }
    }

    public function __toString() {
        return RestListModeEnum::toString($this->_state);
    }
}

/**
 * class RestMethodEnum
 * enumeration class for REST methods (verbs)
 */
class RestMethodEnum {
	const RM_GET = 1;
	const RM_POST = 2;
	const RM_PUT = 4;
    const RM_DELETE = 8;
    const RM_OPTIONS = 16;
    const RM_UNKNOWN = 32;

    private $_state;

    public function __construct($state) {
        $this->_state = $state;
    }

    public static function toString($e) {
        switch($e) {
        case RestMethodEnum::RM_GET:
            return "GET";
        case RestMethodEnum::RM_POST:
            return "POST";
        case RestMethodEnum::RM_PUT:
            return "PUT";
        case RestMethodEnum::RM_DELETE:
            return "DELETE";
        case RestMethodEnum::RM_OPTIONS:
            return "OPTIONS";
        case RestMethodEnum::RM_UNKNOWN:
            return "UNKNOWN";
        default:
            return null;
        }
    }

    public function __toString() {
        return RestMethodEnum::toString($this->_state);
    }
}

/**
 * class RestErrorEnum
 * enumeration class for errors that might come up during a REST request
 */
class RestErrorEnum {
    /*** Attributes ***/

    /**
     * no error
     */
    const RE_OK = 0;
    /**
     * no user credentials, or user with no access, for a resource that 
     * requires authorization
     */ 
    const RE_ACCESS_DENIED = 1;
    /** 
     * the resource type requested exists, but the specified item does not exist in the backend
     */
    const RE_ITEM_NOT_FOUND = 2;
    /**
     * the resource representation sent to the server is invalid (e.g. 
     * mal-formated XML)
     */
    const RE_INVALID_REPRESENTATION = 3;
    /** 
     * the HTTP method makes no sense for the requested REST resource
     */
    const RE_INVALID_METHOD = 4;
    /**
     * the resource type requested does not exist
     */
    const RE_INVALID_RESOURCE = 5;
    /**
     * catch-all error for all backend errors (e.g. SQL errors, network errors, 
     * etc)
     */
    const RE_BACKEND_ERROR = 6;
    /**
     * the operation is not allowed (data soundness)
     * etc)
     */
    const RE_INVALID_OPERATION = 7;
    /**
     * holds the error state set at construction time
     */
	private $_state;

    /**
     * constructor
     *
     * @param integer $state the enumeration that represents the error state
     *
     * @return void
     *
     */
	public function __construct($state) {
		$this->_state = $state;
	}

    /**
     * PHP string representation magic function
     *
     * @return string
     *
     */
	public function __toString() {
		return RestErrorEnum::toString($this->_state);
	}

    /**
     * returns a text description of the error state
     *
     * @param integer $e the enumeration that represents the error state
     * 
     * @return string
     *
     */
	public static function toString($e) {
		switch($e) {
			case RestErrorEnum::RE_OK: return null;
			case RestErrorEnum::RE_ACCESS_DENIED: return "Access denied";
			case RestErrorEnum::RE_ITEM_NOT_FOUND: return "Resource not found";
			case RestErrorEnum::RE_INVALID_REPRESENTATION: return "Invalid resource representation";
			case RestErrorEnum::RE_INVALID_METHOD: return "Method not allowed";
			case RestErrorEnum::RE_INVALID_RESOURCE: return "Requested invalid resource";
			case RestErrorEnum::RE_BACKEND_ERROR: return "Backend error";
			case RestErrorEnum::RE_INVALID_OPERATION: return "Invalid operation";
			default: return "Unknown error";
		}
	}
}

/**
 * class RestXMLParser
 * helper class which parses XML representing a resource and stores it into the 
 * backend
 */
abstract class RestXMLParser {
    /*** Attributes ***/
    /**
     * internal error state during parsing
     *
     */
    protected $_error;
    /** 
     * internal extended error state during parsing
     *
     */
    protected $_extError;
    /** 
     * reference to the parent REST resource that instanciated us
     *
     */
    protected $_parent;
    /** 
     * reference to parent's user attribute
     *
     */
	protected $_user;

    /**
     * helper function which returns the 1st element of a certain name under 
     * the XML tree. handy due to PHP syntax restrictions
     *
     * @param SimpleXMLElement $xml the XML document at hand
     * @param string $name the name of the element that we want
     *
     * @return SimpleXMLElement
     *
     */
	protected function el($xml,$name) {
		$i = $xml->xpath($name);
		if ( count($i)>0 ) return $i[0]; else return null;
	}

    /**
     * constructor
     *
     * @param RestResource $parent the REST resource class that instanciated us
     *
     * @return void
     *
     */
	public function __construct($parent) {
		$this->_parent = $parent;
		$this->_user = $parent->getUser();
        $this->_error = RestErrorEnum::RE_OK;
        $this->_extError = null;
	}

    /**
     * getter function for _error attribute
     *
     * @return integer
     *
     */
	public function getError() {
		return $this->_error;
    }

    /**
     * getter function for _error attribute
     *
     * @return integer
     *
     */
	public function getExtError() {
		return $this->_extError;
	}

    /**
     * actual parsing function. To be implemented by each subclass
     *
     * @param SimpleXMLElement $xml the XML representation of the resource
     * 
     * @return iDefault_Model_Item
     * access public
     */
	abstract public function parse($xml);

	/**
     * eliminate duplicate entries for properties with 0..* cardinality, so 
     * that they may not be inserted twice in the database
     *
     * @param string $data[] array of application property data
     *
     *
     */
	protected function noDupes($data) {
		return $data;
	}

    /**
     * synchronize data collections such as middlwares, disciplines, etc., about an 
     * entiity in the backend
     *
     * @param string $masterName the name of the attribute that represents the 
     * entity id
     * @param integer $masterID the entity id
     * @param string $slaveName the name of the attribute that represents the 
     * collection item (slave) id
     * @param string $collectionName the classname of the class that represents the 
     * collection of items related to the entity 
     * @param string $collectionItemName the classname of the class that represents idividual 
     * items in such a collection
     * @param string $data[] array of actual collection data
     * @param string $dataSlaveName the array key used to retrieve relevant data from 
     * the array above. If empty, it is considered to be equal to @slaveName
     *
     *
     */
    protected function syncDBCollection($masterName, $masterID, $slaveName, $collectionName, $collectionItemName, &$data, $dataSlaveName = "") {
        if ( is_null($data) ) return;

		$data = $this->noDupes($data);
		
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
						$value = json_decode($value)->name;
						$mws->filter->name->equals($value);
						if ( count($mws->items) > 0 ) {
							$slaveID = $mws->items[0]->id;
						} else $slaveID = $value;
					} elseif ( $dataSlaveName == "license" ){
						$v = json_decode($value);
						if( intval($v->licenseid) == "0" ){
							$slaveID = "-1";
						}else{
							$slaveID = $v->licenseid;
						}
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
					$mws2->filter->name->equals(json_decode($value)->name);
					if ($mws2->count()>0) {
							$mwid = $mws2->items[0]->id;
							$mwcomment = null;
					} else {
							$mwid = 5;
							$mwcomment = json_decode($value)->name;
					}
					$slaveID = $mwid;
				} elseif ( $dataSlaveName == "license" ) {
					$licenseData = json_decode($value);
					if( intval($licenseData->licenseid) < 1  ){
						$slaveID = 0;
					}else{
						$slaveID = intval($licenseData->licenseid);
					}
				}else {
					$slaveID = $value;
				}
				for ($i=$collection->count()-1; $i>=0; $i--) {
					if ( $slaveID == $collection->items[$i]->$slaveName) {
						if( 
							$collectionItemName === "Default_Model_AppLicense" || 
							$collectionItemName === "Default_Model_DatasetLicense"
						) {
							$lic = $collection->items[$i];
							$lic->comment = $licenseData->comment;
							$lic->save();
						}
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
						//FIXME: Quick and dirty fix for "other" MWs registered as "5" for some reason
						if (($mwid == 5) && ($mwcomment == 5)) {
							$mwcomment = null;
						}
						if ( ! is_null($mwcomment) ) {
							debug_log("mwlink: ". json_decode($value)->link);
							$collectionItem->link = json_decode($value)->link;
						}
					} elseif ( 
						$collectionItemName == "Default_Model_AppLicense" ||
						$collectionItemName == "Default_Model_DatasetLicense" 
					) {
						$licenseData = json_decode($value);
						$collectionItem->comment = $licenseData->comment;
						if(intval($licenseData->licenseid) < 1 ){
							$collectionItem->licenseid = 0;
							$collectionItem->title = $licenseData->title;
							if (parse_url($licenseData->url, PHP_URL_SCHEME) == '') {
								$licenseData->url = 'http://'.$licenseData->url;
							}
							$collectionItem->link = $licenseData->url;
						} else {
							$collectionItem->licenseid = intval($licenseData->licenseid);
						}
					}
					$collectionItem->save();
//					$collection->add($collectionItem);
					$j++;
				}    
			}    
		}   		
	}

    /**
     * build an array of collection data from XML, so that it may be fed to 
     * syncDBCollection()
     * if a single xsi:nil element is found, then return an empty array, 
     * signifying deletion of all elements
     * if no element is found, then return null, signifying no changes to 
     * existing elements
     * otherwise, return an array of new element so that they may be synced 
     * with the existing ones (i.e. add/remove accordingly)
     *
     * @param SimpleXMLElement $xml the resource's XML representation root element
     * @param string $path XPath to the elements that belong to the collection in 
     * question
     * @param string $key the key that will be used to put data in the array
     *
     * @return string[]
     *
     */
    protected function buildCollection($xml, $path, $key) {
        $xmli = @$xml->xpath($path);
        if ( $xmli === false ) return null;
        if ( (count($xmli) === 1) && ($xmli[0]->attributes(RestAPIHelper::XMLNS_XSI())->nil) && (strval($xmli[0]->attributes(RestAPIHelper::XMLNS_XSI())->nil) == "true") ) {
            return array();
        } elseif ( count($xmli) === 0 ) {
            return null;
        } else $data = array();
        $i = 0;
        foreach ( $xmli as $xml ) {
            if ( ($xml->attributes(RestAPIHelper::XMLNS_XSI())->nil && strval($xml->attributes(RestAPIHelper::XMLNS_XSI())->nil) != "true") || (is_null($xml->attributes(RestAPIHelper::XMLNS_XSI())->nil)) ) {
                if ( $key == "mw" ) {
                    $id = '{"name": "' . str_replace('"','\'',strval($xml)) . '", "link": "' . strval($xml->attributes()->link) . '"}';
                } elseif ( $key == "url" ) {
                    $id = '{"id": "'.strval($xml->attributes()->id).'", "type": "'.strval($xml->attributes()->type).'", "url": "'.strval($xml).'", "title": "'.strval($xml->attributes()->title).'"}';
				}elseif ( $key == "license" ) {
					$lic = array("licenseid"=> "", "name"=>"", "group"=>"","title"=>"","url"=>"","comment"=>"");
					foreach($xml->attributes() as $lk=>$lv){
						$lic[strval($lk)] = strval($lv);
					}
					$cv = $xml->attributes()->id;
					$lic["licenseid"] = trim(strval($cv));
					if( $lic["licenseid"] === "" ){
						$lic["licenseid"] = "0";
					}
					$cv = $xml->xpath("./license:comment");
					$lic["comment"] = ( ( count($cv)>0 )?(strval($cv[0])):"" );
					$cv = $xml->xpath("./license:url");
					$lic["url"] = ( ( count($cv)>0 )?(strval($cv[0])):"" );
					$cv = $xml->xpath("./license:title");
					$lic["title"] = ( ( count($cv)>0 )?(strval($cv[0])):"" );
					$id = json_encode($lic);
				} else {
                    $id = strval($xml->attributes()->id);
                }
                if ( $id != "" ) {
                    $data[$key.$i] = $id;
                    $i = $i+1;
                }
            }
		}
		return $data;
	}



    /**
     * helper function that parses the XML representation of a resource and 
     * returns the root element's ID attribute, if any. Useful before calling 
     * parse(), in order to validate existence of resource in the backend
     *
     * @param SimpleXMLElement $xml the XML representation of the resource
     * @param string $path optional XPath for the main element
     *
     * @return string
     *
     */
    public function getID($xml, $path = "") {
		$this->_error = RestErrorEnum::RE_OK;
		try {
			libxml_clear_errors();
			$xml = new SimpleXMLElement($xml);
			if ( (! (libxml_get_last_error() === false)) || is_null($xml) ) {
				$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
				return null;
			}
		} catch (Exception $e) {
			$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
			return null;
		}
		if ( $path == "" ) {
			$path = "//appdb:appdb/node()[2]";
		} else {
			$path = "//appdb:appdb/".$path;
        }
		$xmli = $xml->xpath($path);
		if ( count($xmli) > 0 ) {
			$xml = $xmli[0];
			if ( $xml->attributes()->id ) {
				return strval($xml->attributes()->id);
			}
		} else {
			$this->_error = RestErrorEnum::RE_INVALID_REPRESENTATION;
			return null;
		}
		return "";
	}
}

/**
 * interface iRestAuthModule
 * Interface to be implemented by REST operations that require authorization
 */
interface iRestAuthModule {
    /**
     * authorize a request to a REST method
     *
     * @param integer $method the enumeration that represents the request method, 
     * according to RestMethodEnum
     * 
     * @return bool
     */
    public function authorize($method);
    /**
     * authenticate user according to given credentials, if any
     *
     * @return bool
     */
    public function authenticate();
    /**
     * check whether the authenticated user has an administrative role
     *
     * @return bool
     */
    public function userIsAdmin();
    /** return a reference to the authenticated user, or NULL if none
     *
     * @return Default_Model_Researcher
     */
    public function getUser();
    /** return true when the authenticated user has permission to sync a target
     *
     * @return bool
     */
    public function canSync($target);
}

/* interface iRestResource
 * the interface to be implemented by any class that handles requests to REST 
 * resources
 */
interface iRestResource {
	/**
	 * function to return the UNIX epoch whence the request was made
	 *
	 * @return int
	 */
	public function getRequestTime();
	/**
	 * function to indicate resource's caching intention (do not cache response if false)
	 *
	 * @return bool
	 */
	public function isCacheable();
  	/**
	 * function to indicate resource's caching validity time (in seconds) 
	 *
	 * @return int 
	 */
	public function getCacheLife();
//  	/**
//	 * function to set resource's caching validity time (in seconds) 
//	 *
//	 * @return int 
//	 */
//	public function setCacheLife();
	/**
     * getter function for internal array of named parameters, set at 
     * construction time
     *
     * @param string $v the name of the requested parameter
     *
     * @return string
     */
    public function getParam($v);
    /**
     * function to implement a GET REST request
     *
     * @return iRestResponse
     */
    public function get();
    /**
     * function to implement a POST REST request
     *
     * @return iRestResponse
     */
    public function post();
    /**
     * function to implement a PUT REST request
     *
     * @return iRestResponse
     */
    public function put();
    /**
     * function to implement a DELETE REST request
     *
     * @return iRestResponse
     */
    public function delete();
    /**
     * function to implement an OPTIONS REST request
     *
     * @return iRestResponse
     */
    public function options();
    /**
     * function to call if passed with an unknown HTTP request
     *
     * @return iRestResponse
     */
    public function unknown();
    /**
     * getter function for internal error state that might have occured during 
     * REST request execution
     *
     * @return RestErrorEnum
     */
    public function getError();
    /**
     * getter function for that total number of resources found to match a REST 
     * request
     *
     * @return integer
     */
    public function getTotal();
    /**
     * getter function for the name of the resource that is being represented
     *
     * @return string
     */
    public function getDataType();
    /**
     * getter function for the page length (records per page) of resources 
     * matching a REST request
     *
     * @return integer
     */
    public function getPageLength();
    /**
     * getter function for the page offset (page number minus one) of resources 
     * matching a REST request
     *
     * @return integer
     */
    public function getPageOffset();
}

/**
 * class RestResource
 * base class for all REST resource classes
 */
abstract class RestResource implements iRestResource, iRestAuthModule, iRestAPILogger {
    /*** Attributes ***/

	/**
	 * UNIX epoch whence the request was initiated
	 */
	protected $_requestTime;
	/**
	 * cache validity time, in seconds
	 */
	protected $_cacheLife;
	/**
	 * UNIX epoch whence the GET/PUT/POST method was called
	 */
	protected $_cacheable;
    /**
     * internal reference to logfile for iRestAPILogger
     * time
     */
    protected $_logfile;
    /**
     * internal reference to request parameters array, given at construction 
     * time
     */
    protected $_pars;
    /**
     * REST request error state
     */
    protected $_error;
    /**
     * extended information about the error state (e.g. debug info, backend 
     * error description, etc)
     */
    protected $_extError;
    /**
     * internal reference to the model used to fetch resources for GET HTTP requests, 
     * if any. Set by getModel()
     */
    protected $_model;
    /**
     * the id of the authenitcated user, if any
     */
    protected $_userid;
    /**
     * the actor groups of the authenticated user, if any
     */
    protected $_userGroups;
    /**
     * the HTTP method we were created for, according to RestMethodEnum
     */
	private $_method;

    /**
     * realization of canSync($target) from iRestAuthModule
     */
    public function canSync($target) {
        $res = false;
        if ( $this->authenticate() ) {
            if ( $this->userIsAdmin() ) {
                $res = true;
            } else {
                $res = $this->getUser()->privs->canSync($target);
            }
        }
        return $res;
    }

    /**
     * realization of userIsAdmin() from iRestAuthModule
     */ 
	public function userIsAdmin() {
		if ( $this->authenticate() ) {
			if ( is_array($this->_userGroups) ) {
				foreach ( $this->_userGroups as $g ) {
					if ( ($g->groupid == -1) || ($g->groupid == -2) ) {
						return true;
					}
				}
			}
		}
		return false;
	}

    /**
     * realization of getUser() from iRestAuthModule
     */ 
	public function getUser() {
		if ( isset($this->_userid) && ($this->_userid !== 0) ) {
			$users = new Default_Model_Researchers();
			$users->filter->id->equals($this->_userid);
			if ( count($users->items)>0 ) {
				return $users->items[0];
			} else return null;
		} else {
			if ( $this->authenticate() ) {
				return $this->getUser();
			} else return null;
		}
	}

    /** 
     * returns the representation of the data sent to the server during a REST 
     * request (e.g. PUTed, or POSTed as 'data=')
     *
     * @return string
     *
     */
	protected function getData() {
        if ( $this->getMethod() === RestMethodEnum::RM_PUT ) {
            if ( ! array_key_exists('data', $this->_pars) ) {
                $data = file_get_contents("php://input");
                // keep the data cached after reading php://, since the buffer 
                // is then cleared, and subsequent calls to getData would 
                // return nothing
                $this->_pars['data'] = $data;
            } else {
                $data = $this->getParam("data");    // allow manual calling, by passing data in the constructor
			}
		} elseif ( $this->getMethod() === RestMethodEnum::RM_POST || $this->getMethod() === RestMethodEnum::RM_DELETE ) {
			$data = $this->getParam("data");
		} else {
			$data = null;
        }
        $data = str_replace('&nbsp;', '&#xA0;', $data);
        return $data;
	}

    /**
     * returns the HTTP method for the REST request this object has created for, 
     * according the RestMethodEnum
     *
     * @return integer
     *
     */
	public function getMethod() {
		return $this->_method;
    }

    /**
     * check that the apikey is valid for the IP that made the request
     *
     * @param string $key the API key
     * @param string $netfilter the netfilter for which the key is valid
     *
     * @return boolean
     *
     */
    private function _validateAPIKey($key) {
        $valid = false;
	if ( $this->getParam("remoteaddr") != "" ) {
		$ip = base64_decode($this->getParam("remoteaddr"));
	} else {
	        $ip = $_SERVER['REMOTE_ADDR'];
	}
        if ( count($key->netfilters) == 0 ) $valid = true;
        foreach($key->netfilters as $netfilter) {
            if ( $netfilter == '' ) {
                // NULL netfilter
                $valid = true;
                break;
            } elseif ( isCIDR($netfilter) ) {
                if ( ipCIDRCheck($ip, $netfilter) ) {
                    $valid = true;
                    break;
                }
            } elseif ( isCIDR6($netfilter) ) {
                if ( ipCIDRCheck6($ip, $netfilter) ) {
                    $valid = true;
                    break;
                }
            } elseif ( isIPv4($netfilter) || isIPv6($netfilter) ) {
                if ( $ip == $netfilter ) {
                    $valid = true;
                    break;
                }
            } else {
                // domain name based netfilter
                $hostname = gethostbyaddr($ip);
                $netfilter = str_replace('\\', '', $netfilter);     // do not permit escaping
                if ( 
                    preg_match('/\.'.str_replace('.','\.',$netfilter).'$/', $hostname) ||   // domain name match
                    preg_match('/^'.str_replace('.','\.',$netfilter).'$/', $hostname)       // host name match
                ) {
                    $valid = true;
                    break;
                }
            }
        }
        if ( ! $valid ) error_log('Invalid API key ' . $key->key . "(ip = $ip)");
        return $valid;
    }

    private function _validateAPIKeyAuthMethod($key, $user) {
        $valid = false;
        if ( $key->authMethods == APIKeyAuthMethods::E_NONE ) {
            error_log('API key disabled (authMethods == E_NONE)');
        } elseif ( APIKeyAuthMethods::has($key->authMethods, APIKeyAuthMethods::E_SSO) && $user->accountType == 0 ) {
            $valid = true;
        } elseif ( APIKeyAuthMethods::has($key->authMethods, APIKeyAuthMethods::E_SYSTEM) && $user->accountType == 1 ) {
            if ( $user->id == $key->sysAccountID ) $valid = true;
        }
        if ( ! $valid ) debug_log('Invalid auth method for API key');
        return $valid;
    }


	public static function ldapErrorFunc($ds, $err) {
		error_log($err);
	}

	/**
     * realization of authenticate() from iRestAuthModule
     */ 
	public function authenticate() {
		//if ( ! isset($this->_userid) ) {
		if ( true ) {
			if ( (! is_null($this->getParam("userid"))) && (! is_null($this->getParam("passwd"))) && ( ! is_null($this->getParam("apikey"))) ) {
				// SAML Token auth
                $keys = new Default_Model_APIKeys();
                $keys->filter->key->equals($this->getParam("apikey"));
                if ( count($keys->items) === 1 ) {
                    if ( $this->_validateAPIKey($keys->items[0]) ) {
						$u = new Default_Model_UserCredentials();
						$u->filter->researcherid->numequals($this->getParam("userid"))->and($u->filter->sessionid->equals($this->getParam("sessionid"))->and($u->filter->token->equals($this->getParam("passwd"))));
						if( count($u->items) > 0 ) { 
							$u = new Default_Model_Researchers();
							$u->filter->id->numequals($this->getParam("userid"));
							if (count($u->items) > 0) {
								$this->_userid = $u->items[0]->id;
								$this->_userGroups = $u->items[0]->actorGroups;
								return $this->_validateAPIKeyAuthMethod($keys->items[0], $u->items[0]);
							}
                        }  	
                    }
                }
			} elseif ( (! is_null($this->getParam("username"))) && (! is_null($this->getParam("passwd"))) && ( ! is_null($this->getParam("apikey"))) ) { 
				// EGI SSO Account auth
				$keys = new Default_Model_APIKeys();
                $keys->filter->key->equals(trim($this->getParam("apikey")));
                if ( count($keys->items) === 1 ) {
                    if ( $this->_validateAPIKey($keys->items[0]) ) {				
						//$u = new Default_Model_Researchers();
                        //$u->filter->username->equals($this->getParam("username"));
						$u = new Default_Model_UserAccounts();
						$u->filter->account_type->equals('egi-sso-ldap')->and($u->filter->accountid->equals($this->getParam("username")));
						if( count($u->items) > 0 ) { 
                            $username = $this->getParam("username");
							$userid = $u->items[0]->researcherid;
							$u = $u->items[0]->researcher;
							$this->_userGroups = $u->actorGroups;
                        } else {
                            $username = null;
                        }   
						if ( $username !== null ) { 
                            $username = "uid=".$username.",ou=people,dc=egi,dc=eu";
                            $password = $this->getParam('passwd');
							$ds = initLDAP(true, $username, $password, 'RestResource::ldapErrorFunc');
							if (is_resource($ds)) { //login info was valid
	                            ldap_close($ds);
            //                  error_log('API call authenticated');
                                $this->_userid = $userid;
                                $_GET['userid'] = $userid;
                                return $this->_validateAPIKeyAuthMethod($keys->items[0], $u);
                            } else {
                                error_log('API call authentication failed');
                            }
                        }
                    }
                }
			} elseif (! is_null($this->getParam("accesstoken"))) {
				$actor = AccessTokens::getActorByToken($this->getParam("accesstoken"), true);
				if ($actor !== null) {
					if ($actor->type === "ppl") {
						$this->_userid = $actor->id;
						$_GET['userid'] = $actor->id;
						$u = new Default_Model_Researchers();
						$u->filter->id->numequals($actor->id);
						if (count($u->items) > 0) {
							$this->_userGroups = $u->items[0]->actorGroups;
						}
						return true;
					}
				} else {
					error_log("API call authentication failed: cannot map access token to actor (invalid token?)");
				}
			}
			$this->_userid = 0;
			return false;
		} else {
			return ($this->_userid !== 0);
		}
	}

    /**
     * default handler for REST requests that may not be completed
     */
    private function accessDenied() {        
		if ( $this->_error == RestErrorEnum::RE_OK) $this->setError(RestErrorEnum::RE_INVALID_METHOD);
		return false;
    }

    /**
     * returns the model that may be used to fetch GET request resources.
     * Either this or the get() function must be overriden in derived classes 
     * for meaningful GET operations
     *
     * @return iDefault_Model_ItemCollection
     *
     */
	protected function getModel() {
		return null;
	}

    /**
     * constructor
     *
     * @param string $pars[] parameters for the REST request (e.g. HTTP query string, 
     * etc)
     *
     *
     */
	public function __construct($pars = null) {
		$this->_requestTime = microtime(true);
        if ( ! is_null($pars) ) {
            $this->_pars = $pars;
        }
        if ( is_array($this->_pars) && array_key_exists('passwd', $this->_pars) ) {
            $this->_pars['passwd'] = trim($this->_pars['passwd']);
	}
		// hard-codedly disable global private chained filter in flt, now that default scope is the search target and not the whole dependence graph
		if (is_array($this->_pars) && array_key_exists('flt', $this->_pars)) {
			$this->_pars['flt'] = preg_replace("/ *\| *& *$/", "", $this->_pars['flt']);
		}
        $this->_error = RestErrorEnum::RE_OK;
		$this->_extError = null;
		$this->_cacheLife = 60; // default to 60 seconds
		$this->_cacheable = false;
// NOTE: uncomment to re-enable cache
//		$this->_cacheable = true;
		if ( isset($this->_pars['nocache']) ) {
			$this->_cacheable = ! $this->getParam('nocache');
			unset($this->_pars['nocache']);
		}
		$this->init();
	}

	public function getRequestTime() {
		return $this->_requestTime;
	}

    /* helper parameter-less function which is called by the constructor upon 
     * successful initialization. Derived classes are advised to override 
     * this function instead of the constructor, unless there is specific 
     * reason to do otherwise
     *
     *
     */
	protected function init() {
		return true;
	}

	public function isCacheable() {
		return $this->_cacheable;
	}

	public function getCacheLife() {
		return $this->_cacheLife;
	}

//	public function setCacheLife($val) {
//		$this->_cacheLife = val;
//	}

    /**
     * PHP magic property getter function
     * 
     *
     */
    public function __get($name) {
        $method = 'get' . $name;
        if (!method_exists($this, $method)) {
            throw new Exception("Invalid property: '$name'");
        }   
        $ret = $this->$method();
        return $ret;
    }

    /**
     * getter function for the internal _error attribute
     *
     *
     */
    public function getError() {
        return $this->_error;
    }

    /**
     * getter function for the internal _extError attribute
     *
     *
     */
	public function getExtError() {
		return $this->_extError;
	}

    /**
     * sets the error state, along with optional extended error info, which may 
     * be encrypted. The appropriate HTTP headers are also set, according to 
     * the error state specified.
     *
     * @param RestErrorEnum $e the error state
     * @param string $ext optional extended error information
     * @param bool $enc whether the extended error information should be encrypted or 
     * not (i.e. sensitive debug data, or not)
     *
     *
     */
	protected function setError($e, $ext = null, $enc = true) {
		$this->_error = $e;
		if ( ! is_null($ext) ) {
			if ( $enc ) {
				$this->_extError = "An unexpected error has occured. If you choose to submit a bug report, please include the above string by copying and pasting it into the report, in order to help us resolve the issue.\n\nDEBUG DATA:\n" . base64_encode(encrypt($ext, substr(ApplicationConfiguration::api('key',''), 0, 8)));
			} else {
				$this->_extError = $ext;
			}
		} else {
			$this->_extError = null;
		}
		switch($e){
			case RestErrorEnum::RE_OK:
				header("HTTP/1.0 200 OK");
				break;
			case RestErrorEnum::RE_ACCESS_DENIED:
				header("HTTP/1.0 403 Forbidden");
				break;
			case RestErrorEnum::RE_INVALID_REPRESENTATION:
				header("HTTP/1.0 400 Bad Request");
				break;
			case RestErrorEnum::RE_ITEM_NOT_FOUND:
				header("HTTP/1.0 404 Not Found");
				break;
			case RestErrorEnum::RE_INVALID_METHOD:
				header("HTTP/1.0 405 Method Not Allowed");
				break;
			case RestErrorEnum::RE_INVALID_OPERATION:
				header("HTTP/1.0 403 Forbidden");
				break;
			default:
				header("HTTP/1.0 500 Internal Server Error");
				break;
		}
	}

    /**
     * realization of getParam() from iRestResource
     */
    public function getParam($v) {
        if ( isset($this->_pars[$v] ) )
            return $this->_pars[$v];
        else
            return null;
    }

    /**
     * returns a reference to the internal array of request parameters
     *
     * @return string[]
     *
     */
	public function getParams() {
		return $this->_pars;
	}

	/** 
	 * returns a "key" which identifies a request cache file, based on the request's parameters
	 *
	 * @return string
	 *
	 */
	public function cachekey() {
		return md5(var_export($this->_pars,true));
	}

	/** 
	 * returns the name of the request's cache file. File existence is based on isCachable attribute
	 *
	 * @return string
	 *
	 */	
	public function cachefile() {		
		$ext = ".xml";
		return RestAPIHelper::getFolder(RestFolderEnum::FE_CACHE_FOLDER) . '/query_' . get_class($this) . '_' . $this->cachekey() . $ext;
	}

    /**
     * realization of get() from iRestResource. If authorization succeeds, then 
     * sets the GET model. Calls accessDenied() and returns FALSE otherwise.
     */
	public function get() {
        $this->_list = false;
		$this->_method = RestMethodEnum::RM_GET;
		if ($this->authorize(RestMethodEnum::RM_GET)) {
			$cachefile = $this->cachefile();
			//debug_log("checking API cache file '" . $cachefile . "'");
			if ( file_exists( $cachefile ) && $this->isCacheable() ) {
				$cache = file_get_contents($cachefile);
				// invalidate cache if its life span has been exeeced
				$xml = new SimpleXMLElement($cache);
				$maxcachelife = 0;
				foreach ($xml->xpath('//appdb:appdb') as $x) {
					$cachetime = strval($x->attributes()->cached);
					if (is_numeric($cachetime)) {
						$cachelife = time() - $cachetime;
						if ($cachelife > $maxcachelife) {
							$maxcachelife = $cachelife;
						}
					}
				}
				if ($maxcachelife > $this->getCacheLife()) { // unlink cache file and perform proper query
					@unlink($cachefile);
					$this->_model = $this->getModel();
				    return new XMLFragmentRestResponse("", $this);
				} else { // serve existing cache
					debug_log("serving cached data");
					// TODO: remove this code block, and add cache hooks to the RestResource interface which will properly implement
					// needed actions in subclasses
					if ((get_class($this) == "RestAppItem") || (get_class($this) == "RestPplItem") || (get_class($this) == "RestBroker")) {
						$xml = new SimpleXMLElement($cache);
						foreach ($xml->xpath('//appdb:appdb') as $x) {
							$cachetime = strval($x->attributes()->cached);
							if (is_numeric($cachetime)) {
								if (time() - $cachetime > $this->getCacheLife()) { // TODO: read min cache time from config (do not hardcode to 1min)
									$x->attributes()->cached = time();
									foreach ($x->xpath('//application:application|person:person') as $y) {
										$hitCount = strval($y->attributes()->hitcount);
										if (is_numeric($hitCount)) {
											$y->attributes()->hitcount = $hitCount + 1;
										}
									}
								}
							}
						}
						$cache = $xml->asXML();
						$f = fopen($cachefile, "w");
						fwrite($f, $cache);
						fclose($f);
					}
					return new XMLRestResponse($cache, $this);
				}
			} else {
	            $this->_model = $this->getModel();
				return new XMLFragmentRestResponse("", $this);
			};
        } else return $this->accessDenied();
    }

    /**
     * realization of post() from iRestResource. Checks for authorization and calls accessDenied() and returns FALSE if 
     * it fails. 
     */
    public function post() {
		$this->_method = RestMethodEnum::RM_POST;
        if ($this->authorize(RestMethodEnum::RM_POST)) {
            return new XMLFragmentRestResponse("", $this);
        } else return $this->accessDenied();
    }

    /**
     * realization of put() from iRestResource. Checks for authorization and calls accessDenied() and returns FALSE if 
     * it fails. 
     */
    public function put() {
		$this->_method = RestMethodEnum::RM_PUT;
        if ($this->authorize(RestMethodEnum::RM_PUT)) {
            return new XMLFragmentRestResponse("", $this);
        } else return $this->accessDenied();
    }

    /**
     * realization of delete() from iRestResource. Checks for authorization and calls accessDenied() and returns FALSE if 
     * it fails. 
     */
    public function delete() {
		$this->_method = RestMethodEnum::RM_DELETE;
        if ($this->authorize(RestMethodEnum::RM_DELETE)) {
            return new XMLFragmentRestResponse("", $this);
        } else return $this->accessDenied();
    }

    protected abstract function _options();

    /**
     * realization of options() from iRestResource. Checks for authorization and calls accessDenied() and returns FALSE if 
     * it fails. 
     */
    public function options() {
        $this->_method = RestMethodEnum::RM_OPTIONS;
        $options = array();
        foreach($this->_options() as $option) {
            $options[] = '<appdb:option xmlns:appdb="' . RestAPIHelper::XMLNS_APPDB() . '">'.RestMethodEnum::toString($option).'</appdb:option>';
        }
        return new XMLFragmentRestResponse($options, $this);
    }

    /**
     * realization of unknown() from iRestResource. Checks for authorization and calls accessDenied() and returns FALSE if 
     * it fails. 
     */
    public function unknown() {
        $this->_method = RestMethodEnum::RM_UNKNOWN;
        $this->setError(RestErrorEnum::RE_INVALID_METHOD);
        return $this->accessDenied();
    }

    /**
     * realization of startLogging() from iRestAPILogger.
     */
    public function startLogging($logfile) {
        $this->_logfile = $logfile;
    }

    /**
     * realization of stopLogging() from iRestAPILogger. 
     */
    public function stopLogging() {
        $this->_logfile = null;
    }

    /**
     * realization of logAction() from iRestAPILogger.
     */
	public function logAction($event, $target, $id, $old, $new, $disposition = null) {
		$this->logActionDB($event, $target, $id, $old, $new, $disposition);
		if ($this->_logfile != '') {
			$this->logActionFile($event, $target, $id, $old, $new, $disposition);
		}
	}

	public function logActionDB($event, $target, $id, $old, $new, $disposition = null) {
		if ( strval($old) != strval($new) ) {
			$now = new DateTime();
			$userid = $this->_userid != "" ? $this->_userid : null;
			$username = $this->_userid != "" ? $this->getUser()->name : null;
			$usercontact = $this->_userid != "" ? (is_null($this->getUser()->primaryContact) ? null : $this->getUser()->primaryContact) : null;
			db()->query("INSERT INTO apilog.actions (target, targetid, event, userid, username, usercontact, disposition, apiver, oldval, newval) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", array(
				$target,
				$id,
				$event,
				$userid,
				$username,
				$usercontact,
				$disposition,
				RestAPIHelper::getVersion(),
				($old == '' ? null : $old),
				($new == '' ? null : $new)
			))->fetchAll();
		}
	}

    public function logActionFile($event, $target, $id, $old, $new, $disposition = null) {
        if ( $this->_logfile != '' ) {
			if ( strval($old) != strval($new) ) {
                $now = new DateTime();
                $f = @fopen($this->_logfile, "a");
				if ($f !== false) {
					error_log('Acquiring exclusive lock on logfile "' . $this->_logfile . '"');
					if ( flock($f, LOCK_EX) ) {
						$userid = $this->_userid != "" ? ' userid="'.$this->_userid.'"' : '';
						$username = $this->_userid != "" ? ' username="'.$this->getUser()->name.'"' : '';
						$usercontact = $this->_userid != "" ? (is_null($this->getUser()->primaryContact) ? '' : ' usercontact="'.$this->getUser()->primaryContact.'"') : '';
						$dispo = $disposition === null ? "" : ' disposition="' . $disposition . '"';
						fwrite($f,'<action target="'.$target.'" id="'.$id.'" event="'.$event.'"'.$userid.$username.$usercontact.$dispo.' apiver="'.RestAPIHelper::getVersion().'" timestamp="'.$now->format('Ydm H:i:s').'">'."\n");
						if ( $old != "" ) fwrite($f,'<oldvalue>'.base64_encode(bzcompress($old)).'</oldvalue>'."\n");
						if ( $new != "" ) fwrite($f,'<newvalue>'.base64_encode(bzcompress($new)).'</newvalue>'."\n");
						fwrite($f,'</action>'."\n");
						flock($f, LOCK_UN);
						fclose($f);
					} else error_log('Could not acquire exclusive write log for logfile "' . $this->_logfile . '". Will not log API action.');
				} else error_log('Could not write API log entry in logfile "' . $this->_logfile . '"');
            }
        }
    }

/*    public function logAction($action) {
        if ( ! is_null($this->_logfile) ) {
            $exclude = array();
            $exclude[] = "version";
            $exclude[] = "resource";
            $exclude[] = "action";
            $exclude[] = "controller";
            $exclude[] = "format";
            $exclude[] = "routeXslt";
            $f = fopen($this->_logfile,"a");
            $now = new DateTime();
            fwrite($f,'<apiaction version="'.$this->_pars['version'].'" method="'.RestMethodEnum::toString($action).'" resource="'.get_class($this).'" '.($this->_userid!=""?'userid="'.$this->_userid.'"':'').' timestamp="'.$now->format('Ydm H:i:s').'"');
            $count = 0;
            foreach($this->_pars as $k => $v) {
                if ( ! in_array($k, $exclude) ) {
                    $count += 1;
                    if ( $count == 1 ) fwrite($f,">");
                    fwrite($f,"\n".'<parameter name="'.$k.'">'.$v.'</parameter>');
                }
            }
            if ( $count == 0 ) {
                fwrite($f,' />'."\n");
            } else {
                fwrite($f,"\n".'</apiaction>'."\n");
            }
            fclose($f);
        }
    } */
}

/**
 * class RestResourceList
 * derived base class for REST resources that are collections (lists) of other 
 * resources
 */
abstract class RestResourceList extends RestResource {
    /*** Attributes ***/

    protected $_listMode;
    protected $_pageLength;
    protected $_pageOffset;
    protected $_total;

    /**
     * constructor. _pageLength and _pageOffset attributes are initialized here from 
     * the request parameters. Their value may change after an HTTP request, to 
     * reflect the result.
     *
     *
     */
    public function __construct($pars = null) {
        parent::__construct($pars);
        $this->_pageLength = $this->getParam("pagelength");
        $this->_pageOffset = $this->getParam("pageoffset");
        if ( $this->getParam("orderbyOp") == '' ) $this->_pars['orderbyOp'] = 'ASC';
        if ( $this->getParam('listmode') === "listing" ) {
            $this->_listMode = RestListModeEnum::RL_LISTING;
        } elseif ( $this->getParam('listmode') === "details" ) {
            $this->_listMode = RestListModeEnum::RL_DETAILS;
        } else $this->_listMode = RestListModeEnum::RL_NORMAL;

    }

    /**
     * realization of getTotal from iRestResource
     */
    public function getTotal() {
        return $this->_total;
    }

    /**
     * realization of getPageOffset from iRestResource
     */
    public function getPageOffset() {
        if (! isset($this->_pageOffset) ) $this->_pageOffset = 0;
        return $this->_pageOffset;
    }

    /**
     * realization of getPageLength from iRestResource
     */
    public function getPageLength() {
        if (! isset($this->_pageLength) ) $this->_pageLength = $this->_total;
        return $this->_pageLength;
    }

    /**
     * alternative to get() which returns a short reference list, in 
     * conformance to CRUDL. Called by get when _listMode == RL_LISTING. 
     * _listMode is set in the constructor
     *
     *
     * @return iRestResponse
     */
    protected abstract function _list();

    /**
     * @param iRestResource $overrides::get()
     * if the parent's get() operation does not fail, we check for a 
     * valid GET model. If there is one, use it to return the data. If not, then just
     * delegate the parent's return value and let the derived class handle the GET request.
     */
    public function get() {
		$res = parent::get();
        if ( $res !== false ) {
			if ( ! is_null($this->_model) ) {
				if ( isset($this->_pageLength) ) $this->_model->filter->limit = $this->_pageLength;
				if ( isset($this->_pageOffset) ) $this->_model->filter->offset = $this->_pageOffset;
				if ( ! is_null($this->getParam("orderby"))) {
					if ( $this->getParam("orderby") !== "unsorted" ) {
						$this->_model->filter->orderBy($this->getParam("orderby")." ".$this->getParam("orderbyOp"));
					}
				} else {
					if ( $this->_model->filter->getOrderBy() === null ) {
						// default to order by rank, name, or description, if such a column exists
	/*                    if ( in_array("rank", $this->_model->filter->_fields)) {
							$this->_model->filter->orderBy("rank"." ".$this->getParam("orderbyOp"));
						} else*/if ( in_array("name", $this->_model->filter->_fields)) {
							$this->_model->filter->orderBy("name"." ".$this->getParam("orderbyOp"));
						} elseif ( in_array("description", $this->_model->filter->_fields)) {
							$this->_model->filter->orderBy("description"." ".$this->getParam("orderbyOp"));
						}
					}
				}
				$this->_total = $this->_model->count();
				if ( $this->_listMode === RestListModeEnum::RL_NORMAL ) {
					$this->_model->refresh("xml");
					return new XMLFragmentRestResponse($this->_model->items, $this);
				} elseif ( $this->_listMode === RestListModeEnum::RL_LISTING ) {
					// Set listmode to NORMAL so that list() may callback get() 
					// when there should be no difference
					$this->_listMode = RestListModeEnum::RL_NORMAL;
					return $this->_list();
				} elseif ( $this->_listMode === RestListModeEnum::RL_DETAILS ) {
                    $this->_model->refresh("xml", true);
					return new XMLFragmentRestResponse($this->_model->items, $this);
				}
			} else return $res;
		} else return false;
    }
}

/**
 * class RestROResourceList
 * derived base class for read-only list REST resources
 */
abstract class RestROResourceList extends RestResourceList {

    protected function _options() {
        $options = array();
        $options[] = RestMethodEnum::RM_GET;
        return $options;
    }

    /**
     * @param RestResourceList $overrides::authorize()
     */
    public function authorize($method) {
        $res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
            $res = true;
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
 * class RestROAuthResourceList
 * derived base class for read-only authoritative list REST resources
 */
abstract class RestROAuthResourceList extends RestROResourceList {

    protected function _options() {
        $options = array();
        if ( $this->authenticate() ) $options[] = RestMethodEnum::RM_GET;
        return $options;
    }

    /**
     * @param RestROResourceList $overrides::authorize()
     */
    public function authorize($method) {
        $res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
            $res = $this->authenticate();
			if ($res !== true) $this->setError(RestErrorEnum::RE_ACCESS_DENIED);
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
 * class RestROSelfAuthResourceList
 * derived base class for read-only authoritative list REST resources
 */
abstract class RestROSelfAuthResourceList extends RestROAuthResourceList {
	
	public function authorize($method) {
        $res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
			$res = $this->authenticate();
			$res = $res && (( $this->getParam("id") == $this->_userid ) || $this->userIsAdmin());
			if ($res !== true) $this->setError(RestErrorEnum::RE_ACCESS_DENIED);
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
 * class RestROAdminResourceList
 * derived base class for read-only authoritative list REST resources which require 
 * administrative privileges
 */
abstract class RestROAdminResourceList extends RestROAuthResourceList {

    protected function _options() {
        $options = array();
        if ( $this->userIsAdmin() ) $options[] = RestMethodEnum::RM_GET;
        return $options;
    }

    /**
     * @param RestROAuthResourceList $overrides::authorize()
     */
    public function authorize($method) {
        $res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
            $res = $this->userIsAdmin();
			if ($res !== true) $this->setError(RestErrorEnum::RE_ACCESS_DENIED);
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
 * class RestResourceItem
 * derived base class for REST resources that are single items and that may belong 
 * to a collection of items
 */
abstract class RestResourceItem extends RestResource {
    /*** Attributes ***/

    /**
     * internal reference to RestResourceList parent resource
     *
     */
    protected $_parent;

    /**
     * constructor.
     *
     * @param string $pars[] request parameters. If set, then parameters will be 
     * merged with the parent's parameters.
     * @param RestResourceList $parent optional reference to parent resource
     *
     *
     */
    public function __construct($pars = null, $parent = null) {
		if ( ! is_null($parent) ) {
        	$this->_parent = $parent;
			if ( ! is_null($pars) ) {
				// must put $pars as 2nd argument to give precedence to speficied keys over parent keys
				$pars = array_merge($parent->getParams(), $pars);
			} else $pars = $parent->getParams();
		}
        parent::__construct($pars);
    }

    /**
     * getter function for the _parent attribute
     *
     * @return RestResourceList
     *
     */
    public function getParent() {
        return $this->_parent;
    }

    /**
     * realization of getTotal from iRestResource
     * always returns -1 to indicate single item instead of collection
     */
    public function getTotal() {
        return -1;
    }

    /**
     * realization of getPageOffset from iRestResource
     */
    public function getPageOffset() {
        return null;
    }

    /**
     * realization of getPageLength from iRestResource
     */
    public function getPageLength() {
        return null;
    }

    /**
     * @param RestResource $overrides::get()
     * if the parent's get() operation does not fail, we check for a 
     * valid GET model. If there is one, use it to return the data. If not, then just
     * delegate the parent's return value and let the derived class handle the GET request.
     */ 
    public function get() {
		$res = parent::get();
		if ( $res === false ) {
			return $res;
		} else {
			if ( ! is_null($this->_model) && ! ($this->_model === false) ) {
				if (get_class($this) == "RestAppItem") {
					if (is_array($this->_model->items)) {
						if (count($this->_model->items) > 0) {
							$_res = $this->_model->items[0];
							if ( is_numeric($_res->id) ) {
								$this->_model->filter->id->numequals($_res->id);
							}
						}
					}
				}
				$this->_model->refresh("xml", true);
				if ( count($this->_model->items) > 0 ) {
//					if ( $this->getParam("format") === "json" ) {
//						return new JSONRestResponse(new XMLFragmentRestResponse($this->_model->items, $this), $this);
//					} else {
						return new XMLFragmentRestResponse($this->_model->items, $this);
//					}
				} else {
					$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
					return false;
				}
			} else return $res;
		}
    }
}

/**
 * class RestROResourceItem
 * derived base class for read-only REST resources that are single items
 */
abstract class RestROResourceItem extends RestResourceItem {

    protected function _options() {
        $options = array();
        $options[] = RestMethodEnum::RM_GET;
        return $options;
    }

    public function authorize($method) {
        $res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
            $res = true;
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
 * class RestROAuthResourceItem
 * derived base class for read-only authoritative REST resources that are single items
 */
abstract class RestROAuthResourceItem extends RestROResourceItem {

    protected function _options() {
        $options = array();
        if ( $this->authenticate() ) $options[] = RestMethodEnum::RM_GET;
        return $options;
    }

    /**
     * @param RestROResourceItem $overrides::authorize()
     */
    public function authorize($method) {
        $res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
            $res = $this->authenticate();
			if ($res !== true) $this->setError(RestErrorEnum::RE_ACCESS_DENIED);
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
 * class RestROSelfAuthResourceItem
 * derived base class for read-only authoritative REST resources that are single items
 */
abstract class RestROSelfAuthResourceItem extends RestROAuthResourceItem {

    public function authorize($method) {
        $res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
            $res = $this->authenticate();
			$res = $res && (( $this->getParam("id") == $this->_userid ) || $this->userIsAdmin());
			if ($res !== true) $this->setError(RestErrorEnum::RE_ACCESS_DENIED);
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
 * class RestROAdminResourceItem
 * derived base class for read-only authoritative REST resources that are single items 
 * and require administrative privileges
 */
abstract class RestROAdminResourceItem extends RestROAuthResourceItem {
   
    protected function _options() {
        $options = array();
        if ( $this->userIsAdmin() ) $options[] = RestMethodEnum::RM_GET;
        return $options;
    }

     /**
     * @param RestROResourceItem $overrides::authorize()
     */
    public function authorize($method) {
        $res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
            $res = $this->userIsAdmin();
			if ($res !== true) $this->setError(RestErrorEnum::RE_ACCESS_DENIED);
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
