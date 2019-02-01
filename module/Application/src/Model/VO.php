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

class VO extends VOBase
{
	protected $_contacts;
	protected $_members;
	protected $_vomses;
	protected $_mws;
	protected $_res;

	public function getContacts() {
		if ($this->_contacts === null) {
			$urls = new VOContacts();
			$urls->filter->void->equals($this->id);
			$urls->filter->orderby("role ASC");
			$this->_contacts = $urls;
		}
		return $this->_contacts->items;
	}

	public function getMembers() {
		if ($this->_members === null) {
			$urls = new VOMembers();
			$urls->filter->void->equals($this->id);
			$this->_members= $urls;
		}
		return $this->_members->items;
	}

	public function getVOMSes() {
		if ($this->_vomses === null) {
			$urls = new VOMSes();
			$urls->filter->void->equals($this->id);
			$this->_vomses= $urls;
		}
		return $this->_vomses->items;
	}

	public function getResources() {
		if ($this->_res === null) {
			$urls = new VoResources();
			$urls->filter->void->equals($this->id);
			$this->_resources = $urls;
		}
		return $this->_resources->items;
	}

	public function getMiddlewares() {
		if ($this->_mws === null) {
			$urls = new VoMiddlewares();
			$urls->filter->void->equals($this->id);
			$this->_mws = $urls;
		}
		return $this->_mws->items;
	}

	public function getLogo() {
		$ids = db()->query("SELECT UNNEST(disciplineid) AS did FROM vos WHERE id = " . $this->id . " ORDER BY did DESC LIMIT 1")->fetchAll();
		$id = $ids[0]->did;
		$discs = new Disciplines();
		$discs->filter->id->numequals($id);
		if (count($discs->items) > 0) {
			$disc = $discs->items[0];
			return $disc->getLogo();
		} else {
			return "images/disciplines/998.png";
		}
	}
}
