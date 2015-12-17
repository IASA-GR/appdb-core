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
class Default_Model_DisciplinesMapper extends Default_Model_DisciplinesMapperBase
{
	public function fetchAll($filter = null, $format = '', $xmldetailed = false) {
		if ($format == '') {
			return parent::fetchAll($filter);
		} else {
			if ($format === 'xml') {
				$select = $this->getDbTable()->select();
				if ( $filter !== null ) {
					if ( trim($filter->expr()) != '' ) $select->where($filter->expr());
				}
				$this->getDbTable()->getAdapter()->setFetchMode(Zend_Db::FETCH_OBJ);
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
				$query = "SELECT $func(id) as discipline FROM (".$select." $order) AS T;";
				debug_log($query);
				$resultSet = $this->getDbTable()->getAdapter()->query($query)->fetchAll();
				$entries = array();
				foreach ($resultSet as $row) {
					$entry = $row->discipline;
					$entries[] = $entry;
				}
				return $entries;
         	}
		}
	}

}
