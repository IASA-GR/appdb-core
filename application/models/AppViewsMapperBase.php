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
class Default_Model_AppViewsMapperBase
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
			$this->setDbTable('Default_Model_DbTable_AppViews');
		}
		return $this->_dbTable;
	}

	public function save(Default_Model_AppView $value)
	{
		global $application;
		$data = array();
		if ( ! isnull($value->getId()) ) $data['id'] = $value->getId();
		if ( ! isnull($value->getName()) ) $data['name'] = $value->getName();
		if ( ! isnull($value->getDescription()) ) $data['description'] = $value->getDescription();
		if ( ! isnull($value->getAbstract()) ) $data['abstract'] = $value->getAbstract();
		if ( ! isnull($value->getLogo()) ) $data['logo'] = $value->getLogo();
		if ( ! isnull($value->getStatusID()) ) $data['statusid'] = $value->getStatusID();
		if ( ! isnull($value->getMiddlewareID()) ) $data['middlewareid'] = $value->getMiddlewareID();
		if ( ! isnull($value->getDateAdded()) ) $data['dateadded'] = $value->getDateAdded();
		if ( ! isnull($value->getAddedBy()) ) $data['addedby'] = $value->getAddedBy();
		if ( ! isnull($value->getTool()) ) $data['tool'] = $this->pgBool($value->getTool());
		if ( ! isnull($value->getRespect()) ) $data['respect'] = $this->pgBool($value->getRespect());
		if ( ! isnull($value->getCountryID()) ) $data['countryid'] = $value->getCountryID();
		if ( ! isnull($value->getRegionID()) ) $data['regionid'] = $value->getRegionID();
		if ( ! isnull($value->getVoID()) ) $data['void'] = $value->getVoID();
		if ( ! isnull($value->getPersonData()) ) $data['persondata'] = $value->getPersonData();
		if ( ! isnull($value->getHasDocs()) ) $data['hasdocs'] = $this->pgBool($value->getHasDocs());
		if ( ! isnull($value->getGuid()) ) $data['guid'] = $value->getGuid();
		if ( ! isnull($value->getDeleted()) ) $data['deleted'] = $this->pgBool($value->getDeleted());
		if ( ! isnull($value->getModerated()) ) $data['moderated'] = $this->pgBool($value->getModerated());


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

	public function delete(Default_Model_AppView $value)
	{
		$q1 = '';
		$q2 = '';
		$s=$this->getDbTable()->getAdapter()->quoteInto($q1,$q2);
		$this->getDbTable()->delete($s);
	}

	public function populate(&$entry,$row)
	{
		$entry->setId($row->id);
		$entry->setName($row->name);
		$entry->setDescription($row->description);
		$entry->setAbstract($row->abstract);
		$entry->setLogo($row->logo);
		$entry->setStatusID($row->statusid);
		$entry->setMiddlewareID($row->middlewareid);
		$entry->setDateAdded($row->dateadded);
		$entry->setAddedBy($row->addedby);
		$entry->setTool($row->tool);
		$entry->setRespect($row->respect);
		$entry->setCountryID($row->countryid);
		$entry->setRegionID($row->regionid);
		$entry->setVoID($row->void);
		$entry->setPersonData($row->persondata);
		$entry->setHasDocs($row->hasdocs);
		$entry->setGuid($row->guid);
		$entry->setDeleted($row->deleted);
		$entry->setModerated($row->moderated);
	}

	public function find($id, Default_Model_AppViews &$value)
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
			$entry = new Default_Model_AppView();
			$this->populate($entry,$row);
			if ($format === 'xml') $entry = $entry->toXML(true);
			$entries[] = $entry;
		}		return $entries;
	}
}