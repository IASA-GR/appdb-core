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
class Default_Model_ResearchersMapper extends Default_Model_ResearchersMapperBase
{
	public $_nocache = false;

	public function save(Default_Model_Researcher $value){
        $value->lastUpdated = date('Y-m-d H:i:s');
        $ret = parent::save($value);
        return $ret;
	}
	
	public function joins(&$select, $filter) {
		$select->joinLeft('researchers.any','researchers.any.id = researchers.id', array());
		if ( is_array($filter->joins) ) {
			if ( (in_array("actor_group_members", $filter->joins)) || (in_array("actor_groups", $filter->joins)) ) {
				$select->joinLeft('actor_group_members', 'actor_group_members.actorid = researchers.guid', array());
				$select->joinLeft('actor_groups', 'actor_group_members.groupid = actor_groups.id', array());
			}
			if (in_array("positiontypes", $filter->joins)) {
				$select->joinLeft('positiontypes','positiontypes.id = researchers.positiontypeid', array());
				$select->joinLeft('positiontypes.any','positiontypes.any.id = positiontypes.id', array());
			}
			if ( ( (in_array("applications", $filter->joins)) || (in_array("vos", $filter->joins)) || (in_array("disciplines", $filter->joins)) || (in_array("middlewares", $filter->joins)) ) || (in_array("appcountries", $filter->joins)) || in_array("categories", $filter->joins) || in_array("archs", $filter->joins) || in_array("oses", $filter->joins) || in_array("proglangs", $filter->joins) || in_array("statuses", $filter->joins) || in_array("licenses", $filter->joins) || in_array("app_licenses", $filter->joins) ) {
				$select->joinLeft('researchers_apps', 'researchers_apps.researcherid = researchers.id', array());
				$select->joinLeft('applications', 'applications.id = researchers_apps.appid AND applications.deleted IS FALSE AND applications.moderated IS FALSE', array());
				$select->joinLeft('applications.any','applications.any.id = applications.id', array());
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
				$select->joinLeft('countries.any','countries.any.id = countries.id', array());
			}
			if (in_array("vos", $filter->joins)) {
				$select->joinLeft('vo_members', 'vo_members.researcherid = researchers.id');
				if (! in_array("vos", $filter->privateJoins)) {
					$select->joinLeft('app_vos', 'app_vos.appid = researchers_apps.appid AND app_vos.appid NOT IN (SELECT id FROM applications WHERE deleted IS TRUE OR moderated IS TRUE)', array());
					$select->joinLeft('vos', '(vos.id = vo_members.void OR vos.id = app_vos.void) AND vos.deleted IS FALSE', array());
				} else {
					$select->joinLeft('vos', 'vos.id = vo_members.void AND vos.deleted IS FALSE', array());
				}
				$select->joinLeft('vos.any', 'vos.any.id = vos.id', array());
			}
			if (in_array("disciplines", $filter->joins)) {
				$select->joinLeft('disciplines', 'disciplines.id = ANY(applications.disciplineid)', array());
				$select->joinLeft('disciplines.any', 'disciplines.any.id = disciplines.id', array());
			}
			if (in_array("middlewares", $filter->joins)) {
				$select->joinLeft('app_middlewares','applications.id = app_middlewares.appid', array());
				$select->joinLeft('app_middlewares.any','app_middlewares.any.id = app_middlewares.id', array());
				$select->joinLeft('middlewares','middlewares.id = app_middlewares.middlewareid', array());
				$select->joinLeft('middlewares.any','middlewares.any.id = middlewares.id', array());
            }
            if (in_array("contacts", $filter->joins)) {
                $select->joinLeft('contacts','contacts.researcherid = researchers.id AND contacts.contacttypeid=7', array());
                $select->joinLeft('contacts.any','contacts.any.id = contacts.id', array());
            }
			if (in_array("categories", $filter->joins)) {
				$select->joinLeft("categories","categories.id = ANY(applications.categoryid)",array());
				$select->joinLeft("categories.any.any","categories.any.id = categories.id",array());
			}
			if ( in_array("oses", $filter->joins) ) {
				$select->joinLeft('app_oses', 'app_oses.appid = applications.id', array());
				$select->joinLeft('oses', 'app_oses.osid = oses.id', array());
				$select->joinLeft('oses.any', 'oses.any.id = oses.id', array());
			}
			if ( in_array("proglangs", $filter->joins) ) {
				$select->joinLeft('appproglangs', 'appproglangs.appid = applications.id', array());
				$select->joinLeft('proglangs', 'appproglangs.proglangid = proglangs.id', array());
				$select->joinLeft('proglangs.any', 'proglangs.any.id = proglangs.id', array());
			}
			if ( in_array("archs", $filter->joins) ) {
				$select->joinLeft('app_archs', 'app_archs.appid = applications.id', array());
				$select->joinLeft('archs', 'app_archs.archid = archs.id', array());
				$select->joinLeft('archs.any', 'archs.any.id = archs.id', array());
			}
			if ( in_array("statuses", $filter->joins) ) {
				$select->joinLeft('statuses', 'statuses.id = applications.statusid', array());
				$select->joinLeft('statuses.any', 'statuses.any.id = applications.statusid', array());
			}
			if ( in_array("licenses", $filter->joins) || (in_array("app_licenses", $filter->joins)) ) {
				$select->joinLeft('app_licenses', 'app_licenses.appid = applications.id', array());
				$select->joinLeft('licenses', 'app_licenses.licenseid = licenses.id', array());
				$select->joinLeft('licenses.any', 'licenses.any.id = licenses.id', array());
				$select->joinLeft('app_licenses.any', 'app_licenses.any.id = app_licenses.id', array());
			}
//			if (in_array("permissions", $filter->joins)) $select->joinLeft('permissions','permissions.object = researchers.guid', array());
		}
	}

