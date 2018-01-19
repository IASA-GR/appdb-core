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

class RepositoryXSLT {
	public static function transform($data,$type){
		$xslt = APPLICATION_PATH . "/configs/repository/0.1/xslt/" . $type;
		$xslt = $xslt . ".xsl";
		if (file_exists($xslt)) {
			$xsl = new DOMDocument();
			$xsl->load($xslt);
			$xml = new DOMDocument();
			$xml->loadXML($data, LIBXML_NSCLEAN | LIBXML_COMPACT);
			$proc = new XSLTProcessor();
			$proc->registerPHPFunctions();
			$proc->importStylesheet($xsl);
			$data = $proc->transformToXml( $xml );
			$data = str_replace('<?xml version="1.0"?'.'>', '', $data);
		} else {
			error_log('Cannot find '.$xslt);
		}
		return $data;
	}
}
class RepositoryError{
	public static function toXML($resource){
		$s =  '<repository datatype="item" content="release" error="';
		if( $resource instanceof RestResource ){
			$s .= $resource->getError() . '"';
		} else {
			$s .= $resource;
		}
		$s .=  '" ></repository>';
		return $s;
	}
}

class RestProductReleaseList extends RestROResourceList {
	public function getRawData(){
		$rs = new Repository_Model_MetaProductReleases();
		//TODO: replace equals(0) with is(null) if foreign key fixed
		$f1 = new Repository_Model_MetaProductRepoAreasFilter();
		$f1->swId->equals($this->getParam("swid"));
		$f2 = new Repository_Model_MetaProductReleasesFilter();
		$f2->parent_id->equals(0);
		$rs->filter->chain($f1->chain($f2, "AND"), "AND");
		$rs->filter->orderBy( "meta_product_repo_area.timestampCreated ASC, meta_product_release.displayIndex ASC"  );
		return $rs;
	}
	public function get() {
		if ( parent::get() !== false ) {
			$rs = $this->getParam("data");
			if( !$rs ){
				$rs = $this->getRawData();
			}
			
			$s = '';
			if ( count($rs->items) > 0 ) {
				foreach($rs->items as $rel) {
					$s2 = $rel->toXML(true);					
					$children = $rel->getChildren();
					$s3 = '';
					foreach($children as $child) {
						$s3 .= $child->toXML(true);
					}
					$s2 = str_replace('</MetaProductRelease>', $s3 . '</MetaProductRelease>', $s2);
					$s2 = str_replace('</MetaProductRelease>', '<utclastproductiondate>' . $rel->getUTCLastProductionBuild() . '</utclastproductiondate></MetaProductRelease>', $s2);
					$s2 = str_replace("</MetaProductRelease>", "<utcservertime>" . gmdate("Y-m-d H:i:s", (int)gmdate('U')	) . "</utcservertime></MetaProductRelease>", $s2);
					$s .= $s2;
				}
				$s = '<repository datatype="list" content="productrelease">' . $s . '</repository>';
				return RepositoryXSLT::transform($s,"productreleases");
			} else {
				$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
				return false;
			}
		}
	}
	public function getDataType(){
        return "release";
    }
    protected function _list() {
        return $this->get();
    }
	
}

