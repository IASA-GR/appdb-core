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

class Researcher extends ResearcherBase
{
	protected $_inbox;
	protected $_privs;
	protected $_applications;
	protected $_contacts;
	protected $_region;
	protected $_docCount;
	protected $_ngis;
	protected $_publications;
    protected $_primaryContact;
	protected $_delInfo;
	protected $_cnames;
	protected $_vos;
	protected $_vocontacts;
	protected $_actorGroups;
	protected $_accessTokens;
	
    public function clearImage() {
		if ( ! isnull($this->id) ) {
            db()->query("DELETE FROM researcherimages WHERE researcherid = " . $this->id);
        }   
	}

	public function getImage() {
        if ( ! isnull($this->id) ) {
			db()->setFetchMode(Zend_Db::FETCH_OBJ);
			$res = db()->query("SELECT image FROM researcherimages WHERE researcherid = " . $this->id)->fetchAll();
			if ( count($res) > 0 ) {
				if ($res[0]->image !== null) {
					$image = stream_get_contents($res[0]->image);
					return $image;
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

	public function setImage($v) {
		if ( ! isnull($this->id) ) {
			$this->clearImage();
			try {
				db()->query("INSERT INTO researcherimages (researcherid, image) VALUES (?, ?)", array($this->id, $v));
			} catch (Exception $e) {
				error_log('[Researcher::setImage]: ' . $e->getMessage());
			}
		}
		return $this;
	}

    public function getInbox()
	{
		if ( $this->_inbox === null ) {
			$inbox = new Messages();
			$inbox->filter->orderBy('senton DESC');
			$inbox->filter->receiverid->equals($this->id);
			$this->_inbox = $inbox->refresh();
		}
		return $this->_inbox;
	}

	public function setLastLogin($v) {
		if (is_numeric($v)) { //asume UNIX timestamp
			global $application;
			$db = $application->getBootstrap()->getResource('db');
			$db->setFetchMode(Zend_Db::FETCH_BOTH);
			$res = $db->query("SELECT TIMESTAMP 'epoch' + $v * INTERVAL '1 second'")->fetchAll();
			if ( count($res) > 0 ) {
				$this->_lastLogin = $res[0][0];
			}
			return $this;
		} else { //asume literal value
			return parent::setLastLogin($v);
		}
	}

	public function getRole() {
		return parent::getPositionType();
	}

	public function setRole($v) {
		return parent::setPositionType($v);
	}

    public function getFullname() {
        return $this->firstname.' '.$this->lastname;
	}

	public function getRegion() {
		if ($this->_region === null) {
			$regs = new Regions();
			$rs = new Countries();
			$rs->filter->id->equals($this->countryid);
			if ( count($rs->items) > 0 ) {
				$id = $rs->items[0]->regionID;
				$regs->filter->id->equals($id);
				$this->_region = $regs->items[0];
			} else $this->_region = null;
		}
		return $this->_region;
	}

	public function getNGIs() {
		if ( $this->_ngis === null ) {
			$ngis = new NGIs();
			$ngis->filter->countryid->equals($this->countryID);
			$this->_ngis=$ngis->items;
		}
		return $this->_ngis;
	}

	public function getVOMemberships() {
		if ( $this->_vos === null ) {
			$vos = new VOMembers();
			$vos->filter->researcherid->equals($this->_id);
			$this->_vos = $vos->items;
		}
		return $this->_vos;
	}

	public function getVOContacts() {
		if( $this->_vocontacts == null ) {
			$vos = new VOContacts();
			$vos->filter->researcherid->equals($this->_id);
			$this->_vocontacts = $vos->items;
		}
		return $this->_vocontacts;
	}

	public function getPublications()
	{
		if ($this->_publications === null) {
			$docs = new IntAuthors();
			$fapp = new ApplicationsFilter();
			$fapp->deleted->equals(false)->and($fapp->moderated->equals(false));
			$docs->filter->authorid->equals($this->id);
			$docs->filter->chain($fapp, "AND");
			$p = array();
			foreach ($docs->items as $i) $p[] = $i->appDocument;
			$this->_publications = $p;
			$this->_docCount = count($p);
		}
		return $this->_publications;
	}

	public function getDocCount() {
		if ($this->_docCount === null) $this->getPublications();
		return $this->_docCount;
	}

    public function getPrimaryContact() {
        if ($this->_primaryContact === null) {
            $cs = new Contacts();
            $cs->filter->researcherid->equals($this->id)->and($cs->filter->isprimary->equals(true));
            if ( count($cs->items) > 0 ) {
                $this->_primaryContact = $cs->items[0]->data;
            } else {
                if ( $this->getAccountType() == 1 && $this->getID() != '' ) {
                    db()->setFetchMode(Zend_Db::FETCH_BOTH);
                    $res = db()->query("SELECT contacts.data FROM contacts INNER JOIN researchers ON researchers.id = contacts.researcherid INNER JOIN apikeys ON apikeys.ownerid = researchers.id WHERE contacts.contacttypeid=7 AND contacts.isprimary IS TRUE AND apikeys.sysaccountid = " . $this->getID())->fetchAll();
                    if ( count($res) > 0 ) {
                        $this->_primaryContact = $res[0][0];
                    }
                }
            }
        }
        return $this->_primaryContact;
    }

	public function getCnames() {
		if ($this->_cnames === null) {
			$cs = new ResearcherCnames();
			$cs->filter->researcherid->equals($this->id);
			$this->_cnames= $cs;
		}
		return $this->_cnames->items;
	}

	public function getContacts() {
		if ($this->_contacts === null) {
			$cs = new Contacts();
			$cs->filter->researcherid->equals($this->id);
			$this->_contacts = $cs;
		}
		return $this->_contacts->items;
	}

	public function getApplications($type = null) {
		if (($type === null) || ($type === 0)) {
			if ($this->_applications === null) {
				$rs = new Applications();
				$f = new ResearchersFilter();
				$f->id->numequals($this->id);
				$ff = new ApplicationsFilter();
				$ff->owner->numequals($this->id);
				$rs->filter->chain($f->chain($ff, "OR"), "AND");
				if (count($rs->items) == 0) {
					$this->_applications = array();
				} else {
					$this->_applications = $rs->items;
				}
			}
			if (is_array($this->_applications)) return $this->_applications; else return $this->_applications->items;
		}
	}

	public function getApplications2($type = null) {
		if (($type === null) || ($type === 0)) {
			if ($this->_applications === null) {
				$rs = new ResearchersApps();
				$rs->filter->researcherid->equals($this->id);
				$ids = array();
				foreach($rs->items as $i) $ids[] = $i->appID;
				if (count($ids) == 0) {
					$this->_applications = array();
				} else {
					$apps = new Applications;
					$apps->filter->id->in($ids)->and($apps->filter->moderated->equals(false))->and($apps->filter->deleted->equals(false));
					$this->_applications = $apps;
				}
			}
			if (is_array($this->_applications)) return $this->_applications; else return $this->_applications->items;
		}
	}

    public function getDelInfo() {
		if ( $this->_delInfo === null ) {
			$dis = new PplDelInfos();
			$dis->filter->researcherid->equals($this->id);
			if ( count($dis->items) > 0 ) {
				$this->_delInfo = $dis->items[0];
			} else {
				$this->_delInfo = new PplDelInfo();
				$this->_delInfo->researcherid = $this->id;
			}
		}
		return $this->_delInfo;
	}

    public function save() {
		parent::save();
		if ($this->deleted) {
			if ( $this->_delInfo !== null ) $this->_delInfo->save();	
		} else {
			$dis = new PplDelInfos();
			$dis->filter->id->equals($this->id);
			if ( count($dis->items) > 0 ) {
				$tmp = $dis->items[0];
				$dis->remove($tmp);
			}
		}	
	}

	public function getPrivs() {
		if ( $this->_privs === null ) {
			//			$this->_privs = new Privs($this);
			$this->_privs = new UserPrivs($this);
		}
		return $this->_privs;
	}	

	public function getActorGroups() {
		if ( $this->_actorGroups === null ) {
			$ag = new ActorGroupMembers();
			$ag->filter->actorid->numequals("'" . $this->getGUID() . "'");
			$ag->refresh();
			if ( count($ag->items) > 0 ) {
				$this->_actorGroups = $ag->items;
			} else {
				$this->_actorGroups = array();
			}
		}
		return $this->_actorGroups;
	}
	public function getAccessTokens(){
		if( $this->_accessTokens === null ){
			$ats = new AccessTokens();
			$ats->filter->addedby->equals($this->_id);
			if( count($ats->items) > 0 ){
				$this->_accessTokens = $ats->items;
			}else{
				$this->_accessTokens = array();
			}
		}
		return $this->_accessTokens;
	}
	public function toXML($recursive=false) {	
		 // We always want the following, force them now
		 $this->getContacts();
		 $this->getPositionType();
		 $this->getCountry();
		 $xml = parent::toXML($recursive);
		 $x2 = "";
		 if ($recursive) {
			 $this->getApplications();
			 $this->getPublications();
		 };
		 if ( $this->_applications !== null ) foreach ($this->applications as $vo) { $x2.=$vo->toXML(); };
		 if ( $this->_publications !== null ) foreach ($this->publications as $vo) { $x2.=$vo->toXML(); };
		 if ( $this->_contacts !== null ) foreach ($this->contacts as $vo) { $x2.=$vo->toXML(); };
		 $x2.='<permalink>http://'.$_SERVER['APPLICATION_UI_HOSTNAME'].'/?p='.base64_encode('/people/details?id='.$this->Id).'</permalink>';
		 $xml = preg_replace("/<\/Researcher>/",$x2."</Researcher>",$xml);
		 return $xml;
	 }



}
