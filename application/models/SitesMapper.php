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
class Default_Model_SitesMapper extends Default_Model_SitesMapperBase
{
	public function joins(&$select, $filter) {
		$select->joinLeft("sites.any", "sites.any.id = sites.id", array());
		if ( is_array($filter->joins) ) {
			
			$select->joinLeft('va_providers','va_providers.sitename = sites.name', array());
			$select->joinLeft('va_provider_images', 'va_provider_images.va_provider_id = va_providers.id', array() );

			if ( in_array("vos", $filter->joins) ) {
				$select->joinLeft('vowide_image_list_images', 'vowide_image_list_images.id = va_provider_images.vowide_vmiinstanceid', array() );
				$select->joinLeft('vowide_image_lists', 'vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id', array() );
				$select->joinLeft('vos', 'vos.id = vowide_image_lists.void', array() );
				$select->joinLeft('vos.any', 'vos.any.id = vos.id', array() );
			}
			
			if (in_array("applications", $filter->joins) || in_array("disciplines", $filter->joins) || in_array("middlewares", $filter->joins) || in_array("categories", $filter->joins) || in_array("oses", $filter->joins) || in_array("proglangs", $filter->joins) || in_array("archs", $filter->joins) || in_array("countries", $filter->joins) || in_array("hypervisors", $filter->joins) || in_array("oses", $filter->joins) || in_array("os_families", $filter->joins)) {
				$select->joinLeft('vaviews','vaviews.vmiinstanceid = va_provider_images.vmiinstanceid', array());
				$select->joinLeft('applications', 'applications.id = vaviews.appid', array() );
				$select->joinLeft('applications.any', 'applications.any.id = applications.id', array() );
			}
			
			if ( ! in_array("countries", $filter->joins) && in_array("countries", $filter->privateJoins) ) {
				$select->joinLeft('countries', 'countries.id = sites.countryid', array() );
				$select->joinLeft('countries.any', 'countries.any.id = countries.id', array() );
			} elseif ( in_array("countries", $filter->joins) ) {
				$select->joinLeft('appcountries', 'appcountries.appid = applications.id', array() );
				$select->joinLeft('countries', 'countries.id = sites.countryid OR appcountries.id = sites.countryid', array() );
				$select->joinLeft('countries.any', 'countries.any.id = countries.id', array() );
			}
			
			if ( in_array("disciplines", $filter->joins) ) {
				$select->joinLeft('disciplines', 'disciplines.id = ANY(applications.disciplineid)', array());
				$select->joinLeft('disciplines.any', 'disciplines.any.id = disciplines.id', array());
			}
			
			if ( in_array("middlewares", $filter->joins) ) {
				$select->joinLeft('app_middlewares','applications.id = app_middlewares.appid', array());
				$select->joinLeft('app_middlewares.any','app_middlewares.any.id = app_middlewares.id', array());
				$select->joinLeft('middlewares','middlewares.id = app_middlewares.middlewareid', array());
				$select->joinLeft('middlewares.any','middlewares.any.id = middlewares.id', array());
            }
			
            if ( in_array("categories", $filter->joins) ) {
				$select->joinLeft("categories","categories.id = ANY(applications.categoryid)",array());
				$select->joinLeft("categories.any.any","categories.any.id = categories.id",array());
			}
			if ( in_array("proglangs", $filter->joins) ) {
				$select->joinLeft('appproglangs', 'appproglangs.appid = applications.id', array());
				$select->joinLeft('proglangs', 'appproglangs.proglangid = proglangs.id', array());
				$select->joinLeft('proglangs.any', 'proglangs.any.id = proglangs.id', array());
			}
/*			if ( in_array("archs", $filter->joins) ) {
				$select->joinLeft('app_archs', 'app_archs.appid = applications.id', array());
				$select->joinLeft('archs', 'app_archs.archid = archs.id', array());
				$select->joinLeft('archs.any', 'archs.any.id = archs.id', array());
} */
/*			
			if ( in_array("oses", $filter->joins) ) {
				$select->joinLeft('app_oses', 'app_oses.appid = applications.id', array());
				$select->joinLeft('oses', 'app_oses.osid = oses.id', array());
				$select->joinLeft('oses.any', 'oses.any.id = oses.id', array());
			}
 */			
			if ( in_array("vapp_versions", $filter->joins) || in_array("vmiflavours", $filter->joins) || in_array("oses", $filter->joins) || in_array("os_families", $filter->joins) || in_array("archs", $filter->joins) || in_array("hypervisors", $filter->joins) ) {
				$select->joinLeft('vapplications', 'vapplications.appid = applications.id', array());
				$select->joinLeft('vapp_versions', 'vapp_versions.vappid = vapplications.id AND vapp_versions.published AND vapp_versions.enabled AND NOT vapp_versions.archived', array());
			}
			if ( in_array("vmiflavours", $filter->joins) || in_array("oses", $filter->joins) || in_array("os_families", $filter->joins) || in_array("archs", $filter->joins) || in_array("hypervisors", $filter->joins) ) {
				$select->joinLeft('vmis', 'vmis.vappid = vapplications.id', array());
				$select->joinLeft('vmiflavours', 'vmiflavours.vmiid= vmis.id', array());
			}
			if ( in_array("hypervisors", $filter->joins) ) {
				$select->joinLeft('hypervisors', 'hypervisors.name::TEXT = ANY(vmiflavours.hypervisors::TEXT[])', array());
			}
			if ( in_array("archs", $filter->joins) ) {
				$select->joinLeft('archs', 'vmiflavours.archid = archs.id', array());
				$select->joinLeft('archs.any', 'archs.any.id = archs.id', array());
			}
			if ( in_array("oses", $filter->joins) || in_array("os_families", $filter->joins) ) {
				$select->joinLeft('oses', 'vmiflavours.osid = oses.id', array());
				$select->joinLeft('oses.any', 'oses.any.id = oses.id', array());
				if ( in_array("os_families", $filter->joins) ) {
					$select->joinLeft('os_families', 'os_families.id = oses.os_family_id', array());
				}
			}
		}
	}

