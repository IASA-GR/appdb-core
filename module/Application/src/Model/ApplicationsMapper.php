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

use \Zend\Db\Adapter\Adapter;

class ApplicationsMapper extends ApplicationsMapperBase
{
	public $_userid;
	public $_nocache = false;

	public function populate(&$entry, $row) {
		parent::populate($entry,$row);
		if (! array_key_exists('keywords', $row)) $row['keywords'] = null;
		if (! array_key_exists('disciplineid', $row)) $row['disciplineid'] = null;
		if (! array_key_exists('categoryid', $row)) $row['categoryid'] = null;
		$entry->setKeywords(pg_to_php_array($row['keywords']));
		$entry->setDisciplineID(pg_to_php_array($row['disciplineid']));
		$entry->setCategoryID(pg_to_php_array($row['categoryid']));
	}

	public function save(AROItem $value) {
		$value->Keywords = null;
		$value->disciplineID = null;
		$value->categoryID = null;
		parent::save($value);
	}

	public function joins(&$select, $filter) {
		$select->join("applications.any", "applications.any.id = applications.id", array(), 'left');
		if ( is_array($filter->joins) ) {
			if ( in_array("vapp_versions", $filter->joins) || in_array("vmiflavours", $filter->joins) || in_array("oses", $filter->joins) || in_array("os_families", $filter->joins) || in_array("archs", $filter->joins) || in_array("hypervisors", $filter->joins) ) {
				$select->join('vapplications', 'vapplications.appid = applications.id', array(), 'left');
				$select->join('vapp_versions', 'vapp_versions.vappid = vapplications.id AND vapp_versions.published AND vapp_versions.enabled AND NOT vapp_versions.archived', array(), 'left');
			}
			if ( in_array("vmiflavours", $filter->joins) || in_array("oses", $filter->joins) || in_array("os_families", $filter->joins) || in_array("archs", $filter->joins) || in_array("hypervisors", $filter->joins) ) {
				$select->join('vmis', 'vmis.vappid = vapplications.id', array(), 'left');
				$select->join('vmiflavours', 'vmiflavours.vmiid= vmis.id', array(), 'left');
			}
			if ( in_array("licenses", $filter->joins) || (in_array("app_licenses", $filter->joins)) ) {
				$select->join('app_licenses', 'app_licenses.appid = applications.id', array(), 'left');
				$select->join('licenses', 'app_licenses.licenseid = licenses.id', array(), 'left');
				$select->join('licenses.any', 'licenses.any.id = licenses.id', array(), 'left');
				$select->join('app_licenses.any', 'app_licenses.any.id = app_licenses.id', array(), 'left');
			}
			if ( in_array("statuses", $filter->joins) ) {
				$select->join('statuses', 'statuses.id = applications.statusid', array(), 'left');
				$select->join('statuses.any', 'statuses.any.id = applications.statusid', array(), 'left');
			}
			if ( in_array("oses", $filter->joins) || in_array("os_families", $filter->joins) ) {
				$select->join('oses', 'vmiflavours.osid = oses.id', array(), 'left');
				$select->join('oses.any', 'oses.any.id = oses.id', array(), 'left');
				if ( in_array("os_families", $filter->joins) ) {
					$select->join('os_families', 'os_families.id = oses.os_family_id', array(), 'left');
				}
			}
			if ( in_array("proglangs", $filter->joins) ) {
				$select->join('appproglangs', 'appproglangs.appid = applications.id', array(), 'left');
				$select->join('proglangs', 'appproglangs.proglangid = proglangs.id', array(), 'left');
				$select->join('proglangs.any', 'proglangs.any.id = proglangs.id', array(), 'left');
			}
			if ( in_array("hypervisors", $filter->joins) ) {
				$select->join('hypervisors', 'hypervisors.value = ANY(vmiflavours.hypervisors)', array(), 'left');
			}
			if ( in_array("archs", $filter->joins) ) {
				$select->join('archs', 'vmiflavours.archid = archs.id', array(), 'left');
				$select->join('archs.any', 'archs.any.id = archs.id', array(), 'left');
			}
			if ( in_array("app_release_count", $filter->joins) ) {
				$select->join('app_release_count', 'app_release_count.appid = applications.id', array(), 'left');
			}
			if ( in_array("vos", $filter->joins) || 
				in_array("disciplines",$filter->joins) || 
				in_array("middlewares",$filter->joins) ) {
				$select->join('app_vos', 'app_vos.appid = applications.id', array(), 'left');
				$select->join('vos','vos.id = app_vos.void AND vos.deleted IS FALSE', array(), 'left');
				$select->join('vos.any', 'vos.any.id = vos.id', array(), 'left');
            }
			if (in_array("categories", $filter->joins)) {
				$select->join('categories','categories.id = ANY(applications.categoryid)', array(), 'left');
				$select->join('categories.any','categories.any.id = categories.id', array(), 'left');
			}
			if (in_array("disciplines", $filter->joins)) {
				$select->join('disciplines','disciplines.id = ANY(applications.disciplineid)', array(), 'left');
				$select->join('disciplines.any','disciplines.any.id = disciplines.id', array(), 'left');
			}
//			if (in_array("appcountries", $filter->joins)) $select->join('appcountries','applications.id = appcountries.appid', array(), 'left');
			if (in_array("countries", $filter->joins)) {
				$select->join('appcountries','applications.id = appcountries.appid', array(), 'left');
				$select->join('countries','countries.id = appcountries.id', array(), 'left');
				$select->join('countries.any','countries.any.id = countries.id', array(), 'left');
			}
			if (in_array("middlewares", $filter->joins)) {
				$select->join('app_middlewares','applications.id = app_middlewares.appid', array(), 'left');
				$select->join('app_middlewares.any','app_middlewares.any.id = app_middlewares.id', array(), 'left');
				if ( in_array("middlewares", $filter->privateJoins) ) {
					$select->join('middlewares','middlewares.id = app_middlewares.middlewareid', array(), 'left');
				} else {
					$select->join('vo_middlewares','vo_middlewares.void = vos.id', array(), 'left');
					$select->join('middlewares','middlewares.id = app_middlewares.middlewareid OR middlewares.id = vo_middlewares.middlewareid', array(), 'left');
				}
				$select->join('middlewares.any', 'middlewares.any.id = middlewares.id', array(), 'left');
			}
			if (in_array("researchers", $filter->joins) || in_array("contacts", $filter->joins) || in_array("positiontypes", $filter->joins)) {
				$select->join('researchers_apps','researchers_apps.appid = applications.id', array(), 'left');
				$select->join('researchers','researchers.id = researchers_apps.researcherid AND researchers.deleted IS FALSE', array(), 'left');
				$select->join("researchers.any", "researchers.any.id = researchers.id", array(), 'left');
			}
			if (in_array("contacts", $filter->joins)) {
				$select->join('contacts','researchers.id = contacts.researcherid', array(), 'left');
				$select->join('contacts.any','contacts.any.id = contacts.id', array(), 'left');
			}
			if (in_array("positiontypes", $filter->joins)) {
				$select->join('positiontypes','researchers.positiontypeid = positiontypes.id', array(), 'left');
				$select->join('positiontypes.any','positiontypes.any.id = positiontypes.id', array(), 'left');
			}
			if (in_array("appbookmarks", $filter->joins)) $select->join('appbookmarks','appbookmarks.appid = applications.id', array(), 'left');
			if (in_array("permissions", $filter->joins)) $select->join('permissions','permissions.object = applications.guid', array(), 'left');
		}
	}

