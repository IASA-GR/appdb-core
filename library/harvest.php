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

/**
 * Provides helper functions for searching and associating 
 * harvested data objects from OpenAire. 
 *
 * @author nakos
 */
class harvest {
	/* raw field ids & names
	* -- organizations
	* 315=>"project.rel.country.classid"
	* 316=>"project.rel.country.classname"
	* 317=>"project.rel.legalname"
	* 318=>"project.rel.legalshortname"
	* 
	* --projects
	* 281 => "code"
	* 282 => "acronym"
	* 283 => "title"
	* 
	* --persons
	* 322 => "fullname",
	* 320 => "email"
	*/
	
	/*
	 * Retrieve searchable properties of harvested archived objects 
	 * base on the given archive id.
	 * 
	 * These properies are stored in the harvest.raw_fields table
	 */
	public static function getSearchProperties($archiveid){
		switch( intval($archiveid) ) {
			case 1: //projects
				return array (281 => "code", 282 => "acronym", 283 => "title");
			case 2: //publications
				return array();
			case 3: //organizations
				return array (315=>"project.rel.country.classid",316=>"project.rel.country.classname",317=>"project.rel.legalname",318=>"project.rel.legalshortname");
			case 4: //persons
				return array (322 => "project.rel.fullname", 320 => "project.rel.email");
		}
	}
	
	/*
	 * Prepare the given search text for usage.
	 * Splits it into keywords and returns 
	 * a keyword array.
	 */
	private static function textToKeywords($text = ""){
		$stext = explode(" ", preg_replace('/\s\s+/', ' ', trim($text) ));
		$res = array();
		foreach($stext as $t){
			if( strlen($t) > 1 && substr($t,-1) === '%' ){
				$res[] = strtolower($t);
			}else if( strlen($t) > 0 ) {
				$res[] = strtolower($t . '%');
			}
		}
		return $res;
	}
	
	private static function doSearchHarvestedData($archiveid, $keywords = array(), $keyfields=array(),$limit = 50){
		$q = "SELECT harvest.search_records_to_xml((?)::TEXT[],(?)::TEXT[],?,?)";
		$stmt = db()->query($q, array( php_to_pg_array($keywords, false), php_to_pg_array($keyfields, false), $archiveid, $limit) );
		$rows = $stmt->fetchAll();
		$res = array();
		if( count($rows) > 0 ){
			foreach($rows as $r){
				$res[] = $r["search_records_to_xml"];
			}
		}
		return $res;
	}
	private static function doSearchLocalData($archiveid, $keywords = array(), $keyfields=array(),$limit = 50){
		$q = "SELECT harvest.search_local_records_to_xml((?)::TEXT[],(?)::TEXT[],?,?)";
		$stmt = db()->query($q, array( php_to_pg_array($keywords, false), php_to_pg_array($keyfields, false), $archiveid, $limit) );
		$rows = $stmt->fetchAll();
		$res = array();
		if( count($rows) > 0 ){
			foreach($rows as $r){
				$res[] = $r["search_local_records_to_xml"];
			}
		}
		return $res;
	}
	
	/*
	 * Actual call to the DB for searching.
	 */
	private static function doSearch($archiveid, $keywords = array(), $keyfields=array(),$limit = 50){
		$res = self::doSearchLocalData($archiveid, $keywords, $keyfields, $limit);
		$res = array_merge($res, self::doSearchHarvestedData($archiveid, $keywords, $keyfields, $limit));
		return $res;
	}
	
	/*
	 * Search in available harvested archives for given text
	 */
	public static function search($archiveid, $search = "", $limit = 50){
		if (($archiveid == 1) || ($archiveid == 3)) {
			return openAIRE::search($archiveid, $search, $limit);
		} else {
			$props = array_values( self::getSearchProperties($archiveid) );
			$keys = self::textToKeywords($search);
			return self::doSearch($archiveid, $keys, $props, $limit);
		}
	}
	
	public static function getRecord($recordid){
		$rows = db()->query("select * from harvest.records where record_id = ?", array($recordid))->fetchAll();
		if( count($rows) === 0 ){
			return null;
		}
		return $rows[0];
	}
	public static function getRecordByExtIdentifier($extidentifier){
		$rows = db()->query("select * from harvest.records where external_identifier = ?", array($extidentifier))->fetchAll();
		if( count($rows) === 0 ){
			return null;
		}
		return $rows[0];
	}
	public static function getRecordByIdentifier($identifier){
		$rows = db()->query("select * from harvest.records where appdb_identifier = ?", array($identifier))->fetchAll();
		if( count($rows) === 0 ){
			return null;
		}
		return $rows[0];
	}
	public static function getRecordType($recordid){
		$rec = null;
		if( is_numeric($recordid) ){
			$rec = self::getRecord($recordid);
		}else {
			$rec = $recordid;
		}
		if( is_array($rec) && isset($rec["archive_id"]) ){
			$archid = intval($rec["archive_id"]);
		}else{
			$archid = intval($rec->archive_id);
		}
		
		switch( $archid ) {
			case 1: //projects
				return "project";
			case 2: //publications
				return "publication";
			case 3: //organizations
				return "organization";
			case 4: //persons
				return "person";
		}
	}
	public static function importRecord($recordid, $userid=null){
		$rec = self::getRecord($recordid);
		if( $rec === null ){
			return "Could not retrieve record";
		}
		$rectype = self::getRecordType($rec);
		switch( $rectype ) {
			case "project":
				return HarvestProjects::import($recordid, $userid);
			case "publication":
				return "Harvest import does not support publication type";
			case "organization":
				return HarvestOrganizations::import($recordid, $userid);
			case "person": 
				return "Harvest import does not support person type";
		}
	}
}
class HarvesterInitRelations {
	private static function getInstitutionsToOrganizations(){
		$fmode = db()->getFetchMode();
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$query = "SELECT id, guid, record_id::TEXT, institution FROM researcher_institution_organization_map UNION ALL SELECT id, guid, orgguid::TEXT AS record_id, institution FROM researcher_institution_local_organization_map";

		$q = db()->query($query);
		$res = $q->fetchAll();
		db()->setFetchMode($fmode);
		return $res;
	}
	
