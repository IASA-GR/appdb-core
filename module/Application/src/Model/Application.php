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

class Application extends ApplicationBase {
	protected $_urls;
	protected $_countries;
	protected $_VOs;
	protected $_researchers;
	protected $_documents;
	protected $_middlewares;
	protected $_regions;
	protected $_relatedApps;
	protected $_keywords;
	protected $_ngis;
	protected $_modInfo;
	protected $_delInfo;
	protected $_categories;
	protected $_disciplines;
	protected $_hitcount;
	protected $_relcount;

	public function getRelcount() {
		if ( ! isnull($this->id) ) {
			if ( isnull($this->_relcount) ) {
				db()->setFetchMode(Zend_Db::FETCH_OBJ);
				$res = db()->query("SELECT relcount FROM app_release_count WHERE appid = ?", array($this->id))->toArray();
				if ( count($res) > 0 ) {
					$this->_relcount = $res[0]['hitcount'];
				}
			}
		}
		return $this->_relcount;
	}

	public function getHitcount() {
		if ( ! isnull($this->id) ) {
			if ( isnull($this->_hitcount) ) {
				db()->setFetchMode(Zend_Db::FETCH_OBJ);
				$res = db()->query("SELECT count AS hitcount FROM hitcounts WHERE appid = ?", array($this->id))->toArray();
				if ( count($res) > 0 ) {
					$this->_hitcount = $res[0]['hitcount'];
				}
			}
		}
		return $this->_hitcount;
	}

    public function clearLogo() {
        if ( ! isnull($this->id) ) {
            db()->query("DELETE FROM applogos WHERE appid = " . $this->id);
        }
	}

	public function getLogo() {
        if ( ! isnull($this->id) ) {
			$res = db()->query("SELECT logo FROM applogos WHERE appid = ?", array($this->id))->toArray();
			if ( count($res) > 0 ) {
				if ($res[0]['logo'] !== null) {
					$logo = stream_get_contents($res[0]['logo']);
					return $logo;
				} else {
					return null;
				}
			} else {
				return null;
			}
		} else {
			return null;
		}
	}

	public function setLogo($v) {
		if ( ! isnull($this->id) ) {
			$this->clearLogo();
			db()->query("INSERT INTO applogos (appid, logo) VALUES (" . $this->id . ", ?)", array($v));
		}
		return $this;
	}

	public function getNGIs() {
		if ($this->_ngis === null) {
			$ngis = new NGIs();
			$ids = array();
			foreach ($this->countries as $c) $ids[] = $c->id;
			if (count($ids) == 0) {
				$this->_ngis = array();
			} else {
				$ngis->filter->countryid->in($ids);
				$this->_ngis = $ngis;
			}
		}
		if (is_array($this->_ngis)) return $this->_ngis; else return $this->_ngis->_items;
	}

	public function getUrls() {
		if ($this->_urls === null) {
			$urls = new AppUrls();
			$urls->filter->appid->equals($this->id);
			$this->_urls = $urls;
		}
		return $this->_urls->items;
	}

	public function getCountries() {
		if ($this->_countries === null) {
			$cs = new AppCountries();
			$cs->filter->appid->equals($this->id);
			$this->_countries = $cs;
		}
		return $this->_countries->items;
	}

	public function getRegions() {
		if ($this->_regions === null) {
			$regs = new Regions();
			$rs = new AppCountries();
			$rs->filter->appid->equals($this->id);
			$ids = array();
			foreach ($rs->items as $r) $ids[] = $r->regionID;
			if (count($ids) == 0) {
				$this->_regions = array();
			} else {				
				$regs->filter->id->in($ids);
				$this->_regions = $regs;
			}
		}
		if (is_array($this->_regions)) return $this->_regions; else return $this->_regions->items;
	}

	public function getVOs() {
		if ($this->_VOs === null) {
			$vos = new AppVOs();
			$vos->filter->appid->equals($this->id);
			$ids = array();
			foreach ($vos->items as $vo) $ids[] = $vo->voID;
			if (count($ids) == 0) {
				$this->_VOs = array();
			} else {
				$v = new VOs();
				$v->filter->id->in($ids);
				$this->_VOs = $v;
			}
		}
		if (is_array($this->_VOs)) return $this->_VOs; else return $this->_VOs->items;
	}

	public function getResearchers() {
		if ($this->_researchers === null) {
			$r = new ResearchersApps();
			$r->filter->appid->equals($this->id);
			$ids = array();
			foreach ($r->items as $i) $ids[] = $i->researcherID;
			if (count($ids) == 0) {
				$this->_researchers = array();
			} else {
				$rr = new Researchers();
				$rr->filter->id->in($ids);
				$this->_researchers = $rr;
			}
		}
		if (is_array($this->_researchers)) return array(); else return $this->_researchers->items;
	}

	public function getDocuments() {
		if ($this->_documents === null) {
			$docs = new AppDocuments();
			$docs->filter->appid->equals($this->id);
			$this->_documents = $docs;
		}
		return $this->_documents->items;
	}

	public function getMiddlewares() {
		if ($this->_middlewares === null) {
				$appmws = new AppMiddlewares();
				$appmws->filter->appid->equals($this->id);
				$this->_middlewares = $appmws;
		}
		return $this->_middlewares->items;
//		if (is_array($this->_middlewares)) return $this->_middlewares; else return $this->_middlewares->items;
	}
	
	
//	public function getMiddlewares() {
//		if ($this->_middlewares === null) {
//			$mws = new Middlewares();
//			$appmws = new AppMiddlewares();
//			$appmws->filter->appid->equals($this->id);
//			$ids = array();
//			foreach ($appmws->items as $mw) $ids[] = $mw->middlewareID;
//			if (count($ids) == 0) {
//				$this->_middlewares = array();
//			} else {
//				$mws->filter->id->in($ids);
//				$this->_middlewares = $mws;
//			}
//		}
//		if (is_array($this->_middlewares)) return $this->_middlewares; else return $this->_middlewares->items;
//	}