	public function count($filter = null) {
		//$rs = $this->fetchAll($filter, "xml", false);
		//return count($rs);
		$select = $this->getDbTable()->select();
		if ( (($filter !== null) && ($filter->expr() != '')) ) {
			$select = $this->getDbTable()->getAdapter()->select()->distinct()->from('sites');
			$this->joins($select, $filter);
			if ( is_array($filter->expr()) ) {
				$where = array();
				$from = array();
				foreach($filter->expr() as $ex) {
					if ( $ex != '' ) {
						$sss = clone $select;
						$sss->where($ex);
						getZendSelectParts($sss, $f, $w, $orderby, $limit);
						if ( $f == '' ) $f= 'FROM sites';
						$f = fixuZenduBuguru($f);
						$where[] = $w;
						$from[] = $f;
					}
				}
			} else {
				$select->where($filter->expr());
				getZendSelectParts($select, $from, $where, $orderby, $limit);
				$from = fixuZenduBuguru($from);
				if ( $from == '' ) $from = 'FROM sites';
			}
		} else {
			$from = 'FROM sites';
			$where = '';
		}
		$this->getDbTable()->getAdapter()->setFetchMode(Zend_Db::FETCH_OBJ);
		noDBSeqScan(db());
		if ( is_array($filter->expr()) ) {
			$res = db()->query("SELECT COUNT(DISTINCT id) FROM filtersites((?)::text[], (?)::text[], (?)::text[])", array(php_to_pg_array($filter->fltstr, false), php_to_pg_array($from, false), str_replace("''", "\'", php_to_pg_array($where, false))))->fetchAll();
		} else {
			//if ( ! $this->_nocache ) {
				$res = db()->query("SELECT COUNT(DISTINCT id) FROM filtersites(?,?,?)", array($filter->fltstr, $from, $where))->fetchAll();
			//} else {
			//	$res = db()->query("SELECT COUNT(DISTINCT id) $from $where");
			//}
		}
		return $res[0]->count;
	}
	