	public function count($filter = null)
	{

		$select = $this->getDbTable()->select();
		if ( (($filter !== null) && ($filter->expr() != '')) ) {
			$select = $this->getDbTable()->getAdapter()->select()->distinct()->from('researchers');
			$this->joins($select, $filter);
			if ( is_array($filter->expr()) ) {
				$where = array();
				$from = array();
				foreach($filter->expr() as $ex) {
					if ( $ex != '' ) {
						$sss = clone $select;
						$sss->where($ex);
						getZendSelectParts($sss, $f, $w, $orderby, $limit);
						if ( $f == '' ) $f= 'FROM researchers';
						$f = fixuZenduBuguru($f);
						$where[] = $w;
						$from[] = $f;
					}
				}
			} else {
				$select->where($filter->expr());
				getZendSelectParts($select, $from, $where, $orderby, $limit);
				if ( $from == '' ) $from = 'FROM researchers';
				$from = fixuZenduBuguru($from);
			}
		} else {
			$from = 'FROM researchers';
			$where = '';
		}
		$this->getDbTable()->getAdapter()->setFetchMode(Zend_Db::FETCH_OBJ);
		noDBSeqScan(db());

		if ( is_array($filter->expr()) ) {
			$res = db()->query("SELECT COUNT(DISTINCT id) FROM filterppl((?)::text[], (?)::text[], (?)::text[])", array(php_to_pg_array($filter->fltstr, false), php_to_pg_array($from, false), str_replace("''", "\'", php_to_pg_array($where, false))))->fetchAll();
		} else {
			if ( ! $this->_nocache ) {
				$res = db()->query("SELECT COUNT(DISTINCT id) FROM filterppl(?,?,?)", array($filter->fltstr, $from, $where))->fetchAll();
			} else {
				$res = db()->query("SELECT COUNT(DISTINCT id) $from $where");
			}
		}
		return $res[0]->count;
	}

