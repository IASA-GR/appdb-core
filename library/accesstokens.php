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
class AccessTokens{
	/**
	 * Helper function to retrieve a user's profile.
	 * 
	 * @param Default_Model_Researcher|integer $user Either the user's profile object or the user's profile id.
	 * @return Default_Model_Researcher|null
	 */
	private static function getUser($user){
		if( $user === null ) {
			return null;
		} else if( is_numeric($user) ){
			$userid = intval($user);
			$users = new Default_Model_Researchers();
			$users->filter->id->equals($userid);
			if( count($users->items) === 0 ){
				return null;
			}
			$user = $users->items[0];
		}
		return $user;
	}
	/**
	 * Helper function to retrieve an AccessToken entry
	 * 
	 * @param integer|string|Default_Model_AccessToken $token Either accesstoken id, token uuid value or access token entry
	 * @return Default_Model_AccessToken|null
	 */
	private static function getAccessToken($token){
		if( $token === null ) {
			return null;
		} else if( is_numeric($token) ){
			$tokenid = intval($token);
			$acctokens = new Default_Model_AccessTokens();
			$acctokens->filter->id->numequals($tokenid);
			if( count($acctokens->items) === 0 ){
				return null;
			}
			$token = $acctokens->items[0];
		} else if( is_string($token) === true ){
			$tokenval = strval($token);
			$acctokens = new Default_Model_AccessTokens();
			$acctokens->filter->token->equals($tokenval);
			if( count($acctokens->items) === 0 ){
				return null;
			}
			$token = $acctokens->items[0];
		}
		return $token;
	}
	
	private static function getPersonalAccessTokens($user){
		$user = self::getUser($user);
		if( $user === null ) {
			return array();
		}
		
		$acctokens = new Default_Model_AccessTokens();
		$acctokens->filter->actor->equals($user->guid)->and($acctokens->filter->type->equals("personal"));
		if( count($acctokens->items) > 0 ){
			return $acctokens->items;
		}
		
		return array();
	}

	private static function getApplicationAccessTokens($actor){
		$acctokens = new Default_Model_AccessTokens();
		$acctokens->filter->actor->equals($actor)->and($acctokens->filter->type->equals("application"));
		if( count($acctokens->items) > 0 ){
			return $acctokens->items;
		}
		return array();
	}
	private static function getApplicationAccessTokensFromUser($user){
		$user = self::getUser($user);
		if( $user === null ) {
			return array();
		}
		$acctokens = new Default_Model_AccessTokens();
		$acctokens->filter->addedby->numequals($user->id)->and($acctokens->filter->type->equals("application"));
		if( count($acctokens->items) > 0 ){
			return $acctokens->items;
		}
		return array();
	}
	
	private static function getActor($actor){
		if( $actor === null ) {
			return null;
		} else if( is_string($actor) === true ){
			$actorval = strval($actor);
			//FIXME: should be Default_Model_Actors, with filter->actorid
			$actors = new Default_Model_Researchers();
			$actors->filter->guid->equals($actorval);
			if( count($actors->items) === 0 ){
				return null;
			}
			$actor = $actors->items[0];
		}
		return $actor;
	}
	public static function validNetFilters($nip){
		$ips = array();
		if( is_array($nip) === false ){
			if( trim($nip) === "" ){
				return "Empty netfilters are not allowed";
			}
			$ips = array($nip);
		}else{
			$ips = $nip;
		}
		
		foreach($ips as $ip){
			$res = (isIPv4($ip)>0 || isIPv6($ip)>0 || isCIDR($ip)>0 || isCIDR6($ip)>0 );
			if($res==false){
				$res = (preg_match('/^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$/',$ip)>0);
			}
			if( $res === false ){
				return "Invalid net filter: " . $ip;
			}
		}
		return $res;
	}
	private static function getMaximumAccessTokens(){
		$maxtokens = ApplicationConfiguration::api('maxkeys');
		if( is_numeric($maxtokens) && $maxtokens>0){
			return intval($maxtokens);
		}
		return null;
	}
	
