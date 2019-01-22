<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;

	function loadAPIroutes() {
		$apiRoutes = array();

		$fname = __DIR__ . "/apiroutes.xml";
		$f = fopen($fname,"r");
		$xml = fread($f, filesize($fname));
		fclose($f);
		$xml = new \SimpleXMLElement($xml);
		$routes = $xml->xpath('//route[@type="rest"]');
		foreach ($routes as $route) {
			$attrs = $route->attributes();
			$disabled = false;
			if (isset($attrs["disabled"]) && $attrs["disabled"] == "true") $disabled = true;
			if ( ! $disabled ) {
				$routeOpts = array();
				$routeOpts["resource"] = "".strval($route->resource);
				$routeOpts["controller"] = "api";
				$routeOpts["action"] = "rest";
				$routeOpts["uri"] = "".strval($route->attributes()->url);
				if ( isset($route->format) ) {
					if ( is_array($route->format) ) {
						$format = $route->format;
					} else {
						$format = array();
						$format[] = $route->format;
					}
					foreach($format as $f) {
						if ( strval($f) == "xml" ) {
							$routeOpts["format"] = strval($f);
							if ( isset($f->attributes()->xslt) ) $routeOpts["routeXslt"] = strval($f->attributes()->xslt);
						}
					}
				}
				$routePars = array();            
				if ( isset($route->param) ) {
					if ( is_array($route->param) ) {
						$param = $route->param;
					} else {
						$param = array();
						$param[] = $route->param;
					}
					foreach($param as $p) {
						$routePars["".strval($p->attributes()->name)] = strval($p->attributes()->fmt);
					}
				}
				$apiRoutes["".$attrs["name"]] = new Zend_Controller_Router_Route("/".$attrs["type"].$attrs["url"], $routeOpts, $routePars);
			}
		}
//		$apiRoutes["defaulttolatest"] = new Zend_Controller_Router_Route('/rest/',array(
//			"controller" => "api",
//			"action" => "latest"
//		));
//		$apiRoutes["defaulttoresources"] = new Zend_Controller_Router_Route('/rest/:version/',array(
//			"controller" => "api",
//			"action" => "resources"
//		));
//		$apiRoutes["latest"] = new Zend_Controller_Router_Route('/rest/latest/*',array(
//			"controller" => "api",
//			"action" => "latest"
//		));

		return apiRoutes;
//		foreach($apiRoutes as $rk=>$rv){
//		   $front->getRouter()->addRoute($rk,$rv);
//		}
	}

