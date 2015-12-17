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
class Default_Model_VOs2Filter extends Default_Model_Filter
{
	protected $_id;
	protected $_name;
	protected $_serial;
	protected $_status;
	protected $_alias;
	protected $_depricated;

	public function getId()
	{
		return $this->_id;
	}

	public function setId($value)
	{
		$this->_id = $value;
		return $this;
	}

	public function getName()
	{
		return $this->_name;
	}

	public function setName($value)
	{
		$this->_name = $value;
		return $this;
	}

	public function getSerial()
	{
		return $this->_serial;
	}

	public function setSerial($value)
	{
		$this->_serial = $value;
		return $this;
	}

	public function getStatus()
	{
		return $this->_status;
	}

	public function setStatus($value)
	{
		$this->_status = $value;
		return $this;
	}

	public function getAlias()
	{
		return $this->_alias;
	}

	public function setAlias($value)
	{
		$this->_alias = $value;
		return $this;
	}

	public function getDepricated()
	{
		return $this->_depricated;
	}

	public function setDepricated($value)
	{
		$this->_depricated = $value;
		return $this;
	}

}
