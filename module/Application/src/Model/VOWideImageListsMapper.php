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
class Default_Model_VOWideImageListsMapper extends Default_Model_VOWideImageListsMapperBase
{
	public function joins(&$select, $filter) {
		if ( is_array($filter->joins) ) {
			if ( in_array("vowide_image_list_images", $filter->joins) ) {
				$select->joinLeft('vowide_image_list_images', 'vowide_image_list_images.vowide_image_list_id = vowide_image_lists.id', array());
			}
		}
	}
	
	public function count($filter = null) {
		return count($this->fetchAll($filter));
	}

	public function fetchAll($filter = null, $format = '')
	{
		$select = $this->getDbTable()->select();
		$executor = $this->getDbTable();
		if ( $filter !== null ) {
			$select = $this->getDbTable()->getAdapter()->select()->distinct()->from('vowide_image_lists');
			if ($filter->expr() != '') {
				$this->joins($select, $filter);
				if ( ! is_array($filter->expr()) ) $select->where($filter->expr());
				$executor = $this->getDbTable()->getAdapter();
				$executor->setFetchMode(Zend_Db::FETCH_OBJ);
			}
		}
		if ($filter !== null) $select->limit($filter->limit, $filter->offset);
		if ($filter !== null) $select->order($filter->orderBy);
		noDBSeqScan($executor);
		$query = fixuZenduBuguru("" . $select);
		$resultSet = $executor->fetchAll($query);
		$entries = array();
		foreach ($resultSet as $row) {
			$entry = new Default_Model_VOWideImageList();
			$this->populate($entry,$row);
			if ($format === 'xml') $entry = $entry->toXML(true);
			$entries[] = $entry;
		}
		return $entries;
	}

}
