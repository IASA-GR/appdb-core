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
// PUT YOUR CUSTOM CODE HERE
namespace Application\Model;

class RelatedApplications extends Applications
{
	protected $_appid;
	public $limit;
	public $offset;
	public $filter;

	public function __construct($appid) {
		parent::__construct();
		$this->_appid = $appid;
		$this->filter = null;
	}

	protected function __filterItems() {
		if ($this->filter !== null && trim($this->filter->fltstr) !== "" ) {
			$apps = new Applications();
			$apps->filter = $this->filter;
			$apps->filter->limit = null;
			$apps->filter->offset = null;
			$apps->filter->orderBy("name");
			$ids = array();
			foreach ($apps->items as $app) {
				$ids[] = $app->id;
			};
			if (count($ids) > 0) {
				$where = " applications.id IN (" . implode($ids, ",") . ") ";
			} else {
				$where = " FALSE ";
			}
		} else {
			$where = "";
		}
		return $where;
	}

	public function refresh($format = '', $userid = '', $xmldetailed = false) {
		$limit = '';
		if ( isset($this->limit) ) $limit .= ' LIMIT '.$this->limit;
		if ( isset($this->offset) ) $limit .= ' OFFSET '.$this->offset;
		$having = '';
		if ( ! $this->viewModerated ) {
			$having = 'HAVING ((app).moderated = FALSE) AND ((app).deleted = FALSE) ';
		}
		$where = $this->__filterItems();
		if ($where != '') $where = ' WHERE '. $where;
		if ( $format === 'xml') {
			$res = db()->query('SELECT xmlelement(name "application:relatedapp", xmlattributes(\'http://appdb.egi.eu/api/0.2/application\' as "xmlns:application", MIN(rank) as rank,'.$this->_appid.' as parentID, (SELECT name FROM applications WHERE id = '. $this->_appid .') as parentName),app_to_xml((app).id)) as relatedapp FROM related_apps(' . $this->_appid .') INNER JOIN applications ON applications.id = (app).id' . $where . ' GROUP BY app '. $having .'ORDER BY MIN(rank), (app).name' . $limit, array())->toArray();
		} else {
			$res = db()->query('SELECT DISTINCT (app).*, MIN(rank) AS rank FROM related_apps(' . $this->_appid . ') GROUP BY app '. $having . 'ORDER BY rank, name' . $limit . ';', array())->toArray();
		}
		$a = array();
		foreach($res as $row) {
			if ( $format == 'xml' ) {
				$app = $row['relatedapp'];
			} else {
				$app = new RelatedApplication();
				$this->getMapper()->populate($app, $row);
				$app->rank = $row['rank'];
			}
			$a[] = $app;
		}
		$this->_items = $a;
		return $this;
	}

	public function count() {
		$where = $this->__filterItems();
		if ( ! $this->viewModerated ) {
			$having = 'WHERE ((app).moderated = FALSE) AND ((app).deleted = FALSE) ';
			if ($where != '') {
				$having = $having . " AND " . $where;
			}
		} else {
			if ($where != '') {
				$having = $where; 
			} else {
				$having = '';
			}
		}
		$res = db()->query('SELECT COUNT(DISTINCT app) AS count FROM related_apps(' . $this->_appid .') INNER JOIN applications ON applications.id = (app).id ' . $having .';', array())->toArray();
		return $res[0]['count'];
	}
}