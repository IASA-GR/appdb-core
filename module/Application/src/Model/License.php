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
namespace Application\Model;

class License extends LicenseBase
{
	public function toXML($recursive=false) {
		$xml = '';
		if ( $this->_id !== null ) {
			$xml = '<application:license id="' . $this->id . '" name="' . $this->name. '" group="' . $this->group . '"><license:title>' . $this->title. '</license:title><license:url>' . $this->link. '</license:url></application:license>';
		}
		return $xml;
	}

}