	private static function getPersonOrganizationRelationType(){
		$reltypes = new Default_Model_RelationTypes();
		$f1 = new Default_Model_RelationTypesFilter();
		$f2 = new Default_Model_RelationTypesFilter();
		$f3 = new Default_Model_RelationTypesFilter();
		
		$f1->subject_type->equals('person');
		$f2->target_type->equals('organization');
		$f3->verbid->numequals(1); //"employee"
		
		$reltypes->filter->chain($f1->chain($f2->chain($f3, 'AND'), 'AND'),'AND');
		if( count($reltypes->items) > 0 ){
			return $reltypes->items[0];
		}
		return null;
	}
	
	private static function pairResearcherOrganization($reltype, $userid, $recordids, $unrelateold = true){
		$ps = new Default_Model_Researchers();
		$ps->filter->id->equals($userid);
		$p = null;
		if( count($ps->items) > 0 ){
			$p = $ps->items[0];
		}
		if( $p !== null ){
			$rels = array();
			foreach($recordids as $recordid){
				$rel = array(
					"id" => $reltype->id,
					"targetguid" => $recordid
				);
				$rel["parentid"] = null;
				$rels[] = $rel;
			}
			
			$res = EntityRelations::syncRelations($p->guid, $p->id, $rels, false, $unrelateold);
			if( $res !== true ){
				throw new Exception($res);
			}
		}
	}
	private static function getUniquePairs($orginsts){
		$res = array();
		for($i=0; $i<count($orginsts); $i+=1){
			$id = $orginsts[$i]["id"];
			$rid = $orginsts[$i]["record_id"];
			$inst = $orginsts[$i]["institution"];
			if( isset($res[$id]) === false ){
				$res[$id] = array($rid);
			}else{
				$res[$id][] = $rid;
			}
		}
		return $res;
	}
	
	public static function initResearchersOrganizations($unrelateold = true){
		db()->beginTransaction();
		$res = true;
		try{
			$reltype = self::getPersonOrganizationRelationType();
			if( $reltype === null ){
				return "Could not retrieve relation type";
			}

			$orginsts = self::getInstitutionsToOrganizations();
			if( count($orginsts) === 0 ){
				return "No organization/institution pairing";
			}
			$orginsts = self::getUniquePairs($orginsts);
			if( is_string($orginsts) ){
				return $orginsts;
			}
			debug_log("UNIQUE PAIRS: " . COUNT($orginsts));
			$count = 0;
			foreach($orginsts as $k=>$v){
				$count += 1;
				debug_log("[".$count."] Relating " . $k . ":with record_id = " . implode(",", $v));
				self::pairResearcherOrganization($reltype, $k, $v, $unrelateold);
				print($k . ";" . implode(",", $v) . "\n");
				flush();
				ob_flush();
			}
			db()->commit();
		}catch(Exception $ex){
			db()->rollback();
			$res = $ex->getMessage();
			error_log("[Harvest::ERROR]: " . $res );
		}
		return $res;
	}
	
}
class HarvestFundings{
	
}

class HarvestContractTypes{
	
}
class HarvestOrganizations{
	
	private static function parseXML($xml = ""){
		if( trim($xml) === "" ){
			return "Cannot parse empty organization xml";
		}
		
		$res = array();
		$doc = new SimpleXMLElement($xml);
		$rel = $doc->rel[0];
		
		if( $rel->to !== null ){
			$res["to"] = array();
			if( $rel->to->class !== null ){
				$res["to"]["class"] = strval($rel->to->class);
			}
			
			if( $rel->to->type !== null ){
				$res["to"]["type"] = strval($rel->to->type);
			}
			
			$res["to"]["identifier"] = strval($rel->to);
		}
		
		if( $rel->participantnumber !== null ){
			$res["order"] = intval($rel->participantnumber);
		}
		if( $rel->legalname !== null ){
			$res["legalname"] = strval($rel->legalname);
		}
		$res["legalshortname"] = strval($rel->legalshortname);
		
		$res["websiteurl"] = strval($rel->websiteurl);
		
		if( $rel->country !== null ){
			$res["country"] = array();
			if( $rel->country->attributes()->classid !== null ){
				$clsid = strval($rel->country->attributes()->classid);
				if( trim(strtolower($clsid)) === 'uk'){
					$res["country"]["isocode"] = 'GB';
				}else if(trim(strtolower($clsid)) === 'el'){
					$res["country"]["isocode"] = 'GR';
				}else {
					$res["country"]["isocode"] = strval($clsid);
				}
				$res["country"]["id"] = self::getCountryIDByISO($res["country"]["isocode"]);
			}
			if( $rel->country->attributes()->classname !== null ){
				$res["country"]["name"] = strval($rel->country->attributes()->classid);
			}
		}
		
		return $res;
	}
	