	public function count($filter = null)
	{
		$from = '';
		$where = '';
		$select = $this->getDbTable()->getSql()->select();
		if ( (($filter !== null) && ($filter->expr() != '')) ) {
			$select = $this->getDbTable()->getSql()->select();
			$select->quantifier('DISTINCT');
			$this->joins($select, $filter);
			if ( is_array($filter->expr()) ) {
				$where = array();
				$from = array();
				foreach($filter->expr() as $ex) {
					if ( $ex != '' ) {
						$sss = clone $select;
						$sss->where($ex);
						//getZendSelectParts($sss, $f, $w, $orderby, $limit);
						if ( $f == '' ) $f= 'FROM applications';
						//$f = fixuZenduBuguru($f);
						$where[] = $w;
						$from[] = $f;
					}
				}
			} else {
				$select->where($filter->expr());
				getZendSelectParts($select, $from, $where, $orderby, $limit);
				//$from = fixuZenduBuguru($from);
				if ( $from == '' ) $from = 'FROM applications';
			}
		} else {
			$from = 'FROM applications';
			$where = '';
		}
//		//$this->getDbTable()->getAdapter()->setFetchMode(Zend_Db::FETCH_OBJ);
		noDBSeqScan(db());
		if ( is_array($filter->expr()) ) {
			$res = db()->query("SELECT COUNT(DISTINCT id) FROM filterapps((?)::text[], (?)::text[], (?)::text[])", array(php_to_pg_array($filter->fltstr, false), php_to_pg_array($from, false), str_replace("''", "\'", php_to_pg_array($where, false))))->toArray();
		} else {
			if ( ! $this->_nocache ) {
				$res = db()->query("SELECT COUNT(DISTINCT id) FROM filterapps(?,?,?)", array($filter->fltstr, $from, $where))->toArray();
			} else {
				$res = db()->query("SELECT COUNT(DISTINCT id) $from $where");
			}
		}
		return $res[0]['count'];
	}

