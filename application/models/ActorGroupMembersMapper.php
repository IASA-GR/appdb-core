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
class Default_Model_ActorGroupMembersMapper extends Default_Model_ActorGroupMembersMapperBase
{
	public function joins(&$select, $filter) {
//		$select->join('researchers.any','researchers.any.id = researchers.id', array());
		if ( is_array($filter->joins) ) {
			if (in_array("actor_groups", $filter->joins)) {
				$select->joinLeft('actor_groups', 'actor_group_members.groupid = actor_groups.id', array());
			}

		}
	}

	public function fetchAll($filter = null, $format = '', $userid = '', $xmldetailed = false) {
		if ( isset($filter) ) {
			$orderby = $filter->orderBy;
			if ( is_array($orderby) ) {
				$_orderby = implode(", ", $orderby);
			} else {
				$_orderby = $orderby;
			}
		} else {
			$orderby = "";
		}
		$select = $this->getDbTable()->select();
		$executor = $this->getDbTable();
		if ( (($filter !== null) && ($filter->expr() != '')) ) {
			$select = $this->getDbTable()->getAdapter()->select()->distinct()->from('actor_group_members');
			$this->joins($select, $filter);
			$select->where($filter->expr());
			$executor = $this->getDbTable()->getAdapter();
			$executor->setFetchMode(Zend_Db::FETCH_OBJ);
		}
		$select = fixuZenduBuguru("" . $select);
		noDBSeqScan($executor);
		$resultSet = $executor->fetchAll($select);
		$entries = array();
		foreach ($resultSet as $row) {
			$entry = new Default_Model_ActorGroupMember();
			$this->populate($entry, $row);
			$entries[] = $entry;
		}
		return $entries;
	}

}