class RestProductReleaseItem extends RestROResourceItem{
	public function getRawData(){
		$rs = new Repository_Model_MetaProductReleases();
		//TODO: replace equals(0) with is(null) if foreign key fixed
		$rs->filter->id->equals($this->getParam("id"));
		if( count($rs->items) == 0 ){
			return null;
		}
		return $rs->items[0];
	}
	public function get() {
		if ( parent::get() !== false ) {			
			$rel = $this->getParam("data");
			if( !$rel ){
				$rel = $this->getRawData();
			}
			$s = '';
			if ( $rel !== null ) {
					$s2 = $rel->toXML(true);
					$s2 = str_replace('</MetaProductRelease>', "<swId>" . $rel->getSoftwareId() . "</swId>" . '</MetaProductRelease>', $s2);
					$poas = $rel->getPOAs();
					$s3 = '';
					foreach( $poas as $poa ) {
						$s3 = $poa->toXML();
						$s4 = '';
						$target = $poa->getTarget();
						
						if( $target ){
							$s4 = $target->toXML(true);
						}
						$os = $target->getOs();
						if( $os ){
							$dmethods = $os->getDeployMethods();
							if( $dmethods ){
								$s5 = '';
								foreach( $dmethods as $method){
									$s5 .= $method->getDeployMethod()->toXML(true);
								}
								$s4 = str_replace('</CommRepoAllowedPlatformCombination>', $s5 . '</CommRepoAllowedPlatformCombination>', $s4);
							}
						}
						$packs = $poa->getPackages();
						foreach( $packs as $pack ) {
							$s4 .= $pack->toXML(); 
						}
						$s3 = str_replace('</MetaPoaRelease>', $s4 . '</MetaPoaRelease>', $s3);
						if( $rel->currentStateId != 1){
							$repourls = $poa->getRepositoryUrls();
							if( $repourls ){
								$s3 = str_replace('</MetaPoaRelease>', $repourls . '</MetaPoaRelease>', $s3);
							}
						}
						$s2 = str_replace('</MetaProductRelease>', $s3 . '</MetaProductRelease>', $s2);
					}
					
					$s .= $s2;
					$contacts = $rel->getContacts();
					if( $contacts !== null ){
						$s6 = '';
						foreach($contacts as $contact){
							$s6 .= $contact->toXML(true);
						}
						$s = str_replace('</MetaProductRelease>', $s6 . '</MetaProductRelease>', $s);
					}
					$s = str_replace('</MetaProductRelease>', '<utclastproductiondate>' . $rel->getUTCLastProductionBuild() . '</utclastproductiondate></MetaProductRelease>', $s);
					$s = str_replace("</MetaProductRelease>", "<utcservertime>" . gmdate("Y-m-d H:i:s", (int)gmdate('U')	) . "</utcservertime></MetaProductRelease>", $s);
				$s = '<repository datatype="item" content="productrelease" ' . ( ( isset($_SESSION["default"]) && isset($_SESSION["default"]["userid"]) )?'userid="'.$_SESSION["default"]["userid"].'"':'' ) . ' host="'.$_SERVER['HTTP_HOST'].'" >' . $s . '</repository>';
				
				return RepositoryXSLT::transform($s,"productrelease");
			} else {
				$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
				return false;
			}
		}
	}
	public function getDataType() {
        return "productrelease";
    }
}

class RestProductReleaseLatestItem extends RestProductReleaseItem{
	public function getRawData(){
		$rs = new Repository_Model_MetaProductReleases();
		//TODO: replace equals(0) with is(null) if foreign key fixed
		$f1 = new Repository_Model_MetaProductRepoAreasFilter();
		$f1->swId->equals($this->getParam("swid"));
		$f2 = new Repository_Model_MetaProductReleasesFilter();
		$f2->parent_id->equals(0);
		$rs->filter->chain($f1->chain($f2, "AND"), "AND");
		
		if( count($rs->items) == 0 ){
			return null;
		}
		return $rs->items[0];
	}
}

class RestTargetItem extends RestROResourceItem{
	public function getRawData(){
		$rs = new Repository_Model_CommRepoAllowedPlatformCombinations();
		//TODO: replace equals(0) with is(null) if foreign key fixed
		if( $this->getParam("id") ) {
			$rs->filter->id->equals($this->getParam("id"));
		}
		if( count($rs->items) == 0 ){
			return null;
		}
		return $rs->items[0];
	}
	public function get() {
		if ( parent::get() !== false ) {			
			$rel = $this->getParam("data");
			if( !$rel ){
				$rel = $this->getRawData();
			}
			$s = '';
			if ( $rel !== null ) {					
				$s = '<repository datatype="item" content="target">' . $rel->toXML() . '</repository>';
				return RepositoryXSLT::transform($s,"target");
			} else {
				$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
				return false;
			}
		}
	}
	public function getDataType() {
        return "target";
    }
}

