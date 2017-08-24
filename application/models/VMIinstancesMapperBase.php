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
class Default_Model_VMIinstancesMapperBase
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
			$this->setDbTable('Default_Model_DbTable_VMIinstances');
		}
		return $this->_dbTable;
	}

	public function save(Default_Model_VMIinstance $value)
	{
		global $application;
		$data = array();
		if ( ! isnull($value->getId()) ) $data['id'] = $value->getId();
		if ( ! isnull($value->getSize()) ) $data['size'] = $value->getSize();
		if ( ! isnull($value->getUri()) ) $data['uri'] = $value->getUri();
		if ( ! isnull($value->getVersion()) ) $data['version'] = $value->getVersion();
		if ( ! isnull($value->getChecksum()) ) $data['checksum'] = $value->getChecksum();
		if ( ! isnull($value->getChecksumFunc()) ) $data['checksumfunc'] = $value->getChecksumFunc();
		if ( ! isnull($value->getNotes()) ) $data['notes'] = $value->getNotes();
		if ( ! isnull($value->getGuID()) ) $data['guid'] = $value->getGuID();
		if ( ! isnull($value->getAddedOn()) ) $data['addedon'] = $value->getAddedOn();
		if ( ! isnull($value->getAddedbyID()) ) $data['addedby'] = $value->getAddedbyID();
		if ( ! isnull($value->getVmiflavourID()) ) $data['vmiflavourid'] = $value->getVmiflavourID();
		if ( ! isnull($value->getAutoIntegrity()) ) $data['autointegrity'] = $this->pgBool($value->getAutoIntegrity());
		if ( ! isnull($value->getCoreMinimum()) ) $data['coreminimum'] = $value->getCoreMinimum();
		if ( ! isnull($value->getRAMminimum()) ) $data['ramminimum'] = $value->getRAMminimum();
		if ( ! isnull($value->getLastUpdatedByID()) ) $data['lastupdatedby'] = $value->getLastUpdatedByID();
		if ( ! isnull($value->getLastUpdatedOn()) ) $data['lastupdatedon'] = $value->getLastUpdatedOn();
		if ( ! isnull($value->getDescription()) ) $data['description'] = $value->getDescription();
		if ( ! isnull($value->getTitle()) ) $data['title'] = $value->getTitle();
		if ( ! isnull($value->getIntegrityStatus()) ) $data['integrity_status'] = $value->getIntegrityStatus();
		if ( ! isnull($value->getIntegrityMessage()) ) $data['integrity_message'] = $value->getIntegrityMessage();
		if ( ! isnull($value->getRAMrecommend()) ) $data['ramrecommend'] = $value->getRAMrecommend();
		if ( ! isnull($value->getCoreRecommend()) ) $data['corerecommend'] = $value->getCoreRecommend();
		if ( ! isnull($value->getAccelRecommend()) ) $data['rec_acc'] = $value->getAccelRecommend();
		if ( ! isnull($value->getAccelMinimum()) ) $data['min_acc'] = $value->getAccelMinimum();
		if ( ! isnull($value->getAccelType()) ) $data['rec_acc_type'] = $value->getAccelType();
		if ( ! isnull($value->getAccessinfo()) ) $data['accessinfo'] = $value->getAccessinfo();
		if ( ! isnull($value->getEnabled()) ) $data['enabled'] = $this->pgBool($value->getEnabled());
		if ( ! isnull($value->getInitialsize()) ) $data['initialsize'] = $value->getInitialsize();
		if ( ! isnull($value->getInitialchecksum()) ) $data['initialchecksum'] = $value->getInitialchecksum();
		if ( ! isnull($value->getOVFURL()) ) $data['ovfurl'] = $value->getOVFURL();
		if ( ! isnull($value->getDefaultAccess()) ) $data['default_access'] = $value->getDefaultAccess();


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

	public function delete(Default_Model_VMIinstance $value)
	{
		$q1 = 'id = ?';
		$q2 = $value->id;
		$s=$this->getDbTable()->getAdapter()->quoteInto($q1,$q2);
		$this->getDbTable()->delete($s);
	}

	public function populate(&$entry,$row)
	{
		$entry->setId($row->id);
		$entry->setSize($row->size);
		$entry->setUri($row->uri);
		$entry->setVersion($row->version);
		$entry->setChecksum($row->checksum);
		$entry->setChecksumFunc($row->checksumfunc);
		$entry->setNotes($row->notes);
		$entry->setGuID($row->guid);
		$entry->setAddedOn($row->addedon);
		$entry->setAddedbyID($row->addedby);
		$entry->setVmiflavourID($row->vmiflavourid);
		$entry->setAutoIntegrity($row->autointegrity);
		$entry->setCoreMinimum($row->coreminimum);
		$entry->setRAMminimum($row->ramminimum);
		$entry->setLastUpdatedByID($row->lastupdatedby);
		$entry->setLastUpdatedOn($row->lastupdatedon);
		$entry->setDescription($row->description);
		$entry->setTitle($row->title);
		$entry->setIntegrityStatus($row->integrity_status);
		$entry->setIntegrityMessage($row->integrity_message);
		$entry->setRAMrecommend($row->ramrecommend);
		$entry->setCoreRecommend($row->corerecommend);
		$entry->setAccelRecommend($row->rec_acc);
		$entry->setAccelType($row->rec_acc_type);
		$entry->setAccelMinimum($row->min_acc);
		$entry->setAccessinfo($row->accessinfo);
		$entry->setEnabled($row->enabled);
		$entry->setInitialsize($row->initialsize);
		$entry->setInitialchecksum($row->initialchecksum);
		$entry->setOVFURL($row->ovfurl);
		$entry->setDefaultAccess($row->default_access);
	}

	public function find($id, Default_Model_VMIinstances &$value)
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
			$entry = new Default_Model_VMIinstance();
			$this->populate($entry,$row);
			if ($format === 'xml') $entry = $entry->toXML(true);
			$entries[] = $entry;
		}		return $entries;
	}
}
