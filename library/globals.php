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

require_once('appdb_configuration.php');
require_once('support.php');
require_once('restapi.php');

$api = $this->getOption("api");
Zend_Registry::set('api',$api);

$app = $this->getOption("app");
Zend_Registry::set('app',$app);

if (isset($app["timezone"])) {
	date_default_timezone_set($app["timezone"]);
} else {
	date_default_timezone_set('UTC');
}

$vouser_sync = $this->getOption("vouser_sync");
Zend_Registry::set('vouser_sync',$vouser_sync);

$_SERVER["validation_period"] = $app["invalid"];
$_SERVER['APILatestVersion'] = $api["latestVersion"];
$_SERVER['Repository_Enabled'] = ( ( isset($app["enableRepository"]) && $app["enableRepository"] == 'true' )?"true":"false");
$_SERVER['Repository_Api'] = ( ( isset($app["repositoryApi"]) )?$app["repositoryApi"]:"" );

list($php_major, $php_minor, $php_bug) = explode(".", phpversion(), 3);

if ($php_major <= 5) {
	if (function_exists('override_function')) {
		override_function('mysql_real_escape_string' ,'$s,$r = null', 'if (is_null($r)) { $r = Zend_Registry::get("repository")->getConnection(); } $s = preg_replace("/^\'/", "", preg_replace("/\'$/", "", $r->quote($s))); return $s;');
	} else {
		error_log("Warning: function 'override_function' does not exist. Has the 'php-pecl-apd' package been properly installed?");
	}
} else {
	if (function_exists('mysql_real_escape_string')) {
		error_log("Warning: ext/mysql has been officially removed in PHP 7.0; please remove obsolete extension");
	} else {
		function mysql_real_escape_string($s, $r = null) {
			if (is_null($r)) {
				$r = Zend_Registry::get("repository")->getConnection(); 
			} 
			$s = preg_replace("/^\'/", "", preg_replace("/\'$/", "", $r->quote($s)));
			 return $s;
		}
	}
}

function web_get_contents($url) {
	$arrContextOptions=array(
		"ssl"=>array(
			"verify_peer"=>false,
			"verify_peer_name"=>false,
		),
	);   
	return file_get_contents($url, false, stream_context_create($arrContextOptions));
}

function userIsAdmin($id) {
	if (is_null($id)) return false;
	$admins = new Default_Model_Researchers();
	$admins->filter->id->numequals($id);
	$agmf = new Default_Model_ActorGroupMembersFilter();
	$agmf->groupid->numequals(-1);
	$admins->filter->chain($agmf, "AND");
	return (count($admins->items) > 0);
}

function userIsManager($id) {
	if (is_null($id)) return false;
	$admins = new Default_Model_Researchers();
	$admins->filter->id->numequals($id);
	$agmf = new Default_Model_ActorGroupMembersFilter();
	$agmf->groupid->numequals(-2);
	$admins->filter->chain($agmf, "AND");
	return (count($admins->items) > 0);
}

function userIsNIL($id, $cid = null) {
	if (is_null($id)) return false;
	$admins = new Default_Model_Researchers();
	$admins->filter->id->numequals($id);
	$agmf = new Default_Model_ActorGroupMembersFilter();
	$agmf->groupid->numequals(-3);
	if (is_null($cid)) {
		$agmf->groupid->numequals(-3);
	} else {
		$agmf->groupid->numequals(-3)->and($agmf->payload->equals($cid));
	}
	$admins->filter->chain($agmf, "AND");
	return (count($admins->items) > 0);
}

function userIsAdminOrManager($id) {
	if (is_null($id)) return false;
	$admins = new Default_Model_Researchers();
	$admins->filter->id->numequals($id);
	$agmf = new Default_Model_ActorGroupMembersFilter();
	$agmf->groupid->in(array(-1, -2));
	$admins->filter->chain($agmf, "AND");
	return (count($admins->items) > 0);
}

function getAdminsAndManagers() {
	$admins = new Default_Model_Researchers();
	$agmf = new Default_Model_ActorGroupMembersFilter();
	$agmf->groupid->in(array(-1, -2));
	$admins->filter->chain($agmf, "AND");
	return $admins->items;
}