class RestTargetList extends RestROResourceList{
	public function getRawData(){
		$rs = new Repository_Model_CommRepoAllowedPlatformCombinations();
		//TODO: replace equals(0) with is(null) if foreign key fixed
		return $rs;
	}
	public function get() {
		if ( parent::get() !== false ) {
			$rs = $this->getParam("data");
			if( !$rs ){
				$rs = $this->getRawData();
			}
			$s = '';
			if ( count($rs->items) > 0 ) {
				foreach($rs->items as $rel) {
					$s1 = $rel->toXML(true);
					$osses = $rel->getOs();
					if ( $osses ) {
						$methods = $osses->getDeployMethods();
					} else {
						error_log("Warning: Repository module: Target OS undefined; no deployment methods available");
						$methods = array();
					}
					$s2 = '';
					foreach($methods as $mth){
						$s2 .= $mth->getDeployMethod()->toXML();
					}
					$s1 = str_replace('</CommRepoAllowedPlatformCombination>', $s2 . '</CommRepoAllowedPlatformCombination>', $s1);
					$s .= $s1;
				}
				$s = '<repository datatype="list" content="target">' . $s . '</repository>';
				return RepositoryXSLT::transform($s,"targets");
			} else {
				$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
				return false;
			}
		}
	}
	public function getDataType() {
        return "target";
    }
    protected function _list() {
        return $this->get();
    }
}

class RestProductReleasePropertyItem extends RestResourceItem{
	public function getRawData(){
		$prop = $this->getParam("name");
		$rs = new Repository_Model_MetaProductReleases();
		//TODO: replace equals(0) with is(null) if foreign key fixed
		$rs->filter->id->equals($this->getParam("id"));
		if( count($rs->items) == 0 ){
			return null;
		}
		return $rs->items[0]->$prop;
	}
	public function get() {
		if ( parent::get() !== false ) {
			$rs = $this->getParam("data");
			if( !$rs ){
				$rs = $this->getRawData();
			}
			if( $rs !== null ){
				$s =  '<repository datatype="item" content="release">';
				$s .= '<MetaProductRelease id="'.$this->getParam("id").'">';
				$s .= '<' . $this->getParam("name"). '><![CDATA[' . $rs . ']]></'.$this->getParam("name").'>';
				$s .= '</MetaProductRelease></repository>';
				return $s;
			} else {
				$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
				return false;
			}
		}	
	}
	protected function _options() {
        $options = array();
        $options[] = RestMethodEnum::RM_GET;
        $options[] = RestMethodEnum::RM_POST;
        return $options;
    }
	 /**
     * handles PUT and POST HTTP methods to REST requests
     *
     * @param integer $method the method enumeration according to RestMethodEnum
     *
     * @return iRestResponse
     *
     */
	private function putpost($method) {
		
		$id = $this->getParam("id");
		$name = $this->getParam("name");
		$value = $this->getParam("value");
		$rs = new Repository_Model_MetaProductReleases();
		//TODO: replace equals(0) with is(null) if foreign key fixed
		$rs->filter->id->equals($id);
		$found = ( (count($rs->items)>0)?true:false);
		
		if ( !$found ) {
			$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
			return false;
		}
		$r = $rs->items[0];
		if( ! ($r instanceof Repository_Model_MetaProductRelease) ){
				$this->setError(RestErrorEnum::RE_BACKEND_ERROR);
				return false;
		}
		db()->beginTransaction();	
		try{
			$r->$name = $value;
			$r->save();
			db()->commit();
		}catch(Exception $e){
			db()->rollBack();
			$this->setError(RestErrorEnum::RE_BACKEND_ERROR, $e->getMessage());
			return false;
		}
		
		$res = new RestProductReleaseItem(array("id" => $id), $this);
		$ret = $res->get();
		return $ret;
	}

    /**
     * overrides RestResource::put()
     */
	public function put() {
		if (  parent::put() !== false ) {
			return new XMLFragmentRestResponse($this->putpost(RestMethodEnum::RM_PUT), $this);
		} else return false;
	}

    /**
     * overrides RestResource::post()
     */
	public function post() {
		if ( parent::post() !== false ) {
			return new XMLFragmentRestResponse($this->putpost(RestMethodEnum::RM_POST), $this);
		} else return false;
	}
	
