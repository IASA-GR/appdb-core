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
class Default_Model_VAversion extends Default_Model_VAversionBase
{
	private $_vmis;
	private $_vmiids;
	private $_vapplists;
	
	public function getVMIIds(){
		if( !is_array($this->_vmiids) ){
			$this->_vmiids = array();
			$wmiids = array();
			$valists = new Default_Model_VALists();
			$valists->filter->vappversionid->equals($this->getId());
			if( count($valists->items) > 0 ){
				for( $i=0; $i<count($valists->items); $i+=1 ){
					$item = $valists->items[$i];
					$vmiinst = $item->getVMIinstance();
					if( $vmiinst ){
						$flavour = null;
						$flavours = new Default_Model_VMIflavoursBase();
						$flavours->filter->id->equals($vmiinst->vmiflavourid);
						if( count($flavours->items) > 0 ){
							$flavour = $flavours->items[0];
						}
						if( $flavour ){
							$vmi = $flavour->getVmi();
							if( $vmi ){
								$wmiids[] = $vmi->id;
							}
						}
					}
				}
				$wmiids = array_unique($wmiids);
				$this->_vmiids = array_values($wmiids); //reindex
			}
		}
		return $this->_vmiids;
	}
	public function getVMIs(){
		if( !is_array($this->_vmis) ){
			$this->_vmis = array();
			$wmiids = $this->getVMIIds();
			$vmis = new Default_Model_VMIs();
			$vmis->filter->id->in($wmiids);
			if( count($vmis->items) > 0 ){
				$this->_vmis = $vmis->items;
			}
		}
		return $this->_vmis;
	}
	public function delete(){
		$this->getMapper()->delete($this);
	}
	
	public function getImageByIdentifier($guid){
		$vapplists = new Default_Model_VALists();
		$vapplists->filter->vappversionid->equals($this->id);
		if( count($vapplists->items) === 0 ) return null;
		for($i=0; $i<count($vapplists->items); $i+=1){
			$vapplist = $vapplists->items[$i];
			$instance = $vapplist->getVMIinstance();
			if( !$instance )return null;
			if( trim($instance->guid) === trim($guid) ){
				return $instance;
			}
		}
	}
	
	public function isExpired(){
		if( time() < strtotime($this->_expireson) ){
			return false;
		}
		return true;
	}
	
	public function getVappLists(){
		if( $this->_vapplists === null ){
			$vapplists = new Default_Model_VALists();
			$vapplists->filter->vappversionid->equals($this->_id);
			if( count($vapplists->items) > 0 ){
				$this->_vapplists = $vapplists->items;
			}else{
				$this->_vapplists = array();
			}
		}
		return $this->_vapplists;
	}
}