	public function fetchAll($filter = null, $format = '', $userid = '', $xmldetailed = false)
	{
		$select = $this->getDbTable()->select();
		$executor = $this->getDbTable();
		if ( $filter !== null ) {
			$orderby = $filter->orderBy;
			if ( is_array($orderby) ) {
				$orderby = end($orderby);
			}
		} else {
			$orderby = null;
		}
		if ( (($filter !== null) && ($filter->expr() != '')) ) {
			$select = $this->getDbTable()->getAdapter()->select()->distinct()->from('researchers');
			$this->joins($select, $filter);
			if ( ! is_array($filter->expr()) ) $select->where($filter->expr());
			$executor = $this->getDbTable()->getAdapter();
			$executor->setFetchMode(Zend_Db::FETCH_OBJ);
		}
		if ($filter !== null) $select->limit($filter->limit, $filter->offset);
		if ($filter !== null) {
			$inv = false;
			if ( (substr($orderby,0,16) === "researchers.rank") ) {
//				$orderby = substr($orderby,12);
//				if ( strpos($_orderby," DESC") === false ) {
//					$orderby = str_replace(" ASC"," DESC", $orderby);
//				} else {
//					$orderby = str_replace(" DESC"," ASC", $orderby);
//					$inv = true;
//				}
//				$orderby = array($orderby, 'name'.($inv?' DESC':''));
				/** 
				 * NOTE: Ordering by rank is handled by the database, so trust DB order
				 */
				$orderby = "rank DESC, name ASC";
			}
			$select->order($orderby);
			if ( is_array($orderby) ) {
				$_orderby = implode(",", $orderby);
			} else $_orderby = $orderby;
		}
		if (str_replace('export', '', $format) === 'xml' || str_replace('export', '', $format) === 'csv') {
			$this->getDbTable()->getAdapter()->setFetchMode(Zend_Db::FETCH_OBJ);
			if ( $userid != '' ) $userid = ", $userid"; else $userid = '';
			$simpleList = ", ''";
			if ( $xmldetailed === "-1" ) {
				$xmldetailed = false;
				$simpleList = ", 'simpleindex'";
            }
            if ( $format === 'xml' ) {
                $func = "researcher_to_xml"; 
            } else {
                $func = "export_researcher";
                $simpleList = ',\'' . str_replace('export', '', $format). '\'';
            }
			if ( $xmldetailed ) $func = $func . "_ext";

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
			if ( isset($_orderby) ) $orderby = 'ORDER BY ' . $_orderby;	# TODO: FIX NULLS FIRST/LAST bypass

			if ( $from == '' ) $from = 'FROM researchers';
			if ( $func == "researcher_to_xml" ) {
//				debug_log(var_export($filter->fltstr,true));
//				debug_log(var_export($from, true));
//				debug_log(var_export($where,true));
//				debug_log($orderby);
				if ( is_array($filter->expr()) ) {
					noDBSeqScan(db());
					$resultSet = db()->query("SELECT ".$func."(array_agg(id $orderby)) as researcher FROM filterppl((?)::text[],(?)::text[],(?)::text[]) AS researchers $limit", array(php_to_pg_array($filter->fltstr, false), php_to_pg_array($from, false), str_replace("''", "\'", php_to_pg_array($where, false))))->fetchAll();
				} else {
					noDBSeqScan(db());
					$resultSet = db()->query("SELECT ".$func."(array_agg(id $orderby)".$simpleList.$userid.") AS researcher FROM filterppl(?,?,?) AS researchers $limit", array($filter->fltstr, $from, $where))->fetchAll();
				}
			} else {
				$select = fixuZenduBuguru("" . $select);
				noDBSeqScan($this->getDbTable()->getAdapter());
		        $resultSet = $this->getDbTable()->getAdapter()->query("SELECT ".$func."(id".$simpleList.$userid.") as researcher FROM (".$select.") AS t;")->fetchAll();
			}
		} else {
			$select = fixuZenduBuguru("" . $select);
			noDBSeqScan($executor);
			$resultSet = $executor->fetchAll($select);
		}
		$entries = array();
		foreach ($resultSet as $row) {
			if ( str_replace('export', '', $format) === 'xml' || str_replace('export', '', $format) === 'csv' ) {
				$entry = $row->researcher;
			} else {
				$entry = new Default_Model_Researcher();
				$this->populate($entry,$row);
			}
			$entries[] = $entry;
		}		return $entries;
	}

}
