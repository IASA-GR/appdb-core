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
class Default_Model_AppDocument extends Default_Model_AppDocumentBase
{
	protected $_authors;

	public function getAuthors() {
		if ($this->_authors === null) {
			$a = new Default_Model_Authors();
			$a->filter->docid->equals($this->id);
			$this->_authors = $a;
		}
		return $this->_authors->items;
	}

	public function toXML($recursive=false)
	{
		$this->getDocType();
		$xml=parent::toXML($recursive);
		$x2="";
		$this->getAuthors();
		if ( $this->_authors !== null ) foreach ($this->_authors as $a) { $x2.=$a->toXML(); }
		$xml=preg_replace('/<\/AppDocument>/',$x2."</AppDocument>",$xml);
		return $xml;
	}

}
