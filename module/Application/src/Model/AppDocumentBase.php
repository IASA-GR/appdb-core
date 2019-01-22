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
?>
<?php
class Default_Model_AppDocumentBase
{
	protected $_mapper;
	protected $_id;
	protected $_appID;
	protected $_application;
	protected $_title;
	protected $_url;
	protected $_conference;
	protected $_proceedings;
	protected $_isbn;
	protected $_pageStart;
	protected $_pageEnd;
	protected $_volume;
	protected $_publisher;
	protected $_year;
	protected $_mainAuthor;
	protected $_docTypeID;
	protected $_docType;
	protected $_journal;
	protected $_guID;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid AppDocument property: '$name'");
		}
		if ( is_string($value) ) {
			$value = str_replace("'","’",$value);
			$value = str_replace('"','”',$value);
		}
		$this->$method($value);
	}

	public function __get($name)
	{
		$method = 'get' . $name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid AppDocument property: '$name'");
		}
		$ret = $this->$method();
		if ( is_string($ret) ) {
			$ret= str_replace("'","’",$ret);
			$ret = str_replace('"','”',$ret);
		}
		return $ret;
	}

	public function setOptions(array $options)
	{
		$methods = get_class_methods($this);
		foreach ($options as $key => $value) {
			$method = 'set' . ucfirst($key);
			if (in_array($method, $methods)) {
				$this->$method($value);
			}
		}
		return $this;
	}

	public function setId($value)
	{
		/* if ( $value === null ) {
			$this->_id = 'NULL';
		} else */ $this->_id = $value;
		return $this;
	}

	public function getId()
	{
		return $this->_id;
	}

	public function setAppID($value)
	{
		/* if ( $value === null ) {
			$this->_appID = 'NULL';
		} else */ $this->_appID = $value;
		return $this;
	}

	public function getAppID()
	{
		return $this->_appID;
	}

	public function getApplication()
	{
		if ( $this->_application === null ) {
			$Applications = new Default_Model_Applications();
			$Applications->filter->id->equals($this->getAppID());
			if ($Applications->count() > 0) $this->_application = $Applications->items[0];
		}
		return $this->_application;
	}

	public function setApplication($value)
	{
		if ( $value === null ) {
			$this->setAppID(null);
		} else {
			$this->setAppID($value->getId());
		}
	}


	public function setTitle($value)
	{
		/* if ( $value === null ) {
			$this->_title = 'NULL';
		} else */ $this->_title = $value;
		return $this;
	}

	public function getTitle()
	{
		return $this->_title;
	}

	public function setUrl($value)
	{
		/* if ( $value === null ) {
			$this->_url = 'NULL';
		} else */ $this->_url = $value;
		return $this;
	}

	public function getUrl()
	{
		return $this->_url;
	}

	public function setConference($value)
	{
		/* if ( $value === null ) {
			$this->_conference = 'NULL';
		} else */ $this->_conference = $value;
		return $this;
	}

	public function getConference()
	{
		return $this->_conference;
	}

	public function setProceedings($value)
	{
		/* if ( $value === null ) {
			$this->_proceedings = 'NULL';
		} else */ $this->_proceedings = $value;
		return $this;
	}

	public function getProceedings()
	{
		return $this->_proceedings;
	}

	public function setIsbn($value)
	{
		/* if ( $value === null ) {
			$this->_isbn = 'NULL';
		} else */ $this->_isbn = $value;
		return $this;
	}

	public function getIsbn()
	{
		return $this->_isbn;
	}

	public function setPageStart($value)
	{
		/* if ( $value === null ) {
			$this->_pageStart = 'NULL';
		} else */ $this->_pageStart = $value;
		return $this;
	}

	public function getPageStart()
	{
		return $this->_pageStart;
	}

	public function setPageEnd($value)
	{
		/* if ( $value === null ) {
			$this->_pageEnd = 'NULL';
		} else */ $this->_pageEnd = $value;
		return $this;
	}

	public function getPageEnd()
	{
		return $this->_pageEnd;
	}

	public function setVolume($value)
	{
		/* if ( $value === null ) {
			$this->_volume = 'NULL';
		} else */ $this->_volume = $value;
		return $this;
	}

	public function getVolume()
	{
		return $this->_volume;
	}

	public function setPublisher($value)
	{
		/* if ( $value === null ) {
			$this->_publisher = 'NULL';
		} else */ $this->_publisher = $value;
		return $this;
	}

	public function getPublisher()
	{
		return $this->_publisher;
	}

	public function setYear($value)
	{
		/* if ( $value === null ) {
			$this->_year = 'NULL';
		} else */ $this->_year = $value;
		return $this;
	}

	public function getYear()
	{
		return $this->_year;
	}

	public function setMainAuthor($value)
	{
		/* if ( $value === null ) {
			$this->_mainAuthor = 'NULL';
		} else */ $this->_mainAuthor = $value;
		return $this;
	}

	public function getMainAuthor()
	{
		$v = $this->_mainAuthor;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setDocTypeID($value)
	{
		/* if ( $value === null ) {
			$this->_docTypeID = 'NULL';
		} else */ $this->_docTypeID = $value;
		return $this;
	}

	public function getDocTypeID()
	{
		return $this->_docTypeID;
	}

	public function getDocType()
	{
		if ( $this->_docType === null ) {
			$DocTypes = new Default_Model_DocTypes();
			$DocTypes->filter->id->equals($this->getDocTypeID());
			if ($DocTypes->count() > 0) $this->_docType = $DocTypes->items[0];
		}
		return $this->_docType;
	}

	public function setDocType($value)
	{
		if ( $value === null ) {
			$this->setDocTypeID(null);
		} else {
			$this->setDocTypeID($value->getId());
		}
	}


	public function setJournal($value)
	{
		/* if ( $value === null ) {
			$this->_journal = 'NULL';
		} else */ $this->_journal = $value;
		return $this;
	}

	public function getJournal()
	{
		return $this->_journal;
	}

	public function setGuID($value)
	{
		/* if ( $value === null ) {
			$this->_guID = 'NULL';
		} else */ $this->_guID = $value;
		return $this;
	}

	public function getGuID()
	{
		return $this->_guID;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_AppDocumentsMapper());
		}
		return $this->_mapper;
	}

	public function save()
	{
		$this->getMapper()->save($this);
	}

	public function find($id)
	{
		$this->getMapper()->find($id, $this);
		return $this;
	}

	public function fetchAll($args = null)
	{
		return $this->getMapper()->fetchAll($args);
	}

	public function toXML($recursive=false)
	{
		$XML = "<AppDocument>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_appID !== null) $XML .= "<appID>".$this->_appID."</appID>\n";
		if ( $recursive ) if ( $this->_application === null ) $this->getApplication();
		if ( ! ($this->_application === null) ) $XML .= $this->_application->toXML();
		if ($this->_title !== null) $XML .= "<title>".recode_string("utf8..xml",$this->_title)."</title>\n";
		if ($this->_url !== null) $XML .= "<url>".recode_string("utf8..xml",$this->_url)."</url>\n";
		if ($this->_conference !== null) $XML .= "<conference>".recode_string("utf8..xml",$this->_conference)."</conference>\n";
		if ($this->_proceedings !== null) $XML .= "<proceedings>".recode_string("utf8..xml",$this->_proceedings)."</proceedings>\n";
		if ($this->_isbn !== null) $XML .= "<isbn>".recode_string("utf8..xml",$this->_isbn)."</isbn>\n";
		if ($this->_pageStart !== null) $XML .= "<pageStart>".$this->_pageStart."</pageStart>\n";
		if ($this->_pageEnd !== null) $XML .= "<pageEnd>".$this->_pageEnd."</pageEnd>\n";
		if ($this->_volume !== null) $XML .= "<volume>".recode_string("utf8..xml",$this->_volume)."</volume>\n";
		if ($this->_publisher !== null) $XML .= "<publisher>".recode_string("utf8..xml",$this->_publisher)."</publisher>\n";
		if ($this->_year !== null) $XML .= "<year>".$this->_year."</year>\n";
		if ($this->_mainAuthor !== null) $XML .= "<mainAuthor>".$this->_mainAuthor."</mainAuthor>\n";
		if ($this->_docTypeID !== null) $XML .= "<docTypeID>".$this->_docTypeID."</docTypeID>\n";
		if ( $recursive ) if ( $this->_docType === null ) $this->getDocType();
		if ( ! ($this->_docType === null) ) $XML .= $this->_docType->toXML();
		if ($this->_journal !== null) $XML .= "<journal>".recode_string("utf8..xml",$this->_journal)."</journal>\n";
		if ($this->_guID !== null) $XML .= "<guID>".$this->_guID."</guID>\n";
		$XML .= "</AppDocument>\n";
		return $XML;
	}
}
