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
class Default_Model_LinkStatusesMapper extends Default_Model_LinkStatusesMapperBase
{
	private function joins(&$select, $filter) {
        if ( is_array($filter->joins) ) {
			if (in_array("countries", $filter->joins)) {
					$select->joinLeft('appcountries','linkstatuses.appid = appcountries.appid', array());
					$select->joinLeft('countries','countries.id = appcountries.id', array());
			}
		}
	}

	public function count($filter = null)
    {
		$select = $this->getDbTable()->select();
		$executor = $this->getDbTable();
		$select->from($this->getDbTable(),array('COUNT(DISTINCT (linkid,linktype)) FROM linkstatuses AS count'));
		if ( ($filter !== null) && ($filter->expr() != '') ) {
			$select = $this->getDbTable()->getAdapter()->select()->distinct()->from('linkstatuses',array('COUNT(DISTINCT (linkid,linktype)) AS count'));
			$this->joins($select, $filter);
			$select->where($filter->expr());
			$executor = $this->getDbTable()->getAdapter();
			$executor->setFetchMode(Zend_Db::FETCH_OBJ);
        }
        //debug_log("".$select);
		$res = $executor->fetchAll($select);
		return $res[0]->count;
	}

	public function fetchAll($filter = null, $format = '')
	{
		$select = $this->getDbTable()->select();
		$executor = $this->getDbTable();
		if ( (($filter !== null) && ($filter->expr() != '')) ) {
			$select = $this->getDbTable()->getAdapter()->select()->distinct()->from('linkstatuses');
			if ( $filter !== null ) {
				if ($filter->expr() != '') {
					$this->joins($select, $filter);
					$select->where($filter->expr());
					$executor = $this->getDbTable()->getAdapter();
					$executor->setFetchMode(Zend_Db::FETCH_OBJ);
				}
			}
		}
		if ($filter !== null) $select->limit($filter->limit, $filter->offset);
		if ($filter !== null) {
			$orderby = $filter->orderBy;
			$select->order($orderby);
		}
		$resultSet = $executor->fetchAll($select);
		$entries = array();
		foreach ($resultSet as $row) {
			$entry = new Default_Model_LinkStatus();
			$this->populate($entry,$row);
			$entries[] = $entry;
		}		
		return $entries;
	}
}

