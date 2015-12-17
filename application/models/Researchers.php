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
class Default_Model_Researchers extends Default_Model_ResearchersBase
{
	protected $_userid;
	protected $_viewModerated;

	public function __construct($filter = null)
	{
		parent::__construct($filter);
		if ( isset($_GET['userid'] ) ) $this->_userid = $_GET['userid'];
	}

	protected function getViewModerated() {
		if ( ! isset($this->_viewModerated) ) {
			$this->_viewModerated = false;
			
			return false;

			if ( isset($this->_userid) ) {
				if ( userIsAdminOrManager($this->_userid) ) {
					$this->_viewModerated = true;
				}
			}
		}
		return $this->_viewModerated;
    }

    protected function setViewModerated($v) {
        $this->_viewModerated = $v;
    }

	public function refresh($format = '', $xmldetailed = false, $userid = '')
    {
        if ( $userid == '' ) $userid = $this->_userid;
		if ( ! $this->viewModerated ) {
			$ex = $this->_filter->expr();
			if ( is_array($ex) ) {
				$ex = implode(" ", $ex);
			}
			if ( strpos($ex, 'researchers.deleted) IS FALSE') === false ) {
				$f = new Default_Model_ResearchersFilter();
				$f->deleted->equals(false);
				$this->_filter->chain($f,"AND");
			}
		}
        if ( $format === 'xml') {
			$this->_items = $this->getMapper()->fetchAll($this->_filter, 'xml', $userid, $xmldetailed);
		} elseif ( $format === 'csvexport' || $format === 'xmlexport' )  {
			$this->_items = $this->getMapper()->fetchAll($this->_filter, $format, $userid);
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
			if ( strpos($ex, 'researchers.deleted) IS FALSE') === false ) {
				$f = new Default_Model_ResearchersFilter();
				$f->deleted->equals(false);
				$this->_filter->chain($f,"AND");
			}
		}
		return parent::count();
	}

	public function toXML() {
		$items = $this->getMapper()->fetchAll($this->filter, 'xml');
		return "<people>".implode($items)."</people>";
	}

}
