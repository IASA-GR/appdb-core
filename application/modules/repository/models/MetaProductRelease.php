<?php
// PUT YOUR CUSTOM CODE HERE
class Repository_Model_MetaProductRelease extends Repository_Model_MetaProductReleaseBase
{
	protected $_children;
	protected $_POA;
	protected $_swid;
	protected $_supportedtargets;
	protected $_contacts;
	
	public function getChildren() {
		if ($this->_children === null) {
			$r = new Repository_Model_MetaProductReleases();
			$r->filter->parent_id->equals($this->id);
			$this->_children = $r->items;
		}
		return $this->_children;
	}
	
	public function getPOAs(){
		if ($this->_POA === null) {
			$r = new Repository_Model_MetaPoaReleases();
			$r->filter->productReleaseId->equals($this->id);
			$this->_POA = $r->items;
		}
		return $this->_POA;
	}
	
	public function getSoftwareId(){
		if ($this->_swid === null) {
			$r = new Repository_Model_MetaProductRepoAreas();
			$r->filter->id->equals($this->repoAreaId);
			if( count($r->items) > 0 ){
				$this->_swid = $r->items[0]->swId;
			}
		}
		return $this->_swid;
	}
	
	public function getSupportedTargets(){
		if ($this->_supportedtargets === null) {
			$r = new Repository_Model_MetaPoaReleases();
			$r->filter->productReleaseId->equals($this->id);
			if( count($r->items) > 0 ){
				$targets = array();
				for($i=0; $i < count($r->items); $i+=1){
					$poa = $r->items[$i];
					$target = $poa->getTarget();
					if( $target ){
						$targets[] = $target;
					}
				}
				if( count($targets)>0){
					$this->_supportedtargets  = $targets;
				}
			}
		}
		return $this->_supportedtargets ;
	}
	
	public function getContacts(){
		if( $this->_contacts === null ){
			$r = new Repository_Model_MetaContacts();
			$r->filter->assocId->equals($this->id)->and($r->filter->assocEntity->equals("release"));
			if( count($r->items) > 0 ){
				$this->_contacts = $r->items;
			}
		}
		return $this->_contacts;
	}
	
	public function getUTCLastProductionBuild(){
		if( $this->timestampLastProductionBuild != "0000-00-00 00:00:00" ){
			return gmdate("Y-m-d H:i:s", strtotime($this->timestampLastProductionBuild));
		}
		return "";
	}
}
