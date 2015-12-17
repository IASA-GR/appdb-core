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


//The remote server name e.g. appdb.egi.eu
$remote_server = $_SERVER["SERVER_NAME"];

//Name of local server where this proxy is hosted
$local_server = $_SERVER["SERVER_NAME"];

$config = array(
	"localhost" => array(
		"url" => "http://" . $local_server
	),
	"localpath" => array(
		"url" => "http://" . $local_server . "/commrepo/api/"
	),
	"feed" => array(
		"url" => "https://".$remote_server."/repository/feed/production?proxy=" . urlencode($local_server . "/commrepo/api"),
		"headers" => array("content-type: application/rss+xml")
	),
	"software" => array(
		"url" => "https://".$remote_server."/rest/1.0/applications/:swid",
		"headers" => array("content-type: text/xml")
	),
	"series" => array(
		"url" => "https://".$remote_server."/repository/repositoryarea/item?view=data&id=:seriesid",
		"headers" => array("content-type: text/xml")
	),
	"release" => array(
		"url" => "https://".$remote_server."/repository/release/item?view=data&id=:releaseid",
		"headers" => array("content-type: text/xml")
	),
	"swimage" => array(
		"url" => "https://".$remote_server."/apps/getlogo?id=:swimageid",
		"headers" => array("content-type: image/png")
	)
);

function getConfig($name, $params=null){
	global $config;
	$val = null;
	if( trim($name) !== "" ){
		$names = explode(".",$name);
		if( count($names) > 1 ){
			$cursor = 0;
			$val = $config;
			while($cursor < count($names)){
				if( !isset($names[$cursor]) && !isset($config[$names[$cursor]])){
					break;
				}
				$val = $val[$names[$cursor]];
				$cursor += 1;
			}
		}else{
			$val = (isset($config[$name]))?$config[$name]:"";
		}
	}else{
		return $val;
	}
	
	
	if(is_array($params)===true && count($params) > 0){
		foreach($params as $k=>$v){
			$val = str_replace(":".$k, $v, $val);
		}
	}
	
	return $val;
}

function echoFetchedData($url,$headers,$fn=null){
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	if ( defined('CURLOPT_PROTOCOLS') ) curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	
	curl_close($ch);
	
	if( is_string($fn) ){
		$response = $fn($response);
	}
	//header_remove();
	if( is_array($headers) ){
		foreach($headers as $h){
			header($h);
		}
	}
	echo $response;
}