/**
 * establish an LDAP connection (bind)
 *
 * @secure bool use TLS if true
 * @rdn string RDN to use when binding
 * @pwd string Password to use when binding
 * @ldapError mixed Callback function or object method to set error information
 *
 * @return mixed If @rdn and @pwd are both unset, tries to connect using the root credentials, 
 * or else tries to connect using the provided credentials. Returns a PHP resource upon success, 
 * and the caller is responsible for closing the connection. If the credentials are invalid, it returns
 * false, and if an error occurs, it returns null. If the callback parameter @ldapError is not null, then
 * it is called to set error state upon error. This parameter must eiter be a string, if the callback
 * is a function, or an array with an object reference at index 0 and a string with the method at index
 * 1 if the callback is an object method. Note that the callback must accept 2 arguments: the 1st is a
 * reference to the LDAP resource and the second is a string which may help describe the error
 */
function initLDAP($secure = true, $rdn = null, $pwd = null, $ldapError = null) {
	if ($secure) {
		$ldapCount = 0;
		while($ldapCount < 10) { // try, try, try again
			if ($ldapCount >= 0) {
				error_log('Trying to set-up TLS ldap connection: attempt #' . $ldapCount);
			}
			$ds = _initLDAP(true, $rdn, $pwd, $ldapError);
			if (!is_null($ds)) { // non-null: no error, break
				break;
			} else { // null: an error has occured, try again
				$ldapCount += 1;
				usleep(100000);
			}
		}
	} else {
		$ds = _initLDAP(false, $rdn, $pwd, $ldapError);
	}
	return $ds;
}


function _initLDAP($secure = true, $rdn = null, $pwd = null, $ldapError = null) {
	if (! is_null($ldapError)) {
		call_user_func_array($ldapError, array(null, null)); // clear ldap error state
	}
	$ldap = ApplicationConfiguration::service('egi.ldap.host');
	$ds = ldap_connect($ldap);
	if ($ds === false) {
		if (! is_null($ldapError)) {
			call_user_func_array($ldapError, array(null, "Could not initialize connection to the EGI SSO server"));
		}
		return null;
	}
	if (! @ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3)) {
		if (! is_null($ldapError)) {
			call_user_func_array($ldapError, array($ds, "Could not set EGI SSO server connection options"));
		}
		return null;
	}
	if (! @ldap_set_option($ds, LDAP_OPT_REFERRALS, 0)) {
		if (! is_null($ldapError)) {
			call_user_func_array($ldapError, array($ds, "Could not set EGI SSO server connection options"));
		}
		return null;
	}
	if ($secure) {
		if (! @ldap_start_tls($ds)) {
			if (! is_null($ldapError)) {
				call_user_func_array($ldapError, array($ds, "Could not establish a secure connection to the EGI SSO server"));
			}
			return null;
		}
	}
	if ((! isset($rdn)) && (! isset($pwd))) {
		$ok = @ldap_bind($ds, ApplicationConfiguration::service('egi.ldap.username'), ApplicationConfiguration::service('egi.ldap.password'));
	} else {
		$ok = @ldap_bind($ds, $rdn, $pwd);
	}
	if (ldap_errno($ds) !== 0) {
		if (! is_null($ldapError)) {
			call_user_func_array($ldapError, array($ds, "Could not bind to the EGI SSO server"));
		}
		@ldap_close($ds);
		return null;
	} else {			
		if ($ok === false) {
			@ldap_close($ds);
			return false;
		}
	}
	return $ds;
}

function setAuthCookies($username, $password) {
    if ( APPLICATION_ENV != "production" ) {
		$domain = null; 
	} else {
		$domain = "." . $_SERVER['HTTP_HOST'];
	}
	setcookie("scookname", $username, 0, "/", $domain, true, true);
    setcookie("scookpass", $password, 0, "/", $domain, true, true);
}

function clearAuthCookies() {
    if ( APPLICATION_ENV != "production" ) {
		$domain = null; 
	} else {
		$domain = "." . $_SERVER['HTTP_HOST'];
	}
    setcookie("cookname", "", time() - 3600, "/");
    setcookie("cookpass", "", time() - 3600, "/");
    setcookie("scookname", "", time() - 3600, "/", $domain, true, true);
    setcookie("scookpass", "", time() - 3600, "/", $domain, true, true);
}

