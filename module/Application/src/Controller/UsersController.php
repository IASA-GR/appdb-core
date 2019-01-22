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

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class UsersController extends AbstractActionController
{

    public function init()
    {
    	$this->session = new Zend_Session_Namespace('default');
    }

    public function indexAction()
    {
        // action body
    }
    
    public function createaccountAction()
    {
		$this->view->session = $this->session;
    }
    
    public function claimaccountAction()
    {
        $this->_helper->layout->disableLayout();
		$data=$_POST;
		if ( is_array($this->session->canClaim) ) {
			if ( in_array($data['id'],$this->session->canClaim) ) {
				$users = new Default_Model_Researchers();
				$users->filter->id->equals($data['id']);
				$user = $users->items[0];
				$user->username = $this->session->username;
				$this->session->userid = $data['id'];
				$this->session->userRole = $user->positionTypeID;
				$user->lastLogin = time();
				$user->password = $this->session->claimPassword;
				setAuthCookies($this->session->username, $this->session->claimPassword);
				$user->save();
				$this->session->claimPassword = null;
				$this->session->fullName = $user->firstName." ".$user->lastName;
				$this->session->canClaim = null;
				$users->refresh();
				$user = $users->items[0];
				$this->session->userCountryName = $user->country->name;
				return true;
			}
		}
		$this->session->canClaim = null;
	}

	public function ldapsearchAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$ldap= ApplicationConfiguration::service('egi.ldap.username');
		if ( $_SERVER['APPLICATION_ENV]'] != "production" ) {
			echo "<pre>";
			$ldapbind=false;
			$ds=ldap_connect($ldap); 
			if(ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3))
				if(ldap_set_option($ds, LDAP_OPT_REFERRALS, 0))
						$ldapbind = @ldap_bind($ds, ApplicationConfiguration::service ('egi.ldap.username'), ApplicationConfiguration::service('egi.ldap.password'));
			if ($ldapbind) {
				$sr=ldap_search($ds, "ou=people,dc=egi,dc=eu", "(uid=".$_GET['username'].")");
				$info = ldap_get_entries($ds, $sr);
				ldap_close($ds);
				if ($info["count"] > 0) {
						$pts = new Default_Model_PositionTypes();
						$roleTypeID = 3;
						if ( array_key_exists('usercertificatesubject',$info[0]) ) {
							$sub = explode('/',$info[0]['usercertificatesubject'][0]);
							foreach($sub as $s) {
								if (substr($s,0,2) == 'C=') {
									$country = substr($s,2);
									$cs = new Default_Model_Countries();
									$cs->filter->isocode->equals($country);
									if ($cs->count()>0) echo 'Country: '.$cs->items[0]->name."\n";
									break;
								}
							}
						}
						if ( array_key_exists('sn',$info[0]) ) {
							echo "lastname: ".$info[0]['sn'][0]."\n";
							echo "firstname: ".str_replace($info[0]['sn'][0],"",$info[0]['cn'][0])."\n";
						}
						if ( array_key_exists('ou',$info[0]) ) echo "institute: ".$info[0]['ou'][0]."\n";
						if ( array_key_exists('employeetype',$info[0]) ) {                                                                    
							if ( ( $info[0]['employeetype'][0] == "Experienced researcher") || ( $info[0]['employeetype'][0] == "Management" ) ) {
								$roleTypeID = 2;
							} else if ( ( $info[0]['employeetype'][0] == "Technical Support" ) || ( $info[0]['employeetype'][0] == "PhD Student" ) ) {
								$roleTypeID = 3;
							} else $roleTypeID = 4;
						}
						if ( array_key_exists('mail',$info[0]) ) {
							echo "SSOmail: ".$info[0]['mail'][0]."\n";
						}
						$pts->filter->id->equals($roleTypeID);
						echo "roleType: ".$pts->items[0]->description."\n";
						$contactTypes = new Default_Model_ContactTypes();
						$this->view->contactTypes = $contactTypes->refresh();
			}
			}
			echo "</pre>";
	}
    }

    public function logindev2Action()
    {
        return $this->loginAction();
    }
    
    public function logindevAction()
    {
		//Apply only in development enviroments
        if ( ApplicationConfiguration::isEnviroment("production") === FALSE ) {
            $this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender();
			$this->session->userid = ((isset($_GET["id"]))?$_GET["id"]:NULL);
			
			$us = new Default_Model_Researchers();
			$us->viewModerated = true;
			$us->filter->id->equals($this->session->userid);
			if ( count($us->items) > 0 ) {
				$this->session->username = $us->items[0]->username;
				$this->session->fullName = $us->items[0]->name;
				$this->session->userRole = $us->items[0]->positionTypeID;				
				$this->session->userCountryID = $us->items[0]->countryID;
				$this->session->userCountryName = $us->items[0]->country->name;
				$this->session->cname = $us->items[0]->cname;
			} else {
				$this->logoutAction();
			}
			
            $this->view->session = $this->session;
            $this->view->entries = null;
			$users = new Default_Model_Researchers();
			$users->viewModerated = true;
			$users->filter->id->equals($this->session->userid);
			if( count($users->items) > 0){
				$user = $users->items[0];
				
				setcookie("SimpleSAMLAuthToken", "09a4fcd92a07c008c2de0dcba1665580",0,"/",null,true, true);
				//Create new user credentials
				$cred = new Default_Model_UserCredential();
				$cred->researcherid = $this->session->userid;
				$cred->sessionid = session_id();
				$cred->token ='09a4fcd92a07c008c2de0dcba1665580';
				$cred->save();
				$this->session->developsession = true;
				if( $user->deleted === true ){
					//Setup session variables in case of deleted profile
					$this->session->userDeleted = $user->deleted;
					if( isset($user->delInfo) ){
						$this->session->userDeletedById = $user->delInfo->deleter->id;
						$this->session->userDeletedByName = $user->delInfo->deleter->name;
						$this->session->userDeletedByCName = $user->delInfo->deleter->cname;
						$this->session->userDeletedOn = $user->delInfo->deletedOn;
					}else{
						$this->session->userDeletedById = null;
						$this->session->userDeletedByName = null;
						$this->session->userDeletedByCName = null;
						$this->session->userDeletedOn = null;
					}
					$this->_redirect('/saml/deletedprofile');
					return;
				}else{
					//Get first user account and initialize saml session
					$uaccounts = new Default_Model_UserAccounts();
					$uaccounts->filter->researcherid->equals($user->id);
					if( count($uaccounts->items) > 0 ){
						$uaccount = $uaccounts->items[0];
						SamlAuth::setupSamlSession($this->session, $uaccount, $user);
					}
					
				}
			}
            header('Location: https://'.$_SERVER['HTTP_HOST']);
			$this->session->userWarning = array("title"=>"Development user", "message"=>"You are currently signed in developer mode");
        }
    }

    private function handle_actions() {
		$this->view->handled = true;
        if ( $this->_getParam('a') == "register" ) {
			header('Location: https://'.$_SERVER['HTTP_HOST'].'/?p='.base64_encode('/apps/details?id=0&r='.$this->_getParam('r')));
            return true;
		} elseif ( $this->_getParam("referrer") != '' ) {
			$s = str_replace("http://","https://",'Location: '.$this->_getParam("referrer"));
		    header($s);
			error_log('redirecting to '.$s);
            return true;
		}
		$this->view->handled = false;
        return false;
	}

	private function saveUserCredentials($userid, $token) {
		$cred = new Default_Model_UserCredential();
		$cred->researcherid = $userid;
		$cred->sessionid = session_id();
		$cred->token = $token;
		$cred->save();
		return $cred;
	}

	public function ldapError($ds, $desc = "Error while contacting LDAP") {
		$this->view->ldapError = $desc;
		if (isset($ds)) {
			$this->view->ldapError .= ": " . ldap_error($ds) . "(err " . ldap_errno($ds) . ")";
		}
	}

	private function initLDAP($secure = true, $rdn = null, $pwd = null) {
		return initLDAP($secure, $rdn, $pwd, array($this, "ldapError"));
	}

    public function loginAction()
    {
        /* Check whether the user is already logged in */
        if ( $this->session->userid !== null ) {
            if (! $this->handle_actions()) {
                $this->_helper->layout->disableLayout();
        		$this->_helper->viewRenderer->setNoRender();
                header('Location: https://'.$_SERVER['HTTP_HOST'].'/'); 
            }
            return;
        }
        $this->view->session = $this->session;
        $ldap= ApplicationConfiguration::service('egi.ldap.host');
		if ( array_key_exists('username',$_POST) ) {
			$this->view->username = $_POST['username'];
            $username = "uid=".$_POST['username'].",ou=People,dc=egi,dc=eu";
			$password = $_POST['password'];
			$ds = $this->initLDAP(true, $username, $password);
			if (is_resource($ds)) {
				@ldap_close($ds);
                $username = $_POST['username'];
                $this->session->username=$username;
                $users = new Default_Model_Researchers();
                $users->viewModerated = true;
                $users->filter->username->equals($username);
                if ( $users->count() == 1 ) {
                    if ( ! $users->items[0]->deleted ) {
                        $this->session->userid = $users->items[0]->Id;
                        $this->session->userRole = $users->items[0]->positionTypeID;
                        $this->session->userCountryID = $users->items[0]->countryID;
                        $this->session->userCountryName = $users->items[0]->country->name;
                        $this->session->user = $users->items[0];
                        $this->session->fullName=$users->items[0]->firstName." ".$users->items[0]->lastName;
						$this->session->cname = $users->items[0]->cname;
                        setAuthCookies($username, md5($password));
                        $user = $users->items[0];
                        $user->lastLogin = time();
                        $user->password = md5($password);	// TODO: remove this when SAML has been implemented
						$user->save();
						$this->saveUserCredentials($user->id, md5($password));	// TODO: replace with SAML Auth token
                        $this->handle_actions();
                    } else {
                        $this->view->accountDeleted = true;
                        error_log(var_export($users->items[0]->delInfo,true));
                        $this->view->accountDeleter = $users->items[0]->delInfo->deleter->name;
                        $this->view->accountDeleterID = $users->items[0]->delInfo->deleter->id;
                        $this->view->accountDeletedOn = $users->items[0]->delInfo->deletedOn;
                    }
                } else {
					$this->session->claimPassword = md5($password);
					$ds = $this->initLDAP(true); // no rdn/pwd: root connection
                    if ($ds !== false) {
                        $users2 = new Default_Model_Contacts();
                        $sr = ldap_search($ds, "ou=people,dc=egi,dc=eu", "(uid=".$_POST['username'].")");
                        $info = ldap_get_entries($ds, $sr);
                        ldap_close($ds);
                        if ($info["count"] > 0) {
                            $this->session->fullName=$info[0]['cn'][0];
                            $users2->filter->data->ilike($info[0]['mail'][0]);
                            if ( $users2->refresh()->count() != 0 ) { // Found existing profiles
                                $ids=array();
                                for ($i=0; $i<$users2->count(); $i++) {
                                    $ids[] = $users2->items[$i]->researcherid;
                                }
                                $users->viewModerated = false;
                                $users->filter->id->in($ids);
                                $users->refresh();
                                $entries=array();
                                $canClaim=array();
                                for ($i=0; $i<$users->count(); $i++) {
                                    $entries[] = $users->items[$i];
                                    $canClaim[] = $users->items[$i]->id;
                                }
                                $this->view->entries = $entries;
                                $this->session->canClaim = $canClaim;
                            } else { // Create a new profile
								$roleTypeID = 3;
								if ( array_key_exists('usercertificatesubject',$info[0]) ) {
									$sub = explode('/',$info[0]['usercertificatesubject'][0]);
									foreach($sub as $s) {
										if (substr($s,0,2) == 'C=') {
											$country = substr($s,2);
											$cs = new Default_Model_Countries();
											$cs->filter->isocode->equals($country);
											if ($cs->count()>0) $this->view->country = $cs->items[0]->name;
											break;
										}
									}
								}
								if ( array_key_exists('sn',$info[0]) ) {
									$this->view->lastname = $info[0]['sn'][0];
									$this->view->firstname = str_replace($info[0]['sn'][0],"",$info[0]['cn'][0]);
								}
								if ( array_key_exists('ou',$info[0]) ) $this->view->institute = $info[0]['ou']['0'];
                                if ( array_key_exists('employeetype',$info[0]) ) {                                                                    
                                    if ( ( $info[0]['employeetype'][0] == "Experienced researcher") || ( $info[0]['employeetype'][0] == "Management" ) ) {
                                        $roleTypeID = 2;
                                    } else if ( ( $info[0]['employeetype'][0] == "Technical Support" ) || ( $info[0]['employeetype'][0] == "PhD Student" ) ) {
                                        $roleTypeID = 3;
                                    } else $roleTypeID = 4;
                                }
                                if ( array_key_exists('mail',$info[0]) ) {
                                    $this->view->SSOmail = $info[0]['mail'][0];
                                }
                                $pts = new Default_Model_PositionTypes();
                                $pts->filter->id->equals($roleTypeID);
                                $this->view->roleType = $pts->items[0]->description;
                                $contactTypes = new Default_Model_ContactTypes();
                                $this->view->contactTypes = $contactTypes->refresh();
                            }
                        }
					} else {
						error_log($this->view->ldapError);
                    }
                }
			} else {
				error_log($this->view->ldapError);
			}
        }        
    }

    public function logoutAction()
    {
        $this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
        Zend_Session::destroy(true);
        clearAuthCookies();
        header('Location: http://'.$_SERVER['HTTP_HOST']);
    }
    
    public function readallmsgAction()
    {
    	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		if ( $this->session->userid !== null ) {
			$users = new Default_Model_Researchers();
			$users->filter->id->equals($this->session->userid);
			$user = $users->items[0];
			for ($i=0; $i<count($user->inbox->items); $i++) {
				$item = $user->inbox->items[$i];
				$item->isRead = true;
				$item->save();
			}
		}
    }
    
    public function getinboxAction()
    {
		$ss="";
    	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		if ( $this->session->userid !== null ) {
			$users = new Default_Model_Researchers();
			$users->filter->id->equals($this->session->userid);
			if(count($users->items) > 0){
				$user = $users->items[0];
				$ss="<Messages>";
				$inbox = $user->inbox->items;
				for ($i=0; $i<count($inbox); $i++) {
					$s=$inbox[$i]->toXML();
					if ( ! isnull($inbox[$i]->senderID) ) {
						$sender="<senderName>".$inbox[$i]->sender->firstName." ".$inbox[$i]->sender->lastName."</senderName>";
						$sender.="<senderCName>".$inbox[$i]->sender->cname."</senderCName>";
						$s=substr($s,0,strlen($s)-11).$sender."</Message>";
					}
					$ss.=$s;
				}
				$ss.="</Messages>";
			}
		}
		header('Content-Type: text/xml');
		echo $ss;
    }

    public function getunreadmsgcountAction()
    {
		if(trim($_SERVER['REQUEST_METHOD']) === "GET"){
			if ($this->session->isLocked()) {
				$this->session->unLock();
			}
			session_write_close();
		}
    	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		if ( $this->session->userid !== null ) {
			$inbox = new Default_Model_Messages();		
			$inbox->filter->isread->equals(false)->and($inbox->filter->receiverid->equals($this->session->userid));
			$items = $inbox->items;
			echo count($items);
		}
    }
    
    public function delmsgAction()
    {
    	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$ms = new Default_Model_Messages();
		$ms->filter->id->equals($_POST['msgid']);
		$ms->refresh()->remove(0);
    }
    
	public function updateallAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		return;

		$ldap = ApplicationConfiguration::service('egi.ldap.host');
		$ldapbind=false;
		$ds=ldap_connect($ldap); 
		if(ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3))
			if(ldap_set_option($ds, LDAP_OPT_REFERRALS, 0))
					$ldapbind = @ldap_bind($ds, ApplicationConfiguration::service('egi.ldap.username'), ApplicationConfiguration::service('egi.ldap.password'));
		if ($ldapbind) {
			$users = new Default_Model_Researchers();
			$users->refresh();
			for($i=0; $i<$users->count(); $i++) {
				$u = $users->items[$i];
				if ( ! isnull($u->username)) {
					$sr=ldap_search($ds, "ou=people,dc=egi,dc=eu", "(uid=".$u->username.")");
					$info = ldap_get_entries($ds, $sr);
					if ($info["count"] > 0) {
						if ( array_key_exists('destinationindicator', $info[0]) ) {
							$u->save();
						}
					}
				}
			}
			ldap_close($ds);
		}
	}
	
	public function inboxAction(){
			$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		if ( $this->session->userid === null ) {
			header('HTTP/1.0 403 Forbidden');
			return;
		}
		header('Content-Type: text/xml');
		$meth = strtoupper(trim($_SERVER['REQUEST_METHOD']));	
		switch($meth){
			case "GET":
				echo UserInbox::getMessages($this->session->userid,$_GET);
				break;
			case "POST":
			case "PUT":
			case "DELETE":
				header('HTTP/1.0 403 Forbidden');
				echo "<person:messages error='not implemented yet' ></person:messages>";
				break;
		}
		
	}
}
