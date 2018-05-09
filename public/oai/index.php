<?php
require('./OaiPmhServerAppDB.php');

header('content-type: text/xml;charset=UTF-8');
header('access-control-allow-methods: GET, POST');

switch ($_SERVER['REQUEST_METHOD']) {
case "POST":
	$args = $_POST;
	break;
case "GET":
	$args = $_GET;
	break;
default:
	header("HTTP/1.0 400 Bad Request");
}
$conf = json_decode(file_get_contents('../../application/configs/oai.conf'), true);
if (is_array($conf)) {
	$srv = new OaiPmhServerAppdB(
		array_key_exists("dbname", $conf) ? $conf["dbname"] : "appdb",
		array_key_exists("dbhost", $conf) ? $conf["dbhost"] : "localhost",
		array_key_exists("dbuser", $conf) ? $conf["dbuser"] : "appdb",
		array_key_exists("dbpass", $conf) ? $conf["dbpass"] : "",
		array_key_exists("dbport", $conf) ? $conf["dbport"] : "5432"
	);
	$data = $srv->processRequest($args);
	echo $data;
} else {
	error_log("application/configs/oai.conf missing or invalid");
	header("HTTP/1.0 500 Internal Server Error");
}
?>