function getZendSelectParts($select, &$from, &$where, &$orderby, &$limit) {
		$from = '';
		try {
			$_from = $select->getPart('FROM'); 
			foreach ($_from as $f) {
				$from = $from . ' ' . strtoupper($f['joinType']) . ' ' .($f['schema']===null?'':$f['schema'].'.') . $f['tableName'] . ($f['joinCondition']===null?'':' ON '.$f['joinCondition']);
			}
		} catch (Exception $e) {
		}

		$where = '';
		try {
			$where = $select->getPart('WHERE');
			if ( count($where) > 0 ) {
				if ( is_array($where[0]) ) {
					foreach ($where[0] as $w) {
						debug_log($w);
					}
				} else {
					$where = "WHERE " . $where[0];
				};
			} else $where = '';
		} catch (Exception $e) {
		}

		$orderby = '';
		try {
			$_orderby = $select->getPart('ORDER'); 
			foreach ($_orderby as $f) {
				$orderby = $orderby . ', ' . $f[0] . (count($f)>1?' ' . $f[1]:'');
			}
			if ( $orderby != '' ) $orderby = str_replace('ORDER BY ,','ORDER BY','ORDER BY ' . $orderby);
		} catch (Exception $e) {
		}

		$limit = '';
		try {
			if ($select->getPart('LIMITCOUNT') != 0) {
				$limit = 'LIMIT ' . $select->getPart('LIMITCOUNT');
				$limit = $limit . ' OFFSET ' . $select->getPart('LIMITOFFSET');
			}
		} catch (Exception $e) {
		}
}

function debug_log($s) {
    if ( APPLICATION_ENV != "production" ) error_log($s);
}

/*
class myTimer {
	private $timers = array();

    public function mark($v) {
        $this->timers[] = array($v, microtime(true));
    }    

	public function dump($all = false) {
		debug_log("##############################");
		if ($all) {
			for ($i = 0; $i < count($this->timers) - 1; $i++) {
				debug_log($this->timers[$i][0] . ":" . $this->timers[$i + 1][0] . "=" . ($this->timers[$i + 1][1] - $this->timers[$i][1]));
			}
		} else {
	        debug_log($this->timers[count($this->timers) - 2][0] . ":" . $this->timers[count($this->timers) - 1][0] . "=" . ($this->timers[count($this->timers) - 1][1] - $this->timers[count($this->timers) - 2][1]));
		}
		debug_log("##############################");
    }   

	public function clear() {
		$this->timers = array();
	} 
}
*/

function generate_uuid_v4() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

function pg_now() {
	global $application;
	$db = $application->getBootstrap()->getResource('db');
	$db->setFetchMode(Zend_Db::FETCH_BOTH);
	$res = $db->query("SELECT TIMESTAMP 'epoch' + ".time()." * INTERVAL '1 second'")->fetchAll();
	if ( count($res) > 0 ) {
		return $res[0];
	} else {
		return time();
	}
}

function now() {
	return pg_now();
}

function appdbVerInfo() {
	$v=exec("cat ".APPLICATION_PATH."/../VERSION"); 
	if ( APPLICATION_ENV != "production" ) {
		$rev=@exec("svn info 2>&1 | grep Revision | awk '{print $2}'");
		if ( $rev != '' ) $v="$v-r$rev";
	}
	return $v;
}

// NOTE: In PHP 5.6 mcryopt_* behavior has changed:
// will no longer accept keys or IVs with incorrect sizes, and block cipher modes that require IVs will now fail if an IV isn't provided
// make sure these functions still work
function encrypt($str, $key)
{
    $block = mcrypt_get_block_size('des', 'ecb');
    $pad = $block - (strlen($str) % $block);
    $str .= str_repeat(chr($pad), $pad);

    return mcrypt_encrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
}

function decrypt($str, $key)
{  
    $str = mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);

    $block = mcrypt_get_block_size('des', 'ecb');
    $pad = ord($str[($len = strlen($str)) - 1]);
    return substr($str, 0, strlen($str) - $pad);
}

function noDBSeqScan($db) {
	return true;
//	$db->exec("SET enable_seqscan = FALSE");
}

function db() {
	global $application;
	return $application->getBootstrap()->getResource('db');
}

function localRequest() {
	$local = false;
	if ( array_key_exists('REMOTE_ADDR',$_SERVER) ) {
		$addr = $_SERVER['REMOTE_ADDR'];
		if ( ($addr==="::1") || ($addr === "127.0.0.1") || (substr($addr,0,3) === "10.") || (substr($addr,0,7) === "172.16.") || (substr($addr,0,8) === "192.168.") || ($addr === "195.251.54.91") || ($addr === "195.251.54.93") ) {
			$local = true;
		}
		return $local;
	}
}

function browserName() {
	if ( (! isset($_SERVER['HTTP_USER_AGENT'])) || ($_SERVER['HTTP_USER_AGENT']) == '' ) {
		return 'IE';
	}
    $browser = get_browser(null, true);
    return $browser['browser'];
}

