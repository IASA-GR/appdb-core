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

class VOsMapper extends VOsMapperBase
{
	public function joins(&$select, $filter) {
		$select->joinLeft('vos.any.any','vos.any.id = vos.id', array());
		if ( is_array($filter->joins) ) {
			if (in_array("vo_contacts", $filter->joins) ) {
				$select->joinLeft('vo_contacts', 'vos.id = vo_contacts.void');
			}
			if (in_array("vo_members", $filter->joins) ) {
				$select->joinLeft('vo_members', 'vos.id = vo_members.void');
			}
			if (in_array("domains", $filter->joins) ) {
				$select->joinLeft('domains', 'domains.id = vos.domainid');
			}
			if (in_array("applications", $filter->joins) || 
				in_array("licenses", $filter->joins) || 
				(in_array("disciplines", $filter->joins) && (! in_array("disciplines", $filter->privateJoins))) || 
				in_array("countries", $filter->joins) ||
				in_array("appcountries", $filter->joins) ||
				in_array("middlewares", $filter->joins) ||
				//in_array("contacts", $filter->joins) || 
				in_array("positiontypes", $filter->joins) ||
				in_array("categories", $filter->joins) ||
				in_array("archs", $filter->joins) ||
				in_array("oses", $filter->joins) ||
				in_array("proglangs", $filter->joins) ||
				in_array("statuses", $filter->joins)/* ||
				in_array("researchers", $filter->joins)*/) {
				$select->joinLeft('app_vos', 'app_vos.void = vos.id', array());
				$select->joinLeft('applications','applications.id = app_vos.appid AND applications.deleted IS FALSE AND applications.moderated IS FALSE', array());
				$select->joinLeft('applications.any','applications.any.id = applications.id', array());
			}
			if (in_array("disciplines", $filter->joins)) {
				if ( in_array("disciplines", $filter->privateJoins) ) {
					$select->joinLeft('disciplines','disciplines.id = ANY(vos.disciplineid)', array());
					$select->joinLeft('disciplines.any','disciplines.any.id = disciplines.id', array());
				} else {
					$select->joinLeft('disciplines','disciplines.id = ANY(vos.disciplineid) OR disciplines.id = ANY(applications.disciplineid)', array());
					$select->joinLeft('disciplines.any','disciplines.any.id = disciplines.id', array());
				}
			}
			if (in_array("countries", $filter->joins) || in_array("appcountries", $filter->joins) ) {
				$select->joinLeft('appcountries','applications.id = appcountries.appid', array());
				$select->joinLeft('countries','countries.id = appcountries.id', array());
				$select->joinLeft('countries.any','countries.any.id = countries.id', array());
			}
			if (in_array("middlewares", $filter->joins)) {
				if ( in_array("middlewares", $filter->privateJoins) ) {
					$select->joinLeft('vo_middlewares','vos.id = vo_middlewares.void', array());
					$select->joinLeft('middlewares','middlewares.id = vo_middlewares.middlewareid', array());
				} else {
					$select->joinLeft('app_middlewares','applications.id = app_middlewares.appid', array());
					$select->joinLeft('app_middlewares.any','app_middlewares.any.id = app_middlewares.id', array());
					$select->joinLeft('vo_middlewares','vos.id = vo_middlewares.void', array());
					$select->joinLeft('middlewares','middlewares.id = app_middlewares.middlewareid OR middlewares.id = vo_middlewares.middlewareid', array());
				}
				$select->joinLeft('middlewares.any','middlewares.any.id = middlewares.id', array());
			}
/*			if (in_array("researchers", $filter->joins) || in_array("contacts", $filter->joins) || in_array("positiontypes", $filter->joins)) {
				$select->joinLeft('researchers_apps','researchers_apps.appid = applications.id', array());
				$select->joinLeft('researchers','researchers.id = researchers_apps.researcherid AND researchers.deleted IS FALSE', array());
				$select->joinLeft('researchers.any','researchers.any.id = researchers.id', array());
			} */
			if (in_array("categories", $filter->joins)) {
				$select->joinLeft('categories','categories.id = ANY(applications.categoryid)', array());
				$select->joinLeft('categories.any','categories.any.id = categories.id', array());
			}
/*			if (in_array("contacts", $filter->joins)) {
				$select->joinLeft('contacts','researchers.id = contacts.researcherid', array());
				$select->joinLeft('contacts.any', 'contacts.any.id = contacts.id', array());
			} */
/*			if (in_array("positiontypes", $filter->joins)) {
				$select->joinLeft('positiontypes','researchers.positiontypeid = positiontypes.id', array());
				$select->joinLeft('positiontypes.any','positiontypes.any.id = positiontypes.id', array());
			}*/
			if ( in_array("oses", $filter->joins) ) {
				$select->joinLeft('app_oses', 'app_oses.appid = applications.id', array());
				$select->joinLeft('oses', 'app_oses.osid = oses.id', array());
				$select->joinLeft('oses.any', 'oses.any.id = oses.id', array());
			}
			if ( in_array("licenses", $filter->joins) ) {
				$select->joinLeft('app_licenses', 'app_licenses.appid = applications.id', array());
				$select->joinLeft('licenses', 'app_licenses.licenseid = licenses.id', array());
				$select->joinLeft('licenses.any', 'licenses.any.id = licenses.id', array());
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

		}
	}

	public function count($filter = null)
	{
/*		$flt = clone $filter;
		$flt->limit = 0;
		debug_log("HERE HERE HERE: " . var_export($flt->expr(), true));
		return count($this->fetchAll($flt, $format));
 */
		$select = $this->getDbTable()->select();
		if ( (($filter !== null) && ($filter->expr() != '')) ) {
			$select = $this->getDbTable()->getAdapter()->select()->distinct()->from('vos');
			$this->joins($select, $filter);
			if (! is_array($filter->expr())) $select->where($filter->expr());
		} else {
			$from = 'FROM vos';
			$where = '';
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

		db()->setFetchMode(Zend_Db::FETCH_OBJ); 
		if ( is_array($filter->expr()) ) {
			noDBSeqScan(db());
			$resultSet = db()->query("SELECT COUNT(DISTINCT vos.id) FROM filtervos((?)::text[],(?)::text[],(?)::text[]) AS vos", array(php_to_pg_array($filter->fltstr, false), php_to_pg_array($from, false), str_replace("''", "\'", php_to_pg_array($where, false))))->fetchAll();
		} else {
			noDBSeqScan(db());
			$resultSet = db()->query("SELECT COUNT(DISTINCT vos.id) FROM filtervos(?,?,?) AS vos", array($filter->fltstr, $from, $where))->fetchAll();
		}
		return $resultSet[0]->count;
	}
	
	public function fetchAll($filter = null, $format = '', $xmldetailed = false)
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
			$select = $this->getDbTable()->getAdapter()->select()->distinct()->from('vos');
			$this->joins($select, $filter);
			if ( ! is_array($filter->expr()) ) $select->where($filter->expr());
			$executor = $this->getDbTable()->getAdapter();
			$executor->setFetchMode(Zend_Db::FETCH_OBJ);
		}

		if ($filter !== null) $select->limit($filter->limit, $filter->offset);
		if ($filter !== null) {
			$inv = false;
			if ( (substr($orderby,0,8) === "vos.rank") ) {
//				$orderby = substr($orderby, 4);
//				if ( strpos($orderby," DESC") === false ) {
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
		if ( $from == '' ) $from = 'FROM vos';

		if ($format === 'xml') {
			$func_name = "vo_to_xml";
			if ( $xmldetailed ) {
				$func_name .= "_ext";
			}
//			debug_log(var_export($filter->fltstr,true));
//			debug_log(var_export($from, true));
//			debug_log(var_export($where,true));
//			debug_log($orderby);

			$this->getDbTable()->getAdapter()->setFetchMode(Zend_Db::FETCH_OBJ);
			if ( is_array($filter->expr()) ) {
				noDBSeqScan(db());
//				$fff = fopen(APPLICATION_PATH . "/../cache/debuglog", "a+");
//				$sss = db()->quoteInto("SELECT ".$func_name."(array_agg(id $orderby)) as vo FROM filtervos((?)::text[],", php_to_pg_array($filter->fltstr, false)) . db()->quoteInto("(?)::text[],", php_to_pg_array($from, false)) . db()->quoteInto("(?)::text[]) AS vos $limit", str_replace("''", "\'", php_to_pg_array($where, false)));
//				fwrite($fff, $sss);
//				fclose($fff);
				$resultSet = db()->query("SELECT ".$func_name."(array_agg(id $orderby)) as vo FROM filtervos((?)::text[],(?)::text[],(?)::text[]) AS vos $limit", array(php_to_pg_array($filter->fltstr, false), php_to_pg_array($from, false), str_replace("''", "\'", php_to_pg_array($where, false))))->fetchAll();
			} else {
				noDBSeqScan(db());
				$resultSet = db()->query("SELECT ".$func_name."(array_agg(id $orderby)) AS vo FROM filtervos(?,?,?) AS vos $limit", array($filter->fltstr, $from, $where))->fetchAll();
			}
		} else {
			$select = fixuZenduBuguru("" . $select);
			noDBSeqScan($executor);
			$resultSet = $executor->fetchAll($select);
		}
		$entries = array();
		foreach ($resultSet as $row) {
			if ( $format === 'xml' ) {
				$entry = $row->vo;
			} else {
				$entry = new VO();
				$this->populate($entry,$row);
			}
			$entries[] = $entry;
		}		return $entries;
	}
}
