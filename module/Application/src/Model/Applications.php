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
class Default_Model_Applications extends Default_Model_ApplicationsBase
{
	protected $_userid;
	protected $_viewModerated; // defaults to false

	public function __construct($filter = null)
	{
		parent::__construct($filter);
		if ( isset($_GET['userid'] ) ) $this->_userid = $_GET['userid'];
	}

	public static function nameAvailable($name, $id = null) {
		global $application;
		$db = $application->getBootstrap()->getResource('db');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);
        if ( $id != '' ) $where = " WHERE id <> $id"; else $where = '';
		$res = $db->query("SELECT * FROM app_name_available(E'".pg_escape_string($name)."')$where")->fetchAll();
		if ( count($res) == 0 ) {
			return true;
		} else {
			$a = new Default_Model_Application();
			$m = new Default_Model_ApplicationsMapper();
			$m->populate($a,$res[0]);
			return $a;
		}
	}

	public function getMapper() {
		$m = parent::getMapper();
		$m->_userid = $this->_userid;
		return $m;
	}

	protected function setViewModerated($value) {
		$this->_viewModerated = $value;
	}

	protected function getViewModerated() {
		if ( ! isset($this->_viewModerated) ) {
			$this->_viewModerated = false;

			return false;

			if ( isset($this->_userid) ) {
				if (userIsAdminOrManager($this->_userid)) {
					$this->_viewModerated = true;
				}
			}
		}
		return $this->_viewModerated;
	}

  public function refresh($format = '', $xmldetailed = false)
  {
	  if ( ! $this->viewModerated ) {
			$ex = $this->_filter->expr();
			if ( is_array($ex) ) {
				$ex = implode(" ", $ex);
			}
			if (
			  ( strpos($ex, 'applications.moderated) IS FALSE') === false ) ||
			  ( strpos($ex, 'applications.deleted) IS FALSE') === false )
			) {
				$f = new Default_Model_ApplicationsFilter();
				$f->moderated->equals(false)->and($f->deleted->equals(false));
				$this->_filter->chain($f,"AND");
			}
		}
		if ( $format === 'xml') {
			$this->_items = $this->getMapper()->fetchAll($this->_filter, 'xml', $xmldetailed);
		} elseif ( $format === 'csvexport' || $format === 'xmlexport' ) {
			$this->_items = $this->getMapper()->fetchAll($this->_filter, $format);
		} else {
			$this->_items = $this->getMapper()->fetchAll($this->_filter);
		}
		return $this;
	}

	public function count() {
		if ( ! $this->viewModerated ) {
			$ex = $this->_filter->expr();
			if ( is_array($ex) ) {
				$ex = implode(" ", $ex);
			}
		  if (
			  ( strpos($ex, 'applications.moderated) IS FALSE') === false ) ||
			  ( strpos($ex, 'applications.deleted) IS FALSE') === false )
		  ) {
			$f = new Default_Model_ApplicationsFilter();
			$f->moderated->equals(false)->and($f->deleted->equals(false));

			$this->_filter->chain($f,"AND");
		  }
		}
		return parent::count();
	}

	public function toXML() {
		$items = $this->getMapper()->fetchAll($this->filter, 'xml');
		return "<applications>".implode($items)."</applications>";
	}

	public function setItems($v)                                                                          
	{                                                                                                     
		$this->_items = $v;                                                                               
		return $this;                                                                                     
	}        
}
