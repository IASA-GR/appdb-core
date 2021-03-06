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
class Default_Model_PermissionsMapper extends Default_Model_PermissionsMapperBase
{
	public function fetchAll($filter = null, $format = '')
	{
		$select = $this->getDbTable()->select();
		if ( ($filter !== null) && ($filter->expr() != '') ) {
			$select->where($filter->expr());
		}
		if ($filter !== null) $select->limit($filter->limit, $filter->offset);
		if ($filter !== null) $select->order($filter->orderBy);
//		$select = str_replace('* FROM "permissions"', '* FROM permissionscache() AS "permissions"', "".$select);
		$resultSet = db()->query($select)->fetchAll();
//		$resultSet = $this->getDbTable()->fetchAll($select);
		$entries = array();
		foreach ($resultSet as $row) {
			$entry = new Default_Model_Permission();
			$this->populate($entry,$row);
			$entries[] = $entry;
		}		return $entries;
	}

}