	private static function isRecordImported($identifier){
		$orgs = new Default_Model_Organizations();
		$orgs->filter->identifier->like($identifier);
		if( count($orgs->items) > 0 ){
			return $orgs->items[0];
		}
		return null;
	}
	private static function getCountryIDByISO($iso){
		$countries = new Default_Model_Countries();
		$countries->filter->isocode->equals($iso);
		if( count($countries->items) > 0 ){
			$country = $countries->items[0];
			return $country->id;
		}
		return null;
	}
	
	private static function saveRecord($data){
		try{
			$org = new Default_Model_Organization();

			$org->identifier = $data["appdb_identifier"];
			$org->extIdentifier = $data["external_identifier"];
			$org->name = $data["legalname"];
			$org->shortname = $data["legalshortname"];
			$org->websiteurl = $data["websiteurl"];
			if( isset($data["country"]) && isset($data["country"]["id"]) &&  $data["country"]["id"] !== null ) {
				$org->countryid	= $data["country"]["id"];
			}
			if( isset($data["userid"]) ){
				$org->addedbyID = $data["userid"];
			}
			
			$org->sourceid = 2; //OpenAire source
			
			$org->save();
			
		}catch(Exception $ex){
			return $ex->getMessage();
		}
		return self::isRecordImported($org->identifier);
	}
	
	public static function getImportedOrganization($id){
		$orgs = new Default_Model_Organizations();
		if( is_numeric($id) ) {
			$orgs->filter->id->numequals($id);
		} else if(is_string($id) ) {
			$orgs->filter->guid->equals($id);
		}
		if( count($orgs->items) > 0 ){
			return $orgs->items[0];
		}
		return null;
	}
	
	public static function import($recordid, $userid = null){
		$record = null;
		if( is_numeric($recordid) ){
			$record = Harvest::getRecord($recordid);
		}else if( is_string($recordid) ){
			$record = Harvest::getRecordByIdentifier($recordid);
			if( $record === null ){
				$record = Harvest::getRecordByExtIdentifier($recordid);
			}
		}
		if( $record === null ){
			return "Could not find record " . $recordid;
		}
		
		$parsed = self::parseXML($record->contents);
		if( is_string($parsed) === true || $parsed === false ){
			return $parsed;
		}
		
		if( $userid !== null ){
			$parsed["userid"] = $userid;
		}
		
		$imported = self::isRecordImported($record->appdb_identifier);
		if( $imported !== null ){
			return $imported; //Organization is already imported
		}
		
		$parsed["appdb_identifier"] = $record->appdb_identifier;
		$parsed["external_identifier"] = $record->external_identifier;
		
		return self::saveRecord($parsed);
	}
	
	
}

class HarvestProjects{
	private static function getXmlSingleValue($x,$path){
		$v = $x->xpath($path);
		if( count($v) > 0 ){
			$v = $v[0];
		}else {
			$v = "";
		}
		return trim(strval($v));
	}
	
	private static function parseFundingXml($xml,$index = 0){
		$res = null;
		
		$f = $xml->xpath('//funding_level_' . $index);
		if( count($f) === 0 ){
			return null;
		}
		$f = $f[0];
		
		$res = array(
			"identifier" => self::getXmlSingleValue($f, './id'),
			"name" => self::getXmlSingleValue($f, './name'),
			"description" => self::getXmlSingleValue($f, './description')
		);
		
		return $res;
	}
	private static function parseFundingTreeXml($xml){
		$res = array();
		
		for($i=0; $i<3; $i+=1){
			$r = self::parseFundingXml($xml,$i);
			if( $r === null ){
				break;
			}
			$res[] = $r;
		}
		if( count($res) === 0 ){
			$res = null;
		}
		
		return $res;
	}
	
	public  static function parseXML($xml = ""){
		if( trim($xml) === "" ){
			return "Cannot parse empty project xml";
		}
		
		$res = array();
		$doc = new SimpleXMLElement($xml);
		$rel = $doc->xpath('//oaf:project');
		if( count($rel) > 0 ){
			$rel = $rel[0];
		}
		
		$res["code"] = self::getXmlSingleValue($rel, "./code");
		$res["acronym"] = self::getXmlSingleValue($rel, "./acronym");
		$res["title"] = self::getXmlSingleValue($rel,'./title');
		$res["startdate"] = self::getXmlSingleValue($rel,'./startdate');
		$res["enddate"] = self::getXmlSingleValue($rel,'./enddate');
		$res["callidentifier"] = self::getXmlSingleValue($rel,'./callidentifier');
		$res["websiteurl"] = self::getXmlSingleValue($rel,'./websiteurl');
		$res["keywords"] = self::getXmlSingleValue($rel,'./keywords');
		$res["duration"] = self::getXmlSingleValue($rel,'./duration');
		
		$res["organizations"] = array();
		$orgs = $rel->xpath("./rels/rel[to/@type='organization']");
		foreach($orgs as $org){
			$res["organizations"][] = array("type"=>self::getXmlSingleValue($org, './to/@class'), "identifier"=>self::getXmlSingleValue($org, './to'));
		}
		
		$res["contracttype"] = null;
		$contracttype = $rel->xpath('./contracttype');
		if( count($contracttype) > 0 ) {
			$contracttype = $contracttype[0];
		
			$res["contracttype"] = array(
				"name" => self::getXmlSingleValue($contracttype, './@classid'),
				"title" => self::getXmlSingleValue($contracttype, './@classname'),
				"groupname" => self::getXmlSingleValue($contracttype, './@schemeid')
			);
		}
		
		
		$res["funding"] = self::parseFundingTreeXml($rel);
		
		return $res;
	}
	
