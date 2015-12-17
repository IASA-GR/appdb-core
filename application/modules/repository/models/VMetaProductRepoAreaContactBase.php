<?php
class Repository_Model_VMetaProductRepoAreaContactBase
{
	protected $_mapper;
	protected $_pseudoId;
	protected $_externalId;
	protected $_contactTypeId;
	protected $_contactType;
	protected $_firstname;
	protected $_lastname;
	protected $_email;
	protected $_repoareaID;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid VMetaProductRepoAreaContact property: '$name'");
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
			throw new Exception("Invalid VMetaProductRepoAreaContact property: '$name'");
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

	public function setPseudoId($value)
	{
		/* if ( $value === null ) {
			$this->_pseudoId = 'NULL';
		} else */ $this->_pseudoId = $value;
		return $this;
	}

	public function getPseudoId()
	{
		return $this->_pseudoId;
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

	public function setRepoareaID($value)
	{
		/* if ( $value === null ) {
			$this->_repoareaID = 'NULL';
		} else */ $this->_repoareaID = $value;
		return $this;
	}

	public function getRepoareaID()
	{
		return $this->_repoareaID;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Repository_Model_VMetaProductRepoAreaContactsMapper());
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
		$XML = "<VMetaProductRepoAreaContact>\n";
		if ($this->_pseudoId !== null) $XML .= "<pseudoId>".recode_string("utf8..xml",$this->_pseudoId)."</pseudoId>\n";
		if ($this->_externalId !== null) $XML .= "<externalId>".$this->_externalId."</externalId>\n";
		if ($this->_contactTypeId !== null) $XML .= "<contactTypeId>".$this->_contactTypeId."</contactTypeId>\n";
		if ( $recursive ) if ( $this->_contactType === null ) $this->getContactType();
		if ( ! ($this->_contactType === null) ) $XML .= $this->_contactType->toXML();
		if ($this->_firstname !== null) $XML .= "<firstname>".recode_string("utf8..xml",$this->_firstname)."</firstname>\n";
		if ($this->_lastname !== null) $XML .= "<lastname>".recode_string("utf8..xml",$this->_lastname)."</lastname>\n";
		if ($this->_email !== null) $XML .= "<email>".recode_string("utf8..xml",$this->_email)."</email>\n";
		if ($this->_repoareaID !== null) $XML .= "<repoareaID>".$this->_repoareaID."</repoareaID>\n";
		$XML .= "</VMetaProductRepoAreaContact>\n";
		return $XML;
	}
}
