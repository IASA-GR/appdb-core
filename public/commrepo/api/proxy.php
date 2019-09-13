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

include("config.php");
$url = null;
$headers = null;
$cb = false;

function transformSoftware($response){
	$xml = simplexml_load_string($response);
	if ($xml === false) {
		error_log("Cannot parse software response as XML");
		return $response;
	}
	$sw = simplexml_load_string("<software></software>");
	$swpath = $xml->xpath("//application:application");
	if( count($swpath)>0 ){
		$swpath = $swpath[0];
		$sw->addAttribute("id",$swpath->attributes()->id);
		$sw->addAttribute("cname",$swpath->attributes()->cname);
		
		$x = $swpath->xpath("./application:name");
		if( count($x) > 0 ){
			$x = $x[0];
			$s = $sw->addChild("name");
			$s[0] = $x;
		}
		$x = $swpath->xpath("./application:description");
		if( count($x) > 0 ){
			$x = $x[0];
			$s = $sw->addChild("description");
			$s[0] = $x;
		}
		$x = $swpath->xpath("./application:abstract");
		if( count($x) > 0 ){
			$x = $x[0];
			$s = $sw->addChild("abstract");
			$s[0] = $x;
		}
		$x = $swpath->xpath("./application:addedOn");
		if( count($x) > 0 ){
			$x = $x[0];
			$s = $sw->addChild("addedOn");
			$s[0] = $x;
		}
		
		$x = $swpath->xpath("./application:lastUpdated");
		if( count($x) > 0 ){
			$x = $x[0];
			$s = $sw->addChild("lastUpdated");
			$s[0] = $x;
		}
		$x = $swpath->xpath("./application:addedOn");
		if( count($x) > 0 ){
			$x = $x[0];
			$s = $sw->addChild("addedOn");
			$s[0] = $x;
		}
		$x = $swpath->xpath("./application:category");
		if( count($x) > 0 ){
			$cats = $sw->addChild("categories");
			foreach($x as $c){
				$s = $cats->addChild("category");
				$s->addAttribute("id",$c->attributes()->id);
				$s->addAttribute("primary",$c->attributes()->primary);
				if( $c->attributes()->parentid ){
					$s->addAttribute("parentid",$c->attributes()->parentid);
				}
				$s[0] = strval($c);
			}
			
		}
		
		$x = $swpath->xpath("./discipline:discipline");
		if( count($x) > 0 ){
			$disc = $sw->addChild("disciplines");
			foreach($x as $c){
				$s = $disc->addChild("discipline");
				$s->addAttribute("id",$c->attributes()->id);
				if( $c->attributes()->parentid ){
					$s->addAttribute("parentid",$c->attributes()->parentid);
				}
				$s[0] = strval($c);
			}
		}
		$x = $swpath->xpath("./vo:vo");
		if( count($x) > 0 ){
			$vos = $sw->addChild("vos");
			foreach($x as $c){
				$s = $vos->addChild("vo");
				$s->addAttribute("id",$c->attributes()->id);
				$s->addAttribute("name",$c->attributes()->name);
				$s[0] = strval($c);
			}
		}
		$img = $sw->addChild("image");
		$img[0] = getConfig("localpath.url") . "proxy.php?swimageid=" . $sw->attributes()->id;
		return $sw->asXML();
	}
	return $response;
}
if( isset($_GET["type"]) && trim($_GET["type"]) === "release" && isset($_GET["id"]) && is_numeric($_GET["id"])){
	$url = getConfig("release.url",array("releaseid"=>trim($_GET["id"])));
	$headers = getConfig("release.headers");
}else if(isset($_GET["type"]) && trim($_GET["type"]) === "sw" && isset($_GET["id"]) && is_numeric($_GET["id"])){
	$url = getConfig("software.url",array("swid"=>trim($_GET["id"])));
	$headers = getConfig("software.headers");
	$cb = 'transformSoftware';
}else if(isset($_GET["type"]) && trim($_GET["type"]) === "series" && isset($_GET["id"]) && is_numeric($_GET["id"])){
	$url = getConfig("series.url",array("seriesid"=>trim($_GET["id"])));
	$headers = getConfig("series.headers");
}else if( isset($_GET["swimageid"]) && is_numeric($_GET["swimageid"])){
	$url = getConfig("swimage.url",array("swimageid"=>trim($_GET["swimageid"])));
	$headers = getConfig("swimage.headers");
}

if( trim($url) !== "" ){
	echoFetchedData($url, $headers,$cb);
}else{
	header("Status: 404 Not Found");
}