function browserPlatform() {
    $browser = get_browser(null, true);
    return $browser['platform'];
}

function browserVersion() {
    $browser = get_browser(null, true);
    return $browser['version'];
}

function browser($agent=null) {
 // Declare known browsers to look for
 $known = array('msie', 'firefox', 'chrome', 'safari', 'webkit', 'opera', 'netscape',
   'konqueror', 'gecko', 'googlebot', 'msnbot');

 // Clean up agent and build regex that matches phrases for known browsers
 // (e.g. "Firefox/2.0" or "MSIE 6.0" (This only matches the major and minor
 // version numbers.  E.g. "2.0.0.6" is parsed as simply "2.0"
 if ( array_key_exists('HTTP_USER_AGENT', $_SERVER) ) {
	$agent = strtolower($agent ? $agent : $_SERVER['HTTP_USER_AGENT']);
 } else {
	$agent='';
 }
// named matches only work with PCRE >= 7.0
   $pattern = '#(' . join('|', $known) . ')[/ ]+([0-9]+(?:\.[0-9]+)?)#';
   
 // Find all phrases (or return empty array if none found)
 if (!preg_match_all($pattern, $agent, $matches)) return '';

 // Since some UAs have more than one phrase (e.g Firefox has a Gecko phrase,
 // Opera 7,8 have a MSIE phrase), use the last one found (the right-most one
 // in the UA).  That's usually the most correct.
 //$i = count($matches['browser'])-1;
 //return $matches['browser'][$i];
   $i = count($matches[1])-1;
   return $matches[1][$i];
}

function isMSIE8() {
    if (strpos($_SERVER['HTTP_USER_AGENT'],"MSIE 8")) return true; else return false;
}
function isMSIE10() {
    if (strpos($_SERVER['HTTP_USER_AGENT'],"MSIE 10")) return true; else return false;
}
function isMSIE9(){
	 if (strpos($_SERVER['HTTP_USER_AGENT'],"MSIE 9") || strpos($_SERVER['HTTP_USER_AGENT'],"MSIE 10")) return true; else return false;
}
function getMSIEVersion(){
	if( !isset($_SERVER['HTTP_USER_AGENT']) ) return -1;
	if( isMSIE8() ) return 8;
	if( isMSIE9() ) return 9;
	if( isMSIE10() ) return 10;
	if( stripos($_SERVER['HTTP_USER_AGENT'],"trident") !== false ){
		$result = explode("rv:", strtolower(trim($_SERVER['HTTP_USER_AGENT'])) );
		if( count($result) > 0 ){
			return intval(preg_replace("/[^0-9.]+/", "", $result[1]));
		}
	}
	return -1;
}
function isMSIE(){ 
	 if (browserName() == 'IE') return true; else return false;
}
function isnull($val) {
	if ( ($val === null) || ($val === 'NULL') ) {
       	return true;
	} else return false;
}

/*
function is_url2($url){
    $url = substr($url,-1) == "/" ? substr($url,0,-1) : $url;
    if ( !$url || $url=="" ) return false;
    if ( !( $parts = @parse_url( $url ) ) ) return false;
    else {
        if ( $parts['scheme'] != "http" && $parts['scheme'] != "https" && $parts['scheme'] != "ftp" && $parts['scheme'] != "gopher" ) return false;
        else if ( ! preg_match( "/^[0-9a-z]([-.]?[0-9a-z])*.[a-z]{2,4}$/i", $parts['host'], $regs ) ) return false;
        else if ( ! preg_match( "/^([0-9a-z-]|[_])*$/i", $parts['user'], $regs ) ) return false;
        else if ( ! preg_match( "/^([0-9a-z-]|[_])*$/i", $parts['pass'], $regs ) ) return false;
        else if ( ! preg_match( "/^[0-9a-z/_.@~-]*$/i", $parts['path'], $regs ) ) return false;
        else if ( ! preg_match( "/^[0-9a-z?&=#,]*$/i", $parts['query'], $regs ) ) return false;
    }
    return true;
}
*/

function trackPage($url,$format = null) {
    return false;
}

/*
function cloneXMLNode($node,$doc){
    $nd=$doc->createElement($node->nodeName);
           
    foreach($node->attributes as $value) {
		$nd->setAttribute($value->nodeName,$value->value);
	}
           
    if(!$node->childNodes)
        return $nd;
               
    foreach($node->childNodes as $child) {
        if($child->nodeName=="#text")
            $nd->appendChild($doc->createTextNode($child->nodeValue));
        else
            $nd->appendChild(cloneXMLNode($child,$doc));
    }
           
    return $nd;
}
*/

