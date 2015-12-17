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
class Default_Model_VMI extends Default_Model_VMIBase
{	
	private $_vmiinstances;
	private $_vmiinstanceids;
	private $_flavours;
	private $_flavourids;
	
	public function getWMIInstanceIds(){
		$this->_vmiinstanceids = array();
		$vmiinstances = $this->getVMIInstances();
		if( count($vmiinstances) > 0 ){
			for( $i=0; $i<count($vmiinstances); $i+=1 ){
				$item = $vmiinstances[$i];
				$this->_vmiinstanceids[] = $item->id; 
			}
			$this->_vmiinstanceids = array_unique($this->_vmiinstanceids);
		}
		return $this->_vmiinstanceids;
	}
	public function getVMIInstances(){
		$this->_vmiinstances = array();
		$flavours = new Default_Model_VMIflavoursBase();
		$flavours->filter->vmiid->equals($this->Id);
		$flavourids = array();
		if( count($flavours->items) > 0 ){
			for($i=0; $i<count($flavours->items); $i+=1){
				$flavour = $flavours->items[$i];
				$flavourids[] = $flavour->Id;
			}
			$flavourids = array_unique($flavourids);
			$instances = new Default_Model_VMIinstances();
			$instances->filter->vmiflavourid->in($flavourids);
			if( count($instances->items) > 0 ){
				$this->_vmiinstances = $instances->items;
			}else{
				$this->_vmiinstances = array();
			}
		}
		return $this->_vmiinstances;
	}
	public function getFlavourIds(){
		$this->_flavourids = array();
		$this->getFlavours();
		for( $i=0; $i<count($this->_flavours); $i+=1 ){
			$flavour = $this->_flavours[$i];
			$this->_flavourids[] = $flavour->id;
		}
		return $this->_flavourids;
	}
	public function getFlavours(){
		$this->_flavours = array();
		$flavours = new Default_Model_VMIflavours();
		$flavours->filter->vmiid->equals($this->id);
		if( count($flavours->items) > 0 ){
			$this->_flavours[] = $flavours->items;
		}
		return $this->_flavours;
	}
	public function delete(){
		//delete associated wmi instances (associated vapplists are deleted upon vmi instance deletion)
		$instances = $this->getVMIInstances();
		if( count($instances) > 0 ) {
			for( $i=0; $i<count($instances); $i+=1 ){
				$instance = $instances[$i];
				$instance->delete();
			}
		}
		//delete associated vmi flavours
		$flavours = new Default_Model_VMIflavours();
		$flavours->filter->vmiid->equals($this->id);
		if( count($flavours->items) >  0 ){
			for( $i=0; $i<count($flavours->items); $i+=1 ){
				$flavour = $flavours->items[$i];
				$flavour->delete();
			}
		}
		//Delete the VMI entry
		$this->getMapper()->delete($this);
	}
}
