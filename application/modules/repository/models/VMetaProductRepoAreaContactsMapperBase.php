<?php
// PLEASE DO NOT EDIT THIS FILE
// IT IS AUTOMATOCALLY GENERATED BY THE MODELLER
// AND ANY CHANGES WILL BE OVERWRITTEN
class Repository_Model_VMetaProductRepoAreaContactsMapperBase
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
			$this->setDbTable('Repository_Model_DbTable_VMetaProductRepoAreaContacts');
		}
		return $this->_dbTable;
	}

	public function save(Repository_Model_VMetaProductRepoAreaContact $value)
	{
		global $application;
		$data = array();
		if ( ! isnull($value->getPseudoId()) ) $data['pseudoId'] = $value->getPseudoId();
		if ( ! isnull($value->getExternalId()) ) $data['externalId'] = $value->getExternalId();
		if ( ! isnull($value->getContactTypeId()) ) $data['contactTypeId'] = $value->getContactTypeId();
		if ( ! isnull($value->getFirstname()) ) $data['firstname'] = $value->getFirstname();
		if ( ! isnull($value->getLastname()) ) $data['lastname'] = $value->getLastname();
		if ( ! isnull($value->getEmail()) ) $data['email'] = $value->getEmail();
		if ( ! isnull($value->getRepoareaID()) ) $data['repoareaid'] = $value->getRepoareaID();


		$q1 = '';
		$q2 = '';
		if (null === ($id = '')) {
			unset($data['']);
			$this->getDbTable()->insert($data);
		} else {
			$s = $this->getDbTable()->getAdapter()->quoteInto($q1,$q2);
			$this->getDbTable()->update($data, $s);
		}
	}

	public function delete(Repository_Model_VMetaProductRepoAreaContact $value)
	{
		$q1 = '';
		$q2 = '';
		$s=$this->getDbTable()->getAdapter()->quoteInto($q1,$q2);
		$this->getDbTable()->delete($s);
	}

	public function populate(&$entry,$row)
	{
		$entry->setPseudoId($row->pseudoId);
		$entry->setExternalId($row->externalId);
		$entry->setContactTypeId($row->contactTypeId);
		$entry->setFirstname($row->firstname);
		$entry->setLastname($row->lastname);
		$entry->setEmail($row->email);
		$entry->setRepoareaID($row->repoareaid);
	}

	public function find($id, Repository_Model_VMetaProductRepoAreaContacts &$value)
	{
		$result = $this->getDbTable()->find($id);
		if (0 == count($result)) {
			return;
		}		$row = $result->current();
		$this->populate($value,$row);	}

	public function count($filter = null)
	{
		$select = $this->getDbTable()->select();
		$select->from($this->getDbTable(),array('COUNT(*) AS count'));
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
			$entry = new Repository_Model_VMetaProductRepoAreaContact();
			$this->populate($entry,$row);
			if ($format === 'xml') $entry = $entry->toXML(true);
			$entries[] = $entry;
		}		return $entries;
	}
}