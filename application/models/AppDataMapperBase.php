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
// PLEASE DO NOT EDIT THIS FILE
// IT IS AUTOMATOCALLY GENERATED BY THE MODELLER
// AND ANY CHANGES WILL BE OVERWRITTEN
class Default_Model_AppDataMapperBase
{
	protected $_dbTable;

	private function pgBool($v) { if ($v) return 't'; else return 'f'; }

	public function setDbTable($dbTable)
	{
		if (is_string($dbTable)) {
			$dbTable = new $dbTable();
		}
		if (!$dbTable instanceof Zend_Db_Table_Abstract) {
			throw new Exception('Invalid table data gateway provided');
		}
			$this->_dbTable = $dbTable;
			return $this;
	}

	public function getDbTable()
	{
		if (null === $this->_dbTable) {
			$this->setDbTable('Default_Model_DbTable_AppData');
		}
		return $this->_dbTable;
	}

	public function save(Default_Model_AppDatum $value)
	{
		global $application;
		$data = array();
		if ( ! isnull($value->getId()) ) $data['id'] = $value->getId();
		if ( ! isnull($value->getAppID()) ) $data['appid'] = $value->getAppID();
		if ( ! isnull($value->getData()) ) $data['data'] = $value->getData();
		if ( ! isnull($value->getDescription()) ) $data['description'] = $value->getDescription();


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

	public function delete(Default_Model_AppDatum $value)
	{
		$q1 = 'id = ?';
		$q2 = $value->id;
		$s=$this->getDbTable()->getAdapter()->quoteInto($q1,$q2);
		$this->getDbTable()->delete($s);
	}

	public function populate(&$entry,$row)
	{
		$entry->setId($row->id);
		$entry->setAppID($row->appid);
		$entry->setData($row->data);
		$entry->setDescription($row->description);
	}

	public function find($id, Default_Model_AppData &$value)
	{
		$result = $this->getDbTable()->find($id);
		if (0 == count($result)) {
			return;
		}		$row = $result->current();
		$this->populate($value,$row);	}

	public function count($filter = null)
	{
		$select = $this->getDbTable()->select();
		$select->from($this->getDbTable(),array('COUNT(DISTINCT (id)) AS count'));
		if ( ($filter !== null) && ($filter->expr() != '') ) {
			$select->where($filter->expr());
		}
		$res = $this->getDbTable()->fetchAll($select);
		return $res[0]->count;
	}
	public function fetchAll($filter = null, $format = '')
	{
		$select = $this->getDbTable()->select();
		if ( ($filter !== null) && ($filter->expr() != '') ) {
			$select->where($filter->expr());
		}
		if ($filter !== null) $select->limit($filter->limit, $filter->offset);
		if ($filter !== null) $select->order($filter->orderBy);
		$resultSet = $this->getDbTable()->fetchAll($select);
		$entries = array();
		foreach ($resultSet as $row) {
			$entry = new Default_Model_AppDatum();
			$this->populate($entry,$row);
			if ($format === 'xml') $entry = $entry->toXML(true);
			$entries[] = $entry;
		}		return $entries;
	}
}