<?php
require('OaiPmhServerBase.php');

class OaiPmhServerAppDB extends OaiPmhServerBase {
	private $_dbname;
	private $_dbhost;
	private $_dbuser;
	private $_dbpass;
	private $_dbport;

	public function __construct($dbname, $dbhost, $dbuser, $dbpass, $dbport = "5432") {
		parent::__construct(
			'https://appdb.egi.eu',
			'EGI Applications Database',
			'https://appdb.egi.eu/oai',
			'appdb-support@iasa.gr',
			null,
			'appdb.egi.eu',
			'2.0',	
			null,
			'persistent',	
			$compression = null,
			':'
		);
		$this->_dbname = $dbname;
		$this->_dbhost = $dbhost;
		$this->_dbuser = $dbuser;
		$this->_dbpass = $dbpass;
		$this->_dbport = $dbport;
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
		if ($action == "list") {
			$res = $this->dbQuery("SELECT oai_app_cursor($this->_from, $this->_until)");
		} elseif ($action == "resume") {
			$res = $this->dbQuery("SELECT oai_app_cursor(NULL, NULL, $this->_token)");
		} elseif ($action == "listids") {
			$res = $this->dbQuery("SELECT oai_app_cursor($this->_from, $this->_until, NULL, TRUE)");
		} else {
			return $this->requestError(500);
		}
		if (is_array($res)) {
			$res = $res[0];
			$res = $res[0];
			$res = json_decode($res, true);
			if (array_key_exists("error", $res)) {
				return $this->requestError($res["error"]);
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
			return $this->requestError(500);
		}
	}

	protected function getRecord() {
	
	}

	protected function listSets() {
	
	}

	protected function listMetadataFormats() {
	
	}

	protected function listIdentifiers() {
		return $this->listIds($from, $until);
	}

	protected function listRecords() {
		return $this->listRecs();
	}

	protected function preprocessRequest($args) {
		parent::preprocessRequest($args);
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
	}
}

?>