	public function fetchFilteredEntries($filter = null, $format = '')
	{
		if ( $filter !== null ) {
			$select = $this->getDbTable()->getAdapter()->select()->distinct()->from('sites');
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
		if ( $from == '' ) $from = 'FROM sites';

		db()->setFetchMode(Zend_Db::FETCH_OBJ);
		if ( is_array($filter->expr()) ) {
			noDBSeqScan(db());
			$resultSet = db()->query("SELECT sites.guid as guid FROM filtersites((?)::text[],(?)::text[],(?)::text[]) AS sites INNER JOIN sites AS s ON s.id = sites.id $limit", array(php_to_pg_array($filter->fltstr, false), php_to_pg_array($from, false), str_replace("''", "\'", php_to_pg_array($where, false))))->fetchAll();
		} else {
			noDBSeqScan(db());
			$resultSet = db()->query("SELECT sites.guid as guid FROM filtersites(?,?,?) AS sites INNER JOIN sites AS s ON s.id = sites.id $limit", array($filter->fltstr, $from, $where))->fetchAll();
		}
		$guids = array();
		foreach ($resultSet as $row) {
			 $guids[] = $row->guid;
		}
		
		$select = $this->getDbTable()->select();
		if ( ($filter !== null) && ($filter->expr() != '') ) {
			if (trim(implode("','", $guids)) != '') {
				$select->where("sites.guid in ('" . implode("','", $guids) . "')");
			} else {
				// no guids found, return no results 
				$select->where("sites.guid IS NULL");
			}
		}
		if ($filter !== null) $select->limit($filter->limit, $filter->offset);
		if ($filter !== null) $select->order($filter->orderBy);
		$resultSet = $this->getDbTable()->fetchAll($select);
		$entries = array();
		foreach ($resultSet as $row) {
			$entry = new Default_Model_Site();
			$this->populate($entry,$row);
			$entries[] = $entry;
		}		
		return $entries;
	}
	
	public function fetchAll($filter = null, $format = '', $xmldetailed = false) {
		$select = $this->getDbTable()->select()->from('sites');
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
			if( $filter !== null && is_array($filter->joins) && count($filter->joins)>0 ){
				return $this->fetchFilteredEntries($filter);
			}else{
				return parent::fetchAll($filter);
			}
		} else {
			if ($format === 'xml') {
				if ( $filter !== null ) {
					$select = $this->getDbTable()->getAdapter()->select()->distinct()->from('sites');
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
				if ( $from == '' ) $from = 'FROM sites';
				
				if ( $xmldetailed === true ) {
					$func = "site_to_xml_ext";
				} else {
					$func = "site_to_xml";
				}
				db()->setFetchMode(Zend_Db::FETCH_OBJ);
				if ( is_array($filter->expr()) ) {
					noDBSeqScan(db());
					$resultSet = db()->query("SELECT ".$func."(array_agg(sites.guid::text $orderby)) as site FROM filtersites((?)::text[],(?)::text[],(?)::text[]) AS sites INNER JOIN sites AS s ON s.id = sites.id $limit", array(php_to_pg_array($filter->fltstr, false), php_to_pg_array($from, false), str_replace("''", "\'", php_to_pg_array($where, false))))->fetchAll();
				} else {
					noDBSeqScan(db());
					$resultSet = db()->query("SELECT ".$func."(array_agg(sites.guid::text $orderby)) as site FROM filtersites(?,?,?) AS sites INNER JOIN sites AS s ON s.id = sites.id $limit", array($filter->fltstr, $from, $where))->fetchAll();
				}
				$entries = array();
				foreach ($resultSet as $row) {
					$entry = $row->site;
					$entries[] = $entry;
				}
				return $entries;
         	}
		}
	}
}
