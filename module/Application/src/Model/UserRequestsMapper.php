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
			if (in_array("researchers", $filter->joins)) $select->join('researchers', 'researchers.guid = userrequests.userguid', array(), 'left');
			if (in_array("actor_groups", $filter->joins)) $select->join('actor_groups', 'actor_groups.guid = userrequests.targetguid', array(), 'left');
			if (in_array("userrequesttypes", $filter->joins)) $select->join('userrequesttypes','userrequesttypes.id = userrequests.typeid', array(), 'left');
			if (in_array("userrequeststates", $filter->joins)) $select->join('userrequeststates','userrequeststates.id = userrequests.stateid', array(), 'left');
			if (in_array("permissions", $filter->joins)) {
				$select->join('permissions','permissions.object = userrequests.guid OR permissions.object IS NULL', array(), 'left');
				$select->where('permissions.actionid = 25');
			}
		}
	}
	
	public function fetchAll($filter = null, $format = '') {
		$from = '';
		$where = '';
		$orderby = '';
		$limit = '';
		$select = $this->getDbTable()->getSql()->select();
		if ( ($filter !== null) && ($filter->expr() != '') ) {
			$select->quantifier('DISTINCT');
			$this->joins($select, $filter);
			$select->where($filter->expr());
		}
		if ($filter !== null) {
			if (! is_null($filter->limit)) $select->limit($filter->limit);
			if (! is_null($filter->offset)) $select->offset($filter->offset);
			if (! is_null($filter->orderBy)) $select->order($filter->orderBy);
		}
		getZendSelectParts($select, $from, $where, $orderby, $limit);
		$from = fixuZenduBuguru($from);
		$select = (new \Zend\Db\Sql\Sql($this->getDbTable()->getAdapter()))->getSqlStringForSqlObject($select);
		$select = str_replace('"IS" "NULL"', 'IS NULL', $select);
		$resultSet = db()->query($select, array())->toArray(); 
		$entries = array();
		foreach ($resultSet as $row) {
			$entry = new UserRequest();
			$this->populate($entry, $row);
			$entries[] = $entry;
		}		
		return $entries;
	}
	
	public function count($filter = null) {
		$select = $this->getDbTable()->getSql()->select();
		$executor = $this->getDbTable();
		$select->from($this->getDbTable(),array('COUNT(DISTINCT (userrequests.id)) AS count'));
		if ( ($filter !== null) && ($filter->expr() != '') ) {
			$select = $this->getDbTable()->getAdapter()->select()->distinct()->from('userrequests',array('COUNT(DISTINCT (userrequests.id)) AS count'));
			$this->joins($select, $filter);
			$select->where($filter->expr());
			$executor = $this->getDbTable()->getAdapter();
		}
		//debug_log("".$select);
		$res = $executor->fetchAll($select);
		return $res[0]->count;
	}
}
