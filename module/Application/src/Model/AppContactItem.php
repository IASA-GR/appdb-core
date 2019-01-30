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

class AppContactItem extends AppContactItemBase
{
	protected $_appcontact = null;
	protected $_item = null;

	public function getAppContact() {
		if ( $this->_appcontact === null ) {
			$ac = new ResearchersApps();
			$ac->filter->appid->equals($this->_appid)->and($ac->filter->researcherid->equals($this->_researcherid));
			if ( count($ac->items) > 0 ) {
				$this->_appcontact = $ac->items[0];
			}
		}
		return $this->_appcontact;
	}

	public function getItem() {
		if ( $this->_item === null ) {
			if ( $this->_itemType === 'vo' ) {
				$items = new VOs();
				$items->filter->id->equals($this->_itemID);
				if ( count($items->items) > 0 ) {
					$this->_item = $items->items[0];
				}
			} elseif ( $this->_itemType === 'middleware' ) {
				$items = new Middlewares();
				$items->filter->id->equals($this->_itemID);
				if ( count($items->items) > 0 ) {
					$this->_item = $items->items[0];
				}
			} elseif ( $this->_itemType === 'other' ) {
				$this->_item = parent::getItem();
			} else {
				return null;
			}
		}
		return $this->_item;
	}

	public function getResearcher() {
		if ( $this->getAppContact() !== null ) return $this->getAppContact()->researcher;
	}

	public function getApplication() {
		if ( $this->getAppContact() !== null ) return $this->getAppContact()->application;
	}
}
