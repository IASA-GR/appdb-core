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
class Default_Model_DatasetLocationsMapper extends Default_Model_DatasetLocationsMapperBase
{
	private function pgBool($v) { if ($v) return 't'; else return 'f'; }

	public function populate(&$entry, $row) {
		parent::populate($entry,$row);
		$entry->setSiteID(pg_to_php_array($row->siteid));
		$entry->setOrganizationID(pg_to_php_array($row->organizationid));
	}

	public function save(Default_Model_DatasetLocation $value) {
		$value->siteID = null;
		$value->organizationID = null;
		parent::save($value);
	}
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
					if ( $ord == '' ) $ord = 'addedon';
					$order = "ORDER BY ".$ord;
				}
				if ( $order == '' ) $order = "ORDER BY addedon";
				if ( $xmldetailed ) {
					$func = "dataset_location_to_xml";
				} else {
					$func = "dataset_location_to_xml";
				}
				//error_log("".$select);
				$query = "SELECT $func(id) as dataset FROM (".$select." $order) AS T;";
				//debug_log($query);
				$resultSet = $this->getDbTable()->getAdapter()->query($query)->fetchAll();
				$entries = array();
				foreach ($resultSet as $row) {
					$entry = $row->dataset;
					$entries[] = $entry;
				}
				return $entries;
         	}
		}
	}
}
