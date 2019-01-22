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
class Default_Model_UserAccountsMapper extends Default_Model_UserAccountsMapperBase
{
	public function populate(&$entry, $row) {
		parent::populate($entry,$row);
		$entry->setIDPTrace(pg_to_php_array($row->idptrace));
	}

	public function save(Default_Model_UserAccount $value) {
		$rec = $value->IDPTrace;
		$value->IDPTrace = php_to_pg_array($value->IDPTrace, true);
		parent::save($value);
		$value->IDPTrace = $rec;
    }
}
