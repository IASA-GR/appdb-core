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

class UserRequestsMapper extends UserRequestsMapperBase
{
	private function joins(&$select, $filter) {
		if ( is_array($filter->joins) ) {
			if (in_array("researchers", $filter->joins)) $select->joinLeft('researchers', 'researchers.guid = userrequests.userguid', array());
			if (in_array("actor_groups", $filter->joins)) $select->joinLeft('actor_groups', 'actor_groups.guid = userrequests.targetguid', array());
			if (in_array("userrequesttypes", $filter->joins)) $select->joinLeft('userrequesttypes','userrequesttypes.id = userrequests.typeid', array());
			if (in_array("userrequeststates", $filter->joins)) $select->joinLeft('userrequeststates','userrequeststates.id = userrequests.stateid', array());
			if (in_array("permissions", $filter->joins)) {
				$select->joinLeft('permissions','permissions.object = userrequests.guid OR permissions.object IS NULL', array());
				$select->where('permissions.actionid = 25');
			}
		}
	}
	
	public function fetchAll($filter = null, $format = '') {
		$select = $this->getDbTable()->select();
		$executor = $this->getDbTable();
		if ( ($filter !== null) && ($filter->expr() != '') ) {
			$select = $this->getDbTable()->getAdapter()->select()->distinct()->from('userrequests');
			$this->joins($select, $filter);
			$select->where($filter->expr());
			$executor = $this->getDbTable()->getAdapter();
			$executor->setFetchMode(Zend_Db::FETCH_OBJ);
		}
		if ($filter !== null) $select->limit($filter->limit, $filter->offset);
		if ($filter !== null) $select->order($filter->orderBy);
		
		$resultSet = $executor->fetchAll($select);
		$entries = array();
		foreach ($resultSet as $row) {
			$entry = new UserRequest();
			$this->populate($entry,$row);
			$entries[] = $entry;
		}		
		return $entries;
	}
	
	public function count($filter = null) {
		$select = $this->getDbTable()->select();
		$executor = $this->getDbTable();
		$select->from($this->getDbTable(),array('COUNT(DISTINCT (userrequests.id)) AS count'));
		if ( ($filter !== null) && ($filter->expr() != '') ) {
			$select = $this->getDbTable()->getAdapter()->select()->distinct()->from('userrequests',array('COUNT(DISTINCT (userrequests.id)) AS count'));
			$this->joins($select, $filter);
			$select->where($filter->expr());
			$executor = $this->getDbTable()->getAdapter();
			$executor->setFetchMode(Zend_Db::FETCH_OBJ);
		}
		//debug_log("".$select);
		$res = $executor->fetchAll($select);
		return $res[0]->count;
	}
}
