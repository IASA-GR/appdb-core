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
class Default_Model_FAQBase
{
	protected $_mapper;
	protected $_id;
	protected $_question;
	protected $_answer;
	protected $_submitterID;
	protected $_submitter;
	protected $_when;
	protected $_ord;
	protected $_locked;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid FAQ property: '$name'");
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
			throw new Exception("Invalid FAQ property: '$name'");
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

	public function setQuestion($value)
	{
		/* if ( $value === null ) {
			$this->_question = 'NULL';
		} else */ $this->_question = $value;
		return $this;
	}

	public function getQuestion()
	{
		return $this->_question;
	}

	public function setAnswer($value)
	{
		/* if ( $value === null ) {
			$this->_answer = 'NULL';
		} else */ $this->_answer = $value;
		return $this;
	}

	public function getAnswer()
	{
		return $this->_answer;
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


	public function setWhen($value)
	{
		/* if ( $value === null ) {
			$this->_when = 'NULL';
		} else */ $this->_when = $value;
		return $this;
	}

	public function getWhen()
	{
		return $this->_when;
	}

	public function setOrd($value)
	{
		/* if ( $value === null ) {
			$this->_ord = 'NULL';
		} else */ $this->_ord = $value;
		return $this;
	}

	public function getOrd()
	{
		return $this->_ord;
	}

	public function setLocked($value)
	{
		/* if ( $value === null ) {
			$this->_locked = 'NULL';
		} else */ $this->_locked = $value;
		return $this;
	}

	public function getLocked()
	{
		$v = $this->_locked;
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
			$this->setMapper(new Default_Model_FAQsMapper());
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
		$XML = "<FAQ>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_question !== null) $XML .= "<question>".recode_string("utf8..xml",$this->_question)."</question>\n";
		if ($this->_answer !== null) $XML .= "<answer>".recode_string("utf8..xml",$this->_answer)."</answer>\n";
		if ($this->_submitter !== null) $XML .= "<submitter>".$this->_submitter."</submitter>\n";
		if ( $recursive ) if ( $this->_submitter === null ) $this->getSubmitter();
		if ( ! ($this->_submitter === null) ) $XML .= $this->_submitter->toXML();
		if ($this->_when !== null) $XML .= "<when>".recode_string("utf8..xml",$this->_when)."</when>\n";
		if ($this->_ord !== null) $XML .= "<ord>".$this->_ord."</ord>\n";
		if ($this->_locked !== null) $XML .= "<locked>".$this->_locked."</locked>\n";
		$XML .= "</FAQ>\n";
		return $XML;
	}
}
