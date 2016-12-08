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

include('appdb_configuration.php');
include('support.php');
include('email_configuration.php');
include('Mail.php');
include('Mail/mime.php');
include('email_service.php');
include('SimpleDOM.php');
include('restapi.php');
include('RESTProxy.php');
include('harvest.php');
include('contextualization.php');
include('datasets.php');
include('Storage.php');

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


class textPNG {
    public $font;
    public $msg = ""; // default text to display.
    public $size = 24; // default font size.
    public $rot = 0; // rotation in degrees.
    public $pad = 0; // padding.
    public $transparent = 1; // transparency set to on.
    public $red = 0; // black text...
    public $grn = 0;
    public $blu = 0;
    public $bg_red = 255; // on white background.
    public $bg_grn = 255;
	public $bg_blu = 255;

	function __construct() {
		$this->font = ApplicationConfiguration::app('pngfont', 'wine-tahoma.ttf');
	}
    
    function draw() 
    {
        putenv('GDFONTPATH='.APPLICATION_PATH.'/../library/fonts');
        $width = 0;
        $height = 0;
        $offset_x = 0;
        $offset_y = 0;
        $bounds = array();
        $image = "";
    
        $bounds = ImageTTFBBox($this->size, $this->rot, $this->font, "W");
        if ($this->rot < 0) {
            $font_height = abs($bounds[7]-$bounds[1]);      
        } else if ($this->rot > 0) {
	        $font_height = abs($bounds[1]-$bounds[7]);
        } else {
            $font_height = abs($bounds[7]-$bounds[1]);
        }

        $bounds = ImageTTFBBox($this->size, $this->rot, $this->font, $this->msg);
        if ($this->rot < 0) {
            $width = abs($bounds[4]-$bounds[0]);
            $height = abs($bounds[3]-$bounds[7]);
            $offset_y = $font_height;
            $offset_x = 0;
        } else if ($this->rot > 0) {
            $width = abs($bounds[2]-$bounds[6]);
            $height = abs($bounds[1]-$bounds[5]);
            $offset_y = abs($bounds[7]-$bounds[5])+$font_height;
            $offset_x = abs($bounds[0]-$bounds[6]);
        } else {
            $width = abs($bounds[4]-$bounds[6]);
            $height = abs($bounds[7]-$bounds[1]);
            $offset_y = $font_height;;
            $offset_x = 0;
        }
        
        $image = imagecreate($width+($this->pad*2)+1,$height+($this->pad*2)+1);
        $background = ImageColorAllocate($image, $this->bg_red, $this->bg_grn, $this->bg_blu);
        $foreground = ImageColorAllocate($image, $this->red, $this->grn, $this->blu);
    
        if ($this->transparent) ImageColorTransparent($image, $background);
        ImageInterlace($image, false);
    
        ImageTTFText($image, $this->size, $this->rot, $offset_x+$this->pad, $offset_y+$this->pad, $foreground, $this->font, $this->msg);
    
        imagePNG($image);
	}
}

function verGT($v1, $v2) {
	$v1 = explode(".", $v1);
	$v2 = explode(".", $v2);
	for ( $i = 0; $i < count($v1); $i++ ) {
		if ( $v1[$i] > $v2[$i] ) {
			return true;
		} elseif ( $v1[$i] < $v2[$i] ) {
			return false;
		}
	}
	return false;
}

function check_for_appdbcached() {
	$appdbcached = trim(`pidof appdbcached`);
	if ( $appdbcached == "" ) {
		error_log('AppDB cache daemon not running. Starting it now');
		$apppath = str_replace("\n", "", APPLICATION_PATH);
		exec("bash -c 'cd $apppath/../bin; . deploy.ini; export PGPASSWORD DBNAME DBUSER DBPORT DBHOST; ./appdbcached >/dev/null 2>&1 &' >/dev/null 2>&1 &");
	}
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

function getFltHash($flt) {
	db()->setFetchMode(Zend_Db::FETCH_BOTH);
	$res = db()->query("SELECT flthash('" . str_replace("'", "''", $flt) . "') AS f;")->fetchall();
	$res = $res[0][0];
	return $res;
}

function ipCIDRCheck ($IP, $CIDR) {
    list ($net, $mask) = explode("/", $CIDR);
    
    $ip_net = ip2long ($net);
    $ip_mask = ~((1 << (32 - $mask)) - 1);

    $ip_ip = ip2long ($IP);

    $ip_ip_net = $ip_ip & $ip_mask;

    return ($ip_ip_net == $ip_net);
}

// converts inet_pton output to string with bits
function inet_to_bits($inet) 
{
// pack and unpack behavior was changed in PHP 5.5
// this call should be OK w/o changes, though
// http://php.net/manual/en/migration55.incompatible.php
   $unpacked = unpack('A16', $inet); // working with PHP 5.4
   $unpacked = str_split($unpacked[1]);
   $binaryip = '';
   foreach ($unpacked as $char) {
             $binaryip .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
   }
   return $binaryip;
}    

function isIPv4($ip) {
    return preg_match('/([0-9]{1,3}\.){3}[0-9]{1,3}/', $ip);
}

function isIPv6($ip) {
    return preg_match('/([0-9A-Fa-f]{4}:){7}[0-9A-Fa-f]{4}/', $ip);
}

function isCIDR($ip) {
    $ip = explode("/", $ip);
    if ( count($ip) == 2 ) {
        return isIPv4($ip[0]) && is_numeric($ip[1]) && $ip[1] >= 0 && $ip[1] <=32;
    } else return false;
}

function isCIDR6($ip) {
    $ip = explode("/", $ip);
    if ( count($ip) == 2 ) {
        return isIPv6($ip[0]) && is_numeric($ip[1]) && $ip[1] >= 0 && $ip[1] <=128;
    } else return false;
}

function ipCIDRCheck6($ip, $cidrnet) {
    //$ip='21DA:00D3:0000:2F3B:02AC:00FF:FE28:9C5A';
    //$cidrnet='21DA:00D3:0000:2F3B::/64';

    $ip = inet_pton($ip);
    $binaryip = inet_to_bits($ip);

    list($net, $maskbits) = explode('/', $cidrnet);
    $net = inet_pton($net);
    $binarynet = inet_to_bits($net);

    $ip_net_bits = substr($binaryip, 0, $maskbits);
    $net_bits = substr($binarynet, 0, $maskbits);

    return ( $ip_net_bits === $net_bits );
}

function debug_log($s) {
    if ( APPLICATION_ENV != "production" ) error_log($s);
}

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

class APIKeyAuthMethods {
    const E_NONE = 0;
    const E_SSO = 1;
    const E_SYSTEM = 2;

	public static function has($n,$m) {
		return (($n & $m)==$m);
	}
}

class NewsEventType {
	const E_INSERT = 1;
	const E_UPDATE = 2;
	const E_DELETE = 4;
	const E_INSERT_COMMENT = 8;
	const E_INSERT_CONTACT = 16;
	const E_ROLE_REQUEST = 32;
	const E_ROLE_VERIFIED = 64;

	public static function has($n,$m) {
		return (($n & $m)==$m);
	}
}

class NewsDeliveryType {
	const D_NO_DIGEST = 1;
	const D_DAILY_DIGEST = 2;
	const D_WEEKLY_DIGEST = 4;
	const D_MONTHLY_DIGEST = 8;

	public static function has($n,$m) {
		return ($n & $m)==$m;
	}
}
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

function validateFilterActionHelper($flt, $type, $ver = null) {
	if ( is_null($ver) ) {
		$api = Zend_Registry::get("api");
		$ver = $api["latestVersion"];
	}
	$origFlt = $flt;
	$error = '';
	$stype = '';
	$flds = array();
	$flt = FilterParser::normalizeFilter($flt, $type, $error, $flds, $ver);
	switch ($type) {
		case FilterParser::NORM_APP:
			$stype="application";
			break;
		case FilterParser::NORM_PPL:
			$stype="person";
			break;
		case FilterParser::NORM_VOS:
			$stype="vo";
			break;
		case FilterParser::NORM_DISSEMINATION:
			$stype="dissemination";
			break;
		case FilterParser::NORM_SITE:
			$stype="site";
			break;
	}
	$s = '<'.$stype.':filter xmlns:filter="http://appdb.egi.eu/api/'.$ver.'/filter" xmlns:'.$stype.'="http://appdb.egi.eu/api/'.$ver.'/'.$stype.'">';
	if ( $error != '' ) {
		$s .= '<filter:error>';
		$s .= base64_encode($error);
		$s .= '</filter:error>';
	}
	$s .= '<filter:originalForm>';
	$s .= base64_encode($origFlt);
	$s .= '</filter:originalForm>';
	$s .= '<filter:normalForm>';
	$s .= base64_encode($flt);
	$s .= '</filter:normalForm>';
	$ref = array();
	foreach ($flds as $f) {
		if ( $f["value"] != "" ) {
			if (! isset($ref[$f["ref"]]) ) $ref[$f["ref"]] = '<filter:field level="0" name="'.$f["ref"].'">';
			$ref[$f["ref"]] .= '<filter:field level="1" name="'.$f["field"].'" '.
				($f["operator"] == '' ? '' : 'operator="'.htmlentities($f["operator"]).'" ').
				($f["required"] == false ? '' : 'required="true" ').
				($f["negated"] == false ? '' : 'negated="true" ').
				'>'.base64_encode($f["value"]).'</filter:field>';
		}
	}
	foreach ($ref as $r) {
		$r .= '</filter:field>';
		$s .= $r;
	}
	$s .= '</'.$stype.':filter>';

	return $s;
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

function is_weburl($s) {
    $parts = @parse_url( $s );
    if ( array_key_exists("scheme",$parts) ) {        
        $ss = $parts["scheme"];
        if ( $ss == "http" || $ss == "https" || $ss == "ftp" || $ss == "sftp" ) return true;
    }
    return false;
}

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

function trackPage($url,$format = null) {
    return false;
}

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

function printPagingButton($parent,$page) {
        $disabled = "";
        if ($page == "prev") {
                $page = "&lt;";
                $func = 'prevPage();';
                if ($parent->currentPage == 0) $disabled = 'disabled="disabled"';
        } elseif ($page == "next") {
                $page = "&gt;";
                $func = 'nextPage();';
                if ($parent->currentPage+1 == $parent->pageCount) $disabled = 'disabled="disabled"';
        } else {
                $func = 'gotoPage('.$page.');';
                if ($parent->currentPage+1 == $page) $disabled = 'disabled="disabled"';
        }
        echo '<button dojoType="dijit.form.Button" style="font-family:Arial,sans-serif;font-size:12px;font-weight:400;color:#454545;" '.$disabled.' onclick="'.$func.'">'.$page.'</button>';
}

function putPaging($parent) {
        echo "<div style='margin-left: auto; margin-right: auto; display:block; width:auto !important; max-width:500px;  overflow-x:auto;'>";
        echo "<div style='margin-left: auto; margin-right: auto; display:block; width:".(($parent->pageCount+2)*38)."px; '>";
        printPagingButton($parent,"prev");
        for ($i=1;$i<=$parent->pageCount;$i++) {
                printPagingButton($parent,$i);
        }
        printPagingButton($parent,"next");
        echo "</div>";
        echo "</div>";
}
function putPaging2($parent) {
        if ($parent->total>0) {
                printPagingButton($parent,"prev");
                if ($parent->pageCount <= 7) {
                        for ($i=1;$i<=$parent->pageCount;$i++) {
                                printPagingButton($parent,$i);
                                echo "    ";
                        }
                } else {
                        for ($i=1;$i<=2;$i++) {
                                printPagingButton($parent,$i);
                                echo "     ";
                        }; echo "...";
                        $j=1;
                        for ($i=floor($parent->pageCount/4);$i<$parent->pageCount-1;$i=floor($parent->pageCount/4)*$j) {
                                printPagingButton($parent,$i);
                                echo "     ";
                                $j++;
                        }; echo "...";
                        for ($i=$parent->pageCount-1;$i<=$parent->pageCount;$i++) {
                                printPagingButton($parent,$i);
                                echo "     ";
                        }
                }
                printPagingButton($parent,"next");
        }
}
function putPager($parent,$sides=2,$center=5){
        $right = $left = $sides;
        $sepleft = $sepright = "";
        $sep = "...";
        //no data
        if ($parent->pageCount<=1){
            return;
        }
        //not enough data
        if($parent->pageCount<=(($sides*2)+$center)){
            putPaging($parent);
            return;
        }
        echo "<div style='margin-left: auto; margin-right: auto; display:block;width:auto !important; max-width:500px; '>";
        echo "<div style='margin-left: auto; margin-right: auto; display:block;'>";
        printPagingButton($parent,"prev");
        if($parent->currentPage<($sides+$center-1)){
            $left = $sides+$center;
            $sepright=$sep;
            $center = 0;
        }else if(($parent->currentPage+$sides+$center-1)>=$parent->pageCount){
            $right=$sides+$center;
            $sepleft = $sep;
            $center = 0;
        }else{
            $sepright = $sepleft = $sep;
        }
        //left side buttons
        for($i=1;$i<=$left;$i++){
                printPagingButton($parent, $i);
        }
        echo $sepleft;
        //center buttons
        if($center>0){
            for ($i=($parent->currentPage-floor($center/2)+1);$i<=($parent->currentPage+floor($center/2)+1);$i++) {
                printPagingButton($parent,$i);
            }
        }
        echo $sepright;
        //right side buttons
        for ($i=($parent->pageCount-$right+1);$i<=$parent->pageCount;$i++) {
            printPagingButton($parent,$i);
        }
        printPagingButton($parent,"next");
        echo "</div>";
        echo "</div>";
}
class fltTerm {
	public $val;
	public $literal;
	public $field;
	public $table;
	public $excluded;
	public $required;
	public $strict;
	public $numeric;
	public $regex;
	public $fuzzy;
	public $private;
	public $in;

	public function __construct() {
		$this->val = '';
		$this->literal = false;
		$this->field = null;
		$this->table = null;
		$this->excluded = false;
		$this->required = false;
		$this->strict = false;
		$this->regex = false;
		$this->fuzzy = false;
		$this->numeric = null;
		$this->private = false;
	}
}

function explodeFltStr(&$fltstr) {
	$f = $fltstr;
    $r = array();
    $pos1 = 0;  
    $pos2 = 0;  
	
	///////////////////////////////////////////////////////////
	// FIXME: temp fix for obscure bug in dissemination tool //	
	$f = str_replace("”", '"', $f);							 //
	///////////////////////////////////////////////////////////

	// replace spaces inside double quotes with no-break spaces
	// in order to split arguments by spaces later on
	$nbs = " "; // UTF-8 NO-BREAK SPACE;
	$s = $f;
	$instr = false;
	for ($i=0; $i<strlen($s); $i++) {
		if ( substr($s,$i,1) == '"' ) {
			$instr = ! $instr;
			if ( ($instr === false) && (substr($s,$i+1,1) !== " ") && ($i<strlen($s)-1) ) {
				$error = "Syntax error. Expected space sparator or EOL at position ".($i+1).".";
				return $s;
			}
		} elseif ( substr($s,$i,1) == " " ) {
			if ( $instr ) $s = substr($s,0,$i).$nbs.substr($s,$i+1);
		}
	}
	$f = $s;

	$f = preg_replace("/\s+/", " ", $f);
	$r = array_merge($r, explode(" ", $f));
	$a = array();
	foreach ($r as $i) {
		if (!((substr($i,0,1) == '-') || (substr($i,0,1) == '+'))) $a[] = $i;
	}
	foreach($r as $i) {
		if ((substr($i,0,1) == '-') || (substr($i,0,1) == '+')) $a[] = $i;
	}
	$fltstr=trim(implode(" ",$a));
	for($i=0; $i<count($a); $i++) $a[$i] = preg_replace('/"/','',$a[$i]);
	$b = array();
	foreach ($a as $i) if ($i != '') {
		// restore regular spaces in each argument
		$i = str_replace($nbs, " ", $i);
		$x = new fltTerm();
		$predicate = true;
		while ($predicate) {
			if ( substr($i,0,1) === "+" ) {
				$x->required = true;
				$i = substr($i,1);
			} elseif ( substr($i,0,1) === "-" ) {
				$x->excluded = true;
				$i = substr($i,1);
			} elseif ( substr($i,0,1) === ">" ) {
				$x->numeric = "g";
				$i = substr($i,1);
			} elseif ( substr($i,0,1) === "<" ) {
				$x->numeric = "l";
				$i = substr($i,1);
			} elseif ( substr($i,0,1) === "=" ) {
				$x->strict = true;
				$i = substr($i,1);
			} elseif ( substr($i,0,1) === "~" ) {
				$x->regex = true;
				$i = substr($i,1);
			} elseif ( substr($i,0,1) === "&" ) {
				$x->private = true;
				$i = substr($i,1);
			} elseif ( substr($i,0,1) === "$" ) {
				$x->fuzzy = true;
				$i = substr($i,1);
			} elseif ( substr($i,0,1) === "*" ) {
				$x->in = true;
				$i = substr($i,1);
			} else $predicate = false;			
		}
		if ( strpos($i,":") === false ) {
			$x->table = null;
			$x->field = null;
			$x->val = $i;
		} else {
			$xx = explode(":", $i);
			$x->val = $xx[1];
			if ( strpos($xx[0],".") === false ) {
				$x->field = ($xx[0]==''?null:$xx[0]);
			} else {
				$xx = explode(".", $xx[0]);
				$x->table = $xx[0];
				$x->field = ($xx[1]==''?null:$xx[1]);
			}
		}
		$b[] = $x;
	}
	return $b;
}

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

function sendMail($subject, $to, $body = '', $username, $password) { 
		if ( ApplicationConfiguration::isProductionInstance() === FALSE ) return;

		$headers = array();
        $headers['From']    = $username; 
        $headers['Subject'] = $subject;
		$headers['Date'] = date("r");
		$headers['MIME-Version'] = '1.0';
		$headers['Content-Type'] = 'text/html; charset=UTF-8';

        $recipients = array();
        if ( is_array( $to ) ) {
            foreach( $to as $_to ) {
    //            $headers['To'] = 
                $recipients[] = $_to;
            };
        } else $recipients[] = $to;

        $params['host'] = EmailConfiguration::getSmtpHost();
        $params['port'] = EmailConfiguration::getSmtpPort();
        $params['auth'] = EmailConfiguration::getSmtpAuth();
        $params['username'] = $username;
        $params['password'] = $password;

        // Create the mail object using the Mail::factory method
        $mail_object = Mail::factory('smtp', $params);
		// Split mail sending operations in bunches of 10 recipients at a time
		$rec = array();
		$recipients2 = array_unique($recipients);
		$recipients = array();
		foreach($recipients2 as $recipient) {
				$recipients[] = $recipient;
		}    
		error_log("sendMail recipients: " . var_export($recipients, true));
		for($i=0; $i<count($recipients); $i++) {
			$rec[] = $recipients[$i];
			if ((($i % 10) === 0) && ($i > 0)) {
		        $mail_object->send($rec, $headers, $body);
				$rec = array();
			} 
		}
		if ( count($rec) > 0 ) $mail_object->send($rec, $headers, $body);
}

function sendMultipartMail($subject, $to, $txtbody='', $htmlbody='', $username, $password, $replyto = false, $attachment = null, $cc=false, $ext = null) {
		error_log("[sendMultipartMail] Subject: $subject, To: " . var_export($to, true) . "CC: " . var_export($cc, true));
		if ( ApplicationConfiguration::isProductionInstance() === FALSE ) return;

		$message = new Mail_mime();
		$message->setTXTBody($txtbody);
        $message->setHTMLBody($htmlbody);
        if ( $attachment !== null ) {
            if ( isset($attachment['data']) ) {
                if ( isset($attachment['type']) ) $type = $attachment['type']; else $type='application/octem-stream';
                if ( isset($attachment['name']) ) $name = $attachment['name']; else $name='attachment.dat';
                $message->addAttachment($attachment['data'], $type, $name, false);
            }
        }
		$body = $message->get();
		$recipients = array();
		$extheaders = array('From' => $username, 'Subject' => $subject, 'Date' => date("r"));
		if( is_array($ext) && isset($ext["From"]) ){
			$extheaders["From"] = $ext["From"];
		}
		if( is_array($ext) && isset($ext["Precedence"]) ){
			$extheaders["Precedence"] = $ext["Precedence"];
		}
		if($replyto!==false && trim($replyto)!=''){
			$extheaders["Reply-To"] = $replyto;
			$extheaders["Bcc"] = $replyto;
            $recipients[] = $replyto;
			$headers["Bcc"] = $replyto;
		}
		if( $cc!==false && $cc !== null && is_numeric($cc) == false ) {
			if(is_array($cc) && count($cc)>0){
				$extCc = implode(",", $cc);
				$extheaders["Cc"] = $extCc;
				$recipients = array_merge($recipients,$cc);
			} else if(trim($cc) !== ""){
				$extheaders["Cc"] = $cc;
				$recipients[] = $cc;
			}
		}
	        $headers = $message->headers($extheaders);

        if ( is_array( $to ) ) {
            foreach( $to as $_to ) {
                $recipients[] = $_to;
            };
        } else $recipients[] = $to;

        $params['host'] = EmailConfiguration::getSmtpHost();
        $params['port'] = EmailConfiguration::getSmtpPort();
        $params['auth'] = EmailConfiguration::getSmtpAuth();
        $params['username'] = $username;
        $params['password'] = $password;
		

        // Create the mail object using the Mail::factory method
        $mail_object = Mail::factory('smtp', $params);
		// Split mail sending operations in bunches of 10 recipients at a time
		$rec = array();
		$recipients2 = array_unique($recipients);
		$recipients = array();
		foreach($recipients2 as $recipient) {
				$recipients[] = $recipient;
		}    
		error_log("sendMultipartMail recipients: " . var_export($recipients, true));
		for($i=0; $i<count($recipients); $i++) {
			$rec[] = $recipients[$i];
			if ((($i % 10) === 0) && ($i > 0)) {
		        $mail_object->send($rec, $headers, $body);
				$rec = array();
			} 
		}
		if ( count($rec) > 0 ) $mail_object->send($rec, $headers, $body);
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
class FilterParser {
	const NORM_APP = 0;
	const NORM_PPL = 1;
	const NORM_VOS = 2;
	const NORM_DISSEMINATION = 3;
	const NORM_SITE = 4;

	public static function fieldsToXML($flds, $base, $n = 0) {
		$a = explode(" ",$flds);
		sort($a);
		if ($n > 0) array_unshift($a, "any:string");
		$s = '';
		foreach ($a as $i) {
			$end = "/>";
			if ($n === 0) {
                switch ($i) {
                    case("site"):
                        $fltstr = "id:string name:string description:string tier:numeric roc:string subgrid:string supports:string";
                        if ( $base === $i ) {
                            $fltstr = $fltstr." country:complex";
                        }
						$end = '>'.FilterParser::fieldsToXML($fltstr, $base, $n+1).'</filter:field>';
                        break;
                    case("dissemination"):
                        $fltstr = "id:numeric date:string subject:string message:string";
                        if ( $base === "dissemination" ) {
                            $fltstr = $fltstr." sender:complex";
                        }
						$end = '>'.FilterParser::fieldsToXML($fltstr, $base, $n+1).'</filter:field>';
                        break;
					case("application"):
						$fltstr = "id:numeric name:string description:string abstract:string registeredon:datetime lastupdated:datetime tool:boolean rating:numeric tag:string validated:boolean owner:numeric addedby:numeric deleted:boolean releasecount:numeric arch:string os:string language:string status:string phonebook:string license:complex published:boolean hypervisor:string osfamily:string imageformat:string metatype:integer sitecount:integer year:integer month:integer day:integer";
						if ( $base === $i ) {
							$fltstr = $fltstr." person:complex country:complex middleware:complex vo:complex discipline:complex category:complex";
						}
						$end = '>'.FilterParser::fieldsToXML($fltstr, $base, $n+1).'</filter:field>';
						break;
					case("sender"):
					case("person"):
						$fltstr = "id:numeric firstname:string lastname:string name:string registeredon:datetime institute:string activated:boolean lastlogin:datetime lastupdated:datetime gender:string nodissemination:boolean contact:string role:string roleid:numeric language:string os:string arch:string phonebook:string license:complex accessgroup:complex";
						if ( $base === $i ) {
							$fltstr = $fltstr." country:complex application:complex middleware:complex vo:complex discipline:complex category:complex";
						}
						$end = '>'.FilterParser::fieldsToXML($fltstr, $base, $n+1).'</filter:field>';
                        break;
                    case("category"):
						$end = '>'.FilterParser::fieldsToXML("id:numeric name:string", $base, $n+1).'</filter:field>';
                        break;
					case("discipline"):
						$end = '>'.FilterParser::fieldsToXML("id:numeric name:string", $base, $n+1).'</filter:field>';
						break;
					case("accessgroup"):
						$end = '>'.FilterParser::fieldsToXML("id:numeric name:string payload:string", $base, $n+1).'</filter:field>';
						break;
					case("license"):
						$end = '>'.FilterParser::fieldsToXML("id:numeric name:string title:string group:string", $base, $n+1).'</filter:field>';
						break;
					case("vo"):
						$fltstr = "id:numeric name:string description:string";
						if ( $base === $i ) {
							$fltstr = $fltstr." country:complex application:complex middleware:complex vo:complex discipline:complex category:complex storetype:string phonebook:string scope:string";
						}
						$end = '>'.FilterParser::fieldsToXML($fltstr, $base, $n+1).'</filter:field>';
						break;
					case("country"):
						$end = '>'.FilterParser::fieldsToXML("id:numeric name:string isocode:string", $base, $n+1).'</filter:field>';
						break;
					case("middleware"):
						$end = '>'.FilterParser::fieldsToXML("id:numeric name:string", $base, $n+1).'</filter:field>';
						break;
					case("any"):
						$end = '>'.FilterParser::fieldsToXML("", $base, $n+1).'</filter:field>';
						break;
				}
			} else {
				// HACK
				if ( $i === "xcountry" ) {
					$i = "countryname:string";
				}
			}
			$ii = explode(":", $i);
			if ( count($ii) > 1 ) {
				$name = $ii[0];
				$type = $ii[1];
			} else {
				$name = $i;
				$type = "complex";
			}
			if ( $name != "" ) $s .= '<filter:field level="'.$n.'" name="'.$name.'" type="'.$type.'"'.$end;
		}
		return $s;
	}

	public static function normalizeFilter($s, $mode, &$error, &$flds = null, $ver = null) {
		if ( is_null($ver) ) {
			$api = Zend_Registry::get("api");
			$ver = $api["latestVersion"];
		}
		switch ($mode) {
			case (FilterParser::NORM_APP):
				$normalizer = new RestAppFilterReflection();
				break;
			case (FilterParser::NORM_PPL):
				$normalizer = new RestPplFilterReflection();
				break;
			case (FilterParser::NORM_VOS):
				$normalizer = new RestVOFilterReflection();
				break;
			case (FilterParser::NORM_DISSEMINATION):
				$normalizer = new RestDisseminationFilterReflection();
				break;
			case (FilterParser::NORM_SITE):
				$normalizer = new RestSiteFilterReflection();
				break;
		}
		$reflection_str = strval($normalizer->get()->finalize());
		$reflection = new DOMDocument();
		$reflection->loadXML($reflection_str, LIBXML_NSCLEAN | LIBXML_COMPACT);
		$help = '';
		$nbs = " "; // UTF-8 NO-BREAK SPACE;
		$ss = '';
		$error = '';
		$s = trim($s);
		// replace escaped double quotes with unicode double quotes
		$s = str_replace('\\"', "”", $s);
		// replace spaces inside double quotes with no-breaking unicode spaces 
		$instr = false;
		for ($i=0; $i<strlen($s); $i++) {
			if ( substr($s,$i,1) == '"' ) {
				$instr = ! $instr;
				if ( ($instr === false) && (substr($s,$i+1,1) !== " ") && ($i<strlen($s)-1) ) {
					$error = "Syntax error. Expected space sparator or EOL at position ".($i+1).".";
					return $s;
				}
			} elseif ( substr($s,$i,1) == " " ) {
				if ( $instr ) $s = substr($s,0,$i).$nbs.substr($s,$i+1);
			}
		}
		if ( $instr ) {
			$error = "Syntax error. Expected closing ` \" ' character before EOL.";
			return $s;
		}
		// replace multiple spaces with single space
		$s = preg_replace('/ +/', ' ', $s);
		// remove single space after ":" which is usually a user mistake
		$s = preg_replace('/: /', ':', $s);
		// split string into arguments per spaces
		$args = explode(" ", $s);
		// analyze each argument
		$argi = 0;
		foreach($args as $s) {
			$op = "";
			$neg = false;
			$required = false;
			$private = false;
			// restore normal spaces
			$s = str_replace($nbs, " ", $s);
			// look for operators (modifiers) at the start of the argument
			$doops = true;
			$ops = '';
			for ($i=0; $i<=strlen($s); $i++) {
				switch (substr($s, $i, 1)) {
					case '-':
						$neg = true;
					case '+':
						$required = true;
						break;
					case '&':
						$private = true;
						break;
					case '$':
					case '=':
					case '~':
					case '<':
					case '>':
					case '*':
						break;
					default:
						$doops = false;
				}
				if ( ! $doops ) {
					$ops = substr($s, 0, $i);
					if ( substr($s, $i) !== false ) {
						$s = substr($s, $i);
					}
					break;
				}
			}
			// now operators, if any, are in the "ops" buffer, the rest of the argument in the "s" buffer 
			// normilize operator ordering
			$ops2 = '';
			if ( (strpos($ops, "-") !== false) /*&& ($argi > 0)*/ ) $ops2 = $ops2."-";	// ignore "+","-" operator at the 1st argument
			if ( (strpos($ops, "+") !== false) /*&& ($argi > 0)*/ && (strpos($ops, "-") === false) ) $ops2 = $ops2."+";	// "-" is stronger than "+"
			if ( (strpos($ops, "<=") !== false) || (strpos($ops, "=<") !== false) ) $ops2 = $ops2."<=";
			elseif ( (strpos($ops, ">=") !== false) || (strpos($ops, "=>") !== false)) $ops2 = $ops2.">=";
			elseif ( (strpos($ops, ">") !== false) ) $ops2 = $ops2.">";
			elseif ( (strpos($ops, "<") !== false) ) $ops2 = $ops2."<";
			elseif ( (strpos($ops, "=") !== false) ) $ops2 = $ops2."=";
			elseif ( (strpos($ops, "*") !== false) ) $ops2 = $ops2."*";
			if ( (strpos($ops, "~") !== false) ) $ops2 = $ops2."~";
			elseif ( (strpos($ops, "$") !== false) ) $ops2 = $ops2."$";
			if ( (strpos($ops, "&") !== false) ) {
				if ( $ops2 !== "&" ) {
					$ops2 = $ops2."&";
				}
			}
			$ops = $ops2;
			// double quotes, if any, should be right after a colon, or at the beginning of the argument
			// return with an error if not
			if ( (strpos($s, '"') !== false) && (strpos($s, ':"') === false) && (strpos($s, '"')>0) ) {
				$error = 'Syntax error. Unexpected character ` " \' at position '.(strlen($ss)+strpos($s, '"')).".";
				return substr($ss." ".$s,1);
			}
			// look for a colon outside of double quotes, and split into property and value if there is one
			$prop = '';
			$p1 = strpos($s, ':');
			$p2 = strpos($s, '"');
			if ( $p2 === false ) $p2 = $p1+1;
			if ( ($p1 < $p2) && ($p2 !== false)) {
				$prop = substr($s, 0, $p1);
				$s = substr($s, $p1+1*($p1!==false));
			}
			// now the property, if any, is in the "prop" buffer, the rest of the argument (i.e. the value) in the "s" buffer
			// look for a dot in the property name, in order to handle references to objects
			$obj = '';
			if ( strpos($prop, ".") !== false ) {
				$obj = substr($prop, 0, strpos($prop, "."));
				$prop = substr($prop, strpos($prop, ".")+1);
			}
			$hackFlagAny = false;
			if ($obj == '') {
				//if ( $prop == '' ) {
				// DISABLE "any.any" as default context; instead, use "SEARCH_TAGET.any"
				if ( false ) {
					$hackFlagAny = true;
					$obj = "any";
					$prop = "any";
				} else {
					if (trim($prop) == "") {
						$prop = "any";
					};
					switch ($mode) {
						case (FilterParser::NORM_APP):
                            switch ($prop) {
								case "person":
								case "discipline":
								case "vo":
								case "country":
								case "middleware":
								case "category":
								case "application":
									$obj = $prop;
									$prop = "any";
									break;
								default:
									$obj = "application";
							}
							break;
						case (FilterParser::NORM_PPL):
							switch ($prop) {
								case "person":
								case "discipline":
								case "vo":
								case "country":
								case "middleware":
								case "category":
								case "application":
									$obj = $prop;
									$prop = "any";
									break;
								default:
									$obj = "person";
							}
							break;
                        case (FilterParser::NORM_VOS):
                            switch ($prop) {
								case "person":
								case "discipline":
								case "vo":
								case "country":
								case "middleware":
								case "category":
								case "application":
                                    $obj = $prop;
                                    $prop = "any";
                                    break;
                                default:
                                    $obj = "vo";
                            }
                            break;
                        case (FilterParser::NORM_DISSEMINATION):
                            switch ($prop) {
								case "sender":
								case "discipline":
								case "vo":
								case "country":
								case "middleware":
								case "category":
								case "application":
								case "dissemination":
                                    $obj = $prop;
                                    $prop = "any";
                                    break;
                                default:
                                    $obj = "dissemination";
                            }
                            break;
                        case (FilterParser::NORM_SITE):
                            switch ($prop) {
								case "country":
								case "discipline":
								case "vo":
								case "middleware":
								case "category":
								case "application":
                                    $obj = $prop;
                                    $prop = "any";
                                    break;
                                default:
                                    $obj = "site";
                            }
                            break;
					}
				}
			}
			if ($prop == $obj) {
				$prop = "any";
			}
			if ( ($obj == "any") && ( $prop != "any" ) ) {
				$error = 'Grammar error. Invalid property ` '.$prop.' \' for specifier ` any \' at keyword '.($argi+1).".";
				return substr($ss." ".$ops.($obj !== "" ? $obj."." : "").$prop.":".$s,1);
			}
			
			//backwards compatibility for "role.id"
			if ( $obj === "role" ) {
				$obj = "person"; 
				if ($prop === "id") {
					$prop = "roleid";
				} else {
					$prop = "role";
				}
			}

			// validate specifiers and properties against API reflection 
			$xp = new DOMXPath($reflection);
			$xpres = $xp->query('//filter:field[@name="'.$obj.'"]');
			$found = false;
			if (!is_null($xpres)) {
				if (count($xpres)>0) $found=true;
			}
			if (!$found) {
				$error = 'Grammar error. Invalid specifier ` '.$obj.' \' at keyword '.($argi+1).".";				
				return substr($ss." ".$ops.($obj !== "" ? $obj."." : "").$prop.":".$s,1);
			} else {
				$found = false;
				$xpres = $xp->query('//filter:field[@name="'.$obj.'"]/filter:field[@name="'.$prop.'"]');
				if (!is_null($xpres)) {
					if ($xpres->length>0) {
						$found=true;
						$isComplex = ($xpres->item(0)->attributes->getNamedItem("type")->nodeValue==="complex"?true:false);
					}
				}
				if (!$found) {
					$error = 'Grammar error. Invalid property ` '.$prop.' \' for specifier ` '.$obj.' \' at keyword '.($argi+1).".";	
					return substr($ss." ".$ops.($obj !== "" ? $obj."." : "").$prop.":".$s,1);
				} else {
					if ( $isComplex ) {
						$obj = $prop;
						$prop = "any";
					}
				}
			}
	
			if ( trim($s) === "" ) $s = '""';
			// append the normalized argument to the output buffer
			if ( $neg ) if (strpos($ops, "-") === false) $ops = "-".$ops;
			// HACK: ommit "any.any"
			if ($hackFlagAny) {
				if ( $ops !== $s ) {
					$ss = $ss." ".$ops.$s;
				} else {
					$ss = $ss." ".$s;
				}
			} else {
				$ss = $ss." ".$ops.($obj !== "" ? $obj."." : "").$prop.":".$s;
			}
			if ( $flds !== null ) {
				$op = str_replace("+", "", $ops);
				$op = str_replace("-", "", $op);
				$flds[] = array("ref" => $obj, "field" => $prop, "value" => $s, "operator" => $op, "required" => $required, "negated" => $neg);
			}
			$argi++;
		}
		// do not return the first char in the "ss" buffer, which should always be a space
		return substr($ss,1);
	}

	private static function filterImplode($f,$type = "application"){
		if($f->field !== null){
		   if($f->table === null){
				if(in_array($f->field, array("tag","tags","keyword"))){
					$f->table=$type;
					$f->field="keywords";
				}else if(in_array($f->field, array("vo","country","middleware","discipline","application"))){
					$f->table = $f->field;
					$f->field = "name";
				}else if($f->field === "person"){
					$f->table = $f->field;
					$f->field = "any";
				} else if ($f->field === "role"){
					$f->table = $f->field;
					$f->field = "description";
				} else {
					$f->table = $type;
				}
			}
		}
		$s =  ( $f->table === null )?"":$f->table .".";
		$s .= ( $f->field === null )?"":$f->field.":";
		$s .= ( $f->val === null )?"":( ( $f->table === null && $f->field === null )?'"'.$f->val.'"':$f->val );
		$pref = "";
		if($f->required){
		   $pref = "+";
		}else if($f->excluded){
		   $pref = "-";
		}
		if($f->strict){
		   $pref .= "=";
		}
		if($f->numeric !== null){
		   switch($f->numeric){
		   case "g":
			   $pref .= ">";
			   break;
		   case "l":
			   $pref .= "<";
			   break;
		   }
		}
		if($f->regex){
		   $pref .= "~";
		}
		if($f->fuzzy){
			$pref .= "$";
		}
		if($f->private){
			$pref .= "&";
		}
		$s = $pref . $s;
		return $s;
	}

	public static function filterNormalization($fltstr,&$flthash=0){
		error_log("WARNING: this function is deprecated, please use FilterParser::normalizeFilter instead");
		if ( $fltstr == '' ) {
			return $fltstr;
		}
		$fltarray = explodeFltStr($fltstr);
		
		$strar = array();
		$tmp = null;

		foreach($fltarray as $f){
			$tmp = self::filterImplode($f);
			$flthash = $flthash + hexdec(md5($tmp));
			$strar[] = $tmp;
		}
		return implode(" ", $strar);
	}

public static function getApplications($fltstr, $isfuzzy=false) {
		$f = new Default_Model_ApplicationsFilter();
		FilterParser::buildFilter($f, $fltstr, $isfuzzy);
		return $f;
	}

	public static function getPeople($fltstr, $isfuzzy=false) {
		$f = new Default_Model_ResearchersFilter();
		FilterParser::buildFilter($f, $fltstr, $isfuzzy);
		return $f;
	}

	public static function getVOs($fltstr, $isfuzzy=false) {
		$f = new Default_Model_VOsFilter();
		FilterParser::buildFilter($f, $fltstr, $isfuzzy);
		return $f;
    }

	public static function getDissemination($fltstr, $isfuzzy=false) {
		$f = new Default_Model_DisseminationFilter();
		FilterParser::buildFilter($f, $fltstr, $isfuzzy);
		return $f;
	}

	public static function getSites($fltstr, $isfuzzy=false) {
		$f = new Default_Model_SitesFilter();
		FilterParser::buildFilter($f, $fltstr, $isfuzzy);
		return $f;
	}

	public static function buildFilter(&$filter, $fltstr, $isfuzzy=false){
		$fltstr = trim($fltstr);
		if ( substr($fltstr, 0, 1) === "|" ) {
			$fltstr = trim(substr($fltstr, 1));
		}
		if ( substr($fltstr, -1, 1) === "|" ) {
			$fltstr = trim(substr($fltstr, 0, -1));
		}
		$fltstr_parts = explode(" | ", $fltstr);
		$exprs = array();
		foreach($fltstr_parts as $fltstr_part) {
			if ( trim($fltstr_part) != '' ) {
				$filter->_expr = '';
				FilterParser::__buildFilter($filter, $fltstr_part, $isfuzzy);
				$exprs[] = $filter->expr();
			}
		}
		if (count($exprs) === 0) {
			debug_log("buildFilter: null expression");
		} elseif (count($exprs) === 1 ) {
			$filter->_expr = $exprs[0];
			$filter->fltstr = $fltstr_parts[0];
		} else {
			$filter->_expr = $exprs;
			$filter->fltstr = $fltstr_parts;
		}
	}

	public static function __buildFilter(&$filter, $fltstr, $isfuzzy=false){
		$fltstr_orig = $fltstr;
		$globalPrivate = ((substr(trim($fltstr), 0, 2) === "& ") || (trim($fltstr) === "&"));
		switch(get_class($filter)) {
		case "Default_Model_ApplicationsFilter":
			$defaultTable = "application";
			break;
		case "Default_Model_ResearchersFilter":
			$defaultTable = "person";
			break;
		case "Default_Model_VOsFilter":
			$defaultTable = "vo";
            break;
        case "Default_Model_DisseminationFilter":
            $defaultTable = "dissemination";
            break;
        case "Default_Model_SitesFilter":
            $defaultTable = "site";
            break;
		default:
			return false;
		}
		if ( $fltstr != '' ) {
			$fltarray = explodeFltStr($fltstr);
			foreach($fltarray as $fltstr) {
				$chainOp = "OR";
				$innerChainOp = "OR";
				if ($fltstr->numeric !== null) { // there is an explicit numeric comparison operator present
					$expOp = $fltstr->numeric;
					if ($fltstr->strict) {
						$expOp = $expOp."e";
					} else {
						$expOp = $expOp."t";
					}
				} else { // auto comparison, no explicit numeric comparison operator present
					// following line is for backwards compatibility
					// "isfuzzy" argument has been obsoleted
					if ($isfuzzy) $expOp = "soundsLike"; else $expOp = "ilike";
					if ($fltstr->strict) {
						$expOp = "ilike";
						$strict = true;
					} else {
						$strict = false;
					}
					if ($fltstr->regex) {
						$expOp = "matches";
					}
					if ($fltstr->fuzzy) {
						$expOp = "soundsLike";
					}
					if ($fltstr->in) {
						$expOp = "in";
					}
				}
				$private = $fltstr->private || $globalPrivate;
				if ($fltstr->required) {
					$chainOp = "AND";
				} elseif ($fltstr->excluded) {
					$expOp = "not".$expOp;
					$innerChainOp = "AND";
					$chainOp = "AND";
				}
				if ( ($fltstr->field === "any") && ($fltstr->table === "any") ) $fltstr->field = null;
				if ($fltstr->field === null) {
					if (($expOp === 'like') || ($expOp === 'ilike') || ($expOp === 'notlike') || ($expOp === 'notilike')) {
						$fltstr = str_replace("%",'\\\\%',$fltstr->val);
						$fltstr = str_replace("_",'\\\\_',$fltstr);
						if ( ! $strict ) {
							$fltstr = "%".$fltstr."%";
						}
					} else $fltstr = $fltstr->val;
					$ff = array();
					$f = new Default_Model_ApplicationsFilter();
					$f->any->$expOp($fltstr);
					$ff[] = $f;
					if ( $defaultTable !== "site") {
						$f = new Default_Model_LicensesFilter();
						$f->any->$expOp($fltstr);
						$ff[] = $f;
					}
					$f = new Default_Model_VOsFilter();
					$f->any->$expOp($fltstr);
					$ff[] = $f;
					$f = new Default_Model_MiddlewaresFilter();
					$f->any->$expOp($fltstr);
					$ff[] = $f;
					$f = new Default_Model_CountriesFilter();
					$f->any->$expOp($fltstr);
					$ff[] = $f;
					if ( ! $private || $defaultTable === "application" ) {
						$f = new Default_Model_AppCountriesFilter();
						$f->any->$expOp($fltstr);
						$ff[] = $f;
					}
					if ( $defaultTable !== "site") {
						$f = new Default_Model_StatusesFilter();
						$f->any->$expOp($fltstr);
						$ff[] = $f;
					}
					$f = new Default_Model_ArchsFilter();
					$f->any->$expOp($fltstr);
					$ff[] = $f;
					$f = new Default_Model_OSesFilter();
					$f->any->$expOp($fltstr);
					$ff[] = $f;
					$f = new Default_Model_ProgLangsFilter();
					$f->any->$expOp($fltstr);
					$ff[] = $f;
					$f = new Default_Model_DisciplinesFilter();
					$f->any->$expOp($fltstr);
					$ff[] = $f;
					if ( $defaultTable !== "site") {
						$f = new Default_Model_ResearchersFilter();
						$f->any->$expOp($fltstr);
						$ff[] = $f;
					}
					$f = new Default_Model_CategoriesFilter();
					$f->any->$expOp($fltstr);
					$ff[] = $f;
					if ( $defaultTable !== "site") {
						$f = new Default_Model_PositionTypesFilter();
						$f->any->$expOp($fltstr);
						$ff[] = $f;
					}
					if ( $defaultTable !== "site") {
						$f = new Default_Model_ContactsFilter();
						$f->any->$expOp($fltstr);
						$ff[] = $f;
					}
					if ( (! $private || $defaultTable === "application") && ($defaultTable !== "vo") && ($defaultTable !== "site") && ($defaultTable !== "person") ) {
						$f = new Default_Model_HypervisorsFilter();
						$f->name->$expOp($fltstr);
						$ff[] = $f;
					}
					if ( (! $private || $defaultTable === "application" ) && ($defaultTable !== "vo") && ($defaultTable !== "site") && ($defaultTable !== "person") ) {
						$f = new Default_Model_VMIflavoursFilter();
						$f->format->$expOp($fltstr);
						$ff[] = $f;
					}
					if ( /*(! $private && $defaultTable === "application") ||*/ $defaultTable === "site" ) {
						$f = new Default_Model_SitesFilter();
						$f->any->$expOp($fltstr);
						$ff[] = $f;
					}
					$innerFilter = null;
					foreach($ff as $f) {
						if ( $innerFilter === null ) {
							$innerFilter = $f;
						} else {
							$innerFilter = $innerFilter->chain($f, $innerChainOp, $private);
						}
					}
					$filter = $filter->chain($innerFilter, $chainOp, $private);
				} else {
					$countryHack = false;
					$f1 = null;
					$val = $fltstr->val;
					$fld = $fltstr->field;
					if ( $fld === "phonebook" ) {
						$expOp = "equals";
					}
					if (($expOp === 'like') || ($expOp === 'ilike') || ($expOp === 'notlike') || ($expOp === 'notilike')) {
						$val = str_replace("%",'\\\\%',$val);
						$val = str_replace("_",'\\\\_',$val);
						if ( ! $strict ) {
							$val = "%".$val."%";
						}
					}
					if ( $fltstr->table == "any" ) $fltstr->table = null;
					if ($fltstr->table === null) {
						$tbl = $defaultTable;
					} else {
						$tbl = $fltstr->table;
					}
					//complex search fields are mapped to tables
					if ( in_array($fld, array("sender","application","person","vo","country","middleware","discipline","category")) ) {
						$tbl = $fld;
						$fld = "any";
					}
					if ( $tbl === "role" ) {
						$tbl = "person";
						$fld = "role";
					}
					if ( $tbl === "application" && $fld === "countryname" ) {
						$tbl = "country";
						$fld = "name";
						$countryHack = true;
                    }
                    $interval = Zend_Registry::get("app");
                    $interval = $interval["invalid"];
					switch($tbl) {
					case "site":
						switch($fld) {
						case "id":
						case "description":
						case "tier":
						case "subgrid":
						case "roc":
							break;
						case "name":
							break;
						case "phonebook":
							$f1 = false;
							$f2 = new Default_Model_SitesFilter();
							if ( (strtolower($val) >= "a") && (strtolower($val) <= "z") ) {
								$val = strtolower($val);
								if (strtolower($val) < "z") {
									$f2->name->ge($val, false)->and($f2->name->lt("chr(ascii('$val')+1)", false, false));
								} else {
									$f2->name->ge($val, false)->and($f2->name->lt("'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz'", false, false));
								}
							} elseif ( $val === "0-9" ) {
								$f2->name->ge("'0'", false, false)->and($f2->name->le("'9'", false, false));
							} elseif ( $val === "#" ) {
								$fld = "name";
								$f2->$fld->matches("^[^A-Za-z0-9].+");
							} else {
								continue;
							}							
							$filter->chain($f2, $chainOp, $private);
							break;
						case "supports":
							$f1 = false;
							$f2 = new Default_Model_SitesFilter();
							if (is_numeric(trim(str_replace("%", '', $val)))) {
								$supports = trim(str_replace("%", '', $val));
							} else {
								switch (strtolower($supports)) {
								case "occi":
									$supports = 1;
									break;
								default:
									$supports = 0;
									break;
								}
							}
							switch (intval($supports)) {
							case 1:
								$f2->id->in("(SELECT site_supports('occi'))",false,false);
								break;
							case 0:
							default:
								$f2->id->notin("(SELECT site_supports('occi'))",false,false);
								break;
							}
							$filter->chain($f2, $chainOp, $private);
							break;
						case "hasinstances":
							$f1 = false;
							$f2 = new Default_Model_SitesFilter();
							if (is_numeric(trim(str_replace("%", '', $val)))) {
								$siteinstances = trim(str_replace("%", '', $val));
							} else {
								switch (strtolower($siteinstances)) {
								case "occi":
									$siteinstances = 1;
									break;
								default:
									$siteinstances = 0;
									break;
								}
							}
							switch (intval($siteinstances)) {
							case 1:
								$f2->id->in("(SELECT site_instances('occi'))",false,false);
								break;
							case 0:
							default:
								$f2->id->notin("(SELECT site_instances('occi'))",false,false);
								break;
							}
							$filter->chain($f2, $chainOp, $private);
							break;
						}
						if ($f1 === null) $f1 = new Default_Model_SitesFilter();
						break;
					case "application":
						switch($fld) {
						case "sitecount":
							$fld = "(SELECT vappliance_site_count(###THETABLE###.id))";
							break;
						case "published":
							$val = str_replace("%", "", $val);
							if (filter_var($val, FILTER_VALIDATE_BOOLEAN)) {
								$f1 = false;
								$f2 = new Default_Model_VAversionsFilter();
								$f2->published->equals("true")->and($f2->archived->equals(false))->and($f2->enabled->equals(true));
								$filter->chain($f2, $chainOp, $private);
							} else {
								$f1 = new Default_Model_VAversionsFilter();
							}
							break;
						case "relatedto":
							$f1 = false;
							$f2 = new Default_Model_ApplicationsFilter();
							$val = str_replace("%", "", $val);
							$f2->id->in("(SELECT (app).id FROM related_apps(" . $val . "))", false, false);
							$filter->chain($f2, $chainOp, $private);
							break;
						case "phonebook":
							$f1 = false;
							$f2 = new Default_Model_ApplicationsFilter();
							if ( (strtolower($val) >= "a") && (strtolower($val) <= "z") ) {
								$val = strtolower($val);
								if (strtolower($val) < "z") {
									$f2->name->ge($val, false)->and($f2->name->lt("chr(ascii('$val')+1)", false, false));
								} else {
									$f2->name->ge($val, false)->and($f2->name->lt("'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz'", false, false));
								}
							} elseif ( $val === "0-9" ) {
								$f2->name->ge("'0'", false, false)->and($f2->name->le("'9'", false, false));
							} elseif ( $val === "#" ) {
								$fld = "SUBSTRING(applications.name, 1, 1)";
								$f3 = new Default_Model_ApplicationsFilter();
								// NOTE: max name length is 50 characters
								$f2->$fld->lt("'a'", false, false)->or($f2->$fld->gt("'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz'", false, false));
								$f3->$fld->lt("'0'", false, false)->or($f3->$fld->gt("'99999999999999999999999999999999999999999999999999'", false, false));
								$f2->chain($f3, "AND", $private);
							} else {
								continue;
							}							
							$filter->chain($f2, $chainOp, $private);
							break;
						case "status":
							if (is_numeric(trim(str_replace("%", '', $val)))) {
								$fld = "id";
							} else {
								$fld = "name";
							}
							$tbl = "statuses";
							$f1 = new Default_Model_StatusesFilter();
							break;
						case "osfamily":
							if (is_numeric(trim(str_replace("%", '', $val)))) {
								$fld = "id";
							} else {
								$fld = "name";
							}
							$tbl = "os_families";
							$f1 = new Default_Model_OSFamiliesFilter();
							break;
						case "imageformat":
							$tbl = "vmiflavours";
							$fld = "format";
							$f1 = new Default_Model_VMIflavoursFilter();
							break;
						case "hypervisor":
							if (is_numeric(trim(str_replace("%", '', $val)))) {
								$fld = "id";
							} else {
								$fld = "name";
							}
							$tbl = "hypervisors";
							$f1 = new Default_Model_HypervisorsFilter();
							break;
						case "os":
							if (is_numeric(trim(str_replace("%", '', $val)))) {
								$fld = "id";
							} else {
								$fld = "name";
							}
							$tbl = "oses";
							$f1 = new Default_Model_OSesFilter();
							break;
						case "arch":
							if (is_numeric(trim(str_replace("%", '', $val)))) {
								$fld = "id";
							} else {
								$fld = "name";
							}
							$tbl = "archs";
							$f1 = new Default_Model_ArchsFilter();
							break;
						case "language":
							if (is_numeric(trim(str_replace("%", '', $val)))) {
								$fld = "id";
							} else {
								$fld = "name";
							}
							$tbl = "proglangs";
							$f1 = new Default_Model_ProgLangsFilter();
							break;
						case "releasecount":
							$fld="relcount";
							$f1 = new Default_Model_AppReleaseCountFilter();
							break;
						case "validated":
							if (preg_match('/^[0-9]+ (month|year)s{0,1}$/', trim(str_replace('%', '', $val)))) {
								$fld = "lastupdated BETWEEN NOW() - INTERVAL '" . str_replace('%', '', $val) . "' AND NOW()";
								$val = "true";
							} else {
								$fld = "lastupdated BETWEEN NOW() - INTERVAL '".$interval."' AND NOW()";
							};
							break;
						case "year":
							$fld="EXTRACT(YEAR FROM dateadded)";
							break;
						case "month":
							$fld="EXTRACT(MONTH FROM dateadded)";
							break;
						case "day":
							$fld="EXTRACT(DAY FROM dateadded)";
							break;
						case "registeredon":
						case "date":
							$fld = "dateadded";
							break;
						case "tag":
						case "tags":
						case "keyword":
							$fld = "keywords";
							break;
						case "metatype":
							$f1 = new Default_Model_ApplicationsFilter();
							$f1->metatype->numequals($val);
							break;
						}
						if ($f1 === null) $f1 = new Default_Model_ApplicationsFilter();
						break;
					case "vo":
						$f1 = null;
						switch ($fld) {
						case "storetype":
							$f1 = false;
							$f2 = new Default_Model_ApplicationsFilter();
							if (is_numeric(trim(str_replace("%", '', $val)))) {
								$storetype = trim(str_replace("%", '', $val));
							} else {
								switch (strtolower($storetype)) {
								case "application":
									$storetype = 1;
									break;
								case "virtual appliance":
									$storetype = 2;
									break;
								case "software appliance":
									$storetype = 3;
									break;
								default:
									$storetype = 0;
									break;
								}
							}
							switch ($storetype) {
							case 1:
								$f2->metatype->numequals(0);
								break;
							case 2:
								$f2->metatype->numequals(1);
								break;
							case 3:
								$f2->metatype->numequals(2);
								break;
							default:
								$f2 = false;
								break;
							}
							if ($f2 !== false) {
								$filter->chain($f2, $chainOp, $private);
							}
							break;
						case "phonebook":
							$f1 = false;
							$f2 = new Default_Model_VOsFilter();
							if ( (strtolower($val) >= "a") && (strtolower($val) <= "z") ) {
								$val = strtolower($val);
								if (strtolower($val) < "z") {
									$f2->name->ge($val, false)->and($f2->name->lt("chr(ascii('$val')+1)", false, false));
								} else {
									$f2->name->ge($val, false)->and($f2->name->lt("'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz'", false, false));
								}
							} elseif ( $val === "0-9" ) {
								$f2->name->ge("'0'", false, false)->and($f2->name->le("'9'", false, false));
							} elseif ( $val === "#" ) {
								$fld = "name";
								$f2->$fld->matches("^[^A-Za-z0-9].+");
							} else {
								continue;
							}							
							$filter->chain($f2, $chainOp, $private);
						}
						if ($f1 === null) $f1 = new Default_Model_VOsFilter();
						break;
					case "country":
						if ( ! $private || $defaultTable === "application" ) {
							$f1 = false;
							$f2 = new Default_Model_CountriesFilter();
							$f2->$fld->$expOp($val);
							$f3 = new Default_Model_AppCountriesFilter();
							$f3->$fld->$expOp($val);
							$filter->chain($f2->chain($f3, "OR", $private), $chainOp, $private);
						} else {
							$f1 = new Default_Model_CountriesFilter();
						}
					case "countryxxx":
						if (( $defaultTable === "site" ) || ( $defaultTable === "person" )) {
							if ( $countryHack ) {
								$f1 = new Default_Model_CountriesFilter();
								$filter->chain($f1,$chainOp);
								$f1 = new Default_Model_AppCountriesFilter();
							} else {
								$f1 = new Default_Model_CountriesFilter();
							}
						} else {
							$f1 = new Default_Model_AppCountriesFilter();
						}
						break;
					case "middleware":
						$f1 = new Default_Model_MiddlewaresFilter();
						break;
					case "discipline":
						$f1 = new Default_Model_DisciplinesFilter();
						break;
					case "category":
						$f1 = new Default_Model_CategoriesFilter();
						break;
					case "accessgroup":
						switch($fld) {
							case "name":
							case "id":
								$f2 = new Default_Model_ActorGroupsFilter();
								$f2->$fld->$expOp($val);
								$filter->chain($f2, $chainOp, $private);
								break;
							case "payload":
								$f2 = new Default_Model_ActorGroupMembersFilter();
								$f2->$fld->$expOp($val);
								$filter->chain($f2, $chainOp, $private);
								break;
						}
						$f1 = false;
						break;
					case "license":
						switch ($fld) {
							case "id":
								$f1 = new Default_Model_LicensesFilter();
								break;
							default:
								$f2 = new Default_Model_LicensesFilter();
								$f2->$fld->$expOp($val);
								$f3 = new Default_Model_AppLicensesFilter();
								$f3->$fld->$expOp($val);
								$filter->chain($f2->chain($f3, "OR", $private), $chainOp, $private);
								$f1 = false;
						}
						break;
                    case "sender":
					case "person":
						switch($fld) {
						case "phonebook":
							$f1 = false;
							$f2 = new Default_Model_ResearchersFilter();
							if ( (strtolower($val) >= "a") && (strtolower($val) <= "z") ) {
								$val = strtolower($val);
								if (strtolower($val) < "z") {
									$f2->name->ge($val, false)->and($f2->name->lt("chr(ascii('$val')+1)", false, false));
								} else {
									$f2->name->ge($val, false)->and($f2->name->lt("'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz'", false, false));
								}
							} elseif ( $val === "0-9" ) {
								$f2->name->ge("'0'", false, false)->and($f2->name->le("'9'", false, false));
							} elseif ( $val === "#" ) {
								$fld = "name";
								$f2->$fld->matches("^[^A-Za-z0-9].+");
							} else {
								continue;
							}							
							$filter->chain($f2, $chainOp, $private);
							break;
						case "language":
							if (is_numeric(trim(str_replace("%", '', $val)))) {
								$fld = "id";
							} else {
								$fld = "name";
							}
							$tbl = "proglangs";
							$f1 = new Default_Model_ProgLangsFilter();
							break;
						case "os":
							if (is_numeric(trim(str_replace("%", '', $val)))) {
								$fld = "id";
							} else {
								$fld = "name";
							}
							$tbl = "oses";
							$f1 = new Default_Model_OSesFilter();
							break;
						case "arch":
							if (is_numeric(trim(str_replace("%", '', $val)))) {
								$fld = "id";
							} else {
								$fld = "name";
							}
							$tbl = "archs";
							$f1 = new Default_Model_ArchsFilter();
							break;
						case "year":
							$fld="EXTRACT(YEAR FROM dateinclusion)";
							break;
						case "month":
							$fld="EXTRACT(MONTH FROM dateinclusion)";
							break;
						case "day":
							$fld="EXTRACT(DAY FROM dateinclusion)";
							break;
						case "registeredon":
						case "date":
							$fld = "dateinclusion";
							break;
						case "institute":
							$fld = "institution";
							break;
						case "role":
							$f1 = new Default_Model_PositionTypesFilter();
							$fld = "any";
							break;
						case "roleid":
							$f1 = new Default_Model_PositionTypesFilter();
							$fld = "id";
							break;
						case "contact":
							$f1 = new Default_Model_ContactsFilter();
							$fld = "data";
							break;
						}
						if ($f1 === null) $f1 = new Default_Model_ResearchersFilter();
						break;
					}
					if (($f1 !== null) && ($f1 !== false)) {
						if ( isset($strict) && $strict && is_numeric($val) && (($f1->_fieldTypes[$fld] === "integer") || ($f1->_fieldTypes[$fld] === "float")) && (($expOp === 'like') || ($expOp === 'ilike') || ($expOp === 'notlike') || ($expOp === 'notilike')) ) {
							if (($expOp === "notlike") || ($expOp === "notilike")) {
								$expOp = "notnumequals";
							} else {
								$expOp = "numequals";
							}
						}
						if ((($expOp === "in") || ($expOp === "notin")) && ($val === "va_categories()")) {
							$f1->$fld->$expOp("(SELECT $val)", true, false);
						} else {
							$f1->$fld->$expOp($val);
						}
						$filter->chain($f1, $chainOp, $private);
					}
				}
			}
		}
		$filter->fltstr = $fltstr_orig;
		return true;
	}
}

class NewsFeed {
    public static function createNewsRequest($name='',$title='',$action='',$type='app',$flt='',$from='',$to='',$length='',$offset=''){
        $res = new stdClass();
		$dname = '';
        if(is_numeric($action)){
            $act = array();
            if(NewsEventType::has($action,NewsEventType::E_INSERT)){
                $act[] = "insert";
				$act[] = "insertvav";
            }
            if(NewsEventType::has($action,NewsEventType::E_UPDATE)){
                $act[] = "update";
				$act[] = "updaterel";
				$act[] = "updatevav";
            }
            if(NewsEventType::has($action,NewsEventType::E_INSERT_COMMENT)){
                $act[] = "insertcmm";
            }
            if(NewsEventType::has($action,NewsEventType::E_INSERT_CONTACT)){
                $act[] = "insertcnt";
            }
            $action = $act;
        }else{
            if(trim($action)===""){
                $action = array();
            }else{
                $action = explode(",", $action);
            }
        }
        switch(strtolower($type)){
			case "app-entry":
			  $dname = "AppDB - Software latest news";
			  $type="app-entry";
			  break;
			case "app":
				$dname = "AppDB - Software latest news";
				$type="app";
				break;
			case "ppl":
				$dname = "AppDB - People latest news";
				$type = "ppl";
				break;
			case "doc":
				$dname = "AppDB - Publications latest news";
				break;
			default:
				$type = "";//array("applications","people");
				$dname = "AppDB - Software and People latest news";
				break;
		}
		$res->name = ($name=='')?$dname:$name;
		$res->action = $action;
		$res->type = $type;
        $res->title = $title;
		$res->filter = $flt;
        $res->length = $length;
        $res->offset = $offset;
        if($to===''){
            $res->to = "EXTRACT(EPOCH FROM NOW())";
        }else{
            $res->to = "EXTRACT(EPOCH FROM NOW()) - " . $to;
        }
        if($from==='') { 
            $res->from = '0';
        } else {
            $res->from = "EXTRACT(EPOCH FROM NOW()) - " . $from;
        }
        return $res;
    }
    /* Parses url and checks for:
	// name :  display name for feed
	// action : insert,delete,update
	// type : application, people
	// flt : <the filtering>*/
    public static function parseUrl(){
		$action = urldecode((isset($_GET["a"])?$_GET["a"]:""));
        $type = urldecode((isset($_GET["t"])?$_GET["t"]:""));
        $title = ((isset($_GET["title"])?base64_decode($_GET["title"]):""));
		$flt = ((isset($_GET["f"])?base64_decode($_GET["f"]):''));
		$name = urldecode((isset($_GET["n"])?$_GET["n"]:''));
        $length = (isset($_GET['len'])?$_GET["len"]:'');
        $offset = (isset($_GET['ofs'])?$_GET["ofs"]:'');
        $from = (isset($_GET["from"])?$_GET["from"]:'');
        $to = (isset($_GET["to"])?$_GET["to"]:'');
        if(is_numeric($action)===true){
            $action = intval($action);
        }
		return self::createNewsRequest($name, $title, $action, $type, $flt,$from,$to,$length,$offset);
	}
    /* 
     * Returns the loaded aggregated_news model based on the user request query
     */
    public static function getNews($request){
        $t = $request;
		if ( $t->type == "app-entry") $t->type="app";
		$t->filter = str_replace("\xe2\x80\x9d",'"',$t->filter);
		if(trim($t->filter)!=='') {
			if($t->type==='ppl') {
				$t->filter = FilterParser::normalizeFilter($t->filter, FilterParser::NORM_PPL, $err);
				$f = FilterParser::getPeople($t->filter);
			} else if($t->type==='app') {
				$t->filter = FilterParser::normalizeFilter($t->filter, FilterParser::NORM_APP, $err);
				$f = FilterParser::getApplications($t->filter);
			} 
		}
		$news = new Default_Model_AggregateNews();
		
		if ( $t->length != '' )  $news->filter->limit($t->length);
		if ( $t->offset != '' ) $news->filter->offset($t->offset);
        
		$nf = new Default_Model_AggregateNewsFilter();
        if (count($t->action) >0) {
			if( $t->type == "app" && in_array("update", $t->action) == true ){
				$t->action[] = "updaterel";
			}
            for( $i=0; $i < count( $t->action ) ; $i+=1){
                if ( isset ( $a ) ) {
                    $a = $a->or( $nf->action->equals( trim( $t->action[$i] ) ) );
                } else {
                    $a = $nf->action->equals( trim( $t->action[$i] ) );
                }
            }
            $news->filter = $news->filter->chain($nf,"AND");
		}
        if ( trim($t->type) != '') {
			$nf->subjecttype->equals(trim($t->type));
			$news->filter = $news->filter->chain($nf,"AND");
		}

        $nf->timestamp->between(array($t->from,$t->to));
		$news->filter = $news->filter->chain($nf,"AND");
		if(isset($f)){
			if (is_array($f->expr())) {
				$f->SetExpr("(" . implode(") AND (", $f->expr()). ")");
			}
			if ( $f->expr() != '' ) {
				$nf = $nf->chain($f,"AND");
			}
		}
		$news->filter = $news->filter->chain($nf,"AND");
		debug_log($news->filter->expr());
		
		$news->filter->orderBy('timestamp DESC');
        $news->refresh();
        return $news;
    }
	public static function getPrimaryNameFromData($d){
		$len = count($d);
		if($len!=0){
			for($i=0; $i<$len; $i+=1){
				if($d[$i]->isPrimary == "true"){
					return $d[$i]->name;
				}
			}
		}
		return "software";
	}
	public static function getPrimaryIdFromData($d){
		$len = count($d);
		if($len!=0){
			for($i=0; $i<$len; $i+=1){
				if($d[$i]->isPrimary == "true"){
					return $d[$i]->id;
				}
			}
		}
		return "-1";
	}
    public static function parsePubEntry($e){
        
    }
    public static function parsePeopleEntry($e){
        switch($e->action){
            case "update":
				$link = "http://".$_SERVER['APPLICATION_UI_HOSTNAME']."?p=".base64_encode("/people/details?id=".$e->subjectid);
				$summary = $e->subjectname . " has updated his/her" . (($fields!=='')?" ".$fields:" profile information").".";
				$summaryHTML = "<table><tbody><tr><td><img src='http://".$_SERVER['APPLICATION_UI_HOSTNAME']."/people/getimage?id=".$e->subjectid."' width='30' alt=''/></td><td>".$summary."</td></tr></tbody></table>";
				break;
			case "insert":
				$link = "http://".$_SERVER['APPLICATION_UI_HOSTNAME']."?p=".base64_encode("/people/details?id=".$e->subjectid);
				$summary = $e->subjectname . " has registered with the AppDB";
				$summaryHTML = "<table><tbody><tr><td><img src='http://".$_SERVER['APPLICATION_UI_HOSTNAME']."/people/getimage?id=".$e->subjectid."' width='30' alt=''/></td><td>".$summary."</td></tr></tbody></table>";
				break;
        }
    }
	public static function parseApplicationReleaseEntry($e, $data, $type="application"){
		$result = array();
		$relfields = array();
		$tfields = explode(",", $e->fields);
		$tmp = null;
		for($i=0; $i<count($tfields); $i++){
			$tmp = explode(":",($tfields[$i]));
			if( isset($relfields[$tmp[1]]) ){
				$relfields[$tmp[1]]["events"][] = $tmp[0];
			}else{
				$relfields[$tmp[1]] = array("events"=>array($tmp[0]),"data"=>null);
			}
		}
		foreach($relfields as $k=>$v){
			$rels = new Default_Model_AppReleases();
			$rels->filter->releaseid->equals($k);
			if( count($rels) > 0 ){
				$relfields[$k]["data"] = $rels->items[0];
			}
		}
		foreach($relfields as $k=>$v){
			$d = $v["data"];
			if( in_array("state", $v["events"]) == false || ($d !== null && $d->state == 1) ){
				continue;
			}
			$d = $v["data"];
			$state = "into production";
			if( $d->state == 3){
				$state = "as a candidate";
			}
			$title = "New release of '".$data->name."'" . " " . $type . ": ".  $d->release;
			$html = "<div class='newsitem'>";
			$summary = "Release '" . $d->release . "' of series '" . $d->series . "' has been published " . $state . " at " . $d->publishedon;
			$textsummary = $summary;
			$link = "http://".$_SERVER['APPLICATION_UI_HOSTNAME']."/store/software/" . $data->cname . "/releases/" . $d->series . "/" . $d->release;
			$html .= "<div class='description'><img src='http://".$_SERVER['APPLICATION_UI_HOSTNAME']."/apps/getlogo?id=".$e->subjectid."' width='30' alt=''/>";
			$html .= "</div>";
			
			$html .= "<div class='summary'>".$summary."</div>";
			$html .="</div>";
			$result[] = array("title"=>$title,"summary"=>$summary,"textsummary"=>$textsummary,"html"=>$html,"link"=>$link,"publishedon"=>date('c',strtotime($d->publishedon)));
		}
		usort($result, "compareReleaseDates");
		return $result;
	}
	public static function parseVApplianceEntry($e, $data, $type="application"){
		$result = array();
		$relfields = array();
		$tfields = explode(",", $e->fields);
		$tmp = null;
		for($i=0; $i<count($tfields); $i++){
			$tmp = explode(":",($tfields[$i]));
			if( isset($relfields[$tmp[1]]) ){
				$relfields[$tmp[1]]["events"][] = $tmp[0];
			}else{
				$relfields[$tmp[1]] = array("events"=>array($tmp[0]),"data"=>null);
			}
		}
		foreach($relfields as $k=>$v){
			$rels = new Default_Model_VAversions();
			$rels->filter->id->equals($k);
			if( count($rels) > 0 ){
				$relfields[$k]["data"] = $rels->items[0];
			}
		}
		foreach($relfields as $k=>$v){
			if( in_array("published", $v["events"]) == false ){
				continue;
			}
			$d = $v["data"];
			$title = "Published Version '" . $d->version . "' of '".$data->name."' virtual appliance";
			$html = "<div class='newsitem'>";
			$summary = "A new version '" . $d->version . "' of " . $data->name . " virtual appliance has been published.";
			$textsummary = $summary;
			$link = "http://".$_SERVER['APPLICATION_UI_HOSTNAME']."/store/software/" . $data->cname . "/vaversion/latest";
			$html .= "<div class='description'><img src='http://".$_SERVER['APPLICATION_UI_HOSTNAME']."/apps/getlogo?id=".$e->subjectid."' width='30' alt=''/>";
			$html .= "</div>";
			
			
			$html .= "<div class='summary'>".$summary."</div>";
			$html .="</div>";
			
			$result[] = array("title"=>$title,"summary"=>$summary,"textsummary"=>$textsummary,"html"=>$html,"link"=>$link,"publishedon"=>date('c',strtotime($d->createdon)), "timestamp"=>$d->createdon);
		}
		usort($result, "compareReleaseDates");
		return $result;
	}
    public static function parseApplicationEntry($e){
        $title = '';
		$summary = '';
		$summaryHTML = '';
		$textsummary='';
        $html = "<div class='newsitem'>";
		$link = '';
        $tmp = '';
        $name = $e->subjectname;
        $typetype = "application";
		$data = json_decode(str_replace("\xe2\x80\x9d",'"',$e->subjectdata));
		if ( ! is_null($data) ) $data = $data->application;
		$fields = $e->fields;
		if(trim($fields)!=='' && ($e->action === "insertcnt" || $e->action === "insertcmm")){
            $fields = explode(",", $fields);
            $tmp = null;
            for($i=0; $i<count($fields); $i++){
                $tmp = explode(":",($fields[$i]));
                $fields[$i] = array("id"=>$tmp[0],"name"=>$tmp[1]);
            }
        }
		
		if(isset($data->category)){
			$primaryid = "" . self::getPrimaryIdFromData($data->category);
			$typetype = strtolower("" . self::getPrimaryNameFromData($data->category));
			if ($typetype != "software" ) $typetype = substr($typetype,0,strlen($typetype) - 1);
        }
        switch($e->action){
            case "update":
                $title = "`".$name."'" . " " . $typetype . " has been updated";
				$link = "http://".$_SERVER['APPLICATION_UI_HOSTNAME']."?p=".base64_encode("/apps/details?id=".$e->subjectid);
                $html .= "<div class='description'><img src='http://".$_SERVER['APPLICATION_UI_HOSTNAME']."/apps/getlogo?id=".$e->subjectid."' width='30' alt=''/>";
				if($data->description!==''){
					$html .= "<span class='title'>Description: </span><span>".$data->description."</span>";
				}
                $html .= "</div>";
                $summary = "`".$name."'" . " " . $typetype . " has updated information". (($fields!=='')?" regarding its ".$fields:"").".";
				$textsummary = $summary;
                $html .= "<div class='summary'>".$summary."</div>";
				if(is_null($data->discipline)===false && $data->tool!=='true'){
					$dName = array();
					foreach($data->discipline as $d) {
						$dName[] = $d->name;
					}
					$html .= "<div class='discipline'><span class='title'>Discipline".(count($dName)>1?"s":"").": <span><span>" . implode(", ", $dName) . "</span></div>";
				}
                break;
			case "updaterel":
				return self::parseApplicationReleaseEntry($e, $data, $typetype);
			case "updatevav":
			case "insertvav":
				return self::parseVApplianceEntry($e, $data, $typetype);
            case "insert":
                $title = "New " . $typetype . " " ."`".$name."'" ;
				$link = "http://".$_SERVER['APPLICATION_UI_HOSTNAME']."?p=".base64_encode("/apps/details?id=".$e->subjectid);
				$html .= "<div class='description'><img src='http://".$_SERVER['APPLICATION_UI_HOSTNAME']."/apps/getlogo?id=".$e->subjectid."' width='30' alt=''/>";
                if($data->description!==''){
					$html .= "<span class='title'>Description: </span><span>".$data->description."</span></div>";
				}
                $html .= "</div>";
                $summary = "`".$name."'" . " " . $typetype . " has been registered";
                if(is_null($data->discipline)===false && $data->tool!=='true'){
					$dName = array();
					foreach($data->discipline as $d) {
						$dName[] = $d->name;
					}
					$summary .= " under the `" . implode(", ", $dName) . "' discipline".(count($dName)>1?"s":"").". ";
				}
				$textsummary = $summary;
				$html .=  "<div class='summary'>".$summary."</div>";
                break;
			case "insertcnt":
                $title = "New contact".(count($fields>0)?"s":"")." added to " . "`".$name."'" . " " . $typetype . ".";
                $link = "http://".$_SERVER['APPLICATION_UI_HOSTNAME']."?p=".base64_encode("/apps/details?id=".$e->subjectid);
                $html .= "<div class='description'><img src='http://".$_SERVER['APPLICATION_UI_HOSTNAME']."/apps/getlogo?id=".$e->subjectid."' width='30' alt=''/>";
                if($data->description!==''){
					$html .= "<span class='title'>Description: </span><span>".$data->description."</span></div>";
				}
                $html .= "</div>";
                $summary = "";
				$textsummary = "";
                if(count($fields)===1){
                    $summary .= "<a href='http://".$_SERVER['APPLICATION_UI_HOSTNAME']."?p=" .base64_encode("/people/details?id=".$fields[0]["id"])."' target='_blank'>". trim($fields[0]["name"])."</a> has been added as a contact";
					$textsummary = trim($fields[0]["name"]) . " has been added as a contact";
                }else{
                    $summary = "";
                    foreach($fields as $f){
                        $summary .= "<a href='http://".$_SERVER['APPLICATION_UI_HOSTNAME']."?p=" .base64_encode("/people/details?id=".$f["id"])."' target='_blank'>". trim($f["name"])."</a>, ";
						$textsummary .=  trim($f["name"]) . ", ";
                    }
                    $summary = substr($summary,0,-2);
                    $summary .= " have been added as contacts";
					$textsummary = substr($textsummary,0,-2);
                    $textsummary .= " have been added as contacts";
                }
				$html .= "<div class='summary'>".$summary."</div>";
                break;
            case "insertcmm":
                $title = "New comment" . ((count($fields)>1)?"s":"") . " on " . "`".$name."'" . " " . $typetype . ".";
                $link = "http://".$_SERVER['APPLICATION_UI_HOSTNAME']."?p=".base64_encode("/apps/details?id=".$e->subjectid);
                $html .= "<div class='description'><img src='http://".$_SERVER['APPLICATION_UI_HOSTNAME']."/apps/getlogo?id=".$e->subjectid."' width='30' alt=''/>";
                if($data->description!==''){
					$html .= "<span class='title'>Description: </span><span>".$data->description."</span></div>";
				}
                $html .= "</div>";
				$summary .= ((count($fields)>1)?"N":"A n")."ew comment" . ((count($fields)>1)?"s have":" has") . " been posted by ";
				$textsummary = $summary;
				if ( ! is_array($fields) ) {
					$fields_tmp = array();
					$fields_tmp[] = $fields;
					$fields = $fields_tmp;
				}
				foreach($fields as $f){
					$summary .= "<a href='http://".$_SERVER['APPLICATION_UI_HOSTNAME']."?p=" .base64_encode("/people/details?id=".$f["id"])."' target='_blank'>". $f["name"]."</a>, ";
					$textsummary .=  $f["name"] . ", ";
				}
				$summary = substr($summary,0,-2);
				$textsummary = substr($textsummary,0,-2);
				$html .= "<div class='summary'>".$summary."</div>";
                break;
        }
        $html .="</div>";
        return array(array("title"=>$title,"summary"=>$summary,"textsummary"=>$textsummary,"html"=>$html,"link"=>$link));
	}
    public static function parseEntry($e){
        $res = array();
        switch($e->subjecttype){
	    case "app-entry":
	     $res = self::parseApplicationEntry($e);
	      break;
            case "app":
                $res = self::parseApplicationEntry($e);
                break;
            case "ppl":
                $res = self::parsePeopleEntry($e);
                break;
            case "doc":
                break;
        }
        return $res;

	}
    public static function getEmailDigest($delivery,$userid,$subjecttype){
        $res = array();
        $from = '';//30*24*3600

        if($delivery == NewsDeliveryType::D_DAILY_DIGEST){
            $from = 24 * 3600;
        } else if($delivery == NewsDeliveryType::D_MONTHLY_DIGEST) {
            $from = 30 * 24 * 3600;
        } else if ($delivery == NewsDeliveryType::D_WEEKLY_DIGEST) {
            $from = 7 *24 * 3600;
        }
        $mails = new Default_Model_MailSubscriptions();
        $mails->filter->researcherid->equals($userid)->and($mails->filter->delivery->hasbit($delivery))->and($mails->filter->subjecttype->equals($subjecttype));
        
        if(count($mails->items)==0){
            return array();
        }
        $mails->refresh();
        foreach($mails->items as $m){
            $req = self::createNewsRequest('', $m->name, $m->events, $m->subjecttype, $m->flt,$from,'');
            $news = array();
            $flatnews = self::getNews($req);
            if($flatnews->count()>0){
                $fl = $flatnews->items;
                foreach($fl as $fn){
                    $news[] = array("parsed"=>self::parseApplicationEntry($fn), "item"=>$fn);
                }
                $res[] = array("news"=>$news,"subscription"=>$m);
            }
        }
        return $res;
    }
    public static function getMailForUser($userid=null,$delivery='',$mc=null,$subjecttype="app"){
		if(is_null($userid)){
			return null;
		}
		$mailData = array();
		if($delivery=='' || is_numeric($delivery)==false){
			$delivery = NewsDeliveryType::D_DAILY_DIGEST | NewsDeliveryType::D_WEEKLY_DIGEST | NewsDeliveryType::D_MONTHLY_DIGEST;
		}
        $news = NewsFeed::getEmailDigest($delivery,$userid,$subjecttype);
        if(count($news)==0){
            return null;
        }
        $mailData["news"] = $news;
		$mailData["server"] = "http://".$_SERVER['APPLICATION_UI_HOSTNAME'] . "/";
		$mailData["delivery"] = $delivery;
		if($delivery == NewsDeliveryType::D_DAILY_DIGEST){
            $mailData["digest"] = "daily";
        } else if($delivery == NewsDeliveryType::D_WEEKLY_DIGEST){
            $mailData["digest"] = "weekly";
        } else if($delivery == NewsDeliveryType::D_MONTHLY_DIGEST){
            $mailData["digest"] = "monthly";
        }
        $us = new Default_Model_Researchers();
        $us->filter->id->equals($userid);
        if(count($us->items)>0){
            $u = $us->items[0];
            $mailData["unsubscribeall"]=$mailData["server"] . "news/unsubscribeall?id=".$userid."&pwd=".md5($u->mailUnsubscribePwd);
            $mailData["unsubscribedigestall"] =$mailData["server"] . "news/unsubscribeall?id=".$userid."&delivery=" . $delivery . "&pwd=".md5($u->mailUnsubscribePwd);
			$mailData["externalurl"] = $mailData["server"] . "news/mail?id=" . $userid . "&delivery=" . $delivery . "&h=" . md5('' . $u->id . ':' . $u->mailUnsubscribePwd . ':' . $delivery );
        }else{
           $mailData["unsubscribeall"]= null;
           $mailData["unsubscribedigestall"] = null;
		   $mailData["externalurl"] = null;
        }

		$text = self::getTextMail($mailData);
		$mc->mailAction($mailData);
		$html = $mc->view->render("news/mail.phtml");
		return array("html"=>$html,"text"=>$text);
	}
	public static function getTextMail($data=null){
		$res = "";
		$nl = "\r\n";
		$t = "\t";
		$res .= "To view in html format from your web browser use the url bellow : " .$nl . " " . $data["externalurl"] . $nl  .$nl;
		
		$subtitle = ucwords($data["digest"] . " news digest");
		$res .= str_repeat("=",strlen($subtitle)) . $nl;
		$res .= $subtitle  .$nl;
		$res .= str_repeat("=",strlen($subtitle)) . $nl;

		$subnews = $data["news"];
		foreach($subnews as $subnew){
			$res .= $nl;
			$sub = $subnew["subscription"];
			$news = $subnew["news"];
			$newscount = count($news);
			$title = "[ " . $sub->name . " ]" . $nl;
			$utitle = " ( Unsubscription url : " . $data["server"] ."news/unsubscribe?id=".$sub->id."&delivery=".$data["delivery"]."&pwd=".md5($sub->unsubscribePassword) . " )" . $nl;
			$res .= $title . $utitle;
			$res .= str_repeat("-",strlen($utitle)) . $nl;
			$nc = 1;
			foreach($news as $new){
				$parsedall = $new["parsed"];
				foreach ($parsedall as $parsed) {
					$item = $new["item"];
					$res .= "  " . $nc . ") " . $parsed["title"] .$nl;
					$res .= "  " . str_repeat(" ", strlen($nc . ") ")+1) . $parsed["textsummary"] . $nl;
					$res .= "  " . str_repeat(" ", strlen($nc . ") ")+1) ."For details visit : "  . $parsed['link'] . $nl;
					$nc += 1;
				}
			}
		}
		$res .= $nl.$nl;

		$res .= "To unsubscribe from all " .strtoupper($data["digest"]) . " e-mail notifications visit the URL : " . $nl . "     " . $data["unsubscribedigestall"] .$nl;
		$res .= "To unsubscribe from ALL e-mail notifications visit the URL : " . $nl . "     " . $data["unsubscribeall"]  .$nl;
		$res .= "Alternatively, you could login to the EGI Applications Database and manage your subscriptions from your profile preferences tab." .$nl;
		return $res;
	}

	public static function sendSubscriptionVerificationTextMail($subscription){
		$actions = array();
		$delivery = array();
		$users = new Default_Model_Researchers();
		$subject = "EGI AppDB: Email subscription verification";
		$body = "";
		$nl = "\r\n";
		$t = "\t";

		//Find subscriber in researchers
		$users->filter->id->equals($subscription->researcherid);
		$users->refresh();
		if(count($users->items) == 0){
			error_log("[appdb:Subscription Verification Email] : Could not find user with id = " . $subscription->researcherID . " . Delivery cancelled.");
			return ;
		}
		$user = $users->items[0];

		//Get event types of subscriptions
		if( NewsEventType::has($subscription->events,  NewsEventType::E_INSERT) == true ) $actions[] = "new software";
		if( NewsEventType::has($subscription->events,  NewsEventType::E_UPDATE) == true ) $actions[] = "software updates";
		if( NewsEventType::has($subscription->events,  NewsEventType::E_INSERT_CONTACT) == true ) $actions[] = "new contacts";
		if( NewsEventType::has($subscription->events,  NewsEventType::E_INSERT_COMMENT) == true ) $actions[] = "new comments";
		
		//Get delivery types of subscriptions
		if( NewsDeliveryType::has($subscription->delivery, NewsDeliveryType::D_DAILY_DIGEST) == true ) $delivery[] = "daily";
		if( NewsDeliveryType::has($subscription->delivery, NewsDeliveryType::D_WEEKLY_DIGEST) == true ) $delivery[] = "every monday";
		if( NewsDeliveryType::has($subscription->delivery, NewsDeliveryType::D_MONTHLY_DIGEST) == true ) $delivery[] = "every 1st day of the month";

		$body = "Dear " . $user->firstName . " " . $user->lastName . "," . $nl.$nl;
		$body .= "Your request to receive e-mail notifications about '" . $subscription->name . "' has been processed. " .$nl.$nl;

		//Render actions (events)
		$body .= "You will be notified for" ;
		if(count($actions) == 1){
			$body .= " " . $actions[0];
		}else{
			$ac = count($actions);
			for($i=0; $i<$ac; $i+=1){
				$body .= " " . $actions[$i];
				if( $i == $ac-2 ){
					$body .= (($ac>2)?",":"") ." and";
				}else if( $i < $ac-1 ){
					$body .= ",";
				}
			}
		}
		$body .= "." . $nl;

		//Render delivery
		$body .= "The delivery of notifications will take place";
		if( count($delivery) == 1 ) {
			$body .= " " . $delivery[0];
		}else{
			$dc = count($delivery);
			for($i=0; $i<$dc; $i+=1){
				$body .= " " . $delivery[$i];
				if( $i == $dc-2 ){
					$body .= (($dc>2)?",":"") ." and";
				}else if( $i <$dc-1 ){
					$body .= ",";
				}
			}
		}
		$body .= "." .$nl.$nl;

		$body .= 'If no new software registrations (or updates of existing software) occur within the given delivery time span, no e-mail will be sent.'.$nl.$nl;
		$body .= "Sincerely," .$nl;
		$body .= "EGI AppDB notifications service".$nl;
		$body .= "website: http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] ."/";
		
		//Get primary e-mail contact of subscriber and send e-mail
		$rs = new Default_Model_Contacts();
		$rs->filter->researcherid->equals($subscription->researcherid)->and($rs->filter->contacttypeid->equals(7))->and($rs->filter->isprimary->equals(true));
		if ( count($rs->refresh()->items) > 0 ) {
			$to = $rs->items[0]->data;
			//sendMultipartMail($subject, $to, $body,'', 'appdb-reports@iasa.gr', 'enadyskolopassword');
			EmailService::sendReport($subject, $to, $body);
			error_log("[appdb:Subscription Verification Email]: Sending subscription verification to " . $to );
		}else{
			error_log("[appdb:Subscription Verification Email]: Cannot find a primary e-mail for user with id = " . $subscription->researcherid);
		}
	}
    public function getSubscribers($obj=null,$action=127,$delivery=15){
        if(is_null($obj)){
            return array();
        }
        $cls = get_class($obj);
        switch($cls){
            case "Default_Model_Researcher":
                $type='app';
                break;
            case "Default_Model_Applciation":
                $type='ppl';
                break;
            default :
                return array();
        }
        $act = array();
        if(NewsEventType::has($action,NewsEventType::E_INSERT)){
            $act[] = "insert";
        }
        if(NewsEventType::has($action,NewsEventType::E_UPDATE)){
            $act[] = "update";
        }
        if(NewsEventType::has($action,NewsEventType::E_INSERT_COMMENT)){
            $act[] = "insertcmm";
        }
        if(NewsEventType::has($action,NewsEventType::E_INSERT_CONTACT)){
            $act[] = "insertcnt";
        }

        $subs = new Default_Model_MailSubscriptions();
        $subs->filter->type->equals($type)->and($subs->filter->events->hasbit($event))->and($subd->filter->delivery->hasbit($delivery));
        if($subs->count()==0){
            return array();
        }
        $subs = $subs->items;
        foreach($subs as $sub){
            switch($type){
		case "app-entry":
		   $f = FilterParser::getApplications($sub->flt);
		   break;
                case "app":
                    $f = FilterParser::getApplications($sub->flt);
                    break;
                case "ppl":
                    $f = FilterParser::getPeople($sub->flt);
                    break;
            }
            $news = new Default_Model_AggregateNews();
            $nf = new Default_Model_AggregateNewsFilter();
            if (count($act) >0) {
                for( $i=0; $i < count( $act ) ; $i+=1){
                    if ( isset ( $a ) ) {
                        $a = $a->or( $nf->action->equals( trim( $act[$i] ) ) );
                    } else {
                        $a = $nf->action->equals( trim( $act[$i] ) );
                    }
                }
                $news->filter = $news->filter->chain($nf,"AND");
            }
            $nf->subjecttype->equals(trim($type));
			$news->filter = $news->filter->chain($nf,"AND");
            $news->filter->orderBy('timestamp DESC');
            $news->refresh();
        }
        return array();
	}
}

class UserRequests {
	//Used only in development mode
	public static function getDevelopmentRecipients($extramail=''){
		$recipients = EmailConfiguration::getDevelopmentRecepients();
		if($extramail !== ''){
			$recipients[] = $extramail;
		}
		return array_unique($recipients);
	}
	
	//Retreive the primary emails of related appication contacts
	public static function getRelatedEmails($application,$requestorid){
		$receivers = array();
		$countries = array();
		
		//adding administrators and managers
		$uitems = getAdminsAndManagers();
		foreach($uitems as $i){
			$receivers[] = $i->id;
		}
		
		//Fetching primary e-mail accounts
		$emails = array();
		$contacts = new Default_Model_Contacts();
		$contacts->filter->isprimary->equals(true)->and($contacts->filter->researcherid->in($receivers));
		$conts = $contacts->items;
		foreach($conts as $i){
			if($i->data != '' && is_null($i->data) == false ){
				$emails[] = $i->data;
			}
		}
		
		//Filter out invalid values, such as duplicate or empty values
		$emails = array_unique($emails);
		return $emails;
	}
	
	public static function getAccessGroupRecipientsForGroup($groupid,$payload=null){
		$actors = new Default_Model_ActorGroupMembers();
		$f1 = new Default_Model_ActorGroupMembersFilter();
		$f2 = new Default_Model_ActorGroupMembersFilter();
		
		$f1->groupid->equals($groupid);
		$actors->filter->chain($f1, "AND");
		if( is_numeric($payload) && intval($payload) > 0 ){
			$f2->payload->equals($payload);
			$actors->filter->chain($f2, "AND");
		}
		$result = array();
		if( count($actors->items) > 0 ){
			foreach($actors->items as $actor){
				$result[] = $actor->actorguid;
			}
		}
		return $result;
	}
	public static function getAccessGroupRecipients($group, $payload=null){
		$admins =  array();
		$managers = array();
		$nils = array();
		
		switch($group->id){
			case "-1":
				$admins = self::getAccessGroupRecipientsForGroup("-1");
				break;
			case "-2":
				$admins = self::getAccessGroupRecipientsForGroup("-1");
				$managers = self::getAccessGroupRecipientsForGroup("-2");
				break;
			case "-3":
				$admins = self::getAccessGroupRecipientsForGroup("-1");
				$managers = self::getAccessGroupRecipientsForGroup("-2");
				$nils = self::getAccessGroupRecipientsForGroup("-3", $payload);
				break;
			default:
				error_log("[UserRequests:::sendEmailAccessGroupRequestNotifications]: Cannot send notifications for access group id: ". $group->id);
				return;
		}
		//Since a user can belong to many access groups
		//prefer the following order: ardmins, managers and NILS
		$uniqmanagers = array();
		foreach($managers as $m){
			if( in_array($m, $admins) === false ){
				$uniqmanagers[] = $m;
			}
		}
		$uniqnils = array();
		foreach($nils as $n){
			if( in_array($n, $admins) === false && in_array($n, $managers) === false ){
				$uniqnils[] = $n;
			}
		}
		//load group data 
		$userguids = array();
		foreach($admins as $a){
			$userguids[] = array("groupid"=> "-1", "groupname"=> "AppDB Administrators", "userguid"=> $a);
		}
		foreach($uniqmanagers as $m){
			$userguids[] = array("groupid"=> "-2", "groupname"=> "Managers", "userguid"=> $m);
		}
		foreach($uniqnils as $n){
			$userguids[] = array("groupid"=> "-3", "groupname"=> "National Representatives", "payload"=> $payload, "userguid"=> $n);
		}
		
		$users = array();
		foreach($userguids as $u){
			$researchers = new Default_Model_Researchers();
			$researchers->filter->guid->equals($u["userguid"]);
			if( count($researchers->items) === 0 ){
				continue;
			}
			$researcher = $researchers->items[0];
			$user = $u;
			$user["id"] = $researcher->id;
			$user["user"] = $researcher;
			$user["countryname"] = $researcher->getCountry()->name;
			$contacts = new Default_Model_Contacts();
			$contacts->filter->isprimary->equals(true)->and($contacts->filter->researcherid->equals($researcher->id)->and($contacts->filter->contacttypeid->equals(7)));
			if( count($contacts->items) === 0){
				continue;
			}
			$contact = $contacts->items[0];
			$user["email"] = $contact->data;
			
			$users[] = $user;
		}
		return $users;
		
	}
	public static function getAccessGroupEmailTextBody($userdata, $group, $payloaddata=null){
		$user = $userdata["user"];
		$gname = $userdata["groupname"];
		if( trim($userdata["groupid"]) === "-3" && $payloaddata!==null){
			$gname .= " of " . $payloaddata;
		}
		$groupname = $group->name;
		if( $group->id == "-3" && trim($payloaddata) !== ""){
			$groupname .= " of " . $payloaddata;
		}
		$username = $user->firstname . " " . $user->lastname;
		$res  = "Dear " . $username . ",\n\n";
		
		$res .= "\tUser [1] has requested to be included in " . $groupname . " access group.\n";
				
		$res .= "Since you are included in the " . $gname .  " group, you are authorized either to accept or reject this request.\n";
		$res .= "In order to do so, please follow the steps bellow:\n";
		$res .= "\t* login to the [2]\n";
		$res .= "\t* go to your profile, by clicking your name on the upper right corner of the page\n" ;
		$res .= "\t* select 'Pending requests' tab.\n\n";
		
		$res .= "Best regards,\n";
		$res .= "EGI Applications Database team";
		
		return $res;
	}
	public static function sendEmailAccessGroupRequestNotifications($user, $group, $payload=null){
		$country = $user->getCountry();
		$countryid = $country->id;
		$countryname = $country->name;
		$recipients = self::getAccessGroupRecipients($group, $countryid);
		$username = $user->firstname . " " . $user->lastname;
		
		$subject = "EGI Applications Database: User request to join access group " . $group->name;
		foreach($recipients as $recipient){
			$textbody = self::getAccessGroupEmailTextBody($recipient, $group, $countryname);
			$body = preg_replace("/\n/", "<br/>", $textbody);
			$body = preg_replace("/\t/", "<span style='padding-left:10px;'></span>",$body);
			$body = preg_replace("/\[1\]/","<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "/store/person/" . $user->cname . "' target='_blank' title='Visit person's entry in EGI AppDB'>" . $username . "</a>", $body);
			$body = preg_replace("/\[2\]/","<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "' target='_blank' title='Visit EGI Applications Database'>EGI Applications Database</a>",$body);
			$body = "<html><head></head><body>" . $body . "</body></html>";
			$textbody = preg_replace("/\t/", "   ",$textbody);
			$textbody = preg_replace("/\[1\]/", $username ." [1]",$textbody);
			$textbody = preg_replace("/\[2\]/", "EGI Applications Database [2]",$textbody);
			$textbody .= "\n\n________________________________________________________________________________________________________\n";
			$textbody .= "[1]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "/store/person/" . $user->cname . "\n";
			$textbody .= "[2]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "\n";
			//SEND TO APPLICATION OWNER
			if ( ApplicationConfiguration::isProductionInstance() === FALSE ) {
				error_log("SENDING TO: " . $recipient["email"] );
				error_log("SUBJECT: " . $subject);
				error_log("MESSAGE: " . $textbody);
			}else{
				//sendMultipartMail($subject,array($recipient["email"]), $textbody, $body, 'appdb-reports@iasa.gr', 'enadyskolopassword');
				EmailService::sendReport($subject, array($recipient["email"]), $textbody, $body);
			}
		}		
	}
	public static function getResponseAccessGroupsEmailBody($user, $group, $stateid){
		$state = ($stateid==2)?"accepted":"rejected";
		$username = $user->firstname . " " . $user->lastname;
		
		$res = "Dear " . $username . ",\n\n";
		$res .= "\twe inform you that the request you submitted to join " . $group->name . " access group has been " . $state . ".\n\n";
		
		if ( $stateid != 2 ) {
			$res .= "If you need further clarifications on this, please do not hesitate to contact us, by forwarding this email to either the EGI UCST team (ucst@egi.eu) or to the EGI AppDB team (appdb-support@iasa.gr)\n\n";
		}		
		$res .= "Best regards,\n";
		$res .= "EGI AppDB team";
		
		return $res;
	
	}
	//Build and send email to the requestor when a user accepts or rejects his request
	public static function sendEmailResponseAccessGroupsNotification($user,$group, $stateid=0){
		$recipient = self::getUserPrimaryEmail($user->id);
		$recipients = array();
		if( $recipient !== '' ){
			$recipients = array($recipient);
		}
		if(($stateid == 2 || $stateid == 3) && count($recipients)>0 ) {
			$state = ($stateid==2)?"Accepted":"Rejected";
			$subject = "EGI Applications Database: " . $state . " request to join " . $group->name . " access group response";
			
			$textbody = self::getResponseAccessGroupsEmailBody($user, $group, $stateid);
			$body = preg_replace("/\n/", "<br/>", $textbody);
			$body = preg_replace("/\t/", "<span style='padding-left:10px;'></span>",$body);
			$body = "<html><head></head><body>" . $body . "</body></html>";
			$textbody = preg_replace("/\t/", "   ",$textbody);
			if( ApplicationConfiguration::isProductionInstance() === FALSE ) {
				error_log("SENDING TO: " . $recipients );
				error_log("SUBJECT: " . $subject);
				error_log("MESSAGE: " . $textbody);
			}else{
				//sendMultipartMail($subject,$recipients, $textbody, $body, 'appdb-reports@iasa.gr', 'enadyskolopassword');
				EmailService::sendReport($subject, $recipients, $textbody, $body);
			}
		}
	}
	
	//Retrieve the primary email contact from a userid
	public static function getUserPrimaryEmail($userid){
		$emails = array();
		$contacts = new Default_Model_Contacts();
		$contacts->filter->isprimary->equals(true)->and($contacts->filter->researcherid->equals($userid));
		$conts = $contacts->items;
		foreach($conts as $i){
			if($i->data != '' && is_null($i->data) == false ){
				$emails[] = $i->data;
			}
		}
		return (count($emails)>0)?$emails[0]:'';
	}
	
	//Produce the text body of the email to send to the owner of the related application
	public static function getOwnerEmailTextBody($user,$app,$message,$type='joinapplication'){
		$researcher = $app->researcher;
		$owner = $researcher->firstname . " " . $researcher->lastname;
		
		$res  = "Dear " . $owner . ",\n\n";
		
		if( $type == 'joinapplication'){
			$res .= "\tUser [1] has requested to be included as a contact in the software [2].\n";
		}else if( $type == 'releasemanager'){
			$res .= "\tUser [1] has requested to be granted as a software release manager for the software [3].\n\n";
		}		
		$res .= "Since you are registered as the entry owner of this software, you may accept or reject this request.\n";
		$res .= "In order to do so, please follow the steps bellow::\n";
		$res .= "\t* login to the [3]\n";
		$res .= "\t* go to your profile, by clicking your name on the upper right corner of the page\n" ;
		$res .= "\t* select 'Pending requests' tab.\n\n";
		
		$res .= "Best regards,\n";
		$res .= "EGI Applications Database team";
		
		return $res;
	}
	
	//Produces the text body of the email to send to the related contacts(Managers,Admins)
	public static function getEmailTextBody($user,$app,$message, $type='joinapplication') {
		$res = "Dear AppDB Manager,\n\n";
		
		$res .= "This is an informative message from [1]. \n";
		$res .= "No action is required by you.\n\n";
		if( $type == 'joinapplication'){
			$res .= "User [2] requested to be included as a contact in the software [3] as a contact.\n\n";
		}else if( $type == 'releasemanager'){
			$res .= "User [2] requested to be granted as a software release manager for the software [3].\n\n";
		}
		$res .= "The software owner [4] has already been informed and been asked either to accept or reject the request.\n" ;
		$res .= "In case you would like to review the request, or override the respone, please follow the steps bellow:\n";
		$res .= "\t* login to the [1]\n";
		$res .= "\t* go to your profile, by clicking your name on the upper right corner of the page\n" ;
		$res .= "\t* select 'Pending requests' tab.\n\n";
		
		$res .= "Best regards,\n";
		$res .= "EGI Applications Database team";
		
		return $res;
	}
	
	//Build and send email notification 
	public static function sendEmailRequestNotifications($user,$app,$message,$type='joinapplication'){
		$recipients = array();
		$subject = "EGI Applications Database: User requests to join software " . $app->name;
		if( $type == 'releasemanager'){
			$subject = "EGI Applications Database: User requests to become " . $app->name . " release manager";
		}
		//SEND TO APPLICATION OWNER
		if ( $_SERVER["APPLICATION_UI_HOSTNAME"] !== "appdb.egi.eu" ){
			$recipients = self::getDevelopmentRecipients(self::getUserPrimaryEmail($user->id));//add requestor for testing development
		}else{
			$recipients[] = self::getUserPrimaryEmail($app->addedby);
		}
		$textbody = self::getOwnerEmailTextBody($user, $app, $message, $type);
		$body = preg_replace("/\n/", "<br/>", $textbody);
		$body = preg_replace("/\t/", "<span style='padding-left:10px;'></span>",$body);
		$body = preg_replace("/\[1\]/","<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/people/details?id=". $user->id) ."' target='_blank' title='Visit person's entry in EGI AppDB'>" . $user->firstname . " " . $user->lastname . "</a>", $body);
		$body = preg_replace("/\[2\]/","'<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/apps/details?id=".$app->id) ."' target='_blank' title='Visit software entry in EGI AppDB'>" . $app->name . "</a>'", $body);
		$body = preg_replace("/\[3\]/","<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "' target='_blank' title='Visit EGI Applications Database'>EGI Applications Database</a>",$body);
		$body = "<html><head></head><body>" . $body . "</body></html>";
		$textbody = preg_replace("/\t/", "   ",$textbody);
		$textbody = preg_replace("/\[1\]/", $user->firstname . " " . $user->lastname ." [1]",$textbody);
		$textbody = preg_replace("/\[2\]/", "'" . $app->name ."' [2]",$textbody);
		$textbody = preg_replace("/\[3\]/", "EGI Applications Database [3]",$textbody);
		$textbody .= "\n\n________________________________________________________________________________________________________\n";
		$textbody .= "[1]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/people/details?id=". $user->id) . "\n";
		$textbody .= "[2]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/apps/details?id=".$app->id) . " \n";
		$textbody .= "[3]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "\n";
		
		//SEND TO APPLICATION OWNER
		if ( ApplicationConfiguration::isProductionInstance() === FALSE ){
			error_log("SENDING TO: " . implode(" , ", $recipients) );
			error_log("SUBJECT: " . $subject );
			error_log("MESSAGE: " . $textbody);
		}else{
			//sendMultipartMail($subject,$recipients, $textbody, $body, 'appdb-reports@iasa.gr', 'enadyskolopassword');
			EmailService::sendReport($subject, $recipients, $textbody, $body);
		}
		//SEND TO MANAGERS AND ADMINISTRATORS
		if ( ApplicationConfiguration::isProductionInstance() === FALSE ){
			$recipients = self::getDevelopmentRecipients(self::getUserPrimaryEmail($user->id));//add requestor for testing development
		}else{
			$recipients = self::getRelatedEmails($app, $user->id);
		}
		
		$owner =  $app->researcher->firstname . " " . $app->researcher->lastname;
		$ownerid = $app->researcher->id;
		$textbody = self::getEmailTextBody($user, $app, $message, $type);
		$body = preg_replace("/\n/", "<br/>", $textbody);
		$body = preg_replace("/\t/", "<span style='padding-left:10px;'></span>",$body);
		$body = preg_replace("/\[1\]/","<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "' target='_blank' title='Visit EGI Applications Database'>EGI Applications Database</a>",$body);
		$body = preg_replace("/\[2\]/","<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/people/details?id=". $user->id) ."' target='_blank' title='Visit person's entry in EGI AppDB'>" . $user->firstname . " " . $user->lastname . "</a>", $body);
		$body = preg_replace("/\[3\]/","'<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/apps/details?id=".$app->id) ."' target='_blank' title='Visit software entry in EGI AppDB'>" . $app->name . "</a>'", $body);
		$body = preg_replace("/\[4\]/","<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/people/details?id=". $ownerid) . "' target='_blank' title='Visit owner's entry in EGI AppDB'>" . $owner  ."</a>",$body);
		$body = "<html><head></head><body>" . $body . "</body></html>";
		$textbody = preg_replace("/\t/", "   ",$textbody);	
		$textbody = preg_replace("/\[1\]/", "EGI Applications Database [1]",$textbody);
		$textbody = preg_replace("/\[2\]/", $user->firstname . " " . $user->lastname ." [2]",$textbody);
		$textbody = preg_replace("/\[3\]/", "'" . $app->name ."' [3]",$textbody);
		$textbody = preg_replace("/\[4\]/", $owner ." [4]",$textbody);
				
		$textbody .= "\n\n________________________________________________________________________________________________________\n";
		$textbody .= "[1]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "\n";
		$textbody .= "[2]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/people/details?id=". $user->id) . "\n";
		$textbody .= "[3]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/apps/details?id=".$app->id) . " \n";
		$textbody .= "[4]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/people/details?id=".$ownerid) . " \n";
		
		//SEND TO APPLICATION OWNER
		if ( ApplicationConfiguration::isProductionInstance() === FALSE ) {
			error_log("SENDING TO: " . implode(" , ", $recipients) );
			error_log("SUBJECT: " . $subject );
			error_log("MESSAGE: " . $textbody);
		} else {
			//sendMultipartMail($subject,$recipients, $textbody, $body, 'appdb-reports@iasa.gr', 'enadyskolopassword');
			EmailService::sendReport($subject, $recipients, $textbody, $body);
		}
	}
	
	//Build the text body of the email for responding to an action over a user request.
	public static function getResponseEmailBody($user, $app, $stateid, $type='joinapplication'){
		$state = ($stateid==2)?"accepted":"rejected";
		$username = $user->firstname . " " . $user->lastname;
		
		$res = "Dear " . $username . ",\n\n";
		if( $type == 'joinapplication'){
			$res .= "\twe inform you that the request you submitted to join software [1] has been " . $state . ".\n\n";
		}else if( $type == 'releasemanager'){
			$res .= "\twe inform you that the request you submitted to be granted as a software release manager for software [1] has been " . $state . ".\n\n";
		}
		if ( $stateid != 2 ) {
			$res .= "If you need further clarifications on this, please do not hesitate to contact us, by forwarding this email to either the EGI UCST team (ucst@egi.eu) or to the EGI AppDB team (appdb-support@iasa.gr)\n\n";
		}		
		$res .= "Best regards,\n";
		$res .= "EGI AppDB team";
		
		return $res;
	}
	
	//Build and send email to the requestor when a user accepts or rejects his request
	public static function sendEmailResponseNotification($user,$app, $stateid=0, $type='joinapplication'){
		$recipients = self::getUserPrimaryEmail($user->id);
		if(($stateid == 2 || $stateid == 3) && count($recipients)>0 ) {
			$state = ($stateid==2)?"Accepted":"Rejected";
			$subject = "EGI Applications Database: " . $state . " request to join software " . $app->name . " response";
			if($type == 'releasemanager'){
				$subject = "EGI Applications Database: " . $state . " request to manage releases for software " . $app->name . " response";
			}
			$textbody = self::getResponseEmailBody($user, $app, $stateid, $type);
			$body = preg_replace("/\n/", "<br/>", $textbody);
			$body = preg_replace("/\t/", "<span style='padding-left:10px;'></span>",$body);
			$body = preg_replace("/\[1\]/", "'<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/apps/details?id=".$app->id) . "' target='_blank' title='View software entry in EGI AppDB'>" . $app->name . "</a>'", $body);
			$body = "<html><head></head><body>" . $body . "</body></html>";
			$textbody = preg_replace("/\t/", "   ",$textbody);
			$textbody = preg_replace("/\[1\]/","'" . $app->name . "' [1]",$textbody);
			$textbody .= "\n\n________________________________________________________________________________________________________\n";
			$textbody .= "[1]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/apps/details?id=".$app->id) . " \n";
			if( ApplicationConfiguration::isProductionInstance() === FALSE ) {
				error_log("SENDING TO: " . $recipients );
				error_log("SUBJECT: " . $subject);
				error_log("MESSAGE: " . $textbody);
			}
			//sendMultipartMail($subject,$recipients, $textbody, $body, 'appdb-reports@iasa.gr', 'enadyskolopassword');
			EmailService::sendReport($subject, $recipients, $textbody, $body);
		}
	}
}

class ApplicationMessage {
	public static function getTextBody($app, $user, $useremail, $recipient, $message){
		$res = "";
		$res .= "The [1] user, [2] sent you the message bellow\n";
		$res .= "regarding the software [3].\n";
		$res .= "If you would like to send a reply, then please do so by directly using\n";
		$res .= "his/her personal email address: [ " . $useremail . " ]\n";
		$res .= "\n\n";
		
		$res .= $message;
		
		return $res;
	}
	public static function sendMessage($appid,$userid,$recipientid,$message){
		//Get sender
		$users = new Default_Model_Researchers();
		$users->filter->id->equals($userid);
		if( $users->count() == 0 ) {
			return "Sender not found";
		}
		$user = $users->items[0];
		
		//Get sender's primary email
		$contacts = new Default_Model_Contacts();
		$contacts->filter->isprimary->equals(true)->and($contacts->filter->researcherid->equals($userid));
		if( $contacts->count() == 0 ) {
			return "Sender has no primary e-mail set";
		}
		$useremail = $contacts->items[0]->data;
		
		//Get recipient
		$recipients = new Default_Model_Researchers();
		$recipients->filter->id->equals($recipientid);
		if( $recipients->count() == 0 ) { 
			return "Recipient not found";
		}
		$recipient = $recipients->items[0];
		
		//Get recipient's primary email
		$contacts = new Default_Model_Contacts();
		$contacts->filter->isprimary->equals(true)->and($contacts->filter->researcherid->equals($recipientid));
		if( $contacts->count() == 0 ) {
			return "Recipient has no primary e-mail set";
		}
		$recipientmail = $contacts->items[0]->data;
		
		//Get application
		$applications = new Default_Model_Applications();
		$applications->filter->id->equals($appid);
		if( $applications->count() == 0 ) {
			return "Software not found";
		}
		$app = $applications->items[0];
		
		//Decode message
		$message = base64_decode($message);
		if( strlen(trim($message)) === 0) {
			return "Message is empty";
		}
		
		//Get text body and also set html body
		$textbody = self::getTextBody($app,$user,$useremail,$recipient,$message);
		$body = preg_replace("/\</","&lt;",$textbody);
		$body = preg_replace("/\>/","&gt;",$body);
		$body = preg_replace("/\n/", "<br/>", $body);
		$body = preg_replace("/\t/", "<span style='padding-left:10px;'></span>",$body);
		$body = preg_replace("/\[1\]/", "<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "' target='_blank' title='Visit EGI Applications Database' >EGI AppDB</a>"   , $body);
		$body = preg_replace("/\[2\]/", "<a href='http://" .  $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("//people/details?id=".$user->id) . "' target='_blank' title='View person's entry in EGI AppDB' >" . $user->firstname . " " . $user->lastname . "</a>"   , $body);
		$body = preg_replace("/\[3\]/", "'<a href='http://" .  $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/apps/details?id=".$app->id) . "' target='_blank' title='View software entry in EGI AppDB' >" . $app->name . "</a>'"   , $body);
		$body = "<html><head></head><body>" . $body . "</body></html>";
		
		$textbody = preg_replace("/\t/", "   ",$textbody);
		$textbody = preg_replace("/\[1\]/", "EGI AppDB [1]",$textbody);
		$textbody = preg_replace("/\[2\]/", $user->firstname ." " . $user->lastname . " [2]", $textbody);
		$textbody = preg_replace("/\[3\]/", "'" . $app->name . "' [3]",$textbody);
		$textbody .= "\n\n________________________________________________________________________________________________________\n";
		$textbody .= "[1]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "\n";
		$textbody .= "[2]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/people/details?id=". $user->id) . "\n";
		$textbody .= "[3]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/apps/details?id=".$app->id) . " \n";
		
		$subject = "EGI Applications Database: " . $user->firstname . " " . $user->lastname . " sent you a message";
		
		if(trim($useremail) === ''){
			$useremail = false;
		}
		//sendMultipartMail($subject,$recipientmail, $textbody, $body, 'appdb-reports@iasa.gr', 'enadyskolopassword',$useremail, null, false, array("From"=>$useremail));
		EmailService::sendReportAsUser($useremail, $subject, $recipientmail, $textbody, $body);
	}
}

class APIKeyRequests{
	public static function getMailBody($user,$apikey, $message){
		$res = "";
		$res .= "User [1] requests permissions regarding API key [2]\n";
		$res .= "=====================================================\n\n";
		
		$res .= $message;
		
		return $res;
	}
	
	public static function sendPermissionsRequest($userid, $apikeyid, $msg){
		//Get sender
		$users = new Default_Model_Researchers();
		$users->filter->id->equals($userid);
		if( $users->count() == 0 ) {
			return "Sender not found";
		}
		$user = $users->items[0];
		
		//Get sender's primary email
		$contacts = new Default_Model_Contacts();
		$contacts->filter->isprimary->equals(true)->and($contacts->filter->researcherid->equals($userid));
		if( $contacts->count() == 0 ) {
			return "Sender has no primary e-mail set";
		}
		
		//Check if e-mail has value
		$useremail = $contacts->items[0]->data;
		if( trim($useremail) == '' ){
			return "Sender has no valid primary e-mail set";
		}
		
		//Get api key
		$apikeys = new Default_Model_APIKeys();
		$apikeys->filter->id->equals($apikeyid)->and($apikeys->filter->ownerid->equals($userid));
		if( count($apikeys) == 0 ) {
			return "Api key not found";
		}
		$apikey = $apikeys->items[0];
		
		//Get Appdb administrators
		$recipients = array();
		$admins = new Default_Model_Researchers();
		$agmf = new Default_Model_ActorGroupMembersFilter();
		$agmf->groupid->numequals(-1); // admins
		$admins->filter->chain($agmf, "AND");
		if( count($admins->items) == 0 ) {
			return "";
		}
		//Get admins primary emails
		$admins = $admins->items;
		foreach($admins as $admin){
			$contacts = new Default_Model_Contacts();
			$contacts->filter->isprimary->equals(true)->and($contacts->filter->researcherid->equals($admin->id))->and($contacts->filter->contacttypeid->equals(7));
			if( count($contacts->items) == 0 ) {
				continue;
			}
			if(trim($contacts->items[0]->data) !== ''){
				$recipients[] = $contacts->items[0]->data;
			}
		}
		$recipients = array_unique($recipients);
		if( count($recipients) == 0 ){
			return "";
		}
		
		$textbody = self::getMailBody($user, $apikey, $msg);
		//Get text body and also set html body
		$body = preg_replace("/\</","&lt;", $textbody);
		$body = preg_replace("/\>/","&gt;", $body);
		$body = preg_replace("/\n/", "<br/>", $body);
		$body = preg_replace("/\t/", "<span style='padding-left:10px;'></span>", $body);
		$body = preg_replace("/\[1\]/", "<a href='http://" .  $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/people/details?id=".$user->id) . "' target='_blank' title='View person's entry in EGI AppDB' >" . $user->firstname . " " . $user->lastname . "</a>"   , $body);
		$body = preg_replace("/\[2\]/", "<b>" . $apikey->key . "</b>", $body);
		$body = "<html><head></head><body>" . $body . "</body></html>";
		
		$textbody = preg_replace("/\t/", "   ", $textbody);
		$textbody = preg_replace("/\[1\]/", $user->firstname . " " . $user->lastname . " [id: " . $user->id . ", url: http://" .  $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/people/details?id=".$user->id) . "]", $textbody);
		$textbody = preg_replace("/\[2\]/", $apikey->key, $textbody);
		
		$subject = "EGI AppDB: API Permissions request from user " . $user->firstname . " " . $user->lastname;
		
		//sendMultipartMail($subject,$recipients, $textbody, $body, 'appdb-reports@iasa.gr', 'enadyskolopassword',$useremail);
		EmailService::sendReport($subject, $recipients, $textbody, $body, $useremail);
		return true;
	}
}

class OutdatedApplication{
	public static function getTemplateBody($ownername){
		$res = "Dear " . $ownername .",\n";
		$res .= "\n";
		$res .= "According to our records the profile of your software [1] in the \n";
		$res .= "[2] has not been updated during the past ".$_SERVER["validation_period"]."\n";
		$res .= "or more. This is an invitation to you to login to the Applications Database \n";
		$res .= "and update the profile of your software or to confirm that the profile is \n";
		$res .= "still up to date. Both the update and the validation can be done on the \n";
		$res .= "profile page of your software.\n";
		$res .= "Your cooperation would help us in the maintenance of the database and\n";
		$res .= "would help the users distinguish up to date information from outdated\n";
		$res .= "entries.\n";
		$res .= "\n";
		$res .= "Best regards,\n";
		$res .= "The AppDB development team\n";
		return $res;
	}
	public static function getTemplateListBody($ownername){
		$res = "Dear " . $ownername .",\n";
		$res .= "\n";
		$res .= "According to our records the profiles of your [1] listed bellow in the \n";
		$res .= "[2] have not been updated during the past ".$_SERVER["validation_period"]."\n";
		$res .= "or more.\n\n";
		$res .= "[*]";
		$res .= "\n";
		$res .= "This is an invitation to you to login to the Applications Database \n";
		$res .= "and update the profiles of your software or to confirm that the profiles are \n";
		$res .= "still up to date. Both the update and the validation can be done on the \n";
		$res .= "profile pages of your software.\n";
		$res .= "Your cooperation would help us in the maintenance of the database and\n";
		$res .= "would help the users distinguish up to date information from outdated\n";
		$res .= "entries.\n";
		$res .= "\n";
		$res .= "Best regards,\n";
		$res .= "The AppDB development team\n";
		return $res;
	}
	
	public static function sendMessages($isReminder = false){
		$items = db()->query("SELECT * FROM nonvalidated_apps_per_owner")->fetchAll();
		foreach($items as $item){
			$text = "";
			$body = "";
			if(is_null($item["contact"])){
				error_log("[OutdatedApps]: No contact info for " . $item["ownerid"] .":" . $item["firstname"] . " " . $item["lastname"] );
				//TODO: Case where the owner of the application has no contact point
				continue;
			}
			$recipients = array($item["contact"]);
			$subject = "Notification:";
			if(isnull($item["lastsent"]) == false) {
				$subject = "Reminder:";
			}
			$subject .= "EGI AppDB outdated software profile";
			
			$appids = $item["appids"];
			$appids = explode(";", $appids);
			$appnames = $item["appnames"];
			$appnames = explode(";", $appnames);
			if( count($appids) == 0 ) continue;
			if( count($appids) == 1 ) {
				$template = self::getTemplateBody($item["firstname"] . " " . $item["lastname"]);
				$body = preg_replace("/\[1\]/","<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/apps/details?id=".$appids[0]) . "' target='_blank'>".$appnames[0]."</a>",$template);
				$body = preg_replace("/\[2\]/","<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"]."' target='_blank'>EGI Applications Database</a>", $body);
				$body = "<html><head></head><body><pre>" . $body . "</pre></body></html>";
				$text = preg_replace("/\[1\]/","" . $appnames[0] . " [1]",$template);
				$text = preg_replace("/\[2\]/", "EGI Applications Database [2]",$text);
				$text .= "\n\n________________________________________________________________________________________________________\n";
				$text .= "[1]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/apps/details?id=".$appids[0]) . "\n";
				$text .= "[2]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"];
			} else {
				$template = self::getTemplateListBody($item["firstname"] . " " . $item["lastname"]);
				$listpermalink = "http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode('{"url":"/apps","query":{"flt":"=application.owner:' . $item["ownerid"] . ' +=application.validated:false"},"ext":{"isBaseQuery":true,"append":true,"filterDisplay":"Search outdated...","mainTitle":"Outdated entries"}}');
				$body = preg_replace("/\[1\]/","<a href='".$listpermalink."' target='_blank'>software</a>",$template);
				$body = preg_replace("/\[2\]/","<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"]."' target='_blank'>EGI Applications Database</a>", $body);
				$body = "<html><head></head><body><pre>" . $body . "</pre></body></html>";
				$text = preg_replace("/\[1\]/","software [1]",$template);
				$text = preg_replace("/\[2\]/", "EGI Applications Database [2]",$text);
				$text .= "\n\n________________________________________________________________________________________________________\n";
				$text .= "[1]. ".$listpermalink." \n";
				$text .= "[2]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"];
				
				$bodylist = "";
				$textlist = "";
				
				//Make unique arrays of application ids and names
				$unames = array();
				$uids = array();
				for($i=0; $i<count($appids); $i+=1){
					if( in_array($appids[$i], $uids) === false ){
						$uids[] = $appids[$i];
						$unames[] = $appnames[$i];
					}
				}
				$appids = $uids;
				$appnames = $unames;
				
				for($i=0; $i<count($appids); $i+=1){
					$bodylist .= "<div style='padding-left:10px;'>-<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/apps/details?id=".$appids[$i]) . "' target='_blank'>". $appnames[$i] . "</a></div>";
					$textlist .= "\t- " . $appnames[$i] . "\n";
				}
				$body = preg_replace("/\[\*\]/", $bodylist, $body);
				$text = preg_replace("/\[\*\]/", $textlist, $text);
			}
			
			if( ApplicationConfiguration::isProductionInstance() === FALSE ) {
				error_log("SENDING OUTDATED TO:");
				error_log("SUBJECT:" . $subject);
				error_log("RECIPIENTS: " . var_export($recipients,true));
				error_log("BODY: " . $text);
				echo "<div style='background-color:lightgrey;border:1px solid black'><b>subject:</b>".$subject."</div><div style='background-color:lightgrey;margin-bottom:10px;border:1px solid black'><b>TO:</b>".implode(",",$recipients)."</div><div style='background-color:lightgreen;border:1px solid black;'>". $body . "</div><div style='background-color:#99DBFF;margin-bottom:10px;border:1px solid black'><pre>" . $text . "</pre></div>";	
			} else {
				//sendMultipartMail($subject,$recipients,$text,$body,'appdb-reports@iasa.gr','enadyskolopassword','appdb-support@iasa.gr',null, false,array("Precedence"=>"bulk"));
				EmailService::sendBulkReport($subject, $recipients, $text, $body, EmailConfiguration::getSupportAddress());
			}
		}
		if ( APPLICATION_ENV === "production" ) db()->query("INSERT INTO app_validation_log (appid) SELECT DISTINCT id FROM applications, (SELECT string_to_array(array_to_string(array_agg(array_to_string(ids,',')),','),',') as ids FROM (SELECT string_to_array(appids, ';') as ids FROM (SELECT * FROM nonvalidated_apps_per_owner) AS t) as tt) as ttt WHERE id::text = ANY(ttt.ids)")->fetchAll();
		return true;
	}
}

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

function sendUserInboxNotification($receiverid=null, $sendername=""){
	if( $receiverid == null || is_numeric($receiverid) == false ) {
		return;
	}
	
	$receivers = new Default_Model_Researchers();
	$receivers->filter->id->equals($receiverid);
	if( count($receivers->items) == 0 ) {
		return;
	}
	$receiver = $receivers->items[0];
	
	$mails = new Default_Model_MailSubscriptions();
	$mails->filter->researcherid->equals($receiverid)->and($mails->filter->subjecttype->equals('inbox'));
	if ( count($mails->items) == 0 ) {
		return;
	}
	
	$email = getPrimaryContact($receiverid);
	if ( $email == null ) {
		return;
	}
	
	
	$recipients = array($email);
	
	$ms=new Default_Model_Messages();
	$ms->filter->receiverid->equals($receiverid);
	$all = count($ms->items);
	$unread = $ms->unreadCount();
	
	$subject = "EGI AppDB: New message from user";
	
	$text = "Dear " . $receiver->name .",\n";
	$text .= "\n";
	$text .= "you have a new message in your inbox";
	if ( trim($sendername) !== "" ){
		$text .= " by " . $sendername;
	}
	$text .= ".\n\n";
	$text .= "You have " . $unread . " unread message" . ($unread == 1 ? "" : "s") . " out of a total of " . $all . " message" . ($all == 1 ? "" : "s") . " in your inbox.\n";
	$text .= "To view your inbox, login to [1] and click on \n";
	$text .= "the inbox icon, on the top of the page.\n";
	$text .= "\n";
	$text .= "Best regards,\n";
	$text .= "The AppDB team\n";
	
	$body = preg_replace("/\[1\]/","<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"]."' target='_blank'>EGI Applications Database</a>", $text);
	$body = "<html><head></head><body><pre>" . $body . "</pre></body></html>";
	
	$text = preg_replace("/\[1\]/", "EGI Applications Database [1]",$text);
	$text .= "\n\n________________________________________________________________________________________________________\n";
	$text .= "[1]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"];
	
	//sendMultipartMail($subject,$recipients,$text,$body,'appdb-reports@iasa.gr','enadyskolopassword');
	EmailService::sendReport($subject, $recipients, $text, $body);
}

function compareReleaseDates($a, $b){
	return strcmp($b['publishedon'], $a['publishedon']);
}

class SEO{
	private static $loaded = array();
	private static function getPagesContentData($uri){
		$res = array();
		$cname = explode("/",$uri);
		
		if( count($cname) > 0 && trim($cname[0]) == "" ){
			array_splice($cname,0,1);
		}
		
		if( count($cname) > 0 && (trim($cname[count($cname)-1]) == "" || trim($cname[count($cname)-1]) == "?" || trim($cname[count($cname)-1]) == "#") ){
			array_splice($cname,count($cname)-1,1);
		}
		
		if( count($cname) > 0 && strpos("?",$cname[count($cname)-1]) !== false  ){
			$last = explode("?", $cname[count($cname)-1]);
			if( count($last) > 1 ){
				$cname[count($cname)-1] = $last[0];
			}
		}
		
		if( count($cname) >= 3 ){
			switch($cname[1]){ // about or statistics
				case "about":
					switch($cname[2]){
						case "usage":
							$res = array(
								"meta" => array(
									"title" => "Usage",
									"description" => "EGI Application Database Usage"
								),
								"link" => array(
									"canonical" => "https://" . $_SERVER["SERVER_NAME"] ."/pages/about/usage"
								)
							);
							break;
						case "announcements":
							$res = array(
								"meta" => array(
									"title" => "Announcements",
									"description" => "EGI Application Database Announcements"
								),
								"link" => array(
									"canonical" => "https://" . $_SERVER["SERVER_NAME"] ."/pages/about/announcements"
								)
							);
							break;
						case "faq":
							$faq = null;
							if( isset($cname[3]) && is_numeric($cname[3]) ){
								$faqs = new Default_Model_FAQs();
								$faqs->filter->id->equals($cname[3]);
								if( count($faqs->items) > 0 ){
									$faq = $faqs->items[0];
								}
							}
							if( $faq ) {
								$res = array(
									"meta" => array(
										"title" => "FAQ - " . $faq->question,
										"description" => $faq->question . ": " . $faq->answer
									),
									"link" => array(
										"canonical" => "https://" . $_SERVER["SERVER_NAME"] ."/pages/about/faq/" . $cname[3]
									)
								);
							}else{
								$res = array(
									"meta" => array(
										"title" => "FAQ",
										"description" => "EGI Application Database Frequently Asked Questions"
									),
									"link" => array(
										"canonical" => "https://" . $_SERVER["SERVER_NAME"] ."/pages/about/faq"
									)
								);
							}
							break;
						case "credits":
							$res = array(
								"meta" => array(
									"title" => "Credits",
									"description" => "EGI Application Database Credits"
								),
								"link" => array(
									"canonical" => "https://" . $_SERVER["SERVER_NAME"] ."/pages/about/credits"
								)
							);
							break;
						case "changelog":
							$res = array(
								"meta" => array(
									"title" => "Changelog",
									"description" => "EGI Application Database Changelog"
								),
								"link" => array(
									"canonical" => "https://" . $_SERVER["SERVER_NAME"] ."/pages/about/changelog"
								)
							);
							break;
						case "latestfeatures":
							$res = array(
								"meta" => array(
									"title" => "Latest Features",
									"description" => "EGI Application Database Latest Features"
								),
								"link" => array(
									"canonical" => "https://" . $_SERVER["SERVER_NAME"] ."/pages/about/features"
								)
							);
							break;
						default: 
							break;
					}
					break;
				case "statistics":
					if(count($cname) > 3 ){ //statistics needs 4 url sections
						switch($cname[2]){
							case "software":
								switch($cname[3]){
									case "discipline":
										$res = array(
											"meta" => array(
												"title" => "Software Statistics per Discipline",
												"description" => "EGI Application Database Software Statistics per Discipline"
											),
											"link" => array(
												"canonical" => "https://" . $_SERVER["SERVER_NAME"] ."/pages/statistics/software/discipline"
											)
										);
										break;
									case "category":
										$res = array(
											"meta" => array(
												"title" => "Software Statistics per Category",
												"description" => "EGI Application Database Software Statistics per Category"
											),
											"link" => array(
												"canonical" => "https://" . $_SERVER["SERVER_NAME"] ."/pages/statistics/software/category"
											)
										);
										break;
									case "vo":
										$res = array(
											"meta" => array(
												"title" => "Software Statistics per Virtual Organization",
												"description" => "EGI Application Database Software Statistics per Virtual Organization"
											),
											"link" => array(
												"canonical" => "https://" . $_SERVER["SERVER_NAME"] ."/pages/statistics/software/vo"
											)
										);
										break;
									default: 
										break;
								}
								break;
							case "vappliance":
								switch($cname[3]){
									case "discipline":
										$res = array(
											"meta" => array(
												"title" => "Virtual Appliance Statistics per Discipline",
												"description" => "EGI Application Database Virtual Appliance Statistics per Discipline"
											),
											"link" => array(
												"canonical" => "https://" . $_SERVER["SERVER_NAME"] ."/pages/statistics/vappliance/discipline"
											)
										);
										break;
									case "category":
										$res = array(
											"meta" => array(
												"title" => "Virtual Appliance Statistics per Category",
												"description" => "EGI Application Database Virtual Appliance Statistics per Category"
											),
											"link" => array(
												"canonical" => "https://" . $_SERVER["SERVER_NAME"] ."/pages/statistics/vappliance/category"
											)
										);
										break;
									default:
										break;
								}
								break;
							case "people":
								switch($cname[3]){
									case "country":
										$res = array(
											"meta" => array(
												"title" => "People Statistics per Country",
												"description" => "EGI Application Database People Statistics per Country"
											),
											"link" => array(
												"canonical" => "https://" . $_SERVER["SERVER_NAME"] ."/pages/statistics/people/country"
											)
										);
										break;
									case "position":
										$res = array(
											"meta" => array(
												"title" => "People Statistics per Scientific Orientation",
												"description" => "EGI Application Database People Statistics per Scientific Orientation"
											),
											"link" => array(
												"canonical" => "https://" . $_SERVER["SERVER_NAME"] ."/pages/statistics/people/position"
											)
										);
										break;
									default:
										break;
								}
								break;
						}
					}
					break;
				default:
					break;
			}
		}
		
		return $res;
	}
	private static function getDataItem($type=null,$cname=null){
		if( $type===null || $cname===null){
			$type = self::getContentType();
			$cname = self::getCName();
		}
		$classname = null;
		switch( strtolower($type) ){
			case "software":
			case "vappliance":
				$classname = new Default_Model_Applications();
				break;
			case "person":
				$classname = new Default_Model_Researchers();
				break;
			default:
				return null;
		}
		
		if( $classname ){
			$classname->filter->cname->equals($cname);
			if( in_array( strtolower($type), array("software","people","vappliance","swappliance") ) === true ){
				$classname->viewModerated = false;
			}
			
			if( count($classname->items) == 0 ){
				return null;
			}
			$item = $classname->items[0];
			return $item;
		}
		return null;
	}
	private static function getContentData(){
		$type = self::getContentType();
		$cname = self::getCName();
		$item = null;
		$res = array("meta"=>array(),"link"=>array());
		
		switch( strtolower($type) ){
			case "software":
			case "person":
			case "vappliance":
			case "swappliance":
				$item = self::getDataItem($type,$cname);
				break;
			case "home":
				$res = array(
					"meta" => array(
						"title" => "",
						"description" => "The EGI Applications Database (AppDB) is a central service that stores and provides to the public, information about:1.tailor-made software tools for scientists and developers to use,2.the programmers and the scientists who developed them, 3.and the publications derived from the registered software items.All software filed in the AppDB is ready to be used on the European Grid Infrastructure. Reusing software from the AppDB means that scientists and software developers can use custom code on the European Grid Infrastructures without reinventing the wheel. This way, scientists can spend less time porting software to Distributed Computing Infrastrures (DCIs), and software developers can create scientific code more easily. Thus, AppDB aims to avoid duplication of effort across the DCI communities, and aims to inspire scientists less familiar with DCI programming.",
						"author" => "EGI.eu, IASA"
					),
					"link" => array(
						"canonical" => "https://" . $_SERVER["SERVER_NAME"]
					)
				);
				break;
			case "pages":
				return self::getPagesContentData($_SERVER['REQUEST_URI']);
			default:
				return $res;
		}
		if(!$item ){ 
			return $res; 
		}
		switch( strtolower($type) ){
			case "software":
			case "vappliance":
				$keywords = array($item->name, $item->getPrimaryCategory()->name);
				if( strtolower($type) === "vappliance" ){
					$vcats = $item->getCategories();
					foreach($vcats as $vcat){
						$cat = $vcat->getCategory();
						if( is_null($cat) === false && trim($vcat->isPrimary) === "false" ){
							$keywords[] = $cat->getName();
						}
					}
				}
				$tags = new Default_Model_AppTags();
				$tags->filter->appid->equals($item->id);
				if( count($tags->items) > 0 ){
					foreach($tags->items as $tag){
						$keywords[] = $tag->tag;
					}
				}
				$owner = $item->getOwner();
				if( $owner ){
					$authors = array( $item->getOwner()->getFullName() );
				}else{
					$authors = array();
				}
				$researchers = $item->getResearchers();
				if( count($researchers) > 0 ){
					foreach($researchers as $researcher){
						$authors[] = $researcher->getFullName();
					}
				}
				$authors = array_unique($authors);
				$fbsalt =  time().rand(5, 15000);
				$contentText = "Software";
				if( strtolower($type) === "vappliance" ){
					$contentText = "Virtual Appliance";
				}
				$res = array( 
					"meta" => array(
						"title" => $contentText . " " . $item->name,
						"description" => (($item->description!=$item->name)?$item->description:$item->abstract), 
						"abstract" => $item->abstract,
						"keywords"=> $keywords, 
						"author" => $authors,
						"og:image" => "http://" . $_SERVER["SERVER_NAME"] ."/apps/getfblogo?id=" . $item->id . "_" . $fbsalt,
						"og:image:secure_url" => "https://" . $_SERVER["SERVER_NAME"] ."/apps/getfblogo?id=" . $item->id . "_" . $fbsalt,
						"og:image:type" => "image/png",
						"og:image:width" => "210",
						"og:image:height" => "210"
					),
					"link" => array(
						"canonical" => "https://" . $_SERVER["SERVER_NAME"] . "/store/".strtolower($type)."/" . $item->cname
					)
				);
				break;
			case "person":
				break;
			case "pages":
				break;
			default:
				break;
		}
		
		$res["type"] = $type;
		$res["cname"] = $cname;
		//Store this for use in body meta tags with SEO::getBodyMetaTags function
		if( $item ){ 
			$res["data"] = $item;
		}
		return $res;
	}
	private static function getContentType(){
		$uri = $_SERVER['REQUEST_URI'];
		if( strpos($uri, '/store/software/') !== false ) {
			return "software";
		}else if( strpos($uri, '/store/vappliance/') !== false ) {
			return "vappliance";
		}else if( strpos($uri, '/store/swappliance/') !== false ) {
			return "swappliance";
		}else if ( strpos($uri, '/store/person/') !== false ){
			return "person";
		}else if ( strpos($uri, '/pages/') !== false){
			return "pages";
		}
		return "home";
	}
	private static function getCName(){
		$uri = trim($_SERVER['REQUEST_URI']);
		if( $uri === "" || $uri === "/" ){
			return "home";
		}
		$cname = explode("/", $uri);
		if( count($cname) > 3 ){
			$cname = $cname[3];
		}else if( count($cname) > 0 ){
			$cname = $cname[count($cname)-1];
		}else{
			$cname = "";
		}
		if( strpos($cname, '?') !== false ){
			$cname = explode("?", $cname);
			$cname = $cname[0];
		}
		$cname = strtolower($cname);
		return $cname; 
	}
	private static function getDefaultMetaTags(){
		return array(
			"distribution" => "global", 
			"web_author" => "EGI.eu, IASA",
			"no-email-collection"=>"",
			"rating" => "safe for kids",
			"revisit" => "3 days");
	}
	public static function getHeaderTags(){
		if( empty(self::$loaded) ){
			self::$loaded = self::getContentData();
		}
		$data = self::$loaded;
		
		$tags = "";
		$MAXCHARS = 500;
		/*Open graph meta elements */
		if( isset($data["link"]) && isset($data["link"]["canonical"])){
			if( isset($data["meta"]) ){
				$data["meta"]["og:url"] = $data["link"]["canonical"];
			}
		}
		foreach($data as $key=>$val){
			if( $key == "meta" && is_array($val) ){
				$val = array_merge(self::getDefaultMetaTags(),$val);
				if( isset($val["title"])){
					if( trim($val["title"]) != "" ) {
						$val["title"] = htmlentities($val["title"], ENT_QUOTES, "UTF-8") . " | EGI Applications Database ";
					}else{
						$val["title"] = "EGI Applications Database";
					}
				}
				if( isset($val["title"]) ){
					$tags .= "\n<title>" . htmlentities($val["title"], ENT_QUOTES, "UTF-8")  . "</title>";
					$val["og:title"] = $val["title"];
				}else if ( $_SERVER["APPLICATION_UI_HOSTNAME"] === "appdb.egi.eu" ){
					$tags .= "\n<title>EGI Applications Database</title>";
				} else {
					$tags .= "\n<title>EGI AppDB (DEV/pgSQL)</title>"; 
				}
				/*Open graph meta elements */
				$val["og:type"] = "website";
				if( isset($val["description"])){
					$val["og:description"] = $val["description"];
				}
				if( isset($val["og:image"]) === false){
					$val["og:image"] = "http://" . $_SERVER["SERVER_NAME"] ."/images/appdb_logo_moto.png";
				}
				if( isset($val["og:image:secure_url"]) === false ){
					$val["og:image:secure_url"] = "https://" . $_SERVER["SERVER_NAME"] ."/images/appdb_logo_moto.png";
				}
				
				foreach($val as $k=>$v){
					//if( $k == "title") $v = $v . " | EGI Applications Database ";
					if( strpos($k,"og:") === 0 ){
						$tags .= "\n<meta property='" . $k . "' ";
					}else{
						$tags .= "\n<meta name='" . $k . "' ";
					}
					if( !$v ){
						$tags .= " >";
						continue;
					} 
					$tags .= " content='" ;
					if (is_array($v) ){
						$tags .= substr(htmlentities(implode(",", $v) , ENT_QUOTES, "UTF-8"), 0, $MAXCHARS);
					} else {
						$tags .= substr(htmlentities($v, ENT_QUOTES, "UTF-8"), 0, $MAXCHARS);
					}
					$tags .= "' >";
				}
				
			}else if( $key == "link" ){
				foreach($val as $k=>$v){
					$tags .= "\n<link rel='" . $k . "' ";
					if( !$v ){
						$tags .= " >";
						continue;
					} 
					$tags .= " href='" ;
					if (is_array($v) ){
						$tags .= substr(htmlentities(implode(",", $v) , ENT_QUOTES, "UTF-8"),0 ,$MAXCHARS);
					} else {
						$tags .= substr(htmlentities($v, ENT_QUOTES, "UTF-8"), 0, $MAXCHARS);
					}
					$tags .= "' >";
				}
			}
		}
		if( isset($data["meta"]) && isset($data["meta"]["og:image"]) ){
			$tags .= "<link rel='image_src' href='".$data["meta"]["og:image"]."' />";
		}
		return $tags;
	}
	public static function isBot(){
		 $bots = array(
			'Googlebot', 'Baiduspider', 'ia_archiver',
			'R6_FeedFetcher', 'NetcraftSurveyAgent', 'Sogou web spider',
			'bingbot', 'Yahoo! Slurp', 'facebookexternalhit', 'PrintfulBot',
			'msnbot', 'Twitterbot', 'UnwindFetchor',
			'urlresolver', 'Butterfly', 'TweetmemeBot' 
		);
		foreach($bots as $b){
			if( stripos( $_SERVER['HTTP_USER_AGENT'], $b ) !== false ) return true;
		}
		return false;
	}
	public static function getBodyMetaTags(){
		if( empty(self::$loaded) ){
			self::$loaded = self::getContentData();
		}
		if( empty(self::$loaded) || isset(self::$loaded["data"]) == false || isset(self::$loaded["type"]) == false || (self::$loaded["type"]=="software" || self::$loaded["type"]=="vappliance")===false ) return "";
		
		$data = SEO::$loaded["data"];
		$meta = SEO::$loaded["meta"];
		$link = SEO::$loaded["link"];
		$res = '<noscript>';
		$res .= '<style>.noscript.softwareentry {display: block;width: 1000px;}.noscript.softwareentry .field {display: block;padding-bottom: 0.5em;padding-left: 10em;width: auto;text-align:left;}';
		$res .= '.noscript.softwareentry .field .fieldtype {color: #444444;display: inline-block;font-weight: bold;min-width: 90px;vertical-align: top;width: 90px;}';
		$res .= '.noscript.softwareentry .field .fieldvalue {display: inline-block;max-width: 780px;text-align:left;}.noscript.softwareentry .field.image {height: 100px;left: -110px;position: absolute;top: 0;width: 100px;.height: 100px;border:none;}';
		$res .= '</style>';		
		$res .= '<div class="noscript softwareentry" itemscope itemtype="https://schema.org/SoftwareApplication">';
		$res .= '<div class="field name"><span class="fieldtype">Name:</span><span class="fieldvalue" itemprop="name">' . htmlentities($data->name, ENT_QUOTES, "UTF-8") . '</span></div>';
		$res .= '<div class="field description"><span class="fieldtype">Description:</span><span class="fieldvalue" itemprop="description">' . $meta["description"]. '</span></div>';
		$res .= '<div class="field abstract"><span class="fieldtype">Abstract:</span><span class="fieldvalue" itemprop="text">' . htmlentities($data->abstract, ENT_QUOTES, "UTF-8") . '</span></div>';
		$res .= '<img class="field image" alt="' . htmlentities($data->name, ENT_QUOTES, "UTF-8") . ' logo" src="https://' . $_SERVER["SERVER_NAME"] . '/apps/getlogo?id=' . $data->id . '" width="100px" itemprop="image thumbnailUrl" />';
		$res .= '<div class="field canonical"><span class="fieldtype">Url:</span><span class="fieldvalue"><a href="' .$link["canonical"] . '" itemprop="url" >' .$link["canonical"] . '</a></span></div>';
		if( $data->dateAdded ){
			$res .= '<div class="field datecreated"><span class="fieldtype">Created:</span><span class="fieldvalue" itemprop="dateCreated">' . date('Y-m-d',strtotime($data->dateAdded)) . '</span></div>';
		}
		if( $data->lastUpdated ){
			$res .= '<div class="field lastupdated"><span class="fieldtype">Last updated:</span><span class="fieldvalue" itemprop="dateModified">' . date('Y-m-d',strtotime($data->lastUpdated)) . '</span></div>';
		}
		if( $data->rating ){
			$res .= '<div itemscope itemprop="aggregateRating" itemtype="https://schema.org/AggregateRating">';
			$res .= '<meta itemprop="bestRating" content="5">';
			$res .= '<meta itemprop="worstRating" content="0">';
			$res .= '<div class="field ratingvotes">';
			$res .= '<span class="fieldtype">Rating votes:</span>';
			$res .= '<span class="fieldvalue"><span itemprop="ratingCount">' . $data->getRatingcount()  . '</span></span>';
			$res .= '</div>';
			$res .= '<div class="field rating">';
			$res .= '<span class="fieldtype">Rating score:</span>';
			$res .= '<span class="fieldvalue"><span itemprop="ratingValue">' . $data->getRating() . '</span><span> / 5 </span></span>';
			$res .= '</div></div>';
		}
		$res .= '</div>';
		$res .= '</noscript>';
		return $res;
	}
	private static function getStaticSiteMapEntries($options = array()){
		$filename = "../public/staticsitemap";
		$now = date('Y-m-d', time());
		$res = "";
		
		if( file_exists($filename) != true ){
			return "";
		}
		
		$lines = file($filename);
		foreach($lines as $line){
			if( substr($line, strlen($line)-1,1) == PHP_EOL ){
				$line = substr($line, 0, strlen($line)-1);
			}
			$res .= " <url>\n";
			$res .= "  <loc>". htmlentities("https://" . $_SERVER["SERVER_NAME"] . $line,ENT_QUOTES, "UTF-8") . "</loc>\n";
			$res .= "  <lastmod>" . $now . "</lastmod>\n";
			$res .= "  <changefreq>weekly</changefreq>\n";
			$res .= " </url>\n";
		}
		return $res;
	}
	private static function getApplicationSiteMapEntry($app,$options=array("frequency"=>"weekly")){
		$now = date('Y-m-d', strtotime($app->lastupdated) );
		$contenttype = "software";
		if( trim($app->metatype) === "1"){
			$contenttype = "vappliance";
		}else if( trim($app->metatype) === "2"){
			$contenttype = "swappliance";
		}
		$res = " <url>\n";
		$res .= "  <loc>". htmlentities("https://" . $_SERVER["SERVER_NAME"] . "/store/".$contenttype."/". $app->cname ,ENT_QUOTES, "UTF-8") . "</loc>\n";
		$res .= "  <lastmod>" . $now . "</lastmod>\n";
		$res .= "  <changefreq>" . $options["frequency"]. "</changefreq>\n";
		$res .= " </url>\n";
		return $res;
	}
	private static function generateSiteMapIndexFile(){
		$filename = "../public/sitemapindex.xml.temp";
		$targetfilename = "../public/sitemapindex.xml";
		
		if( file_exists($filename) ){
			@unlink($filename);
		}
		$fp = fopen($filename, 'w');
		if( !$fp ){
			return;
		}
		$xml = '<' . '?xml version="1.0" encoding="UTF-8"?' . '>';
		$xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		fwrite($fp, $xml);
		fwrite($fp, "<sitemap>");
		fwrite($fp, "<loc>" . htmlentities("https://" . $_SERVER["SERVER_NAME"] . "/sitemap.xml", ENT_QUOTES, "UTF-8") . "</loc>");
		fwrite($fp, "<lastmod>" .  date('Y-m-d', time()) . "</lastmod>");
		fwrite($fp, "</sitemap>");
		fwrite($fp, "</sitemapindex>");
		fclose($fp);
		
		if( file_exists($targetfilename) ){
			unlink($targetfilename);
		}
		rename($filename, $targetfilename);
	}
	public static function generateSitemap($options = array("frequency"=>"daily")){
		$filename = "../public/sitemap.xml.temp";
		$targetfilename = "../public/sitemap.xml";
		
		if( file_exists($filename) ){
			@unlink($filename);
		}
		$fp = fopen($filename, 'w');
		if( !$fp ){
			return;
		}
		$xml = '<' . '?xml version="1.0" encoding="UTF-8"?' . '>';
		$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		$xml .= self::getStaticSiteMapEntries($options);
		fwrite($fp, $xml);
		
		$apps = new Default_Model_Applications();
		foreach($apps->items as $app){
			fwrite($fp,self::getApplicationSiteMapEntry($app, $options));
		}
		
		fwrite($fp, '</urlset>');
		fclose($fp);
		
		if( file_exists($targetfilename) ){
			unlink($targetfilename);
		}
		rename($filename, $targetfilename);
		self::generateSiteMapIndexFile();
	}
}

function fixuZenduBuguru($s) {
	return preg_replace('/ AS "(\w+\.){0,1}\w+\.any_2"/', '', $s);
}

class VMCaster{
	private static $vmcasterurl = null;
	public static function getVMCasterUrl(){
		if( VMCaster::$vmcasterurl === null ){
			require_once('Zend/Config/Ini.php');
			$conf = new Zend_Config_Ini(__DIR__ . '/../application/configs/application.ini', $_SERVER['APPLICATION_ENV']);
			$appconf = $conf->app;
			VMCaster::$vmcasterurl = $appconf->vmcasterUrl;
		}
		return VMCaster::$vmcasterurl;
	}
	
	public static function transformXml($vmcxml = null, $apiversion = '1.0') {
		$envelop_start = '<appdb:appdb xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:appdb="http://appdb.egi.eu/api/1.0/appdb" xmlns:application="http://appdb.egi.eu/api/1.0/application" xmlns:discipline="http://appdb.egi.eu/api/1.0/discipline" xmlns:category="http://appdb.egi.eu/api/1.0/category" xmlns:dissemination="http://appdb.egi.eu/api/1.0/dissemination" '.
				'xmlns:person="http://appdb.egi.eu/api/' . $apiversion . '/person" '.
				'xmlns:permission="http://appdb.egi.eu/api/' . $apiversion . '/permission" '.
				'xmlns:privilege="http://appdb.egi.eu/api/' . $apiversion . '/privilege" '.
				'xmlns:user="http://appdb.egi.eu/api/' . $apiversion . '/user" '.
				'xmlns:virtualization="http://appdb.egi.eu/api/' . $apiversion . '/virtualization" '.
				'datatype="virtualization" version="' . $apiversion . '">';
		$envelop_end = '</appdb:appdb>';
		$result = '';
		
		if( $vmcxml !== null && trim($vmcxml) !== "" && !is_numeric($vmcxml) ) {
			if( strpos($vmcxml, "<?xml") !== 0 ) {
				$vmcxml = '<' . '?xml version="1.0" encoding="utf-8"?' . '>' . $vmcxml;
			}
			
			try {
				$xsl = new DOMDocument();
				$xsl->load("../application/configs/api/1.0/xslt/vmc2appdb_group.xsl");
				$inputdom = new DomDocument();
				$inputdom->loadXML($vmcxml);

				$proc = new XSLTProcessor();
				$proc->importStylesheet($xsl);
				$proc->setParameter(null, "", "");

				$grouped = $proc->transformToXml($inputdom);

				$xsl = new DOMDocument();
				$xsl->load("../application/configs/api/1.0/xslt/vmc2appdb.xsl");
				$inputdom = new DomDocument();
				$inputdom->loadXML($grouped);

				$proc = new XSLTProcessor();
				$proc->importStylesheet($xsl);
				$proc->setParameter(null, "", "");

				$result .= $proc->transformToXml($inputdom);
			} catch(Exception $e) {
				$result = '';
			}
		}
		
		return $envelop_start . $result . $envelop_end;
	}
	public static function createImageList($vaversionid, $target="published"){
		//Call vmcaster service to produce image list
		$url = VMCaster::getVMCasterUrl() . "/" . "vmlistcontroller/create/" . $vaversionid . "/".$target;
		$result = web_get_contents($url);
		if( $result === false ){
			error_log("[VAPP:VMCaster:createImageList]:Could not retrieve response data from " . $url);
			return "Could not retrieve response data from " . $url;
		}
		return true;
	}
	private static function parseIntegrityCheckResponse($response){
		if( is_string($response) && substr($response, 0, strlen('[ERROR]')) === '[ERROR]') {
			return array(
				"status"=> "error",
				"message" => substr($response, strlen('[ERROR]'))
			);
		}
		
		$result = array("id"=>null,"status"=>null,"message"=>null);
		$xml = new SimpleXMLElement($response);
		if( count($xml->xpath("./id")) > 0 ){
			$id = $xml->xpath("./id");
			$result["id"] = intval($id[0]);
		}
		if( count($xml->xpath("./status")) > 0 ){
			$status = $xml->xpath("./status");
			$result["status"] = strval($status[0]);
		}
		if( count($xml->xpath("./message")) > 0 ){
			$message = $xml->xpath("./message");
			$result["message"] = strval($message[0]);
		}
		$images = array();
		if( count($xml->xpath("//details/image")) > 0 ){
			$ximgs = $xml->xpath("//details/image");
			$immgcount = count($xml->xpath("//details/image"));
			
			for($i=0; $i<$immgcount; $i+=1){
				$ximage = $ximgs[$i];
				if( count($ximage->xpath("./id")) > 0 ){
					$image["id"] = $ximage->xpath("./id");
					$image["id"] = intval($image["id"][0]);
				}
				if( count($ximage->xpath("./status")) > 0 ){
					$image["status"] = $ximage->xpath("./status");
					$image["status"] = strval($image["status"][0]);
				}
				if( $image["status"] === "ignore" ){
					continue;
				}
				$image["http"] = array();
				if( count($ximage->xpath("./details/Server")) > 0 ){
					$image["http"]["server"] = $ximage->xpath("./details/Server");
					$image["http"]["server"] = strval($image["http"]["server"][0]);
				}
				if( count($ximage->xpath("./details/Date")) > 0 ){
					$image["http"]["date"] = $ximage->xpath("./details/Date");
					$image["http"]["date"] = strval($image["http"]["date"][0]);
				}
				if( count($ximage->xpath("./details/Content-Type")) > 0 ){
					$image["http"]["contenttype"] = $ximage->xpath("./details/Content-Type");
					$image["http"]["contenttype"] = strval($image["http"]["contenttype"][0]);
				}
				if( count($ximage->xpath("./details/Content-Length")) > 0 ){
					$image["http"]["contentlength"] = $ximage->xpath("./details/Content-Length");
					$image["http"]["contentlength"] = intval($image["http"]["contentlength"][0]);
				}
				if( count($ximage->xpath("./details/Last-Modified")) > 0 ){
					$image["http"]["lastmodified"] = $ximage->xpath("./details/Last-Modified");
					$image["http"]["lastmodified"] = strval($image["http"]["lastmodified"][0]);
				}
				if( count($ximage->xpath("./details/Connection")) > 0 ){
					$image["http"]["connection"] = $ximage->xpath("./details/Connection");
					$image["http"]["connection"] = strval($image["http"]["connection"][0]);
				}
				if( count($ximage->xpath("./details/Accept-Ranges")) > 0 ){
					$image["http"]["acceptranges"] = $ximage->xpath("./details/Accept-Ranges");
					$image["http"]["acceptranges"] = strval($image["http"]["acceptranges"][0]);
				}
				if( count($ximage->xpath("./details/Code")) > 0 ){
					$image["http"]["code"] = $ximage->xpath("./details/Code");
					$image["http"]["code"] = intval($image["http"]["code"][0]);
				}
				if( count($ximage->xpath("./details/message")) > 0 ){
					$image["http"]["message"] = $ximage->xpath("./details/message");
					$image["http"]["message"] = strval($image["http"]["message"][0]);
				}
				$image["process"] = array();
				if( count($ximage->xpath("./size")) > 0 ){
					$image["process"]["size"] = $ximage->xpath("./size");
					$image["process"]["size"] = strval($image["process"]["size"][0]);
				}
				if( count($ximage->xpath("./downloaded")) > 0 ){
					$image["process"]["downloaded"] = $ximage->xpath("./downloaded");
					$image["process"]["downloaded"] = strval($image["process"]["downloaded"][0]);
				}
				if( count($ximage->xpath("./downloaded_percentage")) > 0 ){
					$image["process"]["percentage"] = $ximage->xpath("./downloaded_percentage");
					$image["process"]["percentage"] = strval($image["process"]["percentage"][0]);
				}
				$images[] = $image;
			}
		}
		$result["images"] = $images;
		
		return $result;
	}

	//Update vmiinstance fields regarding integrity check state
	private static function updateVMInstanceIntegrity($vmiinstance) {
		//Check if given VMIInstance is a valid model instance
		if (is_null($vmiinstance) || is_numeric($vmiinstance->id) === false ||  $vmiinstance->id < 1) {
			error_log('[Globals::VMCaster::updateVMInstanceIntegrity] Invalid VMIInstance model instance given with id = ' . $vmiinstance->id);
			return false;
		}

		return self::dbUpdateIntegrityCheck($vmiinstance->id, $vmiinstance->autointegrity, $vmiinstance->integrityStatus, $vmiinstance->integrityMessage);
	}

	//Update only specific given fields in vmiinstances regarding integrity check state
	private static function dbUpdateIntegrityCheck($id, $autointegrity, $integrity_status = '', $integrity_message = '') {
		$integrityStatus = '';
		$integrityMessage = '';

		//Normalize integrity status value
		if (is_null($integrity_status)) {
			$integrityStatus = '';
		} else if (is_string($integrity_status)) {
			$integrityStatus = trim($integrity_status);
		} else {
			$integrityStatus = $integrity_status;
		}

		//Normalize integrity message value
		if (is_null($integrity_message)) {
			$integrityMessage = '';
		} else if (is_string($integrity_message)) {
			$integrityMessage = trim($integrity_message);
		} else {
			$integrityMessage = $integrity_message;
		}

		//If all given parameters are valid proceed with update
		if (is_numeric($id) && $id > 0 && is_bool($autointegrity) && is_string($integrityStatus) && is_string($integrityMessage)) {
			//Prepare sql update statement
			$sql = 'UPDATE vmiinstances SET autointegrity = ?, integrity_status = ?, integrity_message = ? WHERE vmiinstances.id = ' . $id;
			$vals = array($autointegrity, $integrityStatus, $integrityMessage);
			db()->beginTransaction();
			try{
				db()->query($sql, $vals)->fetchAll();
				db()->commit();
			} catch (Exception $ex) {
				db()->rollback();
				error_log("[Globals::VMCaster::dbUpdateIntegrityCheck] " . $ex->getMessage());
				return false;
			}
		} else {
			$err = "[Globals::VMCaster::dbUpdateIntegrityCheck] Invalid input parameters given";
			$err .= " id=" . $id;
			$err .= " autointegrity=" . $autointegrity;
			$err .= " integrity_status=" . $integrity_status;
			$err .= " integrity_message=" . $integrity_message;
			error_log($err);
			return false;
		}

		return true;
	}

	public static function clearIntegrityCheck($vaversionid){
		$vapplists = new Default_Model_VALists();
		$vapplists->filter->vappversionid->equals($vaversionid);
		if( count($vapplists->items) > 0 ){
			for($i=0; $i<count($vapplists->items); $i+=1){
				$vapplist = $vapplists->items[$i];
				$instance = $vapplist->getVMIinstance();
				if( $instance->autointegrity === true ){
					$instance->integrityStatus = "";
				}
				$instance->integrityMessage = "";
				self::updateVMInstanceIntegrity($instance);
			}
		}
		$vaversions = new Default_Model_VAversions();
		$vaversions->filter->id->equals($vaversionid);
		if( count($vaversions->items) > 0 ){
			$vaversion = $vaversions->items[0];
			$vaversion->status = "canceled";
			$vaversion->save();
		}
	}
	public static function needsIntegrityCheck($vaversionid){
		$valists = new Default_Model_VALists();
		$valists->filter->vappversionid->equals($vaversionid);
		if( count($valists->items) > 0 ){
			for( $i=0; $i<count($valists->items); $i+=1 ){
				$valist = $valists->items[$i];
				$inst = $valist->getVMIinstance();
				if( $inst !== null && $inst->autointegrity === true ){
					return true;
				}
			}
		}
		return false;
	}
	public static function startIntegrityCheck($vaversionid){
		$versions = new Default_Model_VAVersions();
		$versions->filter->id->equals($vaversionid);
		if( count($versions->items) === 0 ){
			return false;
		}
		$version = $versions->items[0];
		$prevstatus = $version->status;
		//first cancel any running integrity check for this version
		VMCaster::cancelIntegrityCheck($vaversionid);
		$version->status = $prevstatus;
		$version->save();
		
		if( !($version->published=== false && $version->archived === false && ($version->status=="verifing" || $version->status == "verifingpublish") && $version->enabled===true) ){
			return false;
		}
		
		$url = VMCaster::getVMCasterUrl() . "/integrity/checkimagelist/".$vaversionid."/xml";
		
		try{
			$xml = web_get_contents($url);
			if( trim($xml) === "" ){
				throw new Exception('Could not connect to integrity check service. Please, try again later.');
			}
			$result = VMCaster::parseIntegrityCheckResponse($xml);
		} catch( Exception $ex) {
			$result = VMCaster::parseIntegrityCheckResponse('[ERROR]' . $ex->getMessage());
			
			if( intval($vaversionid) > 0 ) {
				VMCaster::cancelIntegrityCheck($vaversionid);
			}
			
			return $result;
		}
		
		$allimagesfailed = true;
		if( $result["status"] !== "success" ){
			for($i=0; $i<count($result["images"]); $i+=1){
				$image = $result["images"][$i];
				if( $image["status"] === "error" ){
					$instances = new Default_Model_VMIinstances();
					$instances->filter->id->equals($image["id"]);
					if( count($instances->items) > 0 ) {
						$instance = $instances->items[0];
						if($instance->autointegrity == true ){
							$instance->integrityStatus = $image["status"];
						}else{
							$instance->integrityStatus = "warning";
						}
						$instance->integrityMessage = $image["http"]["message"];//"Server responded with code: " . $image["http"]["code"];
						self::updateVMInstanceIntegrity($instance);
					}
				}else{
					$allimagesfailed = false;
				}
			}
		}else{
			$allimagesfailed = false;
		}
		
		if( $allimagesfailed === true ){
			$version->status = 'failed';
		}else{
			//'verifing';
		}
		$needscheck = self::needsIntegrityCheck($version->id);
		if( $needscheck === false ){
			$version->status = 'verified';
		}else{
			if( $version->status === 'verify' ){
				$version->status = 'verifing';
			}
			if( $version->status === 'verifypublish' ){
				$version->status = 'verifingpublish';
			}
		}
		
		$version->save();
		return $result;
	}
	public static function cancelIntegrityCheck($vaversionid){
		//clear statuses
		VMCaster::clearIntegrityCheck($vaversionid);
		
		$url = VMCaster::getVMCasterUrl() . "/integrity/cancelimageList/".$vaversionid."/xml";
		$xml = web_get_contents($url);		
		if( trim($xml) === "" ) return false;
		$result = VMCaster::parseIntegrityCheckResponse($xml);
		return $result;
	}
	
	public static function statusIntegrityCheck($vaversionid){
		$url = VMCaster::getVMCasterUrl() . "/integrity/statusimageList/".$vaversionid."/xml";
		try{
			$xml = web_get_contents($url);		
			if( trim($xml) === "" ){
				throw new Exception('Could not connect with integrity check service. Please, try again later.');
			}
			$result = VMCaster::parseIntegrityCheckResponse($xml);
		} catch( Exception $ex) {
			$result = VMCaster::parseIntegrityCheckResponse('[ERROR]' . $ex->getMessage());
			return $result;
		}
		
		$newres = VMCaster::syncStatusIntegrityCheck($result);
		return $newres;
	}
	private static function getVerifiedResponse($version){
		$newimagelist = array();
		$images = array();
		$newimagelist["id"] = $version->id;
		if( $version->status === "failed" ){
			$newimagelist["status"] = "error";
		}else if( $version->status === "verified" ){
			$newimagelist["status"] = "success";
		}else if( $version->status === "verifing" ){
			$newimagelist["status"] = "running";
		}else {
			$newimagelist["status"] = $version->status;
		}
		$newimagelist["message"] = $newimagelist["status"];
		
		$vapplists = new Default_Model_VALists();
		$vapplists->filter->vappversionid->equals($version->id);
		if( count($vapplists->items) > 0 ){
			$isrunning = false;
			for($i=0; $i<count($vapplists->items); $i+=1){
				$vapplist = $vapplists->items[$i];
				$img = $vapplist->getVMIinstance();
				if( !$img || $img->integrityStatus == "" ) continue;
				if( $img->integrityStatus === "success" && $img->integrityMessage !== "current" ) continue;
				$image = array(
					"id" => $img->id, 
					"status" => $img->integrityStatus, 
					"message"=> $img->integrityMessage,
					"http"=>array(),
					"process"=>array("size" => $img->size,"downloaded"=>$img->size,"percentage"=>100));
				if( in_array($image["status"], array("downloading","checksuming"))){
					$image["status"] = "running";
				}
				if ($image["status"] === "success" ){
					$image["message"] = "success";
				}
				if( $image["status"] === "running"){
					$isrunning = true;
				}
				$images[] = $image;
			}
		}
		$newimagelist["images"] = $images;
		if( $isrunning === true ){
			$newimagelist["status"] = "running";
			$newimagelist["message"] = "running";
		}
		return $newimagelist;
	}
	private static function syncStatusIntegrityCheck($res){
		$tobepublished = false;
		if( !$res || (is_array($res) && ( !isset($res["images"]) || !isset($res["status"]) || !isset($res["id"]) ) ) ) return;
		$versions = new Default_Model_VAversions();
		$versions->filter->id->equals($res["id"]);
		if( count($versions->items) === 0 ){
			return $res;
		}
		$version = $versions->items[0];
		if( $version->status === "init" || $version->status === "verified" || $version->status === "ready"){
			return $res;
		}
		$images = $res["images"];
		$hasimageerrors = false;
		$successfulimages = array();
		$isrunning = false;
		for($i=0; $i<count($images); $i+=1){
			$image = $images[$i];
			$process = $image["process"];
			$instances = new Default_Model_VMIinstances();
			$instances->filter->id->equals($image["id"]);
			if( count($instances->items) === 0 ) continue;
			$instance = $instances->items[0];
			if( $instance->integrityStatus === "error" && $instance->autointegrity == true){
			}
			if( $instance->autointegrity == false || $image["status"] === "n/a" ) {
				continue;
			}
			
			switch($image["status"]){
				case "running":
					if( $process["percentage"] == 100 ){
						$instance->integrityStatus = "checksuming";
					}else{
						$instance->integrityStatus = "downloading";
					}
					$instance->integrityMessage = "current";
					$isrunning = true;
					break;
				case "success":
					$instance->integrityStatus = "success";
					$instance->integrityMessage = "current";
					$successfulimages[] = $instance->id;
					break;
				case "cancelled":
					$instance->integrityStatus = "canceled";
					$instance->integrityMessage = "";
					$hasimageerrors = true;
					break;
				case "error":
					if($instance->integrityStatus == "checksuming"){
						$instance->integrityMessage = "Error while calculating checksum";
					}else if($instance->integrityStatus == "downloading"){
						$instance->integrityMessage = "Error while downloading image";
					}else{
						$instance->integrityMessage = "Unknown error";
					}
					$instance->integrityStatus = "error";
					$hasimageerrors = true;
				default:
					continue;
			}
			self::updateVMInstanceIntegrity($instance);
		}
		if( $isrunning === true ){
			$res["status"] = "running";
			$res["message"] = "running";
		}
		switch($res["status"]){
			case "running":
				if( trim($version->status) !== "verifingpublish"){
					$version->status = "verifing";
				}
				break;
			case "success":
				if( $version->status === "verifing" || $version->status === "verifingpublish" ){
					if( $hasimageerrors === true ){
						$version->status = "failed";
					}else{
						if( $version->status === "verifingpublish" ){
							$tobepublished = true;
						}
						$version->status = "verified";
					}
				}
				break;
			case "canceled":
				if( $version->status === "verifing" || $version->status === "verifingpublish" ){
					$version->status = "canceled";
				}
				break;
			case "error":
				if( ($version->status === "verifing" || $version->status === "verifingpublish") && $isrunning === false){
					$version->status = "failed";
				}else if($isrunning === true){
					//"verifing";
				}
				break;
			default:
				return $res;
		}
		$version->save();
		if( in_array($version->status, array("canceled","failed","verified") ) === true ){
			for($i=0; $i<count($successfulimages); $i+=1){
				$instances = new Default_Model_VMIInstances();
				$instances->filter->id->equals($successfulimages[$i]);
				if( count($instances->items) > 0 ) {
					$img = $instances->items[0];
					if( $img->integrityStatus === "success"){
						$img->autointegrity = false;
						$img->integrityMessage = "current";
						self::updateVMInstanceIntegrity($img);
					}
				}
			}
		}
		if( $tobepublished === true ){
			self::publishVersion($version);
			$res["published"] = "true";
		}else if($version->published === true){
			$res["published"] = "true";
		}
		return $res;
	}
	private static function publishVersion($version){
		$vaversions = new Default_Model_VAversions();
		$f = $vaversions->filter;
		$f->vappid->equals($version->vappid)->and($f->published->equals(true)->and($f->archived->equals(false)->and($f->id->notequals($version->id))));
		if( count( $vaversions->items ) > 0 ) {
			$latestversion = $vaversions->items[0];
			$latestversion->archived = true;
			$latestversion->save();
		}
		$version->published = true;
		$version->status = "verified";
		$version->createdon = "now()";
		$version->save();
		
		VMCaster::createImageList($version->id, "published");
	}
	public static function deleteVersion($version){
		try{
			$vapplists = new Default_Model_VALists();
			$vapplists->filter->vappversionid->equals($version->id);
			if( count($vapplists->items) > 0 ){
				for($i=0; $i<count($vapplists->items); $i+=1){
					$vapplist = $vapplists->items[$i];
					self::deleteVALists($vapplist);
				}
			}
			$version->delete();
		}catch(Exception $e){
			return $e->getMessage();
		}
		return true;
	}
	private static function deleteVALists($item){
		$inst = $item->getVMIInstance();
		self::deleteVMIInstance($inst);
		$item->delete();
	}
	private static function deleteVMIInstance($item){
		$instances = new Default_Model_VMIInstances();
		$instances->filter->vmiflavourid->equals($item->vmiflavourid)->and($instances->filter->id->notequals($item->id));
		if( count($instances->items) === 0 ){
			self::deleteFlavour($item->getFlavour(),$item);
		}
		self::deleteContextScripts($item->id);
		$item->delete();
	}
	private static function deleteContextScripts($vmiinstanceid){
		$scriptids = array();
		$vmiscripts = new Default_Model_VMIinstanceContextScripts();
		$vmiscripts->filter->vmiinstanceid->numequals($vmiinstanceid);
		if( count($vmiscripts->items) > 0 ){
			foreach($vmiscripts->items as $item){
				$scriptids[] = $item->contextscriptid;
				$vmiscripts->remove($item);
			}
		}
		$scriptids = array_unique($scriptids);
		//check if the referenced scripts have relations
		//if no relation found remove them from db.
		foreach($scriptids as $id){
			$vmiscripts = new Default_Model_VMIinstanceContextScripts();
			$vmiscripts->filter->contextscriptid->numequals($id);
			if( count($vmiscripts->items) === 0 ){
				$scripts = new Default_Model_ContextScripts();
				$scripts->filter->id->numequals($id);
				if( count($scripts->items) > 0 ){
					$scripts->remove($scripts->items[0]);
				}
			}
		}
	}
	private static function deleteFlavour($item,$parent){
		$instances = new Default_Model_VMIflavours();
		$instances->filter->vmiid->equals($item->vmiid)->and($instances->filter->id->notequals($parent->id));
		if( count($instances->items) === 0 ){
			self::deleteVMI($item->getVMI());
			$item->delete();
		}
	}
	private static function deleteVMI($item, $parent){
		$item->delete();
	}
	
	private static function instanceInLatestVersion($instance){
		$version = $instance->getVAVersion();
		if( $version && $version->published === true && $version->archived === false && $version->enabled === true ){
			return $version;
		}
		return null;
	}
	private static function getInstanceContextScript($instance){
		$vmiscripts = new Default_Model_VMIinstanceContextScripts();
		$vmiscripts->filter->vmiinstanceid->numequals($instance->id);
		if( count($vmiscripts->items) > 0 ){
			$vmiscript = $vmiscripts->items[0];
			return $vmiscript->getContextScript();
		}
		return null;
	}
	public static function getImageInfoById($imageid,$identifier=null,$strict=false){
		if( $imageid !== null && !is_numeric($imageid)) return null;
		if( $identifier!==null && trim($identifier) === "") return null;
		
		//check if image with identifier exists
		$vmiinstances = new Default_Model_VMIinstances();
		if( $identifier !== null ){
			$vmiinstances->filter->id->equals($imageid)->and($vmiinstances->filter->guid->equals(trim($identifier)));
		}else{
			$vmiinstances->filter->id->equals($imageid);
		}
		if( count($vmiinstances->items) === 0 ) return null;
		
		$instance = $vmiinstances->items[0];
		$originalimageid = $instance->id;
		//Get good vmi instance id (same with up to date metadata)
		if( $strict === false ){
			db()->setFetchMode(Zend_Db::FETCH_BOTH);
			$res = db()->query("SELECT get_good_vmiinstanceid(?)", array($instance->id))->fetchAll();
			if (count($res) > 0) {
				$res = $res[0];
			}else{
				$res = null;
			}
			if (count($res) > 0) {
				$res = $res[0];
			}else{
				$res = null;
			}
			//if good instance id differs use that one
			if ($res && is_numeric($res) && intval($res) !== intval($instance->id)) {
				$originalimageid = $instance->id;
				$images = new Default_Model_VMIinstances();
				$images->filter->id->numequals(intval($res));
				if( count($images->items) > 0 ){
					$instance = $images->items[0];
				}
			}
		}
		
		$version = $instance->getVAVersion();
		if( $version === null )return null;
		$vapp = $version->getVa();
		if( $vapp === null ) return null;
		$result = array("va"=>$vapp,"version"=>$version,"image"=>$instance);
		if( $originalimageid !== $instance->id ){
			$result["requested_id"] = $originalimageid;
		}
		
		//Retrieve conetxt script associated with image (if any)
		$contextscript = self::getInstanceContextScript($instance);
		if( $contextscript !== null){
			$result["contextscript"] = $contextscript;
		}
		return $result;
	}
	public static function getImageInfoByIdentifier($identifier){
		
		//Check if parameters are valid
		if( trim($identifier) === "" ) return null;
		
		//check if image with identifier exists
		$instances = new Default_Model_VMIinstances();
		$instances->filter->guid->equals($identifier);
		if( count($instances->items) === 0 ) return null;
		
		//Retrieve virtual appliance of image
		$instance = $instances->items[0];
		$version = $instance->getVAVersion();
		if( $version === null )return null;
		$vapp = $version->getVa();
		if( $vapp === null ) return null;
		
		//Check latest version 
		$latest = $vapp->getLatestVersion();
		if( $latest !== null ){
			$image = $latest->getImageByIdentifier($identifier);
			if( $image !== null ){
				$result = array("va"=>$vapp,"version"=>$latest,"image"=>$image);
				//Retrieve conetxt script associated with image (if any)
				$contextscript = self::getInstanceContextScript($instance);
				if( $contextscript !== null){
					$result["contextscript"] = $contextscript;
				}
				return $result;
			}
		}
		
		//check previous versions
		$previous = $vapp->getArchivedVersions();
		if( $previous === null || count($previous) === 0 ) return null;
		
		for($i=0; $i<count($previous); $i+=1){
			$prev = $previous[$i];
			$image = $prev->getImageByIdentifier($identifier);
			if( $image === null )continue;
			$result = array("va"=>$vapp,"version"=>$prev,"image"=>$image);
			//Retrieve conetxt script associated with image (if any)
			$contextscript = self::getInstanceContextScript($instance);
			if( $contextscript !== null){
				$result["contextscript"] = $contextscript;
			}
			return $result;
		}
		
		return null;
	}
	private static function convertArrayToXML($data, &$xml){
		foreach($data as $key => $value) {
			if(is_array($value)) {
				if(!is_numeric($key)){
					$subnode = $xml->addChild("$key");
					self::convertArrayToXML($value, $subnode);
				}
				else{
					$subnode = $xml->addChild("item$key");
					self::convertArrayToXML($value, $subnode);
				}
			}
			else {
				$xml->addChild("$key", htmlspecialchars("$value",ENT_COMPAT,'UTF-8'));
			}
		}
	}
	public static function getSitesByVMI($guid,$id){
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$sql = "SELECT distinct sites.id AS site_id, 
				sites.name AS site_name, 
				sites.officialname AS site_officialname, 
				sites.portalurl AS site_portalurl, 
				sites.homeurl AS site_homeurl, 
				va_provider_images.mp_uri AS mp_uri, 
				vos.name AS vo_name, 
				vowide_image_lists.state AS voimagelist_state, 
				vowide_image_list_images.state AS voimage_state,
				va_providers.id AS service_id,
				va_providers.url AS service_url,
				va_providers.gocdb_url AS service_gocdb_url,
				va_providers.hostname AS service_hostname,
				va_providers.ngi AS service_ngi,
				va_provider_images.va_provider_image_id as occi_id,
				va_provider_endpoints.endpoint_url as occi_endpoint
			FROM sites
			INNER JOIN va_providers ON va_providers.sitename = sites.name
			INNER JOIN va_provider_images ON va_provider_images.va_provider_id = va_providers.id
			INNER JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
			LEFT OUTER JOIN vowide_image_list_images ON vowide_image_list_images.id = va_provider_images.vowide_vmiinstanceid
			LEFT OUTER JOIN vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id
			LEFT OUTER JOIN vos ON  vos.id = vowide_image_lists.void
			LEFT OUTER JOIN va_provider_endpoints ON va_provider_endpoints.va_provider_id = va_providers.id
			WHERE  (vaviews.vmiinstance_guid = ? 
			AND vaviews.vmiinstanceid = ?)
			AND (vowide_image_lists.state <> 'draft' OR vowide_image_lists.state is NULL)
			AND (va_providers.id = va_provider_images.va_provider_id)";
		$items = db()->query($sql,array($guid,$id))->fetchAll();
		$sites = array();
		foreach($items as $item){
			if( count($sites) === 0 || isset($sites[$item['site_name']]) === false ){
				$sites[$item['site_name']] = array(
					'id' => $item['site_id'],
					'name' => $item['site_name'],
					'officialname' => $item['site_officialname'],
					'url' => array(
						'portal' => $item['site_portalurl'],
						'home' => $item['site_homeurl']
					),
					'services' => array()
				);
			}
			$site = $sites[$item['site_name']];
			$services = $site['services'];
			$srvindex = -1;
			for($i=0; $i< count($services); $i+=1){
				$s = $services[$i];
				if( $s['id'] === $item['service_id'] ){
					$srvindex = $i;
					break;
				}
			}
			if( $srvindex  === -1 ){
				$service = array('id' => $item['service_id'],
					'hostname' => $item['service_hostname'],
					'url' => array(
						'default' => $item['service_url'],
						'gocdb' => $item['service_gocdb_url']
					),
					'ngi' => $item['service_ngi'],
					'vos' => array()
				);
			}else{
				$service = $services[$srvindex];
			}
			
			$vos = $service['vos'];
			$voindex = -1;
			for($i=0; $i< count($vos); $i+=1){
				$s = $vos[$i];
				if (array_key_exists("id", $s)) {
					if( $s['id'] === $item['service_id'] ){
						$voindex = $i;
						break;
					}
				}
			}
			
			if( $voindex === -1 ){
				$vo = array(
					'name' => (trim($item['vo_name'])===''?'none':$item['vo_name']),
					'imageliststate' => $item['voimagelist_state'],
					'imagestate' => $item['voimage_state'],
					'url' => array(
						'operations_portal' => 'http://operations-portal.egi.eu/vo/view/voname/' . strtolower(trim($item['vo_name']))
					),
					'occi' => array(
						'id' => $item['occi_id'],
						'endpoint' => $item['occi_endpoint']
					)
				);
				if( trim($item['vo_name']) === '' ) {
					unset($vo['imageliststate']);
					unset($vo['imagestate']);
					unset($vo['url']);
				}
			}else{
				$vo = $vos[$voindex];
			}
			
			if( $voindex > -1 ){
				$vos[$voindex] = $vo;
			}else{
				$vos[] = $vo;
			}
			
			$service['vos'] = $vos;
			
			if( $srvindex > -1 ){
				$services[$srvindex] = $service;
			}else{
				$services[] = $service;
			}
			$site['services'] = $services;
			$sites[$item['site_name']] = $site;
		}
		
		return $sites;
	}
	public static function convertImage($data, $format='xml'){
		$result = "";
		$img = $data["image"];
		$flavour = $img->getFlavour();
		$arch = $flavour->getArch();
		$os = $flavour->getOs();
		$ver = $data["version"];
		$vapp = $data["va"];
		$vo = ( isset($data["vo"])?$data["vo"]:null );
		$voimage = ( isset($data["voimage"])?$data["voimage"]:null );
		$voimagelist = ( isset($data["voimagelist"])?$data["voimagelist"]:null );
		$app = $vapp->getApplication();
		$addedby = $img->getAddedBy();
		$updatedby = $img->getLastUpdatedBy();
		$d = array(
			"id" => $img->id,
			"identifier" => $img->guid,
			"version" => $img->version,
			"url" => $img->uri,
			"size" => $img->size,
			"checksum" => array( "hash"=>$img->checksumFunc, "value"=>$img->checksum ),
			"arch" => array( "id"=>$arch->id, "name"=>$arch->name ),
			"os" => array( "id"=>$os->id,"family"=>$os->name, "version"=> $flavour->osversion ),
			"format" => $flavour->format,
			"hypervisor" => $flavour->getHypervisors(),
			"title" => $img->title,
			"notes" => $img->notes,
			"description" => $img->description,
			"cores" => array( "minimum" => $img->coreMinimum, "recommended" => $img->coreRecommend ),
			"ram" => array( "minimum" => $img->RAMminimum, "recommended" => $img->RAMrecommend ),
			"addedon" => str_replace("+00:00","Z",gmdate("c", strtotime($img->addedon))),
			"addedby" => array( "id" => $addedby->id, "cname" => $addedby->cname, "firstname" => $addedby->firstname, "lastname" => $addedby->lastname, "gender" => $addedby->gender, "permalink" =>  'https://'.$_SERVER['HTTP_HOST'].'/store/person/'.$addedby->cname),
			"published" => $ver->published,
			"archived" => $ver->archived,
			"vappliance" => array( "version" => $ver->version,),
			"application" => array( "id" => $app->id, "name" => $app->name, "cname" => $app->cname )
		);
		if( isset($d["hypervisor"]) ){
			if( is_array($d["hypervisor"]) ){
				$d["hypervisor"] = implode(",",$d["hypervisor"]);
			}
		}
		if( isset($data["id"]) ){
			$d["id"] = trim($data["id"]);
		}
		if( isset($data["requested_id"]) ){
			$d["requested_id"] = trim($data["requested_id"]);
		}
		if( isset($data["baseid"]) ){
			$d["baseid"] = trim($data["baseid"]);
		}
		if( isset($data["requested_baseid"]) ){
			$d["requested_baseid"] = trim($data["requested_baseid"]);
		}
		if( isset($data["identifier"]) ){
			$d["identifier"] = trim($data["identifier"]);
		}
		if( isset($data["baseidentifier"]) ){
			$d["baseidentifier"] = trim($data["baseidentifier"]);
		}
		if( isset($data["mpuri"]) ){
			$d["mpuri"] = trim($data["mpuri"]);
		}else{
			$d["mpuri"] = 'https://'.$_SERVER['HTTP_HOST'].'/store/vm/image/' .$img->guid . ':' . $img->id . '';
		}
		if( isset($data["basempuri"]) ){
			$d["basempuri"] = trim($data["basempuri"]);
		}
		if( isset($data["contextscript"]) &&  $data["contextscript"] !== null ){
			$cscript = $data["contextscript"];
			$d["contextscript"] = array("id"=> $cscript->id, "url" => $cscript->url, "hashtype" =>$cscript->checksumfunc, "checksum" => $cscript->checksum, "size"=>$cscript->size );
		}
		if( $vo !== null && $voimage !== null && $voimagelist !== null){
			$d["vo"] = array(
				"id"=>$vo->id, 
				"name"=> $vo->name, 
				"domain"=>$vo->domain->name, 
				"voimagelist"=> array("id"=>$voimagelist->id, "state"=>$voimagelist->state, "voimage"=> array("id"=>$voimage->id, "state"=>$voimage->state))
			);
		}
		if( trim($img->lastUpdatedOn)!== "" ){
			$d["lastupdatedon"] =  str_replace("+00:00","Z",gmdate("c", strtotime($img->lastUpdatedOn)));
			$d["lastupdatedby"] = array( "id" => $updatedby->id, "cname" => $updatedby->cname, "firstname" => $updatedby->firstname, "lastname" => $updatedby->lastname, "gender" => $updatedby->gender,"permalink" =>  'http://'.$_SERVER['HTTP_HOST'].'/store/person/'.$updatedby->cname );
		}
		if( trim($ver->createdon) !== "" ){
			$d["vappliance"]["createdon"] = str_replace("+00:00","Z",gmdate("c", strtotime($ver->createdon)));
		}
		if( trim($ver->archivedon) !== "" ){
			$d["vappliance"]["archivedon"] =  str_replace("+00:00","Z",gmdate("c", strtotime($ver->archivedon)));
		}
		if( trim($ver->expireson) !== "" ){
			$d["vappliance"]["expireson"] = str_replace("+00:00","Z",gmdate("c", strtotime($ver->expireson)));
		}
		$d["hypervisor"] = preg_replace("/[\\{\\}]/", "", $d["hypervisor"]);
		
		//Hide private data if needed
		if( isset($data["isprivateimage"]) && $data["isprivateimage"] === true && isset($data["canaccessprivate"]) && $data["canaccessprivate"] === false){
			$d["url"] = "";
			$d["checksum"] = "";
			$d["size"] = "";
		}
		
		if( isset($data["sites"]) ){
			$d["sites"] = array();
			foreach($data["sites"] as $site){
				$d["sites"][] = $site;
			}
		}
		if( $format === "xml" ){
			$d["published"] = ($d["published"] === true)?"true":"false";
			$d["archived"] = ($d["archived"] === true)?"true":"false";
			$xml = new SimpleXMLElement('<vmiinstance></vmiinstance>');
			self::convertArrayToXML($d, $xml);
			$result = $xml->asXML();
			$apiversion = "1.0";
			$result = substr($result, strpos($result, '?>') + 2);
			$result = '<?xml version="1.0" encoding="utf-8"?><appdb:appdb xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:appdb="http://appdb.egi.eu/api/1.0/appdb" xmlns:application="http://appdb.egi.eu/api/1.0/application" xmlns:discipline="http://appdb.egi.eu/api/1.0/discipline" xmlns:category="http://appdb.egi.eu/api/1.0/category" xmlns:dissemination="http://appdb.egi.eu/api/1.0/dissemination" '.
				'xmlns:person="http://appdb.egi.eu/api/' . $apiversion . '/person" '.
				'xmlns:virtualization="http://appdb.egi.eu/api/' . $apiversion . '/virtualization" '.
				'xmlns:site="http://appdb.egi.eu/api/' . $apiversion . '/site" '.
				'xmlns:siteservice="http://appdb.egi.eu/api/' . $apiversion . '/site" '.
				'xmlns:vo="http://appdb.egi.eu/api/' . $apiversion . '/vo" '.
				'datatype="virtualization" version="' . $apiversion . '">' . $result . '</appdb:appdb>';
			try {
				$xsl = new DOMDocument();
				$xsl->load("../application/configs/api/1.0/xslt/virtualization.image.xsl");
				$inputdom = new DomDocument();
				$inputdom->loadXML($result);

				$proc = new XSLTProcessor();
				$proc->importStylesheet($xsl);
				$proc->setParameter(null, "", "");

				$result = $proc->transformToXml($inputdom);
			}catch(Exception $e){
				return null;
			}
		}else if( $format === "json" ){ 
			$result = json_encode($d,JSON_HEX_TAG | JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES );
		}
		return $result;
	}
	
	public static function getExpiredVappliances(){
		$query = "SELECT DISTINCT applications.id, applications.cname, applications.name, applications.owner, applications.addedby FROM vaviews " +
			"INNER JOIN applications ON applications.id = vaviews.appid" + 
			"WHERE va_version_published = TRUE AND va_version_archived = FALSE AND va_version_expireson::date < now()::date";
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$res = db()->query($query)->fetchAll();
		return $res;
	}
	public static function vappliancesToBeExpired($days = 5){
		$query = "SELECT DISTINCT applications.id, applications.cname, applications.name, applications.owner, applications.addedby FROM vaviews " +
			"INNER JOIN applications ON applications.id = vaviews.appid" + 
			"WHERE va_version_published = TRUE AND va_version_archived = FALSE";
		$countdays = null;
		if( is_numeric($days) ){
			$countdays = intval($days);
		}
		
		if( $countdays !== null && $countdays > 0 ){
			$query .= " AND (va_version_expireson::date) = (now()::date + " . $countdays . ") and va_version_expireson > now()";
		}else {
			$query .= " AND (va_version_expireson::date) < (now()::date)";
		}
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$res = db()->query($query)->fetchAll();
		return $res;	
	}
	
	public static function cleararchivedvappversions($appid, $fromindex){
		if( !is_numeric($appid) || $appid <= 0 ) {
			return "Invalid vapplication id";
		}
		
		if( !is_numeric($fromindex) || $fromindex <= 0){
			return "Invalid index value";
		}
		
		if( $fromindex < 20 ){
			//Make sure it will never delete all of the vapp archived versions
			$fromindex = 20;
		}
		
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$res = db()->query("select vapp_old_archived_versions(?,?);", array($appid, $fromindex))->fetchAll();
		$result = array();
		if( count($res) > 0 ){
			foreach($res as $r){
				$vappversions = new Default_Model_VAversions();
				$vappversions->filter->id->equals($r[0]);
				if( count($vappversions->items) > 0 ){
					$vappversion = $vappversions->items[0];
					$deleted = VMCaster::deleteVersion($vappversion);
					if( $deleted !== true ){
						error_log("[VMCaster::cleararchivedvappversions]: " . $deleted);
					}else{
						$result[] = $vappversion->id;
					}
				}
			}
		}		
		return implode(",",$result);
	}
}
class VMCasterNotifications{
	private static function getMaxPastDays(){
		return 30;
	}
	/*
	 * Returns user's entry for given id or cname
	 */
	public static function getUser($user){
		if( $user === null || ( is_string($user) && trim($user) === "" ) ) {
			return null;
		} else if( is_numeric($user) ){
			$userid = intval($user);
			$users = new Default_Model_Researchers();
			$users->filter->id->equals($userid);
			if( count($users->items) === 0 ){
				return null;
			}
			$user = $users->items[0];
		} else if( is_string($user) && trim($user) !== "" ){
			$usercname = trim($user);
			$users = new Default_Model_Researchers();
			$users->filter->cname->equals($usercname);
			if( count($users->items) === 0 ){
				return null;
			}
			$user = $users->items[0];
		}
		return $user;
	}
	/*
	 * Returns users with va management permissions for vappliances 
	 * that will be expired in given days (days > 0)
	 * or has been expired for given days (days < 0)
	 * or expires today (days = 0 )
	 */
	public static function getExpirationData($days = 5){
		$query = 'SELECT DISTINCT researchers.id as id,
			researchers.name as name,
			researchers.cname as cname ,
			contacts.data as email,
			(\'[\'::text || string_agg(DISTINCT ((((((((\'{"id":"\'::text || applications.id::text) || \'"\'::text) || \',"cname":"\'::text) || replace(applications.cname, \'"\'::text, \'\\"\'::text)) || \'"\'::text) || \',"name":"\'::text) || replace(applications.name, \'"\'::text, \'\\"\'::text)) || \'"\'::text)   || \'}\'::text, \',\'::text)) || \']\'::text AS apps
		FROM 
			applications 
			INNER JOIN vaviews ON vaviews.appid = applications.id
			INNER JOIN researchers_apps ON researchers_apps.appid = applications.id
			INNER JOIN researchers ON researchers.id = researchers_apps.researcherid
			INNER JOIN permissions ON (permissions.object = applications.guid OR permissions.object is null)
			INNER JOIN contacts ON contacts.researcherid = researchers.id
		WHERE 
		permissions.actionid = 32 AND 
		permissions.actor = researchers.guid AND
		vaviews.va_version_published = TRUE AND 
		vaviews.va_version_archived = FALSE AND 
		contacts.isprimary = true AND 
		{{expireson}}
		GROUP BY researchers.id , contacts.data';
		
		$expireson = "";
		$qdays = intval(floor(abs($days)));
		if( $days > 0 ){
			$expireson = '(vaviews.va_version_expireson::date) = (now()::date + ' . $qdays . ') and vaviews.va_version_expireson > now()';
		}else if ( $days < 0){
			if( $qdays > VMCasterNotifications::getMaxPastDays()){
				$expireson = '(vaviews.va_version_expireson::date) < (now()::date - ' . VMCasterNotifications::getMaxPastDays() . ')';
			}else{
				$expireson = '(vaviews.va_version_expireson::date) = (now()::date - ' . $qdays . ')';
			}
			
		}else {
			$expireson = '(vaviews.va_version_expireson::date) = (now()::date)';
		}
		$q = preg_replace('/\{\{expireson\}\}/i', $expireson, $query);
		
		
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$res = db()->query($q)->fetchAll();
		
		return $res;
	}
	private static function getToBeExpiredMessage($notification){
		$qdays = intval(floor(abs($notification["days"])));
		$appindex = array();
		$user = $notification["user"];
		if( $qdays === 1){
			$subject = "[EGI APPDB] Virtual appliances expire tomorrow";
		}else{
			$subject = "[EGI APPDB] Virtual appliances will expire in " . $qdays . " days";
		}
		
		$message = "Dear " . $user["name"] . ",\n";
		if( $qdays === 1){
			$message .= "  the published versions of the following virtual appliances expire tomorrow.\n\n";
		}else{
			$message .= "  the published versions of the following virtual appliances will expire in " . $qdays . " days.\n\n";
		}
		
		foreach($notification["vappliances"] as $vapp){
			$appindex[] = $vapp;
			$message .= "    [" . count($appindex) . "]. " . $vapp["name"] . " \n";
		}
		$message .= "\n\nBest regards,\n";
		$message .= "AppDB team\n\n";
		$message .= "________________________________________________________________________________________________________\n";
		for($i=0; $i<count($appindex); $i+=1){
			$ap = $appindex[$i];
			$message .= "[" . ($i+1) . "] https://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "/store/vappliance/" . $ap["cname"] ."\n";
		}
		$notification["message"] = $message;
		$notification["subject"] = $subject;
		
		return $notification;
	}
	private static function getAlreadyExpiredMessage($notification){
		$qdays = intval(floor(abs($notification["days"])));
		$appindex = array();
		$user = $notification["user"];
		
		$message = "Dear " . $user["name"] . ",\n";
		if( $qdays > VMCasterNotifications::getMaxPastDays()){
			$subject = "[EGI APPDB] Virtual appliances expired more than " . VMCasterNotifications::getMaxPastDays() . " days ago";
			$message .= "  the published versions of the following virtual appliances expired more than " . VMCasterNotifications::getMaxPastDays() . " days.\n\n";
		}else{
			$subject = "[EGI APPDB] Virtual appliances expired " . $qdays . " days ago";
			$message .= "  the published versions of the following virtual appliances expired " . $qdays . " days ago.\n\n";
		}
		foreach($notification["vappliances"] as $vapp){
			$appindex[] = $vapp;
			$message .= "    [" . count($appindex) . "]. " . $vapp["name"] . " \n";
		}
		$message .= "\n\nBest regards,\n";
		$message .= "AppDB team\n\n";
		$message .= "________________________________________________________________________________________________________\n";
		for($i=0; $i<count($appindex); $i+=1){
			$ap = $appindex[$i];
			$message .= "[" . ($i+1) . "] https://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "/store/vappliance/" . $ap["cname"] ."\n";
		}
		$notification["message"] = $message;
		$notification["subject"] = $subject;
		
		return $notification;
	}
	private static function getExpiresMessage($notification){
		$appindex = array();
		$user = $notification["user"];
		$subject = "[EGI APPDB] Virtual appliances expire today";
		$message = "Dear " . $user["name"] . ",\n";
		$message .= "  the published versions of the following virtual appliances expire today.\n\n";
		foreach($notification["vappliances"] as $vapp){
			$appindex[] = $vapp;
			$message .= "    [" . count($appindex) . "]. " . $vapp["name"] . " \n";
		}
		$message .= "\n\nBest regards,\n";
		$message .= "AppDB team\n\n";
		$message .= "________________________________________________________________________________________________________\n";
		for($i=0; $i<count($appindex); $i+=1){
			$ap = $appindex[$i];
			$message .= "[" . ($i+1) . "] https://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "/store/vappliance/" . $ap["cname"] ."\n";
		}
		$notification["message"] = $message;
		$notification["subject"] = $subject;
		
		return $notification;
	}
	/*
	 * Creates a notification object for the given item.
	 * $item is an item of the list returned from 
	 * self::getExpirationData function
	 */
	public static function getExpirationNotification($item,$days = 5){
		$notification = array(
			"user"=>array("id"=>$item["id"], "name"=>$item["name"], "cname"=>$item["cname"]), 
			"subject"=>"", 
			"message"=>"",
			"days"=>$days,
			"recipient"=>$item["email"], 
			"vappliances" => array() 
		);
		if( trim($notification["recipient"]) === "" ){
			return null;
		}
		if( trim($item["id"]) === "" ){
			return null;
		}
		
		try{
			$apps =  trim($item["apps"]);
			if( $apps === "" ){
				return null;
			}
			$apps = json_decode($apps, true);
			if( $apps === null || count($apps) === 0){
				return null;
			}
			
			$notification["vappliances"] = $apps;
		}catch(Exception $ex){
			return null;
		}
		
		if( $days > 0 ){
			$notification = self::getToBeExpiredMessage($notification);
		}else if ( $days < 0 ){
			$notification = self::getAlreadyExpiredMessage($notification);
		}else {
			$notification = self::getExpiresMessage($notification);
		}
		$notification["message"] = "-- This is an automated message, please do not reply -- \n\n" . $notification["message"];
		return $notification;
	}
	private static function sendExpirationNotification($notification){
		$subject = $notification["subject"];
		$to = array($notification["recipient"]);
		$txtbody = $notification["message"];
		if ( ApplicationConfiguration::isProductionInstance() === FALSE ) {
			self::debugSendMultipart($subject, $to, $txtbody, null, "appdb reports username", "appdb reports password", false, null, false, null);
		} else {
			//sendMultipartMail($subject, $to, $txtbody, null, 'appdb-reports@iasa.gr', 'enadyskolopassword', false, null, false, array("Precedence"=>"bulk"));
			EmailService::sendBulkReport($subject, $to, $txtbody);
		}
	}
	private static function debugSendMultipart($subject, $to, $txtbody='', $htmlbody='', $username, $password, $replyto = false, $attachment = null, $cc = false, $ext = null){
		error_log("SENDING EXPIRATION NOTIFICATION: ");
		error_log("TO: " . implode(",", $to));
		error_log("REPLY_TO: " . $replyto);
		error_log("SUBJECT: " . $subject);
		error_log("MESSAGE: " . $txtbody);
	}
	public static function getExpirationNotificationList($days = 5){
		$data = self::getExpirationData($days);
		$res = array();
		foreach($data as $d){
			$notification = self::getExpirationNotification($d, $days);
			if( $notification !== null ){
				$res[] = $notification;
			}
		}
		return $res;
	}
	
	public static function sendExpirationNotificationList($days = 5){
		$data = self::getExpirationData($days);
		$res = array();
		foreach($data as $d){
			$notification = self::getExpirationNotification($d, $days);
			if( $notification !== null ){
				self::sendExpirationNotification($notification);
			}
		}
		return $res;
	}
}

/*
 * Stores va version state transition.
 * Created by RestAppVAXMLParser::parseVAppVersion 
 * which can set previous and current state of version.
 */
class VApplianceVersionState {
	private $oldData = array();
	private $newData = array();
	private $instances = null;
	private $needcheck = null;
	
	function __construct(Default_Model_VAversion $olddata=null, Default_Model_VAversion $newdata=null){
		$this->oldData = $olddata;
		if( $newdata !== null ){
			$this->newData = $newdata;
		}
	}
	public function isNewVersion(){
		if( !is_numeric($this->oldData->id) || intval($this->oldData->id) <= 0 ){
			return true;
		}
		return false;
	}
	public function setVersionNewState($newdata){
		$this->newData = $newdata;
		if( $this->newData->enabled === null ){
			$this->newData->enabled = true;
		}
		if( $this->newData->published === null ){
			$this->newData->published = false;
		}
		if( $this->newData->archived === null ){
			$this->newData->archived = false;
		}
		if( $this->newData->status === null ){
			$this->newData->status = 'init';
		}
		return $this->validate();
	}
	public function getInstances(){
		if( !$this->instances ){
			$this->instances = array();
			$lists = new Default_Model_VALists();
			$lists->filter->vappversionid->equals($this->getId());
			if( count( $lists->items ) > 0 ){
				for($i=0; $i<count($lists->items); $i+=1){
					$list = $lists->items[$i];
					$this->instances[] = $list->getVMIinstance();
				}
				
			}
		}
		return $this->instances;
	}
	public function validate(){
		$newversion = $this->isNewVersion();
		
		//Validation only takes place for state transitions
		//In case of a new version there is no need for it.
		if( $newversion  === true ){
			return true;
		}
		if( $this->oldData->published === true && $this->oldData->archived === false && $this->oldData->enabled === $this->newData->enabled){
			return "Cannot make changes to published version";
		}
		if( $this->oldData->archived === true ){
			return "Cannot make changes to archived version";
		}
		if( $this->oldData->enabled === false && $this->oldData->enabled === $this->newData->enabled){
			return "Cannot make changes in disabled version";
		}
		//check if publishing is valid
		if( $this->toBePublished() === true ){
			
			if( $this->toBeDisabled() ){
				return  "Cannot publish and disable a version at the same time.";
			}
			
			if( $this->isWorkingVersion() === false ){
				return "Only working versions can be published.";
			}
			
			if( in_array($this->getCurrentStatus(), array("init", "verified", "ready", "verify", "verifypublish") ) === false ){
				return "Cannot publish a version in " . $this->getCurrentStatus() . " state.";
			}
			
			$instances = $this->getInstances();
			if( count( $instances ) === 0 ){
				return "Cannot publish an empty version.";
			}
			
		}
		return true;
	}
	public function isWorkingVersion(){
		if( $this->oldData->published == false && $this->oldData->archived == false ) {
			return true;
		}
		return false;
	}
	public function isLatestVersion(){
		if( $this->oldData->published == true && $this->oldData->archived == false ) {
			return true;
		}
		return false;
	}
	public function isEnabled(){
		if( $this->oldData->enabled == true ){
			return true;
		}
		return false;
	}
	public function isPublished(){
		if( $this->oldData->published == true ){
			return true;
		}
		return false;
	}
	public function toBeDisabled(){
		if( $this->isEnabled() && $this->newData->enabled == false ){
			return true;
		}
		return false;
	}
	public function toBeEnabled(){
		if( !$this->isEnabled() && $this->newData->enabled == true ){
			return true;
		}
		return false;
	}
	public function toCancelIntegrityCheck(){
		if( ($this->oldData->status === "verifing" ||  $this->oldData->status === "verifingpublish" ) && $this->newData->status === "init"){
			return true;
		}
		return false;
	}
	public function toBePublished(){
		if( $this->oldData->published == false && $this->newData->published == true ){
			return true;
		}
		return false;
	}
	public function toBeIntegrityChecked(){
		if( ($this->oldData->published === false && $this->newData->published === true) || ($this->oldData->published === false && ($this->newData->status === "verify" || $this->newData->status === "verifypublish") ) ){
			if( $this->needcheck === null ){
				if( $this->newData->status === "verify" ){
					$this->needcheck = true;
					return $this->needcheck;
				}
				$instances = $this->getInstances();
				$this->needcheck = false;
				for( $i=0; $i<count($instances); $i+=1 ){
					$instance = $instances[$i];
					if( $instance->autointegrity == true ){
						$this->needcheck = true;
						break;
					}
				}
			}
			return $this->needcheck;
		}
		return false;
	}
	public function getCurrentStatus(){
		$status = $this->newData->status;
		return strtolower( trim( $status ) );
	}
	public function getId(){
		return $this->newData->id;
	}
	public static function getVapplianceUsedVersions( $appid )
	{
		$res = array();
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$q = "SELECT DISTINCT va_version FROM vaviews WHERE va_version_published = true AND appid = ?;";
		$versions = db()->query( $q, array($appid) )->fetchAll();
		
		if( count($versions) > 0  )
		{
			foreach($versions as $version) {
				$res[] = $version['va_version'];
			}
		}
		
		return $res;
	}
}
/*
 * Handles Virtual appliance version state transitions.
 */
class VApplianceService{
	private $state = null;
	private $version = null;
	private $latestversion = null;
	/*
	 * Needs VApplianceVersionState object to check which action to take
	 */
	function __construct(VApplianceVersionState $state) {
		$this->state = $state;
	}
	
	public function publish(){
		$version = $this->getVAVersion();
		$version->status = "verified";
		$version->createdon = "now()";
		$version->save();
		$result = $this->archiveLatestVersion();
		$va = $version->getVa();
		if( $va ) {
			$app = $va->getApplication();
			if( $app ) {
				$app->lastupdated = "now()";
				$app->save();
			}
		}

		return $result;
	}
	
	public function checkintegrity(){
		$version = $this->getVAVersion();
		$version->published = false;
		if($version->status === "verifypublish" ){ //if request was made for publishing
			$version->status = "verifingpublish";
		}else{
			$version->status = "verifing";
		}
		$version->save();
		return true;
	}	
	public function cancelIntegrityCheck(){
		$version = $this->getVAVersion();
		$version->published = false;
		$version->status = "init";
		$version->save();
		VMCaster::cancelIntegrityCheck($version->id);
		return true;
	}
	//Archive current published version if exists
	public function archiveLatestVersion(){
		$latestversion = $this->getLatestVersion();
		if( $latestversion ){
			$latestversion->archived = true;
			$latestversion->save();
		}
		return true;
	}
	public function disable(){
		$version = $this->getVAVersion();
		$version->enabled = false;
		$version->save();
		return true;
	}
	public function enable(){
		$version = $this->getVAVersion();
		$version->enabled = true;
		$version->save();
		return true;
	}
	public function validate(){
		return $this->state->validate();
	}
	//Calls actions according to va versions state
	public function dispatch(){
		$valid = $this->validate();
		if( $valid !== true ){
			return $valid;
		}
		$tobeintegritychecked = $this->state->toBeIntegrityChecked();
		$tocancelintegritycheck = $this->state->toCancelIntegrityCheck();
		$tobepublished = $this->state->toBePublished();
		$tobedisabled = $this->state->toBeDisabled();
		$tobeenabled = $this->state->toBeEnabled();
		
		if( $tobeintegritychecked === true ){
			return $this->checkintegrity();
		}else if( $tocancelintegritycheck === true ){
			return $this->cancelIntegrityCheck();
		} else if( $tobepublished === true ) {
			return $this->publish();
		}else if( $tobedisabled === true ){
			return $this->disable();
		}else if( $tobeenabled === true ){
			return $this->enable();
		}
		
		return true;
	}
	
	public function postDispatch(){
		$vaversion = $this->getVAVersion(true);
		
		//Create image list for unpubished working version
		if( $vaversion->published === false && 
			$vaversion->archived === false && 
			$vaversion->enabled === true && 
			$vaversion->status === "init" ){
			VMCaster::createImageList($vaversion->id, "unpublished");
		}else if ($vaversion->published === true &&
			$vaversion->archived === false &&
			$vaversion->enabled === true &&
			$vaversion->status === "verified" ){
			VMCaster::createImageList($vaversion->id, "published");
		}else if( $this->state->toBeIntegrityChecked() ){
			VMCaster::startIntegrityCheck($vaversion->id);
		}
		return true;
	}
	
	//Get vaversion current data
	//If force = true it will refetch it from db
	public function getVAVersion($force = false){
		if( !$this->version || $force === true){
			$id = $this->state->getId();
			if( $id ){
				$vers = new Default_Model_VAversions();
				$vers->filter->id->equals($id);
				if( count($vers->items) > 0 ){
					$this->version = $vers->items[0];
				}
			}
		}
		return $this->version;
	}
	//Get published va version if exists
	public function getLatestVersion(){
		if( $this->latestversion === null ){
			$version = $this->getVAVersion();
			$vaversions = new Default_Model_VAversions();
			$f = $vaversions->filter;
			$f->vappid->equals($version->vappid)->and($f->published->equals(true)->and($f->archived->equals(false)->and($f->id->notequals($version->id))));
			if( count( $vaversions->items ) > 0 ) {
				$this->latestversion = $vaversions->items[0];
			}
		}
		return $this->latestversion;
	}
}

class ContextualizationScripts {
	CONST MAX_BYTE_SIZE = 5242880; //5MB 
	private static function getHttpErrorCodes($code){
		$codes = array(
			"400"=>"Bad request",
			"401"=>"Unauthorized",
			"402"=>"Payment Required",
			"403"=>"Forbidden",
			"404"=>"Not found",
			"500"=>"Internal Error",
			"501"=>"Not implemented",
			"502"=>"Service temporarily overloaded",
			"503"=>"Gateway timeout",
			"204"=>"No Response"
		);
		
		if( isset( $codes[$code] ) ){
			return $codes[$code] . " (" . $code . ")";
		}
		return "code " . $code;
		
	}
	public static function fetchUrl($url){
		$error = false;
		$errorno  = -1;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_SSLVERSION,3);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		if( self::MAX_BYTE_SIZE > 1024 ){
			//curl_setopt($ch, CURLOPT_BUFFERSIZE, 10240); //10kb
			curl_setopt($ch, CURLOPT_NOPROGRESS, false);
			curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function(
				$DownloadSize, $Downloaded, $UploadSize, $Uploaded
			){
				return ($Downloaded > (1 * ContextualizationScripts::MAX_BYTE_SIZE)) ? 1 : 0;
			});
		}
		try{
			$data = curl_exec ($ch);
			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$error = curl_error($ch);
			$errorno = curl_errno($ch);
		} catch (Exception $ex) {
			$error = $ex->getMessage();
		}
		curl_close ($ch);
			
		if( self::MAX_BYTE_SIZE > 1024 && $errorno === CURLE_ABORTED_BY_CALLBACK ){
			$error = "Context script size exceeds " . (ContextualizationScripts::MAX_BYTE_SIZE /1024 /1024) . "MB";
		}else if( !$error && trim($code) !== "200" ){
			$error = "Resource responded with: " . self::getHttpErrorCodes($code);
		}
		
		if( $error ) {
			return $error;
		}
		
		$filesize = strlen( $data );
		if( $filesize === 0 ){
			return "The given url/location returned an empty file.";
		}
		
		$md5 = md5($data);
		$parts = parse_url($url);  
		if( isset($parts['path']) )
		{
			$file_name = trim( basename($parts['path']) );
		} else {
			$file_name = '';
		}
		return array (
			"url" => $url,
			"name" => $file_name,
			"md5" => $md5,
			"data" => $data,
			"size"=> $filesize
		);
		
	}
	
	private static function clearVmiInstances($vmiinstanceid, $userid, $usetransaction=true){
		$scripts = array();
		//clear existing vmiinstancecontextscripts since only 
		//one contextscript per vmiinstance is allowed
		$vmis = new Default_Model_VMIinstanceContextScripts();
		$vmis->filter->vmiinstanceid->numequals($vmiinstanceid);
		if( count($vmis->items) >0 ){
			foreach($vmis->items as $vmi){
				$vmiscript = $vmi->getContextScript();
				if( $vmiscript->hasContext() === false ){
					$scripts[$vmi->id] = $vmi->getContextScript();
				}
			}
		}
		
		foreach( $scripts as $k=>$v){
			self::removeRelatedScript($k, $v, $userid, $usetransaction);
		}
		
	}
	
	private static function relateScriptToVmiInstance( $vmiinstanceid, $script, $user, $usetransaction=true ){
		self::clearVmiInstances($vmiinstanceid,$user->id, $usetransaction);
		
		//Associate context script entry with vmi instance
		$vmiscript = new Default_Model_VMIinstanceContextScript();
		$vmiscript->vmiinstanceid = $vmiinstanceid;
		$vmiscript->contextscriptid = $script->id;
		$vmiscript->addedbyid = $user->id;
		$vmiscript->save();
		
		return $script;
	}
	
	private static function updateScriptData($script, $data, $user, $usetransaction=true){
		try{
			if( $usetransaction ) db()->beginTransaction();
			$script->checksum = $data["md5"];
			$script->checksumfunc = "md5";
			$script->size = $data["size"];
			$script->formatid = intval($data['formatid']);
			
			if( trim($script->name) === "" ){
				$script->name = $data["name"];
			}
			$script->lastupdatedbyid = $user->getId();
			$script->save();
			$script = VapplianceStorage::store($script, $data['vmiinstanceid'], $user->id);
			if( $usetransaction ) db()->commit();
		}catch(Exception $ex){
			if( $usetransaction ) {
				db()->rollback();
				return $ex->getMessage();
			} else {
				throw $ex;
			}
		}
		return $script;
	}
	
	private static function createPseudoScript( $data ){
		$script = new Default_Model_ContextScript();
		$script->name = trim( $data["name"] );
		$script->url = trim( $data["url"] );
		$script->formatid = $data['formatid'];
		$script->checksum = $data["md5"];
		$script->checksumfunc = "md5";
		$script->size = $data["size"];
		
		return $script;
	}
	
	private static function addScriptData( $data, $vmiinstanceid, $user, $usetransaction = true){
		try{
			if( $usetransaction ) db()->beginTransaction();
			//create context script entry
			$script = new Default_Model_ContextScript();
			//$script->id = -1;
			$script->name = trim( $data["name"] );
			$script->url = trim( $data["url"] );
			$script->formatid = intval($data["formatid"]);
			$script->checksum = $data["md5"];
			$script->checksumfunc = "md5";
			$script->size = $data["size"];
			$script->addedbyid = $user->id;
			$script->save();
			$script = VapplianceStorage::store($script, $vmiinstanceid, $user->id);
			self::relateScriptToVmiInstance($vmiinstanceid, $script, $user, false);
			
			if( $usetransaction ) db()->commit();
		}catch(Exception $ex){
			if( $usetransaction ) {
				db()->rollback();
				return $ex->getMessage();
			}else{
				throw $ex;
			}
		}
		return $script;
	}
	
	private static function removeRelatedScript( $id, $script, $userid, $usetransaction=true ){
		try{
			$vmiinstanceid = 0;
			if( $usetransaction ) db()->beginTransaction();
			$vmis = new Default_Model_VMIinstanceContextScripts();
			$vmis->filter->id->numequals($id);
			if( count($vmis->items) > 0 ){
				$vmi = $vmis->items[0];
				if( $vmi->contextscriptid === $script->id ){
					$vmiinstanceid = $vmi->vmiinstanceid;
					$vmis->remove($vmi);
				}
			}
			
			$vmis = new Default_Model_VMIinstanceContextScripts();
			$vmis->filter->contextscriptid->numequals($script->id);
			if( count($vmis->items) === 0 ){
				//The script is not longer used. Remove it.
				$scripts = new Default_Model_ContextScripts();
				$scripts->filter->id->numequals($script->id);
				if( count($scripts->items) > 0 ){
					VapplianceStorage::remove($scripts->items[0],$vmiinstanceid, $userid);
					$scripts->remove($scripts->items[0]);
				}
			}
			if( $usetransaction ) db()->commit();
		}catch(Exception $ex){
			if( $usetransaction ) {
				db()->rollback();
				return $ex->getMessage();
			}else{
				throw $ex;
			}
		}
		return true;
	}
	
	public static function contextualizationScriptAction($userid, $action, $url, $vmiinstanceid, $appid = null, $formatid=1){
		$user = null;
		$app = null;
		$vaview = null;
		$issamescript = false;
		$similarscript = false;
		$vmiscript = null;
		$relationid = -1;
		$formatid = intval($formatid);
		if( $formatid <= 0 ) {
			$formatid = 1;
		}
		$users = new Default_Model_Researchers();
		$users->filter->id->numequals($userid);
		if( count( $users->items ) > 0 ){
			$user = $users->items[0];
		} else {
			return false;
		}
		
		if( !in_array($action, array("set", "remove"))  ){
			return "Invalid action type";
		}
		
		if( $vmiinstanceid !== null ){
			$vaviews = new Default_Model_VAviews();
			$vaviews->filter->vmiinstanceid->numequals($vmiinstanceid);
			if( count($vaviews->items) > 0 ){
				$vaview = $vaviews->items[0];
				$appid = $vaview->appid;
			}
		}
		
		$apps = new Default_Model_Applications();
		$apps->filter->id->numequals($appid);
		if( count( $apps->items) > 0 ){
			$app = $apps->items[0];
		} else {
			return "Virtual appliance not found";
		}
		
		$privs = $user->getPrivs();
		if( !$privs || !$privs->canManageVAs($app->guid) ){
			return "No permission for this action";
		}
		
		$url = trim( filter_var(urldecode($url), FILTER_VALIDATE_URL) );
		if( !$url ){
			return "Invalid url";
		}
		
		if( $action !== 'remove') 
		{
			$scriptdata = self::fetchUrl($url);
			if( $scriptdata === false || is_string($scriptdata) ){
				return $scriptdata;
			}
		}
		
		$scriptdata['formatid'] = $formatid;
		$scriptdata['vmiinstanceid'] = $vmiinstanceid;
		
		if( $vmiinstanceid === null ){
			//just do the hashing
			//and return pseudo script object
			//will be used in case of a new working version
			return self::createPseudoScript( $scriptdata );
		}
		
		
		
		//Find if script is already related to the current vmi instance
		$vmis = new Default_Model_VMIinstanceContextScripts();
		$vmis->filter->vmiinstanceid->numequals($vmiinstanceid);
		if( count($vmis->items) > 0 ){
			//find first association with vmiinstance that 
			//does not belong to a sw appliance
			foreach($vmis->items as $vmi){
				if( $vmi->hasContext() === false ){
					$vmiscript = $vmi->getContextScript();
					$relationid = $vmi->id;	
					break;
				}
			}
		
		}
		
		if( !$vmiscript || trim($vmiscript->url) !== trim($url) ){ 
			//if script does not belong to the current vmi instance 
			//Perform checks to determine if a new context script should be created
			
			//Find context script with same url
			$scripts = new Default_Model_ContextScripts();
			$scripts->filter->url->equals( $url );
			if( count( $scripts->items )  > 0 ){
				foreach($scripts->items as $script){
					if( $script->hasContext() === false ){
						$similarscript = $script;
						$vmis = new Default_Model_VMIinstanceContextScripts();
						$vmis->filter->vmiinstanceid->numequals($vmiinstanceid)->and($vmis->filter->contextscriptid->numequals($similarscript->id));
						if( count($vmis->items) > 0 ){
							foreach($vmis->items as $vmi){
								if( $vmi->hasContext() === false ){
									$issamescript = true;
									$relationid = $vmi->id;
									break;
								}
							}
						}
						break;
					}
				}
			}
		} else if( trim($vmiscript->url) === trim($url) ) {
			$issamescript = true;
			$similarscript = false;
		}
		//if script is referenced by a sw appliance then
		//assume it is not a similar script, to avoid conflict 
		//with swappliances contexualization scripts
		if( $similarscript && $similarscript->hasContext() === true ){
			$similarscript = false;
		}
		
		if( $action === "set" ){
			if( $issamescript ){
				//this is the same script for the same vmiinstance
				//just update hashes.
				return self::updateScriptData($vmiscript, $scriptdata, $user);
			} else if( $similarscript ){
				//the script already exists but is not related to the same vmiinstance
				//update hashes and relate to vmi instance
				$res = self::updateScriptData($similarscript, $scriptdata, $user);
					if( $res && !is_string($res) && $relationid && $vmiscript ){
						$res = self::removeRelatedScript($relationid, $vmiscript, $user->id);
					}
					if( $res && !is_string($res) ){
						$res = self::relateScriptToVmiInstance($vmiinstanceid, $similarscript, $user);
					}
					return $res;
			}else {
				//Remove any existing relation with a script
				if( $relationid && $vmiscript ){
					$res = self::removeRelatedScript($relationid, $vmiscript, $user->id);
					if( !$res || is_string($res) ){
						return $res;
					}
				}
				//add script and related it to vmiinstance (will remove any other script relation)
				return self::addScriptData($scriptdata, $vmiinstanceid, $user);
			}
		} else if ( $action === "remove" && $issamescript ){
			if( $vmiscript && $vmiscript->hasContext() === false ){
				return self::removeRelatedScript($relationid, $vmiscript, $user->id);
			}
		}
		
		return "No actions performed";
	}
}

class SocialReport{
	private static function getConfig(){
		$config['appdb']['host']="http://". $_SERVER['APPLICATION_UI_HOSTNAME'] . "/";
		$config['appdb']['api_rel_url']="rest/1.0/applications";
		$config['appdb']['sw_rel_url']="store/software/";
		$config['appdb']['social'] = array('fb','tw','in','gp');
		$config['appdb']['shares']['path'] = "../public/reports/social";

		$config['fb']['cnt_lbl']="shares";
		$config['fb']['url_lbl']="id";
		$config['fb']['end_point']="http://graph.facebook.com/?id=";

		$config['in']['cnt_lbl']="count";
		$config['in']['url_lbl']="url";
		$config['in']['end_point']="http://www.linkedin.com/countserv/count/share?url=";

		$config['tw']['cnt_lbl']="count";
		$config['tw']['url_lbl']="url";
		$config['tw']['end_point']="http://urls.api.twitter.com/1/urls/count.json?url=";

		$config['gp']['cnt_lbl']="count";
		$config['gp']['url_lbl']="url";
		$config['gp']['end_point']="https://clients6.google.com/rpc";

		return $config;
	}
	private static function makeGET($url){
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, '3');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

		$content = trim(curl_exec($ch));
		curl_close($ch);
		return json_decode(str_replace(array('(',');'),'',str_replace('IN.Tags.Share.handleCount','',$content)));
	}
	private static function get_plusones($end_point, $url) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $end_point);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"' . $url . '","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
		$curl_results = curl_exec ($curl);
		curl_close ($curl);
		$json = json_decode($curl_results, true);
		return intval( $json[0]['result']['metadata']['globalCounts']['count'] );
	}
	private static function fetchItemSocialShares($appId, $social, $config, $url){

		$data['appId'] = $appId;
		$data['social'] = $social;
		$target=$config[$social]['end_point'].$url;

		if($social != 'gp'){
			$obj=SocialReport::makeGET($target);
			(isset($obj->{$config[$social]['url_lbl']}) ? $obj->{$config[$social]['url_lbl']} : $obj->{$config[$social]['url_lbl']}='n/a');
			(isset($obj->{$config[$social]['cnt_lbl']}) ? $obj->{$config[$social]['cnt_lbl']} : $obj->{$config[$social]['cnt_lbl']}=0);

			$data['url'] = $obj->{$config[$social]['url_lbl']};
			$data['count'] = $obj->{$config[$social]['cnt_lbl']};
		}
		else{
			$data['url']=$url;
			$data['count']=SocialReport::get_plusones($config[$social]['end_point'],$url);
		}

		return $data['count'];
	}
	private static function fetchSocialShares($apps, $config){
		$countapps = count($apps);
		for($i=0;$i<$countapps; $i++){
			foreach($config['appdb']['social'] as $social){
				$apps[$i]['count'][$social]= SocialReport::fetchItemSocialShares($apps[$i]['id'],$social,$config,$apps[$i]['url']);
			}
		}

		return $apps;
	}
	private static function getAppDBdata($config){
		$result = array();
		$apps = new Default_Model_Applications();
		$apps->viewModerated = true;
		$apps->filter->deleted->equals(false);
		if( count($apps->items) > 0 ){
			for($i=0; $i<count($apps->items); $i+=1){
				$app = $apps->items[$i];
				if($app->deleted == true ) continue;
				$moderated = "false";
				if( $app->moderated === true ){
					$moderated = "true";
				}
				$result[$i]['id']=(int)$app->id;
				$result[$i]['url']=$config['appdb']['host'].$config['appdb']['sw_rel_url'].$app->cname;
				$result[$i]['cname']=(string)$app->cname;
				$result[$i]['name']=(string)$app->name;
				$result[$i]['moderated'] = $moderated;
			}
		}
		return $result;
	}
	private static function getAppDBdataRest($config){
		$apps=array();
		$api_url=$config['appdb']['host'].$config['appdb']['api_rel_url'];

		$xml = simplexml_load_file($api_url);

		$ns = $xml->getNamespaces(true);
		$child = $xml->children($ns['application']);
		$idx=0;
		foreach ($child as $app) {
			if($app->attributes()->deleted == 'false'){
				$apps[$idx]['id']=(int)$app->attributes()->id;
				$apps[$idx]['url']=$config['appdb']['host'].$config['appdb']['sw_rel_url'].$app->attributes()->cname;
				$apps[$idx]['cname']=(string)$app->attributes()->cname;
				$apps[$idx]['name']=(string)$app->name;
				$idx++;
			}
			} 
		return $apps;
	}
	private static function array_to_xml($arr, &$xml) {
		foreach($arr as $key => $value) {
			if(is_array($value)) {
				if(!is_numeric($key)){
					$subnode = $xml->addChild("$key");
					SocialReport::array_to_xml($value, $subnode);
				}
				else{
					$subnode = $xml->addChild("software");
					SocialReport::array_to_xml($value, $subnode);
				}
			}
			else {
				$xml->addChild("$key","$value");
			}
		}
	}
	
	private static function convertReportToCSV($filename){
		$result = true;
		try {
			$xml = file_get_contents("../public/reports/social/" . $filename . ".xml");
			$xsl = new DOMDocument();
			$xsl->load("../application/configs/api/1.0/xslt/swsocial_export_csv.xsl");
			$inputdom = new DomDocument();
			$inputdom->loadXML($xml);

			$proc = new XSLTProcessor();
			$proc->importStylesheet($xsl);
			$proc->setParameter(null, "", "");

			$transform = $proc->transformToXml($inputdom);
			if( $transform !== false ){
				$result = file_put_contents("../public/reports/social/" . $filename . ".csv", $transform);
			}
			if( $result !== false ){
				$result = true;
			}
		} catch(Exception $e) {
			$result = false;
		}
		return $result;
	}
	private static function generateNonZeroShareCountReport($filename){
		$result = true;
		try {
			$xml = file_get_contents("../public/reports/social/" . $filename . ".xml");
			$xsl = new DOMDocument();
			$xsl->load("../application/configs/api/1.0/xslt/swsocial_export_nonzero.xsl");
			$inputdom = new DomDocument();
			$inputdom->loadXML($xml);

			$proc = new XSLTProcessor();
			$proc->importStylesheet($xsl);
			$proc->setParameter(null, "", "");

			$transform = $proc->transformToXml($inputdom);
			if( $transform !== false ){
				$result = file_put_contents("../public/reports/social/" . $filename . "_nz.xml", $transform);
			}
			if( $result !== false ){
				$result = true;
			}
		} catch(Exception $e) {
			$result = false;
		}
		return $result;
	}
	private static function generateShareCountReport($config,$filename){
		$date=date('c');
		$udate=date('U');
		$folder = $config['appdb']['shares']['path'] . "/";
		
		$appsdata = SocialReport::getAppDBdata($config);
		$apps = SocialReport::fetchSocialShares($appsdata, $config);

		$shares_xml = new SimpleXMLElement("<shares dateProduced=\"".$date."\" dateProduced_unix=\"".$udate."\" count=\"".count($apps)."\"></shares>");
		SocialReport::array_to_xml($apps,$shares_xml);
		$xml = $shares_xml->asXML();
		if( $xml === false ){
			error_log("[SocialReport::generateShareCountReport]: Could not generate xml " . $folder.$filename.".xml");
			return false;
		}
		$writesuccess = file_put_contents($folder.$filename.".xml",$xml);
		if( $writesuccess === false ){
			error_log("[SocialReport::generateShareCountReport]: Could not write to file " . $folder.$filename.".xml");
			return false;
		}
		return true;
	}
	private static function generateFileName(){
			$year = date('Y');
			$month = date('m');
			$day = date('d');
			return "sw_" . $year . "_" . $month . "_" . $day;
	}
	private static function mailDispatchReport($recipients, $filename,$folder){
		$reportcsv = file_get_contents($folder.$filename.".csv");
		if( $reportcsv === false ){
			error_log("[SocialReport::mailDispatch]: Could not load report " . $filename);
			return false;
		}
		
		$subject = "EGI AppDB Social media sharing report (" . date('Y') . "-" . date('m') . "-" . date('d') . ")";
		
		$sbody = "This is an automatically generated report for social media sharing count per registered software item.";
		$sbody = $sbody . "Please find attached a CSV file containing the report.<br/>";
		$sbody = $sbody . "You can access this report at http://" . $_SERVER['APPLICATION_UI_HOSTNAME'] . "/reports/social/" . $filename . ".csv <br/><br/>";
		$sbody = $sbody . "Regards,<br/>";
		$sbody = $sbody . "EGI AppDB Team";
		
		$textsbody = simpleHTML2Text($sbody);
		
		$att = array(
			"data" => $reportcsv,
			"type" => "application/vnd.ms-excel",
			"name" => $filename .".csv"
		);
		//sendMultipartMail($subject, $recipients, $textsbody, $sbody, 'appdb-reports@iasa.gr', 'enadyskolopassword', 'appdb-support@iasa.gr',$att, false,array("Precedence"=>"bulk"));
		EmailService::sendBulkReport($subject, $recipients, $textsbody, $sbody, EmailConfiguration::getSupportAddress(), $att);
	}
	public static function generateReports($recipients=array(),$filename=""){
		if( trim($filename) === "" ){
			$filename = SocialReport::generateFileName();
		}
		$config = SocialReport::getConfig();
		$folder = $config['appdb']['shares']['path'] . "/";
		
		$res = SocialReport::generateShareCountReport($config,$filename);
		if( $res === true ){
			$res = SocialReport::convertReportToCSV($filename);
		}
		
		if( $res === true ){
			$res = SocialReport::generateNonZeroShareCountReport($filename);
		}
		
		if( $res === true ){
			$res = SocialReport::convertReportToCSV($filename."_nz");
		}
		if( $res === true && count($recipients) > 0 ){
			SocialReport::mailDispatchReport($recipients, $filename."_nz" ,$folder );
		}
	}
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
class UserInbox{
	private static function getEnvelopStart($apiversion = '1.0'){
		return  '<appdb:appdb xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:appdb="http://appdb.egi.eu/api/1.0/appdb" xmlns:application="http://appdb.egi.eu/api/1.0/application" xmlns:discipline="http://appdb.egi.eu/api/1.0/discipline" xmlns:category="http://appdb.egi.eu/api/1.0/category" xmlns:dissemination="http://appdb.egi.eu/api/1.0/dissemination" '.
				'xmlns:person="http://appdb.egi.eu/api/' . $apiversion . '/person" '.
				'xmlns:permission="http://appdb.egi.eu/api/' . $apiversion . '/permission" '.
				'xmlns:privilege="http://appdb.egi.eu/api/' . $apiversion . '/privilege" '.
				'xmlns:user="http://appdb.egi.eu/api/' . $apiversion . '/user" '.
				'xmlns:virtualization="http://appdb.egi.eu/api/' . $apiversion . '/virtualization" '.
				'xmlns:message="http://appdb.egi.eu/api/' . $apiversion . '/message" '.
				'datatype="virtualization" version="' . $apiversion . '">';
	}
	private static function getEnvelopEnd(){
		return '</appdb:appdb>';
	}
	private static function getModel($uid,$flt=array()){
		$msgs = new Default_Model_Messages();
		$length = ( (isset($flt["length"]) && is_numeric($flt["length"]) )?intval($flt["length"]):10 );
		$offset = ( (isset($flt["offset"]) && is_numeric($flt["offset"]) )?intval($flt["offset"]):0 );
		$order = strtolower(trim(( (isset($flt["order"]) && is_string($flt["order"]) )?strval($flt["order"]):"sendon" )));
		$orderop = strtolower(trim(( (isset($flt["orderop"]) && is_string($flt["orderop"]) )?strval($flt["orderop"]):"ASC" )));
		$folder = strtolower(trim(( (isset($flt["folder"]) && is_string($flt["folder"]) )?strval($flt["folder"]):"all" )));
		$from = ( (isset($flt["from"]) && is_numeric($flt["from"]) )?intval($flt["from"]):null );
		$to = ( (isset($flt["to"]) && is_numeric($flt["to"]) )?intval($flt["to"]):null );
		$unread = (isset($flt["unread"])?true:false);
		$id = ( ( isset($flt["id"]) && is_numeric($flt["id"]) )?intval($flt["id"]):null );
		if( $id !== null ){
			$msgs->filter->id->equals($id);
		}else{
			switch($folder){
				case "inbox":
					$f1 = new Default_Model_MessagesFilter();
					$f2 = new Default_Model_MessagesFilter();
					$f3 = new Default_Model_MessagesFilter();

					$f1->receiverid->equals($uid);
					$f2->senderid->equals($from);
					$f3->isread->equals(false);

					$msgs->filter->chain($f1, "AND");
					if( $from !== null ){
						error_log("from $from");
						$msgs->filter->chain($f2, "AND");
					}
					if( $unread === true ){
						$msgs->filter->chain($f3, "AND");
					}

					break;
				case "outbox":
					$msgs->filter->senderid->equals($uid);
					if( $to !== null ){
						$msgs->filter->chain($msgs->filter->receiverid->equals($to), "AND");
					}
					break;
				default:
					$msgs->filter->senderid->equals($uid)->or($msgs->filter->receiverid->equals($uid));
					break;
			}
		}
		
		$msgs->filter->limit = $length;
		$msgs->filter->offset = $offset;
		
		return $msgs;
	}
	public static function getMessages($uid,$flt=array()){
		$envelop_start = self::getEnvelopStart();
		$envelop_end = self::getEnvelopEnd();
		$folder = strtolower(trim(( (isset($flt["folder"]) && is_string($flt["folder"]) )?strval($flt["folder"]):null )));
		$action = strtolower(trim(( (isset($flt["action"]) && is_string($flt["action"]) )?strval($flt["action"]):'fetch' )));
		$onlycount = ($action==="count")?true:false;
		if( $uid === null ){
			return $envelop_start . "<person:messages count='0' length='0' offset='0' folder='".$folder."'></person:messages>" . $envelop_end;
		}
		
		$msgs = self::getModel($uid,$flt);
		$msgscount = count($msgs->items);
		$res = $envelop_start;
		$res .= "<person:messages count='".$msgscount."' length='".$msgs->filter->limit."' offset='".$msgs->filter->offset."' orderby='".$msgs->filter->orderBy."'>";
		if( $msgscount === 0 || $onlycount ){
			$res .= "</person:messages>" . $envelop_end;
			return $res;
		}
		$senders = array();
		for($i=0; $i<$msgscount; $i+=1){
			$item = $msgs->items[$i];
			$isread = ( ($item->isread===true)?"true":"false" );
			$dir = "unknown";
			$personid = -1;
			$msg = "<person:message id='" . $item->id . "' isread='" . $isread  . "' sendon='" . $item->senton . "' ";
			if( $uid == $item->receiverid ){
				$msg .= "folder='inbox' >";
				$dir = "from";
				$personid = $item->senderid;
				if( isset($senders[$personid]) == false ){
					$s = $item->getSender();
					$senders[$personid] = " id='" . $s->id . "' cname='" . $s->cname . "' firstname='" . $s->firstname . "' lastname='" . $s->lastname . "' ";
				}
			}else if($uid == $item->senderid){
				$msg .= "folder='outbox' >";
				$dir = "to";
				$personid = $item->receiverid;
				if( isset($senders[$personid]) == false ){
					$s = $item->getReceiver();
					$senders[$personid] = " id='" . $s->id . "' cname='" . $s->cname . "' firstname='" . $s->firstname . "' lastname='" . $s->lastname . "' ";
				}
			}else{
				$msg .= "folder='unknown' ></person:message>";
				$res .= $msg;
				continue;
			}
			
			$msg .= "<message:headers><message:" . $dir . " " . $senders[$personid] . "></message:" . $dir . "></message:headers>";
			$msg .= "<message:content>" . $item->msg . "</message:content>";
			$msg .= "</person:message>";
			$res .= $msg;
		}
		
		$res .= "</person:messages>" . $envelop_end;
		return $res;
	}
}

class SamlAuth{
	const LIB_AUTOLOAD = '/var/simplesamlphp.sp/lib/_autoload.php';
	
	//Check SimpleSAML if user is authenticated by another service
	public static function isAuthenticated(){
		require_once(SamlAuth::LIB_AUTOLOAD);
		$source=null;

		$config = SimpleSAML_Configuration::getInstance();
		$t = new SimpleSAML_XHTML_Template($config, 'core:authsource_list.tpl.php');
		$t->data['sources'] = SimpleSAML_Auth_Source::getSourcesMatch('-sp');

		foreach ($t->data['sources'] as &$_source) {
			$as = new SimpleSAML_Auth_Simple($_source);
			if($as->isAuthenticated()){
				$source=$as;
				break;
			}
		}
		if( $source === null ){
			return false;
		}
		return $source;
	}
	
	//Create a Researcher model for new user with session data retrieved by SAML Auth
	public static function initNewUserProfile($session){
		$newuser = new Default_Model_Researcher();
		$newuser->id = -1;
		$newuser->firstname = $session->userFirstName;
		$newuser->lastname = $session->userLastName;
		$newuser->positionTypeID = $session->userRole;
		$newuser->countryID = $session->userCountryID;
		if( trim($session->userPrimaryEmail) !== "" ){
			$contact = new Default_Model_Contact();
			$contact->contacttypeid = 7;
			$contact->data = $session->userPrimaryEmail;
			$session->userContacts = array($contact);
		}
		return $newuser;
	}
	
	//Retrieve profiles that the new account might correspond to.
	public static function getConnectableProfileIds($session){
		$profiles = array();
		$email = $session->userPrimaryEmail;
		if( trim($email) !== ""){
			//Search by email for NOT-deleted users 
			$contacts = new Default_Model_Contacts();
			$contacts->filter->data->ilike($email);
			if( count($contacts->items) > 0 ){
				$contact = $contacts->items[0];
				$user = $contact->getResearcher();
				//Check if profile is NOT deleted
				if( !$user->deleted  ){ 
					array_push($profiles, $user->id);
				}
			}
		}
		//Search by first and last name for NOT-deleted
		$users = new Default_Model_Researchers();
		$f1 = new Default_Model_ResearchersFilter();
		$f2 = new Default_Model_ResearchersFilter();
		$f1->firstname->ilike($session->userFirstName);
		$f2->lastname->ilike($session->userLastName);
		$users->filter->chain($f1, "AND");
		$users->filter->chain($f2, "AND");
		if( count($users->items) > 0 ){
			foreach($users->items as $user){
				if( !$user->deleted ){
					array_push($profiles, $user->id);
				}
			}
		}
		//Search by last and first name
		$users = new Default_Model_Researchers();
		$f1 = new Default_Model_ResearchersFilter();
		$f2 = new Default_Model_ResearchersFilter();
		$f1->firstname->ilike($session->userLastName);
		$f2->lastname->ilike($session->userFirstName);
		$users->filter->chain($f1, "AND");
		$users->filter->chain($f2, "AND");
		if( count($users->items) > 0 ){
			foreach($users->items as $user){
				if( !$user->deleted ){
					array_push($profiles, $user->id);
				}
			}
		}
		
		return $profiles;
	}
	
	//Get user account entry for given values
	public static function getUserAccount($uid, $accounttype){
		$useraccounts = new Default_Model_UserAccounts();
		$f1 = new Default_Model_UserAccountsFilter();
		$f2 = new Default_Model_UserAccountsFilter();
		$f1->accountid->_escape_seq = "";
		$f1->accountid->equals($uid);
		$f2->account_type->equals($accounttype);
		$useraccounts->filter->chain($f1, "AND");
		$useraccounts->filter->chain($f2, "AND");
		if( count( $useraccounts->items ) > 0 ){
			return $useraccounts->items[0];
		}
		return null;
	}

	//Get available user accounts for specific user
	public static function getResearcherUserAccounts($userid, $accounttype = null){
		$useraccounts = new Default_Model_UserAccounts();
		$f1 = new Default_Model_UserAccountsFilter();
		$f2 = new Default_Model_UserAccountsFilter();
		$f1->researcherid->equals($userid);
		$useraccounts->filter->chain($f1, "AND");
		if($accounttype !== null) {
			$f2->account_type->equals($accounttype);
			$useraccounts->filter->chain($f2, "AND");
		}
		if( count( $useraccounts->items ) > 0 ){
			return $useraccounts->items;
		}
		return array();
	}
	
	//Get researcher entry for the given user account entry
	public static function getUserByAccount($useraccount){
		if( is_null($useraccount) ) return null;
		$ppl = new Default_Model_Researchers();
		$ppl->viewModerated = true;
		$ppl->filter->id->equals($useraccount->researcherid);
		if( count($ppl->items) > 0 ){
			$researcher = $ppl->items[0];
			return $researcher;
		}
		return null;
	}
	
	//Retrieves user profile based on user account id and type
	public static function getUserByAccountValues($uid, $accounttype){
		$useraccount = self::getUserAccount($uid, $accounttype);
		if( $useraccount !== null ){
			$researcher = self::getUserByAccount($useraccount);
			return $researcher;
		}
		return null;
	}
	
	//Retrieves user accounts based on researcher id
	public static function getUserAccountsByUser($userid, $asArray = false){
		if( $userid == null || is_numeric($userid) === false || intval($userid) <=0 ){
			return array();
		}
		$result = array();
		
		$useraccounts = new Default_Model_UserAccounts();
		$useraccounts->filter->researcherid->equals($userid);
		if( count($useraccounts->items) > 0 ){
			if( $asArray === true ){ //check if requested as associative array (for client consuption)
				for($i = 0; $i<count($useraccounts->items); $i+=1){
					$ua = $useraccounts->items[$i];
					array_push( $result, array(
						"id" => trim($ua->id),
						"uid" => trim($ua->accountid),
						"source" => trim($ua->accounttypeid),
						"name" => trim($ua->accountname),
						"state" => trim($ua->getState()->name),
						"idptrace" => implode("\n", $ua->getIDPTrace())
					));
				}
			}else{
				$result = $useraccounts->items;
			}
		}
		
		return $result;
	}
	
	//Get user credentials for this session
	public static function getUserCredentials($userid){
		$creds = new Default_Model_UserCredentials();
		$f1 = new Default_Model_UserCredentialsFilter();
		$f2 = new Default_Model_UserCredentialsFilter();
		$f3 = new Default_Model_UserCredentialsFilter();
		$f1->researcherid->equals($userid);
		$f2->sessionid->equals(session_id());
		$f3->token->equals($_COOKIE["SimpleSAMLAuthToken"]);
		$creds->filter->chain($f1, "AND");
		$creds->filter->chain($f2, "AND");
		$creds->filter->chain($f3, "AND");
		if( count($creds->items) > 0 ){
			return $creds->items[0];  
		}
		return null;
	}
	
	//Called from SamlAuth::logout and removes user credentials from database
	public static function clearUserCredentails($session){
		if( is_null($session) || isset($session->userid) === false || is_numeric($session->userid)===false || intval($session->userid) <=0 ) return;
		if( isset($_COOKIE["SimpleSAMLAuthToken"]) === false ) return;
		$cred = self::getUserCredentials($session->userid);
		if( $cred === null ) return;
		$creds = new Default_Model_UserCredentials();
		$creds->filter->id->equals($cred->id);
		if( count($creds->items) > 0 ){
			$creds->remove($cred);
		}
	}
	//Saves and returns the new user session credentials
	//Setups session accordingly
	public static function setupSamlUserCredentials($user, $session = null){
		$userid = $user->id;
		//Remove existing user credentials
		$oldcred = self::getUserCredentials($userid);
		if( $oldcred !== null ) {
			$creds = new Default_Model_UserCredentials();
			if( count($creds->items) > 0 ){
				$creds->remove($oldcred);
			}
			//$oldcred->remove();
		}
		
		//Create new user credentials
		$cred = new Default_Model_UserCredential();
		$cred->researcherid = $userid;
		$cred->sessionid = session_id();
		$cred->token = $_COOKIE["SimpleSAMLAuthToken"];
		$cred->save();
		
		//Save to session
		if( $session !== null ){
			$session->authCredSessionId = $cred->sessionid;
			$session->authCredSamlAuthToken = $cred->token;
			$session->authCredAddedOn = $cred->addedon;
			$session->authCredId = $cred->id;
		}
		
		return $cred;
	}
	
	//Prefills session data from the SAML response, to be used for the creation of a new profile.
	public static function setupSamlNewUserSession($session, $accounttype){
		$attrs = $session->samlattrs;
		//initialize session data
		$session->authCredSessionId = session_id();
		$session->authCredSamlAuthToken = $_COOKIE["SimpleSAMLAuthToken"];
		$session->authCredId = null;
		$session->userid = -1;
		$session->isNewUser = true;
		$session->username = $attrs["idp:uid"][0];
		$session->usercname = "";
		$session->userFirstName = ( ( isset($attrs["idp:givenName"]) === true && count($attrs["idp:givenName"]) > 0 )?$attrs["idp:givenName"][0]:"" );
		$session->userLastName = ( ( isset($attrs["idp:sn"]) === true && count($attrs["idp:givenName"]) > 0 )?$attrs["idp:sn"][0]:"" );
		$session->userFullName = $session->userFirstName . " " . $session->userLastName;
		$session->fullName = $session->userFullName;
		$session->userRole = 4;
		$session->userCountryID = 0;
		$session->userCountryName = "";
		$session->userPrimaryEmail = ( ( isset($attrs["idp:mail"]) === true && count($attrs["idp:mail"]) > 0 )?$attrs["idp:mail"][0]:"" );
		if( isset($session->accountStatus) === false ){
			$session->accountStatus = "new";
		}
		//Check invalid emails from social media user accounts
		if( trim($session->userPrimaryEmail) === "" ||  strtolower(trim($session->userPrimaryEmail)) === strtolower(trim($session->username . "@" . $accounttype . ".com")) ){
			unset($session->userPrimaryEmail);
		}
		
		//Check if user has pending connection
		AccountConnect::isPending($session);
		//collect session data for new user based on saml source
		switch($accounttype){
			case "x509":
				break;
			case "egi-sso-ldap":
				break;
			case "facebook":
				break;
			case "linkedin":
				break;
			case "twitter":
				break;
			case "google":
				break;
			default:
				break;
		}
	}
	
	//Clears any transaction variables before authedication setup
	//called from SamlAuth::setupSamlSession
	public static function clearSession($session){
		unset($session->isNewUser);
		unset($session->accountStatus);
		unset($session->accountPendingId);
		unset($session->accountPendingProfileId);
		unset($session->accountPendingProfileName);
		unset($session->userDeleted);
		unset($session->userDeletedById);
		unset($session->userDeletedByName);
		unset($session->userDeletedByCName);
		unset($session->userDeletedOn);
		unset($session->currentAccountSource);
		unset($session->currentAccountId);
		unset($session->currentAccountUid);
		unset($session->currentAccountName);
		unset($session->currentUserAccounts);
	}
	
	//Fill session data based on the user profile.
	public static function setupSamlSession($session, $account, $user ){
		self::clearSession($session);
		
		$session->userid = $user->id;
		$session->userguid = $user->guid;
		$session->username = $user->cname;
		$session->usercname = $user->cname;
		$session->userfullName = $user->name;
		$session->userFirstName = $user->firstname;
		$session->userLastName = $user->lastname;
		$session->fullName = $user->name;
		$session->userRole = $user->positionTypeID;
		$session->userCountryID = $user->countryID;
		$session->userCountryName = $user->country->name;
		$session->userDeleted = $user->deleted;
		
		//Setup session variables in case of deleted profile
		if( $session->userDeleted == true ){
			$session->userDeletedById = $user->delInfo->deleter->id;
			$session->userDeletedByName = $user->delInfo->deleter->name;
			$session->userDeletedByCName = $user->delInfo->deleter->cname;
			$session->userDeletedOn = $user->delInfo->deletedOn;
		}else{
			//load current account in session
			$session->currentUserAccount = array(
				"id" => $account->id,
				"source" => $account->accounttypeid,
				"uid" => $account->accountid,
				"name" => $account->accountname,
				"idptrace" => implode("\n", $account->getIDPTrace())
			);
			//load available user accounts
			$session->currentUserAccounts = self::getUserAccountsByUser($user->id, true);
		}
		//collect session data for new user based on saml source
		$accounttype = strtolower( trim($account->accounttypeid) );
		switch($accounttype){
			case "x509":
				break;
			case "egi-sso-ldap":
				break;
			case "facebook":
				break;
			case "linkedin":
				break;
			case "twitter":
				break;
			case "google":
				break;
			default:
				break;
		}
	}
	
	//Autoconnects an egisso account to an existing x509 account if exists.
	public static function connectEgiToX509($session){
		//check if there exists an user_accounts.accountid = idp:userCertificateSubject and user_account.account_type = "x509"
		//If true add x509 to the user_accounts with the same researcher id and return the profile
		//If false return null
		$attrs = $session->samlattrs;
		$source = strtolower(trim($session->samlauthsource));
		$uid = ( (isset($attrs["idp:uid"])== true && count($attrs["idp:uid"]) > 0 )?$attrs["idp:uid"][0]:"");
		$ucert = ( (isset($attrs["idp:userCertificateSubject"])== true && count($attrs["idp:userCertificateSubject"]) > 0 )?$attrs["idp:userCertificateSubject"][0]:"");
		
		//Check if current source is indeed egi-sso-ldap-sp
		if( $source !== "egi-sso-ldap-sp") return null;
		
		//Check if there is a profile with an userCertificateSubject x509 account
		$researcher = self::getUserByAccountValues($ucert, "x509");
		if( $researcher === null ) return null;
		
		//Save this egi account to the found researcher profile
		$uaccount = new Default_Model_UserAccount();
		$uaccount->researcherid = $researcher->id;
		$uaccount->accountid = $uid;
		$uaccount->accounttypeid = "egi-sso-ldap";
		$uaccount->save();
		
		return $researcher;
	}
	
	//Autoconnects a x509 account to an existing egi sso account if exists.
	public static function connectX509ToEgi($session){
		//check if there exists an user_accounts.accountid = idp:egiuid and user_account.account_type = "egi-sso-ldap"
		//If true add x509 to the user_accounts with the same researcher id and return the profile
		//If false return null
		$attrs = $session->samlattrs;
		$source = strtolower(trim($session->samlauthsource));
		$uid = ( (isset($attrs["idp:uid"])== true && count($attrs["idp:uid"]) > 0 )?$attrs["idp:uid"][0]:"");
		$egiuid = ( (isset($attrs["idp:egiuid"])== true && count($attrs["idp:egiuid"]) > 0 )?$attrs["idp:egiuid"][0]:"");
		
		//Check if current source is indeed x509-sp
		if( $source !== "x509-sp") return null;
		
		//Check if there is a profile with an egiuid egi sso account type
		$researcher = self::getUserByAccountValues($egiuid, "egi-sso-ldap");
		
		//Failed to retrieve a profile for relative to specific account
		if( $researcher === null ) return null;
		
		//tSave this egi account to the found researchr profile
		$uaccount = new Default_Model_UserAccount();
		$uaccount->researcherid = $researcher->id;
		$uaccount->accountid = $uid;
		$uaccount->accounttypeid = "x509";
		$uaccount->save();

		return $researcher;
	}
	
	//Collect and store implicit user accounts from current one.
	//E.g. if a user signed in with an egi sso account there might be a usercertificate subject. 
	//In this case store it as a x509 user account in the current profile.
	public static function harvestSamlData($session, $user){
		$attrs = $session->samlattrs;
		$egiuid = ( (isset($attrs["idp:egiuid"])== true && count($attrs["idp:egiuid"]) > 0 )?$attrs["idp:egiuid"][0]:"");
		$ucert = ( (isset($attrs["idp:userCertificateSubject"])== true && count($attrs["idp:userCertificateSubject"]) > 0 )?$attrs["idp:userCertificateSubject"][0]:"");
		//collect egi sso ldap user account (possibly from x509 user account)
		if( trim($egiuid) !== "" ){
			$uacs = new Default_Model_UserAccounts();
			$f1 = new Default_Model_UserAccountsFilter();
			$f2 = new Default_Model_UserAccountsFilter();
			$f3 = new Default_Model_UserAccountsFilter();
			$f1->researcherid->equals($user->id);
			$f2->account_type->equals("egi-sso-ldap");
			$f3->accountid->_escape_seq = "";
			$f3->accountid->equals($egiuid);
			$uacs->filter->chain($f1, "AND");
			$uacs->filter->chain($f2, "AND");
			$uacs->filter->chain($f3, "AND");
			if( count($uacs->items) === 0 ){
				$uacc = new Default_Model_UserAccount();
				$uacc->researcherid = $user->id;
				$uacc->accountid = $egiuid;
				$uacc->accounttypeid = "egi-sso-ldap";
				$uacc->save();
			}
		}
		
		//collect x509 user account (possibly from egi sso user account)
		if( trim($ucert) !== "" ){
			$uacs = new Default_Model_UserAccounts();
			$f1 = new Default_Model_UserAccountsFilter();
			$f2 = new Default_Model_UserAccountsFilter();
			$f3 = new Default_Model_UserAccountsFilter();
			$f1->researcherid->equals($user->id);
			$f2->account_type->equals("x509");
			$f3->accountid->_escape_seq = "";
			$f3->accountid->equals($ucert);
			$uacs->filter->chain($f1, "AND");
			$uacs->filter->chain($f2, "AND");
			$uacs->filter->chain($f3, "AND");
			if( count($uacs->items) === 0 ){
				$uacc = new Default_Model_UserAccount();
				$uacc->researcherid = $user->id;
				$uacc->accountid = $ucert;
				$uacc->accounttypeid = "x509";
				$uacc->save();
			}
		}
	}
	
	//Return user account entry for current session user
	public static function getCurrentAccount($session){
		if( isset($session->userid) == false || $session->userid == null ){
			return null;
		}
		$attrs = $session->samlattrs;
		$source = strtolower(trim($session->samlauthsource));
		$uid = ( isset($attrs["idp:uid"])?$attrs["idp:uid"][0]:"");
		if( trim($uid) == "" ) return null;
		$accounttype = str_replace("-sp","",$source);
		
		$useraccounts = new Default_Model_UserAccounts();
		$f1 = new Default_Model_UserAccountsFilter();
		$f2 = new Default_Model_UserAccountsFilter();
		$f1->accountid->_escape_seq = "";
		$f1->accountid->equals($uid);
		$f2->account_type->equals($accounttype);
		$useraccounts->filter->chain($f1, "AND");
		$useraccounts->filter->chain($f2, "AND");
		if( count( $useraccounts->items ) > 0 ){
			return $useraccounts->items[0];
		}
		return null;
	}
	
	//Checks if given user account is not active(id: 1) and updates session accordingly
	public static function setupUserAccountStatus($session, $useraccount){
		if( is_null($useraccount) === true ) return null;
		switch(trim($useraccount->stateid)){
			case "2":
				//User account is blocked
				$session->accountStatus = "blocked";
				break;
			case "1":
			default:
				//All ok
				break;
		}
	}
	//Helper function to create entitlement role mappings
	//based on appdb ini configuration file
	public static function getEGIAAIRoleMappings($key) {
		$res = array();
		$roles =  explode('\n', ApplicationConfiguration::saml('egiaai.entitlements.' . $key, ''));
		
		foreach($roles as $role) {
			$role = explode('=', $role);
			if( count($role) <= 1) {
				continue;
			}
			
			$local = $role[0];
			$remote = explode(';', $role[1]);
			
			if( count($remote) === 0) {
				continue;
			}
		
			$res = array_merge($res, array_fill_keys($remote, $local));
		}
		
		return $res;
	}
	//Helper function to return vo role mapping from EGI AAI entitlements
	//If no EGI AAI vo role is given it return all of the role mappings
	//If the given role is not found it returns null.
	private static function getEGIAAIVORoleMapping($role = null) {
		$roles = self::getEGIAAIRoleMappings('vo');
		
		if( $role === null ) {
			return $roles;
		}
		
		if( isset($roles[$role]) && trim($roles[$role]) !== "" ) {
			return $roles[$role];
		}
		
		return null;
	}
	//Helper function to return site role mapping from EGI AAI entitlements
	//If no EGI AAI site role is given it return all of the role mappings
	//If the given role is not found it returns null.
	private static function getEGIAAISiteRoleMapping($role = null) {
		$roles = self::getEGIAAIRoleMappings('site');
		
		if( $role === null ) {
			return $roles;
		}
		
		if( isset($roles[$role]) && trim($roles[$role]) !== "" ) {
			return $roles[$role];
		}
		
		return null;
	}
	//Extracts user entitlements from the saml login response if they exist.
	//Returns an array with VO memberships and Site roles
	private static function extractSamlEntitlements($attrs) {
	  $res = array('vos' => array("members" => array(), "contacts" => array(), "vmops" => array()), 'sites' => array(), 'groups' => array());

	  if( !is_array($attrs) || !isset($attrs['idp:entitlement']) ){
		return $res;
	  }

	  $entitlements = $attrs['idp:entitlement'];
	  foreach( $entitlements as $e ){
		$matches = array();

		//Check if entitlement specifies a site role
		//preg_match("/^urn\:(mace\:)?(.*)\:user\-role\:(.*)\:on-entity\:(.*)\:primary\-key:(.*):in\-project:(.*):(.*)$/", $e, $matches);
		preg_match("/^urn\:(mace\:)?(egi\.eu)\:(goc\.egi\.eu)\:([^\:]*)\:([^\:]*)\:([^\:]*)\@(egi\.eu)$/", $e, $matches);
		if( count($matches) === 8) {
			$role = self::getEGIAAISiteRoleMapping($matches[6]);
			if( $role === null ) {
				continue;
			}

			$res['sites'][] = array(
				'scope' => $matches[2],
				'source' => $matches[3],
				'site_key' => $matches[4],
				'site_name' => $matches[5],
				'role' => $role
			);
			continue;
		}
		
		//Check if entitlement specifies groups
		//preg_match("/^urn\:(mace\:)?(.*)\:group:(.*)$/", $e, $matches);
		preg_match("/^urn\:(mace\:)?(egi\.eu)\:(www\.egi\.eu)\:([^\:]*)\:([^\:]*)\@egi\.eu$/", $e, $matches);
		if( count($matches) === 6) {
			$res['groups'][] = array(
				'scope' => $matches[2],
				'source' => $matches[3],
				'group' => $matches[4],
				'role' => $matches[5]
			);
			continue;
		}
		
		//Check if entitlement specifies a vo role
		//preg_match("/^urn\:(mace\:)?(.*)\:vo\:(.*)\:role\:(.*)$/", $e, $matches); 
		preg_match("/^urn\:(mace\:)?(egi\.eu)\:([^\:]*)\:(.*\:)*([^\:]*)\@(.*)$/", $e, $matches);
		if( count($matches) === 7 && $matches[6] !== 'egi.eu') {
		  $scope = $matches[2];
		  $source = $matches[3];
		  $group = $matches[4];
		  $role = self::getEGIAAIVORoleMapping($matches[5]);
		  $voname = $matches[6];
		  
		  if ($role === 'VM OPERATOR' && strpos($source, 'appdb_auth') === false) {
			//Do not accept vm_operator role if it is not given by AppDB auth source
			continue;
		  }

		  if( $role === 'member' ) {
			$res['vos']['members'][] = array('scope' => $scope, 'source' => $source, 'vo' => $voname, 'group' => $group );
		  } else if($role !== null) {
			  if ($role === 'VM OPERATOR') {
				$res['vos']['vmops'][] = array('scope' => $scope, 'source' => $source, 'vo' => $voname, 'role' => $role, 'group' => $group );
			  } else {
				$res['vos']['contacts'][] = array('scope' => $scope, 'source' => $source, 'vo' => $voname, 'role' => $role, 'group' => $group );
				$res['vos']['members'][] = array('scope' => $scope, 'source' => $source, 'vo' => $voname, 'group' => $group );
			  }
		  }
		  continue;
		}
	  }
	  return $res;
	}
	
	//Reject VM Operator if account is not entitled with VO membership
	private static function validateEntitlements($user, $entitlements = array()) {
			$vos = ((isset($entitlements['vos'])) ? $entitlements['vos'] : array());
			$vmops = ((isset($vos['vmops'])) ? $vos['vmops'] : array());
			$contacts = ((isset($vos['contacts'])) ? $vos['contacts'] : array());
			$members = ((isset($vos['members'])) ? $vos['members'] : array());
			$storedMembers = array();
			$vomembers = array();

			if (count($vmops) === 0 ) {
			  return $entitlements;
			}

			//Get already stored (Operations Portal) VO memberships
			if ($user !== null) {
				$storedMembers = $user->getVOMemberships();
				if (is_array($storedMembers)) {
					foreach($storedMembers as $storedMember) {
						$vomem = $storedMember->getVO();
						if ($vomem !== null) {
							$vomembers[] = $vomem->name;
						}
					}
				}
			}

			//Get currently retrieved VO membership entitlements
			foreach($members as $member) {
				$vomembers[] = $member['vo'];
			}

			foreach($vmops as $vmop) {
				//If a VO VM Operator is also entitled with membership add it to contacts
				if (in_array($vmop['vo'], $vomembers) === true) {
					$contacts[] = $vmop;
				}
			}

			$vos['contacts'] = $contacts;
			$entitlements['vos'] = $vos;

			return $entitlements;
	}


	//Persist any VO related information from EGI AAI entitlements given for a specific uid in SAML returned attributes
	private static function updateEGIAAIEntitlements($attrs, $entitlements = array(), $user = null) {
		$vocontacts = array();
		$vomembers = array();
		$puid = ( isset($attrs["idp:uid"])?$attrs["idp:uid"][0]:"");
		$email = ( ( isset($attrs["idp:mail"]) === true && count($attrs["idp:mail"]) > 0 )?$attrs["idp:mail"][0]:"" );
		$firstname = ( ( isset($attrs["idp:givenName"]) === true && count($attrs["idp:givenName"]) > 0 )?$attrs["idp:givenName"][0]:"" );
		$lastname = ( ( isset($attrs["idp:sn"]) === true && count($attrs["idp:givenName"]) > 0 )?$attrs["idp:sn"][0]:"" );
		$name = trim($firstname . ' ' . $lastname);

		//Clear any vo contact and membership information regarding given persisted uid
		db()->query("SELECT clear_egiaai_user_info(?)", array($puid))->fetchAll();

		$entitlements = self::validateEntitlements($user, $entitlements);

		//Check if entitlements have VO specific information
		if($entitlements && isset($entitlements['vos'])) {
			$voentitlements = $entitlements['vos'];

			//Get extracted vo contatcs from vo entitlements
			if(isset($voentitlements['contacts'])) {
				$vocontacts = $voentitlements['contacts'];
			}

			//Get extracted vo memberships from vo entitlements
			if(isset($voentitlements['members'])) {
				$vomembers = $voentitlements['members'];
			}

			//Update the VO memberships for the given EGI AAI persistend uid.
			foreach($vomembers as $vomember) {
				db()->query("SELECT add_egiaai_user_vomember_info(?, ?, ?)", array($puid, $name, $vomember['vo']))->fetchAll();
			}

			//Update the VO contacts for the given EGI AAI persistend uid.
			foreach($vocontacts as $vocontact) {
				db()->query("SELECT add_egiaai_user_vocontact_info(?, ?, ?, ?, ?)", array($puid, $name, $vocontact['vo'], $vocontact['role'], $email))->fetchAll();
			}
		}

		return $entitlements;
	}
	
	//Performs actions after successful SAML Authedication
	//Decides if the authedicated user is a new or an old
	//user and fills the session accordingly.
	//Returns the url before authedication initialization.
	public static function setupSamlAuth($session){
		$attrs = $session->samlattrs;
		$source = strtolower(trim($session->samlauthsource));
		$uid = ( isset($attrs["idp:uid"])?$attrs["idp:uid"][0]:"");

		if( trim($uid) == "" ) return false;
		$accounttype = str_replace("-sp","",$source);
		
		$useraccount = self::getUserAccount($uid, $accounttype);
		$user = self::getUserByAccount($useraccount);
		
		//Handle empty user
		if( $user === null ){
			if( $accounttype === "egi-sso-ldap" ){ 
				//Connect egi sso account to an existing x509 account where idp:userCertificateSubject == x509 accountid
				//and get the related profile. In case of a new user returns null
				$user = self::connectEgiToX509($session);
			}else if ( $accounttype === "x509" ){
				//Connect x509 account to an existing egi sso account where idp:egiuid == egi sso accountid
				//and get the related profile. In case of a new user returns null
				$user = self::connectX509ToEgi($session);
			}
		}

		if(isset($attrs['idp:traceidp'])) {
			$session->idptrace = $attrs['idp:traceidp'];
		} else {
			$session->idptrace = array();
		}
		
		if(isset($attrs['idp:loa'])) {
			$session->loa = $attrs['idp:loa'];
			if(is_array($session->loa) && count($session->loa) > 0) { 
				$session->loa = $session->loa[0];
			}
		}
		
		//Create a new dunmmy user account model
		if( $useraccount === null ){
			$useraccount = new Default_Model_UserAccount();
			$useraccount->accountid = $uid;
			$useraccount->accounttypeid = $accounttype;
			$useraccount->stateid = 1;
			$useraccount->IDPTrace = $session->idptrace;
			if( $user !== null ){
				$useraccount->researcherid = $user->id;
			}
		}
		
		if( $user!==null && $user->id ){
			if($accounttype !== 'egi-aai') {
				self::harvestSamlData($session, $user);
			}
			self::setupSamlSession($session, $useraccount, $user);
			if( $_COOKIE["SimpleSAMLAuthToken"] ){
				self::setupSamlUserCredentials($user, $session);
			}
		}else{
			self::setupSamlNewUserSession($session, $accounttype);
		}
		
		//Store user entitlements
		$session->entitlements = self::extractSamlEntitlements($attrs);
		if($accounttype === 'egi-aai') {
			$session->entitlements = self::updateEGIAAIEntitlements($attrs, $session->entitlements, $user);
		}

		//Check if user account is blocked and updates session
		self::setupUserAccountStatus($session, $useraccount);
		
		$session->authSource = $source;
		$session->authUid = $uid;
		$session->logoutUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/saml/logout?source=' . $source;
		$session->loginUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/saml/saml?source=' . $source;
		$session->cancelUrl = 'https://' . $_SERVER["HTTP_HOST"] . "/saml/cancelregistration";
		if( trim($session->authreferer) === "" ){
			return 'https://' . $_SERVER['HTTP_HOST'];
		}
		return $session->authreferer;
	}
	
	//Clears the session and any pending account connection
	public static function cancelRegistrationProcess($session){
		$logouturl = ( ( trim($session->logoutUrl)!=="" )?$session->logoutUrl:$session->authreferer );
		if( trim($logouturl) === "" ){
			$logouturl = 'https://' . $_SERVER['HTTP_HOST'];
		}
		AccountConnect::cancelPendingConnection($session);
		self::logout($session);
		return $logouturl;
	}
	
	//Clears session and cookies
	public static function logout($session = null){
		self::clearUserCredentails($session);
		Zend_Session::destroy(true);
		clearAuthCookies();
		@session_regenerate_id(FALSE);
		session_unset();
	}
}
class AccountConnect {
	//Check if a call to AccountConnect is valid
	//It is only available for signed in accounts without a profile.
	public static function isValid($session){
		//Check if session is filled
		if( !$session || isset($session->userid) === false || trim($session->authUid) == "" || trim($session->authSource) == "" ) return false;
		if( $session->userid > 0 ) return false;
		return true;
	}
	
	//Checks if the current user account is connected in a profile.
	//Useful to inform current session if a change happened from another session,
	//if the user has opened more than one sessions with the current account
	//Returns false or the connected profile
	public static function isConnected($session){
		
		$uid = trim($session->authUid);
		$source = str_replace( "-sp", "", trim($session->authSource) );
		
		$uaccounts = new Default_Model_UserAccounts();
		$f1 = new Default_Model_UserAccountsFilter();
		$f2 = new Default_Model_UserAccountsFilter();
		$f1->accountid->_escape_seq = "";
		$f1->accountid->equals($uid);
		$f2->account_type->equals($source);
		$uaccounts->filter->chain($f1, "AND");
		$uaccounts->filter->chain($f2, "AND");
		if( count($uaccounts->items) === 0 ){
			return false;
		}
		$uaccount = $uaccounts->items[0];
		$researcher = $uaccount->getResearcher();
		
		return $researcher;
	}
	
	//Checks if the current user has a user account with the given values.
	//Returns false or the connected user account
	public static function isConnectedTo($session,$uid,$accounttype){
		$userid = trim($session->userid);
		$uid = trim($uid);
		$source = str_replace( "-sp", "", trim($accounttype) );
		
		$uaccounts = new Default_Model_UserAccounts();
		$f1 = new Default_Model_UserAccountsFilter();
		$f2 = new Default_Model_UserAccountsFilter();
		$f3 = new Default_Model_UserAccountsFilter();
		
		$f1->researcherid->equals($userid);
		$f2->accountid->_escape_seq = "";
		$f2->accountid->equals($uid);
		$f3->account_type->equals($source);
		$uaccounts->filter->chain($f1, "AND");
		$uaccounts->filter->chain($f2, "AND");
		$uaccounts->filter->chain($f3, "AND");
		if( count($uaccounts->items) > 0 ){
			return $uaccounts->items[0];
		}
		return false;
	}
	
	//Retrieves pending connection based on account uid and type
	private static function getPendingConnection($accountuid, $accounttype){
		$paccounts = new Default_Model_PendingAccounts();
		$f1 = new Default_Model_PendingAccountsFilter();
		$f2 = new Default_Model_PendingAccountsFilter();
		$f3 = new Default_Model_PendingAccountsFilter();
		$f4 = new Default_Model_PendingAccountsFilter();
		
		$f1->accountid->_escape_seq = "";
		$f1->accountid->equals($accountuid);
		$f2->account_type->equals($accounttype);
		$f3->resolved->equals(false);
		$f4->setExpr("pending_accounts.addedon > NOW() - '30 minutes'::INTERVAL");
		$paccounts->filter->chain($f1, "AND");
		$paccounts->filter->chain($f2, "AND");
		$paccounts->filter->chain($f3, "AND");
		$paccounts->filter->chain($f4, "AND");
		$paccounts->filter->orderBy("addedon DESC");
		if( count($paccounts->items) === 0 ){
			return null;
		}
		return $paccounts->items[0];
	}
	
	//Setup session as a peding connection account
	public static function updateSessionAsPending($session, $pending){
		$session->accountStatus = "pendingconnect";
		$researcher = $pending->getResearcher();
		$session->accountPendingId = $pending->id;
		$session->accountPendingProfileId = $researcher->id;
		$session->accountPendingProfileName = $researcher->name;
	}
	
	//Removes pending connection for current account.
	public static function cancelPendingConnection($session){
		if( self::isValid($session) === false ) return false;
		$uid = trim($session->authUid);
		$source = str_replace( "-sp", "", trim($session->authSource) );
		$pendingaccount = self::getPendingConnection($uid, $source);
		if( $pendingaccount !== null ){
			$pends = new Default_Model_PendingAccounts();
			if( count($pends->items) > 0 ){
				$pends->remove($pendingaccount);
			}
		}
		if( $session->accountStatus === "pendingconnect" ){
			unset($session->accountStatus);
		}
		unset($session->accountPendingId);
		unset($session->accountPendingProfileId);
		unset($session->accountPendingProfileName);
	}
	
	//Checks if there is a pending connection request for this account
	//Useful to inform current session if a change happened from another session,
	//if the user has opened more than one session with the current account.
	//Returns true or false
	public static function isPending($session){
		if( self::isValid($session) === false ) return false;
		$uid = trim($session->authUid);
		$source = str_replace( "-sp", "", trim($session->authSource) );
		
		$pendingaccount = self::getPendingConnection($uid, $source);
		if( $pendingaccount === null ){
			//Clear session related variables
			if( $session->accountStatus === "pendingconnect" ){
				unset($session->accountStatus);
			}
			unset($session->accountPendingId);
			unset($session->accountPendingProfileId);
			unset($session->accountPendingProfileName);
			return false;
		}		
		//In case it is pending set the account status in current session as "pendingconnect"
		self::updateSessionAsPending($session, $pendingaccount);
		return true;
	}
	
	//Create a request and send confirmation email
	public static function requestAccountConnection($session, $profile){
		if( self::isValid($session) === false ) return false;
		$uid = trim($session->authUid);
		$source = str_replace( "-sp", "", trim($session->authSource) );
		
		$ispending = self::isPending($session);
		if( $ispending === true ){
			return true;
		}
		
		//Save pending account entry
		$pending = new Default_Model_PendingAccount();
		$pending->researcherid = $profile->id;
		$pending->accountid = $uid;
		$pending->accountType = $source;
		$pending->accountName = trim($session->userFirstName . " " . $session->userLastName);
		$pending->save();
		
		//make sure you get the pending account item data from race conditions
		$try_count = 0;
		while( $try_count < 10 ){
			$paccounts = new Default_Model_PendingAccounts();
			$paccounts->filter->id->equals($pending->id);
			if( count($paccounts->items) > 0 ){
				$pending = $paccounts->items[0];
				break;
			}
			$try_count += 1;
			sleep(1);
		}
		
		self::updateSessionAsPending($session, $pending);
		self::sendConfirmationEmail($session, $pending);
		
		return true;
	}
	
	//Dispatch an email with the confirmation code to the profile primary email 
	//the pending connection reqeest refers to.
	private static function sendConfirmationEmail($session, $pending){
		$researcher = $pending->getResearcher();
		$email = $researcher->getPrimaryContact();
		
		$accounttype = str_replace("-", " ", trim($pending->accountType));
		$accountname = ( ( trim($pending->accountName) === "" )?$pending->accountID:$pending->accountName );
		
		$res = "Dear " . $researcher->name . ",\n\n";

		$res .= "    a request has been made to connect the " .  $accounttype . " account of " . $accountname . "\n";
		$res .= "to your profile in the EGI Applications Database [1].\n";
		$res .= "If it is really you the one that made this request and you wish to proceed with the account connection\n";
		$res .= " - go to the EGI Applications Database Portal[1] and\n";
		$res .= " - sign in with the same " . $accounttype . " account.\n";
		$res .= "The system will prompt you with a form where you should enter the confirmation code bellow:\n\n";
		$res .= "   Confirmation Code: " . $pending->code . "\n\n";
		$res .= "Note: The confirmation code expires 30 minutes after this message was sent.\n\n";
		$res .= "If you are not the one that made this request, then please report the incident by replying to this message.\n\n";	

		$res .= "Best regards,\n";
		$res .= "EGI AppDB team\n";
		$res .= "\n\n__________________________________________________\n";
		$res .= "[1]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"];
		
		$subject = "EGI AppDB: Request to connect " . $accounttype . " account to your profile";
		$text = $res;
		$body = $body = preg_replace("/\n/", "<br/>", $res);
		$body = "<div>" . $body . "</div>";
		
		//DEVELOPMENT CODE
		if(ApplicationConfiguration::isProductionInstance() === FALSE  ){
			error_log("\nSending to: " . $email);
			error_log("\n\n" . $res);
		}
		$recipients = array($email);
		//sendMultipartMail($subject,$recipients,$text,$body,'appdb-reports@iasa.gr','enadyskolopassword','appdb-support@iasa.gr',null, false,array("Precedence"=>"bulk"));
		EmailService::sendBulkReport($subject, $recipients, $text, $body, EmailConfiguration::getSupportAddress());
	}
	
	//Connect the given profile id to the user account information given
	public static function connectAccountToProfile($profileid, $id, $type, $name = null, $idptrace = array()){
		//Check if this user account is already connected to a profile
		$user = SamlAuth::getUserByAccountValues($id, $type);
		if( $user !== null ){
			return;
		}
		
		$uaccount = new Default_Model_UserAccount();
		$uaccount->researcherID = $profileid;
		$uaccount->accountID = $id;
		$uaccount->accountTypeID = $type;
		$uaccount->accountName = $name;
		$uaccount->IDPTrace = $idptrace;
		$uaccount->save();
		
		$try_count = 0;
		while($try_count < 10){
			$uaccounts = new Default_Model_UserAccounts();
			$uaccounts->filter->id->equals($uaccount->id);
			if( count($uaccounts->items) > 0 ){
				break;
			}
			$try_count += 1;
			sleep(1);
		}
		
	}
	
	//Check if given code is the same as the penfing connection request
	//If not retrun false. If true resolve pending request, connect the account
	//update the session and return true
	public static function submitPendingConnectionCode($session, $code){
		if( self::isValid($session) === false ) return false;
		$uid = trim($session->authUid);
		$source = str_replace( "-sp", "", trim($session->authSource) );
		$paccount = self::getPendingConnection($uid, $source);
		if( !$paccount ) return false;
		
		if( trim($paccount->code) !== trim($code) ){
			return false;
		}
		
		$paccount->resolved = true;
		$paccount->resolvedOn = 'NOW()';
		$paccount->save();
		
		self::connectAccountToProfile( $paccount->researcherid, $paccount->accountID, $paccount->accountType, $paccount->accountName, $session->idptrace );
		
		unset($session->isNewUser);
		unset($session->accountStatus);
		unset($session->accountPendingId);
		unset($session->accountPendingProfileId);
		unset($session->accountPendingProfileName);
		
		SamlAuth::setupSamlAuth($session);
		return true;
	}
	
	public static function disconnectAccount($session, $account){
		if( is_null($account) ) return;
		if( $session->userid !== $account->researcherid ) return;
		$accs = new Default_Model_UserAccounts();
		$accs->filter->id->equals($account->id);
		if( count($accs->items) > 0 ){
			$accs->remove($accs->items[0]);
		}
	}
}


class AccessGroups{
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
	 * Retrieve user's access group list.
	 * 
	 * @param \Default_Model_Researcher|integer $user User id or instanceof Default_Model_Researcher object.
	 * @return \Default_Model_ActorGroupMember[]|false The access group array of the given user. Returns false on error.
	 */
	public static function getUserAccessGroups($user=null){
		$user = self::getUser($user);
		if( $user instanceof Default_Model_Researcher ){
			return $user->getActorGroups();
		}
		return false;
	}
	/**
	 * Checks if two access groups are equal.
	 * 
	 * @param \Default_Model_ActorGroupMember $source
	 * @param \Default_Model_ActorGroupMember $target
	 * @retrun boolean
	 */
	public static function equalAccessGroups($source=null,$target=null){
		if( is_null($source) || is_null($target) ){
			return false;
		}
		
		if( trim($source->group->id) !== trim($target->group->id) ){
			return false;
		}
		if( trim($source->payload) !== trim($target->payload) ){
			return false;
		}
		if( trim($source->actorguid) !== trim($target->actorguid) ){
			return false;
		}
		return true;
	}
	/**
	 * Check if user belongs to all given access groups.
	 * 
	 * @access public
	 * @param \Default_Model_Researcher $user User id or instanceof Default_Model_Researcher object.
	 * @param integer[] $accessgroups Array of access group ids.
	 * @return boolean True:if user belongs to all given access groups, False: if user belongs to some or none of the given access groups.
	 */
	public static function inAllAccessGroups( $user=null, $accessgroups = array() ){
		if( ( is_array($accessgroups) && count($accessgroups) === 0 ) || is_null($accessgroups) ){
			return false;
		}
		
		if( is_array($accessgroups) === false ){
			$accessgroups = array($accessgroups);
		}
		$userAccessGroups = self::getUserAccessGroups($user);
		if( count($userAccessGroups) === 0){
			return false;
		}
		$hasAll = true;
		foreach($accessgroups as $ac){
			$found = false;
			foreach($userAccessGroups as $uag){
				if( trim($uag->groupid) === trim($ac) ){
					$found = true;
					break;
				}
			}
			if( $found === false ){
				$hasAll = false;
			}
		}
		return $hasAll;
	}
	/**
	 * Check if user belongs at least in one of the given acccess groups.
	 * 
	 * @access public
	 * @param \Default_Model_Researcher|integer $user User id or instanceof Default_Model_Researcher object.
	 * @param \Default_Model_ActorGroupMember[]|integer[] $accessgroups Array of access group ids or instance of Default_Model_ActorGroupMembers.
	 * @return boolean True:if user belongs to all given access groups, False: if user belongs to some or none of the given access groups.
	 */
	public static function inSomeAccessGroups( $user=null, $accesstgroups=array() ){
		if( ( is_array($accessgroups) && count($accessgroups) === 0 ) || is_null($accessgroups) ){
			return false;
		}
		
		if( is_array($accessgroups) === false ){
			$accessgroups = array($accessgroups);
		}
		$userAccessGroups = self::getUserAccessGroups($user);
		if( count($userAccessGroups) === 0){
			return false;
		}
		$hasSome = false;
		foreach($userAccessGroups as $uag){
			if( in_array($uaq->group->id, $accessgroups) === true){
				$hasSome = true;
				break;
			}
		}
		return $hasSome;
	}
	/**
	 * Retrieves an actor group based on its guid.
	 * 
	 * @param string $guid
	 * @return Default_Model_ActorGroup|null
	 */
	public static function getGroupByGUID($guid){
		if( trim($guid) === "" ) {
			return null;
		}
		$groups = new Default_Model_ActorGroups();
		$groups->filter->guid->equals($guid);
		if( count($groups->items) > 0 ){
			return $groups->items[0];
		}
		return null;
	}
	public static function getGroupById($id){
		if( trim($id) === "" ) {
			return null;
		}
		$groups = new Default_Model_ActorGroups();
		$groups->filter->id->equals($id);
		if( count($groups->items) > 0 ){
			return $groups->items[0];
		}
		return null;
	}
	private static function cancelAccessGroupRequest($sourceUser, $targetUser, $id){
		error_log("[AccessGroups::cancelAccessGroupRequest]: Canceling user request with id: " . $id);
		
		$userrequests = new Default_Model_UserRequests();
		$userrequests->filter->id->equals($id);
		if( count($userrequests->items) === 0 ){
			return false;
		}
		$userrequest = $userrequests->items[0];
		
		//Cancel only if request is in the state "submitted". 
		//Any other case means that in the meantime another 
		//user has accepted or rejected it.
		if( $userrequest->stateid === 1 ){
			$userrequest->actorguid = $targetUser->guid;
			$userrequest->stateid = 4;
			$userrequest->save();
		}
		
		return true;
		
	}
	private static function rejectAccessGroupRequest($sourceUser, $targetUser, $id){
		error_log("[AccessGroups::rejectAccessGroupRequest]: Rejecting user request with id: " . $id);
		
		$userrequests = new Default_Model_UserRequests();
		$userrequests->filter->id->equals($id);
		if( count($userrequests->items) === 0 ){
			return false;
		}
		
		$userrequest = $userrequests->items[0];
		
		//Reject only if request is in the state "submitted". 
		//Any other case means that in the meantime another 
		//user has accepted,rejected or cancelled it.
		if( $userrequest->stateid === 1 ){
			$userrequest->actorguid = $sourceUser->guid;
			$userrequest->stateid = 3;
			$userrequest->save();
		}
		
		error_log("[AccessGroups::rejectAccessGroupRequest]: Sending reject email to user " . $targetUser->cname);
		$group = self::getGroupByGUID($userrequest->targetguid);
		UserRequests::sendEmailResponseAccessGroupsNotification($targetUser,$group, 3);
		return true;
	}
	private static function acceptAccessGroupRequest($sourceUser, $targetUser, $id){
		error_log("[AccessGroups::acceptAccessGroupRequest]: Accepting user request with id: " . $id);
		
		$userrequests = new Default_Model_UserRequests();
		$userrequests->filter->id->equals($id);
		if( count($userrequests->items) === 0 ){
			return false;
		}
		$userrequest = $userrequests->items[0];
		
		//Accept only if request is in the state "submitted". 
		//Any other case means that in the meantime another 
		//user has accepted,rejected or cancelled it.
		if( $userrequest->stateid === 1 ){
			$userrequest->actorguid = $sourceUser->guid;
			$userrequest->stateid = 2;
			$userrequest->save();
		}else{
			return true;
		}
		
		error_log("[AccessGroups::acceptAccessGroupRequest]: Sending accept email to user " . $targetUser->cname);
		$group = self::getGroupByGUID($userrequest->targetguid);
		UserRequests::sendEmailResponseAccessGroupsNotification($targetUser,$group, 2);
		return true;
	}
	/**
	 * Returns an array with the user request id and the access group id.
	 * 
	 * @param \Default_Model_Researcher|integer $user The user with access groups requests.
	 * @return array
	 */
	public static function getAccessGroupRequests($user,$groupid=null){
		$result = array();
		if( $user===null ) return $result;
		$user = self::getUser($user);
		$userrequests = new Default_Model_UserRequests();
		$f1 = new Default_Model_UserRequestsFilter();
		$f2 = new Default_Model_UserRequestsFilter();
		$f3 = new Default_Model_UserRequestsFilter();
		$f4 = new Default_Model_UserRequestsFilter();
		
		$f1->stateid->equals(1);//submitted
		$f2->userguid->equals($user->guid); //from given user
		$f3->typeid->equals(3);//access group request
		
		$userrequests->filter->chain($f1, "AND");
		$userrequests->filter->chain($f2, "AND");
		$userrequests->filter->chain($f3, "AND");
		
		if( $groupid !== null ){
			$group = self::getGroupById($groupid);
			if( $group == null ){
				return $result;
			}
			$f4->targetguid->equals($group->guid);
			$userrequests->filter->chain($f4, "AND");
		}
		
		if( count($userrequests->items) === 0 ){
			return $result;
		}
		
		foreach($userrequests->items as $userrequest){
			$group = self::getGroupByGUID($userrequest->targetguid);
			if( $group === null) {
				continue;
			}
			$result[] = array("requestid"=>$userrequest->id, "groupid" => $group->id);
		}
		return $result;
	}
	/**
	 * Check if $sourceUser can perform an access group action for $targetUser based on $accesspermissions
	 * 
	 * @param Default_Model_Researcher $sourceUser User profile object.
	 * @param Default_Model_Researcher $targetUser User profile object.
	 * @param text $action The action to check can take values of "canAdd","canRemove","canRequest","canCancel","canAcceptReject".
	 * @param integer $groupId The id of the action access group.
	 * @param {id, name, canAdd, canRemove, canRequest, canAcceptReject, hasRequest}[] $accesspermissions Array of $sourceUser's access groups permissions upon $targetUser.
	 * @return boolean True if $sourceUser can perform access group action upon $targetUser
	 */
	private static function canPerformAction($sourceUser, $targetUser, $action, $groupid, $accesspermissions){
		$group = null;
		//Find group permission entry from access permissions 
		foreach($accesspermissions as $ap){
			if( trim($ap["id"]) === trim($groupid) ){
				$group = $ap;
				break;
			}
		}
		
		//If group not found then no permissions
		if( $group === null ){
			return false;
		}
		$action = strtolower( trim($action) );
		switch($action){
			case "cancel":
				if( $sourceUser->id === $targetUser->id && $group["canRequest"] === true && is_numeric($group["hasRequest"]) === true) {
					return true;
				}
				return false;
			case "request":
				if( $sourceUser->id === $targetUser->id && $group["canRequest"] === true && $group["hasRequest"] === false) {
					return true;
				}
				return false;
			case "include":
				if( $group["canAdd"] === true && self::inAllAccessGroups($targetUser, array($group["id"])) === false  ){
					return true;
				}
				return false;
			case "exclude":
				if( $group["canRemove"] === true && self::inAllAccessGroups($targetUser, array($group["id"])) === true ) {
					return true;
				}
				return false;
			case "accept":
				if( $group["canAcceptReject"] === true && $group["hasRequest"] !== false ) {
					return true;
				}
				return false;
			case "reject":
				if( $group["canAcceptReject"] === true && $group["hasRequest"] !== false ) {
					return true;
				}
				return false;
			default:
				return false;
		}
	}
	/**
	 * 
	 * @param Default_Model_Researcher|integer $sourceUser The user to check the edit access group permissions of another user.
	 * @param Default_Model_Researcher|integer $targetUser The user whose access groups will be edited.
	 * @param {requestid,groupid}[] $userquests An array with submitted $targetUser access group requests.
	 * @return {id, name, canAdd, canRemove, canRequest, canAcceptReject, hasRequest}[] An array of the $sourceUser permissions to edit the access groups of $targetUser.
	 */
	public static function getAccessGroupsPermissions($sourceUser, $targetUser, $userrequests=array()){
		/* Return array with the groups the target is able to have. Filled with the permissions of the source or target for these groups.
		 * id: access group id
		 * name: access group name
		 * canAdd: source can add target to this access group
		 * canRemove: source can remove target from this access group
		 * canAcceptReject: accept or reject targets requests from $sourceUser to be included in this group
		 * canRequest: target can request to be included in this access group
		 * hasRequest: target user's access group request id if any.the user access group request id if any made by the target user. By default false. 
		 */
		$result = array();
		if( $sourceUser === null || $targetUser === null ){ return array(); }
		
		$sourceUser = self::getUser($sourceUser);
		$targetUser = self::getUser($targetUser);
		$sameuser = false;
		if( $sourceUser->id === $targetUser->id){
			$sameuser = true;
		}
		
		if( self::inAllAccessGroups($sourceUser,array("-1")) === true ) {
			//Administator can do anything except requesting for access group(no reason to do so)
			//$sourceIsAdmin = self::inAllAccessGroups($sourceUser,array("-1"));
			$result = array(
				array("id"=>"-1", "name"=>"AppDB Administrator", "canAdd"=>true, "canRemove"=>(($sameuser)?true:false),"canRequest"=>false, "canAcceptReject"=>true, "hasRequest"=>false),
				array("id"=>"-2", "name"=>"Managers", "canAdd"=>true, "canRemove"=>true,"canRequest"=>false, "canAcceptReject"=>true, "hasRequest"=>false),
				array("id"=>"-3", "name"=>"National Representatives", "canAdd"=>true, "canRemove"=>true, "canRequest"=>false, "canAcceptReject"=>true, "hasRequest"=>false),
			);
			if( Supports::datasets() ) {
				$result[] = array("id"=>"-19", "name"=>"Dataset Managers", "canAdd"=>true, "canRemove"=>true, "canRequest"=>false, "canAcceptReject"=>true, "hasRequest"=>false);
			}
		}else if( self::inAllAccessGroups($sourceUser,array("-2")) === true ){
			//Manager can do anything except removing an other manager
			$result = array(
				array("id"=>"-2", "name"=>"Managers", "canAdd"=>true, "canRemove"=>(($sameuser)?true:false),"canRequest"=>false, "canAcceptReject"=>true, "hasRequest"=>false),
				array("id"=>"-3", "name"=>"National Representatives", "canAdd"=>true, "canRemove"=>true, "canRequest"=>false, "canAcceptReject"=>true, "hasRequest"=>false),
			);
			if( Supports::datasets() ) {
				//Managers can edit datasets by default
				$result[] = array("id"=>"-19", "name"=>"Dataset Managers", "canAdd"=>true, "canRemove"=>true, "canRequest"=>false, "canAcceptReject"=>true, "hasRequest"=>false);
			}
		}else if (self::inAllAccessGroups ($sourceUser, array ("-3")) === true ) {
			//NILS can add other NILS, remove their self or request to become managers
			$sourceCountry = $sourceUser->countryid;
			$targetCountry = $targetUser->countryid;
			$samecountry = ( (trim($targetCountry) === trim($sourceCountry))?true:false );
			$result = array(
				array("id"=>"-2", "name"=>"Managers", "canAdd"=>false, "canRemove"=>false, "canRequest"=>(($sameuser)?true:false), "canAcceptReject"=>false, "hasRequest"=>false),
				array("id"=>"-3", "name"=>"National Representatives", "canAdd"=>(($samecountry)?true:false), "canRemove"=>(($sameuser)?true:false), "canRequest"=>false, "canAcceptReject"=>(($samecountry)?true:false), "hasRequest"=>false),
			);
			
			if( Supports::datasets() ) {
				//A NIL can request to be added to group of dataset managers
				$result[] = array("id"=>"-19", "name"=>"Dataset Managers", "canAdd"=>false, "canRemove"=>false, "canRequest"=>(($sameuser)?true:false), "canAcceptReject"=>false, "hasRequest"=>false);
			}
		}else{
			//All other users can only request to become managers or NILs
			$result = array(
				array("id"=>"-2", "name"=>"Managers", "canAdd"=>false, "canRemove"=>false, "canRequest"=>(($sameuser)?true:false), "canAcceptReject"=>false, "hasRequest"=>false),
				array("id"=>"-3", "name"=>"National Representatives", "canAdd"=>false, "canRemove"=>false, "canRequest"=>(($sameuser)?true:false), "canAcceptReject"=>false, "hasRequest"=>false),
			);
			if( Supports::datasets() ) {
				//Anyone can request to be added to group of dataset managers
				$result[] = array("id"=>"-19", "name"=>"Dataset Managers", "canAdd"=>false, "canRemove"=>false, "canRequest"=>(($sameuser)?true:false), "canAcceptReject"=>false, "hasRequest"=>false);
			}
		}
		
		//Check if user requests are given
		if( is_null($userrequests) || count($userrequests) === 0 ){
			$userrequests = self::getAccessGroupRequests($targetUser);
		}
		
		//Fill results with user access groups requests
		foreach($userrequests as $ur){
			for($i=0; $i<count($result);  $i+=1){
				if( $result[$i]["id"] === trim($ur["groupid"]) ){
					$result[$i]["hasRequest"] = $ur["requestid"];
					break;
				}
			}
		}
		
		return $result;
	}
	/**
	 * Handles access group actions based on $action.
	 * 
	 * @param Default_Model_Researcher|integer $sourceUser User profile object or id.
	 * @param Default_Model_Researcher|integer $targetUser User profile object or id.
	 * @param text $action The handled action which can take values of "include","exclude","request","cancel","accept" or "reject".
	 * @param integer[] $groupIds The ids of the access groups.
	 * @param {id, name, canAdd, canRemove, canRequest, canAcceptReject, hasRequest}[] $accesspermissions Optional array of $sourceUser's access groups permissions.
	 * @return boolean|string True on success, text message on error, False on unknown error.
	 */
	public static function handleUserGroupAction($sourceUser, $targetUser, $action, $groupIds = array(), $accesspermissions = null){
		if( is_array($groupIds) === false ) {
			if( is_numeric($groupIds) === true ) {
				$groupIds = array($groupIds);
			} else {
				return true;
			}
		}
		if( count($groupIds) === 0 || trim($action) === "" ) { return true; }
		
		$action = strtolower( trim($action) );
		$sourceUser = self::getUser($sourceUser);
		$targetUser = self::getUser($targetUser);
		
		if( $sourceUser === null || $targetUser === null ){ return false; }
		
		if( $accesspermissions === null ) {
			$accesspermissions = self::getAccessGroupsPermissions($sourceUser, $targetUser);
		}
		
		switch( $action ) {
			case "include": //include to groups of ids
				return self::includeUserInGroups($sourceUser, $targetUser, $groupIds, $accesspermissions);
			case "exclude": //exclude from groups of ids
				return self::excludeUserInGroups($sourceUser, $targetUser, $groupIds, $accesspermissions);
			case "request": //make request to be included in group ids (same user only)
				return self::requestForGroups($sourceUser, $targetUser, $groupIds, $accesspermissions);
			case "cancel": //cancel user's request to be included in group ids (same user only)
				return self::cancelRequestForGroups($sourceUser, $targetUser, $groupIds, $accesspermissions);
			case "accept": //accept a user's request to be included in group ids
				return self::acceptRequestForGroups($sourceUser, $targetUser, $groupIds, $accesspermissions);
			case "reject": //reject a user's request to be included in group ids 
				return self::rejectRequestForGroups($sourceUser, $targetUser, $groupIds, $accesspermissions);
			default:
				return false;
		}
	}
	/**
	 * Include $targetUser in access groups given by $groupids by the $sourceUser.
	 * 
	 * @param Default_Model_Researcher|integer $sourceUser User profile object or id.
	 * @param Default_Model_Researcher|integer $targetUser User profile object or id.
	 * @param integer[] $groupIds The ids of the access groups.
	 * @param {id, name, canAdd, canRemove, canRequest, canAcceptReject, hasRequest}[] $accesspermissions Optional array of $sourceUser's access groups permissions.
	 * @return boolean|string True on success, text message on error, False on unknown error.
	 */
	private static function includeUserInGroups($sourceUser, $targetUser, $groupIds, $accesspermissions) {
		$res = array();
		foreach($groupIds as $gid){
			$g = array($gid=>self::canPerformAction($targetUser, $targetUser, "include", $gid, $accesspermissions));
			$res[] = $g;
			if( $g[$gid] !== true ){
				continue;
			}
			//Check if user is already in access group, then ignore
			if( self::inAllAccessGroups($targetUser, array($gid)) === true ){
				continue;
			}
			$actormember = new Default_Model_ActorGroupMember();
			$actormember->groupID = $gid;
			$actormember->actorGUID = $targetUser->guid;
			if( trim($gid) === "-3"){
				$actormember->payload = $targetUser->countryID;
			}
			$actormember->save();
			
			//if targetuser has a pending reqeust to join current group, then accept the request
			foreach($accesspermissions as $ap){
				if( trim($ap["id"]) === trim($gid) && is_numeric($ap["hasRequest"]) && intval($ap["hasRequest"]) > 0 ) {
					self::acceptAccessGroupRequest($sourceUser, $targetUser, intval($ap["hasRequest"]));
				}
			}
		}
		return $res;
	}
	/**
	 * Exclude $targetUser from access groups given by $groupids by the $sourceUser.
	 * 
	 * @param Default_Model_Researcher|integer $sourceUser User profile object or id.
	 * @param Default_Model_Researcher|integer $targetUser User profile object or id.
	 * @param integer[] $groupIds The ids of the access groups.
	 * @param {id, name, canAdd, canRemove, canRequest, canAcceptReject, hasRequest}[] $accesspermissions Optional array of $sourceUser's access groups permissions.
	 * @return boolean|string True on success, text message on error, False on unknown error
	 */
	private static function excludeUserInGroups($sourceUser, $targetUser, $groupIds, $accesspermissions) {
		$res = array();
		foreach($groupIds as $gid){
			$g = array($gid=>self::canPerformAction($targetUser, $targetUser, "exclude", $gid, $accesspermissions));
			$res[] = $g;
			if( $g[$gid] !== true ) {
				continue;
			}
			
			$actormembers = new Default_Model_ActorGroupMembers();
			$f1 = new Default_Model_ActorGroupMembersFilter();
			$f2 = new Default_Model_ActorGroupMembersFilter();
			$f3 = new Default_Model_ActorGroupMembersFilter();
			$f1->groupid->equals($gid);
			$f2->actorid->equals($targetUser->guid);
			$actormembers->filter->chain($f1, "AND");
			$actormembers->filter->chain($f2, "AND");
			if( trim($gid) === "-3" ){
				$f3->payload->equals(trim($targetUser->countryID));
				$actormembers->filter->chain($f3, "AND");
			}
			if( count($actormembers->items) > 0 ){
				$am = $actormembers->items[0];
				$actormembers->remove($am);
			}
		}
		return $res;
	}
	/**
	 * Requests of $targetUser to be included in the access groups given by $groupids. ($sourceUser must be $targetUser).
	 * 
	 * @param Default_Model_Researcher|integer $sourceUser User profile object or id.
	 * @param Default_Model_Researcher|integer $targetUser User profile object or id.
	 * @param integer[] $groupIds The ids of the access groups.
	 * @param {id, name, canAdd, canRemove, canRequest, canAcceptReject, hasRequest}[] $accesspermissions Optional array of $sourceUser's access groups permissions.
	 * @return boolean|string True on success, text message on error, False on unknown error.
	 */
	private static function requestForGroups($sourceUser, $targetUser, $groupids, $accesspermissions) {
		if( $sourceUser->id !== $targetUser->id){
			return "Cannot make a user request on behalf of another user";		
		}
		if( is_array($groupids) === false ){
			if( is_numeric($groupids) === false ){
				return false;
			}else{
				$groupids = array($groupids);
			}	
		}
		$res = array();
		foreach($groupids as $gid){
			$g = array($gid=>self::canPerformAction($targetUser, $targetUser, "request", $gid, $accesspermissions));
			$res[] = $g;
			if( $g[$gid] !== true ){
				continue;
			}
			
			//if request exists for this group then return true.
			$ur = self::getAccessGroupRequests($targetUser, $gid);
			if( count($ur) > 0){
				return true;
			}
			
			//If group id does not exist ignore
			$group = self::getGroupById($gid);
			if( $group === null ){
				continue;
			}
			$userrequest = new Default_Model_UserRequest();
			$userrequest->typeid = 3;
			$userrequest->userguid = $targetUser->guid;
			$userrequest->targetguid = $group->guid;
			$userrequest->stateid = 1;
			
			$userrequest->save();
			
			//Dispatch mail to user and managers, appdb administrators and associated NILs
			UserRequests::sendEmailAccessGroupRequestNotifications($targetUser, $group);
		}
		return true;
	}
	/**
	 * Cancel requests of $targetUser to be included in the access groups given by $groupids. ($sourceUser must be $targetUser)
	 * 
	 * @param Default_Model_Researcher|integer $sourceUser User profile object or id.
	 * @param Default_Model_Researcher|integer $targetUser User profile object or id.
	 * @param integer[] $groupIds The ids of the access groups.
	 * @param {id, name, canAdd, canRemove, canRequest, canAcceptReject, hasRequest}[] $accesspermissions Optional array of $sourceUser's access groups permissions.
	 * @return boolean|string True on success, text message on error, False on unknown error.
	 */
	private static function cancelRequestForGroups($sourceUser, $targetUser, $groupids, $accesspermissions) {
		if( $sourceUser->id !== $targetUser->id){
			return "Cannot cancel a user request on behalf of another user";
		}
		$res = array();
		foreach($groupids as $gid){
			$g = array($gid=>self::canPerformAction($targetUser, $targetUser, "cancel", $gid, $accesspermissions));
			$res[] = $g;
			if( $g[$gid] !== true ){
				continue;
			}
			
			//if requests do not exist for this group then ignore.
			$ur = self::getAccessGroupRequests($targetUser, $gid);
			if( count($ur) === 0){
				continue;
			}
			
			//Get access group object
			$group = self::getGroupById($gid);
			if( $group === null ){
				continue;
			}
			
			//cancel each request for this group
			foreach($ur as $req){
				self::cancelAccessGroupRequest($sourceUser, $targetUser, $req["requestid"]);
			}
		}
		return true;	
	}
	/**
	 * Accept requests of $targetUser to be included in the access groups given by $groupids.
	 * 
	 * @param Default_Model_Researcher|integer $sourceUser User profile object or id.
	 * @param Default_Model_Researcher|integer $targetUser User profile object or id.
	 * @param integer[] $groupIds The ids of the access groups.
	 * @param {id, name, canAdd, canRemove, canRequest, canAcceptReject, hasRequest}[] $accesspermissions Optional array of $sourceUser's access groups permissions.
	 * @return boolean|string True on success, text message on error, False on unknown error.
	 */
	private static function acceptRequestForGroups($sourceUser, $targetUser, $groupids, $accesspermissions) {
		if( is_array($groupids) === false ){
			if( is_numeric($groupids) === false ){
				return "no access groups given";
			}else{
				$groupids = array($groupids);
			}
		}
		$res = array();
		foreach($groupids as $gid){
			$g = array($gid=>self::canPerformAction($targetUser, $targetUser, "accept", $gid, $accesspermissions));
			$res[] = $g;
			if( $g[$gid] !== true ){
				continue;
			}
			
			//if requests do not exist for this group then ignore.
			$ur = self::getAccessGroupRequests($targetUser, $gid);
			if( count($ur) === 0){
				continue;
			}
			
			//Get access group object
			$group = self::getGroupById($gid);
			if( $group === null ){
				continue;
			}
			//Include user in group(implicitly accepted by the function includeUserInGroups)
			self::includeUserInGroups($sourceUser, $targetUser, array($gid), $accesspermissions);
		}
		return true;
	}
	/**
	 * Reject requests of $targetUser to be included in the access groups given by $groupids.
	 * 
	 * @param Default_Model_Researcher|integer $sourceUser User profile object or id.
	 * @param Default_Model_Researcher|integer $targetUser User profile object or id.
	 * @param integer[] $groupIds The ids of the access groups.
	 * @param {id, name, canAdd, canRemove, canRequest, canAcceptReject, hasRequest}[] $accesspermissions Optional array of $sourceUser's access groups permissions.
	 * @return boolean|string True on success, text message on error, False on unknown error.
	 */
	private static function rejectRequestForGroups($sourceUser, $targetUser, $groupids, $accesspermissions) {
		if( is_array($groupids) === false ){
			if( is_numeric($groupids) === false ){
				return "no access groups given";
			}else{
				$groupids = array($groupids);
			}
		}
		$res = array();
		foreach($groupids as $gid){
			$g = array($gid=>self::canPerformAction($targetUser, $targetUser, "reject", $gid, $accesspermissions));
			$res[] = $g;
			if( $g[$gid] !== true ){
				continue;
			}
			
			//if requests do not exist for this group then ignore.
			$ur = self::getAccessGroupRequests($targetUser, $gid);
			if( count($ur) === 0){
				continue;
			}
			
			//Get access group object
			$group = self::getGroupById($gid);
			if( $group === null ){
				continue;
			}
			
			//reject each request for this group
			foreach($ur as $req){
				self::rejectAccessGroupRequest($sourceUser, $targetUser, $req["requestid"]);
			}
		}
		return true;
	}
}

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

class VoAdmin{
	private static function getAdminVoMemberships(){
		return array("vo deputy","vo manager","vo expert");
	}
	/*
	 * Returns user's entry for given id or cname
	 */
	public static function getUser($user){
		if( $user === null || ( is_string($user) && trim($user) === "" ) ) {
			return null;
		} else if( is_numeric($user) ){
			$userid = intval($user);
			$users = new Default_Model_Researchers();
			$users->filter->id->equals($userid);
			if( count($users->items) === 0 ){
				return null;
			}
			$user = $users->items[0];
		} else if( is_string($user) && trim($user) !== "" ){
			$usercname = trim($user);
			$users = new Default_Model_Researchers();
			$users->filter->cname->equals($usercname);
			if( count($users->items) === 0 ){
				return null;
			}
			$user = $users->items[0];
		}
		return $user;
	}
	/*
	 * Returns VO entry for given id or name
	 */
	public static function getVo($vo){
		if( $vo === null || ( is_string($vo) && trim($vo) === "" ) ) {
			return null;
		} else if( $vo instanceof Default_Model_VO ){
			return $vo;
		} else if( is_numeric($vo) ){
			$void = intval($vo);
			$vos = new Default_Model_VOs();
			$vos->filter->id->equals($void);
			if( count($vos->items) === 0 ){
				return null;
			}
			$vo = $vos->items[0];
		} else if( is_string($vo) && trim($vo)!=="" ){
			$vos = new Default_Model_VOs();
			$vos->filter->name->equals(trim($vo));
			if( count($vos->items) === 0 ){
				return null;
			}
			$vo = $vos->items[0];
		}
		return $vo;
	}
	/*
	 * Returns the vappliance entry for given id or cname
	 */
	public static function getVAppliance($vappliance){
		if( $vappliance instanceof Default_Model_Application ){
			return $vappliance;
		} else if( $vappliance === null || ( is_string($vappliance) && trim($vappliance) === "" ) ) {
			return null;
		} else if( is_numeric($vappliance) ){
			$vapplianceid = intval($vappliance);
			$vappliances = new Default_Model_Applications();
			$vappliances->filter->id->equals($vapplianceid);
			if( count($vappliances->items) === 0 ){
				return null;
			}
			$vappliance = $vappliances->items[0];
		} else if( is_string($vappliance) && trim($vappliance) !== ""  ){
			$vappliancecname = trim($vappliance);
			$vappliances = new Default_Model_Applications();
			$vappliances->filter->cname->equals($vappliancecname);
			if( count($vappliances->items) === 0 ){
				return null;
			}
			$vappliance = $vappliances->items[0];
		}
		return $vappliance;
	}
	/*
	 * Returns the published version of the given vappliance
	 */
	public static function getVAppVersion($vappliance){
		if( $vappliance instanceof Default_Model_VAversion){
			return $vappliance;
		}
		$appliance = self::getVAppliance($vappliance);
		if( $appliance === null ) {
			return null;
		}
		
		
		$vapplications = new Default_Model_VAs();
		$vapplications->filter->appid->equals($appliance->id);
		if( count($vapplications->items) === 0 ){
			return null;
		}
		$vapplication = $vapplications->items[0];
		
		$vappvers = new Default_Model_VAversions();
		$f1 = new Default_Model_VAversionsFilter();
		$f2 = new Default_Model_VAversionsFilter();
		$f3 = new Default_Model_VAversionsFilter();
		$f1->vappid->equals($vapplication->id);
		$f2->published->equals(true);
		$f3->archived->equals(false);
		$vappvers->filter->chain($f1, "AND");
		$vappvers->filter->chain($f2, "AND");
		$vappvers->filter->chain($f3, "AND");
		
		if( count($vappvers->items) === 0 ){
			return null;
		}
		$vappver = $vappvers->items[0];
		
		return $vappver;
	}
	
	private static function getVAImages($vappliance){
		if( $vappliance instanceof Default_Model_VA ){
			$vappver = self::getVAppVersion($vappliance);
		}else if($vappliance instanceof Default_Model_VAversion) {
			$vappver = $vappliance;
		}else{
			$vappver = null;
		}
		
		if( $vappver === null ){
			return array();
		}
		$result = array();
		
		$vapplists = $vappver->getVappLists();
		if( count($vapplists->items) > 0 ){
			foreach($vapplists->items as $vapplist){
				$vmiinstance = $vapplist->getVMIinstance();
				if( $vmiinstance !== null ){
					$result[] = $vmiinstance;
				}
			}
		}
		
		return $result;
	}
	
	private static function toJSON($arr, $toJSON = true){
		if( $toJSON === false ) return $arr;
		return json_encode($arr);
	}
	
	public static function getUserMembership($researcher, $toJSON = false){
		$res = array();
		$user = self::getUser($researcher);
		if( $user === null ) {
			return self::toJSON($res, $toJSON);
		}
		
		$vomems = $user->getVOMemberships();
		if( count($vomems) === 0 ){
			return self::toJSON($res, $toJSON);
		}
		
		foreach($vomems as $mem){
			if( !$mem->vo ) {
				continue;
			}
			
			$vom = array();
			$vom["id"] = $mem->void;
			$vom["discipline"] = $mem->vo->domain->name;
			$vom["member_since"] = $mem->membersince;
			$vom["name"] = $mem->vo->name;
			array_push($res, $vom);
		}
		return self::toJSON( array_merge($res, self::getVOContacts($user)), $toJSON );
	}
	
	public static function getVOContacts($researcher){
		$res = array();
		$user = self::getUser($researcher);
		if( $user === null ) {
			return $res;
		}
		
		$vomems = $user->getVOContacts();
		if( count($vomems) === 0 ){
			return $res;
		}
		
		foreach($vomems as $mem){
			$vom = array();
			$vom["id"] = $mem->void;
			$vom["discipline"] = $mem->vo->domain->name;
			$vom["name"] = $mem->vo->name;
			$vom["role"] = $mem->role;
			array_push($res, $vom);
		}
		return $res;
	}
	
	public static function canEditVOImageList($researcher, $vo){
		$user = self::getUser($researcher);
		if( $user === null ){
			return false;
		}
		$voitem = self::getVo($vo);
		if( $voitem === null ){
			return false;
		}
		
		if( $user->privs->canManageVOWideImageList($voitem->guid) === true ){
			return true;
		}
		
		return false;
	}
	
	public static function getDraftVoImageList($researcher, $vo, $create = false){
		if ( $create === true ) {
			return self::createDraftVoImageList($researcher, $vo);
		} else {
			$voimglists = new Default_Model_VOWideImageLists();
			$f1 = new Default_Model_VOWideImageListsFilter();
			$f2 = new Default_Model_VOWideImageListsFilter();
			
			$f1->void->numequals($vo->id);
			$f2->state->equals("draft");
			$voimglists->filter->chain($f1, "AND");
			$voimglists->filter->chain($f2, "AND");
			
			if( count($voimglists->items) === 0 ){
				return null;
			} else {
				return $voimglists->items[0];
			}
		}
	}
	
	public static function createDraftVoImageList($researcher, $vo){
		global $application;
		$db = $application->getBootstrap()->getResource('db');
		$db->setFetchMode(Zend_Db::FETCH_BOTH);
		$q = "SELECT edit_vowide_image_list(?,?);";
		$res = $db->query( $q, array($vo->id, $researcher->id) )->fetchAll();
		if( count($res) === 0  ) {
			return null;
		}
		$res = $res[0];
		$voimglistid = $res[0];
		$voimglists = new Default_Model_VOWideImageLists();
		$voimglists->filter->id->numequals($voimglistid);
		if( count($voimglists->items) === 0 ){
			return null;
		}
		return $voimglists->items[0];
	}
	
	public static function getPublishedVoImageList( $vo){
		$voimglists = new Default_Model_VOWideImageLists();
		$f1 = new Default_Model_VOWideImageListsFilter();
		$f2 = new Default_Model_VOWideImageListsFilter();
		
		$f1->void->numequals($vo->id);
		$f2->state->equals("published");
		$voimglists->filter->chain($f1, "AND");
		$voimglists->filter->chain($f2, "AND");
		
		if( count($voimglists->items) === 0 ){
			return null;
		}
		return $voimglists->items[0];
	}
	
	private static function clearDraftImages($researcher, $vo, $vappliance = null){
		$user = self::getUser($researcher);
		if( $user === null ){
			return "Not authorized to clear draft images from vo";
		}
		
		$vorg = self::getVo($vo);
		if( $vorg === null ){
			return true; //nothing to do
		}
		
		$vodraft = self::getDraftVoImageList($user, $vorg, false);
		if( $vodraft === null ){
			return true; //nothing to do
		}
		$vodraftimages = new Default_Model_VOWideImageListImages();
		$f1 = new Default_Model_VOWideImageListImagesFilter();
		$f1->vowide_image_list_id->numequals($vodraft->id);
		$vodraftimages->filter->chain($f1, "AND");
		
		if( $vappliance !== null ){
			$vappversion = self::getVAppVersion($vappliance);
			if( $vappversion === null ){
				return true; //nothing to do
			}
			$vapplists = $vappversion->getVappLists();
			$vapplistids = array();
			foreach($vapplists as $vapplist){
				$vapplistids[] = $vapplist->id;
			}
			$f2 = new Default_Model_VOWideImageListImagesFilter();
			$f2->vapplistid->in($vapplistids);
			$vodraftimages->filter->chain($f2, "AND");
		}
		
		if( count($vodraftimages->items) === 0 ){
			return true; //nothing to do
		}
		
		foreach($vodraftimages->items as $img){
			$vodraftimages->remove($img);
		}
		
		$vodraft->alteredbyid = $user->id;
		$vodraft->save();
		
		return true;
	}
	
	public static function imageAction($action, $researcher, $vorg, $vappliance = null){
		$user = self::getUser($researcher);
		if( $user === null ){
			return "User not found";
		}
		$vo = self::getVo($vorg);
		if( $vo === null ){
			return "Virtual organization not found";
		}
		$canEdit = self::canEditVOImageList($user, $vo);
		if( $canEdit === false ){
			return "Cannot edit virtual organization image list";
		}
		$vappversion = null;
		if( $vappliance !== null ){
			$vapp = self::getVAppliance($vappliance);
			if( $vapp === null ){
				return "Virtual appliance not found";
			}
			$vappversion = self::getVAppVersion($vapp);
			if( $vappversion === null ){
				return "Virtual appliance does not have any published version";
			}
		}
		if( is_string($action) === false ){
			return "No action provided";
		}
		$action = strtolower(trim($action));
		switch( $action ){
			case "add":
				if( $vappversion === null ){
					return "No virtual appliance provided for inclusion";
				}
				return self::addVAppliance($user, $vo, $vappversion);
			case "remove":
				if( $vappversion === null ){
					return "No virtual appliance provided for removal";
				}
				return self::removeVappliance($user, $vo, $vapp);
			case "update":
				if( $vappversion === null ){
					return "No virtual appliance provided for update";
				}
				return self::updateVappliance($user, $vo, $vappversion);
			case "publish":
				return self::publishVoImageList($user, $vo);
			case "revertchanges":
				return self::revertDraftChanges($user, $vo);
			default:
				return "No action provided";
		}
	}
	
	private static function addVAppliance($researcher, $vo, $vappversion){
		if( $vappversion->isExpired() ){
			return "Virtual appliance version is expired";
		}
		
		$imagelists = $vappversion->getVappLists();
		if( count($imagelists) === 0 ){
			return "No vappliance image instances to include in vo image list";
		}
		
		$voimglist = self::getDraftVoImageList($researcher, $vo, true);
		if( $voimglist === null ){
			return "Could not retrieve draft VO wide image list";
		}
		
		//Clearing draft from current vappliance images lists
		$result = self::clearDraftImages($researcher, $vo, $vappversion);
		if( $result !== true ){
			if( $result === false ){
				return "Could not clear draft vo image list for given virtual appliance";
			}else{
				return $result;
			}
		}
		
		foreach($imagelists as $imglst){
			$voimglstimg = new Default_Model_VOWideImageListImage();
			$voimglstimg->vowideImageListID = $voimglist->id;
			$voimglstimg->vapplistid = $imglst->id;
			$voimglstimg->state = "draft";
			$voimglstimg->save();
		}
		return true;
	}
	
	private static function removeVappliance($researcher, $vo, $vappliance){
		db()->query("SELECT remove_va_from_vowide_image_list(?, ?, ?)", array($vo->id, $vappliance->id, $researcher->id))->fetchAll();
		return true;
	}
	
	private static function updateVappliance($researcher, $vo, $vappversion){
		if( $vappversion->isExpired() ){
			return "Virtual appliance version is expired";
		}
		debug_log("AETOST: " . var_export(array($vo->id, $vappversion->id, $researcher->id), true));
		db()->query("SELECT update_vowide_image_list(?, ?, ?)", array($vo->id, $vappversion->id, $researcher->id))->fetchAll();
		return true;
	}
	
	private static function publishVoImageList($researcher, $vo){
		$results = array();
		global $application;
		$db = $application->getBootstrap()->getResource('db');
		$db->setFetchMode(Zend_Db::FETCH_BOTH);
		$q = "SELECT publish_vowide_image_list(?,?);";
		$res = $db->query( $q, array($vo->id, $researcher->id) )->fetchAll();
		if( count($res) === 0  ) {
			return "Could not publish vo image list";
		}
		$res = $res[0];
		$voimglistid = $res[0];
		$voimglists = new Default_Model_VOWideImageLists();
		$voimglists->filter->id->numequals($voimglistid);
		if( count($voimglists->items) === 0 ){
			return "Could not publish vo image list";
		}else{
			$vmcast = self::publishToVMCaster($researcher, $vo, $voimglists->items[0]);
			if( $vmcast !== true ){
				if( $vmcast === false ){
					$results[] = $vmcast;
				}else{
					$results[] = "Could not publish image list with vmcaster";
				}
			}
		}
		
		$draftres = self::getDraftVoImageList($researcher, $vo, true);
		if( !$draftres ){
			$results[] = "Could not create new draft image list";
		}
		if( count($results) === 0 ){
			return true;
		}
		return implode(",",$results);
	}
	private static function publishToVMCaster($researcher, $vo, $voimagelist){
		$url = VMCaster::getVMCasterUrl();
		$url .= "/vmlistcontroller/create/" . $voimagelist->id . "/published/vos";
		$result = web_get_contents($url);		
		if( $result === false ){
			error_log("[VOAdmin:publishToVMCaster]:Could not retrieve response data from " . $url);
			return false;
		}
		return true;
	}
	private static function revertDraftChanges($researcher, $vo){
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$res = db()->query("SELECT discard_vowide_image_list(?)", array($vo->id))->fetchAll();
		if (count($res) > 0) {
			$res = $res[0];
			if ($res) {
				//recreate draft vi wide image list
				$draft = self::getDraftVoImageList($researcher, $vo, true);
				
				if( !$draft ){
					return "Could not recreate draft image list version";
				}
				return true;
			} else {
				return true;
			}
		} else {
			return true;
		}
	}
	public static function getImageInfoById($voimageid,$identifier = null,$strict=false){
		if( $voimageid !== null && !is_numeric($voimageid)) { return null; }
		if( $identifier!==null && trim($identifier) === "") { return null; }
		
		$voimages = new Default_Model_VOWideImageListImages();
		
		$f1 = new Default_Model_VOWideImageListImagesFilter();
		$f1->id->numequals($voimageid);
		$voimages->filter->chain($f1, "AND");
		
		if( $identifier !== null ){
			$f2 = new Default_Model_VOWideImageListImagesFilter();
			$f2->guid->equals($identifier);
			$voimages->filter->chain($f2, "AND");
		}
		
		if( count($voimages->items) === 0 ){ return null; }
		
		$voimage = $voimages->items[0];
		
		//Get vo wide image list for future use
		$voimagelist = $voimage->getVowideImageList();
		if( $voimagelist === null ) { return null; }
		
		//Get VO entry for future use
		$vo = $voimagelist->getVo();
		if( $vo === null ) { return null; }
		
		//Retrieve vapp list to collect image from there
		$vapplist = $voimage->getVappList();
		if( $vapplist === null ) { return null; }
		
		//Get vapp image entry
		$vmiimage = $vapplist->getVMIinstance();
		if( $vmiimage === null ) { return null; }
		
		$image = $vmiimage;
		$originalimageid = $vmiimage->id;
		//Get good vmi instance id (same with up to date metadata)
		if( $strict === false ){
			db()->setFetchMode(Zend_Db::FETCH_BOTH);
			$res = db()->query("SELECT get_good_vmiinstanceid(?)", array($vmiimage->id))->fetchAll();
			if (count($res) > 0) {
				$res = $res[0];
			}
			if (count($res) > 0) {
				$res = $res[0];
			}
			//if good instance id differs use that one
			if ($res && is_numeric($res) && intval($res) !== intval($vmiimage->id)) {
				$originalimageid = $image->id;
				$images = new Default_Model_VMIinstances();
				$images->filter->id->numequals(intval($res));
				if( count($images->items) > 0 ){
					$image = $images->items[0];
				}
			}
		}
		//Retrieve data for image
		$result = VmCaster::getImageInfoById($image->id,$image->guid,$strict);
		if( $result === null ) { return null; }
		
		//Enrich returned data with vo image specific information
		$result["vo"] = $vo;
		$result["voimage"] = $voimage;
		$result["voimagelist"] = $voimagelist;
		$result["mpuri"] = 'https://'.$_SERVER['HTTP_HOST'].'/store/vo/image/' .$voimage->guid . ':' . $voimage->id . '';
		$result["id"] = $voimage->id;
		$result["baseid"] = $image->id;
		if( $originalimageid !== $image->id ){
			$result["requested_baseid"] = $originalimageid;
		}
		$result["identifier"] = $voimage->guid;
		$result["baseidentifier"] = $image->guid;
		$result["basempuri"] = 'https://'.$_SERVER['HTTP_HOST'].'/store/vm/image/' .$image->guid . ':' . $image->id . '';
		return $result; 
	}
	public static function getImageInfoByIdentifier($identifier){
		if( $identifier!==null && trim($identifier) === "") { return null; }
		$voimagelist = null;
		//first search published image lists
		$publists = new Default_Model_VOWideImageLists();
		$f1 = new Default_Model_VOWideImageListImagesFilter();
		$f2 = new Default_Model_VOWideImageListsFilter();
		$f1->guid->equals($identifier);
		$f2->state->equals("published");
		$publists->filter->chain($f1, "AND");
		$publists->filter->chain($f2, "AND");
		if( count($publists->items) > 0 ){ 
			$voimagelist = $publists->items[0];
		}
		
		//Then check draft
		if( $voimagelist == null ){
			$prevlists = new Default_Model_VOWideImageLists();
			$f3 = new Default_Model_VOWideImageListImagesFilter();
			$f4 = new Default_Model_VOWideImageListsFilter();
			$f3->guid->equals($identifier);
			$f4->state->equals("obsolete");
			$prevlists->filter->chain($f3, "AND");
			$prevlists->filter->chain($f4, "AND");
			$prevlists->filter->orderby("published_on DESC");
			if( count($prevlists->items) > 0 ){ 
				$voimagelist = $prevlists->items[0];
			}
		}
		
		if( $voimagelist === null ){
			return null;
		}
		
		//Retrieve vo wide image entry
		$images = new Default_Model_VOWideImageListImages();
		$f5 = new Default_Model_VOWideImageListImagesFilter();
		$f6 = new Default_Model_VOWideImageListImagesFilter();
		
		$f5->vowide_image_list_id->numequals($voimagelist->id);
		$f6->guid->equals($identifier);
		$images->filter->chain($f5, "AND");
		$images->filter->chain($f6, "AND");
		
		if( count($images->items) === 0 ){
			return null;
		}
		$voimage = $images->items[0];
		
		//Get VO entry for future use
		$vo = $voimagelist->getVo();
		if( $vo === null ) { return null; }
		
		//Retrieve vapp list to collect image from there
		$vapplist = $voimage->getVappList();
		if( $vapplist === null ) { return null; }
		
		//Get vapp image entry
		$image = $vapplist->getVMIinstance();
		if( $image === null ) { return null; }
		
		//Retrieve data for image
		$result = VmCaster::getImageInfoById($image->id);
		if( $result === null ) { return null; }
		
		//Enrich returned data with vo image specific information
		$result["vo"] = $vo;
		$result["voimage"] = $voimage;
		$result["voimagelist"] = $voimagelist;
		$result["mpuri"] = 'https://'.$_SERVER['HTTP_HOST'].'/store/vo/image/' .$voimage->guid . ':' . $voimage->id . '';
		$result["id"] = $voimage->id;
		$result["baseid"] = $image->id;
		$result["identifier"] = $voimage->guid;
		$result["baseidentifier"] = $image->guid;
		$result["basempuri"] = 'https://'.$_SERVER['HTTP_HOST'].'/store/vm/image/' .$image->guid . ':' . $image->id . '';
		
		return $result;
	}
	public static function convertImage($result, $format = 'xml'){
		return VMCaster::convertImage($result, $format);
	}
	
	public static function getDefaultVORoles(){
		$default = array("VO MANAGER", "VO DEPUTY", "VO SHIFTER", "VO EXPERT");
		require_once('Zend/Config/Ini.php');
		$conf = new Zend_Config_Ini('../application/configs/application.ini', $_SERVER['APPLICATION_ENV']);
		$appconf = $conf->app;
		$voroles = $appconf->voroles;
		if( trim($voroles) === ""){
			return $default;
		}else {
			$voroles = explode(",",$voroles);
			$saneroles = array();
			foreach($voroles as $role){
				$r = trim(strtoupper($role));
				if( substr($r, 0, 3) !== "VO "){
					$r = "VO " . $r;
				}
				$saneroles[] = $r; 
			}
			if( count($saneroles) > 0 ){
				return $saneroles;
			}
		}
		return $default;
	}
	/* 
	 * Return given roles in a normalized form. 
	 * Only manager, expert, deputy and shifter as accepted.
	 */
	private static function normalizeVORoles($role){
		$roles = array();
		if( is_string($role) ) {
			$roles[] = $role;
		} else if ( is_array($role) ) {
			$roles = $role;
		} else {
			return array();
		}
		$result = array();
		$validroles = self::getDefaultVORoles();
		foreach($roles as $r){
			foreach($validroles as $v){
				if( stripos($v, $r) !== false ){
					$result[] = $v;
					break;
				}
			}
		}
		return array_unique($result);
	}
	/*
	 * Retrieve recipients for given list of vo ids 
	 * grouped by vo name
	 */
	public static function getRecipientsPerVO($vos){
		if( $vos && !is_array($vos) ){
			if( is_numeric($vos) ){
				$vos = array($vos);
			}else{
				$vos = array();
			}
		}
		$roles = VoAdmin::getDefaultVORoles();
		$res = array();
		foreach($vos as $void){
			$contactinfos = VoAdmin::getVOContactInfo($void, $roles);
			if( !$contactinfos || count($contactinfos) === 0){
				continue;
			}
			$voname = "";
			$contacts = array();
			foreach($contactinfos as $ci){
				$voname = $ci["vo"];
				//email, name, role
				$contacts[] = array(
					"name"=> $ci["name"],
					"email"=> $ci["email"],
					"role"=> $ci["role"]
				);
			}
			$res[] = array("vo"=>$voname, "void"=>$void, "contacts"=>$contacts);
		}
		return $res;
	}
	/*
	 * Get the emails of a vo based on the given roles.
	 */
	public static function getVOContactInfo($vo, $roles = array()){
		$voitem = self::getVo($vo);
		if( !$voitem ){
			return array();
		}
		$voroles = self::normalizeVORoles($roles);
		
		$query = "SELECT DISTINCT egiops.vo_contacts.email, egiops.vo_contacts.name , egiops.vo_contacts.role, egiops.vo_contacts.vo FROM egiops.vo_contacts WHERE egiops.vo_contacts.vo = ?";
		if( count($voroles) > 0 ){
			$query .= " AND role in ('" . implode("','", $voroles) . "')";
		}
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$res = db()->query($query, array($voitem->name))->fetchAll();
		
		return $res;
	}
	/*
	 * Returns VOs which published image list contains obsolete/deleted images 
	 * or references a deleted or expired virtual appliances.
	 * 
	 * The results also contain the obsolete vappliances.
	 */
	public static function getObsoleteVOImagelists($vo=null){
		$query = "SELECT * FROM vo_obsolete_images";
		$params = array();
		if( $vo !== null ){
			$voitem = self::getVo($vo);
			if( $voitem === null ){
				error_log("[VOAdmin::getObsoleteVOImagelists] Could not find vo " . $vo);
				return false;
			}
			$query .= " WHERE void = ?";
			$params[] = $voitem->id;
		}
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$res = db()->query($query, $params)->fetchAll();
		return $res;
	}
	/*
	 * Returns VOs which published image list contains images 
	 * of expired virtual appliance versions.
	 * 
	 * The results also contain the obsolete vappliances.
	 */
	public static function getExpiredVOImagelists($vo=null){
		$query = "SELECT * FROM vo_obsolete_images WHERE hasexpired = true";
		$params = array();
		if( $vo !== null ){
			$voitem = self::getVo($vo);
			if( $voitem === null ){
				error_log("[VOAdmin::getObsoleteVOImagelists] Could not find vo " . $vo);
				return false;
			}
			$query .= " AND void = ?";
			$params[] = $voitem->id;
		}
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$res = db()->query($query, $params)->fetchAll();
		return $res;		
	}
	/*
	 * Returns VOs which published image list contains images
	 * of deleted virtual appliances.
	 * 
	 * The results also contain the obsolete vappliances.
	 */
	public static function getDeletedVOImagelists($vo=null){
		$query = "SELECT * FROM vo_obsolete_images WHERE hasdeleted = true";
		$params = array();
		if( $vo !== null ){
			$voitem = self::getVo($vo);
			if( $voitem === null ){
				error_log("[VOAdmin::getObsoleteVOImagelists] Could not find vo " . $vo);
				return false;
			}
			$query .= " AND void = ?";
			$params[] = $voitem->id;
		}
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$res = db()->query($query, $params)->fetchAll();
		return $res;
	}
	private static function getAllVOs(){
		$result = array();
			$query = "SELECT distinct vos.id, vos.name,	domains.name AS discipline, false as endorsed, false as uptodate FROM vos 
			inner join domains on domains.id = vos.domainid 
			WHERE deleted = FALSE GROUP BY vos.id, domains.name ORDER BY vos.name ASC";
		$result = db()->query($query)->fetchAll();
		return $result;
	}
	/*
	 * Return all of the VOs that endorsed the given virtual appliance
	 * if parameter $all is set to true, it will alse return all other VOs 
	 * with endorsed and updated columns set to FALSE
	 */
	public static function getEndorsedVos($vappliance, $all = false){
		$vapp = self::getVAppliance($vappliance);
		if( $vapp === null && $all !== true ){
			return array();
		}
		if( $vapp !== null ){
			$query = "SELECT 
					distinct vos.id, 
					vos.name,
					domains.name AS discipline, 
					bool_and(vowide_image_list_images.state = 'up-to-date'::e_vowide_image_state) AS uptodate
				FROM vos 
					LEFT OUTER JOIN vowide_image_lists ON vowide_image_lists.void = vos.id
					LEFT OUTER JOIN vowide_image_list_images ON vowide_image_list_images.vowide_image_list_id = vowide_image_lists.id
					LEFT OUTER JOIN vaviews ON vaviews.vapplistid = vowide_image_list_images.vapplistid
					LEFT OUTER JOIN domains ON domains.id = vos.domainid
				WHERE
					vowide_image_lists.state = 'published'::e_vowide_image_state AND
					deleted = FALSE  AND 
					vaviews.appid = ? 
				GROUP BY vos.id, domains.name ORDER BY vos.name ASC ";
			db()->setFetchMode(Zend_Db::FETCH_BOTH);
			$res = db()->query($query, array($vappliance->id))->fetchAll();
		} else {
			$res = array();
		}
		if( $all === true ){
			$result = array();
			$query = "SELECT distinct vos.id, vos.name,	domains.name AS discipline, false as endorsed, false as uptodate FROM vos 
				inner join domains on domains.id = vos.domainid 
				WHERE deleted = FALSE GROUP BY vos.id, domains.name ORDER BY vos.name ASC";
			$result = db()->query($query)->fetchAll();
			for($i=0; $i<count($result); $i+=1){
				foreach($res as $e){
					if( $result[$i]["id"] === $e["id"] ){
						$result[$i]["endorsed"] = true;
						$result[$i]["uptodate"] = $e["uptodate"];
						break;
					}
				}
			}
			return $result;
		}
		return $res;
	}
}
class VoAdminNotifications {
	private static function debugSendMultipart($subject, $to, $txtbody='', $htmlbody='', $username, $password, $replyto = false, $attachment = null, $cc = false, $ext = null){
		error_log("SENDING VO OBSOLETE IMAGE LIST NOTIFICATION: ");
		error_log("TO: " . implode(",", $to));
		error_log("REPLY_TO: " . $replyto);
		error_log("SUBJECT: " . $subject);
		error_log("MESSAGE: " . $txtbody);
	}
	private static function getVOObsoleteNotificationMessage($notification){
		$subject = "[EGI AppDB] VO " . $notification["voname"] . " image list notification";
		$message = "-- This is an automated message, please do not reply -- \n\n";
		$message .= "Dear VO management team,\n\n";
		$message .= "  the published image list of the VO " . $notification["voname"] . " contains some obsolete images as follows:\n\n";
		if( $notification["outdated"] > 0 ){
			$message .= "    " . $notification["outdated"] . " image" . ( ($notification["outdated"]>1)?"s":"" ) . " from an outdated virtual appliance version\n";
		}
		if( $notification["deleted"] > 0 ){
			$message .= "    " . $notification["deleted"] . " image" . ( ($notification["deleted"]>1)?"s":"" ) . " from a user deleted virtual appliance\n";
		}
		if( $notification["expired"] > 0 ){
			$message .= "    " . $notification["expired"] . " image" . ( ($notification["expired"]>1)?"s":"" ) . " from an expired virtual appliance version\n";
		}
		$message .= "\n  It is recommended to update and republish the vo image list by visiting the vo wide image list editor [1].";
		$message .= "\n  A guide to managing VO image lists is available at [2].\n\n";
		$message .= "Best regards,\n";
		$message .= "AppDB team\n";
		$message .= "\n_____________________________________________________________________________________________________________________\n";
		$message .= "[1].https://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "/store/vo/" . $notification["voname"] . "/imagelist (login required)\n";
		$message .= "[2].https://wiki.appdb.egi.eu/main:guides:manage_vo-wide_image_lists\n";
		$notification["subject"] = $subject;
		$notification["message"] = $message;
		
		return $notification;
	}
	/*
	 * Create and return a VO obsolete notification. Parameter $data is a row from the 
	 * list returned by VoAdmin::getVOObsoleteNotification function
	 */
	private static function getVOObsoleteNotification($data){
		$notification = array(
			"subject"=>"",
			"message"=>"",
			"recipients"=>array(), 
			"voname"=> $data["voname"], 
			"void"=>$data["void"],
			"hasdeleted"=>( ($data["hasdeleted"])?true:false ),
			"hasexpired"=>( ($data["hasexpired"])?true:false ),
			"hasoutdated"=>( ($data["hasoutdated"])?true:false ),
			"deleted"=>0,
			"expired"=>0,
			"outdated"=>0,
			"vappliances"=> array()
		);
		//Must have at least one type of obsolete data
		if( $notification["hasdeleted"] === false && $notification["hasexpired"] === false && $notification["hasoutdated"] === false ){
			return null;
		}
		//Parse json with obsolete virtual appliances
		try{
			$apps =  trim($data["apps"]);
			if( $apps === "" ){
				return null;
			}
			$apps = json_decode($apps, true);
			if( $apps === null || count($apps) === 0){
				return null;
			}
			$notification["vappliances"] = $apps;
			$deleted = 0;
			$expired = 0;
			$outdated = 0;
			foreach($apps as $a){
				if( $a["expired"] == "true"){
					$expired += 1;
				}
				if( $a["deleted"] == "true"){
					$deleted += 1;
				}
				if( $a["outdated"] == "true"){
					$outdated += 1;
				}
			}
			if( ($deleted+$expired+$outdated) === 0 ){
				return null;
			}
			$notification["deleted"] = $deleted;
			$notification["expired"] = $expired;
			$notification["outdated"] = $outdated;
		}catch(Exception $ex){
			return null;
		}
		//Retrieve vo emails
		$to = VoAdmin::getRecipientsPerVO(intval($data["void"]));
		$recs = array();
		foreach($to as $t){
			if( trim($t["void"]) === trim($data["void"]) ){
				$cnts = $t["contacts"];
				foreach($cnts as $cnt){
					if( trim($cnt["email"]) !== "" ){
						$recs[$cnt["email"]] = $cnt["email"];
					}
				}
				break;
			}
		}
		$recipients = array();
		foreach($recs as $r){
			$recipients[] = $r;
		}
		if( count($recipients) === 0 ){
			return null;
		}
		$notification["recipients"] = $recipients;
		return self::getVOObsoleteNotificationMessage($notification);
	}
	/*
	 * Create and return a list of VO obsolete notifiactions.
	 * Vo obsolete images are retrieved by 
	 * VoAdmin::getObsoleteVOImagelists function
	 */
	public static function createVOObsoleteNotifications(){
		$notifications = array();
		$obsolete = VoAdmin::getObsoleteVOImagelists();
		foreach($obsolete as $obs){
			$nt = self::getVOObsoleteNotification($obs);
			if( $nt !== null ){
				$notifications[] = $nt;
			}
		}
		return $notifications;
	}
	public static function sendVOObsoleteNotifications(){
		$obsolete = VoAdmin::getObsoleteVOImagelists();
		foreach($obsolete as $obs){
			$nt = self::getVOObsoleteNotification($obs);
			if( $nt !== null ){
				if ( ApplicationConfiguration::isProductionInstance() === FALSE ) {
					self::debugSendMultipart($nt["subject"], $nt["recipients"], $nt["message"], null, "appdb reports username", "appdb reports password", false, null, false, null);
				} else {
					//sendMultipartMail($nt["subject"], $nt["recipients"], $nt["message"], null, 'appdb-reports@iasa.gr', 'enadyskolopassword', false, null, false, array("Precedence"=>"bulk"));
					EmailService::sendBulkReport($nt["subject"], $nt["recipients"], $nt["message"], null);
				}
			}
		}
	}
}
class VoContact{
	CONST VO_NOTIFY_SUBJECT_MIN_SIZE = 1;
	CONST VO_NOTIFY_SUBJECT_MAX_SIZE = 200;
	CONST VO_NOTIFY_MESSAGE_MIN_SIZE = 1;
	CONST VO_NOTIFY_MESSAGE_MAX_SIZE = 1000;
	/*
	 * Normalize VO ids. Return an array of integers.
	 */
	private static function normalizeVOs($voids){
		$result = array();
		if( !$voids ) {
			return $result;
		}
		if( is_array($voids) === false ){
			if( is_numeric($voids) === false ) {
				return $result;
			}else{
				$voids = array($voids);
			}
		}
		
		foreach( $voids as $void ){
			if( is_numeric($void) && intval($void) > 0){
				$result[] = intval($void);
			}
		}
		return $result;
	}
	/*
	 * Check if user can send notifications to VOs for the given 
	 * virtual appliance.
	 */
	private static function canSendVONotification($user, $vappliance){
		$user = VoAdmin::getUser($user);
		$vappliance = VoAdmin::getVAppliance($vappliance);
		if( $vappliance === null ){
			return "Virtual appliance not found";
		}
		if( $user === null ){
			return "User not found";
		}
		if( trim($user->getPrimaryContact()) === "" ){
			return "Cannot find user's primary email contact";
		}
		$privs = $user->getPrivs();
		if( $privs == null ){
			return "Could not retrieve user's permissions";
		}
		
		return $privs->canManageVAs($vappliance->guid);		
	}
	/*
	 * Returns only the ids of the given vos that have endorsed 
	 * the given virtual appliance (with any vappliance version)
	 */
	private static function getExclusionVOs($vappliance, $vos){
		$endorsed = VoAdmin::getEndorsedVos($vappliance);
		$result = array();
		foreach($endorsed as $e){
			foreach($vos as $vo){
				if( trim($vo) === trim($e["id"]) ){
					$result[] = $vo;
				}
			}
		}
		return $result;
	}
	/*
	 * Validate user's defined subject and message.
	 */
	private static function validateGenericNotificationData($subject, $message){
		if( count($subject) > self::VO_NOTIFY_SUBJECT_MAX_SIZE || count($subject) < self::VO_NOTIFY_SUBJECT_MIN_SIZE){
			return "Subject is mandatory for generic VO notifications and must not exceed " . VO_NOTIFY_SUBJECT_MAX_SIZE . " characters.";
		}
		if( count($message) > self::VO_NOTIFY_MESSAGE_MAX_SIZE || count($message) < self::VO_NOTIFY_MESSAGE_MIN_SIZE){
			return "Message is mandatory for generic VO notifications and must not exceed " . VO_NOTIFY_MESSAGE_MAX_SIZE . " characters.";
		}
		return true;
	}
	/*
	 * Initial validation of given data for the request
	 */
	private static function validateRequest($user, $vappliance, $notificationtype, $vos, $subject, $message){
		if( count($vos) === 0 ){
			return "No virtual organizations given.";
		}
		
		if( trim($subject) !== "" ){
			if( preg_match("/(\r|\n)*(to:|from:|cc:|bcc:)/i",$subject) ) {
				return "The subject contains invalid headers";
			}
		}
		
		if( trim($message) !== "" ){
			if( preg_match("/(\r|\n)(to:|from:|cc:|bcc:)/i",$message) ) {
				return "The message contains invalid headers";
			}
		}
		
		$vappliance = VoAdmin::getVAppliance($vappliance);
		$user = VoAdmin::getUser($user);
		
		//Check for user permissions
		$cansend = self::canSendVONotification($user, $vappliance);
		if( $cansend === false ){
			return "Only users with permission to manage virtual appliance versions can send notifications to VOs";
		} else if( is_string($cansend) ){
			return $cansend;
		}
		
		//Validate data for notifiaction types
		switch($notificationtype){
			case "suggest":
			case "newversion":
				if( count($message) > self::VO_NOTIFY_MESSAGE_MAX_SIZE ){
					return "Message should not exceed " . self::VO_NOTIFY_MESSAGE_MAX_SIZE . "characters.";
				}
				break;
			case "exclude":
				$vos = self::getExclusionVOs($vappliance, $vos);
				if( count($vos) === 0 ){
					return "No endorsed VOs to send notification";
				}
				break;
			case "generic":
				$valid = self::validateGenericNotificationData($subject, $message);
				if( $valid !== true ){
					return $valid;
				}
				break;
			default:
				return "Unknown notification type given";
		}
		return true;
	}
	private static function getSuggestNotificationMessage($user, $vappliance, $subject, $message){
		$res = array("subject"=>"","body"=>"");
		$res["subject"] = "[EGI APPDB VO Notification] User requests virtual appliance endorsement";
		$body = "Dear VO management team,\n\n";
		$body .= "  user " . $user->firstname . " " . $user->lastname . " [1] requests that the virtual appliance '" . $vappliance->name . "' [2] should be endorsed by the {{vo.name}} VO [3] and therefore be included into the VO wide image list.\n";
		$body .= "A guide to managing VO image lists is available at [4].\n ";
		if(strlen(trim($message)) > 0 ){
			$body .= "\nUser's message follows::\n";
			$body .= "\n--------------------------------------------------------\n\n";
			$body .= $message;
			$body .= "\n\n-------------------------------------------------------\n";
		}
		$body .= "\nBest regards,\n";
		$body .= "AppDB team\n\n";
		$body .= "________________________________________________________________________________________________________\n";
		$body .= "[1].https://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "/store/person/". $user->cname ."\n";
		$body .= "[2].https://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "/store/vappliance/". $vappliance->cname ."\n";
		$body .= "[3].https://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "/store/vo/{{vo.name}}\n";
		$body .= "[4].https://wiki.appdb.egi.eu/main:guides:manage_vo-wide_image_lists\n";
		
		$res["message"] = $body;
		return $res;
	}
	private static function getNewVersionNotificationMessage($user, $vappliance, $subject, $message){
		$res = array("subject"=>"","body"=>"");
		$res["subject"] = "[EGI APPDB VO Notification] New virtual appliance version available";
		$body = "Dear VO management team,\n\n";
		$body .= "  user " . $user->firstname . " " . $user->lastname . " [1] informs you that virtual appliance " . $vappliance->name . " [2] has published a new version for {{vo.name}} [3] VO. You should consider to update or not your VO wide image list.\n";
		$body .= "A guide to managing VO image lists is available at [4].\n ";
		if( strlen(trim($message)) > 0 ){
			$body .= "\nUser's message follows:\n";
			$body .= "--------------------------------------------------------\n\n";
			$body .= $message;
			$body .= "\n\n-------------------------------------------------------\n\n";
		}
		$body .= "\nBest regards,\n";
		$body .= "AppDB team\n\n";
		$body .= "________________________________________________________________________________________________________\n";
		$body .= "[1].https://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "/store/person/". $user->cname ."\n";
		$body .= "[2].https://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "/store/vappliance/". $vappliance->cname ."\n";
		$body .= "[3].https://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "/store/vo/{{vo.name}}\n";
		$body .= "[4].https://wiki.appdb.egi.eu/main:guides:manage_vo-wide_image_lists\n";
		
		$res["message"] = $body;
		return $res;
	}
	private static function getExclusionNotificationMessage($user, $vappliance, $subject, $message){
		$res = array("subject"=>"","body"=>"");
		$res["subject"] = "[EGI APPDB VO Notification] Request for virtual appliance exclusion from VO image list";
		$body = "Dear VO management team,\n\n";
		$body .= "  user " . $user->firstname . " " . $user->lastname . " [1] requests that the virtual appliance " . $vappliance->name . " [2] should be excluded from the {{vo.name}} [3] VO and therefore be excluded from the VO wide image list.\n";
		$body .= "A guide to managing VO image lists is available at [4].\n ";
		if( strlen(trim($message)) > 0 ){
			$body .= "\nUser's message follows:\n";
			$body .= "--------------------------------------------------------\n\n";
			$body .= $message;
			$body .= "\n\n-------------------------------------------------------\n\n";
		}
		$body .= "\nBest regards,\n";
		$body .= "AppDB team\n\n";
		$body .= "________________________________________________________________________________________________________\n";
		$body .= "[1].https://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "/store/person/". $user->cname ."\n";
		$body .= "[2].https://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "/store/vappliance/". $vappliance->cname ."\n";
		$body .= "[3].https://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "/store/vo/{{vo.name}}\n";
		$body .= "[4].https://wiki.appdb.egi.eu/main:guides:manage_vo-wide_image_lists\n";
		
		$res["message"] = $body;
		return $res;
	}
	private static function getGenericNotificationMessage($user, $vappliance, $subject, $message){
		$res = array("subject"=>"","body"=>"");
		$res["subject"] = "[EGI APPDB VO Notification] " ;
		if( trim($subject) !== "" ){
			$res["subject"] .= trim($subject);
		}else{
			$res["subject"] .= "Virtual Appliance general notification";
		}
		
		$body = "Dear VO management team,\n\n";
		$body .= "  user " . $user->firstname . " " . $user->lastname . " [1] sent you a notification regarding virtual appliance " . $vappliance->name . " [2] for the {{vo.name}} [3] VO.\n";
		if( strlen(trim($message)) > 0 ){
			$body .= "\nUser's message follows:\n";
			$body .= "--------------------------------------------------------\n\n";
			$body .= $message;
			$body .= "\n\n-------------------------------------------------------\n\n";
		}
		$body .= "Best regards,\n";
		$body .= "AppDB team\n\n";
		$body .= "________________________________________________________________________________________________________\n";
		$body .= "[1].https://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "/store/person/". $user->cname ."\n";
		$body .= "[2].https://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "/store/vappliance/". $vappliance->cname ."\n";
		$body .= "[3].https://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "/store/vo/{{vo.name}}\n";
		
		$res["message"] = $body;
		return $res;
	}
	/**
	 * Creates a report for the sender.
	 */
	private static function sendVONotificationReportMessage($notification, $user, $vappliance, $notificationtype, $usersubject, $usermessage){
		if( !$vappliance || !$user || !$notification){
			return;
		}
		$useremail = trim($notification["useremail"]);
		if( $useremail === "" ){
			return;
		}
		$recipients = $notification["recipients"];
		if( count($recipients) === 0 ){
			return null;
		}
		$noticationtypedisplay = "";
		switch($notificationtype){
			case "suggest":
				$noticationtypedisplay = "your endorsement request for virtual appliance " . $vappliance->name;
				break;
			case "exclude":
				$noticationtypedisplay = "your VO exclusion request for virtual appliance " . $vappliance->name;
				break;
			case "newversion":
				$noticationtypedisplay = "your notification for a new " . $vappliance->name . " version";
				break;
			case "generic":
			case "default":
				$noticationtypedisplay = "your notification for " . $vappliance->name . " virtual appliance";
				break;
		}
		$subject = "[EGI APPDB] VO contact notification";
		
		$message = "-- This is an automated message, please do not reply -- \n\n";
		$message .= "Dear " . $user->firstname . " " . $user->lastname . ",\n\n";
		$message .= "  we report that " . $noticationtypedisplay . " has been sent to the VO management teams of the following VOs:\n\n";
		$recs = array();
		foreach($recipients as $rec){
			$voname = $rec["vo"];
			$recs[] = $voname;
		}
		$message .= "    " . implode(", ", $recs);
		if( trim($usermessage) !== "" ) {
			$message .= "\n\nwith the following message:\n";
			$message .= "-------------------------------------------------------\n";
			if( trim($usersubject) !== "" ){
				$message .= "[subject]: " . $usersubject . "\n\n";
			}
			$message .= "\n" . $usermessage;
			$message .= "\n-------------------------------------------------------\n";
		}
		$message .= "\n\nBest regards,\n";
		$message .= "AppDB team\n";
		
		$to = array($useremail);
		if( ApplicationConfiguration::isProductionInstance() === FALSE ) {
			self::debugSendMultipart($subject, $to, $message, null, "appdb reports username", "appdb reports password", false, null, false, null);
		} else {
			//sendMultipartMail($subject, $to, $message, null, 'appdb-reports@iasa.gr', 'enadyskolopassword', false, null, false, array("Precedence"=>"bulk"));
			EmailService::sendBulkReport($subject, $to, $message);
		}
	}
	private static function getNotificationMessage($user, $vappliance, $notificationtype, $vos, $subject, $message){
		$msg = "";
		switch($notificationtype){
			case "suggest":
				$msg = self::getSuggestNotificationMessage($user, $vappliance, $subject, $message);
				break;
			case "newversion":
				$msg = self::getNewVersionNotificationMessage($user, $vappliance, $subject, $message);
				break;
			case "exclude":
				$msg = self::getExclusionNotificationMessage($user, $vappliance, $subject, $message);
				break;
			case "generic":
				$msg = self::getGenericNotificationMessage($user, $vappliance, $subject, $message);
				break;
			default:
				break;
		}
		return $msg;
	}
	
	private static function debugSendMultipart($subject, $to, $txtbody='', $htmlbody='', $username, $password, $replyto = false, $attachment = null, $cc = false, $ext = null){
		error_log("SENDING VO NOTIFICATION: ");
		error_log("TO: " . implode(",", $to));
		error_log("REPLY_TO: " . $replyto);
		error_log("SUBJECT: " . $subject);
		error_log("MESSAGE: " . $txtbody);
	}
	/*
	 * Creates and sends a VO notification. In case of success it also sends a 
	 * report email back to the sender and returns TRUE. In case of error it returns FALSE or a 
	 * description of the error.
	 */
	public static function sendVONotification($user, $vappliance, $notificationtype, $vos, $subject, $message){
		$notification = null;
		$usermessage = "" . $message;
		$usersubject = "" . $subject;
		$result = self::createVONotification($user, $vappliance, $notificationtype, $vos, $subject, $message, $notification);
		if( $result !== true){
			return $result;
		}
		if( !$notification ){
			return "Could not send notification";
		}
		$recipients = $notification["recipients"];
		$subject = $notification["subject"];
		$message = $notification["message"];
		$replyto = $notification["useremail"];
		try{
			foreach($recipients as $rec){
				$voname = $rec["vo"];
				$to = array();
				foreach($rec["contacts"] as $cnt){
					$to[] = trim( $cnt["email"] );
				}
				$txtbody = preg_replace('/\{\{vo\.name\}\}/i', $voname, $message);
				$subj = preg_replace('/\{\{vo\.name\}\}/i', $voname, $subject);
				if( ApplicationConfiguration::isProductionInstance() === FALSE ) {
					self::debugSendMultipart($subj, $to, $txtbody, null, "appdb reports username", "appdb reports password", $replyto, null, false, null);
				} else {
					//sendMultipartMail($subj, $to, $txtbody, null, 'appdb-reports@iasa.gr', 'enadyskolopassword', $replyto, null, false, array("Precedence"=>"bulk"));
					EmailService::sendBulkReport($subj, $to, $txtbody, null, $replyto);
				}
			}
			self::sendVONotificationReportMessage($notification, $user, $vappliance, $notificationtype, $usersubject, $usermessage);
		}catch(Exception $ex){
			return $ex->getMessage();
		}
		return true;
	}
	/*
	 * Creates a VO notification object and pass it to the $output parameter.
	 * If the given data are invalid or the creation failed it returns the description
	 * of the error or FALSE if no description available. If the creation succeeds
	 * it returns TRUE.
	 */
	public static function createVONotification($user, $vappliance, $notificationtype, $vos, $subject, $message, &$output=""){
		$vappliance = VoAdmin::getVAppliance($vappliance);
		$user = VoAdmin::getUser($user);
		$vos = self::normalizeVOs($vos);
		$isvalid = self::validateRequest($user, $vappliance, $notificationtype, $vos, $subject, $message);
		if( $isvalid !== true ){
			return $isvalid;
		}
		
		$notification = self::getNotificationMessage($user, $vappliance, $notificationtype, $vos, $subject, $message);
		$notification["recipients"] = VoAdmin::getRecipientsPerVO($vos);
		$notification["useremail"] = $user->getPrimaryContact();
		$notification["username"] = $user->firstname . " " . $user->lastname;
		$output = $notification;
		return true;
	}
	
}

class VAProviders{
	private static function getVAppliance($app){
		if( is_numeric($app) ){
			$vapps = new Default_Model_Applications();
			$vapps->filter->id->numequals(intval($app));
			if( count($vapps->items) > 0 ){
				return $vapps->items[0];
			}
		}else if( $app instanceof Default_Model_Application){
			return $app;
		}else if(is_string($app) ){
			$vapps = new Default_Model_Applications();
			$vapps->filter->cname->equals(intval($app));
			if( count($vapps->items) > 0 ){
				return $vapps->items[0];
			}
		}
		return null;
	}
	public static function getProductionImages($vapp){
		$result = '<appdb:appdb xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:appdb="http://appdb.egi.eu/api/1.0/appdb" xmlns:application="http://appdb.egi.eu/api/1.0/application" xmlns:discipline="http://appdb.egi.eu/api/1.0/discipline" xmlns:category="http://appdb.egi.eu/api/1.0/category" xmlns:dissemination="http://appdb.egi.eu/api/1.0/dissemination" xmlns:filter="http://appdb.egi.eu/api/1.0/filter" xmlns:history="http://appdb.egi.eu/api/1.0/history" xmlns:logistics="http://appdb.egi.eu/api/1.0/logistics" xmlns:resource="http://appdb.egi.eu/api/1.0/resource" xmlns:middleware="http://appdb.egi.eu/api/1.0/middleware" xmlns:person="http://appdb.egi.eu/api/1.0/person" xmlns:permission="http://appdb.egi.eu/api/1.0/permission" xmlns:privilege="http://appdb.egi.eu/api/1.0/privilege" xmlns:publication="http://appdb.egi.eu/api/1.0/publication" xmlns:rating="http://appdb.egi.eu/api/1.0/rating" xmlns:ratingreport="http://appdb.egi.eu/api/1.0/ratingreport" xmlns:regional="http://appdb.egi.eu/api/1.0/regional" xmlns:user="http://appdb.egi.eu/api/1.0/user" xmlns:vo="http://appdb.egi.eu/api/1.0/vo" xmlns:virtualization="http://appdb.egi.eu/api/1.0/virtualization" xmlns:license="http://appdb.egi.eu/api/1.0/license" xmlns:provider="http://appdb.egi.eu/api/1.0/provider" xmlns:provider_template="http://appdb.egi.eu/api/1.0/provider_template" xmlns:classification="http://appdb.egi.eu/api/1.0/classification">';
		$vappliance = self::getVAppliance($vapp);
		if( $vappliance === null ) {
			return $result . "</appdb:appdb>";
		}
		$q = 'SELECT xmlelement(
				name "virtualization:image",
				xmlattributes(
						vaviews.vmiinstanceid,
						vaviews.vmiinstance_guid AS identifier,
						vaviews.vmiinstance_version
				),
				XMLELEMENT(NAME "virtualization:hypervisors", array_to_string(vaviews.hypervisors,\',\')::xml), 
				XMLELEMENT(NAME "virtualization:os", XMLATTRIBUTES(oses.id AS id, vaviews.osversion AS version, oses.os_family_id as family_id), oses.name), 
				XMLELEMENT(NAME "virtualization:arch", XMLATTRIBUTES(archs.id AS id), archs.name),
				array_to_string(array_agg(DISTINCT 
						xmlelement(name "virtualization:provider",
								xmlattributes(
										va_provider_images.va_provider_id as provider_id,
										va_provider_images.va_provider_image_id as occi_id,
										vowide_image_lists.void,
										va_provider_images.vmiinstanceid as vmiinstanceid
								)
						)::text
				),\'\')::xml
			)
		FROM 
			applications
			INNER JOIN vaviews ON vaviews.appid = applications.id
			INNER JOIN va_provider_images ON va_provider_images.good_vmiinstanceid = vaviews.vmiinstanceid
			LEFT JOIN archs ON archs.id = vaviews.archid
			LEFT JOIN oses ON oses.id = vaviews.osid
			LEFT JOIN vmiformats ON vmiformats.name::text = vaviews.format
			LEFT OUTER JOIN app_vos ON app_vos.appid = applications.id
			LEFT OUTER JOIN vowide_image_list_images ON vowide_image_list_images.id = va_provider_images.vowide_vmiinstanceid
			LEFT OUTER JOIN vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id AND vowide_image_lists.state::text = \'published\'
			WHERE  
			vaviews.va_version_published AND 
			NOT vaviews.va_version_archived AND
			applications.id = ?
		GROUP BY 
			applications.id, 
			vaviews.uri,
			vaviews.checksumfunc,
			vaviews.checksum,
			vaviews.osversion,
			vaviews.hypervisors,
			vaviews.va_id,
			vaviews.vappversionid,
			vaviews.vappversionid, 
			vaviews.vmiinstanceid, 
			vaviews.vmiflavourid, 
			vaviews.vmiinstance_guid,
			vaviews.vmiinstance_version,
			archs.id, 
			oses.id,
			vmiformats.id,
			app_vos.appid';
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$res = db()->query($q, array($vappliance->id))->fetchall();
		if( count($res) > 0 ){
			foreach($res as $r){
				if( count($r) === 0) {
					continue;
				}
				$result .= $r[0];
			}
		}
		$result .= '</appdb:appdb>';
		
		return $result;
	}

	public function findOS($os) {
		db()->setFetchMode(Zend_Db::FETCH_OBJ);
		$rs = db()->query("SELECT find_os('Windows RTM') AS os")->fetchAll();
		$oses = array();
		foreach ($rs as $r) {
			if ($r[0]->os !== null) {
				$os = $r[0]->os;
				$os = pg_to_php_array($os);
				if ($os[3] !== false) {
					$os[3] = pg_to_php_array($os[3]);
				} else {
					$os[3] = null;
				}
				$oses[] = $os;
			}
		}
		if (count($oses) > 0) {
			return $oses;	
		} else {
			return null;
		}
	}
}

class VMCasterOsSelector {
	public static function findOSOrAlias($os) {
		db()->setFetchMode(Zend_Db::FETCH_OBJ);
		$rs = db()->query( "SELECT find_os(?) AS os", array( trim($os) ) )->fetchAll();
		$oses = array();
		foreach ($rs as $r) {
			if ($r->os !== null) {
				$os = $r->os;
				$os = pg_to_php_array($os);
				if ($os[3] !== false) {
					$os[3] = pg_to_php_array($os[3]);
				} else {
					$os[3] = null;
				}
				$oses[] = $os;
			}
		}
		if (count($oses) > 0) {
			$os = $oses[0];
			$oses = new Default_Model_OSes();
			$oses->filter->id->equals($os[0]);
			if( count($oses->items) > 0 ){
				return $oses->items[0];
			}
		} else {
			return null;
		}
	}
	private static function findOsFamilyByOs($os){
		$os = self::getOs($os);
		if( $os === null ) {
			return null;
		}
		return self::getOsFamily($os->os_family_id);
	}
	private static function getOsFamily($osfamily){
		if( $osfamily instanceof Default_Model_OSFamily ){ //if OS Family model is given
			if( !is_numeric($osfamily->id) || intval($osfamily->id) <= 0 ){
				return null;
			} else {
				return $osfamily;
			}
		} else if ( $osfamily instanceof Default_Model_OS ){ //if OS model is given
			if( !is_numeric($osfamily->id) || intval($osfamily->id) <= 0 ){
				return null;
			} else {
				return $osfamily->getOSFamily();
			}
		} else if ( is_numeric($osfamily) && intval($osfamily) > 0 ){ //If OS Family id is given
			$osfamilies = new Default_Model_OSFamilies();
			$osfamilies->filter->id->equals($osfamily);
			if( count($osfamilies->items) > 0 ){
				return $osfamilies->items[0];
			}
		} else if( is_string($osfamily) && trim($osfamily) !== "" ){ //If OS Family name is given
			$osfamilies = new Default_Model_OSFamilies();
			$osfamilies->filter->name->ilike(trim($osfamily));
			if( count($osfamilies->items) > 0 ){
				return $osfamilies->items[0];
			}
		}
		
		//retrieve os family from oses
		if( is_string($osfamily) && trim($osfamily) !== "" ){
			$os = self::getOs($osfamily);
			if( $os !== null ){
				return $os->getOSFamily();
			}
		}
		return null;
	}
	private static function getOsOther($family){
		if( $family instanceof Default_Model_OS ){
			if( is_numeric($family->id) && intval($family->id)>0 ){
				return self::getOsOther($family->getOSFamily());
			}
			return null;
		} else if( $family instanceof Default_Model_OSFamily ){
			if( is_numeric($family->id) && intval($family->id)>0 ){
				$res = new Default_Model_OSes();
				$f1 = new Default_Model_OSesFilter();
				$f2 = new Default_Model_OSesFilter();
				$f1->os_family_id->equals($family->id);
				$f2->name->ilike("Other");
				$res->filter->chain($f1->chain($f2, "AND"), "AND");
				if( count($res->items) > 0 ){
					return $res->items[0];
				}
			}
			return null;
		} else if( (is_numeric($family) && intval($family)>0 ) || (is_string($family) && trim($family) !== "" )){
			$osfamily = self::getOsFamily($family);
			if( $osfamily === null ){
				return null;
			}
			return self::getOsOther($osfamily);
		} else {
			$osfamily = self::getOsFamily("others");
			if( $osfamily === null ){
				return null;
			}
			return self::getOsOther($osfamily);
		}
	}
	private static function getOs($osname){
		if( $osname instanceof Default_Model_OS ){
			if( !is_numeric($osname->id) || intval($osname->id) <= 0 ){
				return null;
			} else {
				return $osname;
			}
		} else if ( is_numeric($osname) && intval($osname) > 0 ){
			$oses = new Default_Model_OSes();
			$oses->filter->id->equals($osname);
			if( count($oses->items) > 0 ){
				return $oses->items[0];
			}
		} else if( is_string($osname) && trim($osname) !== "" ){
			$os = self::findOSOrAlias($osname);
			return $os;
		}
		return null;
	}
	public static function getOsInfo($osfamilyname, $osname, $osversion){
		$osfamily = self::getOsFamily($osfamilyname);
		$os = self::getOs($osname);
		$osversion = trim($osversion);
		$debug = "";
		if( $osversion === "") {
			$osversion = "n\a";
		}
		$osfromversion = self::findOSOrAlias($osversion);
		if( $osfamily === null && $os === null){
			$debug = "1. OSFamily=none OS=none \n";
			//retrieve from version or set family/os  as other/other
			if( $osfromversion !== null ){
				$debug .= "2. Trying to retrieve info from OSVersion...FOUND OS '" . $osfromversion->name . "' (id:" . $osfromversion->id . ")\n";
				$debug .= "3. Trying to retrieve OS Family from OS Name...";
				$os = $osfromversion;
				$osfamily = self::getOsFamily($os);
				if( $osfamily === null ){
					$debug .= "FAIL\n";
				}else{
					$debug .= "FOUND OS Family '" . $osfamily->name . "' (id:" . $osfamily->id .")\n";
				}
			}
		} else if( $osfamily === null && $os !== null ){ 
			$debug = "1. OSFamily=none OS=". $os->name ." (id:" . $os->id . ")\n";
			$debug .= "2. Trying to retrieve OS Family from OS Name...";
			//Get family from os 
			$osfamily = self::getOsFamily($os);
			if( $osfamily === null ){
				$debug .= "FAIL\n";
			}else{
				$debug .= "FOUND OS Family '" . $osfamily->name . "' (id:" . $osfamily->id .")\n";
			}
		} else if( $osfamily !== null && $os === null) {
			$debug = "1. OSFamily='". $osfamily->name ."' (id:" . $osfamily->id . ") OSname=none\n";
			//Try to guess form os family
			if( $os === null ){
				$debug .= "2. Trying to retrieve OS from given OS Family name...";
				$os = self::getOs($osfamilyname);
				$debug .= ( ( $os === null )?"FAIL\n":"FOUND OS '" . $os->name . "' (id:" . $os->id . ")\n" );
			}
			//Try to guess by OS Version
			if( $os === null ){
				$debug .= "3. Trying to retrieve OS name from OS Version...";
				$os = $osfromversion;
				$debug .= ( ( $os === null )?"FAIL\n":"FOUND OS '" . $os->name . "' (id:" . $os->id . ")\n" );
			}
			//Set OS as other
			if( $os === null ){
				$debug .= "3. Getting OS 'other' of '".$osfamily->name."' family...";
				$os = self::getOsOther($osfamily);
				$debug .= ( ( $os === null )?"FAIL\n":"FOUND\n" );
			}
		} 
		
		//Check results and set defaults.
		if( $osfamily !== null && $os !== null ){
			//Check that os is under the osfamily
			if( $os->OSFamilyID !== $osfamily->id ){
				$debug .= "[WARNING] Os name does not belong to OS family. Set OS name as " . $osfamily->name . "/Other...";
				$os = self::getOsOther($osfamily);
				$debug .= ( ($os === null )?"FAIL\n":"DONE\n");
			}
		}else if( $osfamily === null && $os === null){
			$os = self::getOsOther();
			$osfamily = $os->getOSFamily();
		}else if( $osfamily === null && $os !== null ){
			$osfamily = self::getOsFamily($os);
		}else if( $osfamily !== null && $os === null ){
			$os = self::getOsOther($osfamily);
		}
		
		return array(
			"osfamily" => $osfamily,
			"os" => $os,
			"osversion" => $osversion,
			"debug" => $debug
		);
	}
}

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
		curl_setopt($ch, CURLOPT_SSLCERT, APPLICATION_PATH . '/../bin/sec/usercert.pem');
		curl_setopt($ch, CURLOPT_SSLKEY, APPLICATION_PATH . '/../bin/sec/userkey.pem');
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

class ExternalDataNotification {
	const MESSAGE_TYPE_ERROR = 0;
	const MESSAGE_TYPE_WARNING = 1;
	
	private static function getRecipients(){
		return array(EmailConfiguration::getSupportAddress());
	}
	
	public static function createNotificationMessage($serviceName, $message, $message_type) {
		$res["subject"] = "[APPDB SERVICE";
		$body = "";
		if( $message_type === ExternalDataNotification::MESSAGE_TYPE_ERROR ) {
			$res["subject"] .= ' ERROR] From ' . $serviceName . ' service';
			$body = "An error occured on service " . $serviceName . " with message: \n\n";
		} else {
			$res["subject"] .= ' WARNING] From ' . $serviceName . ' service';
			$body = "A warning is raised from service " . $serviceName . " with message: \n\n";
		}
		
		if( trim($message) === '' ) {
			$body .= '[EMPTY MESSAGE]';
		} else {
			$body .= $message;
		}
		
		$res["message"] = $body;
		
		return $res;
	}
	
	public static function sendNotification($serviceName, $message="", $message_type = ExternalDataNotification::TYPE_ERROR){
		$recipients = self::getRecipients();
		$res = self::createNotificationMessage($serviceName, $message, $message_type);
		
		//sendMultipartMail($res['subject'], $recipients, $res['message'], null, 'appdb-reports@iasa.gr', 'enadyskolopassword');
		EmailService::sendReport($res['subject'], $recipients, $res['message']);
		return true;
	}
}
