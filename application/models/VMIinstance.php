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
}