/*
function strip_javascript($filter){
  
    // realign javascript href to onclick
    $filter = preg_replace("/href=(['\"]).*?javascript:(.*)?\\1/i", "onclick=' $2 '", $filter);

    //remove javascript from tags
    while( preg_match("/<(.*)?javascript.*?\(.*?((?".">[^()]+)|(?R)).*?\)?\)(.*)?".">/i", $filter))
        $filter = preg_replace("/<(.*)?javascript.*?\(.*?((?".">[^()]+)|(?R)).*?\)?\)(.*)?".">/i", "<$1$3$4$5>", $filter);
            
    // dump expressions from contibuted content
    if(0) $filter = preg_replace("/:expression\(.*?((?".">[^(.*?)]+)|(?R)).*?\)\)/i", "", $filter);

    while( preg_match("/<(.*)?:expr.*?\(.*?((?".">[^()]+)|(?R)).*?\)?\)(.*)?".">/i", $filter))
        $filter = preg_replace("/<(.*)?:expr.*?\(.*?((?".">[^()]+)|(?R)).*?\)?\)(.*)?".">/i", "<$1$3$4$5>", $filter);
       
    // remove all on* events   
    while( preg_match("/<(.*)?\s?on.+?=?\s?.+?(['\"]).*?\\2\s?(.*)?".">/i", $filter) )
       $filter = preg_replace("/<(.*)?\s?on.+?=?\s?.+?(['\"]).*?\\2\s?(.*)?".">/i", "<$1$3>", $filter);

    return $filter;
}
*/

/**
 * Change a PHP array into a db array
 * @param $arr the PHP array
 * @return the string representation of the db array
 */
function php_to_pg_array($arr, $numeric = false) {
	if ( ! $numeric === true ) $numeric = false;
	$s = "{";
	for ($i = 0; $i < count($arr); $i++) {
		if ( $numeric ) {
			$s .= $arr[$i];
		} else {
			$s .= '"' . str_replace('"', '\"', pg_escape_string($arr[$i])) . '"';
		}
		if ( $i < count($arr) - 1 ) $s .= ",";
	}
	$s .= "}";
	return $s;
}

/**
 * Change a db array into a PHP array
 * @param $arr String representing the DB array
 * @return A PHP array
 */
function pg_to_php_array($dbarr) {
	if ( $dbarr  === null ) return array();
	// Take off the first and last characters (the braces)
	$arr = substr($dbarr, 1, strlen($dbarr) - 2);

	// Pick out array entries by carefully parsing.  This is necessary in order
	// to cope with double quotes and commas, etc.
	$elements = array();
	$i = $j = 0;       
	$in_quotes = false;
	while ($i < strlen($arr)) {
		// If current char is a double quote and it's not escaped, then
		// enter quoted bit
		$char = substr($arr, $i, 1);
		if ($char == '"' && ($i == 0 || substr($arr, $i - 1, 1) != '\\'))
			$in_quotes = !$in_quotes;
		elseif ($char == ',' && !$in_quotes) {
			// Add text so far to the array
			$elements[] = substr($arr, $j, $i - $j);
			$j = $i + 1;
		}
		$i++;
	}
	// Add final text to the array
	$elements[] = substr($arr, $j);

	// Do one further loop over the elements array to remote double quoting
	// and escaping of double quotes and backslashes
	for ($i = 0; $i < sizeof($elements); $i++) {
		$v = $elements[$i];
		if (strpos($v, '"') === 0) {
			$v = substr($v, 1, strlen($v) - 2);
			$v = str_replace('\\"', '"', $v);
			$v = str_replace('\\\\', '\\', $v);
			$elements[$i] = $v;
		}
	}

	return $elements;
}


/*
 * Converts simple HTML to text, useful for converting Zend error messages
 */
function simpleHTML2Text($html)
{
	$tags = array (
	0 => '~<h[123][^>]+>~si',
	1 => '~<h[456][^>]+>~si',
	2 => '~<table[^>]+>~si',
	3 => '~<tr[^>]+>~si',
	4 => '~<li[^>]+>~si',
	5 => '~<br[^>]+>~si',
	6 => '~<p[^>]+>~si',
	7 => '~<div[^>]+>~si',
	);
	$html = preg_replace($tags,"\n",$html);
	$html = preg_replace('~</t(d|h)>\s*<t(d|h)[^>]+>~si',' - ',$html);
	$html = preg_replace('~<[^>]+>~s','',$html);
	// reducing spaces
	$html = preg_replace('~ +~s',' ',$html);
	$html = preg_replace('~^\s+~m','',$html);
	$html = preg_replace('~\s+$~m','',$html);
	// reducing newlines
	$html = preg_replace('~\n+~s',"\n",$html);
	return $html;
}

