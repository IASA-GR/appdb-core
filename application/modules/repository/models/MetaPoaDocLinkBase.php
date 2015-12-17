<?php
class Repository_Model_MetaPoaDocLinkBase
{
	protected $_mapper;
	protected $_id;
	protected $_poaId;
	protected $_poaRelease;
	protected $_documentationLink;
	protected $_documentationLinkType;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid MetaPoaDocLink property: '$name'");
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
			throw new Exception("Invalid MetaPoaDocLink property: '$name'");
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

	public function setPoaId($value)
	{
		/* if ( $value === null ) {
			$this->_poaId = 'NULL';
		} else */ $this->_poaId = $value;
		return $this;
	}

	public function getPoaId()
	{
		return $this->_poaId;
	}

	public function getPoaRelease()
	{
		if ( $this->_poaRelease === null ) {
			$MetaPoaReleases = new Repository_Model_MetaPoaReleases();
			$MetaPoaReleases->filter->id->equals($this->getPoaId());
			if ($MetaPoaReleases->count() > 0) $this->_poaRelease = $MetaPoaReleases->items[0];
		}
		return $this->_poaRelease;
	}

	public function setPoaRelease($value)
	{
		if ( $value === null ) {
			$this->setPoaId(null);
		} else {
			$this->setPoaId($value->getId());
		}
	}


	public function setDocumentationLink($value)
	{
		/* if ( $value === null ) {
			$this->_documentationLink = 'NULL';
		} else */ $this->_documentationLink = $value;
		return $this;
	}

	public function getDocumentationLink()
	{
		return $this->_documentationLink;
	}

	public function setDocumentationLinkType($value)
	{
		/* if ( $value === null ) {
			$this->_documentationLinkType = 'NULL';
		} else */ $this->_documentationLinkType = $value;
		return $this;
	}

	public function getDocumentationLinkType()
	{
		return $this->_documentationLinkType;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Repository_Model_MetaPoaDocLinksMapper());
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
		$XML = "<MetaPoaDocLink>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_poaId !== null) $XML .= "<poaId>".$this->_poaId."</poaId>\n";
		if ( $recursive ) if ( $this->_poaRelease === null ) $this->getPoaRelease();
		if ( ! ($this->_poaRelease === null) ) $XML .= $this->_poaRelease->toXML();
		if ($this->_documentationLink !== null) $XML .= "<documentationLink>".recode_string("utf8..xml",$this->_documentationLink)."</documentationLink>\n";
		if ($this->_documentationLinkType !== null) $XML .= "<documentationLinkType>".$this->_documentationLinkType."</documentationLinkType>\n";
		$XML .= "</MetaPoaDocLink>\n";
		return $XML;
	}
}
