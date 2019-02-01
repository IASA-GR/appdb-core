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

class VOWideImageListsMapper extends VOWideImageListsMapperBase
{
	public function joins(&$select, $filter) {
		if ( is_array($filter->joins) ) {
			if ( in_array("vowide_image_list_images", $filter->joins) ) {
				$select->join('vowide_image_list_images', 'vowide_image_list_images.vowide_image_list_id = vowide_image_lists.id', array(), 'left');
			}
		}
	}
	
	public function count($filter = null) {
		return count($this->fetchAll($filter));
	}

	public function fetchAll($filter = null, $format = '')
	{
		$select = $this->getDbTable()->getSql()->select();
		$executor = $this->getDbTable();
		if ( $filter !== null ) {
			$select = $this->getDbTable()->getAdapter()->select()->distinct()->from('vowide_image_lists');
			if ($filter->expr() != '') {
				$this->joins($select, $filter);
				if ( ! is_array($filter->expr()) ) $select->where($filter->expr());
				$executor = $this->getDbTable()->getAdapter();
			}
		}
		if (! is_null($filter)) {
	if (! is_null($filter->limit)) $select->limit($filter->limit);
	if (! is_null($filter->offset)) $select->offset($filter->offset);
}
		if ($filter !== null) $select->order($filter->orderBy);
		noDBSeqScan($executor);
		$query = fixuZenduBuguru("" . $select);
		$resultSet = $executor->fetchAll($query);
		$entries = array();
		foreach ($resultSet as $row) {
			$entry = new VOWideImageList();
			$this->populate($entry,$row);
			if ($format === 'xml') $entry = $entry->toXML(true);
			$entries[] = $entry;
		}
		return $entries;
	}

}