return [
    'router' => [
		'routes' => [
			'RESTAPIschemaList' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/schema',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestSchemaList',
						'comment' => 'A list of schemata for data returned by API resources',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIschemaEntry' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/schema/:xsdname',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
							[
								'name' => 'xsdname',
								'fmt' => '.+',
							],
						],
						'resource' => 'RestSchemaItem',
						'comment' => 'Request a schema definition for the resource specified by parameter :xsdname',
					],
					'constraints' => [
						'version' => '1\.0',
						'xsdname' => '.+',
					],
				],
			],
			'RESTAPIprofile' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/profile',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'persons',
							],
						],
						'params' => [
						],
						'resource' => 'RestProfile',
						'comment' => 'The profile of the logged-in user',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIbroker' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/broker',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
						],
						'resource' => 'RestBroker',
						'comment' => 'A broker resource which can execute multiple requests on other resources with one call',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIappsList' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'applications',
							],
							[
								'format' => 'json',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestAppList',
						'comment' => 'A list of software entry resources',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIdelappsList' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/deleted',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'applications',
							],
						],
						'params' => [
						],
						'resource' => 'RestDelAppList',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPImodappsItem' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/moderated/:id',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'applications',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
						],
						'resource' => 'RestModAppItem',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
					],
				],
			],
			'RESTAPImodappsList' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/moderated',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'applications',
							],
						],
						'params' => [
						],
						'resource' => 'RestModAppList',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIapplogistics' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/logistics',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestAppLogistics',
						'comment' => 'A list of software logistics per various properties',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIppllogistics' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/logistics',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestPplLogistics',
						'comment' => 'A list of people logistics per various properties',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIappsDetails' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/:id',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'applications',
							],
							[
								'format' => 'json',
								'xslt' => '',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
						],
						'resource' => 'RestAppItem',
						'comment' => 'The software entry specified by the parameter :id',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
					],
				],
			],
			'RESTAPIappItemPerms' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/:id/privileges',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
							[
								'format' => 'json',
								'xslt' => '',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
						],
						'resource' => 'RestAppPrivList',
						'comment' => 'A list of actors with privileges on the entry',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
					],
				],
			],
			'RESTAPIapppubs' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/:id/publications',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'applications',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
						],
						'resource' => 'RestAppPubList',
						'comment' => 'A list of scientific publications that the software entry specified by the parameter :id has led to',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
					],
				],
			],
			'RESTAPIapppub' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/:id/publications/:pid',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'applications',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
							[
								'name' => 'pid',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestAppPubItem',
						'comment' => 'The scientific publication entry specified by the parameter :pid',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
						'pid' => '\d+',
					],
				],
			],
			'RESTAPIapptags' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/:id/tags',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'applications',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
						],
						'resource' => 'RestAppTagList',
						'comment' => 'A list of tags that have been applied users and the system to the software entry specified by the parameter :id',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
					],
				],
			],
			'RESTAPIapptag' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/:id/tags/:tid',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'applications',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
							[
								'name' => 'tid',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestAppTagItem',
						'comment' => 'The tag entry specified by the parameter :tid',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
						'tid' => '\d+',
					],
				],
			],
			'RESTAPIrelatedApps' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/:id/relatedapps',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'applications',
							],
							[
								'format' => 'json',
								'xslt' => '',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
						],
						'resource' => 'RestRelAppList',
						'comment' => 'A list of software entries that related to the software entry specified by the paramter :id',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
					],
				],
			],
			'RESTAPIrelatedAppsLogistics' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/:id/relatedapps/logistics',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
						],
						'resource' => 'RestRelAppLogistics',
						'comment' => 'A list of related application logistics per various properties',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
					],
				],
			],
			'RESTAPIrateReport' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/:id/ratingsreport',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
						],
						'resource' => 'RestAppRatingReport',
						'comment' => 'A report about ratings the software entry specified by the parameter :id has received by all users',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
					],
				],
			],
			'RESTAPIrateReport2' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/:id/ratingsreport/:type',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
							[
								'name' => 'type',
								'fmt' => '.+',
							],
						],
						'resource' => 'RestAppRatingReport',
						'comment' => 'A report about ratings the software entry specified by the parameter :id has received by registered (:type="internal") or anonymous (:type="external") users',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
						'type' => '.+',
					],
				],
			],
			'RESTAPIratings' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/:id/ratings',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'applications',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
						],
						'resource' => 'RestAppRatingList',
						'comment' => 'A list of all the ratings the software entry specified by the parameter :id has received by all users',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
					],
				],
			],
			'RESTAPIrating' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/:id/ratings/:rid',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'applications',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
							[
								'name' => 'rid',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestAppRatingItem',
						'comment' => 'The rating entry specified by the parameter :rid',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
						'rid' => '\d+',
					],
				],
			],
			'RESTAPIapphistory' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/:id/history',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'history',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
						],
						'resource' => 'RestAppHistoryList',
						'comment' => 'A list of all previous states that the software entry specified by the parameter :id has been in, with regard to changes by authorized users',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
					],
				],
			],
			'RESTAPIapphistoryitem' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/:id/history/:hid',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'history',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
							[
								'name' => 'hid',
								'fmt' => '.+',
							],
						],
						'resource' => 'RestAppHistoryItem',
						'comment' => 'The historical state of a software entry specified by parameter :hid',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
						'hid' => '.+',
					],
				],
			],
			'RESTAPIapphistorydiff' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/:id/history/:hid/diff',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'history',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
							[
								'name' => 'hid',
								'fmt' => '.+',
							],
						],
						'resource' => 'RestAppHistoryDiffItem',
						'comment' => 'The unified diff of old/new historical states of a software entry specified by parameter :hid',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
						'hid' => '.+',
					],
				],
			],
			'RESTAPIapphistoryrbitem' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/:id/history/:hid/rollback',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'history',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
							[
								'name' => 'hid',
								'fmt' => '.+',
							],
						],
						'resource' => 'RestAppHistoryRBItem',
						'comment' => 'Rolls-back a software entry to the state it was in as described by the parameter :hid',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
						'hid' => '.+',
					],
				],
			],
			'RESTAPIappvalist' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/:id/virtualization',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'virtualization',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
						],
						'resource' => 'RestAppVAList',
						'comment' => '',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
					],
				],
			],
			'RESTAPIappvaitem' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/:id/virtualization/:vappid',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'virtualization',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
							[
								'name' => 'vappid',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestAppVAItem',
						'comment' => '',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
						'vappid' => '\d+',
					],
				],
			],
			'RESTAPIappvaitemimages' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/:id/virtualization/productionimages',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
						],
						'resource' => 'RestVAImageList',
						'comment' => '',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
					],
				],
			],
			'RESTAPIappvaitemversion' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/:id/virtualization/:vappid/:versionid',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'virtualization',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
							[
								'name' => 'vappid',
								'fmt' => '\d+',
							],
							[
								'name' => 'versionid',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestAppVAVersionItem',
						'comment' => '',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
						'vappid' => '\d+',
						'versionid' => '\d+',
					],
				],
			],
			'RESTAPIappvaitemintegrity' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/:id/virtualization/integrity/:versionid',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
							[
								'name' => 'versionid',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestAppVAVersionIntegrityItem',
						'comment' => '',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
						'versionid' => '\d+',
					],
				],
			],
			'RESTAPIappcontextualizationlist' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/:id/contextualization',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
						],
						'resource' => 'RestAppContextScriptList',
						'comment' => '',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
					],
				],
			],
			'RESTAPIappcontextualizationlistitem' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/:id/contextualization/:scriptid',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
							[
								'name' => 'scriptid',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestAppContextScriptItem',
						'comment' => '',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
						'scriptid' => '\d+',
					],
				],
			],
			'RESTAPIappcontext' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/:id/contextualization/metadata',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
						],
						'resource' => 'RestAppContext',
						'comment' => '',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
					],
				],
			],
			'RESTAPIswappvaitemimages' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/:id/contextualization/productionimages',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
						],
						'resource' => 'RestSWAppImageList',
						'comment' => '',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
					],
				],
			],
			'RESTAPIcontextscriptformats' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/contextualization/formats',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestContextScriptFormatList',
						'comment' => '',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIpersonprivs' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/:id/privileges',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'applications',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestPplPrivList',
						'comment' => 'A list of the person\'s privileges on other targets',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '\d+',
					],
				],
			],
			'RESTAPIpplvolist' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/:id/vos',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'vos',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestPplVOList',
						'comment' => 'A list of VOs the user specified by parameter :id is related to',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '\d+',
					],
				],
			],
			'RESTAPIvomemberlist' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/:id/vos/member',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'vos',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestVOMemberList',
						'comment' => 'A list of VOs the user specified by parameter :id is a member of',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '\d+',
					],
				],
			],
			'RESTAPIvomanagerlist' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/:id/vos/manager',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'vos',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestVOManagerList',
						'comment' => 'A list of VOs the user specified by parameter :id is a manager of',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '\d+',
					],
				],
			],
			'RESTAPIvodeputylist' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/:id/vos/deputy',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'vos',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestVODeputyList',
						'comment' => 'A list of VOs the user specified by parameter :id is a deputy of',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '\d+',
					],
				],
			],
			'RESTAPIvoexpertlist' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/:id/vos/expert',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'vos',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestVOExpertList',
						'comment' => 'A list of VOs the user specified by parameter :id is an expert of',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '\d+',
					],
				],
			],
			'RESTAPIvoshifterlist' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/:id/vos/shifter',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'vos',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestVOShifterList',
						'comment' => 'A list of VOs the user specified by parameter :id is a shifter of',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '\d+',
					],
				],
			],
			'RESTAPIappreport' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/:id/applications/report',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'applications',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestAppReport',
						'comment' => 'A list of applications the user specified by parameter :id is related to',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '\d+',
					],
				],
			],
			'RESTAPIvoreport' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/:id/vos/report',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'applications',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestVOReport',
						'comment' => 'A list of VOs the user by parameter :id is a member of contact of',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '\d+',
					],
				],
			],
			'RESTAPIappfollowlist' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/:id/applications/followed',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'applications',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestAppFollowedList',
						'comment' => 'A list of applications the user specified by parameter :id follows',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '\d+',
					],
				],
			],
			'RESTAPIappfollowitem' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/:id/applications/followed/:appid',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'applications',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '\d+',
							],
							[
								'name' => 'appid',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestAppFollowedItem',
						'comment' => 'The software entry that the user follows, specified by the parameter :appid',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '\d+',
						'appid' => '\d+',
					],
				],
			],
			'RESTAPIappfollowLogistics' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/:id/applications/followed/logistics',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestAppFollowedLogistics',
						'comment' => 'A list of software logistics per various properties',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '\d+',
					],
				],
			],
			'RESTAPIbookmarks' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/:id/applications/bookmarked',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'applications',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestAppBookmarkList',
						'comment' => 'A list of all software entries that the user specified by parameter :id has bookmarked',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '\d+',
					],
				],
			],
			'RESTAPIbookmarks2' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/:id/applications/bookmarked/:bmid',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'applications',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '\d+',
							],
							[
								'name' => 'bmid',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestAppBookmarkItem',
						'comment' => 'The software entry that the user has bookmarked, as specified by parameter :bmid',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '\d+',
						'bmid' => '\d+',
					],
				],
			],
			'RESTAPIbookmarkLogistics' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/:id/applications/bookmarked/logistics',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestAppBookmarkLogistics',
						'comment' => 'A list of software logistics per various properties',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '\d+',
					],
				],
			],
			'RESTAPIeditableApps' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/:id/applications/editable',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'applications',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestEdtAppList',
						'comment' => 'A list of all software entries that the user can edit',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '\d+',
					],
				],
			],
			'RESTAPIeditableAppLogistics' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/:id/applications/editable/logistics',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestEdtAppLogistics',
						'comment' => 'A list of software logistics per various properties',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '\d+',
					],
				],
			],
			'RESTAPIownedApps' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/:id/applications/owned',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'applications',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestOwnAppList',
						'comment' => 'A list of all the software entries that the user "owns"',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '\d+',
					],
				],
			],
			'RESTAPIownedAppLogistics' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/:id/applications/owned/logistics',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestOwnAppLogistics',
						'comment' => 'A list of software logistics per various properties',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '\d+',
					],
				],
			],
			'RESTAPIassociatedApps' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/:id/applications/associated',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'applications',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestAscAppList',
						'comment' => 'A list of all the software entries in whose contact list the user is in',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '\d+',
					],
				],
			],
			'RESTAPIassociatedAppLogistics' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/:id/applications/associated/logistics',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestAscAppLogistics',
						'comment' => 'A list of software logistics per various properties',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '\d+',
					],
				],
			],
			'RESTAPIpeopleList' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'persons',
							],
						],
						'params' => [
						],
						'resource' => 'RestPplList',
						'comment' => 'A list of the registered users',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIdelpplList' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/deleted',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'persons',
							],
						],
						'params' => [
						],
						'resource' => 'RestDelPplList',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIpeopleDetails' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/:id',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'persons',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestPplItem',
						'comment' => 'The profile entry of the user specified by the parameter :id',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '\d+',
					],
				],
			],
			'RESTAPInamedPeopleDetails' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/:name',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'persons',
							],
						],
						'params' => [
							[
								'name' => 'name',
								'fmt' => 's:.+',
							],
						],
						'resource' => 'RestPplItem',
					],
					'constraints' => [
						'version' => '1\.0',
						'name' => 's:.+',
					],
				],
			],
			'RESTAPIcountries' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/regional',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'regional',
							],
						],
						'params' => [
						],
						'resource' => 'RestRegionalList',
						'comment' => 'A list of countries that registed users are affiliated with',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIcategories' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/categories',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestCategoryList',
						'comment' => 'A flat list of categories by which a user may classify software entries',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIhcategories' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/categories/hierarchical',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'hcat',
							],
						],
						'params' => [
						],
						'resource' => 'RestCategoryList',
						'comment' => 'A hierarchical list of categories by which a user may classify software entries',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIcategory' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/categories/:id',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
						],
						'resource' => 'RestCategoryItem',
						'comment' => 'The software category entry specified by parameter :id',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
					],
				],
			],
			'RESTAPIlangs' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/languages',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestLangList',
						'comment' => 'A list of programming languages by which a user may classify software entries',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIoses' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/oses',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestOSList',
						'comment' => 'A list of computer operating systems by which a user may classify software entries',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIlicenses' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/licenses',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestLicenseList',
						'comment' => 'A list of software licenses by which a user may classify software entries',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIhvs' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/hypervisors',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestHVList',
						'comment' => 'A list of virtualization hypervisors by which a user may classify software entries',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIvmifmt' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/vmiformats',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestVMIFmtList',
						'comment' => 'A list of virtualization image formats by which a user may classify software entries',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIarchs' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/archs',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestArchList',
						'comment' => 'A list of computer architectures by which a user may classify software entries',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIva_providers' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/va_providers',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestVAProvidersList',
						'comment' => 'A list of virtualization providers',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIva_provider' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/va_providers/:id',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '.+',
							],
						],
						'resource' => 'RestVAProviderItem',
						'comment' => 'The virtualization provider entry specified by parameter :id',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '.+',
					],
				],
			],
			'RESTAPIsites' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/sites',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'site',
							],
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestSiteList',
						'comment' => 'A list of sites',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIsitelogistics' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/sites/logistics',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestSiteLogistics',
						'comment' => 'A list of Site logistics per various properties',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIsite' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/sites/:id',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'site',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '.+',
							],
						],
						'resource' => 'RestSiteItem',
						'comment' => 'The site entry specified by the parameter :id',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '.+',
					],
				],
			],
			'RESTAPIsitefiltercheck' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/sites/filter/normalize',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestSiteFilterNormalization',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIsitefilterreflection' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/sites/filter/reflect',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestSiteFilterReflection',
						'comment' => 'Filter reflection resource for site searchers',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIdisciplines' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/disciplines',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'applications',
							],
						],
						'params' => [
						],
						'resource' => 'RestDisciplineList',
						'comment' => 'A list of disciplines by which a user may classify software entries',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIdiscipline' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/disciplines/:id',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'applications',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
						],
						'resource' => 'RestDisciplineItem',
						'comment' => 'The software discipline entry specified by parameter :id',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
					],
				],
			],
			'RESTAPIhdisciplines' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/disciplines/hierarchical',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'hdisc',
							],
						],
						'params' => [
						],
						'resource' => 'RestDisciplineList',
						'comment' => 'A hierarchical list of disciplines by which a user may classify software entries',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPImws' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/middlewares',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'applications',
							],
						],
						'params' => [
						],
						'resource' => 'RestMWList',
						'comment' => 'A list of distributed computing middlewares that may be listed under a software entry',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIstatuses' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/statuses',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'applications',
							],
						],
						'params' => [
						],
						'resource' => 'RestStatusList',
						'comment' => 'A list of states with regard to usability that a software entry may be classified by',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIvos' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/vos',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'vos',
							],
						],
						'params' => [
						],
						'resource' => 'RestVOList',
						'comment' => 'A list of grid Virtual Organizations which may provide access to software',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIvo' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/vos/:id',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'vos',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
						],
						'resource' => 'RestVOItem',
						'comment' => 'The grid Virtual Organization entry specified by parameter :id',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
					],
				],
			],
			'RESTAPIstorestats1' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/storestats/:from/:to',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
							[
								'name' => 'from',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
							[
								'name' => 'to',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
						],
						'resource' => 'RestStoreStatsList',
						'comment' => 'Store statistics',
					],
					'constraints' => [
						'version' => '1\.0',
						'from' => '\d\d\d\d-\d\d-\d\d',
						'to' => '\d\d\d\d-\d\d-\d\d',
					],
				],
			],
			'RESTAPIstorestats2' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/storestats/:from',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
							[
								'name' => 'from',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
						],
						'resource' => 'RestStoreStatsList',
						'comment' => 'Store statistics',
					],
					'constraints' => [
						'version' => '1\.0',
						'from' => '\d\d\d\d-\d\d-\d\d',
					],
				],
			],
			'RESTAPIstorestats3' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/storestats',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
						],
						'resource' => 'RestStoreStatsList',
						'comment' => 'Store statistics',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIdisciplinevostats1' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/disciplines/:id/vostats/:from/:to',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
							[
								'name' => 'from',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
							[
								'name' => 'to',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
						],
						'resource' => 'RestDisciplineVOStatsList',
						'comment' => 'VO statistics by discipline specified by parameter :id',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
						'from' => '\d\d\d\d-\d\d-\d\d',
						'to' => '\d\d\d\d-\d\d-\d\d',
					],
				],
			],
			'RESTAPIdisciplinevostats2' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/disciplines/:id/vostats/:from',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
							[
								'name' => 'from',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
						],
						'resource' => 'RestDisciplineVOStatsList',
						'comment' => 'VO statistics by discipline specified by parameter :id',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
						'from' => '\d\d\d\d-\d\d-\d\d',
					],
				],
			],
			'RESTAPIdisciplinevostats3' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/disciplines/:id/vostats',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
						],
						'resource' => 'RestDisciplineVOStatsList',
						'comment' => 'VO statistics by discipline specified by parameter :id',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
					],
				],
			],
			'RESTAPIdisciplinesvostats1' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/disciplines/vostats/:from/:to',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
							[
								'name' => 'from',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
							[
								'name' => 'to',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
						],
						'resource' => 'RestDisciplineVOStatsList',
						'comment' => 'VO statistics by discipline',
					],
					'constraints' => [
						'version' => '1\.0',
						'from' => '\d\d\d\d-\d\d-\d\d',
						'to' => '\d\d\d\d-\d\d-\d\d',
					],
				],
			],
			'RESTAPIdisciplinesvostats2' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/disciplines/vostats/:from',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
							[
								'name' => 'from',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
						],
						'resource' => 'RestDisciplineVOStatsList',
						'comment' => 'VO statistics by discipline',
					],
					'constraints' => [
						'version' => '1\.0',
						'from' => '\d\d\d\d-\d\d-\d\d',
					],
				],
			],
			'RESTAPIdisciplinesvostats3' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/disciplines/vostats',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
						],
						'resource' => 'RestDisciplineVOStatsList',
						'comment' => 'VO statistics by discipline',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIdisciplineappstats1' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/disciplines/:id/appstats/:from/:to',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
							[
								'name' => 'from',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
							[
								'name' => 'to',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
						],
						'resource' => 'RestDisciplineAppStatsList',
						'comment' => 'Application statistics by discipline specified by parameter :id',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
						'from' => '\d\d\d\d-\d\d-\d\d',
						'to' => '\d\d\d\d-\d\d-\d\d',
					],
				],
			],
			'RESTAPIdisciplineappstats2' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/disciplines/:id/appstats/:from',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
							[
								'name' => 'from',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
						],
						'resource' => 'RestDisciplineAppStatsList',
						'comment' => 'Application statistics by discipline specified by parameter :id',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
						'from' => '\d\d\d\d-\d\d-\d\d',
					],
				],
			],
			'RESTAPIdisciplineappstats3' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/disciplines/:id/appstats',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
						],
						'resource' => 'RestDisciplineAppStatsList',
						'comment' => 'Application statistics by discipline specified by parameter :id',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
					],
				],
			],
			'RESTAPIdisciplinesappstats1' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/disciplines/appstats/:from/:to',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
							[
								'name' => 'from',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
							[
								'name' => 'to',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
						],
						'resource' => 'RestDisciplineAppStatsList',
						'comment' => 'Application statistics by discipline',
					],
					'constraints' => [
						'version' => '1\.0',
						'from' => '\d\d\d\d-\d\d-\d\d',
						'to' => '\d\d\d\d-\d\d-\d\d',
					],
				],
			],
			'RESTAPIdisciplinesappstats2' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/disciplines/appstats/:from',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
							[
								'name' => 'from',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
						],
						'resource' => 'RestDisciplineAppStatsList',
						'comment' => 'Application statistics by discipline',
					],
					'constraints' => [
						'version' => '1\.0',
						'from' => '\d\d\d\d-\d\d-\d\d',
					],
				],
			],
			'RESTAPIdisciplinesappstats3' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/disciplines/appstats',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
						],
						'resource' => 'RestDisciplineAppStatsList',
						'comment' => 'Application statistics by discipline',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIcategoryappstats1' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/categories/:id/appstats/:from/:to',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
							[
								'name' => 'from',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
							[
								'name' => 'to',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
						],
						'resource' => 'RestCategoryAppStatsList',
						'comment' => 'Application statistics by category specified by parameter :id',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
						'from' => '\d\d\d\d-\d\d-\d\d',
						'to' => '\d\d\d\d-\d\d-\d\d',
					],
				],
			],
			'RESTAPIcategoryappstats2' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/categories/:id/appstats/:from',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
							[
								'name' => 'from',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
						],
						'resource' => 'RestCategoryAppStatsList',
						'comment' => 'Application statistics by category specified by parameter :id',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
						'from' => '\d\d\d\d-\d\d-\d\d',
					],
				],
			],
			'RESTAPIcategoryappstats3' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/categories/:id/appstats',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
						],
						'resource' => 'RestCategoryAppStatsList',
						'comment' => 'Application statistics by category specified by parameter :id',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
					],
				],
			],
			'RESTAPIcategoriesappstats1' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/categories/appstats/:from/:to',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
							[
								'name' => 'from',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
							[
								'name' => 'to',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
						],
						'resource' => 'RestCategoryAppStatsList',
						'comment' => 'Application statistics by category',
					],
					'constraints' => [
						'version' => '1\.0',
						'from' => '\d\d\d\d-\d\d-\d\d',
						'to' => '\d\d\d\d-\d\d-\d\d',
					],
				],
			],
			'RESTAPIcategoriesappstats2' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/categories/appstats/:from',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
							[
								'name' => 'from',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
						],
						'resource' => 'RestCategoryAppStatsList',
						'comment' => 'Application statistics by category',
					],
					'constraints' => [
						'version' => '1\.0',
						'from' => '\d\d\d\d-\d\d-\d\d',
					],
				],
			],
			'RESTAPIcategoriesappstats3' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/categories/appstats',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
						],
						'resource' => 'RestCategoryAppStatsList',
						'comment' => 'Application statistics by category',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIvoappstats1' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/vos/:id/appstats/:from/:to',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
							[
								'name' => 'from',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
							[
								'name' => 'to',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
						],
						'resource' => 'RestVOAppStatsList',
						'comment' => 'Application statistics for the grid Virtual Organization entry specified by parameter :id',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
						'from' => '\d\d\d\d-\d\d-\d\d',
						'to' => '\d\d\d\d-\d\d-\d\d',
					],
				],
			],
			'RESTAPIvoappstats2' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/vos/:id/appstats/:from',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
							[
								'name' => 'from',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
						],
						'resource' => 'RestVOAppStatsList',
						'comment' => 'Application statistics for the grid Virtual Organization entry specified by parameter :id',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
						'from' => '\d\d\d\d-\d\d-\d\d',
					],
				],
			],
			'RESTAPIvoappstats3' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/vos/:id/appstats',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
						],
						'resource' => 'RestVOAppStatsList',
						'comment' => 'Application statistics for the grid Virtual Organization entry specified by parameter :id',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
					],
				],
			],
			'RESTAPIvosappstats1' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/vos/appstats/:from/:to',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
							[
								'name' => 'from',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
							[
								'name' => 'to',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
						],
						'resource' => 'RestVOAppStatsList',
						'comment' => 'Application statistics for all grid Virtual Organization entries',
					],
					'constraints' => [
						'version' => '1\.0',
						'from' => '\d\d\d\d-\d\d-\d\d',
						'to' => '\d\d\d\d-\d\d-\d\d',
					],
				],
			],
			'RESTAPIvosappstats2' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/vos/appstats/:from',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
							[
								'name' => 'from',
								'fmt' => '\d\d\d\d-\d\d-\d\d',
							],
						],
						'resource' => 'RestVOAppStatsList',
						'comment' => 'Application statistics for all grid Virtual Organization entries',
					],
					'constraints' => [
						'version' => '1\.0',
						'from' => '\d\d\d\d-\d\d-\d\d',
					],
				],
			],
			'RESTAPIvosappstats3' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/stats/vos/appstats',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
						],
						'resource' => 'RestVOAppStatsList',
						'comment' => 'Application statistics for all grid Virtual Organization entries',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIroles' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/roles',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'persons',
							],
						],
						'params' => [
						],
						'resource' => 'RestRoleList',
						'comment' => 'A list of available roles for users to choose from',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPItags' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/tags',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestTagList',
						'comment' => 'A list of all the tags that have been used in one or more software entries',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIcontacttypes' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/contacttypes',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
						],
						'resource' => 'RestContactTypeList',
						'comment' => 'A list of contact types that a user may specify in his or her profile',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIappfiltercheck' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/filter/normalize',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestAppFilterNormalization',
						'comment' => 'Filter normalization resource for application searches',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIappfilterreflection' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/filter/reflect',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestAppFilterReflection',
						'comment' => 'Filter reflection resource for application searchers',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIpplfiltercheck' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/filter/normalize',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestPplFilterNormalization',
						'comment' => 'Filter normalization resource for user searches',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIpplfilterreflection' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/people/filter/reflect',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestPplFilterReflection',
						'comment' => 'Filter reflection resource for user searches',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIvofiltercheck' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/vos/filter/normalize',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestVOFilterNormalization',
						'comment' => 'Filter normalization resource for VO searches',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIvofilterreflection' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/vos/filter/reflect',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestVOFilterReflection',
						'comment' => 'Filter reflection resource for VO searches',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIdissemination' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/dissemination',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'dissemination',
							],
						],
						'params' => [
						],
						'resource' => 'RestDisseminationList',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIdisseminationentry' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/dissemination/:id',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'dissemination',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '\d+',
							],
						],
						'resource' => 'RestDisseminationItem',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '\d+',
					],
				],
			],
			'RESTAPIdisseminationfiltercheck' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/dissemination/filter/normalize',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestDisseminationFilterNormalization',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIdisseminationfilterreflection' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/dissemination/filter/reflect',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestDisseminationFilterReflection',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIactorgroup' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/accessgroups/:id',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '-{0,1}\d+',
							],
						],
						'resource' => 'RestAccessGroupItem',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '-{0,1}\d+',
					],
				],
			],
			'RESTAPIactorgroups' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/accessgroups',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestAccessGroupList',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIresources' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/resources',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
						],
						'params' => [
						],
						'resource' => 'RestAppDBResourceList',
						'comment' => 'A list of all resource provided by the AppDB REST web-API',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIsciclass' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/classification/version',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
							[
								'format' => 'json',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestSciClassList',
						'comment' => 'EGI Scientific Discipline Classification API',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIsciclassalias1' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/classification/versions',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
							[
								'format' => 'json',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestSciClassList',
						'comment' => 'Alias for EGI Scientific Discipline Classification API',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIsciclassitem' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/classification/version/:id',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
							[
								'format' => 'json',
								'xslt' => '',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
						],
						'resource' => 'RestSciClassItem',
						'comment' => 'EGI Scientific Discipline Classification API',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
					],
				],
			],
			'RESTAPIsciclassitemalias1' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/classification/versions/:id',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
							[
								'format' => 'json',
								'xslt' => '',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|s:.+)',
							],
						],
						'resource' => 'RestSciClassItem',
						'comment' => 'Alias for EGI Scientific Discipline Classification API',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|s:.+)',
					],
				],
			],
			'RESTAPIvologistics' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/vos/logistics',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'proxy',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestVOLogistics',
						'comment' => 'A list of VO logistics per various properties',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIentityrelationtypes' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/entity/relationtypes',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestRelationTypeList',
						'comment' => 'A list of possible entity relation types',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIswappliancereport' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/applications/swappliance/report',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => '',
							],
						],
						'params' => [
						],
						'resource' => 'RestVAppSWAppList',
						'comment' => 'A list of vappliances with their refered software appliances',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIdatasets' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/datasets',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'ds_datasets',
							],
						],
						'params' => [
						],
						'resource' => 'RestDatasetList',
						'comment' => 'A list of available datasets',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIdsexchangeformats' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/datasets/exchangeformats',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'ds_exchange_fmt',
							],
						],
						'params' => [
						],
						'resource' => 'RestDSExchangeFormatList',
						'comment' => 'A list of available exchange formats for datasets',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIdsconnectiontypes' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/datasets/interfaces',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'ds_connection_types',
							],
						],
						'params' => [
						],
						'resource' => 'RestDSConnectionTypeList',
						'comment' => 'A list of available connection types for datasets',
					],
					'constraints' => [
						'version' => '1\.0',
					],
				],
			],
			'RESTAPIdatasetitem' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/datasets/:id',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'ds_datasets',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|.+)',
							],
						],
						'resource' => 'RestDatasetItem',
						'comment' => 'The dataset entry specified by the parameter :id',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|.+)',
					],
				],
			],
			'RESTAPIdatasetverlist' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/datasets/:id/versions',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'ds_datasets',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|.+)',
							],
						],
						'resource' => 'RestDatasetVersionList',
						'comment' => 'The dataset version list',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|.+)',
					],
				],
			],
			'RESTAPIdatasetveritem' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/datasets/:id/versions/:vid',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'ds_datasets',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|.+)',
							],
							[
								'name' => 'vid',
								'fmt' => '(\d+|.+)',
							],
						],
						'resource' => 'RestDatasetVersionItem',
						'comment' => 'The dataset version entry specified by the parameter :vid',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|.+)',
						'vid' => '(\d+|.+)',
					],
				],
			],
			'RESTAPIdatasetloclist' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/datasets/:id/versions/:vid/locations',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'ds_datasets',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|.+)',
							],
							[
								'name' => 'vid',
								'fmt' => '(\d+|.+)',
							],
						],
						'resource' => 'RestDatasetLocationList',
						'comment' => 'The dataset location list for the dataset version specified by the parameter :vid',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|.+)',
						'vid' => '(\d+|.+)',
					],
				],
			],
			'RESTAPIdatasetlocitem' => [
				'type' => 'segment',
				'options' => [
					'route' => '/rest/:version/datasets/:id/versions/:vid/locations/:lid',
					'defaults' => [
						'controller' => Controller\ApiController::class,
						'action' => 'rest',
						'formats' => [
							[
								'format' => 'xml',
								'xslt' => 'ds_datasets',
							],
						],
						'params' => [
							[
								'name' => 'id',
								'fmt' => '(\d+|.+)',
							],
							[
								'name' => 'vid',
								'fmt' => '(\d+|.+)',
							],
							[
								'name' => 'lid',
								'fmt' => '(\d+|.+)',
							],
						],
						'resource' => 'RestDatasetLocationItem',
						'comment' => 'The dataset location entry specified by the parameter :lid',
					],
					'constraints' => [
						'version' => '1\.0',
						'id' => '(\d+|.+)',
						'vid' => '(\d+|.+)',
						'lid' => '(\d+|.+)',
					],
				],
			],
            'home' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
             'Aaimpx' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/aaimpx[/:action]',
                      'defaults' => [
                          'controller' => Controller\AaimpxController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Abuse' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/abuse[/:action]',
                      'defaults' => [
                          'controller' => Controller\AbuseController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Api02action' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/api02action[/:action]',
                      'defaults' => [
                          'controller' => Controller\Api02actionController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Api02' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/api02[/:action]',
                      'defaults' => [
                          'controller' => Controller\Api02Controller::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Api' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/api[/:action]',
                      'defaults' => [
                          'controller' => Controller\ApiController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Apps' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/apps[/:action]',
                      'defaults' => [
                          'controller' => Controller\AppsController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Appstats' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/appstats[/:action]',
                      'defaults' => [
                          'controller' => Controller\AppstatsController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Changelog' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/changelog[/:action]',
                      'defaults' => [
                          'controller' => Controller\ChangelogController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Datasets' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/datasets[/:action]',
                      'defaults' => [
                          'controller' => Controller\DatasetsController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Elixir' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/elixir[/:action]',
                      'defaults' => [
                          'controller' => Controller\ElixirController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Error' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/error[/:action]',
                      'defaults' => [
                          'controller' => Controller\ErrorController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Gadgets' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/gadgets[/:action]',
                      'defaults' => [
                          'controller' => Controller\GadgetsController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Gocdb' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/gocdb[/:action]',
                      'defaults' => [
                          'controller' => Controller\GocdbController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Harvest' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/harvest[/:action]',
                      'defaults' => [
                          'controller' => Controller\HarvestController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Help' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/help[/:action]',
                      'defaults' => [
                          'controller' => Controller\HelpController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Index' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/index[/:action]',
                      'defaults' => [
                          'controller' => Controller\IndexController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Mail' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/mail[/:action]',
                      'defaults' => [
                          'controller' => Controller\MailController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Mobile' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/mobile[/:action]',
                      'defaults' => [
                          'controller' => Controller\MobileController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'News' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/news[/:action]',
                      'defaults' => [
                          'controller' => Controller\NewsController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Ngi' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/ngi[/:action]',
                      'defaults' => [
                          'controller' => Controller\NgiController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Oai' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/oai[/:action]',
                      'defaults' => [
                          'controller' => Controller\OaiController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'People' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/people[/:action]',
                      'defaults' => [
                          'controller' => Controller\PeopleController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Pplstats' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/pplstats[/:action]',
                      'defaults' => [
                          'controller' => Controller\PplstatsController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Repository' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/repository[/:action]',
                      'defaults' => [
                          'controller' => Controller\RepositoryController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Res' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/res[/:action]',
                      'defaults' => [
                          'controller' => Controller\ResController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Saml' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/saml[/:action]',
                      'defaults' => [
                          'controller' => Controller\SamlController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Sites' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/sites[/:action]',
                      'defaults' => [
                          'controller' => Controller\SitesController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Storage' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/storage[/:action]',
                      'defaults' => [
                          'controller' => Controller\StorageController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Supported' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/supported[/:action]',
                      'defaults' => [
                          'controller' => Controller\SupportedController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Texttoimage' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/texttoimage[/:action]',
                      'defaults' => [
                          'controller' => Controller\TexttoimageController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Unsupported' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/unsupported[/:action]',
                      'defaults' => [
                          'controller' => Controller\UnsupportedController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Users' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/users[/:action]',
                      'defaults' => [
                          'controller' => Controller\UsersController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
             'Vo' => [
                  'type'    => Segment::class,
                  'options' => [
                      'route'    => '/vo[/:action]',
                      'defaults' => [
                          'controller' => Controller\VoController::class,
                          'action'     => 'index',
                      ],
                  ],
              ],
//            'default' => [
//                'type'    => Segment::class,
//                'options' => [
//                    'route'    => '/images[/:action]',
//					'defaults' => [
//                        'controller' => Controller\IndexController::class,
//                        'action'     => 'index',
//                    ],
//                ],
//            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => InvokableFactory::class,
			Controller\AaimpxController::class => InvokableFactory::class,
			Controller\AbuseController::class => InvokableFactory::class,
			Controller\Api02actionController::class => InvokableFactory::class,
			Controller\Api02Controller::class => InvokableFactory::class,
			Controller\ApiController::class => InvokableFactory::class,
			Controller\AppsController::class => InvokableFactory::class,
			Controller\AppstatsController::class => InvokableFactory::class,
			Controller\ChangelogController::class => InvokableFactory::class,
			Controller\DatasetsController::class => InvokableFactory::class,
			Controller\ElixirController::class => InvokableFactory::class,
			Controller\ErrorController::class => InvokableFactory::class,
			Controller\GadgetsController::class => InvokableFactory::class,
			Controller\GocdbController::class => InvokableFactory::class,
			Controller\HarvestController::class => InvokableFactory::class,
			Controller\HelpController::class => InvokableFactory::class,
			Controller\MailController::class => InvokableFactory::class,
			Controller\MobileController::class => InvokableFactory::class,
			Controller\NewsController::class => InvokableFactory::class,
			Controller\NgiController::class => InvokableFactory::class,
			Controller\OaiController::class => InvokableFactory::class,
			Controller\PeopleController::class => InvokableFactory::class,
			Controller\PplstatsController::class => InvokableFactory::class,
			Controller\RepositoryController::class => InvokableFactory::class,
			Controller\ResController::class => InvokableFactory::class,
			Controller\SamlController::class => InvokableFactory::class,
			Controller\SitesController::class => InvokableFactory::class,
			Controller\StorageController::class => InvokableFactory::class,
			Controller\SupportedController::class => InvokableFactory::class,
			Controller\TexttoimageController::class => InvokableFactory::class,
			Controller\UnsupportedController::class => InvokableFactory::class,
			Controller\UsersController::class => InvokableFactory::class,
			Controller\VoController::class => InvokableFactory::class,
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
//            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
			'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
