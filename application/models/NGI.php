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
class Default_Model_NGI extends Default_Model_NGIBase
{
	protected $_representatives = null;

	public function getRepresentatives() {
		if ($this->_representatives === null) {
			if ( ! isnull($this->_countryID) ) {
				$rs = new Default_Model_Researchers();
				$rs->filter->countryid->equals($this->countryID)->and($rs->filter->positiontypeid->equals(6));
				$this->_representatives = $rs;
			}
		}
		if ($this->_representatives === null) return array(); else return $this->_representatives->items;
	}

}