	public function getRelatedApps() {
		if ( $this->_relatedApps === null ) {
			if ($this->id !== null) $this->_relatedApps = new RelatedApplications($this->id);
		}
		return $this->_relatedApps;
	}

	public function getKeywords() {
		return $this->_keywords;
	}

	public function setKeywords($v) {
		$this->_keywords = $v;

	}

	public function getPrimaryCategory() {
		foreach ($this->categories as $cat) {
			if ($cat->isPrimary) {
				return $cat->category;
			}
		}
		if (count($this->categories)>0) {
			return $this->categories[0]->category;
		} else {
			return null;
		}
	}

	public function getCategories() {
		if ($this->_categories === null) {
			if ( ( $this->categoryid == '' ) || ( is_array($this->categoryid) && count($this->categoryid) == 0 ) ) {
				$this->_categories = array();		
			} else {
				$ds = new AppCategories();
				$ds->filter->categoryid->in($this->categoryid)->and($ds->filter->appid->equals($this->id));
				if ( count($ds->items) > 0 ) {
					$this->_categories = $ds->items;
				} else $this->_categories = array();		
			}
		}
		return $this->_categories;
	}

	public function getDisciplines() {
		if ($this->_disciplines === null) {
			if ( ( $this->disciplineid == '' ) || ( is_array($this->disciplineid) && count($this->disciplineid) == 0 ) ) {
				$this->_disciplines = array();		
			} else {
				$ds = new Disciplines();
				$ds->filter->id->in($this->disciplineid);
				if ( count($ds->items) > 0 ) {
					$this->_disciplines = $ds->items;
				} else $this->_disciplines = array();		
			}
		}
		return $this->_disciplines;
	}

	public function toXML($recursive=false) {
		$this->getDiscipline();
		$this->getMiddlewares();
		$oldKeywords = null;
		if (is_array($this->_keywords)) {
			$oldKeywords = $this->_keywords;
			$this->_keywords = implode($this->_keywords);
		}
		$xml = parent::toXML($recursive);
		$x2 = "";
		if ($recursive) {
			$this->getVOs();
			$this->getRegions();
			$this->getCountries();
			$this->getResearchers();
			$this->getURLs();
			$this->getDocuments();
			$this->getMiddlewares();
		};
		if ( $this->_middlewares !== null ) foreach ($this->middlewares as $vo) { $x2.=$vo->toXML(); };
		if ( $this->_VOs !== null ) foreach ($this->VOs as $vo) { $x2.=$vo->toXML(); };
		if ( $this->_regions !== null ) foreach ($this->regions as $vo) { $x2.=$vo->toXML(); };
		if ( $this->_countries !== null ) foreach ($this->countries as $vo) { $x2.=$vo->toXML(); };
		if ( $this->_researchers !== null ) foreach ($this->researchers as $vo) {
			$r = $vo->toXML();
			$r=preg_replace('/<Researcher>/','<SciCon>',$r);
			$r=preg_replace('/<\/Researcher>/','</SciCon>',$r);
			$x2.=$r;
		};
		if ( $this->_urls !== null ) foreach ($this->urls as $vo) { $x2.=$vo->toXML(); };
		if ( $this->_documents !== null ) foreach ($this->documents as $vo) { $x2.=$vo->toXML(); };
		$x2.='<permalink>http://'.$_SERVER['APPLICATION_UI_HOSTNAME'].'/?p='.base64_encode('/apps/details?id='.$this->Id).'</permalink>';
		$x2.='<logo>http://'.$_SERVER['APPLICATION_UI_HOSTNAME'].'/apps/getlogo?id='.$this->Id.'</logo>';
		$xml = preg_replace("/<\/Application>/",$x2."</Application>",$xml);
		if ($oldKeywords !== null) $this->_keywords = $oldKeywords;
		return $xml;
	}

	public function save() {
		parent::save();
		if ($this->moderated) {
			if ( $this->_modInfo !== null ) $this->_modInfo->save();	
		} else {
			$mis = new AppModInfos();
			$mis->filter->id->equals($this->id);
			if ( count($mis->items) > 0 ) {
				$tmp = $mis->items[0];
				$mis->remove($tmp);
			}
		}
		if ($this->deleted) {
			if ( $this->_delInfo !== null ) $this->_delInfo->save();	
		} else {
			$dis = new AppDelInfos();
			$dis->filter->id->equals($this->id);
			if ( count($dis->items) > 0 ) {
				$tmp = $dis->items[0];
				$dis->remove($tmp);
			}
		}	
	}

	public function getModInfo() {
		if ( $this->_modInfo === null ) {
			$mis = new AppModInfos();
			$mis->filter->appid->equals($this->id);
			if ( count($mis->items) > 0 ) {
				$this->_modInfo	= $mis->items[0];
			} else {
				$this->_modInfo = new AppModInfo();
				$this->_modInfo->appid = $this->id;
			}
		}
		return $this->_modInfo;
	}	

	public function getDelInfo() {
		if ( $this->_delInfo === null ) {
			$dis = new AppDelInfos();
			$dis->filter->appid->equals($this->id);
			if ( count($dis->items) > 0 ) {
				$this->_delInfo = $dis->items[0];
			} else {
				$this->_delInfo = new AppDelInfo();
				$this->_delInfo->appid = $this->id;
			}
		}
		return $this->_delInfo;
	}
}
