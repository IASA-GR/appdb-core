<?php
// PUT YOUR CUSTOM CODE HERE
class Repository_Model_MetaPoaRelease extends Repository_Model_MetaPoaReleaseBase
{
	protected $_packages;
	protected $_target;
	protected $_repositoryurls;
	
	public function getPackages(){
		if( $this->_packages === null ){
			$r = new Repository_Model_MetaPoaReleasePackages();
			$r->filter->poaId->equals($this->id);
			$this->_packages = $r->items;
		}
		return $this->_packages;
	}
	
	public function getTarget(){
		if( $this->_target === null ){
			$r = new Repository_Model_CommRepoAllowedPlatformCombinations();
			$r->filter->id->equals($this->targetPlatformCombId);
			if( count($r->items) > 0 ) {
				$this->_target = $r->items[0];
			}else{
				$this->_target = null;
			}
		}
		return $this->_target;
	}
	
	public function getRepositoryUrls(){
		if( $this->_repositoryurls === null ){
			$app = Zend_Registry::get("app");
			//$this->_repositoryurls = web_get_contents("http://commrepo/repofiles/getrepositorydata/" . $this->id);
			$this->_repositoryurls = web_get_contents($app["commrepoUrl"] . "repofiles/getrepositorydata/" . $this->id);
		}
		return $this->_repositoryurls;
	}
}
