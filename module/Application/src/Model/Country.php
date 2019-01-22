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
// PUT YOUR CUSTOM CODE HERE
class Default_Model_Country extends Default_Model_CountryBase
{
	protected $_applications;
	protected $_researchers;

	public function getApplications()
	{
		if ( $this->_applications === null ) {
			$apps = new Default_Model_Applications();
			$f = new Default_Model_CountriesFilter();
			$f->id->equals($this->id);
			$apps->filter->chain($f,"AND");
			$this->_applications = $apps;
		}
		return $this->_applications->items;
	}

	public function getResearchers()
	{
		if ($this->_researchers === null) {
			$rs = new Default_Model_Researchers();
			$f = new Default_Model_CountriesFilter();
			$f->id->equals($this->id);
			$rs->filter->chain($f,"AND");
			$this->_researchers = $rs;
		}
		return $this->_researchers->items;
	}

}
