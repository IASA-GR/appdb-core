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

class AppTagsMapper extends AppTagsMapperBase
{
	public function fetchAll($filter = null, $format = '') {
		if ($format == '') {
			return parent::fetchAll($filter);
		} else {
			if ($format === 'xml') {
				$q = "SELECT tags_to_xml() as tags";
				if ($filter !== null && $filter->limit > 0) $q .= " LIMIT " . $filter->limit . " ";
				if ($filter !== null && $filter->offset > 0) $q .= " OFFSET " . $filter->offset;
				$q .= ";";
				$resultSet = db()->query($q, array())->toArray();
				$entries = array();
				foreach ($resultSet as $row) {
					$entry = $row['tags'];
					$entries[] = $entry;
				}
				return $entries;
         	}
		}
	}
	
	public function xmlcount($filter = null)
	{
		$select = $this->getDbTable()->getSql()->select();
		$select->columns(array('COUNT(DISTINCT (tag)) AS count'));
		if ( ($filter !== null) && ($filter->expr() != '') ) {
			$select->where($filter->expr());
		}
		$from = '';
		$where = '';
		$orderby = '';
		$limit = '';
		getZendSelectParts($select, $from, $where, $orderby, $limit);
		$from = fixuZenduBuguru($from);
		$res = db()->query(SQL2STR($this, $select), array())->toArray();
		return $res[0]['count'];
	}

}
