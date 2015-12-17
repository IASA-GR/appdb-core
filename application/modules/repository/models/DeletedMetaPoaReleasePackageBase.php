<?php
class Repository_Model_DeletedMetaPoaReleasePackageBase
{
	protected $_mapper;
	protected $_deletedBy;
	protected $_timestampDeleted;
	protected $_id;
	protected $_poaId;
	protected $_pkgName;
	protected $_pkgVersion;
	protected $_pkgRelease;
	protected $_pkgArch;
	protected $_pkgType;
	protected $_pkgFilename;
	protected $_pkgDescription;
	protected $_pkgGeneral;
	protected $_pkgMisc;
	protected $_pkgLevel;
	protected $_pkgSize;
	protected $_pkgMd5Sum;
	protected $_pkgSha1Sum;
	protected $_pkgSha256Sum;
	protected $_timestampInserted;
	protected $_insertedBy;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid DeletedMetaPoaReleasePackage property: '$name'");
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
			throw new Exception("Invalid DeletedMetaPoaReleasePackage property: '$name'");
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

	public function setDeletedBy($value)
	{
		/* if ( $value === null ) {
			$this->_deletedBy = 'NULL';
		} else */ $this->_deletedBy = $value;
		return $this;
	}

	public function getDeletedBy()
	{
		return $this->_deletedBy;
	}

	public function setTimestampDeleted($value)
	{
		/* if ( $value === null ) {
			$this->_timestampDeleted = 'NULL';
		} else */ $this->_timestampDeleted = $value;
		return $this;
	}

