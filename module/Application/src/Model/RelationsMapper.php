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
class Default_Model_RelationsMapper extends Default_Model_RelationsMapperBase
{
	public function save(Default_Model_Relation $value) {
		global $application;
		$data = array();
		$ishidden = false;
		if( $value->getHiddenByID() === 0 ) {
			$ishidden = true;
			$value->hiddenbyid = null;
		}
		parent::save($value);
		
		if ( $ishidden ) $data['hiddenby'] = null;
		if( count($data) > 0 ){
			$q1 = 'id = ?';
			$q2 = $value->id;
			if (null === ($id = $value->id)) {
				unset($data['id']);
				$value->id = $this->getDbTable()->insert($data);
			} else {
				$s = $this->getDbTable()->getAdapter()->quoteInto($q1,$q2);
				$this->getDbTable()->update($data, $s);
			}
		}
	}
}
