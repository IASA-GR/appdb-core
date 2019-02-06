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
use Zend\Session\Container;

class IndexController extends AbstractActionController 
{

    public function __construct() {
		$this->view = new ViewModel();
		$this->session = new \Zend\Session\Container('base');
		if( $this->session->isNewUser === true ){
			return $this->redirect()->toRoute('saml', ['action' => 'newaccount']);
		}else if( $this->session->userDeleted === true ){
			return $this->redirect()->toRoute('saml', ['action' => 'deletedprofile']);
		}else if( $this->session->accountStatus === "blocked" ) {
			return $this->redirect()->toRoute('saml', ['action' => 'blockedaccount']);
		}
    }

	public function testAction() {
		return DISABLE_LAYOUT($this, true); 
	}

	public function apptagcloudAction()
    {
		return DISABLE_LAYOUT($this);
	}

	public function eginewsAction() {
		$itemcount = 0;
		$news = array();
		$xmlnews = new XMLReader();
		$xmlnews->open("http://www.egi.eu/about/news/news.rss");
		$parseNode = false;
		while (  ($xmlnews->read()) && ($itemcount <= 5) ) {
			if ( ($xmlnews->name == "item") && ($xmlnews->nodeType == \XMLReader::ELEMENT) ) {
				$itemcount++;
				$new = array();
				$parseNode = true;
			} elseif ( ($xmlnews->name == "item") && ($xmlnews->nodeType == \XMLReader::END_ELEMENT) ) {
				$news[] = $new;
				$parseNode = false;
			} elseif ( ($xmlnews->name == "title") && ($xmlnews->nodeType == \XMLReader::ELEMENT) && $parseNode ) {
				$xmlnews->read();
				$new["title"] = $xmlnews->value;
			} elseif ( ($xmlnews->name == "link") && ($xmlnews->nodeType == \XMLReader::ELEMENT) && $parseNode ) {
				$xmlnews->read();
				$new["link"] = $xmlnews->value;
			} elseif ( ($xmlnews->name == "pubDate") && ($xmlnews->nodeType == \XMLReader::ELEMENT) && $parseNode ) {
				$xmlnews->read();
				$new["date"] = $xmlnews->value;
			} elseif ( ($xmlnews->name == "description") && ($xmlnews->nodeType == \XMLReader::ELEMENT) && $parseNode ) {
				$xmlnews->read();
				$new["desc"] = $xmlnews->value;
			}elseif ( ($xmlnews->localName == "creator") && ($xmlnews->nodeType == \XMLReader::ELEMENT) && $parseNode ) {
				$xmlnews->read();
				$new["creator"] = $xmlnews->value;
			}
		}
		$xmlnews->close();
		$this->view->news = $news;
		return DISABLE_LAYOUT($this);
	}
    
    public function homeAction()
    {
		$this->view->session = $this->session;
		$this->view->username = $this->session->username;
		return DISABLE_LAYOUT($this);
	}

