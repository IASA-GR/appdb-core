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

class VO2 extends VO2Base
{
	protected $_sites;
	public function getApplications()
	{
		if ($this->_applications === null) {
			$apps = new Applications();
			$f = new VOsFilter();
			$f->id->equals($this->id);
			$apps->filter->chain($f,"AND");
			$apps->filter->orderBy(array("lastupdated DESC","name ASC"));
			$this->_applications = $apps->items;
		}
		return $this->_applications;
	}
	
	public function getSites()
	{
		if ($this->_sites === null) {
			$sites = new Sites();
			$f = new VOsFilter();
			$f->id->equals($this->id);
			$sites->filter->chain($f,"AND");
			$sites->filter->orderBy(array("name ASC"));
			$this->_sites = $sites->items;
		}
		return $this->_sites;
	}
	
	public function getLogo() {
		db()->setFetchMode(Zend_Db::FETCH_OBJ);
		$ids = db()->query("SELECT UNNEST(disciplineid) AS did FROM vos WHERE id = " . $this->id . " ORDER BY did DESC LIMIT 1")->fetchAll();
		$id = $ids->did;
		$discs = new Disciplines();
		$discs->filter->id->numequals($id);
		if (count($discs->items) > 0) {
			$disc = $discs->items[0];
			return $disc->getLogo();
		} else {
			return "images/disciplines/0.png";
		}
	}
}
