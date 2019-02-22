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
class Default_Model_AppContactVO extends Default_Model_AppContactVOBase
{
	protected $_appcontact = null;

	public function getAppContact() {
		if ( $this->_appcontact === null ) {
			$ac = new Default_Model_ResearchersApps();
			$ac->filter->appid->equals($this->_appid)->and($ac->filter->researcherid->equals($this->_researcherid));
			if ( count($ac->items) > 0 ) {
				$this->_appcontact = $ac->items[0];
			}
		}
		return $this->_appcontact;
	}


}