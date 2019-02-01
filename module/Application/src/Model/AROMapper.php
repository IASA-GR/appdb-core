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

class AROMapper {
	protected $_dbTable;
	protected $_basename;
	protected $_baseitemname;
	protected $_data;
	
	public function __construct() {
	}
	
	public function __destruct() {
		unset($this->_dbTable);
	}

	private function pgBool($v) {
		if ($v) return 't'; else return 'f'; 
	}

	public function setDbTable($dbTable)
	{
		if (is_string($dbTable)) {
			$dbTable = new $dbTable();
		}
//		if (!$dbTable instanceof \Zend\Db\TableGateway\AbstractTableGateway) {
//			throw new \Exception('Invalid table data gateway provided');
//		}
			$this->_dbTable = $dbTable;
			return $this;
	}

	public function getDbTable()
	{
		if (null === $this->_dbTable) {
			$this->setDbTable("Application\\Model\\DbTable\\". $this->_basename);
		}
		return $this->_dbTable;
	}
	
	protected function _presave(AROItem $value) {
		$this->_data = array();
		foreach ($value->properties as $p) {
			if ((! $p->isFKO) && (! isnull($p->value))) {
				$this->_data[$p->dbcol] = $p->value;
			}
		}
	}
	
	protected function _postsave() {
		for ($i = count($this->_data) - 1; $i >= 0; --$i) {
			unset($this->_data);
		}
		unset($this->_data);
	}

	public function save(AROItem $value)
	{
		$this->_presave($value);

		$q1 = 'id = ?';
		$q2 = $value->id;
		if (null === ($id = $value->id)) {
			unset($this->_data['id']);
			$value->id = $this->getDbTable()->insert($this->_data);
		} else {
			$s = $this->getDbTable()->getAdapter()->quoteInto($q1, $q2);
			$this->getDbTable()->update($this->_data, $s);
		}
		
		$this->_postsave();
	}

	public function delete($value)
	{
		$q1 = 'id = ?';
		$q2 = $value->id;
		$s=$this->getDbTable()->getAdapter()->quoteInto($q1,$q2);
		$this->getDbTable()->delete($s);
	}

	public function populate(&$entry, $row)
	{
		foreach ($entry->properties as $p) {
			if (! $p->isFKO) {
				$n0 = strtolower($p->dbcol);
				$n1 = $p->name;
				$baseClassName = "Application\\Model\\DbTable\\Row";
				if (is_array($row)) {
					$arow = $row;
				} else {
	 				if (substr(get_class($row), 0, strlen($baseClassName)) == $baseClassName) {
						$arow = $row->toArray();
					} else {
						$arow = $row;
					}
				}
				if (array_key_exists($n0, $arow)) {
					if (is_array($row)) {
						$ret = call_user_func_array(array($entry, "set" . $n1), array($row[$n0]));
					} else {
						$ret = call_user_func_array(array($entry, "set" . $n1), array($row->$n0));
					}
					//if ($ret === false) {
					//	$ret = call_user_func_array(array($entry, "set" . $n1), array($row->$n0));
					//} 
					if ($ret === false) {
						error_log("#no setter for $n0#");
					}
				} else {
					error_log("$n0 not in DB row object");
				}
				unset($arow);
			}
		}
	}

	public function find($id, &$value)
	{
		error_log("PRIMARY:" . var_export($this->getDbTable()->getPrimary()), true);
		if (! is_array($id)) {
			$ids = array();
			$ids[] = $id;
		} else {
			$ids = $id;
		}
		$select = $this->getDbTable()->getSql()->select();
		$from = '';
		$where = '';
		$orderby = '';
		$limit = '';
		getZendSelectParts($select, $from, $where, $orderby, $limit);
		$result = $this->getDbTable()->getAdapter()->query("SELECT * $from WHERE id = ?", array($ids))->toArray();
		if (0 == count($result)) {
			return;
		}		
		$row = $result->current();
		$this->populate($value,$row);	
	}

	public function count($filter = null)
	{
		$select = $this->getDbTable()->getSql()->select();
		$select->columns(array('COUNT(DISTINCT (id)) AS count'));
		if ( ($filter !== null) && ($filter->expr() != '') ) {
			$select->where($filter->expr());
		}
		$from = '';
		$where = '';
		$orderby = '';
		$limit = '';
		getZendSelectParts($select, $from, $where, $orderby, $limit);
		$res = $this->getDbTable()->getAdapter()->query("SELECT COUNT(DISTINCT (id)) AS count $from $where", array())->toArray();
		return $res[0]['count'];
	}
	
	public function fetchAll($filter = null, $format = '')
	{
		$select = $this->getDbTable()->getSql()->select();
		if ( ($filter !== null) && ($filter->expr() != '') ) {
			$select->where($filter->expr());
		}
		if ($filter !== null) {
			if (! is_null($filter->limit)) $select->limit($filter->limit);
			if (! is_null($filter->offset)) $select->offset($filter->offset);
			if (! is_null($filter->orderBy)) $select->order($filter->orderBy);
		}
		$resultSet = $this->getDbTable()->getAdapter()->query(SQL2STR($this, $select), array())->toArray();
		$entries = array();
		foreach ($resultSet as $row) {
			$type = "Application\\Model\\" . $this->_baseitemname;
			$entry = new $type;
			$this->populate($entry,$row);
			if ($format === 'xml') $entry = $entry->toXML(true);
			$entries[] = $entry;
		}		return $entries;
	}
}
