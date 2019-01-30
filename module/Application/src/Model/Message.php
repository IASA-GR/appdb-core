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

class Message extends MessageBase
{
	protected $_receiver;
	protected $_sender;

	function getReceiver() {
		if ( $this->_receiver === null ) {
			$users = new Researchers();
			$users->filter->id->equals($this->receiverID);
			$this->_receiver = $users->items[0];
		}
		return $this->_receiver;
	}

	function getSender() {
		if ( $this->_sender === null ) {
			$users = new Researchers();
			$users->filter->id->equals($this->senderID);
			$this->_sender = $users->items[0];
		}
		return $this->_sender;
	}

	public function toXML($recursive=false) {
		$xml = parent::toXML();
		$x2="";
		if ( $recursive ) if ( $this->_sender === null ) $this->getSender();
		if ( ! ($this->_sender === null) ) $x2 .= $this->sender->toXML();
		if ( $recursive ) if ( $this->_receiver === null ) $this->getReceiver();
		if ( ! ($this->_receiver === null) ) $x2 .= $this->receiver->toXML();
		if ( $x2 != "") $xml = preg_replace("/<\/Message>/",$x2."</Message>",$xml);
		return $xml;
	}

}