function ISOCodeToFlag($isocode) {
	$isocode = explode("/",strtolower(trim($isocode)));
	$flags = array();
	foreach($isocode as $i) {
		$flags[] = "/images/flags/$i.png";
	}
	return $flags;
}

function isJSON($v) {
	try {
		if (json_decode(str_replace("'",'"',$v)) === null) return false;
	} catch (Exception $e) {
		return false;
	}
	return true;
}

/*
 * Creates a key/value array with the query parameters from the given http query string.
 */
function parseHTTPQuery($q){
    $list = explode('&',$q);
    $params = array();
    foreach($list as $i){
        $item = explode('=',$i);
        $params[$item[0]] = $item[1];
    }
    return $params;
}

/*
 * Checks if the given permalink is a JSON object and builds the appropriate javascript call.
 * If it is not a JSON object it returns null. This function is intended to be used from the index/index.phtml
 * during the loading of the page. The json type permalink is produced and used by the components of the new framework.
 */
function JSONPermalink($v){
    $req = json_decode($v);
    if($req===NULL){
        return null;
    }
    $j = "";
    $u = $req->url;
    $query = $req->query; //get the query object
    $query = json_encode($query);
    $ext = $req->ext; //the extended properties of the component to be called
    $ext = json_encode($ext);
    switch($u){
        case "/apps":
            $j = "appdb.views.Main.showApplications";
            break;
        case "/people":
            $j = "appdb.views.Main.showPeople";
            break;
		case "/vos":
			$j = "appdb.views.Main.showVOs";
			break;
		case "/person":
			$j = "appdb.views.Main.showPerson";
			break;
		case "/vo":
			$j = "appdb.views.Main.showVO";
			break;
        default:
            return null;
            break;
    }
    $j .= "(" . $query . " , " . $ext . ");";
    return $j;
}