	private static function isFundingImported($extidentifier){
		$fs = new Default_Model_Fundings();
		$fs->filter->identifier->equals($extidentifier);
		if( count($fs->items) > 0 ){
			return $fs->items[0];
		}
		return null;
	}
	
	private static function importFunding($data, $parentid = null){
		$f = self::isFundingImported($data["identifier"]);
		if( $f !== null ){
			return $f;
		}
		
		$f = new Default_Model_Funding();
		$f->name = $data["name"];
		$f->description = $data["description"];
		$f->identifier = $data["identifier"];
		$f->parentid = $parentid;
		
		$f->save();
		
		return self::isFundingImported($data["identifier"]);
	}
	
	private static function isContractTypeImported($contractname){
		$cts = new Default_Model_ContractTypes();
		$cts->filter->name->equals($contractname);
		if( count($cts->items) > 0 ){
			return $cts->items[0];
		}
		return null;
	}
	
	private static function importContractType($data){
		$ct = self::isContractTypeImported($data["name"]);
		if( $ct !== null ){
			return $ct;
		}
		
		$ct = new Default_Model_ContractType();
		$ct->name = $data["name"];
		$ct->title = $data["title"];
		$ct->groupname = $data["groupname"];
		
		$ct->save();
		
		return self::isContractTypeImported($data["name"]);
	}
	
	private static function isOrganizationImported($extidentifier){
		$orgs = new Default_Model_Organizations();
		$orgs->filter->extIdentifier->like($extidentifier);
		if( count($orgs->items) > 0 ){
			return $orgs->items[0];
		}
		return null;
	}
	
	private static function importOrganization($data){
		$org = self::isOrganizationImported($data["identifier"]);
		if( $org !== null ){
			return $org;
		}
		
		return HarvestOrganizations::import($data["identifier"]);
	}
	
	private static function isRecordImported($identifier){
		$projs = new Default_Model_Projects();
		$projs->filter->identifier->like($identifier);
		if( count($projs->items) > 0 ){
			return $projs->items[0];
		}
		return null;
	}
	
	private static function saveRecord($data, $imports=array()){
		$userid = null;
		if( isset($data["userid"]) && is_numeric($data["userid"]) ){
			$userid = $data["userid"];
		}
		try{
			$proj = new Default_Model_Project();

			$proj->identifier = $data["appdb_identifier"];
			$proj->extIdentifier = $data["external_identifier"];
			$proj->code = $data["code"];
			$proj->acronym = $data["acronym"];
			$proj->title = $data["title"];
			$proj->startdate = $data["startdate"];
			$proj->enddate = $data["enddate"];
			$proj->callidentifier = $data["callidentifier"];
			$proj->keywords = $data["keywords"];
			$proj->duration = $data["duration"];
			$proj->websiteurl = $data["websiteurl"];
			$proj->addedbyID = $userid;
			
			$proj->sourceid = 2; //OpenAire source
			$proj->save();
		}catch(Exception $ex){
			return $ex->getMessage();
		}
		
		try{
			$proj = self::isRecordImported($proj->identifier);
			if( $proj === null ){
				return null;
			}
			if( $data["contracttype"] !== null ){
				$ct = self::importContractType($data["contracttype"]);
				$proj->contracttypeid = $ct->id;
				$proj->save();
			}

			if( $data["funding"] !== null && count($data["funding"]) > 0 ){
				$funds = $data["funding"];
				$pid = null;
				for($i=0; $i<count($funds); $i+=1){
					$f = self::importFunding($funds[$i], $pid);
					$pid = $f->id;
				}
				if( $pid !== null ){
					$proj->fundingid = $pid;
				}
			}

			if( in_array("organization", $imports) === true && $data["organizations"] !== null && count($data["organizations"]) > 0 ){
				$orgs = $data["organizations"];
				for($i=0; $i<count($orgs); $i+=1){
					$org = $orgs[$i];
					$reltype = EntityRelations::getRelationType("organization", $org["type"], "project");
					if( $reltype !== null ){
						$org["reltypeid"] = $reltype->id;
					}else{
						$org["reltypeid"] = null;
					}
					$orgs[$i] = $org;
				}
				for($i=0; $i<count($orgs); $i+=1){
					$org = $orgs[$i];
					if( isset($org["reltypeid"]) && $org["reltypeid"]!==null ){
						$o = self::importOrganization($org);
						if( $o !== null && is_string($o) === false ){
							EntityRelations::relate($org["reltypeid"], $o->guid, $proj->guid, $userid);
						}
					}
				}
			}
		}catch(Exception $ex){
			return $ex->getMessage();
		}
		return $proj;
	}
	/*
	 * Import project information form harvested data
	 * $recordid: recordid, external_identifier or appdb_identifier of harvested project
	 * $userid: Which user performs this action. Null if system.
	 * $imports: Array of project related entities (e.g organization) to be imported
	 *		NOTICE: Imports array will be used ONLY if project is not already imported
	 */
	public static function import($recordid, $userid = null, $imports = array()){
		$record = null;
		if( is_numeric($recordid) ){
			$record = Harvest::getRecord($recordid);
		}else if( is_string($recordid) ){
			$record = Harvest::getRecordByIdentifier($recordid);
			if( $record === null ){
				$record = Harvest::getRecordByExtIdentifier($recordid);
			}
		}
		if( $record === null ){
			return "Could not find record " . $recordid;
		}
		$xmlcontents = strval($record->contents);
		
		$parsed = self::parseXML($xmlcontents);
		if( is_string($parsed) === true || $parsed === false ){
			return $parsed;
		}
		
		if( $userid !== null ){
			$parsed["userid"] = $userid;
		}
		
		$imported = self::isRecordImported($record->appdb_identifier);
		if( $imported !== null ){
			return $imported; //Project is already imported
		}
		
		$parsed["appdb_identifier"] = $record->appdb_identifier;
		$parsed["external_identifier"] = $record->external_identifier;
		
		return self::saveRecord($parsed, $imports);
	}
	
	
}