    public function indexAction()
	{
		/*
		 * Check if user is signed in from a different service or browser tab.
		 */
		if (! is_null($this->session)) {
			if ($this->session->getManager()->getStorage()->isLocked()) {
				$this->session->getManager()->getStorage()->unLock();
			}
		}

		if (! is_null($this->session) && $this->session->developsession === true ) {
			//do nothing. It's local development instance where no SImpleSaml installed
		} else {
			$auth = \SamlAuth::isAuthenticated();
			if( $auth === false ){
				//if logged in but not authdicated the clear session
				if( isset($this->session->userid) && is_numeric($this->session->userid)  ){
					\SamlAuth::logout($this->session);
					header('Location: http://' . $_SERVER["HTTP_HOST"]);
					return SET_NO_RENDER($this);
				}
			}else if(isset($this->session) === false || isset($this->session->userid) === false || is_numeric($this->session->userid) === false ){
				//if authenticated but not logged in setup user session
				$attributes = $auth->getAttributes();
				$uid = $attributes['idp:uid'][0];
				$_SESSION['identity'] = $uid;
				$_SESSION['logouturl'] = $auth->getLogoutURL();
				$this->session->samlattrs = $attributes;
				$this->session->samlauthsource = ( isset($attributes["idp:sourceIdentifier"])?$attributes["idp:sourceIdentifier"][0]:"");
				\SamlAuth::setupSamlAuth($this->session);
				if( $this->session->isNewUser === true ){
					header('Location: https://' . $_SERVER['HTTP_HOST'] .'/saml/newaccount');
					return SET_NO_RENDER($this);
				}
				//Check and redirect if user account is blocked
				if( $this->session->accountStatus === "blocked" ){
					header('Location: https://' . $_SERVER['HTTP_HOST'] .'/saml/blockedaccount');
					return SET_NO_RENDER($this);
				}
				
				//Check and redirect if user is deleted
				if( $this->session->userDeleted === true ){
					header('Location: https://' . $_SERVER['HTTP_HOST'] .'/saml/deletedprofile');
					return SET_NO_RENDER($this);
				}
			}
		}
		
		$this->session->appCriteria = null;
		$this->session->pplCriteria = null;

		$this->session->certLogin = false;
		$this->view->username = $this->session->username;

		if ( $this->session->userid !== null ) {
			$ppl = new \Application\Model\Researchers();
			$ppl->filter->id->equals($this->session->userid);
			$user = $ppl->items[0];
			$this->view->user = $user;
			
			/* Get count of user requests */
			$urs = new \Application\Model\UserRequests();
			$s2 = new \Application\Model\PermissionsFilter();
			$s2->actor->equals($this->session->userguid);
			$s3 = new \Application\Model\UserRequestStatesFilter();
			$s3->name->equals("submitted");
			$urs->filter->chain(/*$s1->chain(*/$s2->chain($s3,"AND"),"AND"/*),"AND"*/);
			$reqsitems = $urs->items;
			$uritems = array_merge($reqsitems);

			//Fetch user requests for NILs
			if( userIsAdminOrManager($this->session->userid) === false && userIsNIL($this->session->userid) === true ){
				$nilusers = new \Application\Model\UserRequests();
				$s1 = new \Application\Model\UserRequestTypesFilter();
				$s1->id->numequals(3);
				$s2 = new \Application\Model\ResearchersFilter();
				$s2->countryid->equals($this->session->userCountryID);
				$s3 = new \Application\Model\UserRequestStatesFilter();
				$s3->name->equals("submitted");
				$s4 = new \Application\Model\ActorGroupsFilter();
				$s4->id->numequals(-3);
				$nilusers->filter->chain($s1->chain($s2->chain($s3->chain($s4,"AND"),"AND"),"AND"),"AND");
				if( count($nilusers->items) > 0 ){
					$uritems = array_merge($uritems, $nilusers->items);
					$uritems = array_filter($uritems, 'uniqueDBObjectFilter');
				}
			}
			$this->view->userRequests = count($uritems);

                        if ($this->session->userHasPersonalAccessTokens === true) {
                            $this->view->userHasPersonalAccessTokens = true;
                        } else {
                            $this->view->userHasPersonalAccessTokens = userHasPersonalAccessTokens($this->session->userid);
                        }
		}
		$p = '';
		if ( $this->session->permaLink != '' ) {
			$p = $this->session->permaLink;
			$this->session->permaLink = '';
		} elseif ( array_key_exists('p',$_GET) ) {
			$p = $_GET["p"];
		} else{
				//TODO : needs review
				$p = $_SERVER["QUERY_STRING"];
				$pos = strpos($p,"p=");
				if($pos===false){
				  $p = '';
				}else{
					$p = substr($p,2,strlen($p)-2);
				}
			}
		if ( $p != "" ) {
			if ( $p == "reports" ) {
				$this->view->permaLink = $p;
			} elseif ( substr($p,0,6) == "about:" ) {
				$this->view->permaLink = $p;
			} elseif ( substr($p,0,5) == "apps:" ) {
				$this->view->permaLink = $p;
			} elseif ( substr($p,0,7) == "people:" ) {
				$this->view->permaLink = $p;
			} else {
				$pp = base64_decode($p);
				$pp = mb_convert_encoding($pp, 'UTF-8');
				$this->view->permaLink=$pp;
			}
		}
		return $this->view;
    }
	