	public function getDataType() {
        return "release";
    }
	/**
     * realization of authorize() from iRestAuthModule
     */
    public function authorize($method) {
        $res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
        case RestMethodEnum::RM_POST:
			$res = true;
            break;
        case RestMethodEnum::RM_PUT:
			$res = false;
            break;
        case RestMethodEnum::RM_DELETE:
            $res = false;
            break;
        }
        return $res;
    }
}

class RestRepoConfigItem extends RestROResourceItem{
	public function getRawData(){
		$rs = new Repository_Model_Config();
		//TODO: replace equals(0) with is(null) if foreign key fixed
		if( $this->getParam("id") ) {
			$rs->filter->id->equals($this->getParam("id"));
		}else if( $this->getParam("name") ) {
			$rs->filter->name->equals($this->getParam("name"));
		}
		
		if( count($rs->items) == 0 ){
			return null;
		}
		return $rs->items[0];
	}
	public function get() {
		if ( parent::get() !== false ) {			
			$rel = $this->getParam("data");
			if( !$rel ){
				$rel = $this->getRawData();
			}
			$s = '';
			if ( $rel !== null ) {					
				$s = '<repository datatype="item" content="config">' . $rel->toXML() . '</repository>';
				return $s;
			} else {
				$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
				return false;
			}
		}
	}
	public function getDataType() {
        return "config";
    }
}

class RestRepoConfigList extends RestROResourceList{
	public function getRawData(){
		$rs = new Repository_Model_Config();
		if( $this->getParam("id") ) {
			if(is_array($this->getParam("id"))){
				$rs->filter->id->in($this->getParam("id"));
			}else{
				$rs->filter->id->equals($this->getParam("id"));
			}
		}else if( $this->getParam("name") ) {
			$rs->filter->name->equals($this->getParam("name"));
		}
		
		//TODO: replace equals(0) with is(null) if foreign key fixed
		return $rs;
	}
	public function get() {
		if ( parent::get() !== false ) {
			$rs = $this->getParam("data");
			if( !$rs ){
				$rs = $this->getRawData();
			}
			if ( count($rs->items) > 0 ) {
				$s = '<repository datatype="list" content="configentry">' . $rs->toXML() . '</repository>';
				return RepositoryXSLT::transform($s,"configs");
			} else {
				$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
				return false;
			}
		}
	}
	public function getDataType() {
        return "config";
    }
    protected function _list() {
        return $this->get();
    }
}

