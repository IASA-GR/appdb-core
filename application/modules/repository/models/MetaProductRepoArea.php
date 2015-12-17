<?php
// PUT YOUR CUSTOM CODE HERE
class Repository_Model_MetaProductRepoArea extends Repository_Model_MetaProductRepoAreaBase
{
	protected $_productreleases;
	protected $_contacts;
	
	public function getReleases(){
		if ($this->_productreleases === null) {
			$r = new Repository_Model_MetaProductReleases();
			$r->filter->repoAreaId->equals($this->id);
			$r->filter->orderBy( array("displayIndex DESC","timestampInserted DESC") );
			$this->_productreleases = $r->items;
		}
		return $this->_productreleases;
	}
	
	public function getContacts(){
		if ($this->_contacts === null) {
			$r = new Repository_Model_MetaContacts();
			$r->filter->assocId->equals($this->id)->and($r->filter->assocEntity->equals("area"));
			if( count($r->items) > 0 ){
				$this->_contacts = $r->items;
			}else{
				$this->_contacts = array();
			}
			/*$r = new Repository_Model_VMetaProductRepoAreaContacts();
			$r->filter->repoareaid->equals($this->id);
			$this->_contacts = $r->items;*/
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
