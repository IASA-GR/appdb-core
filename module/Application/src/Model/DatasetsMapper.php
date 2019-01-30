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

class DatasetsMapper extends DatasetsMapperBase
{
	public function populate(&$entry, $row) {
		parent::populate($entry,$row);
		$entry->setTags(pg_to_php_array($row->tags));
	}

	public function save(AROItem $value) {
		$oldTags = $value->tags;
		if ((! is_array($value->tags)) || (count($value->tags) == 0) || (trim($value->tags[0]) == "")) {
			$value->setTags("NULL");
		} else {
			$value->setTags(php_to_pg_array($value->tags));
		}

//		parent::save($value);
		global $application;
		$data = array();
		if ( ! isnull($value->getId()) ) $data['id'] = $value->getId();
		if ( ! isnull($value->getName()) ) $data['name'] = $value->getName();
		if ( ! isnull($value->getCategory()) ) $data['category'] = $value->getCategory();
		if ( ! isnull($value->getDescription()) ) $data['description'] = $value->getDescription();
		if ( ! isnull($value->getHomepage()) ) $data['homepage'] = $value->getHomepage();
		if ( ! isnull($value->getElixirURL()) ) $data['elixir_url'] = $value->getElixirURL();
		if ( ! isnull($value->getDisciplineID()) ) $data['disciplineid'] = $value->getDisciplineID();
		if ( ! isnull($value->getAddedByID()) ) $data['addedby'] = $value->getAddedByID();
		if ( ! isnull($value->getAddedon()) ) $data['addedon'] = $value->getAddedon();
		if ( ! isnull($value->getTags()) ) $data['tags'] = $value->getTags();
		if ( ! isnull($value->getGuID()) ) $data['guid'] = $value->getGuID();
		if ( ! isnull($value->getParentID()) ) $data['parentid'] = $value->getParentID();
		
		if ($value->getParentID() == "0") $data['parentid'] = null;
		if (trim($value->getHomepage()) === "") $data['homepage'] = null;
		if (trim($value->getElixirURL()) === "") $data['elixir_url'] = null;
		if (trim($value->getDescription()) === "") $data['description'] = null;
		if ($value->getTags() === "NULL") $data['tags'] = null;

		$q1 = 'id = ?';
		$q2 = $value->id;
		if (null === ($id = $value->id)) {
			unset($data['id']);
			$value->id = $this->getDbTable()->insert($data);
		} else {
			$s = $this->getDbTable()->getAdapter()->quoteInto($q1,$q2);
			$this->getDbTable()->update($data, $s);
		}

		$value->setTags($oldTags);
	}

	public function fetchAll($filter = null, $format = '', $xmldetailed = false, $xmlflat = false) {
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
				if ( $xmldetailed === "listing") {
					$func = "dataset_to_xml_list";
				} elseif ( $xmldetailed === true ) {
					$func = "dataset_to_xml";
				} else {
					$func = "dataset_to_xml";
				}
				$query = "SELECT $func(array_agg(id), " . ($xmlflat ? "TRUE" : "FALSE") . ") as dataset FROM (".$select." $order) AS T;";
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
