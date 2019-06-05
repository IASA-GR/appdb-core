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
class Default_Model_VaProvidersMapper extends Default_Model_VaProvidersMapperBase
{
	public function joins(&$select, $filter) {
		$select->joinLeft("va_providers.any", "va_providers.any.id = va_providers.id", array());
		if ( is_array($filter->joins) ) {
		}
	}

	public function fetchAll($filter = null, $format = '', $xmldetailed = false) {
		$select = $this->getDbTable()->select()->from('va_providers');
		$executor = $this->getDbTable();
		if ( $filter !== null ) {
			$orderby = $filter->orderBy;
			if ( is_array($orderby) ) {
				$orderby = end($orderby);
			}
		} else {
			$orderby = null;
		}
		if ($format == '') {
			return parent::fetchAll($filter);
		} else {
			if ($format === 'xml') {
				if ( $filter !== null ) {
					$select = $this->getDbTable()->getAdapter()->select()->distinct()->from('va_providers');
					if ( is_array($filter->expr()) || trim($filter->expr()) != '' ) {
						$this->joins($select, $filter);
						if ( ! is_array($filter->expr()) ) $select->where($filter->expr());
						$executor = $this->getDbTable()->getAdapter();
						$executor->setFetchMode(Zend_Db::FETCH_OBJ);
					}
				}
				$this->getDbTable()->getAdapter()->setFetchMode(Zend_Db::FETCH_OBJ);
        		if ($filter !== null) {
					$ord = $filter->orderBy;
//					if ( $ord == '' ) $ord = 'name ASC';
					$select->limit($filter->limit, $filter->offset);
					$select->order($ord);
				}

				if ( is_array($filter->expr()) ) {
					$where = array();
					$from = array();
					foreach($filter->expr() as $x) {
						$sss = clone $select;
						$sss->where($x);
						getZendSelectParts($sss, $f, $w, $orderby, $limit);
						$f = fixuZenduBuguru($f);
						$where[] = $w;
						$from[] = $f;
					}
				} else {
					getZendSelectParts($select, $from, $where, $orderby, $limit);
					$from = fixuZenduBuguru($from);
				}
				if ( $from == '' ) $from = 'FROM va_providers';
				
				if ( $xmldetailed === true ) {
					$func = "va_provider_to_xml_ext";
				} else {
					$func = "va_provider_to_xml";
				}
				db()->setFetchMode(Zend_Db::FETCH_OBJ);
				if ( is_array($filter->expr()) ) {
					noDBSeqScan(db());
					$resultSet = db()->query("SELECT ".$func."(array_agg(va_providers.id::text $orderby)) as va_provider FROM filtervaproviders((?)::text[],(?)::text[],(?)::text[]) AS va_providers INNER JOIN va_providers AS s ON s.id = va_providers.id $limit", array(php_to_pg_array($filter->fltstr, false), php_to_pg_array($from, false), str_replace("''", "\'", php_to_pg_array($where, false))))->fetchAll();
				} else {
					noDBSeqScan(db());
					$resultSet = db()->query("SELECT ".$func."(array_agg(va_providers.id::text $orderby)) as va_provider FROM filtervaproviders(?,?,?) AS va_providers INNER JOIN va_providers AS s ON s.id = va_providers.id $limit", array($filter->fltstr, $from, $where))->fetchAll();
				}
				$entries = array();
				foreach ($resultSet as $row) {
					$entry = $row->va_provider;
					$entries[] = $entry;
				}
				return $entries;
         	}
		}
	}
}
