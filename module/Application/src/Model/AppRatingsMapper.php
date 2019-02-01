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

class AppRatingsMapper extends AppRatingsMapperBase
{
	public function fetchAll($filter = null, $format = '')
	{
		$select = $this->getDbTable()->getSql()->select();
		$executor = $this->getDbTable();
		if ( ($filter !== null) && ($filter->expr() != '') ) {
			$select->quantifier('DISTINCT');
			if ($filter->expr() != '') {
				$select->where($filter->expr());
				$executor = $this->getDbTable()->getAdapter();
			}
		}
		if ($filter !== null) {
			if (! is_null($filter->limit)) $select->limit($filter->limit);
			if (! is_null($filter->offset)) $select->offset($filter->offset);
			if (! is_null($filter->ordeBy)) $select->order($filter->orderBy);
		}
		$select = (new \Zend\Db\Sql\Sql($this->getDbTable()->getAdapter()))->getSqlStringForSqlObject($select);
		if ($format === 'xml') {
			$resultSet = db()->query("SELECT appratings_to_xml(id) as apprating FROM (". $select .") AS T;", array())->toArray();
		} else {
			$resultSet = db()->query($select, array())->toArray();
		}
		$entries = array();
		foreach ($resultSet as $row) {
			if ( $format === 'xml' ) {
				$entry = $row['apprating'];
			} else {
				$entry = new AppRating();
				$this->populate($entry,$row);
			}
			$entries[] = $entry;
		}		
		return $entries;
	}

}
