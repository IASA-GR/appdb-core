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
class Gocdb {
	
	private static function getXMLFileName(){
		$now = "";
		$filename = "../public/gocdbsites" . $now . ".xml";
		return $filename;
	}
	//Calls GocDB PI method to retrieve xml for sites.
	//The result is stored in ref aprameter xmldata.
	//In case of error it returns false or description
	//of error.
	private static function getSites(&$xmldata){
		$ch = curl_init();
		$url = "https://goc.egi.eu/gocdbpi/public/?method=get_site";
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, 181, 1 | 2);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSLCERT, $_SERVER['APPLICATION_PATH'] . '/../bin/sec/usercert.pem');
		curl_setopt($ch, CURLOPT_SSLKEY, $_SERVER['APPLICATION_PATH'] . '/../bin/sec/userkey.pem');
		$headers = apache_request_headers();
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$data = curl_exec ($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error = curl_error($ch); 
		
		curl_close ($ch);
		if( !$error && trim($code) !== "200" ){
			return "GogDB PI responded with: " . self::getHttpErrorCodes($code);
		}
		
		$filesize = strlen( $data );
		if( $filesize === 0 ){
			return "GocDB PI: No data retrieved";
		}
		$xmldata = $data;
		return true;
	}
	
	//Stores xml data in ../public/giocdbsites.<datestamp>.xml file. 
	//In case of error it returns false or description of error.
	private static function saveXmlData($xml){
		$filename = self::getXMLFilename();
		if( !file_put_contents($filename, $xml) ){
			return "Could not save gocdb xml data";
		}
		return true;
	}
	
	//returns an array of sites. In case of error it 
	//returns false or description of error.
	private static function parseGocDBSitesXml(){
		$filename = self::getXMLFilename();
		if(file_exists($filename) === false ) {
			return "GocDB Could not load site xml file";
		}
		$result = array();
		
		$xml = simplexml_load_file( $filename );
		foreach($xml->SITE as $site){
			$item = array(
				"pkey" => (string)$site["PRIMARY_KEY"],
				"name" => (string)$site["NAME"],
				"shortname" => (string)$site->SHORT_NAME,
				"officialname" => (string)$site->OFFICIAL_NAME,
				"description" => (string)$site->SITE_DESCRIPTION,
				"portalurl" => (string)$site->GOCDB_PORTAL_URL,
				"homeurl" => (string)$site->HOME_URL,
				"contactemail" => (string)$site->CONTACT_EMAIL,
				"contacttel" => (string)$site->CONTACT_TEL,
				"alarmemail" => (string)$site->ALARM_EMAIL,
				"csirtemail" => (string)$site->CSIRT_EMAIL,
				"giisurl" => (string)$site->GIIS_URL,
				"countrycode" => (string)$site->COUNTRY_CODE,
				"country" => (string)$site->COUNTRY,
				"tier" => (string)$site->TIER,
				"subgrid" => (string)$site->SUBGRID,
				"roc" => (string)$site->ROC,
				"prodinfrastructure" => (string)$site->PRODUCTION_INFRASTRUCTURE,
				"certstatus" => (string)$site->CERTIFICATION_STATUS,
				"timezone" => (string)$site->TIMEZONE,
				"latitude" => (string)$site->LATITUDE,
				"longitude" => (string)$site->LONGITUDE,
				"domainname" => "",
				"siteip" => (string)$site->SITE_IP
			);
			$domains = $site->xpath("./DOMAIN/DOMAIN_NAME");
			if( count($domains) > 0 ){
				$item["domainname"] = (string)$domains[0];
			}
			$result[] = $item;
		}
		return $result;
	}
	
	private static function createSQLStatements($data){
		$queries = array();
		
		foreach($data as $d){
			$update =  "UPDATE gocdb.sites SET ";
			$vals = array();
			foreach($d as $k=>$v){
				$vals[] = "?";
				$update .= $k . " = ? ,";
			}
			$update .= " deleted = false WHERE pkey = '" . $d["pkey"] ."';";
			$queries[] = array( 
				"insertquery"=>"INSERT INTO gocdb.sites (" . implode( ",", array_keys($d) ) . ") VALUES (" . implode( "," , $vals) . " );",
				"values"=> array_values($d),
				"updatequery"=> $update,
				"data" => $d
			);
		}
		
		return $queries;
	}
	
	private static function getFetchedIds($data){
		$ids = array();
		foreach($data as $d){
			if( in_array($d["pkey"], $ids) === false ){
				$ids[] = "'" . $d["pkey"] . "'";
			}
			
		}
		return $ids;
	}
	
	private static function insertAppDB($data){
		$count = 0;
		$sqls = self::createSQLStatements($data);
		db()->beginTransaction();
		try{
			db()->query("DELETE FROM gocdb.sites;")->fetchAll();
			foreach( $sqls as $sql ){
				db()->query($sql["insertquery"], $sql["values"])->fetchAll();
				$count += 1;
			}
			db()->commit();
		} catch (Exception $ex) {
			db()->rollback();
			error_log("[Gocdb::insertAppDB] " . $ex->getMessage());
			return $ex->getMessage();
		}
		return array("inserted" => $count, "updated"=> "0", "deleted"=> "0");
	}
	
	private static function updateAppDB($data){
		$newcount = 0;
		$updatedcount = 0;
		$deletedcount = 0;
		$sqls = self::createSQLStatements($data);
		$ids = self::getFetchedIds($data);
		
		db()->beginTransaction();
		try{
			db()->query("UPDATE gocdb.sites SET deleted=TRUE, deletedon = now(), deletedby = 'gocdb' where deleted=FALSE AND pkey NOT IN (" . implode(",", $ids) . ");")->fetchAll();
			foreach( $sqls as $sql ){
				$data = $sql["data"];
				$pkey = $data["pkey"];
				$res = db()->query("SELECT * FROM gocdb.sites WHERE pkey = ?", array($pkey) )->fetchAll();
				if( count($res) > 0 ){
					db()->query($sql["updatequery"], $sql["values"])->fetchAll();
					$updatedcount += 1;
				}else{
					db()->query($sql["insertquery"], $sql["values"])->fetchAll();
					$newcount += 1;
				}
			}
			$deleted = db()->query("SELECT COUNT(*) FROM gocdb.sites WHERE deleted = TRUE;")->fetchAll();
			if( count($deleted) > 0 ){
				$deletedcount = $deleted[0]["count"];
			}
			db()->commit();
		}catch(Exception $ex){
			db()->rollback();
			error_log("[Gocdb::updateAppDB] " . $ex->getMessage());
			return $ex->getMessage();
		}
		return array("inserted"=>$newcount, "updated"=>$updatedcount, "deleted"=> $deletedcount);
	}
	
	//Returns number of insertions
	private static function syncAppDB($data, $update = true){
		if( $update === false ){
			return self::insertAppDB($data);
		}
		return self::updateAppDB($data);
	}
	
	//Syncs GocDB sites with AppDB sites table.
	//Returns number of insertions. In case of
	//error it returns false or description of error.
	public static function syncSites($update = true, $force = false){
		$xmldata = "";
		if( $force === true || file_exists(self::getXMLFileName()) === false ) {
			$res = self::getSites($xmldata);
			if( $res !== true ){
				return $res;
			}
			$res = self::saveXmlData($xmldata);
			if( $res !== true ){
				return $res;
			}
		}
		
		$res = self::parseGocDBSitesXml();
		if( $res === false || is_string($res) ){
			return $res;
		}
		
		return self::syncAppDB($res, $update);
	}
}
?>