	public function createCaptcha(){
        // decorator of captcha image, we add id and onclick()
        $decorators = array(
            array('HtmlTag', array('tag' => 'div'))
        );
        $form = new Zend_Form();

        // Zend_Captcha_Image : images are saved in public/img/captcha/
        $captcha = $form->createElement('captcha', 'captcha', array(
            'captcha' => array(
                'captcha' => 'Image',
                'wordLen' => 5,
                'fontsize' => 22,
                'width' => 170,
                'height' => 50,
                'dotNoiseLevel' => 15,
                'timeout' => 300,
                'font' => 'fonts/OpenSans-Regular.ttf',
                'imgDir' => './upload/',
                'imgUrl' => '/upload/',
            ),
            'decorators' => $decorators
        ));
        return $captcha;
    }
	public function getcaptchaAction() {
		echo $this->generateCaptchaForm();
		return SET_NO_RENDER($this);
	}
	public function generateCaptchaForm(){
		$form = new Zend_Form();
		$captcha = $this->createCaptcha();
		$form->addElement($captcha);
		$this->view->captchaImageUrl = $captcha->getCaptcha()->getImgUrl()
			. $captcha->getCaptcha()->getId()
			. $captcha->getCaptcha()->getSuffix();
		return $form->render();
	}
	public function feedbackAction(){
		if( $_SERVER["REQUEST_METHOD"] == "GET" ){
			$this->view->src = ( isset( $_GET["src"] ) )?$_GET["src"]:null;
			$this->view->username = "";
			$this->view->contacts = array();

			if( $this->session->userid !== null ) {
				$this->view->username = $this->session->fullName;
				$cnts = new \Application\Model\Contacts();
				$cnts->filter->researcherid->equals($this->session->userid)->and($cnts->filter->contacttypeid->equals(7));
				if( count ( $cnts->items ) > 0 ) {
					$this->view->contacts = $cnts->items;
				}
			}else{
				$this->view->captcha =  $this->generateCaptchaForm();
			}
			
		} else if( $_SERVER["REQUEST_METHOD"] == "POST" ) {
			$this->_helper->viewRenderer->setNoRender();
			$feedback = ( ( isset($_POST["feedback"]) && trim($_POST["feedback"]) !== "" )?$_POST["feedback"]:"" );
			$subject = ( ( isset($_POST["subject"]) && trim( stripslashes($_POST["subject"]) ) !== "" )?stripslashes($_POST["subject"]):"<no subject>" );
			$email = ( ( isset($_POST["email"]) && trim(stripslashes($_POST["email"])) !== "" )?stripslashes($_POST["email"]):"" );
			$cc = ( ( isset($_POST["cc"]) && trim($_POST["cc"]) !== "")?$_POST["cc"]:false );
			$name = ( ( isset($_POST["name"]) && trim($_POST["name"]) !== "" )?$_POST["name"]:"anonymous" );
			$captchaid = ( ( isset($_POST["captchaid"]) && trim($_POST["captchaid"]) !== "" )?$_POST["captchaid"]:"" );
			$captcha = ( ( isset($_POST["captcha"]) && trim($_POST["captcha"]) !== "" )?$_POST["captcha"]:"" );
			
			header("Content-type: text/xml; charset=utf-8"); 
			echo '<' . '?xml version="1.0"?' . '>';
			if( $feedback == "" ) {
				echo "<response error='no feedback given' group='feedback'></response>";
				return DISABLE_LAYOUT($this);
			}
			if( $email == "" ) {
				echo "<response error='no email given' group='email'></response>";
				return DISABLE_LAYOUT($this);
			}
			$bodyheader = "----------------------------------------------------------------------------\n";
			$bodyheader .= "Feedback from user: " . $name . " " . (($this->session->userid!==null)?"(id:".$this->session->userid.")":"") . " \n";
			$bodyheader .= "User email: " . $email . " \n";
			$bodyheader .= "----------------------------------------------------------------------------\n\n";
			
			//Make receivers array
			if ( strpos($email, ";") !== false ){
				$emailto = explode(";",$email);
				$email = array();
				for($i=0; $i<count($emailto); $i+=1){
					$emailto[$i] = trim($emailto[$i]);
					if( $emailto[$i] !== "" ){
						$email[] = $emailto[$i];
					}
				}
			} else {
				$email = array($email);
			}
			
			//Validate email format
			for( $i=0; $i < count($email); $i+=1 ) {
				if(! preg_match('/^([0-9a-z]+[-._+&])*[0-9a-z]+@([-0-9a-z]+[.])+[a-z]{2,6}$/i', $email[$i])) {
					echo "<response error='Email " . $email[$i] . " is invalid' group='email'></response>";
					return DISABLE_LAYOUT($this);
				}
			}
			$body = base64_decode($feedback);
			$body = stripslashes($body);
			
			
			if( trim($body) == "" ){
				echo "<response error='no feedback given' group='feedback'></response>";
				return DISABLE_LAYOUT($this);
			}
			if( preg_match("/(\r|\n)(to:|from:|cc:|bcc:)/i", $body) ) {
				echo "<response error='Message body contains invalid headers' group='feedback'></response>";
				return DISABLE_LAYOUT($this);
			}
			
			if( $this->session->userid === null ){
				if( trim($captchaid) == "" && trim($captcha) == "" ){
					echo "<response error='Must login first to send message'></response>";
					return DISABLE_LAYOUT($this);
				}
				$sessionkey = "Zend_Form_Captcha_" . $captchaid;
				if( isset($_SESSION[$sessionkey]) && isset($_SESSION[$sessionkey]["word"]) && $_SESSION[$sessionkey]["word"] == $captcha){
					
				}else{
					echo "<response error='Security word is wrong' group='captcha'></response>";
					return DISABLE_LAYOUT($this);
				}
			}
			
			$subject = "[AppDB Portal Feedback]: " . $subject;
			$body = $bodyheader . $body;
			
			//Send email
			$recs = array(\EmailConfiguration::getSupportAddress());
			$ccemails = false;
			if($cc == true){
				$ccemails = $email;
			}
			//sendMultipartMail($subject,$recs, $body, '', 'appdb-reports@iasa.gr', 'enadyskolopassword', $email[0], null, $ccemails);
			\EmailService::sendReport($subject, $recs, $body, '', $email[0], null, $ccemails);
			
			$to = "";
			foreach($email as $e){
				if ( trim($to) !== "" ) {
					$to .= ";";
				}
				$to .= $e;
			}
			echo "<response to='".htmlspecialchars($to,ENT_QUOTES)."' subject='".htmlspecialchars($subject,ENT_QUOTES)."' cc='" . $cc . "' >" . htmlspecialchars($body,ENT_QUOTES) . "</response>";
			return DISABLE_LAYOUT($this);
		}
		
	}
	public function hashnavigationAction(){
		if( browser() == "msie" ){
			header('Location: http://'.$_SERVER['HTTP_HOST'] . "#" . $_SERVER['REQUEST_URI']);
			
		}
		return SET_NO_RENDER($this);
	}
	