function getCanonicalName($name)
{
	$rs = db()->query("select normalize_cname(?) as cname", ARRAY($name))->fetchAll();
	
	if( count($rs) > 0 )
	{		
		return $rs[0]['cname'];
	}
	
	return null;
}
function validatePplCName($cname, $id = null) {
	$cname = str_replace("'", "\\'", $cname);
	$valid = true;
	global $application;
	$db = $application->getBootstrap()->getResource('db');
	$db->setFetchMode(Zend_Db::FETCH_BOTH);
	$rs = db()->query("SELECT value FROM researcher_cnames WHERE value = normalize_cname(E'$cname')" . (isset($id) ? " AND researcherid <> $id" : ""))->fetchAll();
	if (count($rs) > 0) $valid = $rs[0]["value"];
	return $valid;
}
function validateAppCName($cname, $id=null){
	$cname = str_replace("'", "\\'", $cname);
	$valid = true;
	global $application;
	$db = $application->getBootstrap()->getResource('db');
	$db->setFetchMode(Zend_Db::FETCH_BOTH);
	$rs = db()->query("SELECT value FROM app_cnames WHERE value = normalize_cname(E'$cname')" . (isset($id) ? " AND appid <> $id" : ""))->fetchAll();
	if (count($rs) > 0) $valid = $rs[0]["value"];
	return $valid;
}
function validateAppName($name, &$error, &$reason, $id=null) {
	$valid = false;
	$name = trim($name);
    //check min length
    if(strlen($name)<3 || strlen($name)>50){
        $error = "Invalid length";
        $reason = "The length of the name must be from 3 to 50 characters long.The current length is <b>" . strlen($name). "</b>." ;
        return false;
    }
    //check validity
	if (!preg_match('/^[A-Za-z0-9 *.+,&!#@=_^(){}\[\]-]+$/',$name)) {
		$error = 'Error : Invalid character.';
        $reason = 'The name contains invalid characters. Valid characters are alphanumeric characters, spaces, and the following sumbols: +(){}[],*&amp;!#@=^._-';
		return false;
	}
    //Check similarity
    $res = Default_Model_Applications::nameAvailable($name, $id);
    if($res !== true){
        $error = "Error : Invalid name";
        $reason = 'Name already taken by <a href="http://'.$_SERVER['HTTP_HOST'].'/?p='.base64_encode('/apps/details?id='.$res->id).'" target="_blank">'.$res->name.'</a>.<p></p>';
		$reason .= "<div>Please have a look at the ".$res->name." software to understand if it is different from the one you want to register.<br/>If it is <b>not</b> different, please join ".$res->name." as a scientific contact (for more information visit the ";
		$reason .= '<a href="#" onclick="appdb.utils.ToggleFaq(12);" >FAQ</a>).<p></p>If it is different, please modify your applcation name. In order to avoid confusion from similarly named software, you should use a modifier in you software name in order to differentiate it from other related entries. Good examples would be :</div>';
		$reason .= "<div><span>  </span>".$name. "-&lt;Country&gt;</div>";
		$reason .= "<div><span>  </span>".$name. "-&lt;Project&gt;</div>";
		$reason .= "<div><span>  </span>".$name. "-&lt;Virtual Organization&gt;</div>";
		$reason .= "<div><span>  </span>".$name. "-&lt;Consortium&gt;</div>";
		$reason .= "<div>etc...</div>";
		$reason .= '<p></p><div>For further information please refer to the <a href="#" onclick="appdb.utils.ToggleFaq(11);" >FAQ</a></div>';
		return false;
    }
    if(strlen($name)>=3){
        $apps = new Default_Model_Applications();
        if ( $id != '' ) {
            $apps->filter->name->ilike("%".pg_escape_string($name)."%")->and($apps->filter->deleted->equals(false))->and($apps->filter->id->notequals($id));
        } else {
            $apps->filter->name->ilike("%".pg_escape_string($name)."%")->and($apps->filter->deleted->equals(false));
        }
        if($apps->count()>0){
            $p = base64_encode('{"url":"/apps","query":{"flt":"name:'.$name.'"},"ext":{"mainTitle":"Software","prepend":[],"append":false,"componentType":"appdb.components.Applications","filterDisplay":"Search...","isList":true,"componentArgs":[{"flt":"name:'.$name.'"}]}}');
            $reason = 'There are software items containing &#39;<i><b>' . $name . '</b></i>&#39;. Click <a href="http://' . $_SERVER["APPLICATION_UI_HOSTNAME"] . '?p='.$p.'" target="_blank">here</a> to view them in a new window.<p></p>';
			$reason .= "<div>In order to avoid confusion from similarly named software, we suggest you use a modifier in your software name in order to differentiate it from other related entries if this applies. <p></p>Good examples would be :</div>";
			$reason .= "<div  ><span>  </span>".$name. "-&lt;Country&gt;</div>";
			$reason .= "<div ><span>  </span>".$name. "-&lt;Project&gt;</div>";
			$reason .= "<div ><span>  </span>".$name. "-&lt;Virtual Organization&gt;</div>";
			$reason .= "<div ><span>  </span>".$name. "-&lt;Consortium&gt;</div>";
			$reason .= "<div>etc...</div>";
			$reason .= '<p></p><div>For further information please refer to the <a href="#" onclick="appdb.utils.ToggleFaq(11);" >FAQ</a></div>';
        }
		//This code should never be reached.
		$res = validateAppCName($name); 
		if( $res !== true ){
			$error = "Error : Invalid cname";
			$reason = 'Name already taken by <a href="http://'.$_SERVER['HTTP_HOST'].'/?p='.base64_encode('/apps/details?id=s:'.$res).'" target="_blank">'. $res . '</a>.<p></p>';
			$reason .= 'Please modify your applcation name. In order to avoid confusion from similarly named software, you should use a modifier in you software name in order to differentiate it from other related entries. Good examples would be :</div>';
			$reason .= "<div><span></span>".$name. "-&lt;Country&gt;</div>";
			$reason .= "<div><span></span>".$name. "-&lt;Project&gt;</div>";
			$reason .= "<div><span></span>".$name. "-&lt;Virtual Organization&gt;</div>";
			$reason .= "<div><span></span>".$name. "-&lt;Consortium&gt;</div>";
			$reason .= "<div>etc...</div>";
			$reason .= '<p></p><div>For further information please refer to the <a href="#" onclick="appdb.utils.ToggleFaq(11);" >FAQ</a></div>';
		}
    }
	return true;
}

function num_to_string($v){
     $r = explode(".",sprintf("%F",$v));
     return $r[0];
}

function uniqueDBObjectFilter($obj){
	static $idlist = array();
	if(in_array($obj->id,$idlist)) {
		return false;
	}
	$idlist []= $obj->id;
	return true;
}

