<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap();

header('Content-type: application/json; charset=utf-8');

$feed= array();

db()->setFetchMode(Zend_Db::FETCH_OBJ);
$rs = db()->query("SELECT * FROM idps where enabled=true")->fetchAll();
foreach ($rs as $r) {
	$feed_item=array();
	$feed_item['entityID'] = $r->entityid;
	$feed_item['country'] = $r->country;
	$feed_item['title'] = $r->title;
	$feed_item['weight'] = $r->weight;
	if($r->descr != null){ $feed_item['descr']=$r->descr;}
	if($r->lon != null && $r->lat !=null){ 
		$feed_item['geo'] = array('lat' => $r->lat, 'lon' => $r->lon);
	}
	array_push($feed, $feed_item);
}
$jsonp = json_encode($feed);


if(isset($_GET['callback'])){
    echo $_GET['callback'] . '(' . $jsonp . ')';
}else{
    echo $jsonp;
}

?>