	public function fetchAll($filter = null, $format = '', $xmldetailed = false)
	{
		error_log("FORMAT: $format");
		$from = '';
		$limit = '';
		$where = '';
		$select = $this->getDbTable()->getSql()->select();
		$select->quantifier('DISTINCT');
		$select->columns(array('*', 'applications.keywords', 'applications.categoryid', 'applications.disciplineid'));
		$executor = $this->getDbTable();
		if ( $filter !== null ) {
			if (! is_null($filter->orderBy)) {
				$orderby = $filter->orderBy;
			} else {
				$orderby = null;
			}
			if ( is_array($orderby) ) {
				$orderby = end($orderby);
			}
		} else {
			$orderby = null;
		}

		if (trim($orderby) == "") $orderby = null;

		if ( (($filter !== null) && ($filter->expr() != '')) || 
			(substr($orderby, 0, 27) === "applications.app_popularity")
		) {
			$select = $this->getDbTable()->getSql()->select();
			$select->quantifier('DISTINCT');
			$cols = array('*', 'applications.keywords', 'applications.categoryid', 'applications.disciplineid');
			if (substr($orderby,0,27) === "applications.app_popularity") {
				$cols[] = 'app_popularity(applications.id)';
				
			}
			$select->columns($cols);
			if ( $filter !== null ) {
				if ($filter->expr() != '') {
					$this->joins($select, $filter);
					if ( ! is_array($filter->expr()) ) $select->where($filter->expr());
					$executor = $this->getDbTable()->getAdapter();
//					//$executor->setFetchMode(Zend_Db::FETCH_OBJ);
				}
			}
		}
		if (! is_null($filter)) {
			if (! is_null($filter->limit)) $select->limit($filter->limit);
			if (! is_null($filter->offset)) $select->offset($filter->offset);
		}
		if ($filter !== null) {
			if (substr($orderby, 0, 17) === "applications.rank") {
//				$orderby = substr($orderby,13);
//				if ( strpos($orderby," DESC") !== false ) {
//					$orderby = str_replace(" DESC"," DESC NULLS LAST", $orderby);
//				} else {
//					$orderby = str_replace(" ASC"," ASC NULLS FIRST", $orderby);
//				}
//				$orderby = array($orderby, "name");
				/**
				 * NOTE: Ordering by rank is handled by the database, so trust DB order
				 */
				$orderby = "applications.rank DESC, applications.name ASC";
			} elseif (substr($orderby,0,27) === "applications.app_popularity") {
				$orderby = substr($orderby,13);
				if ( strpos($orderby," DESC") !== false ) {
					$orderby = str_replace(" DESC"," DESC NULLS LAST", $orderby);
				} else {
					$orderby = str_replace(" ASC"," ASC NULLS FIRST", $orderby);
				}
				$orderby = array($orderby, "applications.name");
			} elseif (substr($orderby,0,19) === 'applications.rating') {
				if ( strpos($orderby," DESC") !== false ) {
					$orderby = str_replace(" DESC"," DESC NULLS LAST", $orderby);
				} else {
					$orderby = str_replace(" ASC"," ASC NULLS FIRST", $orderby);
				}
				$orderby = array($orderby, "applications.ratingcount DESC", "applications.name");
			} elseif (substr($orderby,0,21) === 'applications.hitcount') {
				if ( strpos($orderby," DESC") !== false ) {
					$orderby = str_replace(" DESC"," DESC NULLS LAST", $orderby);
				} else {
					$orderby = str_replace(" ASC"," ASC NULLS FIRST", $orderby);
				}
//				$orderby = str_replace("applications.hitcount","hitcount",$orderby);
				$orderby = array($orderby, "applications.name");
			}

			/** freshness app order HACK start **/
			
			$orderby = str_replace("applications.lastupdated", "CASE WHEN applications.id IN (SELECT appid FROM app_order_hack) THEN '2000-01-01 00:00:00'::timestamp ELSE applications.lastupdated END", $orderby);
			$orderby = str_replace("applications.dateadded", "CASE WHEN applications.id IN (SELECT appid FROM app_order_hack) THEN '2000-01-01 00:00:00'::timestamp ELSE applications.dateadded END", $orderby);
			/** freshness app order HACK end **/

			if (trim($orderby) != "") {
				$select->order($orderby);
				if ( is_array($orderby) ) {
					$_orderby = implode(",", $orderby);
				} else $_orderby = $orderby;
			}
		}
		if (str_replace('export', '', $format) === 'xml' || str_replace('export', '', $format) === 'csv') {
//            //$this->getDbTable()->getAdapter()->setFetchMode(Zend_Db::FETCH_OBJ);
            $userid = '';
            if ( $format === 'xml' ) {
				$func = "app_to_xml"; 
			} else {
				$func = "export_app";
				$userid = ", '" . str_replace('export', '', $format) . "'";
			}
            if ( $xmldetailed === true ) {
                $func = $func . "_ext";
                if ( $this->_userid != '' ) $userid = ", $this->_userid";
			} elseif ( $xmldetailed === "listing" ) {
                $func = $func . "_list";
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
				//$from = fixuZenduBuguru($from);
			}

			if ( isset($_orderby) && trim($_orderby) !== "" ) $orderby = 'ORDER BY ' . $_orderby;	# TODO: FIX NULLS FIRST/LAST bypass
			if ( isset($orderby) ) $orderby = str_replace("applications.lastupdated", "apps.lastupdated", $orderby); # FORCE current value of "lastupdated" instead of cached
			if ( $from == '' ) $from = 'FROM applications';
			if ( $func == "app_to_xml" || $func == "app_to_xml_list" || $func == "export_app" ) {
//				debug_log(var_export($filter->fltstr,true));
//				debug_log(var_export($from, true));
//				debug_log(var_export($where,true));
//				debug_log($orderby);
				$frmt = "";
				if( $func === "export_app" ){
					$frmt = str_replace('export', '', $format);
					if ( !$frmt || trim($frmt) === "" ) {
						$frmt = "";
					} else if ( $frmt === 'xml' ){
						$frmt = ", 'xml'";
					} else if ( $frmt === 'csv' ){
						$frmt = ", 'csv'";
					} else {
						$frmt = "";
					}
				}
				if ( is_array($filter->expr()) ) {
					noDBSeqScan(db());
					$resultSet = db()->query("SELECT ".$func."(array_agg(applications.id $orderby) $frmt ) as application FROM filterapps((?)::text[],(?)::text[],(?)::text[]) AS applications INNER JOIN applications AS apps ON apps.id = applications.id $limit", array(php_to_pg_array($filter->fltstr, false), php_to_pg_array($from, false), str_replace("''", "\'", php_to_pg_array($where, false))))->toArray();
				} else {
					noDBSeqScan(db());
					$resultSet = db()->query("SELECT ".$func."(array_agg(applications.id $orderby) $frmt) as application FROM filterapps(?,?,?) AS applications INNER JOIN applications AS apps ON apps.id = applications.id $limit", array($filter->fltstr, $from, $where))->toArray();
				}
			} else {
				//debug_log("########" . "".$select . "#########");
				$select = (new \Zend\Db\Sql\Sql($this->getDbTable()->getAdapter()))->getSqlStringForSqlObject($select);
				$select = fixuZenduBuguru("" . $select);
				$select = str_replace('"applications"."applications".', '"applications".', $select);
				$select = str_replace('"applications.any"', '"applications"."any"', $select);
				$resultSet = db()->query("SELECT ".$func."(id".$userid.") as application FROM (".$select.") AS t", array())->toArray();
//				$resultSet = $this->getDbTable()->getAdapter()->query("SELECT ".$func."(id".$userid.") as application FROM (".$select.") AS t")->toArray();
			}
		} else {
			$select = (new \Zend\Db\Sql\Sql($this->getDbTable()->getAdapter()))->getSqlStringForSqlObject($select);
			$query = fixuZenduBuguru("" . $select);
			if ((strpos(str_replace('"', '', $orderby) ,"applications.name") !== false) && (! is_array($orderby))) {
				$query = str_replace("DISTINCT \"applications", "DISTINCT ON (applications.name) \"applications", $query);
			}
			$query = str_replace('"applications"."applications".', '"applications".', $query);
			$query = str_replace('"applications.any"', '"applications"."any"', $query);
			$resultSet = db()->query($query, array())->toArray();
////			debug_log("########" . "".$query. "#########");
//			noDBSeqScan($executor);
//			$resultSet = $executor->toArray($query);
        }
		$entries = array();
		foreach ($resultSet as $row) {
			if ( str_replace('export', '', $format) === 'xml' || str_replace('export', '', $format) === 'csv' ) {
				$entry = $row['application'];
			} else {
				$entry = new Application();
				$this->populate($entry,$row);
			}
			$entries[] = $entry;
		}		
		return $entries;
	}
}
