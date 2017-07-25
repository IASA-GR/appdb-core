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
class Default_Model_VMINetworkTrafficEntry extends Default_Model_VMINetworkTrafficEntryBase
{
	protected $_netProtocols;
	protected $_flow;

	public function setNetProtocols($value) {
		if (! is_array($value)) {
			$v = trim(str_replace(",", " ", $value));
			$value = array();
			foreach (explode(" ", $v) as $vv) {
				$value[] = trim($vv);
			}
		}
		$bits = 0;
		foreach ($value as $v) {
			switch (strtoupper($v)) {
				case "TCP":
					$bits |= 1;
					break;
				case "UDP":
					$bits |= 2;
					break;
				case "ICMP":
					$bits |= 4;
					break;
				case "IPSEC":
					$bits |= 8;
					break;
				case "ANY":
					$bits |= 15;
					break;
				default:
					error_log($v);
					throw new Exception("network traffic protocol must be one of \`Any, TCP, UDP, ICMP, IPsec'");
			}
		}
		$this->_netProtocols = $value;
		$this->_netProtocolBits = sprintf("%032d", decbin($bits));
	}

	public function getNetProtocols() {
		return $this->_netProtocols;
	}

	public function setFlow($value) {
		$bits = 0;
		switch (strtolower($value)) {
			case "none":
				break;
			case "inbound":
				$bits |= 1;
				break;
			case "outbound": 
				$bits |= 2;
				break;
			case "both":
				$bits |= 3;
				break;
			default:
				throw new Exception("network traffic flow must be one of \`Inbound, Outbound, Both'");
		}
		$this->_flow = $value;
		$this->_flowBits = sprintf("%02d", decbin($bits));
	}

	public function getFlow() {
		return $this->_flow();
	}

	public function setNetProtocolBits($value)
	{
		if (! is_string($value)) {
			$value = sprintf("%032d", decbin($value));
		}
		$this->_netProtocolBits = $value;
		return $this;
	}

	public function setFlowBits($value)
	{
		if (! is_string($value)) {
			$value = sprintf("%02d", decbin($value));
		}
		$this->_flowBits = $value;		
		return $this;
	}


}