class EntityRelations{
	public static function getRelationVerbByName($name){
		$verbs = new Default_Model_RelationVerbs();
		$verbs->filter->name->equals( trim($name) );
		if( count($verbs->items) > 0 ){
			return $verbs->items[0];
		}
		
		return null;
	}
	
	

	public static function getRelationByID($id){
		$rels = new Default_Model_Relations();
		$rels->filter->id->equals($id);
		if( count($rels->items) === 0 ){
			return null;
		}
		
		return $rels->items[0];
	}
	
	public static function getRelationType($subjecttype, $verb, $targettype){
		$reltypes = new Default_Model_RelationTypes();
		$f1 = new Default_Model_RelationTypesFilter();
		$f2 = new Default_Model_RelationTypesFilter();
		$f3 = new Default_Model_RelationTypesFilter();
		if( is_numeric($verb) ){
			$f1->verbid->equals($verb);
		}else if( is_string($verb) ){
			$verbobj = self::getRelationVerbByName($verb);
			if( !$verbobj ){
				return null;
			}
			$f1->verbid->equals($verbobj->id);
		}
		
		$f2->target_type->equals($targettype);
		$f3->subject_type->equals($subjecttype);
		$reltypes->filter->chain($f1->chain($f2->chain($f3, 'AND'), 'AND'), 'AND');
		if( count($reltypes->items) > 0 ){
			return $reltypes->items[0];
		}
		
		return null;
	}
	public static function getRelationTypePairs($subjecttype, $targettype){
		$reltypes = new Default_Model_RelationTypes();
		$f1 = new Default_Model_RelationTypesFilter();
		$f2 = new Default_Model_RelationTypesFilter();
		
		$f1->target_type->equals($targettype);
		$f2->subject_type->equals($subjecttype);
		$reltypes->filter->chain($f1->chain($f2, 'AND'), 'AND');
		if( count($reltypes->items) > 0 ){
			return $reltypes->items;
		}
		
		return null;
	}
	public static function getRelationTypePairIDs($subjecttype, $targettype){
		$rels = self::getRelationTypePairs($subjecttype, $targettype);
		$res = array();
		if( $rels !== null ){
			foreach($rels as $r){
				$res[] = $r->id;
			}
		}
		return $res;
	}
	public static function getRelationTypeByID($reltypeid){
		$reltypes = new Default_Model_RelationTypes();
		$reltypes->filter->id->equals($reltypeid);
		if( count($reltypes->items) === 0 ){
			return null;
		}
		return $reltypes->items[0];
	}
	
	public static function relationExists($reltypeid,$subjectguid,$targetguid){
		$rels = new Default_Model_Relations();
		$f1 = new Default_Model_RelationsFilter();
		$f2 = new Default_Model_RelationsFilter();
		$f3 = new Default_Model_RelationsFilter();
		$f1->reltypeid->numequals($reltypeid);
		$f2->target_guid->equals($targetguid);
		$f3->subject_guid->equals($subjectguid);
		$rels->filter->chain($f1->chain($f2->chain($f3, 'AND'), 'AND'));
		
		if( count($rels->items) > 0 ){
			return $rels->items[0];
		}
		return false;
	}
	
