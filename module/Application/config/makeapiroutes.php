<?php
$xml = new SimpleXMLElement(file_get_contents('apiroutes.xml'));
$xml->xpath("//route");
foreach ($xml as $x) {
	$name = 'RESTAPI' . strval($x->attributes()->name);
	$type = strval($x->attributes()->type);
	$url = strval($x->attributes()->url);
	$fmts = $x->xpath('./format');
	$formats = array();
	foreach ($fmts as $fmt) {
		$format = array(
			'format' => strval($fmt),
			'xslt' => strval($fmt->attributes()->xslt)
		);
		$formats[] = $format;
	}
	$prms = $x->xpath('./param');
	$params = array();
	foreach ($prms as $prm) {
		$param = array(
			'name' => strval($prm->attributes()->name),
			'fmt' => strval($prm->attributes()->fmt)
		);
		$params[] = $param;
	}
	$resource = $x->xpath('./resource');
	$resource = strval($resource[0]);
	$comment = $x->xpath('./comment');
	if (count($comment) > 0) {
		$comment = str_replace("'", "\\'", strval($comment[0]));
	} else {
		$comment = null;
	}
	echo 
		"'$name' => [\n" .
		"	'type' => 'segment',\n" .
		"	'options' => [\n" .
		"		'route' => '/rest$url',\n" .
		"		'defaults' => [\n" .
		"			'controller' => Controller\\ApiController::class,\n" .
		"			'action' => '$type',\n" .
		"			'formats' => [\n"
	;
	foreach ($formats as $f) {
		echo 
			"				[\n" .
			"					'format' => '" . $f['format'] . "',\n" .
			"					'xslt' => '" . $f['xslt'] . "',\n" .
			"				],\n"
		;
	}
	echo
		"			],\n" .
		"			'params' => [\n"
		;
	foreach ($params as $param) {
		echo 
			"				[\n" .
			"					'name' => '" . $param['name'] . "',\n" .
			"					'fmt' => '" . $param['fmt'] . "',\n" .
			"				],\n"
		;
	}
	echo 
		"			],\n" .
		"			'resource' => '$resource',\n";
	if (! is_null($comment)) {
		echo
			"			'comment' => '$comment',\n"; 
	}
	echo
		"		],\n";
	echo 
		"		'constraints' => [\n" .
		"			'version' => '1\\.0',\n";
	foreach ($params as $param) {
		echo
			"			'" . $param['name'] . "' => '" . $param['fmt'] . "',\n";
	}
	echo
		"		],\n";
	echo
		"	],\n";
	echo
		"],\n"
	;
}
?>
