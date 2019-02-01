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

class IntAuthorsMapper extends IntAuthorsMapperBase
{

	public function joins(&$select, $filter) {
		if ( is_array($filter->joins) ) {
			if ( in_array("applications", $filter->joins) ) {
				$select->join('appdocuments', 'appdocuments.id = intauthors.docid', array(), 'left');
				$select->join('applications', 'applications.id = appdocuments.appid', array(), 'left');
			}
		}
	}

	public function fetchAll($filter = null, $format = '') {
		$select = $this->getDbTable()->getSql()->select();
		if ( (($filter !== null) && ($filter->expr() != '')) ) {
			$select->quantifier('DISTINCT');
			if ( $filter !== null ) {
				if ($filter->expr() != '') {
					$this->joins($select, $filter);
					$select->where($filter->expr());
				}
			}
		}
		$resultSet = db()->query(SQL2STR($this, $select), array())->toArray();
		$entries = array();
		foreach ($resultSet as $row) {
			$entry = new IntAuthor();
			$this->populate($entry,$row);
			$entries[] = $entry;
		}		
		return $entries;
	}
}
