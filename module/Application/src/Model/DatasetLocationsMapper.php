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

class DatasetLocationsMapper extends DatasetLocationsMapperBase
{
	private function pgBool($v) { if ($v) return 't'; else return 'f'; }

	public function populate(&$entry, $row) {
		parent::populate($entry,$row);
		$entry->setSiteID(pg_to_php_array($row['siteid']));
		$entry->setOrganizationID(pg_to_php_array($row['organizationid']));
	}

	public function save(AROItem $value) {
		$value->siteID = null;
		$value->organizationID = null;
		parent::save($value);
	}
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
					if ( $ord == '' ) $ord = 'addedon';
					$order = "ORDER BY ".$ord;
				}
				if ( $order == '' ) $order = "ORDER BY addedon";
				if ( $xmldetailed ) {
					$func = "dataset_location_to_xml";
				} else {
					$func = "dataset_location_to_xml";
				}
				$query = "SELECT $func(id) as dataset FROM (" . SQL2STR($this, $select) . " $order) AS T;";
				$resultSet = $this->getDbTable()->getAdapter()->query($query, array())->toArray();
				$entries = array();
				foreach ($resultSet as $row) {
					$entry = $row['dataset'];
					$entries[] = $entry;
				}
				return $entries;
         	}
		}
	}
}
