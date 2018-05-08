<?php

function uuid_generate_v4() {
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

abstract class OaiPmhServerBase {
	private $_host;
	private $_repoName;
	private $_baseURL;
	private $_supportEmail;
	private $_earliest;
	private $_repoID;
	private $_protoVer;
	private $_granularity;
	private $_deletedRecord;
	private $_compression;
	private $_delimiter;
	private $_sampleID;
	protected $_mdPrefix;
	protected $_verb;
	protected $_from;
	protected $_until;
	protected $_token;

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
		$sampleID = null
	) {
		$this->_host = is_null($host) ? 'localhost' : $host;
		$this->_repoName = is_null($repoName) ? 'Local OAI-PHM repository' : $repoName; 
		$this->_baseURL = is_null($baseURL) ? 'http://localhost/oai' : $baseURL;
		$this->_supportEmail = is_null($supportEmail) ? 'oai@localhost' : $supportEmail;
		$this->_earliest = is_null($earliest) ? '1970-01-01T00:00:00Z' : $earliest;
		$this->_repoID = is_null($repoID) ? 'localhost' : $repoID;
		$this->_protoVer = is_null($protoVer) ? '2.0' : $protoVer;
		$this->_granilarity = is_null($granularity) ? 'YYYY-MM-DDThh:mm:ssZ' : $granularity;
		$this->_deletedRecord = is_null($deletedRecord) ? 'persistent' : $deletedRecord;
		$this->_compression = is_null($compression) ? array() : $compression;
		$this->_delimiter = is_null($delimiter) ? ':' : $delimiter;
		$this->_sampleID = 'oai' . $this->_delimiter . $this->_repoID . $this->_delimiter . (is_null($sampleID) ? 'd6c26dd2-ec9c-442f-9fc9-0c44cb7125b1' : $sampleID);
	}

	private function responseHead() {
		$ret = '<' . '?xml version="1.0" encoding="UTF-8" ?>' . 
			'<' . '?xml-stylesheet type="text/xsl" href="xsl/oaitohtml.xsl" ?>' . 
			'<' . 'OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">' .
			'<' . 'responseDate>' . str_replace('+0000', 'Z', date(DateTime::ISO8601)) . '<' . '/responseDate>' .
			'<' . 'request verb="' . $this->_verb . '"';
		if (! is_null($this->_mdPrefix)) {
			$ret .= ' metadataPrefix="' . $this->_mdPrefix . '"';
		}
		$ret .= '>' . $this->_baseURL . '<' . '/request>';
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
			'<' . 'Identify><repositoryName>' . $this->_repoName . '<' . '/repositoryName>' .
			'<' . 'baseURL>' . $this->_baseURL . '<' . '/baseURL>' .
			'<' . 'protocolVersion>' . $this->_protoVer . '<' . '/protocolVersion>' .
			'<' . 'adminEmail>' . $this->_supportEmail . '<' . '/adminEmail>' .
			'<' . 'earliestDatestamp>'. $this->_earliest . '<' . '/earliestDatestamp>' .
			'<' . 'deletedRecord>' . $this->_deletedRecord . '<' . '/deletedRecord>' .
			'<' . 'granularity>' . $this->_granularity . '<' . '/granularity>';

		foreach ($this->_compression as $c) {
			$ret .= '<' . 'compression>' . $c . '<' . '/compression>';
		}
		$ret .= 
			'<' . 'description><' . 'oai-identifier xmlns="http://www.openarchives.org/OAI/2.0/oai-identifier" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai-identifier http://www.openarchives.org/OAI/2.0/oai-identifier.xsd">' .
			'<' . 'scheme>oai</scheme>' . 
			'<' . 'repositoryIdentifier>' . $this->_repoID . '<' . '/repositoryIdentifier>' .
			'<' . 'delimiter>' . $this->_delimiter . '<' . '/delimiter>' .
			'<' . 'sampleIdentifier>' . $this->_sampleID . '<' . '/sampleIdentifier>' .
			'<' . '/oai-identifier><' . '/description><'. '/Identify>';

		$ret = $this->wrapResponse("Identify", $ret);
		return $ret;

	}

	abstract protected function getRecord();

	abstract protected function listSets();

	abstract protected function listMetadataFormats();

	abstract protected function listIdentifiers();

	abstract protected function listRecords();

	abstract protected function resume();

	protected function requestError($code, $desc = null) {
		if ($code === 500) {
			header("HTTP/1.0 500 Internal Server Error");
			return false;
		} else {
			$ret = '<' . 'error code="' . $code . '"';
			if (isset($desc)) {
				$ret .= ">$desc<" . '/error>';
			} else {
				$ret .= '/>';
			}
			return $this->wrapResponse($ret);
		}
	}

	protected function preprocessRequest($args) {		
		$this->_token = null;
		$this->_until = null;
		$this->_from = null;
		if (isset($args["resumptionToken"])) {
			$this->_token = $args["resumptionToken"];
		} else {
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
					$ret = $this->getRecord();
					break;
				case "ListSets":
					$ret = $this->listSets();
					break;
				case "ListMetadataFormats":
					$ret = $this->listMetadataFormats();
					break;
				default:
					$ret = $this->requestError();
			}
		}
		return $ret;
	}
}
?>
