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
class Default_Model_AppContactMiddlewareBase
{
	protected $_mapper;
	protected $_appID;
	protected $_researcherID;
	protected $_appmiddlewareID;
	protected $_appMiddleware;
	protected $_note;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid AppContactMiddleware property: '$name'");
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
			throw new Exception("Invalid AppContactMiddleware property: '$name'");
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

	public function setResearcherID($value)
	{
		/* if ( $value === null ) {
			$this->_researcherID = 'NULL';
		} else */ $this->_researcherID = $value;
		return $this;
	}

	public function getResearcherID()
	{
		return $this->_researcherID;
	}

	public function setAppmiddlewareID($value)
	{
		/* if ( $value === null ) {
			$this->_appmiddlewareID = 'NULL';
		} else */ $this->_appmiddlewareID = $value;
		return $this;
	}

	public function getAppmiddlewareID()
	{
		return $this->_appmiddlewareID;
	}

	public function getAppMiddleware()
	{
		if ( $this->_appMiddleware === null ) {
			$AppMiddlewares = new Default_Model_AppMiddlewares();
			$AppMiddlewares->filter->id->equals($this->getAppmiddlewareID());
			if ($AppMiddlewares->count() > 0) $this->_appMiddleware = $AppMiddlewares->items[0];
		}
		return $this->_appMiddleware;
	}

	public function setAppMiddleware($value)
	{
		if ( $value === null ) {
			$this->setAppmiddlewareID(null);
		} else {
			$this->setAppmiddlewareID($value->getId());
		}
	}


	public function setNote($value)
	{
		/* if ( $value === null ) {
			$this->_note = 'NULL';
		} else */ $this->_note = $value;
		return $this;
	}

	public function getNote()
	{
		return $this->_note;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_AppContactMiddlewaresMapper());
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
		$XML = "<AppContactMiddleware>\n";
		if ($this->_appID !== null) $XML .= "<appID>".$this->_appID."</appID>\n";
		if ($this->_researcherID !== null) $XML .= "<researcherID>".$this->_researcherID."</researcherID>\n";
		if ($this->_appmiddlewareID !== null) $XML .= "<appmiddlewareID>".$this->_appmiddlewareID."</appmiddlewareID>\n";
		if ( $recursive ) if ( $this->_appMiddleware === null ) $this->getAppMiddleware();
		if ( ! ($this->_appMiddleware === null) ) $XML .= $this->_appMiddleware->toXML();
		if ($this->_note !== null) $XML .= "<note>".recode_string("utf8..xml",$this->_note)."</note>\n";
		$XML .= "</AppContactMiddleware>\n";
		return $XML;
	}
}