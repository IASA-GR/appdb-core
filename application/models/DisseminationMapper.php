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
class Default_Model_DisseminationMapper extends Default_Model_DisseminationMapperBase
{
	public function populate(&$entry, $row) {
		parent::populate($entry,$row);
		$entry->setRecipients(pg_to_php_array($row->recipients));
	}

	public function save(Default_Model_DisseminationEntry $value) {
		$rec = $value->recipients;
		$value->recipients = php_to_pg_array($value->recipients, true);
		parent::save($value);
		$value->recipients = $rec;
    }

	private function joins(&$select, $filter) {
        if ( is_array($filter->joins) ) {
            if (in_array("researchers", $filter->joins) ||
                in_array("positiontypes", $filter->join) ||
                in_array("applications", $filter->join) ||
                in_array("vos", $filter->join) ||
                in_array("disciplines", $filter->join) ||
                in_array("appcountries", $filter->join) ||
                in_array("middlewares", $filter->join) ||
                in_array("contacts", $filter->join) ||
                in_array("categories", $filter->join)
            ) {
                $select->joinLeft('researchers', 'researchers.id = dissemination.composerid AND researchers.deleted IS FALSE', array());
            }
			if (in_array("positiontypes", $filter->joins)) $select->joinLeft('positiontypes','positiontypes.id = researchers.positiontypeid', array());
			if ( ( (in_array("applications", $filter->joins)) || (in_array("vos", $filter->joins)) || (in_array("disciplines", $filter->joins)) || (in_array("middlewares", $filter->joins)) ) || (in_array("appcountries", $filter->joins)) || in_array("categories", $filter->joins) ) {
				$select->joinLeft('researchers_apps', 'researchers_apps.researcherid = researchers.id', array());
				$select->joinLeft('applications', 'applications.id = researchers_apps.appid AND applications.deleted IS FALSE AND applications.moderated IS FALSE', array());
			}
			if (in_array("countries", $filter->joins)) {
				// HACK: do not include application country in country context by default
				// unless it has been explicitly specified by using a special property (application.countryname)
				if (in_array("appcountries", $filter->joins)) {
					$select->joinLeft('appcountries','applications.id = appcountries.appid', array());
					$select->joinLeft('countries','countries.id = researchers.countryid OR countries.id = appcountries.id', array());
				} else {
					$select->joinLeft('countries','countries.id = researchers.countryid', array());
				}
			}
			if (in_array("vos", $filter->joins)) {
				$select->joinLeft('app_vos', 'app_vos.appid = researchers_apps.appid AND app_vos.appid NOT IN (SELECT id FROM applications WHERE deleted IS TRUE OR moderated IS TRUE)', array());
				$select->joinLeft('vos', 'vos.id = app_vos.void AND vos.deleted IS FALSE', array());
			}
			if (in_array("disciplines", $filter->joins)) {
				$select->joinLeft('disciplines', 'disciplines.id = ANY(applications.disciplineid)', array());
			}
			if (in_array("middlewares", $filter->joins)) {
				$select->joinLeft('app_middlewares','applications.id = app_middlewares.appid', array());
				$select->joinLeft('middlewares','middlewares.id = app_middlewares.middlewareid', array());
            }
            if (in_array("contacts", $filter->joins)) {
                $select->joinLeft('contacts','contacts.researcherid = researchers.id AND contacts.contacttypeid=7', array());
            }
			if (in_array("categories", $filter->joins)) $select->joinLeft("categories","categories.id = ANY(applications.categoryid)",array());
        }
    }

	public function count($filter = null)
    {
		$select = $this->getDbTable()->select();
		$executor = $this->getDbTable();
		$select->from($this->getDbTable(),array('COUNT(DISTINCT (dissemination.id)) AS count'));
		if ( ($filter !== null) && ($filter->expr() != '') ) {
			$select = $this->getDbTable()->getAdapter()->select()->distinct()->from('dissemination',array('COUNT(DISTINCT (dissemination.id)) AS count'));
			$this->joins($select, $filter);
			$select->where($filter->expr());
			$executor = $this->getDbTable()->getAdapter();
			$executor->setFetchMode(Zend_Db::FETCH_OBJ);
        }
        debug_log("".$select);
		$res = $executor->fetchAll($select);
		return $res[0]->count;
	}

	public function fetchAll($filter = null, $format = '')
	{
		$select = $this->getDbTable()->select();
		$executor = $this->getDbTable();
		if ( (($filter !== null) && ($filter->expr() != '')) ) {
			$select = $this->getDbTable()->getAdapter()->select()->distinct()->from('dissemination');
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
		debug_log("".$select);
		if ($format === 'xml') {
			$this->getDbTable()->getAdapter()->setFetchMode(Zend_Db::FETCH_OBJ);
			if ( ($this->count($filter) == 1) ) {
				$resultSet = $this->getDbTable()->getAdapter()->query("SELECT dissemination_to_xml_ext(id) as data FROM (".$select.") AS T;")->fetchAll();
			} else {
				$resultSet = $this->getDbTable()->getAdapter()->query("SELECT dissemination_to_xml(id) as data FROM (".$select.") AS T;")->fetchAll();
			}
		} else $resultSet = $executor->fetchAll($select);
		$entries = array();
		foreach ($resultSet as $row) {
			if ( $format === 'xml' ) {
				$entry = $row->data;
			} else {
				$entry = new Default_Model_DisseminationEntry();
				$this->populate($entry,$row);
			}
			$entries[] = $entry;
		}
		return $entries;
	}
}