	private static function createAccessToken($user, $actor, $type){
		$user = self::getUser($user);
		if( $user === null ){
			return "User not found";
		}
		
		$actor = self::getActor($actor);
		if( $actor === null ){
			return "Invalid target for access token creation";
		}
		
		try{
			$token = new Default_Model_AccessToken();
			$token->actorid = $actor->guid;
			$token->type = $type;
			$token->addedbyid = $user->id;

			$token->save();
		}catch(Exception $ex){
			return $ex->getMessage();
		}
		return true;
	}
	private static function canCreatePersonalAccessToken($user){
		$user = self::getUser($user);
		if( $user === null ){
			return "User not found";
		}
		$usertokens = self::getPersonalAccessTokens($user);
		
		//Check if user used all of the available access tokens
		$maxtokens = self::getMaximumAccessTokens();
		if( $maxtokens !== null && count($usertokens) >= $maxtokens ){
			if($maxtokens == 1){
				return 'A personal access token is already generated for the current user.';
			}else{
				return 'Generating more than '. $maxtokens .' personal access tokens per user is not allowed.';
			}
		}
		
		return true;
	}
	private static function canCreateApplicationAccessToken($user, $actor){
		$user = self::getUser($user);
		if( $user === null ){
			return "User not found";
		}
		$actor = self::getActor($actor);
		if( $actor === null ){
			return "Invalid target for access token creation";
		}
		$actortype = "entity";
		switch( $actor->type ){
			case "ppl":
				$actortype = "user";
				break;
			case "vap":
				$actortype = "virtual appliance";
				break;
			case "grp":
				$actortype = "access group";
				break;
			default:
				break;
		}
		$usertokens = self::getApplicationAccessTokens($actor->guid);
		//Check if user used all of the available access tokens
		$maxtokens = self::getMaximumAccessTokens();
		if( $maxtokens !== null && count($usertokens) >= $maxtokens ){
			if($maxtokens == 1){
				return 'An application access token is already generated for the current ' . $actortype . ' ' . $actor->name . '.';
			}else{
				return 'Generating more than '. $maxtokens .' application access tokens per ' . $actortype . ' is not allowed.';
			}
		}
		return true;
	}
	public static function createPersonalAccessToken($user){
		$user = self::getUser($user);
		$canCreate = self::canCreatePersonalAccessToken($user);
		if( $canCreate !== true ){
			return $canCreate;
		}
		return self::createAccessToken($user, $user->guid, "personal");
	}
	
	public static function createApplicationAccessToken($user, $actorguid){
		$canCreate = self::canCreateApplicationAccessToken($user, $actorguid);
		if( $canCreate !== true ){
			return $canCreate;
		}
		return self::createAccessToken($user, $actorguid, "application");
	}
	public static function removeAccessToken($user, $token){
		//Find current user
		$user = self::getUser($user);
		if( $user === null ){
			return "User not found";
		}
		
		//Find given token
		$token = self::getAccessToken($token);
		if( $token === null ){
			return "Invalid token given";
		}
		
		//Check if the access token is added by the current user
		if( $token->addedbyid !== $user->id ){
			return "Only user " . $user->firstname . " " . $user->lastname . " can remove this token";
		}
		
		self::removeAllNetfilters($user, $token);
		try {
			$tokens = new Default_Model_AccessTokens();
			$tokens->filter->id->equals($token->id);
			if( count($tokens->items) > 0 ){
				$tokens->remove($tokens->items[0]);
			}
		}catch(Exception $ex){
			return $ex->getMessage();
		}
		return true;
	}
	public static function addNetfilter($user, $token, $netfilter){
		//Find current user
		$user = self::getUser($user);
		if( $user === null ){
			return "User not found";
		}
		
		//Find given token
		$token = self::getAccessToken($token);
		if( $token === null ){
			return "Invalid token given";
		}
		
		//Check if the access token is added by the current user
		if( $token->addedbyid !== $user->id ){
			return "Only user " . $user->firstname . " " . $user->lastname . " can modify netfilters for this token";
		}
		
		//Check if maximum number of netfilters is reached
		$maxfilters = self::getMaximumAccessTokens();
		$currentfilters = $token->getNetfilters();
		if( count($currentfilters) >= $maxfilters ){
			if($maxfilters == 1){
				return "A netfilter already exists for the current access token";
			}else{
				return "Having more than ". $maxfilters . " netfilters per access token is not allowed.";
			}
		}
		
		//Check validity of netfilter
		$validfilters = self::validNetFilters(array($netfilter));
		if( $validfilters !== true ){
			return $validfilters;
		}
		
		//Save netfilters
		try{
			$nfilter = new Default_Model_AccessTokenNetfilter();
			$nfilter->tokenid = $token->id;
			$nfilter->netfilter = $netfilter;
			$nfilter->save();
		}catch(Exception $ex){
			return $ex->getMessage();
		}
		
		return true;
	}
	private static function removeAllNetfilters($user, $token){
		//Check current user
		$user = self::getUser($user);
		if( $user === null ){
			return "User not found";
		}
		
		//Find given token
		$token = self::getAccessToken($token);
		if( $token === null ){
			return "Invalid token given";
		}
		
		if( $token->addedbyid !== $user->id ){
			return "Only user " . $user->firstname . " " . $user->lastname . " can modify netfilters for this token";
		}
		
		$nflts = new Default_Model_AccessTokenNetfilters();
		$nflts->filter->tokenid->equals($token->id);
		$nfltsitems = $nflts->items;
		if( count($nfltsitems) > 0 ){
			for($i=count($nfltsitems)-1; $i>=0; $i--){
				$nflts->remove($nfltsitems[$i]);
			}
		}
		return true;
	}
	public static function removeNetfilter($user, $token, $netfilter){
		//Check current user
		$user = self::getUser($user);
		if( $user === null ){
			return "User not found";
		}
		
		//Find given token
		$token = self::getAccessToken($token);
		if( $token === null ){
			return "Invalid token given";
		}
		
		//Check if the access token is added by the current user
		if( $token->addedbyid !== $user->id ){
			return "Only user " . $user->firstname . " " . $user->lastname . " can modify netfilters for this token";
		}
		
		//get netfilters
		$nflts = new Default_Model_AccessTokenNetfilters();
		$nflts->filter->tokenid->equals($token->id);
		$nfltsitems = $nflts->items;
		foreach($nfltsitems as $nf){
			if( trim($nf->netfilter) === trim($netfilter) ){
				$nflts->remove($nf);
				break;
			}
		}
		return true;
	}
	