class RestRepositoryAreaItem extends RestROResourceItem{
	public function getRawData(){
		$rs = new Repository_Model_MetaProductRepoAreas();
		//TODO: replace equals(0) with is(null) if foreign key fixed
		if( $this->getParam("id") ) {
			$rs->filter->id->equals($this->getParam("id"));
		}
		if( count($rs->items) == 0 ){
			return null;
		}
		return $rs->items[0];
	}
	public function get() {
		if ( parent::get() !== false ) {			
			$rel = $this->getParam("data");
			if( !$rel ){
				$rel = $this->getRawData();
			}
			
			if ( $rel !== null ) {
				$releases = $rel->getReleases();
				if( $releases ){
					$s2 = '';
					foreach($releases as $release){
						$s3 = $release->toXML(true);
						$s4 = '';
						$s5 = '';
						$targets = $release->getSupportedTargets();
						if( $targets ){
							for($i=0; $i<count($targets); $i+=1){
								$target = $targets[$i];
								$s4 .= $target->toXML(true);
							}
							$s3 = str_replace("</MetaProductRelease>", $s4 . "</MetaProductRelease>", $s3);
						}
						//begin: this should be replaced with a db view. Unique poas of releases under repository area.
						$poas = $release->getPOAs();
						if( $poas ){
							$s5 = '';
							foreach($poas as $poa){
								$s6 = '';
								$s7 = $poa->toXML(true);
								$pcks = $poa->getPackages();
								if($pcks){
									foreach($pcks as $pck){
										$s6 = $pck->toXML();
										$s7 = str_replace("</MetaPoaRelease>", $s6 . "</MetaPoaRelease>", $s7);
									}
								}
								if( $release->currentStateId != 1){
									$repourls = $poa->getRepositoryUrls();
									if( $repourls ){
										$s7 = str_replace("</MetaPoaRelease>", $repourls . "</MetaPoaRelease>", $s7);
									}
								}
								$s5 .= $s7;
							}
							$s3 = str_replace("</MetaProductRelease>", $s5 . "</MetaProductRelease>", $s3);
							$s3 = str_replace("</MetaProductRelease>", "<utclastproductiondate>" . $release->getUTCLastProductionBuild() . "</utclastproductiondate></MetaProductRelease>", $s3);
							$s3 = str_replace("</MetaProductRelease>", "<utcservertime>" . gmdate("Y-m-d H:i:s", (int)gmdate('U')	) . "</utcservertime></MetaProductRelease>", $s3);
						}
						//end: this should be replaced with a db view. Unique poas of releases under repository area.
						$s2 .= $s3;
					}
				}
				$contacts = $rel->getContacts();
				if( $contacts ){
					$s4 = '';
					foreach($contacts as $contact){
						$s4 .= $contact->toXML(true);
					}
					
				}
				$s = $rel->toXML(true);
				$s = str_replace("</MetaProductRepoArea>", $s4 . "</MetaProductRepoArea>", $s);
				$s = str_replace("</MetaProductRepoArea>", $s2 . "</MetaProductRepoArea>", $s);
				$s = str_replace("</MetaProductRepoArea>", "<utclastproductiondate>" . $rel->getUTCLastProductionBuild() . "</utclastproductiondate></MetaProductRepoArea>", $s);
				$s = str_replace("</MetaProductRepoArea>", "<utcservertime>" . gmdate("Y-m-d H:i:s", (int)gmdate('U')	) . "</utcservertime></MetaProductRepoArea>", $s);
				$s = '<repository datatype="item" content="repositoryarea" ' . ( ( isset($_SESSION["default"]) && isset($_SESSION["default"]["userid"]) )?'userid="'.$_SESSION["default"]["userid"].'"':'' ) . ' host="'.$_SERVER['HTTP_HOST'].'" >' . $s . '</repository>';
				//return $s;
				return RepositoryXSLT::transform($s,"repoarea");
			} else {
				$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
				return false;
			}
		}
	}
	public function getDataType() {
        return "repositoryarea";
    }
}

class RestRepositoryAreaList extends RestROResourceList{
	public function getRawData(){
		$rs = new Repository_Model_MetaProductRepoAreas();
		$swid = $this->getParam("swid");
		if( isset($swid) ){
			$rs->filter->swId->equals($swid);
		}
		$rs->filter->orderBy( "timestampInserted DESC" );
		return $rs;
	}
	public function get() {
		if ( parent::get() !== false ) {
			$rs = $this->getParam("data");
			if( !$rs ){
				$rs = $this->getRawData();
			}
			$s = '';
			if ( count($rs->items) > 0 ) {
				$s1 = '';
				$s2 = '';
				foreach($rs->items as $rel) {
					$s2 = $rel->toXML(true);
					$releases = $rel->getReleases();
					if( count($releases) > 0 ){
						$s3 = '';
						foreach($releases as $release){
							$s3 .= $release->toXML(true);
						}
						$s2 = str_replace("</MetaProductRepoArea>", $s3 . "</MetaProductRepoArea>",$s2);
					}
					$s2 = str_replace("</MetaProductRepoArea>", "<utclastproductiondate>" . $rel->getUTCLastProductionBuild() . "</utclastproductiondate></MetaProductRepoArea>",$s2);
					$s2 = str_replace("</MetaProductRepoArea>", "<utcservertime>" . gmdate("Y-m-d H:i:s", (int)gmdate('U')	) . "</utcservertime></MetaProductRepoArea>", $s2);
					$s1 .= $s2;
				}
				$s = '<repository datatype="list" content="repositoryarea">' . $s1 . '</repository>';
				return RepositoryXSLT::transform($s,"repoareas");
			} else {
				$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
				return false;
			}
		}
	}
	public function getDataType() {
        return "repositoryarea";
    }
    protected function _list() {
        return $this->get();
    }
}

