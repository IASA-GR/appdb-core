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
class Default_Model_ExtAuthorBase
{
	protected $_mapper;
	protected $_id;
	protected $_docID;
	protected $_appDocument;
	protected $_author;
	protected $_main;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid ExtAuthor property: '$name'");
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
			throw new Exception("Invalid ExtAuthor property: '$name'");
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

	public function setDocID($value)
	{
		/* if ( $value === null ) {
			$this->_docID = 'NULL';
		} else */ $this->_docID = $value;
		return $this;
	}

	public function getDocID()
	{
		return $this->_docID;
	}

	public function getAppDocument()
	{
		if ( $this->_appDocument === null ) {
			$AppDocuments = new Default_Model_AppDocuments();
			$AppDocuments->filter->id->equals($this->getDocID());
			if ($AppDocuments->count() > 0) $this->_appDocument = $AppDocuments->items[0];
		}
		return $this->_appDocument;
	}

	public function setAppDocument($value)
	{
		if ( $value === null ) {
			$this->setDocID(null);
		} else {
			$this->setDocID($value->getId());
		}
	}


	public function setAuthor($value)
	{
		/* if ( $value === null ) {
			$this->_author = 'NULL';
		} else */ $this->_author = $value;
		return $this;
	}

	public function getAuthor()
	{
		return $this->_author;
	}

	public function setMain($value)
	{
		/* if ( $value === null ) {
			$this->_main = 'NULL';
		} else */ $this->_main = $value;
		return $this;
	}

	public function getMain()
	{
		$v = $this->_main;
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
			$this->setMapper(new Default_Model_ExtAuthorsMapper());
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
		$XML = "<ExtAuthor>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_docID !== null) $XML .= "<docID>".$this->_docID."</docID>\n";
		if ( $recursive ) if ( $this->_appDocument === null ) $this->getAppDocument();
		if ( ! ($this->_appDocument === null) ) $XML .= $this->_appDocument->toXML();
		if ($this->_author !== null) $XML .= "<author>".recode_string("utf8..xml",$this->_author)."</author>\n";
		if ($this->_main !== null) $XML .= "<main>".$this->_main."</main>\n";
		$XML .= "</ExtAuthor>\n";
		return $XML;
	}
}