	public function generatesitemapAction(){
		ignore_user_abort(true);
		set_time_limit(0);
		if( localRequest() == true ){
			\SEO::generateSitemap();
		}
		return SET_NO_RENDER($this);
	}
	
	public function customhomeAction() {
		if( isset($this->session->usercname) === false ){
			$ppl = new \Application\Model\Researchers();
			$ppl->filter->id->equals($this->session->userid);
			if( count($ppl->items) == 0 ){
				return DISABLE_LAYOUT($this, true);
			}
			$p = $ppl->items[0];
			$this->session->usercname = $p->cname;
		}
		$this->view->session = $this->session;
		$this->view->username = $this->session->username;
		return DISABLE_LAYOUT($this);
	}

	public function resetopcacheAction() {
		if( localRequest() === true ){
			opcache_reset();
			// also erase DB metadata cache
			foreach (glob($_SERVER['APPLICATION_PATH'] . "/../cache/dbmeta/*") as $dbcache) {
				unlink($dbcache);
			}
		} else {
			$this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
			$this->getResponse()->setHeader("Status","403 Forbidden");
		}
		return SET_NO_RENDER($this);
	}
	
	public function buildcacheAction(){
		if( localRequest() == false ){
			return SET_NO_RENDER($this);
		}
		$ver = appdbVerInfo();
		$customBuild = true;
		if ( (substr($_SERVER['APPLICATION_ENV'], 0, 5) === 'devel') && (isset($_GET['cb']) && ($_GET['cb'] == "1") ) ) {
			$customBuild = true;
		} elseif ( substr($_SERVER['APPLICATION_ENV'], 0, 5) === 'devel' ) {
			$customBuild = false;
		}
		$customBuild = true;
		$res = "CACHE MANIFEST\n";
		$res .= "# " . date("Y/m/d H:M:i") ."\n";
		$res .= "\n";
		
		$res .= "CACHE:\n";
		if( $customBuild ){
			
			$res .= "/js/dijit/themes/tundra/tundra.css\n";
			$res .= "/js/dojo/dojo.js?v=" .$ver . "\n";
			$res .= "/js/dojo/appdb.jgz?v=" . $ver . "\n";
			$res .= "/css/appdb.min.css?v=" . $ver . "\n";
			$res .= "/js/jQuery.js?v=" . $ver . "\n";
			$res .= "/js/d3.v3.min.js?v=" . $ver . "\n";
			$res .= "/js/countdown.min.js?v=" . $ver . "\n";
			$res .= "/js/appdb.min.js?v=" . $ver . "\n";
			$res .= "/js/tinymce/tiny_mce.js?v=" . $ver . "\n";
			$imgs = file_get_contents(__DIR__ . "/../../bin/imglist");
			if( $imgs === false ){
				echo "Could not load image list file";
			}else{
				$images = explode("\n", $imgs);
				foreach($images as $img){
					$res .= str_replace("../public", "", $img) . "\n";
				}
			}
			$res .= "/images/appdb-logo-new-small.png\n";
			$res .= "/images/appdb_logo_moto.png\n";
			$res .=" /images/homepage.png\n";
		}else{
			$res .= "/js/dojo/appdb.jgz\n";
			$res .= "/js/dojox/grid/_grid/tundraGrid.css?v=" . $ver . "\n";
			$res .= "/js/jQuery.js?v=" . $ver . "\n";
			$res .= "/js/d3.v3.min.js?v=" . $ver . "\n";
			$res .= "/css/main.css?v=" . $ver . "\n";
			$res .= "/css/view.css?v=" . $ver . "\n";
			$res .= "/css/menu.css?v=" . $ver . "\n";
			$res .= "/css/repository.css?v=" . $ver . "\n";
			$res .= "/css/tabbar.css?v=" . $ver . "\n";
			$res .= "/css/jquery-ui.css?v=" . $ver . "\n";
			$res .= "/css/newsfeed.css?v=" . $ver . "\n";
			$res .= "/js/jquery-autocomplete/jquery.autocomplete.css?v=" . $ver . "\n";
			$res .= "/js/archive.js?v=" . $ver . "\n";
			$res .= "/js/brsdet.js?v=" . $ver . "\n";
			$res .= "/js/shortcut.js?v=" . $ver . "\n";
			$res .= "/js/ajaxLoading.js?v=" . $ver . "\n";
			$res .= "/js/json2.js?v=" . $ver . "\n";
			$res .= "/js/appdb.utils.js?v=" . $ver . "\n";
			$res .= "/js/appdb.models.js?v=" . $ver . "\n";
			$res .= "/js/appdb.views.js?v=" . $ver . "\n";
			$res .= "/js/appdbbase.js?v=" . $ver . "\n";
			$res .= "/js/appdb.template.js?v=" . $ver . "\n";
			$res .= "/js/jquery.cookie.js?v=" . $ver . "\n";
			$res .= "/js/jquery.form.js?v=" . $ver . "\n";
			$res .= "/js/jquery.center.js?v=" . $ver . "\n";
			$res .= "/js/jquery.escape.js?v=" . $ver . "\n";
			$res .= "/js/jquery.outerhtml.js?v=" . $ver . "\n";
			$res .= "/js/jquery.scrollTo-min.js?v=" . $ver . "\n";
			$res .= "/js/jquery.tinysort.min.js?v=" . $ver . "\n";
			$res .= "/js/jquery.tagcloud.js?v=" . $ver . "\n";
			$res .= "/js/jquery-autocomplete/jquery.autocomplete.js?v=" . $ver . "\n";
			$res .= "/js/jquery.hashchange.js?v=" . $ver . "\n";
			$res .= "/js/itemview.js?v=" . $ver . "\n";
			$res .= "/js/appdbgui.js?v=" . $ver . "\n";
			$res .= "/js/jquery.google_menu.js?v=" . $ver . "\n";
			$res .= "/js/appdb.pages.js?v=" . $ver . "\n";
			$res .= "/js/appdb.routes.js?v=" . $ver . "\n";
			$res .= "/js/appdb.social.js?v=" . $ver . "\n";
			$res .= "/js/jquery-ui.js?v=" . $ver . "\n";
			$res .= "/js/jquery.form.js?v=" . $ver . "\n";
			$res .= "/js/jquery.center.js?v=" . $ver . "\n";
			$res .= "/js/editForm.js?v=" . $ver . "\n";
			$res .= "/js/tinymce/tiny_mce.js?v=" . $ver . "\n";
			$res .= "/js/tinymce/jquery.tinymce.js?v=" . $ver . "\n";
			$res .= "/js/plupload.full.js?v=" . $ver . "\n";
			$res .= "/js/appdb.repository.js?v=" . $ver . "\n";
			$res .= "/js/appdb.statistics.js?v=" . $ver . "\n";
			$res .= "/js/appdb.vappliance.js?v=" . $ver . "\n";
			$res .= "/js/countdown.min.js?v=" . $ver . "\n";
			$res .= "/js/dojo/appdb.jgz?v=" . $ver . "\n";
			$res .= "/js/dojo/dojo.js?v=" . $ver . "\n";
			$dh  = glob(__DIR__ . "/../../public/images/*.png");
			foreach($dh as $d){
				$res .= "/images/" . basename($d) . "\n";
			}
			$dh  = glob(__DIR__ . "/../../public/images/*.gif");
			foreach($dh as $d){
				$res .= "/images/" . basename($d) . "\n";
			}
		}
		$res .= "\n";
		
		$res .= "\n# Resources that require the user to be online.\n";
		$res .= "NETWORK:\n";
		$res .= "https://" . $_SERVER["HTTP_HOST"] ."/\n";
		$res .= "http://" . $_SERVER["HTTP_HOST"] ."/\n";
		$res .= "https://" . $_SERVER["HTTP_HOST"] ."/auth/\n";
		$res .= "https://" . $_SERVER["HTTP_HOST"] ."/saml/\n";
		$res .= "*\n";
		
		$res .= "#FALLBACK:\n\n";
		
		unlink(__DIR__ . "/../../public/appdb.cache");
		$saved = file_put_contents(__DIR__ . "/../../public/appdb.appcache", $res);
		if( $saved === false ){
			echo "Could not save appdb.appcache file\n";
		} else {
			echo "Saved appdb.appcache file\n";
			$hash = md5($res);
			$saved = file_put_contents(__DIR__ . "/../../public/appdb.appcache.hash", $hash);
			if( $saved === false ){
				echo "Could not save appdb.appcache.hash";
			} else {
				echo "Saved appdb.appcache.hash file\n";
			}
		}
		return SET_NO_RENDER($this);
	}
	
	public function nolayoutAction(){
		$c = trim(GET_REQUEST_PARAM($this, "_c"));
		$a = trim(GET_REQUEST_PARAM($this, "_a"));
		$qstr = trim(GET_REQUEST_PARAM($this, "q"));
		$q = array();
		
		if( $c === "" ){
			$c = "index";
		}
		
		if( $a === "" || strtolower($a) == "nolayout" ){
			$a = "index";
		}
		if( $qstr !== "" ){
			$qstr = urldecode($qstr);
			if( substr($sqtr,0,1) === "?" ){
				$qstr = substr($qstr,1);
			}
			parse_str($sqtr, $q);
		}
		$this->getRequest()->clearParams();
		$this->getRequest()->setParams($q);
		$this->_forward($a,$c,null,$q);
		echo $this->getResponse();
		return SET_NO_RENDER($this); 
	}
	
	public function scriptstatusAction(){
		$data = file_get_contents(__DIR__ . "/../../public/appdb.appcache.hash");
		echo $data;
		return SET_NO_RENDER($this); 
	}
}

