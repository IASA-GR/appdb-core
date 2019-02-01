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

class DisseminationMapper extends DisseminationMapperBase
{
	public function populate(&$entry, $row) {
		parent::populate($entry,$row);
		$entry->setRecipients(pg_to_php_array($row->recipients));
	}

	public function save(AROItem $value) {
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
                $select->join('researchers', 'researchers.id = dissemination.composerid AND researchers.deleted IS FALSE', array(), 'left');
            }
			if (in_array("positiontypes", $filter->joins)) $select->join('positiontypes','positiontypes.id = researchers.positiontypeid', array(), 'left');
			if ( ( (in_array("applications", $filter->joins)) || (in_array("vos", $filter->joins)) || (in_array("disciplines", $filter->joins)) || (in_array("middlewares", $filter->joins)) ) || (in_array("appcountries", $filter->joins)) || in_array("categories", $filter->joins) ) {
				$select->join('researchers_apps', 'researchers_apps.researcherid = researchers.id', array(), 'left');
				$select->join('applications', 'applications.id = researchers_apps.appid AND applications.deleted IS FALSE AND applications.moderated IS FALSE', array(), 'left');
			}
			if (in_array("countries", $filter->joins)) {
				// HACK: do not include application country in country context by default
				// unless it has been explicitly specified by using a special property (application.countryname)
				if (in_array("appcountries", $filter->joins)) {
					$select->join('appcountries','applications.id = appcountries.appid', array(), 'left');
					$select->join('countries','countries.id = researchers.countryid OR countries.id = appcountries.id', array(), 'left');
				} else {
					$select->join('countries','countries.id = researchers.countryid', array(), 'left');
				}
			}
			if (in_array("vos", $filter->joins)) {
				$select->join('app_vos', 'app_vos.appid = researchers_apps.appid AND app_vos.appid NOT IN (SELECT id FROM applications WHERE deleted IS TRUE OR moderated IS TRUE)', array(), 'left');
				$select->join('vos', 'vos.id = app_vos.void AND vos.deleted IS FALSE', array(), 'left');
			}
			if (in_array("disciplines", $filter->joins)) {
				$select->join('disciplines', 'disciplines.id = ANY(applications.disciplineid)', array(), 'left');
			}
			if (in_array("middlewares", $filter->joins)) {
				$select->join('app_middlewares','applications.id = app_middlewares.appid', array(), 'left');
				$select->join('middlewares','middlewares.id = app_middlewares.middlewareid', array(), 'left');
            }
            if (in_array("contacts", $filter->joins)) {
                $select->join('contacts','contacts.researcherid = researchers.id AND contacts.contacttypeid=7', array(), 'left');
            }
			if (in_array("categories", $filter->joins)) $select->join("categories","categories.id = ANY(applications.categoryid)",array(), 'left');
        }
    }

	public function count($filter = null)
    {
		$select = $this->getDbTable()->getSql()->select();
		$executor = $this->getDbTable();
		$select->from($this->getDbTable(),array('COUNT(DISTINCT (dissemination.id)) AS count'));
		if ( ($filter !== null) && ($filter->expr() != '') ) {
			$select = $this->getDbTable()->getAdapter()->select()->distinct()->from('dissemination',array('COUNT(DISTINCT (dissemination.id)) AS count'));
			$this->joins($select, $filter);
			$select->where($filter->expr());
			$executor = $this->getDbTable()->getAdapter();
        }
        //debug_log("".$select);
		$res = $executor->fetchAll($select);
		return $res[0]->count;
	}

	public function fetchAll($filter = null, $format = '')
	{
		$select = $this->getDbTable()->getSql()->select();
		$executor = $this->getDbTable();
		if ( (($filter !== null) && ($filter->expr() != '')) ) {
			$select = $this->getDbTable()->getAdapter()->select()->distinct()->from('dissemination');
			if ( $filter !== null ) {
				if ($filter->expr() != '') {
					$this->joins($select, $filter);
					$select->where($filter->expr());
					$executor = $this->getDbTable()->getAdapter();
				}
			}
		}
		if (! is_null($filter)) {
	if (! is_null($filter->limit)) $select->limit($filter->limit);
	if (! is_null($filter->offset)) $select->offset($filter->offset);
}
		if ($filter !== null) {
			$orderby = $filter->orderBy;
			$select->order($orderby);
		}
		//debug_log("".$select);
		if ($format === 'xml') {
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
				$entry = new DisseminationEntry();
				$this->populate($entry,$row);
			}
			$entries[] = $entry;
		}
		return $entries;
	}
}
