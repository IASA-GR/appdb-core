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
class Default_Model_AppRatingsMapper extends Default_Model_AppRatingsMapperBase
{
	public function fetchAll($filter = null, $format = '')
	{
		$select = $this->getDbTable()->select();
		$executor = $this->getDbTable();
		if ( ($filter !== null) && ($filter->expr() != '') ) {
			$select = $this->getDbTable()->getAdapter()->select()->distinct()->from('appratings');
			if ($filter->expr() != '') {
//				$this->joins($select, $filter);
				$select->where($filter->expr());
				$executor = $this->getDbTable()->getAdapter();
				$executor->setFetchMode(Zend_Db::FETCH_OBJ);
			}
		}
		if ($filter !== null) $select->limit($filter->limit, $filter->offset);
		if ($filter !== null) $select->order($filter->orderBy);
		$s = "".$select; 
		if ($format === 'xml') {
			$this->getDbTable()->getAdapter()->setFetchMode(Zend_Db::FETCH_OBJ);
			$resultSet = $this->getDbTable()->getAdapter()->query("SELECT appratings_to_xml(id) as apprating FROM (".$select.") AS T;")->fetchAll();
		} else $resultSet = $executor->fetchAll($select);
		$entries = array();
		foreach ($resultSet as $row) {
			if ( $format === 'xml' ) {
				$entry = $row->apprating;
			} else {
				$entry = new Default_Model_AppRating();
				$this->populate($entry,$row);
			}
			$entries[] = $entry;
		}		return $entries;
	}

}
