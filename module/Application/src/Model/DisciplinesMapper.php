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

class DisciplinesMapper extends DisciplinesMapperBase
{
	public function fetchAll($filter = null, $format = '', $xmldetailed = false) {
		if ($format == '') {
			return parent::fetchAll($filter);
		} else {
			if ($format === 'xml') {
				$select = $this->getDbTable()->getSql()->select();
				if ( $filter !== null ) {
					if ( trim($filter->expr()) != '' ) $select->where($filter->expr());
				}
        		if ($filter !== null) {
					$ord = $filter->orderBy;
					if ( $ord == '' ) $ord = 'name';
					$order = "ORDER BY ".$ord;
				}
				if ( $order == '' ) $order = "ORDER BY name";
				if ( $xmldetailed ) {
					$func = "discipline_to_xml_ext";
				} else {
					$func = "discipline_to_xml";
				}
				$query = "SELECT $func(id) as discipline FROM (" . SQL2STR($this, $select) . " $order) AS T;";
				$resultSet = $this->getDbTable()->getAdapter()->query($query, array())->toArray();
				$entries = array();
				foreach ($resultSet as $row) {
					$entry = $row['discipline'];
					$entries[] = $entry;
				}
				return $entries;
         	}
		}
	}

}