	public static function canRelateSubject($relationtype, $user, $subject){
		$uguid = $user;
		$sguid = $subject;
		$rid = $relationtype;
		
		if(is_object($user) ){
			$uguid = $user->guid;
		}
		if( is_object($subject) ){
			$sguid = $subject->guid;
		}
		if( is_object($relationtype) ){
			$rid = $relationtype->id;
			$relationtype = EntityRelations::getRelationTypeByID($rid);
			if( $relationtype === null ){
				return "No relation type found";
			}
		}
		$actionid = $relationtype->actionid;
		if( !$actionid ){
			//no privileged action for this relation type
			return true;
		}
		
		$fmode = db()->getFetchMode();
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$res = db()->query("SELECT EXISTS (SELECT * FROM permissions WHERE actor = '".$uguid . "' AND actionid = " .$actionid." AND (object = '".$sguid."' OR object IS NULL)) AS result;")->fetchAll();
		$row = $res[0];
		if ( $row['result'] == "1" || $row['result'] ===  true) {
			$res = true;
		} else {
			$res = false;
		}
		// if not permission set, refresh permissions and check again
		if (! $res) {
			db()->setFetchMode(Zend_Db::FETCH_BOTH);
			$res = db()->exec("REFRESH MATERIALIZED VIEW CONCURRENTLY permissions;");
			$res = db()->query("SELECT EXISTS (SELECT * FROM permissions WHERE actor = '".$uguid . "' AND actionid = " .$actionid." AND (object = '".$sguid."' OR object IS NULL)) AS result;")->fetchAll();
			$row = $res[0];
			if ( $row['result'] == "1" || $row['result'] ===  true) {
				$res = true;
			} else {
				$res = false;
			}
		}
		db()->setFetchMode($fmode);
		return $res;
	}
	public static function validateRelation($reltypeid, $subjectguid, $targetguid, $userid = null, $parentid = null, $reverse = false){
		if( $reverse === true ){
			$tmp = $subjectguid;
			$subjectguid = $targetguid;
			$targetguid = $tmp;
		}
		$relationtype = EntityRelations::getRelationTypeByID($reltypeid);
		if( $relationtype === null ){
			return "No relation type found";
		}
		
		$subtype = EntityTypes::getTypeByGuid($subjectguid);
		$tartype = EntityTypes::getTypeByGuid($targetguid);
		if( $subtype !== $relationtype->subjectType ||
			$tartype !== $relationtype->targetType ){
			return "Invalid relation identifiers";
		}
		
		$subobj = EntityTypes::getEntity($subtype, $subjectguid);
		if( $subobj === null ){
			return "Subject " . $subtype . " not found";
		}
		
		$tarobj = EntityTypes::getEntity($tartype, $targetguid);
		if( $tarobj === null ){
			return "Target " . $tartype . " not found";
		}
		
		$user = null;
		if( (is_numeric($userid) && $userid > 0) || 
			(!is_numeric($userid) && trim($userid) !== "" ) ){
			$user = EntityTypes::getPerson($userid);
			if( $user === null ){
				return "User not found";
			}
			$obj = $subobj;
			if( $reverse === true ){
				$obj = $tarobj;
			}
			$res = self::canRelateSubject($relationtype, $user, $obj);
			if( $res === false ){
				return "No user permissions to relate " . $relationtype->subjectType . " with " . $relationtype->targetType;
			}
		}
		
		if( $parentid !== null && EntityRelations::getRelationByID($parentid) === null ){
			return "Parent relation not found";
		}
		
		return true;
	}
	public static function getAllRelationsForSubject($subjectguid){
		$rels = new Default_Model_Relations();
		$rels->filter->subject_guid->equals($subjectguid);
		if( count($rels->items) > 0 ){
			return $rels->items;
		}
		return array();
	}
	public static function relate($reltypeid, $subjectguid, $targetguid, $userid = null, $parentid = null, $reverse = false){
		$isvalid = self::validateRelation($reltypeid, $subjectguid, $targetguid, $userid, $parentid, $reverse);
		if( $reverse === true ){
			$tmp = $subjectguid;
			$subjectguid = $targetguid;
			$targetguid = $tmp;
		}
		if( $isvalid !== true ){
			return $isvalid;
		}
		
		$relexists = self::relationExists($reltypeid, $subjectguid, $targetguid);
		if( $relexists !== false ){
			return $relexists;
		}
		
		try{
			$rel = new Default_Model_Relation();
			$rel->reltypeid = $reltypeid;
			$rel->subjectGUID = $subjectguid;
			$rel->targetGUID = $targetguid;
			$rel->addedbyid = $userid;
			$rel->parentid = $parentid;
			$rel->save();
		}catch(Exception $ex){
			return $ex->getMessage();
		}
		
		return $rel;
	}
	
	public static function unrelate($relationid){
		try{
			$rels = new Default_Model_Relations();
			$rels->filter->id->equals($relationid);
			if( count($rels->items) > 0 ){
				$rels->remove($rels->items[0]);
			}
		}catch(Exception $ex){
			return $ex->getMessage();
		}
		return true;
	}
	public static function unrelateAll($subjectguid, $excludeids=array(), $reverse = false, $reltypesids= array()){
		$rels = new Default_Model_Relations();
		$f1 = new Default_Model_RelationsFilter();
		if( $reverse ){
			$f1->target_guid->equals($subjectguid);
		}else{
			$f1->subject_guid->equals($subjectguid);
		}
		
		if( count($excludeids) > 0 ){
			$f2 = new Default_Model_RelationsFilter();
			$f2->id->notin($excludeids);
			$f1->chain($f2,"AND");
		}
		
		if( count($reltypesids) > 0 ){
			$f3 = new Default_Model_RelationsFilter();
			$f3->reltypeid->in($reltypesids);
			$f1->chain($f3,"AND");
		}
		
		$rels->filter->chain($f1,"AND");
		if( count($rels->items) === 0 ){
			return true;
		}
		
		try{
			foreach($rels->items as $item){
				$rels->remove($item);
			}
		}catch(Exception $ex){
			return $ex->getMessage();
		}
		
		return true;
	}
	public static function isImportableEntity($entityname){
		return !in_array($entityname, array("software","vappliance","vo","site"));
	}
	