	public function getTimestampDeleted()
	{
		return $this->_timestampDeleted;
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

	public function setPkgName($value)
	{
		/* if ( $value === null ) {
			$this->_pkgName = 'NULL';
		} else */ $this->_pkgName = $value;
		return $this;
	}

	public function getPkgName()
	{
		return $this->_pkgName;
	}

	public function setPkgVersion($value)
	{
		/* if ( $value === null ) {
			$this->_pkgVersion = 'NULL';
		} else */ $this->_pkgVersion = $value;
		return $this;
	}

	public function getPkgVersion()
	{
		return $this->_pkgVersion;
	}

	public function setPkgRelease($value)
	{
		/* if ( $value === null ) {
			$this->_pkgRelease = 'NULL';
		} else */ $this->_pkgRelease = $value;
		return $this;
	}

	public function getPkgRelease()
	{
		return $this->_pkgRelease;
	}

	public function setPkgArch($value)
	{
		/* if ( $value === null ) {
			$this->_pkgArch = 'NULL';
		} else */ $this->_pkgArch = $value;
		return $this;
	}

	public function getPkgArch()
	{
		return $this->_pkgArch;
	}

	public function setPkgType($value)
	{
		/* if ( $value === null ) {
			$this->_pkgType = 'NULL';
		} else */ $this->_pkgType = $value;
		return $this;
	}

	public function getPkgType()
	{
		return $this->_pkgType;
	}

	public function setPkgFilename($value)
	{
		/* if ( $value === null ) {
			$this->_pkgFilename = 'NULL';
		} else */ $this->_pkgFilename = $value;
		return $this;
	}

	public function getPkgFilename()
	{
		return $this->_pkgFilename;
	}

	public function setPkgDescription($value)
	{
		/* if ( $value === null ) {
			$this->_pkgDescription = 'NULL';
		} else */ $this->_pkgDescription = $value;
		return $this;
	}

	public function getPkgDescription()
	{
		return $this->_pkgDescription;
	}

	public function setPkgGeneral($value)
	{
		/* if ( $value === null ) {
			$this->_pkgGeneral = 'NULL';
		} else */ $this->_pkgGeneral = $value;
		return $this;
	}

	public function getPkgGeneral()
	{
		return $this->_pkgGeneral;
	}

	public function setPkgMisc($value)
	{
		/* if ( $value === null ) {
			$this->_pkgMisc = 'NULL';
		} else */ $this->_pkgMisc = $value;
		return $this;
	}

	public function getPkgMisc()
	{
		return $this->_pkgMisc;
	}

	public function setPkgLevel($value)
	{
		/* if ( $value === null ) {
			$this->_pkgLevel = 'NULL';
		} else */ $this->_pkgLevel = $value;
		return $this;
	}

	public function getPkgLevel()
	{
		return $this->_pkgLevel;
	}

	public function setPkgSize($value)
	{
		/* if ( $value === null ) {
			$this->_pkgSize = 'NULL';
		} else */ $this->_pkgSize = $value;
		return $this;
	}

	public function getPkgSize()
	{
		return $this->_pkgSize;
	}

	public function setPkgMd5Sum($value)
	{
		/* if ( $value === null ) {
			$this->_pkgMd5Sum = 'NULL';
		} else */ $this->_pkgMd5Sum = $value;
		return $this;
	}

	public function getPkgMd5Sum()
	{
		return $this->_pkgMd5Sum;
	}

	public function setPkgSha1Sum($value)
	{
		/* if ( $value === null ) {
			$this->_pkgSha1Sum = 'NULL';
		} else */ $this->_pkgSha1Sum = $value;
		return $this;
	}

	public function getPkgSha1Sum()
	{
		return $this->_pkgSha1Sum;
	}

	public function setPkgSha256Sum($value)
	{
		/* if ( $value === null ) {
			$this->_pkgSha256Sum = 'NULL';
		} else */ $this->_pkgSha256Sum = $value;
		return $this;
	}

	public function getPkgSha256Sum()
	{
		return $this->_pkgSha256Sum;
	}

	public function setTimestampInserted($value)
	{
		/* if ( $value === null ) {
			$this->_timestampInserted = 'NULL';
		} else */ $this->_timestampInserted = $value;
		return $this;
	}

	public function getTimestampInserted()
	{
		return $this->_timestampInserted;
	}

	public function setInsertedBy($value)
	{
		/* if ( $value === null ) {
			$this->_insertedBy = 'NULL';
		} else */ $this->_insertedBy = $value;
		return $this;
	}

	public function getInsertedBy()
	{
		return $this->_insertedBy;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Repository_Model_DeletedMetaPoaReleasePackagesMapper());
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
		$XML = "<DeletedMetaPoaReleasePackage>\n";
		if ($this->_deletedBy !== null) $XML .= "<deletedBy>".$this->_deletedBy."</deletedBy>\n";
		if ($this->_timestampDeleted !== null) $XML .= "<timestampDeleted>".recode_string("utf8..xml",$this->_timestampDeleted)."</timestampDeleted>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_poaId !== null) $XML .= "<poaId>".$this->_poaId."</poaId>\n";
		if ($this->_pkgName !== null) $XML .= "<pkgName>".recode_string("utf8..xml",$this->_pkgName)."</pkgName>\n";
		if ($this->_pkgVersion !== null) $XML .= "<pkgVersion>".recode_string("utf8..xml",$this->_pkgVersion)."</pkgVersion>\n";
		if ($this->_pkgRelease !== null) $XML .= "<pkgRelease>".recode_string("utf8..xml",$this->_pkgRelease)."</pkgRelease>\n";
		if ($this->_pkgArch !== null) $XML .= "<pkgArch>".recode_string("utf8..xml",$this->_pkgArch)."</pkgArch>\n";
		if ($this->_pkgType !== null) $XML .= "<pkgType>".recode_string("utf8..xml",$this->_pkgType)."</pkgType>\n";
		if ($this->_pkgFilename !== null) $XML .= "<pkgFilename>".recode_string("utf8..xml",$this->_pkgFilename)."</pkgFilename>\n";
		if ($this->_pkgDescription !== null) $XML .= "<pkgDescription>".recode_string("utf8..xml",$this->_pkgDescription)."</pkgDescription>\n";
		if ($this->_pkgGeneral !== null) $XML .= "<pkgGeneral>".recode_string("utf8..xml",$this->_pkgGeneral)."</pkgGeneral>\n";
		if ($this->_pkgMisc !== null) $XML .= "<pkgMisc>".recode_string("utf8..xml",$this->_pkgMisc)."</pkgMisc>\n";
		if ($this->_pkgLevel !== null) $XML .= "<pkgLevel>".$this->_pkgLevel."</pkgLevel>\n";
		if ($this->_pkgSize !== null) $XML .= "<pkgSize>".$this->_pkgSize."</pkgSize>\n";
		if ($this->_pkgMd5Sum !== null) $XML .= "<pkgMd5Sum>".recode_string("utf8..xml",$this->_pkgMd5Sum)."</pkgMd5Sum>\n";
		if ($this->_pkgSha1Sum !== null) $XML .= "<pkgSha1Sum>".recode_string("utf8..xml",$this->_pkgSha1Sum)."</pkgSha1Sum>\n";
		if ($this->_pkgSha256Sum !== null) $XML .= "<pkgSha256Sum>".recode_string("utf8..xml",$this->_pkgSha256Sum)."</pkgSha256Sum>\n";
		if ($this->_timestampInserted !== null) $XML .= "<timestampInserted>".recode_string("utf8..xml",$this->_timestampInserted)."</timestampInserted>\n";
		if ($this->_insertedBy !== null) $XML .= "<insertedBy>".$this->_insertedBy."</insertedBy>\n";
		$XML .= "</DeletedMetaPoaReleasePackage>\n";
		return $XML;
	}
}
