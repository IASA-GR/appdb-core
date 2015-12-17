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
class Default_Model_AppContactMiddlewaresMapper extends Default_Model_AppContactMiddlewaresMapperBase
{
	private function joins(&$select, $filter) {
		if ( is_array($filter->joins) ) {
			if ( in_array("middlewares",$filter->joins) ) {
				$select->joinLeft('app_middlewares', 'appmiddlewareid = app_middlewares.id', array());
				$select->joinLeft('middlewares','middlewares.id = app_middlewares.middlewareid', array());
            }
        }
    }

	public function fetchAll($filter = null)
	{
		$select = $this->getDbTable()->select();
		$executor = $this->getDbTable();
		if ( (($filter !== null) && ($filter->expr() != '')) ) {
			$select = $this->getDbTable()->getAdapter()->select()->distinct()->from('appcontact_middlewares');
			if ( $filter !== null ) {
				if ($filter->expr() != '') {
					$this->joins($select, $filter);
					$select->where($filter->expr());
					$executor = $this->getDbTable()->getAdapter();
					$executor->setFetchMode(Zend_Db::FETCH_OBJ);
				}
			}
		}
		if ($filter !== null) $select->limit($filter->limit, $filter->offset);
		if ($filter !== null) {
			$orderby = $filter->orderBy;
			$select->order($orderby);
		}
        $resultSet = $executor->fetchAll($select);
		$entries = array();
		foreach ($resultSet as $row) {
            $entry = new Default_Model_AppContactMiddleware();
            $this->populate($entry,$row);
			$entries[] = $entry;
        }
        return $entries;
	}

}
