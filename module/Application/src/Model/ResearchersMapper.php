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

class ResearchersMapper extends ResearchersMapperBase
{
	public $_nocache = false;

	public function save(AROItem $value){
        $value->lastUpdated = date('Y-m-d H:i:s');
        $ret = parent::save($value);
        return $ret;
	}
	
	public function joins(&$select, $filter) {
		$select->join('researchers.any','researchers.any.id = researchers.id', array(), 'left');
		if ( is_array($filter->joins) ) {
			if ( (in_array("actor_group_members", $filter->joins)) || (in_array("actor_groups", $filter->joins)) ) {
				$select->join('actor_group_members', 'actor_group_members.actorid = researchers.guid', array(), 'left');
				$select->join('actor_groups', 'actor_group_members.groupid = actor_groups.id', array(), 'left');
			}
			if (in_array("positiontypes", $filter->joins)) {
				$select->join('positiontypes','positiontypes.id = researchers.positiontypeid', array(), 'left');
				$select->join('positiontypes.any','positiontypes.any.id = positiontypes.id', array(), 'left');
			}
			if ( ( (in_array("applications", $filter->joins)) /*|| (in_array("vos", $filter->joins))*/ || (in_array("disciplines", $filter->joins)) || (in_array("middlewares", $filter->joins)) ) || (in_array("appcountries", $filter->joins)) || in_array("categories", $filter->joins) || in_array("archs", $filter->joins) || in_array("oses", $filter->joins) || in_array("proglangs", $filter->joins) || in_array("statuses", $filter->joins) || in_array("licenses", $filter->joins) || in_array("app_licenses", $filter->joins) ) {
				$select->join('researchers_apps', 'researchers_apps.researcherid = researchers.id', array(), 'left');
				$select->join('applications', 'applications.id = researchers_apps.appid AND applications.deleted IS FALSE AND applications.moderated IS FALSE', array(), 'left');
				$select->join('applications.any','applications.any.id = applications.id', array(), 'left');
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
				$select->join('countries.any','countries.any.id = countries.id', array(), 'left');
			}
/*			if (in_array("vos", $filter->joins)) {
				$select->join('vo_members', 'vo_members.researcherid = researchers.id', array(), 'left');
				if (! in_array("vos", $filter->privateJoins)) {
					$select->join('app_vos', 'app_vos.appid = researchers_apps.appid AND app_vos.appid NOT IN (SELECT id FROM applications WHERE deleted IS TRUE OR moderated IS TRUE)', array(), 'left');
					$select->join('vos', '(vos.id = vo_members.void OR vos.id = app_vos.void) AND vos.deleted IS FALSE', array(), 'left');
				} else {
					$select->join('vos', 'vos.id = vo_members.void AND vos.deleted IS FALSE', array(), 'left');
				}
				$select->join('vos.any', 'vos.any.id = vos.id', array(), 'left');
			} */
			if (in_array("disciplines", $filter->joins)) {
				$select->join('disciplines', 'disciplines.id = ANY(applications.disciplineid)', array(), 'left');
				$select->join('disciplines.any', 'disciplines.any.id = disciplines.id', array(), 'left');
			}
			if (in_array("middlewares", $filter->joins)) {
				$select->join('app_middlewares','applications.id = app_middlewares.appid', array(), 'left');
				$select->join('app_middlewares.any','app_middlewares.any.id = app_middlewares.id', array(), 'left');
				$select->join('middlewares','middlewares.id = app_middlewares.middlewareid', array(), 'left');
				$select->join('middlewares.any','middlewares.any.id = middlewares.id', array(), 'left');
            }
/*            if (in_array("contacts", $filter->joins)) {
                $select->join('contacts','contacts.researcherid = researchers.id AND contacts.contacttypeid=7', array(), 'left');
                $select->join('contacts.any','contacts.any.id = contacts.id', array(), 'left');
			} */
			if (in_array("categories", $filter->joins)) {
				$select->join("categories","categories.id = ANY(applications.categoryid)",array(), 'left');
				$select->join("categories.any.any","categories.any.id = categories.id",array(), 'left');
			}
			if ( in_array("oses", $filter->joins) ) {
				$select->join('app_oses', 'app_oses.appid = applications.id', array(), 'left');
				$select->join('oses', 'app_oses.osid = oses.id', array(), 'left');
				$select->join('oses.any', 'oses.any.id = oses.id', array(), 'left');
			}
			if ( in_array("proglangs", $filter->joins) ) {
				$select->join('appproglangs', 'appproglangs.appid = applications.id', array(), 'left');
				$select->join('proglangs', 'appproglangs.proglangid = proglangs.id', array(), 'left');
				$select->join('proglangs.any', 'proglangs.any.id = proglangs.id', array(), 'left');
			}
			if ( in_array("archs", $filter->joins) ) {
				$select->join('app_archs', 'app_archs.appid = applications.id', array(), 'left');
				$select->join('archs', 'app_archs.archid = archs.id', array(), 'left');
				$select->join('archs.any', 'archs.any.id = archs.id', array(), 'left');
			}
			if ( in_array("statuses", $filter->joins) ) {
				$select->join('statuses', 'statuses.id = applications.statusid', array(), 'left');
				$select->join('statuses.any', 'statuses.any.id = applications.statusid', array(), 'left');
			}
			if ( in_array("licenses", $filter->joins) || (in_array("app_licenses", $filter->joins)) ) {
				$select->join('app_licenses', 'app_licenses.appid = applications.id', array(), 'left');
				$select->join('licenses', 'app_licenses.licenseid = licenses.id', array(), 'left');
				$select->join('licenses.any', 'licenses.any.id = licenses.id', array(), 'left');
				$select->join('app_licenses.any', 'app_licenses.any.id = app_licenses.id', array(), 'left');
			}
//			if (in_array("permissions", $filter->joins)) $select->join('permissions','permissions.object = researchers.guid', array(), 'left');
		}
	}