function getPrimaryCategoryLogo($data = null){
	$app = null;
	
	if (is_numeric($data) ){
		$app = new Default_Model_Applications();
		$app->filter->id->equals($data);
		if ( count($app->items) > 0 ){
			$app = $app->items[0];
		} else {
			$app = null;
		}
	} else {
		$app = $data;
	}
	
	if (  !is_null($app) ){
		if( trim($app->metatype) === "2" ){
			return "/images/swapp.png";
		}
		$cats = $app->categories;
		foreach( $cats as $cat ) {
			if( $cat->isPrimary ) {
				return "/images/category". $cat->category->id . ".png";
			}
		}
		if( $app->tool ){
			return "/images/category2.png";
		}
	}
	return "/images/category1.gif";
}

function getAlphnumericReport($type = 'applications', $flt = "", $subtype = null){
	$sel = "";
	$model = null;
	$exp = "";
	$subtypequery = "";
	switch($type){
		case "researchers":
			$sel = "researchers.name";
			$model = new Default_Model_Researchers();
			$model->filter = FilterParser::getPeople($flt);
			$subtypequery = " WHERE researchers.deleted=false ";
			break;
		case "vos":
			$sel = "vos.name";
			$model = new Default_Model_VOs();
			$model->filter = FilterParser::getVOs($flt);
			break;
		default:
			$sel = "applications.name";
			$model = new Default_Model_Applications();
			$model->filter = FilterParser::getApplications($flt);
			$deleted = "applications.deleted=" . ((is_null($subtype) == false && strtolower($subtype)!="deleted")?"false ":"true ");
			$moderated = " AND applications.moderated=" . ((is_null($subtype) == false && strtolower($subtype)!="moderated")?"false ":"true ");
			$subtypequery = " WHERE " . $deleted . $moderated;
			break;
	}
	if( is_null($subtype) === false && $subtypequery == "") {
		$subtypequery = " WHERE ". $type ."." . $subtype . "=true";
	}
	$so = $model->getMapper()->getDbTable()->select();
	$so->from($type);
	$model->getMapper()->joins($so,$model->filter);
	$q1 = $so.''.($model->filter->expr()?" WHERE " . $model->filter->expr():'');
	$q = "SELECT  count(*) cnt, typechar  
		FROM (SELECT CASE 
		WHEN lower(substring(" . $sel . " from 1 for 1)) ~ '[0-9]' THEN '0-9'
		WHEN lower(substring(" . $sel . " from 1 for 1)) ~ '[a-zA-Z]' THEN lower(substring(" . $sel . " from 1 for 1))
		ELSE '...'
		END typechar FROM (" . $q1 . ") AS " . $type . " " . $subtypequery . " ORDER BY " . $sel . " ASC
		) fc GROUP BY fc.typechar ORDER BY fc.typechar;";
	error_log("MODEL EXPRESSION!!!!  => " . $q);
	try{
		global $application;
		$db = $application->getBootstrap()->getResource('db');
		$db->setFetchMode(Zend_Db::FETCH_BOTH);
		$res = $db->query($q)->fetchAll();
	}catch(Exception $e){
		$res = array();
		error_log($e->getMessage());
	}
	return $res;
}

function getPrimaryContact($userid=null){
	if ( $userid == null ) {
		return null;
	}
	$contacts = new Default_Model_Contacts();
	$contacts->filter->isprimary->equals(true)->and($contacts->filter->researcherid->equals($userid))->and($contacts->filter->contacttypeid->equals(7));
	if ( count($contacts->items) == 0){
		return null;
	}
	return $contacts->items[0]->data;
}

function compareReleaseDates($a, $b){
	return strcmp($b['publishedon'], $a['publishedon']);
}

function fixuZenduBuguru($s) {
	return preg_replace('/ AS "(\w+\.){0,1}\w+\.any_2"/', '', $s);
}

function shortenURL($url) {
	$url = trim($url);
	if ($url == "") {
		return $url;
	}
	$data = array("longUrl" => $url);
	$data_string = json_encode($data);
	$srv = "https://www.googleapis.com/urlshortener/v1/url";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $srv);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	if ( defined('CURLOPT_PROTOCOLS') ) curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTREDIR, 2); // PHP cURL bug: https://bugs.php.net/bug.php?id=49571
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
	curl_setopt($ch, CURLOPT_HTTPHEADER, 
		array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data_string)
		)
	);
	$content = trim(curl_exec($ch));
	curl_close($ch);
	$res = json_decode($content, true);
	if (array_key_exists("id", $res)) {
		return $res["id"];
	} else {
		return $url;
	}
}
