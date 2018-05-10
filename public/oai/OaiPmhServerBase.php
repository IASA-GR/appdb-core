<?php



class OaiPmhVerbEnum {
  	const OAIPMHVERB_GETREC = 1;
	const OAIPMHVERB_ID = 2;
	const OAIPMHVERB_LISTIDS = 3;
	const OAIPMHVERB_LISTFMTS = 4;
	const OAIPMHVERB_LISTRECS = 5;
	const OAIPMHVERB_LISTSETS = 6;
    /**
     * holds the verb state set at construction time
     */
	private $_state;

    /**
     * constructor
     *
     * @param integer $state the enumeration that represents the verb state
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
		return OaiPmhVerbEnum::toString($this->_state);
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
			case OAIPMHVERB_GETREC: return "GetRecord";
			case OAIPMHVERB_ID: return "Identify";
			case OAIPMHVERB_LISTIDS: return "ListIdentifiers";
			case OAIPMHVERB_LISTFMTS: return "ListMetadataFormats";
			case OAIPMHVERB_LISTRECS: return "ListRecords";
			case OAIPMHVERB_LISTSETS: return "ListSets";
			default: return "Illegal verb";
		}
	}

	public static function fromString($s) {
		$e = 0;
		switch($s) {
			case "GetRecord";
				$e = 1;
				break;
			case "Identify";
				$e = 2;
				break;
			case "ListIdentifiers";
				$e = 3;
				break;
			case "ListMetadataFormats";
				$e = 4;
				break;
			case "ListRecords";
				$e = 5;
				break;
			case "ListSets";
				$e = 6;
				break;
			default: 
				return null;
		}
		return new OaiPmhVerbEnum($e);
	}
}

class OaiPmhErrorEnum {
	const OAIPMHERR_OK = 0;
	const OAIPMHERR_BADARG = 1;
	const OAIPMHERR_BADRESTOK = 2;
	const OAIPMHERR_BADVERB = 3;
	const OAIPMHERR_BADFMT = 4;
	const OAIPMHERR_NOTFOUND = 5;
	const OAIPMHERR_NORECS = 6;
	const OAIPMHERR_NOFMT = 7;
	const OAIPMHERR_NOSET = 8;
	const OAIPMHERR_INTERNAL = 500;
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
		return OaiPmhErrorEnum::toString($this->_state);
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
			case OaiPmhErrorEnum::OAIPMHERR_OK: return null;
			case OaiPmhErrorEnum::OAIPMHERR_BADARG: return "badArgument";
			case OaiPmhErrorEnum::OAIPMHERR_BADRESTOK: return "badResumptionToken";
			case OaiPmhErrorEnum::OAIPMHERR_BADVERB: return	"badVerb";
			case OaiPmhErrorEnum::OAIPMHERR_BADFMT: return "cannotDisseminateFormat";
			case OaiPmhErrorEnum::OAIPMHERR_NOTFOUND: return "idDoesNotExist";
			case OaiPmhErrorEnum::OAIPMHERR_NORECS: return "noRecordsMatch";
			case OaiPmhErrorEnum::OAIPMHERR_NOFMT: return "noMetadataFormats";
			case OaiPmhErrorEnum::OAIPMHERR_NOSET: return "noSetHierarchy";
			default: return "Unknown error";
		}
	}

	public static function fromString($s) {
		$e = 0;
		switch ($s) {
			case "badArgument":
				$e = 1;
				break;
			case "badResumptionToken":
				$e = 2;
				break;
			case "badVerb":
				$e = 3;
				break;
			case "cannotDisseminateFormat":
				$e = 4;
				break;
			case "idDoesNotExist":
				$e = 5;
				break;
			case "noRecordsMatch":
				$e = 6;
				break;
			case "noMetadataFormats":
				$e = 7;
				break;
			case "noSetHierarchy":
				$e = 8;
				break;
			default: 
				$e = 500;
		}
		return new OaiPmhErrorEnum($e);
	}
}

abstract class OaiPmhServerBase {
	protected $_host;
	protected $_repoName;
	protected $_baseURL;
	protected $_supportEmail;
	protected $_earliest;
	protected $_repoID;
	protected $_protoVer;
	protected $_granularity;
	protected $_deletedRecord;
	protected $_compression;
	protected $_delimiter;
	protected $_sampleID;
	protected $_mdPrefix;
	protected $_verb;
	protected $_from;
	protected $_until;
	protected $_token;
	protected $_sets;
	protected $_set;
	private $_scheme;

	public function __construct(
		$host = null, 
		$repoName = null,
		$baseURL = null,
		$supportEmail = null,
		$earliest = null,
		$repoID = null,
		$protoVer = null,
		$granularity = null,
		$deletedRecord = null,
		$compression = null,
		$delimiter = null,
		$sampleID = null,
		$sets = null
	) {
		$this->_scheme = 'oai';
		$this->_host = is_null($host) ? 'localhost' : $host;
		$this->_repoName = is_null($repoName) ? 'Local OAI-PHM repository' : $repoName; 
		$this->_baseURL = is_null($baseURL) ? 'http://localhost/oai' : $baseURL;
		$this->_supportEmail = is_null($supportEmail) ? 'oai@localhost' : $supportEmail;
		$this->_earliest = is_null($earliest) ? '1970-01-01T00:00:00Z' : $earliest;
		$this->_repoID = is_null($repoID) ? 'localhost' : $repoID;
		$this->_protoVer = is_null($protoVer) ? '2.0' : $protoVer;
		$this->_granularity = is_null($granularity) ? 'YYYY-MM-DDThh:mm:ssZ' : $granularity;
		$this->_deletedRecord = is_null($deletedRecord) ? 'persistent' : $deletedRecord;
		$this->_compression = is_null($compression) ? array() : $compression;
		$this->_delimiter = is_null($delimiter) ? ':' : $delimiter;
		$this->_sampleID = $this->buildIdentifier($sampleID);
		$this->_sets = is_null($sets) ? array() : $sets;
	}

	protected function safeXML($s) {
		return htmlspecialchars($s, ENT_XML1, 'UTF-8');
	}

	private function uuid_generate_v4() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

			// 16 bits for "time_mid"
			mt_rand( 0, 0xffff ),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand( 0, 0x0fff ) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand( 0, 0x3fff ) | 0x8000,

			// 48 bits for "node"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	} 
	protected function buildIdentifier($id = null) {
		if (is_null($id)) {
			$id = $this->uuid_generate_v4();
		}
		return $this->_scheme . $this->safeXML($this->_delimiter) . $this->safeXML($this->_repoID) . $this->safeXML($this->_delimiter) . $this->safeXML($id);
	}

	private function responseHead() {
		$ret = '<' . '?xml version="1.0" encoding="UTF-8" ?' .'>' . 
			'<' . '?xml-stylesheet type="text/xsl" href="xsl/oaitohtml.xsl" ?' . '>' . 
			'<' . 'OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">' .
			'<' . 'responseDate>' . str_replace('+0000', 'Z', date(DateTime::ISO8601)) . '<' . '/responseDate>' .
			'<' . 'request';
		if (! is_null($this->_verb)) {
			if (! is_null(OaiPmhVerbEnum::fromString($this->_verb))) {
				$ret .= ' verb="' . $this->safeXML($this->_verb) . '"';
			}
		}
		if (! is_null($this->_mdPrefix)) {
			$ret .= ' metadataPrefix="' . $this->safeXML($this->_mdPrefix) . '"';
		}
		$ret .= '>' . $this->safeXML($this->_baseURL) . '<' . '/request>';
		return $ret;
	}

	private function responseTail() {
		$ret = '<' . '/OAI-PMH>';
		return $ret;
	}	

	protected function wrapResponse($response) {
		return $this->responseHead() . $response . $this->responseTail();
	}

	protected function identify() {
		$ret = 
			'<' . 'Identify><repositoryName>' . $this->safeXML($this->_repoName) . '<' . '/repositoryName>' .
			'<' . 'baseURL>' . $this->safeXML($this->_baseURL) . '<' . '/baseURL>' .
			'<' . 'protocolVersion>' . $this->safeXML($this->_protoVer) . '<' . '/protocolVersion>' .
			'<' . 'adminEmail>' . $this->safeXML($this->_supportEmail) . '<' . '/adminEmail>' .
			'<' . 'earliestDatestamp>'. $this->safeXML($this->_earliest) . '<' . '/earliestDatestamp>' .
			'<' . 'deletedRecord>' . $this->safeXML($this->_deletedRecord) . '<' . '/deletedRecord>' .
			'<' . 'granularity>' . $this->safeXML($this->_granularity) . '<' . '/granularity>';

		foreach ($this->_compression as $c) {
			$ret .= '<' . 'compression>' . $this->safeXML($c) . '<' . '/compression>';
		}
		$ret .= 
			'<' . 'description><' . 'oai-identifier xmlns="http://www.openarchives.org/OAI/2.0/oai-identifier" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai-identifier http://www.openarchives.org/OAI/2.0/oai-identifier.xsd">' .
			'<' . 'scheme>oai</scheme>' . 
			'<' . 'repositoryIdentifier>' . $this->safeXML($this->_repoID) . '<' . '/repositoryIdentifier>' .
			'<' . 'delimiter>' . $this->safeXML($this->_delimiter) . '<' . '/delimiter>' .
			'<' . 'sampleIdentifier>' . $this->safeXML($this->_sampleID) . '<' . '/sampleIdentifier>' .
			'<' . '/oai-identifier><' . '/description><'. '/Identify>';
		$ret = $this->wrapResponse($ret);
		return $ret;

	}

	abstract protected function getRecord($id);

	abstract protected function listSets();

	abstract protected function listMetadataFormats();

	abstract protected function listIdentifiers();

	abstract protected function listRecords();

	abstract protected function resume();

	protected function requestError($code, $desc = null) {
		if ($code === OaiPmhErrorEnum::OAIPMHERR_OK) {
			// this shouldn't happen
			return true;
		}
		elseif ($code === OaiPmhErrorEnum::OAIPMHERR_INTERNAL) {
			header("HTTP/1.0 500 Internal Server Error");
			return false;
		} else {
			if (is_null($desc)) {
				switch ($code) {
					case OaiPmhErrorEnum::OAIPMHERR_BADARG:
						$desc = 'The request includes illegal arguments, is missing required arguments, includes a repeated argument, or values for arguments have an illegal syntax.';
						break;
					case OaiPmhErrorEnum::OAIPMHERR_BADRESTOK:
						$desc = 'The value of the resumptionToken argument is invalid or expired.';
						break;
					case OaiPmhErrorEnum::OAIPMHERR_BADVERB:
						$desc = 'Value of the verb argument is not a legal OAI-PMH verb, the verb argument is missing, or the verb argument is repeated.';
						break;
					case OaiPmhErrorEnum::OAIPMHERR_BADFMT:
						$desc = 'The metadata format identified by the value given for the metadataPrefix argument is not supported by the item or by the repository.';
						break;
					case OaiPmhErrorEnum::OAIPMHERR_NOTFOUND:
						$desc = 'The value of the identifier argument is unknown or illegal in this repository.';
						break;
					case OaiPmhErrorEnum::OAIPMHERR_NORECS:
						$desc = 'The combination of the values of the from, until, set and metadataPrefix arguments results in an empty list.';
						break;
					case OaiPmhErrorEnum::OAIPMHERR_NOFMT:
						$desc = 'There are no metadata formats available for the specified item.';
						break;
					case OaiPmhErrorEnum::OAIPMHERR_NOSET:
						$desc = 'The repository does not support sets.';
						break;
					default:
				}
			}
			$ret = '<' . 'error code="' . $this->safeXML(OaiPmhErrorEnum::toString($code)) . '"';
			$ret .= '>' . $this->safeXML($desc) . '<' . '/error>';
			return $this->wrapResponse($ret);
		}
	}

	protected function preprocessRequest($args) {		
		$this->_token = null;
		$this->_until = null;
		$this->_from = null;
		$this->_set = null;
		if (isset($args["resumptionToken"])) {
			$this->_token = $args["resumptionToken"];
		} else {
			if (isset($args["set"])) {
				if (is_null($this->_sets)) {
					return $this->requestError(OaiPmhErrorEnum::OAIPMHERR_NOSET);
				} else {
					$this->_set = $args["set"];
				}
			}
			if (isset($args["from"])) {
				$this->_from = $args["from"];
			}
			if (isset($args["until"])) {
				$this->_until = $args["until"];
			}
		}
	}

	public function processRequest($args) {		
		$this->preprocessRequest($args);
		$ret = false;
		if (isset($args["verb"])) {
			$this->_verb = $args["verb"];
			if (isset($args["resumptionToken"])) {
				$ret = $this->resume();
				return $ret;
			} elseif (isset($args["metadataPrefix"])) {						
				$this->_mdPrefix = $args["metadataPrefix"];
			}
			switch ($args["verb"]) {
				case "ListIdentifiers":
					$ret = $this->listIdentifiers();
					break;
				case "ListRecords":
					$ret = $this->listRecords();
					break;
				case "Identify":
					$ret = $this->identify();
					break;
				case "GetRecord":
					if (isset($args["identifier"])) {
						error_log("id=" . var_export($args["identifier"], true));
						error_log("guid=" . var_export(str_replace("oai" . $this->_delimiter . $this->_repoID . $this->_delimiter, '', $args["identifier"]), true));
						$ret = $this->getRecord(str_replace("oai" . $this->_delimiter . $this->_repoID . $this->_delimiter, '', $args["identifier"]));
					} else {
						$ret = $this->requestError(OaiPmhErrorEnum::OAIPMHERR_BADARG);
					}					
					break;
				case "ListSets":
					$ret = $this->listSets();
					break;
				case "ListMetadataFormats":
					$ret = $this->listMetadataFormats();
					break;
				default:
					$ret = $this->requestError(OaiPmhErrorEnum::OAIPMHERR_BADVERB, "Illegal verb");
			}
		} else {
			$ret = $this->requestError(OaiPmhErrorEnum::OAIPMHERR_BADVERB, "Missing verb");
		}
		return $ret;
	}
}
?>
