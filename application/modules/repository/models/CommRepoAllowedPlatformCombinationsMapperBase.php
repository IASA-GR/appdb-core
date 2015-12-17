<?php
// PLEASE DO NOT EDIT THIS FILE
// IT IS AUTOMATOCALLY GENERATED BY THE MODELLER
// AND ANY CHANGES WILL BE OVERWRITTEN
class Repository_Model_CommRepoAllowedPlatformCombinationsMapperBase
{
	protected $_dbTable;

	private function myBool($v) { if ($v) return 1; else return 0; }

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
			$this->setDbTable('Repository_Model_DbTable_CommRepoAllowedPlatformCombinations');
		}
		return $this->_dbTable;
	}

	public function save(Repository_Model_CommRepoAllowedPlatformCombination $value)
	{
		global $application;
		$data = array();
		if ( ! isnull($value->getId()) ) $data['id'] = $value->getId();
		if ( ! isnull($value->getOsId()) ) $data['osId'] = $value->getOsId();
		if ( ! isnull($value->getArchId()) ) $data['archId'] = $value->getArchId();
		if ( ! isnull($value->getCanSupport()) ) $data['canSupport'] = $value->getCanSupport();
		if ( ! isnull($value->getFsPattern()) ) $data['fsPattern'] = $value->getFsPattern();
		if ( ! isnull($value->getIncRelSupport()) ) $data['incRelSupport'] = $value->getIncRelSupport();


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

	public function delete(Repository_Model_CommRepoAllowedPlatformCombination $value)
	{
		$q1 = 'id = ?';
		$q2 = $value->id;
		$s=$this->getDbTable()->getAdapter()->quoteInto($q1,$q2);
		$this->getDbTable()->delete($s);
	}

	public function populate(&$entry,$row)
	{
		$entry->setId($row->id);
		$entry->setOsId($row->osId);
		$entry->setArchId($row->archId);
		$entry->setCanSupport($row->canSupport);
		$entry->setFsPattern($row->fsPattern);
		$entry->setIncRelSupport($row->incRelSupport);
	}

	public function find($id, Repository_Model_CommRepoAllowedPlatformCombinations &$value)
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
			$entry = new Repository_Model_CommRepoAllowedPlatformCombination();
			$this->populate($entry,$row);
			if ($format === 'xml') $entry = $entry->toXML(true);
			$entries[] = $entry;
		}		return $entries;
	}
}