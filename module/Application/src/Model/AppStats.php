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
class Default_Model_AppStats
{
	protected $_db;
	protected $_where = '';
	protected $_appFlt = '';

	public function __construct($appType = "app")
	{
		global $application;
		$this->_db = $application->getBootstrap()->getResource('db');
		switch ($appType) {
		case "app":
			//$this->appFlt = " AND NOT categoryid <" . "@ (SELECT array_agg(va_categories) FROM va_categories())";
			$this->appFlt = " AND metatype = 0";
			break;
		case "vapp":
			//$this->appFlt = " AND categoryid <" . "@ (SELECT array_agg(va_categories) FROM va_categories())";
			$this->appFlt = " AND metatype = 1";
			break;
		default:
			error_log("[AppStats::__construct] WARNING: unknown s/w type requested");
			break;
		}
		$this->_where = "WHERE applications.deleted IS FALSE AND applications.moderated IS FALSE " . $this->appFlt;
	}
	
	public function setFilter($criteria)
	{
		$apps = new Default_Model_Applications();
		$f = null;
		foreach ($criteria as $k => $v) {
			if ($f === null) {
				$f = $apps->filter->$k->equals($v);
			} else {
				$f->and($f->$k->equals($v));
			}
		}
		$ids = '';
		foreach ( $apps->items as $item ) {
			$ids .= $item->Id.', ';
		}
		$ids = substr($ids,0,-2);
		if ( $ids != '' ) {
			$this->_where = "WHERE applications.id IN ($ids) AND applications.deleted IS FALSE AND applications.moderated IS FALSE " . $this->appFlt;
		}
	}
	
	public function getFilter()
	{
		return $this->_where;
	}
	
	public function perDiscipline()
	{
		$this->_db->setFetchMode(Zend_Db::FETCH_BOTH);
		$res = $this->_db->query("SELECT CASE WHEN disciplines.name IS NULL THEN 'N/A' ELSE disciplines.name END AS Discipline, COUNT(DISTINCT applications.id) AS AppCount, disciplines.id AS STID FROM applications LEFT OUTER JOIN disciplines ON disciplines.id = ANY(applications.disciplineid) " . $this->getFilter() . " GROUP BY Discipline, STID ORDER BY Discipline DESC;")->fetchAll();
		return $res;
	}
	
	public function perCountry()
	{
		//return $this->_db->query("SELECT COUNT(appID) AS AppCount, name AS Country FROM (SELECT DISTINCT countries.name, appID FROM researchers_apps INNER JOIN researchers ON researchers.ID = researchers_apps.researcherID INNER JOIN countries ON countries.ID = researchers.countryID) AS t1 GROUP BY country ORDER BY country DESC;")->fetchAll();
		$this->_db->setFetchMode(Zend_Db::FETCH_BOTH);
		return $this->_db->query("SELECT CASE WHEN countries.name IS NULL THEN 'N/A' ELSE countries.name END AS Country, COUNT(DISTINCT appviews.id) AS AppCount, countries.id as STID FROM appviews LEFT OUTER JOIN countries ON countries.id = countryid ". str_replace('applications.','appviews.',$this->getFilter())." GROUP BY Country, STID ORDER BY country DESC;")->fetchAll();
	}
	
	public function perRegion()
	{		
		//return $this->_db->query("SELECT COUNT(appID) AS AppCount, name AS Region FROM (SELECT DISTINCT regions.name, appID FROM researchers_apps INNER JOIN researchers ON researchers.ID = researchers_apps.researcherID INNER JOIN countries ON countries.ID = researchers.countryID INNER JOIN regions ON regions.ID = (SELECT regionID FROM countryRegions WHERE countryID = countries.ID)) AS t1 GROUP BY region ORDER BY region DESC;")->fetchAll();
		$this->_db->setFetchMode(Zend_Db::FETCH_BOTH);
		return $this->_db->query("SELECT CASE WHEN regions.name IS NULL THEN 'N/A' ELSE regions.name END AS Region, COUNT(DISTINCT appviews.id) AS AppCount, regions.id as STID FROM appviews LEFT OUTER JOIN regions ON regions.id = regionid ".$this->getFilter()." GROUP BY Region, STID ORDER BY Region DESC;")->fetchAll();
	}
	
	public function perVo()
	{
		$this->_db->setFetchMode(Zend_Db::FETCH_BOTH);
		return $this->_db->query("SELECT CASE WHEN vos.name IS NULL THEN 'N/A' ELSE vos.name END AS VO, COUNT(DISTINCT appviews.id) as AppCount, vos.name as STID FROM appviews LEFT OUTER JOIN vos ON vos.id = void AND vos.deleted IS FALSE ". str_replace('applications.', 'appviews.', $this->getFilter())." GROUP BY VO, STID ORDER BY VO DESC;")->fetchAll();
	}
}
