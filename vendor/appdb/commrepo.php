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

class CommunityRepository{
	public static function syncSoftwareRelease($releasedata){
		//"insert","delete","update"
		$apprelease = new Default_Model_AppRelease();
		$apprelease->releaseid = $releasedata["releaseid"];
		$apprelease->appid = $releasedata["swid"];
		
		$appreleases = new Default_Model_AppReleases();
		$appreleases->filter->releaseid->equals( $releasedata["releaseid"] );
		if( $releasedata["action"] == "delete"){
			if( count($appreleases->items) > 0 ){
				$appreleases->remove( $appreleases->items[0] );
			}
			return true;
		}else if( $releasedata["action"] == "update" && count($appreleases->items) > 0){
			$apprelease = $appreleases->items[0];
		}	
		
		if( isset($releasedata["release"]) ){
			$apprelease->release = $releasedata["release"];
		}
		if( isset($releasedata["series"]) ){
			$apprelease->series = $releasedata["series"];
		}
		if( isset($releasedata["state"]) ){
			$apprelease->state = $releasedata["state"];
		}
		if( isset($releasedata["addedon"]) && trim($releasedata["addedon"]) != "" ){
			$apprelease->addedon = $releasedata["addedon"];	
		}
		if( isset($releasedata["publishedon"]) && trim($releasedata["publishedon"]) != "" ){
			$apprelease->publishedon = $releasedata["publishedon"];	
		}
		if( isset($releasedata["lastupdated"]) && trim($releasedata["lastupdated"])!="" ){
			$apprelease->lastupdated = $releasedata["lastupdated"];	
		}
		if( isset($releasedata["manager"]) && $releasedata["manager"] != "0" ){
			$apprelease->managerid = $releasedata["manager"];	
		}
		
		$apprelease->save();
		
		return true;
	}
}
?>
