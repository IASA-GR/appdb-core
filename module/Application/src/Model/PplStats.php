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

class PplStats
{
	protected $_db;
	protected $_where = '';
		
	public function __construct()
	{
	}

	public function setFilter($criteria)
	{
		$apps = new Researchers();
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
			$this->_where = "WHERE pplviews.id IN ($ids)";
		}
	}
	
	public function getFilter()
	{
		return ' WHERE researchers.deleted IS FALSE ';
		//return $this->_where;
	}
	
	public function perPosition()
	{
		return $this->_db->query("SELECT CASE WHEN positiontypes.description IS NULL THEN 'N/A' ELSE positiontypes.description||'s' END AS Position, COUNT(DISTINCT researchers.id) AS PplCount, positiontypes.id AS STID FROM researchers LEFT OUTER JOIN positiontypes ON positiontypeid = positiontypes.id ".$this->getFilter()." GROUP BY Position, positiontypeid, STID ORDER BY positiontypeid DESC;", array())->toArray();
	}
	
	public function perCountry()
	{
		return $this->_db->query("SELECT CASE WHEN countries.name IS NULL THEN 'N/A' ELSE countries.name END AS Country, COUNT(DISTINCT researchers.id) AS PplCount, countries.id AS STID FROM researchers LEFT OUTER JOIN countries ON countries.id = countryid ".$this->getFilter()." GROUP BY country, STID ORDER BY country DESC;", array())->toArray();
	}
	
	public function perRegion()
	{		
		return $this->_db->query("SELECT CASE WHEN regions.name IS NULL THEN 'N/A' ELSE regions.name END AS Region, COUNT(DISTINCT pplviews.id) AS PplCount, regions.id AS STID FROM pplviews LEFT OUTER JOIN regions ON regions.id = regionid ".$this->getFilter()." GROUP BY Region, STID ORDER BY Region DESC;", array())->toArray();
	}
	
}
