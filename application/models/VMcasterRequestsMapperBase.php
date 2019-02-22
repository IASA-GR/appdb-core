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
class Default_Model_VMcasterRequestsMapperBase
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
			$this->setDbTable('Default_Model_DbTable_VMcasterRequests');
		}
		return $this->_dbTable;
	}

	public function save(Default_Model_VMcasterRequest $value)
	{
		global $application;
		$data = array();
		if ( ! isnull($value->getId()) ) $data['id'] = $value->getId();
		if ( ! isnull($value->getStatus()) ) $data['status'] = $value->getStatus();
		if ( ! isnull($value->getUsername()) ) $data['username'] = $value->getUsername();
		if ( ! isnull($value->getPassword()) ) $data['password'] = $value->getPassword();
		if ( ! isnull($value->getAuthType()) ) $data['authtype'] = $value->getAuthType();
		if ( ! isnull($value->getErrorMessage()) ) $data['errormessage'] = $value->getErrorMessage();
		if ( ! isnull($value->getInsertedOn()) ) $data['insertedon'] = $value->getInsertedOn();
		if ( ! isnull($value->getLastSubmitted()) ) $data['lastsubmitted'] = $value->getLastSubmitted();
		if ( ! isnull($value->getIp()) ) $data['ip'] = $value->getIp();
		if ( ! isnull($value->getInputVMIL()) ) $data['input_vmil'] = $value->getInputVMIL();
		if ( ! isnull($value->getProducedXML()) ) $data['produced_xml'] = $value->getProducedXML();
		if ( ! isnull($value->getAppID()) ) $data['appid'] = $value->getAppID();
		if ( ! isnull($value->getAction()) ) $data['action'] = $value->getAction();
		if ( ! isnull($value->getEntity()) ) $data['entity'] = $value->getEntity();
		if ( ! isnull($value->getLdapSn()) ) $data['ldap_sn'] = $value->getLdapSn();
		if ( ! isnull($value->getLdapDn()) ) $data['ldap_dn'] = $value->getLdapDn();
		if ( ! isnull($value->getLdapEmail()) ) $data['ldap_email'] = $value->getLdapEmail();
		if ( ! isnull($value->getLdapDisplayname()) ) $data['ldap_displayname'] = $value->getLdapDisplayname();
		if ( ! isnull($value->getLdapCn()) ) $data['ldap_cn'] = $value->getLdapCn();
		if ( ! isnull($value->getLdapUsercertificatesubject()) ) $data['ldap_usercertificatesubject'] = $value->getLdapUsercertificatesubject();
		if ( ! isnull($value->getLdapGivenname()) ) $data['ldap_givenname'] = $value->getLdapGivenname();
		if ( ! isnull($value->getRID()) ) $data['rid'] = $value->getRID();
		if ( ! isnull($value->getUID()) ) $data['uid'] = $value->getUID();


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

	public function delete(Default_Model_VMcasterRequest $value)
	{
		$q1 = 'id = ?';
		$q2 = $value->id;
		$s=$this->getDbTable()->getAdapter()->quoteInto($q1,$q2);
		$this->getDbTable()->delete($s);
	}

	public function populate(&$entry,$row)
	{
		$entry->setId($row->id);
		$entry->setStatus($row->status);
		$entry->setUsername($row->username);
		$entry->setPassword($row->password);
		$entry->setAuthType($row->authtype);
		$entry->setErrorMessage($row->errormessage);
		$entry->setInsertedOn($row->insertedon);
		$entry->setLastSubmitted($row->lastsubmitted);
		$entry->setIp($row->ip);
		$entry->setInputVMIL($row->input_vmil);
		$entry->setProducedXML($row->produced_xml);
		$entry->setAppID($row->appid);
		$entry->setAction($row->action);
		$entry->setEntity($row->entity);
		$entry->setLdapSn($row->ldap_sn);
		$entry->setLdapDn($row->ldap_dn);
		$entry->setLdapEmail($row->ldap_email);
		$entry->setLdapDisplayname($row->ldap_displayname);
		$entry->setLdapCn($row->ldap_cn);
		$entry->setLdapUsercertificatesubject($row->ldap_usercertificatesubject);
		$entry->setLdapGivenname($row->ldap_givenname);
		$entry->setRID($row->rid);
		$entry->setUID($row->uid);
	}

	public function find($id, Default_Model_VMcasterRequests &$value)
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
			$entry = new Default_Model_VMcasterRequest();
			$this->populate($entry,$row);
			if ($format === 'xml') $entry = $entry->toXML(true);
			$entries[] = $entry;
		}		return $entries;
	}
}