	public static function syncRelations($entityguid, $userid, $relations = array(), $reverse = false, $unrelateold = true){
		$entityname = EntityTypes::getTypeByGuid($entityguid);
		if( $entityname === null ){
			return "Object entity type not found for relation";
		}
		
		$subject = EntityTypes::getEntity($entityname, $entityguid);
		$subjectguid = $subject->guid;
		
		$newrels = array();
		foreach($relations as $r){
			if( is_numeric($r["targetguid"]) ){
				$imp = Harvest::importRecord($r["targetguid"], $userid);
				if( is_string($imp) === true || $imp === false ){
					throw new Exception($imp);
				}
				$r["targetguid"] = $imp->guid;
			}
			
			$newrelation = EntityRelations::relate($r["id"], $entityguid, $r["targetguid"], $userid, $r["parentid"], $reverse);
			
			if( is_string($newrelation) ){
				throw new Exception($newrelation);
			}
			$newrels[] = $newrelation->id;
		}

		//remove old ones 
		if( $unrelateold === true ){
			$dels = EntityRelations::unrelateAll($subjectguid, $newrels, $reverse);
			if( $dels !== true ){
				throw new Exception($dels);
			}
		}
		
		return true;
	}
	
	public static function relationsToXml($entityguid){
		$rows = db()->query("SELECT subject_relations_to_xml(?)", array( $entityguid) )->fetchAll();
		$res = "";
		foreach($rows as $r){
			$res .= $r->subject_relations_to_xml;
		}
		return $res;
	}
	//Filter relations by direction
	//If $reverse=false then filter relations with targetguid else with subjectguid
	//If either subjectguid or targetguid is equal to entityguid ignore as relating
	//an entity to itself is not allowed
	public static function filterRelations($relations, $entityguid, $reverse = false){
		$res = array();
		foreach($relations as $rel){
			if( isset($rel["parentid"]) === false ){
				$rel["parentid"] = null;
			}
			if( $reverse === false && isset($rel["targetguid"]) && trim($rel["targetguid"]) !== "" && trim($rel["targetguid"]) !== trim($entityguid) ){
				if( is_numeric($rel["targetguid"]) ){
					$rel["entitytype"] = harvest::getRecordType($rel["targetguid"]);
				}else{
					$rel["entitytype"] = EntityTypes::getTypeByGuid($rel["targetguid"]);
				}
				$res[] = $rel;
			}else if ( $reverse === true && isset($rel["subjectguid"]) && trim($rel["subjectguid"]) !== "" && trim($rel["subjectguid"]) !== trim($entityguid) ){
				if( is_numeric($rel["subjectguid"]) ){
					$rel["entitytype"] = harvest::getRecordType($rel["subjectguid"]);
				}else{
					$rel["entitytype"] = EntityTypes::getTypeByGuid($rel["subjectguid"]);
				}
				$res[] = $rel;
			}
		}
		return $res;
	}
	
	public static function filterByEntityType($type, $relations){
		$res = array();
		foreach($relations as $rel){
			if( trim($type) === trim($rel["entitytype"]) ){
				$res[] = $rel;
			}
		}
		return $res;
	}
	
	public static function relateDirectEntity($entityguid, $entitytype, $userid, $relations){
		$rels = self::filterByEntityType($entitytype, self::filterRelations($relations, $entityguid, false) );
		$relatedids = array();
		foreach($rels as $r){
			if( is_numeric($r["targetguid"]) ){
				$imp = Harvest::importRecord($r["targetguid"], $userid);
				if( is_string($imp) === true || $imp === false ){
					throw new Exception($imp);
				}
				$r["targetguid"] = $imp->guid;
			}
			$r = EntityRelations::relate($r["id"], $entityguid, $r["targetguid"], $userid, $r["parentid"], false);
			if( is_string($r) || $r == false || $r == null ){
				throw new Exception($r);
			}
			$relatedids[] = $r->id;
		}
		return $relatedids;
	}
	public static function relateReverseEntity($entityguid, $entitytype, $userid, $relations){
		$rels = self::filterByEntityType($entitytype, self::filterRelations($relations, $entityguid, true) );
		$relatedids = array();
		foreach($rels as $r){
			if( is_numeric($r["subjectguid"]) ){
				$imp = Harvest::importRecord($r["subjectguid"], $userid);
				if( is_string($imp) === true || $imp === false ){
					throw new Exception($imp);
				}
				$r["subjectguid"] = $imp->guid;
			}
			$r = EntityRelations::relate($r["id"], $entityguid, $r["subjectguid"], $userid, $r["parentid"], true);
			if( is_string($r) || $r == false || $r == null ){
				throw new Exception($r);
			}
			$relatedids[] = $r->id;
		}
		return $relatedids;
	}
	
