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
namespace Application\Model;

class VO2Base
{
	protected $_mapper;
	protected $_id;
	protected $_name;
	protected $_serial;
	protected $_status;
	protected $_alias;
	protected $_depricated;
	protected $_description;
	protected $_discipline;
	protected $_disciplines;
      	protected $_homepageUrl;
        protected $_enrollmentUrl;
        protected $_validationDate;
        protected $_scope;
	protected $_contacts;
	protected $_applications;
	protected $_guid;
	public $_sourceid;
    public $middlewares;
    public $aup;
	public $supportproc;
#Admins
#Operations
#UserSupport
#Users
	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception('Invalid VOs2 property');
		}
		$this->$method($value);
	}

	public function __get($name)
	{
		$method = 'get' . $name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception('Invalid VOs2 property');
		}
		return $this->$method();
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
		if ( $value === null ) {
			$this->_id = 'NULL';
		} else $this->_id = $value;
		return $this;
	}

	public function getId()
	{
		return $this->_id;
	}
	public function setGuid($value)
	{
		if ( $value === null ) {
			$this->_guid = 'NULL';
		} else $this->_guid = $value;
		return $this;
	}

	public function getGuid()
	{
		return $this->_guid;
	}
	public function setName($value)
	{
		if ( $value === null ) {
			$this->_name = 'NULL';
		} else $this->_name = $value;
		return $this;
	}

	public function getName()
	{
		return $this->_name;
	}

	public function setSerial($value)
	{
		if ( $value === null ) {
			$this->_serial = 'NULL';
		} else $this->_serial = $value;
		return $this;
	}

	public function getSerial()
	{
		return $this->_serial;
	}

	public function setStatus($value)
	{
		if ( $value === null ) {
			$this->_status = 'NULL';
		} else $this->_status = $value;
		return $this;
	}

	public function getStatus()
	{
		return $this->_status;
	}

	public function setAlias($value)
	{
		if ( $value === null ) {
			$this->_alias = 'NULL';
		} else $this->_alias = $value;
		return $this;
	}

	public function getAlias()
	{
		return $this->_alias;
	}

	public function setDepricated($value)
	{
		if ( $value === null ) {
			$this->_depricated = 'NULL';
		} else $this->_depricated = $value;
		return $this;
	}

	public function getDepricated()
	{
		return $this->_depricated;
	}

	public function setDescription($value)
	{
		if ( $value === null ) {
			$this->_description= 'NULL';
		} else $this->_description= $value;
		return $this;
	}

	public function getDescription()
	{
		return $this->_description;
	}

	public function setDiscipline($value)
	{
		if ( $value === null ) {
			$this->_discipline= 'NULL';
		} else $this->_discipline = $value;
		return $this;
	}

	public function getDiscipline()
	{
		return $this->_discipline;
	}

	public function setDisciplines($value)
	{
		if ( $value === null ) {
			$this->_disciplines = 'NULL';
		} else $this->_disciplines = $value;
		return $this;
	}

	public function getDisciplines()
	{
		return $this->_disciplines;
	}

	public function setContacts($value)
        {
                if ( $value === null ) {
                        $this->_contacts = 'NULL';
                } else $this->_contacts = $value;
                return $this;
        }

        public function getContacts()
        {
                return $this->_contacts;
        }

	public function setHomepageUrl($value)
        {
                if ( $value === null ) {
                        $this->_homepageUrl = 'NULL';
                } else $this->_homepageUrl = $value;
                return $this;
        }

        public function getHomepageUrl()
        {
                return $this->_homepageUrl;
        }

        public function setEnrollmentUrl($value)
        {
                if ( $value === null ) {
                        $this->_enrollmentUrl = 'NULL';
                } else $this->_enrollmentUrl = $value;
                return $this;
        }

        public function getEnrollmentUrl()
        {
                return $this->_enrollmentUrl;
        }

	public function getApplications()
	{
		if ($this->_applications === null) {
			$apps = new Applications();
			$f = new VOsFilter();
			$f->id->equals($this->id);
			$apps->filter->chain($f,"AND");
			$this->_applications = $apps->items;
		}
		return $this->_applications;
	}

        public function setValidationDate($value)
        {
                if ( $value === null ) {
                        $this->_validationDate = 'NULL';
                } else $this->_validationDate = $value;
                return $this;
        }

        public function getValidationDate()
        {
                return $this->_validationDate;
        }

        public function setScope($value)
        {
                if ( $value === null ) {
                        $this->_scope = 'NULL';
                } else $this->_scope = $value;
                return $this;
        }

        public function getScope()
        {
                return $this->_scope;
        }

        public function setSourceID($value)
        {
                if ( $value === null ) {
                        $this->_sourceid = 'NULL';
                } else $this->_sourceid = $value;
                return $this;
        }

        public function getSourceID()
        {
                return $this->_sourceid;
        }

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new VOs2Mapper());
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
		$XML = "<VOs2>\n";
		if ($this->_id != 'NULL') $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_name != 'NULL') $XML .= "<name>".recode_string("utf8..xml",$this->_name)."</name>\n";
		if ($this->_serial != 'NULL') $XML .= "<serial>".$this->_serial."</serial>\n";
		if ($this->_status != 'NULL') $XML .= "<status>".recode_string("utf8..xml",$this->_status)."</status>\n";
		if ($this->_alias != 'NULL') $XML .= "<alias>".recode_string("utf8..xml",$this->_alias)."</alias>\n";
		if ($this->_depricated != 'NULL') $XML .= "<depricated>".$this->_depricated."</depricated>\n";
		$XML .= "</VOs2>\n";
		return $XML;
	}
}
