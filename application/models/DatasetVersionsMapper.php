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
class Default_Model_DatasetVersionsMapper extends Default_Model_DatasetVersionsMapperBase
{
	public function save(Default_Model_DatasetVersion $value)
	{
		global $application;
		$data = array();
		if ( ! isnull($value->getId()) ) $data['id'] = $value->getId();
		if ( ! isnull($value->getDatasetID()) ) $data['datasetid'] = $value->getDatasetID();
		if ( ! isnull($value->getVersion()) ) $data['version'] = $value->getVersion();
		if ( ! isnull($value->getNotes()) ) $data['notes'] = $value->getNotes();
		if ( ! isnull($value->getSize()) ) $data['size'] = $value->getSize();
		if ( ! isnull($value->getAddedByID()) ) $data['addedby'] = $value->getAddedByID();
		if ( ! isnull($value->getParentID()) ) $data['parentid'] = $value->getParentID();
		if ( ! isnull($value->getAddedOn()) ) $data['addedon'] = $value->getAddedOn();
		if ( ! isnull($value->getGuID()) ) $data['guid'] = $value->getGuID();

		if ( $value->getParentID() == "0" ) $data['parentid'] = null;

		$q1 = 'id = ?';
		$q2 = $value->id;
		if (null === ($id = $value->id)) {
			unset($data['id']);
			$value->id = $this->getDbTable()->insert($data);
		} else {
			$s = $this->getDbTable()->getAdapter()->quoteInto($q1,$q2);
			$this->getDbTable()->update($data, $s);
		}
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
					if ( $ord == '' ) $ord = 'addedon DESC';
					$order = "ORDER BY ".$ord;
				}
				if ( $order == '' ) $order = "ORDER BY addedon DESC";
				if ( $xmldetailed ) {
					$func = "dataset_version_to_xml";
				} else {
					$func = "dataset_version_to_xml";
				}
				//error_log("".$select);
				$query = "SELECT $func(id) as dataset FROM (".$select." $order) AS T;";
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