	public static function hideExternalRelations($entityguid, $entitytype, $userid, $relations=array()){
		foreach($relations as $relation){
			$rel = self::getRelationByID($relation["id"]);
			if( $rel === null ) return "Could not find given relation";
			if( trim($rel->targetguid) !== trim($entityguid) ) return "Not external relation given";
			$ishidden = (is_numeric($rel->hiddenbyid) && $rel->hiddenbyid > 0)?true:false;
			if( trim($relation["hidden"]) !== trim($ishidden) ){
				if(trim($relation["hidden"]) === "true" ){
					$rel->hiddenbyid = $userid;
					$rel->hiddenon = "NOW()";
				}else{
					$rel->hiddenbyid = 0;
					$rel->hiddenon = NULL;
				}
				$rel->save();
			}
		}
	}
}

class ApplicationRelations{
	
	
	public static function syncRelations($entityguid, $userid, $relations = array()){
		$entityname = EntityTypes::getTypeByGuid($entityguid);
		if( $entityname === null ){
			throw new Exception("Object entity type not found for relation");
		}
		$relids = EntityRelations::relateReverseEntity($entityguid, "organization", $userid, $relations);
		EntityRelations::unrelateAll($entityguid, $relids, true, EntityRelations::getRelationTypePairIDs("organization",$entityname));
		$relids = EntityRelations::relateReverseEntity($entityguid, "project", $userid, $relations);
		EntityRelations::unrelateAll($entityguid, $relids, true, EntityRelations::getRelationTypePairIDs("project", $entityname));
		$relids = EntityRelations::relateDirectEntity($entityguid, "software", $userid, $relations);
		EntityRelations::unrelateAll($entityguid, $relids, false, EntityRelations::getRelationTypePairIDs($entityname, "software"));
		if( $entityname !== 'swappliance'){
			//vappliance realtions to swappliance is done by the backend
			$relids = EntityRelations::relateDirectEntity($entityguid, "vappliance", $userid, $relations);
			EntityRelations::unrelateAll($entityguid, $relids, false, EntityRelations::getRelationTypePairIDs($entityname, "vappliance"));
		}
	}
	
	public static function hideExternalRelations($entityguid, $userid, $relations = array() ){
		$entityname = EntityTypes::getTypeByGuid($entityguid);
		$users = new Default_Model_Researchers();
		$users->filter->id->numequals($userid);
		if( count($users->items) === 0 ){
			return "Invalid user id given";
		}
		$user = $users->items[0];
		$privs = $user->getPrivs();
		if( $privs->canModifyApplicationDescription($entityguid) == false ){
			return "Cannot modify external relations. Permission denied.";
		}
		if( $entityname === null ){
			throw new Exception("Object entity type not found for relation");
		}
		return EntityRelations::hideExternalRelations($entityguid, $entityname, $userid, $relations);
	}
}

class PersonRelations{
	public static function syncRelations($entityguid, $userid, $relations = array()){
		$entityname = EntityTypes::getTypeByGuid($entityguid);
		if( $entityname === null ){
			throw new Exception("Object entity type not found for relation");
		}
		$relids = EntityRelations::relateDirectEntity($entityguid, "organization", $userid, $relations);
		EntityRelations::unrelateAll($entityguid, $relids, false, EntityRelations::getRelationTypePairIDs($entityname, "organization"));
		$relids = EntityRelations::relateDirectEntity($entityguid, "project", $userid, $relations);
		EntityRelations::unrelateAll($entityguid, $relids, false, EntityRelations::getRelationTypePairIDs($entityname, "project"));
	}
}

class EntityTypes {
	private static function getEntityByModel($model,$id){
		if( is_numeric($id) ){
			$model->filter->id->equals($id);
		}else if( is_string($id) ){
			$model->filter->guid->equals($id);
		}else{
			return null;
		}
		if( count($model->items) > 0 ){
			return $model->items[0];
		}
		return null;
	}
	public static function getPerson($id){
		$m = new Default_Model_Researchers();
		return self::getEntityByModel($m, $id);
	}
	
	public static function getOrganization($id){
		$m = new Default_Model_Organizations();
		return self::getEntityByModel($m, $id);
	}
	public static function getProject($id){
		$m = new Default_Model_Projects();
		return self::getEntityByModel($m, $id);
	}
	public static function getSoftware($id){
		$m = new Default_Model_Applications();
		$m->viewModerated = true;
		return self::getEntityByModel($m, $id);
	}
	public static function getVAppliance($id){
		$m = new Default_Model_Applications();
		$m->viewModerated = true;
		return self::getEntityByModel($m, $id);
	}
	public static function getSWAppliance($id){
		$m = new Default_Model_Applications();
		$m->viewModerated = true;
		return self::getEntityByModel($m, $id);
	}
	public static function getEntity($entityname, $id){
		switch(strtolower(trim($entityname))){
			case "person":
				return EntityTypes::getPerson($id);
			case "organization":
				return EntityTypes::getOrganization($id);
			case "project":
				return EntityTypes::getProject($id);
			case "software":
				return EntityTypes::getSoftware($id);
			case "vappliance":
				return EntityTypes::getVAppliance($id);
			case "swappliance":
				return EntityTypes::getSWAppliance($id);
			default:
				return null;
		}
		return null;
	}
	public static function getTypeByGuid($guid){
		$enttypes = new Default_Model_EntityGUIDs();
		$enttypes->filter->guid->equals($guid);
		if( count($enttypes->items) === 0 ){
			return null;
		}
		
		$enttype = $enttypes->items[0];
		
		return $enttype->entitytype;
	}
	
}
