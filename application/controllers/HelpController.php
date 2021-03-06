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

class HelpController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
        $this->session = new Zend_Session_Namespace('default');
    }

    public function indexAction()
    {
        // action body
    }

    public function appdetailsAction()
    {

    }

    public function creditsAction()
    {
		$this->_helper->layout->disableLayout();
    }

	public function faqaAction()
    {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		if ( $this->_getParam("id") != "" ) {
			$faqs = new Default_Model_FAQs();
			$faqs->filter->id->equals($this->_getParam("id"));
			if ( count($faqs->items) > 0 ) {
				echo str_replace('”', '"', $faqs->items[0]->answer);
			}
		}
	}

    public function faqAction()
    {
		$this->_helper->layout->disableLayout();
		if( isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == 'POST' ){
			$this->_helper->viewRenderer->setNoRender();
			$invalidUser = ! (($this->session->userid !== null) && userIsAdminOrManager($this->session->userid));
			if ( $invalidUser ) {
				echo "<response error='access denied' />";
				return;
			}
			if( !(isset($_POST["question"]) && trim($_POST["question"])!=="") ){
				echo "<response error='Title of new faq item is not given'></response>";
				return;
			}
			if( !(isset($_POST["answer"]) && trim($_POST["answer"])!=="") ){
				echo "<response error='Contents of new faq item is not given'></response>";
				return; 
			}

			$question = $_POST["question"];
			$answer = $_POST["answer"];
			if( isset($_POST["order"]) && is_numeric($_POST["order"])){
				$order = $_POST["order"];
			}else{
				$order = -1;
			}
			$faqs = new Default_Model_FAQs();
			$faqs->filter->orderby('ord');
			$fcnt = count($faqs->items);
			if($order==-1){
				$order = $fcnt+1;
			}
			//Prepare items ordering. Make space for new one;
			if($order<=$fcnt){
				for($i=0; $i<$fcnt; $i+=1){
					if( $faqs->items[$i]->ord >= $order){
						$faqs->items[$i]->ord = $faqs->items[$i]->ord+1;
					}
				}
				$faqs->save();
			}
			$faq = new Default_Model_Faq();
			$faq->question = $question;
			$faq->answer = $answer;
			$faq->ord = $order;
			$faq->locked = false;
			$faq->submitterid = $this->session->userid;
			$faqs->add($faq);
			$faqs->save();
			
			echo "<response></response>";
			return;
		} else if(isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == 'DELETE'){
			$this->_helper->viewRenderer->setNoRender();
			$invalidUser = ! (($this->session->userid !== null) && userIsAdminOrManager($this->session->userid));
			if ( $invalidUser ) {
				echo "<response error='access denied' />";
				return;
			}
			if( !(isset($_GET["id"]) && is_numeric($_GET["id"])) ){
				echo "<response error='No id specified'></response>";
				return;
			}
			$faqs = new Default_Model_FAQs();
			$faqs->filter->id->equals($_GET["id"]);
			if(count($faqs->items) > 0){
				//remove item
				$order = $faqs->items[0]->ord;
				$faqs->remove(0);
				$faqs->save();
				
				//reset ordering
				$faqs = new Default_Model_FAQs();
				$faqs->filter->orderby('ord');
				$fcnt = count($faqs->items);
				if($order <= $fcnt){
					for($ii=0; $i<$fcnt; $i+=1){
						if($faqs->items[$i]->ord >= $order){
							$faqs->items[$i]->ord = $faqs->items[$i]->ord-1;
						}
					}
					$faqs->save();
				}
				echo "<response></response>";
				return;
			}
		}else{
			$faqs = new Default_Model_FAQs();
			$faqs->filter->orderby('ord');
			$this->view->entries = $faqs->items;
		}
    }
	
	public function faqreorderAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		//Check user
		$invalidUser = ! (($this->session->userid !== null) && userIsAdminOrManager($this->session->userid));
		if ( $invalidUser ) {
			echo "<response error='access denied' />";
			return;
		}
		//Check parameters 
		$invalidParameters = !(isset($_POST["ordering"]) && trim($_POST["ordering"]) !== "");
		if( $invalidParameters ){
			echo "<response error='invalid parameters' />";
			return;
		}
		//Start reordering
		$ordering = split(",",$_POST["ordering"]);
		$faqs = new Default_Model_FAQs();
		$faqs->filter->orderby('ord');
		$cnt = count($faqs->items);
		
		if( $cnt == 0 ) {
			echo "<response error='No faqs to reorder'></response>";
			return;
		}
		$currentOrdering = 0;
		for($j=0; $j<count($ordering); $j+=1){
			for($i=0; $i<$cnt; $i++){
				//Check if faq exists (if any removed before reordering)
				if($faqs->items[$i]->id == $ordering[$j]){
					$currentOrdering += 1;
					$faqs->items[$i]->ord = $currentOrdering;
					break;
				}
			}
		}
		$faqs->save();	
		echo "<response></response>";
	}
    public function usageAction()
    {
		$this->_helper->layout->disableLayout();
    }

    public function announcementsAction()
    {
        $this->_helper->layout->disableLayout();
	}

	public function latestversionAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$ver = exec("cat ".APPLICATION_PATH."/../VERSION");
		$ver = explode(".", $ver);
		$ver = pow(100,3)*$ver[0]+pow(100,2)*$ver[1]+100*$ver[2];
		echo $ver;
	}

	public function rebuildsearchcacheAction() {
        $this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		if ( ($this->session->userid !== null) ) {
			if ( userIsAdmin($this->session->userid) ) {
				db()->exec('SELECT rebuildfiltercache();');
				echo 'Search cache rebuilt';
			}
		} else {
			$this->getResponse()->clearAllHeaders();
			header("HTTP/1.0 403 Forbidden");
		}
	}

	public function clearsearchcacheAction() {
        $this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		if ( ($this->session->userid !== null) ) {
			if ( userIsAdmin($this->session->userid) ) {
				db()->exec('SELECT invalidate_filtercache();');
				echo 'Search cache cleared';
			}
		} else {
			$this->getResponse()->clearAllHeaders();
			header("HTTP/1.0 403 Forbidden");
		}
	}

	public function editfaqAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		/*$id = $this->_getParam('id');
		$txt = $this->_getParam('answer');
		$question = null;
		
		if ( $txt == '' ) {
			$this->getResponse()->clearAllHeaders();
			header("HTTP/1.0 400 Bad Request");
			echo 'Missing FAQ answer';
			return;
		}
		
		if ( isset($_POST["question"]) && trim($_POST["question"]) != "" ) {
			$question = $this->_getParam('question');
			$question = htmlspecialchars($question);
		}
		$users = new Default_Model_Researchers();
		$users->filter->id->equals($this->session->userid);
		if ( count($users->items) ) $user = $users->items[0]; else $user = null;
		if ( ($user !== null) && $user->privs->canEditFAQs() ) {
			$faq = null;
			if ( $id != '' ) {
				$faqs = new Default_Model_FAQs();
				$faqs->filter->id->equals($id);
				if ( count($faqs->items) > 0 ) {
					$faq = $faqs->items[0];
					$faq->answer = $txt;
					if ( $question != null ) {
						$faq->question = $question;
					}
					$faq->submitterid = $this->session->userid;
					if ( $this->_getParam('ord') != '' ) $faq->ord = $this->_getParam('ord');
				}
			} else {
				$faq = new Default_Model_FAQ();
				$faq->question = $this->_getParam('question');
				if ( $faq->question == '' ) {
					$this->getResponse()->clearAllHeaders();
					header("HTTP/1.0 400 Bad Request");
					echo 'Missing FAQ question';
					return;
				}
				$faq->answer = $txt;
				$faq->submitterid = $this->session->userid;
				if ( $this->_getParam('ord') != '' ) $faq->ord = $this->_getParam('ord');
			}
			if ( $faq !== null ) {
				$faq->when = 'NOW()';
				$faq->save();
				$id = $faq->id;
				$faqs = new Default_Model_FAQs();
				$faqs->filter->id->equals($id);
				$faq = $faqs->items[0];
				$d = new DateTime($faq->when);
				$this->getResponse()->clearAllHeaders();
				header("HTTP/1.0 200 OK");
				header('Content-type: text/xml');
				echo "<?xml version='1.0'?" . "><response id='" . $faq->id . "' submitterId='" . $faq->submitter->id . "' submitterName='" . $faq->submitter->name . "' when='" . $d->format('d M Y, H:i') . "' order='" . $faq->ord . "' locked='" . $faq->locked . "'><question>" . base64_encode($faq->question) . "</question><answer>" . base64_encode($faq->answer) . "</answer></response>";
			} else {
				$this->getResponse()->clearAllHeaders();
				header("HTTP/1.0 404 Not Found");
				echo 'Requested FAQ item not found';
			}
		} else {*/
			$this->getResponse()->clearAllHeaders();
			header("HTTP/1.0 403 Forbidden");
			echo 'Access Denied';
		//}
	}

	public function cachebuildcountAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		db()->setFetchMode(Zend_Db::FETCH_OBJ);
		$res = db()->query("SELECT data FROM config WHERE var = 'cache_build_count'")->fetchAll();
		try {
			echo (int)($res[0]->data);
		} catch(Exception $e) {
				echo 0;
		}
	}

	public function shortenurlAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		echo shortenURL($this->_getParam("url"));
	}

	public function wikiAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$page = ( isset($_GET["page"]) )?strval($_GET["page"]):null;
		if( $page === null ){
			header("HTTP/1.0 404 Not Found");
			return;
		}
		$data = @web_get_contents("https://wiki.appdb.egi.eu/". $page . "?do=export_xhtmlbody");
		if( $data === false ){
			header("HTTP/1.0 404 Not Found");
			return;
		}
		echo "<div class='wikipage'>".$data."</div>";
	}
}