	public static function setNetfilters($user, $token, $netfilters=array()){
		//Check current user
		$user = self::getUser($user);
		if( $user === null ){
			return "User not found";
		}
		
		//Find given token
		$token = self::getAccessToken($token);
		if( $token === null ){
			return "Invalid token given";
		}
		
		//Normalize $netfilters parameter
		if( is_array($netfilters) === false ){
			if( trim($netfilters) === "" ){
				$netfilters = array(); 
			}else{
				$netfilters = array($netfilters); 
			}
		}
		
		if( count($netfilters) > 0 ){
			$allvalid = self::validNetFilters($netfilters);
			if( $allvalid !== true ){
				return $allvalid;
			}
		}
		
		//Remove all netfilters before setting new ones
		$removedAll = self::removeAllNetfilters($user, $token);
		if( $removedAll !== true ){
			return $removedAll;
		}
		
		if( count($netfilters) > 0 ){
			foreach($netfilters as $nf){
				self::addNetfilter($user, $token, $nf);
			}
		}
		return true;
	}
	public static function getActorByToken($token, $validate=false){
		if( trim($token) === "" ) {
			return null;
		}
		$tokens = new Default_Model_AccessTokens();
		$tokens->filter->token->equals($token);
		if( count($tokens->items) === 0 ){
			return null;
		}
		$tokenitem = $tokens->items[0];
		if( $validate === true ){
			$res = self::validateToken($tokenitem);
			if( $res === false ){
				return null;
			}
		}
		return $tokenitem->getActor();
	}

	public static function validateToken($token){
		if( is_string($token) ){
			if( trim($token) === "" ) {
				return null;
			}
			$tokens = new Default_Model_AccessTokens();
			$tokens->filter->token->equals($token);
			if( count($tokens->items) === 0 ){
				return false;
			}
			$token = $tokens->items[0];
		}else if($token instanceof Default_Model_AccessToken) {
			//nothing to do
		}else{
			return false;
		}
        $valid = false;
        $ip = $_SERVER['REMOTE_ADDR'];
		$netfilters = $token->getNetfilters();
        if ( count($netfilters) === 0 ) {
			return true;
		}
        foreach($netfilters as $netfilter) {
            if ( $netfilter == '' ) {
                // NULL netfilter
                $valid = true;
                break;
            } elseif ( isCIDR($netfilter) ) {
                if ( ipCIDRCheck($ip, $netfilter) ) {
                    $valid = true;
                    break;
                }
            } elseif ( isCIDR6($netfilter) ) {
                if ( ipCIDRCheck6($ip, $netfilter) ) {
                    $valid = true;
                    break;
                }
            } elseif ( isIPv4($netfilter) || isIPv6($netfilter) ) {
                if ( $ip == $netfilter ) {
                    $valid = true;
                    break;
                }
            } else {
                // domain name based netfilter
                $hostname = gethostbyaddr($ip);
                $netfilter = str_replace('\\', '', $netfilter);     // do not permit escaping
                if ( 
                    preg_match('/\.'.str_replace('.','\.',$netfilter).'$/', $hostname) ||   // domain name match
                    preg_match('/^'.str_replace('.','\.',$netfilter).'$/', $hostname)       // host name match
                ) {
                    $valid = true;
                    break;
                }
            }
        }
        if ( ! $valid ) debug_log('[AccessTokens::validateToken]: Invalid API key ' . $token->getToken());
        return $valid;
    
	}
}
?>
