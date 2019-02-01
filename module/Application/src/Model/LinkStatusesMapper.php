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

class LinkStatusesMapper extends LinkStatusesMapperBase
{
	private function joins(&$select, $filter) {
        if ( is_array($filter->joins) ) {
			if (in_array("countries", $filter->joins)) {
					$select->join('appcountries','linkstatuses.appid = appcountries.appid', array(), 'left');
					$select->join('countries','countries.id = appcountries.id', array(), 'left');
			}
		}
	}

	public function count($filter = null)
    {
		$select = $this->getDbTable()->getSql()->select();
		$executor = $this->getDbTable();
		$select->from($this->getDbTable(),array('COUNT(DISTINCT (linkid,linktype)) FROM linkstatuses AS count'));
		if ( ($filter !== null) && ($filter->expr() != '') ) {
			$select = $this->getDbTable()->getAdapter()->select()->distinct()->from('linkstatuses',array('COUNT(DISTINCT (linkid,linktype)) AS count'));
			$this->joins($select, $filter);
			$select->where($filter->expr());
			$executor = $this->getDbTable()->getAdapter();
        }
        //debug_log("".$select);
		$res = $executor->fetchAll($select);
		return $res[0]->count;
	}

	public function fetchAll($filter = null, $format = '')
	{
		$select = $this->getDbTable()->getSql()->select();
		$executor = $this->getDbTable();
		if ( (($filter !== null) && ($filter->expr() != '')) ) {
			$select = $this->getDbTable()->getAdapter()->select()->distinct()->from('linkstatuses');
			if ( $filter !== null ) {
				if ($filter->expr() != '') {
					$this->joins($select, $filter);
					$select->where($filter->expr());
					$executor = $this->getDbTable()->getAdapter();
				}
			}
		}
		if (! is_null($filter)) {
	if (! is_null($filter->limit)) $select->limit($filter->limit);
	if (! is_null($filter->offset)) $select->offset($filter->offset);
}
		if ($filter !== null) {
			$orderby = $filter->orderBy;
			$select->order($orderby);
		}
		$resultSet = $executor->fetchAll($select);
		$entries = array();
		foreach ($resultSet as $row) {
			$entry = new LinkStatus();
			$this->populate($entry,$row);
			$entries[] = $entry;
		}		
		return $entries;
	}
}

