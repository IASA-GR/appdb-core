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
class Default_Model_VMIinstance extends Default_Model_VMIinstanceBase
{
	protected $_networkTraffic;

	public function delete(){
		$valists = new Default_Model_VALists();
		$valists->filter->vmiinstanceid->numequals($this->id);
		if( count($valists->items) > 0 ){
			for($i=0; $i<count($valists->items); $i+=1){
				$item = $valists->items[0];
				$item->delete();
			}
		}
		$this->getMapper()->delete($this);
	}
	public function getVA(){
		$vmi = $this->getVmi();
		if( !$vmi ){
			return null;
		}
		return $vmi->getVa();
	}
	public function getVmi(){
		$flavour = $this->getFlavour();
		if( !$flavour ){
			return null;
		}
		$vmi = $flavour->getVmi();
		return $vmi;
	}
	public function getVAVersion(){
		$version = null;
		$vlists = new Default_Model_VALists();
		$vlists->filter->vmiinstanceid->numequals($this->id);
		if( count($vlists->items) > 0 ){
			$item = $vlists->items[0];
			$version = $item->getVAversion();
		}
		return $version;
	}

	public function getNetworkTraffic() {
		if ($this->_networkTraffic === null) {
			$nt = new Default_Model_VMINetworkTraffic();
			$nt->vmiinstanceid->numequals($this->id);
			$this->_networkTraffic = $nt->items;
		}
		return $this->_networkTraffic;
	}

	public function deleteNetworkTraffic() {
		$nts = new Default_Model_VMINetworkTraffic();
		foreach ($this->getNetworkTraffic() as $nt) {
			$nts->remove($nt);
		}
	}

	public function getSites()
	{
		if ($this->_sites === null) {
			$sites = new Default_Model_Sites();
			$f = new Default_Model_VOsFilter();
			$f->id->equals($this->id);
			$sites->filter->chain($f,"AND");
			$sites->filter->orderBy(array("name ASC"));
			$this->_sites = $sites->items;
		}
		return $this->_sites;
	}
}
