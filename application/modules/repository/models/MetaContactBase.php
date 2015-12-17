<?php
class Repository_Model_MetaContactBase
{
	protected $_mapper;
	protected $_id;
	protected $_assocId;
	protected $_assocEntity;
	protected $_externalId;
	protected $_contactTypeId;
	protected $_contactType;
	protected $_firstname;
	protected $_lastname;
	protected $_email;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid MetaContact property: '$name'");
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
			throw new Exception("Invalid MetaContact property: '$name'");
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

	public function setAssocId($value)
	{
		/* if ( $value === null ) {
			$this->_assocId = 'NULL';
		} else */ $this->_assocId = $value;
		return $this;
	}

	public function getAssocId()
	{
		return $this->_assocId;
	}

	public function setAssocEntity($value)
	{
		/* if ( $value === null ) {
			$this->_assocEntity = 'NULL';
		} else */ $this->_assocEntity = $value;
		return $this;
	}

	public function getAssocEntity()
	{
		return $this->_assocEntity;
	}

	public function setExternalId($value)
	{
		/* if ( $value === null ) {
			$this->_externalId = 'NULL';
		} else */ $this->_externalId = $value;
		return $this;
	}

	public function getExternalId()
	{
		return $this->_externalId;
	}

	public function setContactTypeId($value)
	{
		/* if ( $value === null ) {
			$this->_contactTypeId = 'NULL';
		} else */ $this->_contactTypeId = $value;
		return $this;
	}

	public function getContactTypeId()
	{
		return $this->_contactTypeId;
	}

	public function getContactType()
	{
		if ( $this->_contactType === null ) {
			$MetaContactTypes = new Repository_Model_MetaContactTypes();
			$MetaContactTypes->filter->id->equals($this->getContactTypeId());
			if ($MetaContactTypes->count() > 0) $this->_contactType = $MetaContactTypes->items[0];
		}
		return $this->_contactType;
	}

	public function setContactType($value)
	{
		if ( $value === null ) {
			$this->setContactTypeId(null);
		} else {
			$this->setContactTypeId($value->getId());
		}
	}


	public function setFirstname($value)
	{
		/* if ( $value === null ) {
			$this->_firstname = 'NULL';
		} else */ $this->_firstname = $value;
		return $this;
	}

	public function getFirstname()
	{
		return $this->_firstname;
	}

	public function setLastname($value)
	{
		/* if ( $value === null ) {
			$this->_lastname = 'NULL';
		} else */ $this->_lastname = $value;
		return $this;
	}

	public function getLastname()
	{
		return $this->_lastname;
	}

	public function setEmail($value)
	{
		/* if ( $value === null ) {
			$this->_email = 'NULL';
		} else */ $this->_email = $value;
		return $this;
	}

	public function getEmail()
	{
		return $this->_email;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Repository_Model_MetaContactsMapper());
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
		$XML = "<MetaContact>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_assocId !== null) $XML .= "<assocId>".$this->_assocId."</assocId>\n";
		if ($this->_assocEntity !== null) $XML .= "<assocEntity>".recode_string("utf8..xml",$this->_assocEntity)."</assocEntity>\n";
		if ($this->_externalId !== null) $XML .= "<externalId>".$this->_externalId."</externalId>\n";
		if ($this->_contactTypeId !== null) $XML .= "<contactTypeId>".$this->_contactTypeId."</contactTypeId>\n";
		if ( $recursive ) if ( $this->_contactType === null ) $this->getContactType();
		if ( ! ($this->_contactType === null) ) $XML .= $this->_contactType->toXML();
		if ($this->_firstname !== null) $XML .= "<firstname>".recode_string("utf8..xml",$this->_firstname)."</firstname>\n";
		if ($this->_lastname !== null) $XML .= "<lastname>".recode_string("utf8..xml",$this->_lastname)."</lastname>\n";
		if ($this->_email !== null) $XML .= "<email>".$this->_email."</email>\n";
		$XML .= "</MetaContact>\n";
		return $XML;
	}
}
