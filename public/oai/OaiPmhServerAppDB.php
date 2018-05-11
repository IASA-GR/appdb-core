<?php
require('OaiPmhServerBase.php');

class OaiPmhServerAppDB extends OaiPmhServerBase {
	private $_dbname;
	private $_dbhost;
	private $_dbuser;
	private $_dbpass;
	private $_dbport;

	public function __construct($dbname, $dbhost, $dbuser, $dbpass, $dbport = "5432") {
		$this->_dbname = $dbname;
		$this->_dbhost = $dbhost;
		$this->_dbuser = $dbuser;
		$this->_dbpass = $dbpass;
		$this->_dbport = $dbport;
		$sets = array();
// Sets are populated by the DB. Add additional sets here		
//		$sets[] = array("sw", "Software");
//		$sets[] = array("va", "Virtual Appliances");
//		$sets[] = array("sa", "Software Appliances");
		parent::__construct(
			'https://appdb.egi.eu',
			'EGI Applications Database',
			'https://appdb.egi.eu/oai',
			'appdb-support@iasa.gr',
			'2008-01-01T00:00:00Z',
			'appdb.egi.eu',
			'2.0',	
			null,
			'persistent',	
			$compression = null,
			':',
			null,
			$sets
		);
	}

	private function dbQuery($sql) {
		$constr = "";
		$constr .= "dbname=$this->_dbname ";
		$constr .= "host=$this->_dbhost "; 
		$constr .= "port=$this->_dbport ";
		$constr .= "user=$this->_dbuser ";
	    $constr .= "password=$this->_dbpass";
		$conn = pg_connect($constr);
		$res = pg_query($conn, $sql);
		$r = array();
		while ($row = pg_fetch_row($res)) {
			$r[] = $row;
		}
		pg_close($conn);
		return $r;
	}
	
	protected function resume() {
		return $this->listOrResume("resume");
	}

	private function listIds() {
		return $this->listOrResume("listids", null, $this->_from, $this->_until);
	}

	private function listRecs() {
		return $this->listOrResume("list", null, $this->_from, $this->_until);
	}