class RestRepositoryAreaLatestItem extends RestRepositoryAreaItem{
	public function getRawData(){
		$rs = new Repository_Model_MetaProductRepoAreas();
		//TODO: replace equals(0) with is(null) if foreign key fixed
		if( $this->getParam("swid") ) {
			$rs->filter->swId->equals($this->getParam("swid"));
		}
		if( count($rs->items) == 0 ){
			return null;
		}
		return $rs->items[0];
	}
}

class RestRepositoryAreaPropertyItem extends RestResourceItem{
	public function getRawData(){
		$prop = $this->getParam("name");
		$rs = new Repository_Model_MetaProductRepoAreas();
		//TODO: replace equals(0) with is(null) if foreign key fixed
		$rs->filter->id->equals($this->getParam("id"));
		if( count($rs->items) == 0 ){
			return null;
		}
		return $rs->items[0]->$prop;
	}
	public function get() {
		if ( parent::get() !== false ) {
			$rs = $this->getParam("data");
			if( !$rs ){
				$rs = $this->getRawData();
			}
			if( $rs !== null ){
				$s =  '<repository datatype="item" content="repositoryarea">';
				$s .= '<MetaProductRepoArea id="'.$this->getParam("id").'">';
				$s .= '<' . $this->getParam("name"). '><![CDATA[' . $rs . ']]></'.$this->getParam("name").'>';
				$s .= '</MetaProductRepoArea></repository>';
				return $s;
			} else {
				$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
				return false;
			}
		}	
	}
	protected function _options() {
        $options = array();
        $options[] = RestMethodEnum::RM_GET;
        $options[] = RestMethodEnum::RM_POST;
        return $options;
    }
	 /**
     * handles PUT and POST HTTP methods to REST requests
     *
     * @param integer $method the method enumeration according to RestMethodEnum
     *
     * @return iRestResponse
     *
     */
	private function putpost($method) {
		
		$id = $this->getParam("id");
		$name = $this->getParam("name");
		$value = $this->getParam("value");
		$rs = new Repository_Model_MetaProductRepoAreas();
		//TODO: replace equals(0) with is(null) if foreign key fixed
		$rs->filter->id->equals($id);
		$found = ( (count($rs->items)>0)?true:false);
		
		if ( !$found ) {
			$this->setError(RestErrorEnum::RE_ITEM_NOT_FOUND);
			return false;
		}
		$r = $rs->items[0];
			if( ! ($r instanceof Repository_Model_MetaProductRepoArea) ){
				$this->setError(RestErrorEnum::RE_BACKEND_ERROR);
				return false;
		}
		db()->beginTransaction();	
		try{
			$r->$name = $value;
			$r->save();
			db()->commit();
		}catch(Exception $e){
			db()->rollBack();
			$this->setError(RestErrorEnum::RE_BACKEND_ERROR, $e->getMessage());
			return false;
		}
		
		$res = new RestRepositoryAreaItem(array("id" => $id), $this);
		$ret = $res->get();
		return $ret;
	}

    /**
     * overrides RestResource::put()
     */
	public function put() {
		if (  parent::put() !== false ) {
			return new XMLFragmentRestResponse($this->putpost(RestMethodEnum::RM_PUT), $this);
		} else return false;
	}

    /**
     * overrides RestResource::post()
     */
	public function post() {
		if ( parent::post() !== false ) {
			return new XMLFragmentRestResponse($this->putpost(RestMethodEnum::RM_POST), $this);
		} else return false;
	}
	
	public function getDataType() {
        return "repositoryarea";
    }
	/**
     * realization of authorize() from iRestAuthModule
     */
    public function authorize($method) {
        $res = false;
        switch ($method) {
        case RestMethodEnum::RM_GET:
        case RestMethodEnum::RM_POST:
			$res = true;
            break;
        case RestMethodEnum::RM_PUT:
			$res = false;
            break;
        case RestMethodEnum::RM_DELETE:
            $res = false;
            break;
        }
        return $res;
    }
}
?>
