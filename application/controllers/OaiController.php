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

class OaiController extends Zend_Controller_Action
{
	public function indexAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();        
		$verb = $this->_getParam("verb");
		$ret = $this->handleVerb($verb);
		if ($ret === false) {
			header("HTTP/1.0 500 Internal Server Error");
			header("Status: 500 Internal Server Error");
			exit;
		} else {
			header("Content-Type:text/xml");
			echo $ret;
		}
	}

	private function handleVerb($verb) {
		debug_log($verb);
		switch($verb) {
			case "GetRecord":
				$id = $this->_getParam("identifier");
				$prefix = $this->_getParam("metadataPrefix");
				return $this->getRecord($id, $prefix);
			case "Identify":
			case "ListIdentifiers":
			case "ListMetadataFormats":
			case "ListRecords":
			case "ListSets":
				return "<unimplemented/>";
			default:
				return $this->buildResponse($this->getError("badVerb", "Value of the verb argument is not a legal OAI-PMH verb, the verb argument is missing, or the verb argument is repeated."));
		}
	}

	private function getError($code, $desc) {
		return '<error code="'.$code.'">'.$desc.'</error>';
	}

	private function buildResponse($body, $verb = '', $prefix = '', $from = '', $until = '') {
		$res = '<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" 
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
<responseDate>'.str_replace(" ", "T", date("Y-m-d H:i:s")).'Z</responseDate> 
<request ';
		if ( $verb != '' ) $res .= 'verb="'.$verb.'" ';
		if ( $from != '' ) $res .= 'from="'.$from.'" ';
		if ( $until != '' ) $res .= 'until="'.$until.'" ';
		if ( $prefix != '' ) $res .= 'metadataPrefix="'.$prefix.'" ';
		$res .= '>'."http://".$_SERVER["APPLICATION_UI_HOSTNAME"]."/".'</request>';
		if ( $verb != '' ) $res .= '<' . $verb . '>';
		$res .= $body; 
		if ( $verb != '' ) $res .= '</'. $verb . '>';
		$res .= '</OAI-PMH>';
		return $res;
	}

	private function getRecord($id, $prefix) {
		if ( ($id == "") || ($prefix == "") ) {
			return $this->getError("badArgument", "The request is missing required arguments");
		} else {
			if ( $prefix == "oa_dc" ) {
				if ( substr($id, 0, 17) == "oai:appdb.egi.eu:" ) {
					$item = substr($id, 17);
					while ( substr($item, 0, 1) == "/" ) $item = substr($item, 1);
					$items = explode("/", $item);
					$resource = $items[0];
					$itemid = $items[1];
					switch($resource) {
						case "applications":
							$res = new RestAppItem(array("id" => $itemid));
							break;
						case "people":
							$res = new RestPplItem(array("id" => $itemid));
							break;
						default:
							return $this->buildResponse($this->getError("badArgument", "Requested invalid resource"), "GetRecord", $prefix);
					}
					debug_log("[OaiController::getRecord]: Getting " . "http://".$_SERVER["APPLICATION_API_HOSTNAME"]."/rest/latest/$item");
					$res = strval($res->get());
					$res = $this->buildResponse($res, "GetRecord", $prefix);
					$xml = xml_transform(RestAPIHelper::getFolder(RestFolderEnum::FE_XSL_FOLDER) . "oai-applications.xsl", $res);
					# if the XSL transformation fails, $xml will be FALSE
					# callers will be responsible for returning a proper HTTP response(HTTP/1.0 500), since OAI-PMH only allows for certain application-level error codes 
					# https://knb.ecoinformatics.org/knb/docs/oaipmh.html#oai-pmh-error-codes
					return $xml;
				} else {
					return $this->buildResponse($this->getError("idDoesNotExist", "Item not found"));
				}
			} else {
				return $this->buildResponse($this->getError("cannotDisseminateFormat", "The metadata format identified by the value given for the metadataPrefix argument is not supported by the item or by the repository."));
			}
		}
	}
} 
