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
class Default_Model_AppRatingBase
{
	protected $_mapper;
	protected $_id;
	protected $_appID;
	protected $_application;
	protected $_rating;
	protected $_comment;
	protected $_submittedOn;
	protected $_submitterID;
	protected $_submitter;
	protected $_submitterName;
	protected $_submitterEmail;
	protected $_guID;
	protected $_moderated;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid AppRating property: '$name'");
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
			throw new Exception("Invalid AppRating property: '$name'");
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


	public function setRating($value)
	{
		/* if ( $value === null ) {
			$this->_rating = 'NULL';
		} else */ $this->_rating = $value;
		return $this;
	}

	public function getRating()
	{
		return $this->_rating;
	}

	public function setComment($value)
	{
		/* if ( $value === null ) {
			$this->_comment = 'NULL';
		} else */ $this->_comment = $value;
		return $this;
	}

	public function getComment()
	{
		return $this->_comment;
	}

	public function setSubmittedOn($value)
	{
		/* if ( $value === null ) {
			$this->_submittedOn = 'NULL';
		} else */ $this->_submittedOn = $value;
		return $this;
	}

	public function getSubmittedOn()
	{
		return $this->_submittedOn;
	}

	public function setSubmitterID($value)
	{
		/* if ( $value === null ) {
			$this->_submitterID = 'NULL';
		} else */ $this->_submitterID = $value;
		return $this;
	}

	public function getSubmitterID()
	{
		return $this->_submitterID;
	}

	public function getSubmitter()
	{
		if ( $this->_submitter === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getSubmitterID());
			if ($Researchers->count() > 0) $this->_submitter = $Researchers->items[0];
		}
		return $this->_submitter;
	}

	public function setSubmitter($value)
	{
		if ( $value === null ) {
			$this->setSubmitterID(null);
		} else {
			$this->setSubmitterID($value->getId());
		}
	}


	public function setSubmitterName($value)
	{
		/* if ( $value === null ) {
			$this->_submitterName = 'NULL';
		} else */ $this->_submitterName = $value;
		return $this;
	}

	public function getSubmitterName()
	{
		return $this->_submitterName;
	}

	public function setSubmitterEmail($value)
	{
		/* if ( $value === null ) {
			$this->_submitterEmail = 'NULL';
		} else */ $this->_submitterEmail = $value;
		return $this;
	}

	public function getSubmitterEmail()
	{
		return $this->_submitterEmail;
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

	public function setModerated($value)
	{
		/* if ( $value === null ) {
			$this->_moderated = 'NULL';
		} else */ $this->_moderated = $value;
		return $this;
	}

	public function getModerated()
	{
		$v = $this->_moderated;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_AppRatingsMapper());
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
		$XML = "<AppRating>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_appID !== null) $XML .= "<appID>".$this->_appID."</appID>\n";
		if ( $recursive ) if ( $this->_application === null ) $this->getApplication();
		if ( ! ($this->_application === null) ) $XML .= $this->_application->toXML();
		if ($this->_rating !== null) $XML .= "<rating>".$this->_rating."</rating>\n";
		if ($this->_comment !== null) $XML .= "<comment>".recode_string("utf8..xml",$this->_comment)."</comment>\n";
		if ($this->_submittedOn !== null) $XML .= "<submittedOn>".recode_string("utf8..xml",$this->_submittedOn)."</submittedOn>\n";
		if ($this->_submitterID !== null) $XML .= "<submitterID>".$this->_submitterID."</submitterID>\n";
		if ( $recursive ) if ( $this->_submitter === null ) $this->getSubmitter();
		if ( ! ($this->_submitter === null) ) $XML .= $this->_submitter->toXML();
		if ($this->_submitterName !== null) $XML .= "<submitterName>".recode_string("utf8..xml",$this->_submitterName)."</submitterName>\n";
		if ($this->_submitterEmail !== null) $XML .= "<submitterEmail>".recode_string("utf8..xml",$this->_submitterEmail)."</submitterEmail>\n";
		if ($this->_guID !== null) $XML .= "<guID>".$this->_guID."</guID>\n";
		if ($this->_moderated !== null) $XML .= "<moderated>".$this->_moderated."</moderated>\n";
		$XML .= "</AppRating>\n";
		return $XML;
	}
}