	public function count($filter = null)
	{

		$select = $this->getDbTable()->getSql()->select();
		if ( (($filter !== null) && ($filter->expr() != '')) ) {
			$select->quantifier('DISTINCT');
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
		$where = str_replace("CAST(vos.any.any AS TEXT)", "CAST('' AS TEXT)", $where);
		$where = str_replace("CAST(contacts.any.any AS TEXT)", "CAST('' AS TEXT)", $where);

		if ( is_array($filter->expr()) ) {
			$res = db()->query("SELECT COUNT(DISTINCT id) FROM filterppl((?)::text[], (?)::text[], (?)::text[])", array(php_to_pg_array($filter->fltstr, false), php_to_pg_array($from, false), str_replace("''", "\'", php_to_pg_array($where, false))))->toArray();
		} else {
			if ( ! $this->_nocache ) {
				$res = db()->query("SELECT COUNT(DISTINCT id) FROM filterppl(?,?,?)", array($filter->fltstr, $from, $where))->toArray();
			} else {
				$res = db()->query("SELECT COUNT(DISTINCT id) $from $where");
			}
		}
		return $res[0]['count'];
	}

	public function fetchAll($filter = null, $format = '', $userid = '', $xmldetailed = false)
	{
		$orderby = null;
		$select = $this->getDbTable()->getSql()->select();
		$executor = $this->getDbTable();
		if ( $filter !== null ) {
			if (! is_null($filter->orderBy)) $orderby = $filter->orderBy;
			if (is_array($orderby)) {
				$orderby = end($orderby);
			}
		} else {
			$orderby = null;
		}
		if ( (($filter !== null) && ($filter->expr() != '')) ) {
			$select->quantifier('DISTINCT');
			$this->joins($select, $filter);
			if ( ! is_array($filter->expr()) ) $select->where($filter->expr());
			$executor = $this->getDbTable()->getAdapter();
		}
		if ($filter !== null) {
			if (! is_null($filter->limit)) $select->limit($filter->limit);
			if (! is_null($filter->offset)) $select->offset($filter->offset);
		}
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
			if (! is_null($orderby)) $select->order($orderby);
			if ( is_array($orderby) ) {
				$_orderby = implode(",", $orderby);
			} else $_orderby = $orderby;
		}
		if (str_replace('export', '', $format) === 'xml' || str_replace('export', '', $format) === 'csv') {
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
					$w = str_replace("CAST(vos.any.any AS TEXT)", "CAST('' AS TEXT)", $w);
					$w = str_replace("CAST(contacts.any.any AS TEXT)", "CAST('' AS TEXT)", $w);
					$where[] = $w;					
					$from[] = $f;
				}
			} else {
				getZendSelectParts($select, $from, $where, $orderby, $limit);
				$where = str_replace("CAST(vos.any.any AS TEXT)", "CAST('' AS TEXT)", $where);
				$where = str_replace("CAST(contacts.any.any AS TEXT)", "CAST('' AS TEXT)", $where);
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
					$resultSet = db()->query("SELECT ".$func."(array_agg(id $orderby)) as researcher FROM filterppl((?)::text[],(?)::text[],(?)::text[]) AS researchers $limit", array(php_to_pg_array($filter->fltstr, false), php_to_pg_array($from, false), str_replace("''", "\'", php_to_pg_array($where, false))))->toArray();
				} else {
					$resultSet = db()->query("SELECT ".$func."(array_agg(id $orderby)".$simpleList.$userid.") AS researcher FROM filterppl(?,?,?) AS researchers $limit", array($filter->fltstr, $from, $where))->toArray();
				}
			} else {
				$select = (new \Zend\Db\Sql\Sql($this->getDbTable()->getAdapter()))->getSqlStringForSqlObject($select);
				$select = fixuZenduBuguru("" . $select);
				$select = str_replace('"researchers"."researchers".', '"researchers".', $select);
				$select = str_replace('"researchers.any"', '"researchers"."any"', $select);
		        $resultSet = db()->query("SELECT ".$func."(id".$simpleList.$userid.") as researcher FROM (".$select.") AS t;", array())->toArray();
			}
		} else {
			$select = (new \Zend\Db\Sql\Sql($this->getDbTable()->getAdapter()))->getSqlStringForSqlObject($select);
			$select = fixuZenduBuguru("" . $select);
			$select = str_replace('"researchers"."researchers".', '"researchers".', $select);
			$select = str_replace('"researchers.any"', '"researchers"."any"', $select);
			$resultSet = db()->query($select, array())->toArray();
		}
		$entries = array();
		foreach ($resultSet as $row) {
			if ( str_replace('export', '', $format) === 'xml' || str_replace('export', '', $format) === 'csv' ) {
				$entry = $row['researcher'];
			} else {
				$entry = new Researcher();
				$this->populate($entry,$row);
			}
			$entries[] = $entry;
		}		return $entries;
	}

}