	private function listOrResume($action) {
		//error_log("act=" . var_export($action, true) . " tok=" . var_export($this->_token, true) . ' from=' . var_export($this->_from, true) . ' until=' . var_export($this->_until, true));
		if (! is_null($this->_set)) {
			//$set = '\'{' . pg_escape_string($this->_set) . '}\'';
			$set = "'" . pg_escape_string($this->_set) . "'";
		} else {
			$set = 'NULL';
		}
		if (! is_null($this->_mdPrefix)) {
			$mdPrefix = "'" . pg_escape_string($this->_mdPrefix). "'";
		} else {
			$mdPrefix = "NULL";
		}
		if (! in_array($mdPrefix, array(
			"'oai_dc'",
			"'oai_datacite'",
			"'datacite'",
			"NULL" // for resumptionToken
		))) {
			return $this->requestError(OaiPmhErrorEnum::OAIPMHERR_BADFMT);
		}

		if ($action == "list") {
			$res = $this->dbQuery("SELECT oai_app_cursor($this->_from, $this->_until, NULL, FALSE, $set, $mdPrefix)");
		} elseif ($action == "resume") {
			$res = $this->dbQuery("SELECT oai_app_cursor(NULL, NULL, $this->_token)");
		} elseif ($action == "listids") {
			error_log("SELECT oai_app_cursor($this->_from, $this->_until, NULL, TRUE, $set, $mdPrefix)");
			$res = $this->dbQuery("SELECT oai_app_cursor($this->_from, $this->_until, NULL, TRUE, $set, $mdPrefix)");
		} else {
			return $this->requestError(OaiPmhErrorEnum::OAIPMHERR_INTERNAL);
		}
		if (is_array($res)) {
			$res = $res[0];
			$res = $res[0];
			$res = json_decode($res, true);
			if (array_key_exists("error", $res)) {
				return $this->requestError(OaiPmhErrorEnum::fromString($res["error"]));
			} else {
				//$header = base64_decode($res["header"]);
				$payload = base64_decode($res["payload"]);
				//$footer = base64_decode($res["footer"]);
				if (array_key_exists("resumptionToken", $res)) {
					$rt = '<' . 'resumptionToken ';
					if (array_key_exists("expirationDate", $res)) {
						$rt .= 'expirationDate="' . $res["expirationDate"] . '" ';
					}
					if (array_key_exists("completeListSize", $res)) {
						$rt .= 'completeListSize="' . $res["completeListSize"] . '" ';
					}
					if (array_key_exists("cursor", $res)) {
						$rt .= 'cursor="' . $res["cursor"] . '"';
					}
					$rt .= '>' . $res["resumptionToken"] . '<' . '/resumptionToken>';
					//error_log("resumptionToken=" . var_export($res["resumptionToken"], true));
					//error_log("cursor=" . var_export($res["cursor"], true));
				} else {
					$rt = '';
				}
//				return $this->wrapResponse($header . $payload . $footer);
				return $this->wrapResponse('<' . $this->_verb . '>' . $payload . $rt . '<' . '/' . $this->_verb . '>');
			}
		} else {
			return $this->requestError(OaiPmhErrorEnum::OAIPMHERR_INTERNAL);
		}
	}
	protected function getRecord($id) {
		// the $id parameter should hold the record's GUID
		$id = pg_escape_string($id);
		switch ($this->_mdPrefix) {
			case 'datacite':
			case 'oai_datacite':
				$res = $this->dbQuery("SELECT applications.openaire FROM applications WHERE guid = '" . $id . "'");
				break;
			case 'oai_dc':
				$res = $this->dbQuery("SELECT applications.oaidc FROM applications WHERE guid = '" . $id . "'");
				break;
			default:
				return $this->requestError(OaiPmhErrorEnum::OAIPMHERR_BADFMT);
		}
		if (is_array($res)) {
			$res = $res[0];
			$res = $res[0];
			if ($this->_mdPrefix == "datacite") {
				$xml = new SimpleXMLElement('<root><root xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . $res . '</root></root>');
				$xml = $xml->xpath("//record/metadata/*/*/*");
				$res = '';
				foreach ($xml as $x) {
					$res .= $x->asXML();
				}
				$res = '<record><header><identifier>' . $this->buildIdentifier($id) . '</identifier></header><metadata>' . $res . '</metadata></record>';
			}
			$ret = '<' . 'GetRecord>' . $res . '<' . '/GetRecord>';
		} else {
			return $this->responseError("idDoesNotExist");
		}

		$ret = $this->wrapResponse($ret);
		return $ret;
	}

	protected function listSets() {
		$ret = '<' . 'ListSets>';
		foreach ($this->_sets as $set) {
			$ret .= '<' . 'set><' . 'setSpec>' . $set[0] . '<' . '/setSpec><' . 'setName>' . $set[1] . '<' . '/setName><' . '/set>';
		}
		$res = $this->dbQuery("SELECT oai_setspecs()");
		if (is_array($res)) {
			foreach ($res as $r) {
				$r = $r[0];
				$ret .= $r;
			}
		}
		$ret .= '<' . '/ListSets>';
		return $this->wrapResponse($ret);
	}

	protected function listMetadataFormats() {
		$ret = '<' . 'ListMetadataFormats>';
		$ret .= '<' . 'metadataFormat><' . 'metadataPrefix>oai_dc<' . '/metadataPrefix>' .
			'<' . 'schema>http://www.openarchives.org/OAI/2.0/oai_dc.xsd<' . '/schema>' . 
			'<' . 'metadataNamespace>http://www.openarchives.org/OAI/2.0/oai_dc/<' . '/metadataNamespace>' . 
			'<' . '/metadataFormat>';
		$ret .= '<' . 'metadataFormat><' . 'metadataPrefix>oai_datacite<' . '/metadataPrefix>' . 
			'<' . 'schema>http://schema.datacite.org/oai/oai-1.1/oai.xsd<' . '/schema>' . 
			'<' . 'metadataNamespace>http://schema.datacite.org/oai/oai-1.1/<' . '/metadataNamespace>' . 
			'<' . '/metadataFormat>';
		$ret .= '<' . 'metadataFormat><' . 'metadataPrefix>datacite<' . '/metadataPrefix>' .
			'<' . 'schema>http://schema.datacite.org/meta/nonexistant/nonexistant.xsd<' . '/schema>' . 
			'<' . 'metadataNamespace>http://datacite.org/schema/nonexistant<' . '/metadataNamespace>' . 
			'<' . '/metadataFormat>';
		$ret .= '<' . '/ListMetadataFormats>';
		return $this->wrapResponse($ret);
	}

	protected function listIdentifiers() {
		return $this->listIds();
	}

	protected function listRecords() {
		return $this->listRecs();
	}

	protected function preprocessRequest($args) {
		$ret = parent::preprocessRequest($args);
		if ($ret !== true) {
			return $ret;
		}
		if (! is_null($this->_token)) {
			$this->_token= "'" . pg_escape_string($this->_token) . "'";
		} else {
			$this->_token = "NULL";
		}
		if (! is_null($this->_from)) {
			$this->_from = "'" . pg_escape_string($this->_from) . "'";
		} else {
			$this->_from = "NULL";
		}
		if (! is_null($this->_until)) {
			$this->_until = "'" . pg_escape_string($this->_until). "'";
		} else {
			$this->_until = "NULL";
		}
		return true;
	}
}

?>
