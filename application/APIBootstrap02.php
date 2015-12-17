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

function LoadRouters02($_this){
	$front = $_this->getResource('FrontController');
	$apiRoutes = array();
	$apiRoutes["schemaList"] = new Zend_Controller_Router_Route('/rest/0.2/schema/', array(
		"controller"=> "api02",
		"action" => "schema",
		"format"=>"xml"
		));
	 $apiRoutes["schemaEntry"] = new Zend_Controller_Router_Route('/rest/0.2/schema/:xsdname', array(
		"controller"=> "api02",
		"action" => "schema",
		"format"=>"xml"
		));
	$apiRoutes["JSONappsList"] = new Zend_Controller_Router_Route('/json/0.2/applications/', array(
		"controller"=> "api02",
		"action" => "rest",
		"routeController" => "api02action",
		"routeAction" => "appindex",
		"routeXslt" => "applications",
		"routeDataType" =>"application",
		"routeRecursive" =>"0",
		"routeForcePaging" => "1",
		"dc"=>"1",
		"format"=>"json"
		));
	$apiRoutes["appsList"] = new Zend_Controller_Router_Route('/rest/0.2/applications/', array(
		"controller"=> "api02",
		"action" => "rest",
		"routeController" => "api02action",
		"routeAction" => "appindex",
		"routeXslt" => "applications",
		"routeDataType" =>"application",
		"routeRecursive" =>"0",
		"routeForcePaging" => "1",
		"dc"=>"1",
		"format"=>"xml"
		));
	$apiRoutes["delappsList"] = new Zend_Controller_Router_Route('/rest/0.2/applications/deleted', array(
		"controller"=> "api02",
		"action" => "rest",
		"routeController" => "api02action",
		"routeAction" => "delindex",
		"routeXslt" => "applications",
		"routeDataType" =>"application",
		"routeRecursive" =>"0",
		"routeForcePaging" => "1",
		"dc"=>"1",
		"format"=>"xml"
		));
	$apiRoutes["modappsList"] = new Zend_Controller_Router_Route('/rest/0.2/applications/moderated', array(
		"controller"=> "api02",
		"action" => "rest",
		"routeController" => "api02action",
		"routeAction" => "modindex",
		"routeXslt" => "applications",
		"routeDataType" =>"application",
		"routeRecursive" =>"0",
		"routeForcePaging" => "1",
		"dc"=>"1",
		"format"=>"xml"
		));
	 $apiRoutes["JSONappsDetails"] = new Zend_Controller_Router_Route('/json/0.2/applications/:id', array(
		"controller"=> "api02",
		"action" => "rest",
		"routeController" => "api02action",
		"routeAction" => "appdetails",
		"routeXslt" =>"applications",
		"routeDataType" =>"application",
		"dc"=>"1",
		"format"=>"json"
		), array("id"=>"\d+"));
	 $apiRoutes["appsDetails"] = new Zend_Controller_Router_Route('/rest/0.2/applications/:id', array(
		"controller"=> "api02",
		"action" => "rest",
		"routeController" => "api02action",
		"routeAction" => "appdetails",
		"routeXslt" =>"applications",
		"routeDataType" =>"application",
		"routeUpdateLog" => "apps",
		"dc"=>"1",
		"format"=>"xml"
		), array("id"=>"\d+"));
	 $apiRoutes["JSONrelatedApps"] = new Zend_Controller_Router_Route('/json/0.2/applications/:id/relatedapps', array(
		"controller"=> "api02",
		"action" => "rest",
		"routeController" => "api02action",
		"routeAction" => "relatedapps",
		"routeXslt" =>"applications",
		"routeDataType" =>"application",
		"dc"=>"1",
		"format"=>"json"
		), array("id"=>"\d+"));
	 $apiRoutes["relatedApps"] = new Zend_Controller_Router_Route('/rest/0.2/applications/:id/relatedapps', array(
		"controller"=> "api02",
		"action" => "rest",
		"routeController" => "api02action",
		"routeAction" => "relatedapps",
		"routeXslt" =>"applications",
		"routeDataType" =>"application",
		"dc"=>"1",
		"format"=>"xml"
		), array("id"=>"\d+"));
	 $apiRoutes["rateReport"] = new Zend_Controller_Router_Route('/rest/0.2/applications/:id/ratingsreport/:type', array(
		"controller"=> "api02",
		"action" => "rest",
		"routeController" => "api02action",
		"routeAction" => "ratingsreport",
		"dc"=>"1",
		"format"=>"xml",
		"type"=> "both"
		), array("id"=>"\d+"));
	 $apiRoutes["ratings"] = new Zend_Controller_Router_Route('/rest/0.2/applications/:id/ratings', array(
		"controller"=> "api02",
		"action" => "rest",
		"routeController" => "api02action",
		"routeAction" => "ratings",
		"routeXslt" =>"applications",
		"routeDataType" =>"application",
		"dc"=>"1",
		"format"=>"xml"
		), array("id"=>"\d+"));
	 $apiRoutes["bookmarks"] = new Zend_Controller_Router_Route('/rest/0.2/people/:id/applications/bookmarked', array(
		"controller"=> "api02",
		"action" => "rest",
		"routeController" => "api02action",
		"routeAction" => "bmindex",
		"routeXslt" =>"applications",
		"routeDataType" =>"application",
		"dc"=>"1",
		"format"=>"xml"
		), array("id"=>"\d+"));
	 $apiRoutes["editableApps"] = new Zend_Controller_Router_Route('/rest/0.2/people/:id/applications/editable', array(
		"controller"=> "api02",
		"action" => "rest",
		"routeController" => "api02action",
		"routeAction" => "myeditindex",
		"routeXslt" =>"applications",
		"routeDataType" =>"application",
		"dc"=>"1",
		"format"=>"xml"
		), array("id"=>"\d+"));
	 $apiRoutes["ownedApps"] = new Zend_Controller_Router_Route('/rest/0.2/people/:id/applications/owned', array(
		"controller"=> "api02",
		"action" => "rest",
		"routeController" => "api02action",
		"routeAction" => "myownindex",
		"routeXslt" =>"applications",
		"routeDataType" =>"application",
		"dc"=>"1",
		"format"=>"xml"
		), array("id"=>"\d+"));
	 $apiRoutes["associatedApps"] = new Zend_Controller_Router_Route('/rest/0.2/people/:id/applications/associated', array(
		"controller"=> "api02",
		"action" => "rest",
		"routeController" => "api02action",
		"routeAction" => "myappsindex",
		"routeXslt" =>"applications",
		"routeDataType" =>"application",
		"dc"=>"1",
		"format"=>"xml"
		), array("id"=>"\d+"));
	$apiRoutes["peopleList"] = new Zend_Controller_Router_Route('/rest/0.2/people/', array(
		"controller"=> "api02",
		"action" => "rest",
		"routeController" => "api02action",
		"routeAction" => "pplindex",
		"routeXslt" => "persons",
		"routeDataType" =>"person",
		"routeRecursive"=> "0",
		"routeForcePaging" => "1",
		"dc"=>"1",
		"format"=>"xml"
		));
	$apiRoutes["peopleDetails"] = new Zend_Controller_Router_Route('/rest/0.2/people/:id', array(
		"controller"=> "api02",
		"action" => "rest",
		"routeController" => "api02action",
		"routeAction" => "ppldetails",
		"dc"=>"1",
		"format"=>"xml",
		"routeXslt" => "persons",
		"routeDataType" =>"person"
		), array("id"=>"\d+"));

	$apiRoutes["countries"] = new Zend_Controller_Router_Route("/rest/0.2/regional", array(
		"controller" => "api02", "version" => "0.2",
		"action" => "rest",
		"routeModel"=> "Countries;Regions;NGIs",
		"routeXslt" => "regional",
		"routeDataType" =>"regional",
		"format"=>"xml"
	));
	$apiRoutes["disciplines"] = new Zend_Controller_Router_Route("/rest/0.2/disciplines",array(
		"controller" => "api02", "version" => "0.2",
		"action" => "rest",
		"routeModel" => "Domains;Subdomains",
		"routeXslt"=>"applications",
		"routeDataType" =>"discipline",
		"format" => "xml"
	));
	$apiRoutes["statuses"] = new Zend_Controller_Router_Route("/rest/0.2/statuses", array(
		"controller" => "api02", "version" => "0.2",
		"action" => "rest",
		"routeModel" =>"Statuses",
		"routeXslt" => "applications",
		"routeDataType" =>"status",
		"format" => "xml"
	));
	$apiRoutes["middleware"] = new Zend_Controller_Router_ROute("/rest/0.2/middlewares",array(
		"controller" => "api02", "version" => "0.2",
		"action" => "rest",
		"routeModel" => "Middlewares",
		"routeXslt" => "applications",
		"routeDataType" =>"middleware",
		"format" => "xml"
	));
	$apiRoutes["vos"] = new Zend_Controller_Router_Route("/rest/0.2/vos", array(
		"controller" => "api02", "version" => "0.2",
		"action" => "rest",
		"routeModel" => "VOs",
		"routeXslt" => "vos",
		"routeDataType" =>"vo",
		"routeModelQuery" =>"name;domainid",
		"format" =>"xml"
	));
	$apiRoutes["roles"] = new Zend_Controller_Router_Route("/rest/0.2/roles", array(
		"controller" => "api02", "version" => "0.2",
		"action" => "rest",
		"routeModel" => "PositionTypes",
		"routeXslt" => "persons",
		"routeDataType" =>"role",
		"format" =>"xml"
	));
	$apiRoutes["tags"] = new Zend_Controller_Router_Route('/rest/0.2/tags/', array(
		"controller" => "api02", "version" => "0.2",
		"action" => "rest",
		"routeModel" => "AppTags",
		"routeDataType" =>"tags",
		"format" =>"xml"
		));
	$apiRoutes["contacttypes"] = new Zend_Controller_Router_Route("/rest/0.2/contacttypes", array(
		"controller" => "api02", "version" => "0.2",
		"action" => "rest",
		"routeModel" => "ContactTypes",
		"routeXslt" => "persons",
		"routeDataType" =>"contacttype",
		"format" =>"xml"
	));
	$apiRoutes["appfiltercheck"] = new Zend_Controller_Router_Route("/rest/0.2/applications/filter/normalize", array(
		"controller"=> "api02",
		"action" => "rest",
		"routeController" => "api02action",
		"routeAction" => "validateappfilter",
		"routeXslt" =>"applications",
		"routeDataType" =>"filter",
		"dc"=>"1",
		"format"=>"xml"
		));
	$apiRoutes["vosfiltercheck"] = new Zend_Controller_Router_Route("/rest/0.2/vos/filter/normalize", array(
		"controller"=> "api02",
		"action" => "rest",
		"routeController" => "api02action",
		"routeAction" => "validatevosfilter",
		"routeXslt" =>"vos",
		"routeDataType" =>"filter",
		"dc"=>"1",
		"format"=>"xml"
		));
	$apiRoutes["appfilterreflection"] = new Zend_Controller_Router_Route("/rest/0.2/applications/filter/reflect", array(
		"controller"=> "api02",
		"action" => "rest",
		"routeController" => "api02action",
		"routeAction" => "reflectappfilter",
		"routeXslt" =>"applications",
		"routeDataType" =>"filter",
		"dc"=>"1",
		"format"=>"xml"
		));
	$apiRoutes["pplfiltercheck"] = new Zend_Controller_Router_Route("/rest/0.2/people/filter/normalize", array(
		"controller"=> "api02",
		"action" => "rest",
		"routeController" => "api02action",
		"routeAction" => "validatepplfilter",
		"routeXslt" =>"people",
		"routeDataType" =>"filter",
		"dc"=>"1",
		"format"=>"xml"
		));
	$apiRoutes["pplfilterreflection"] = new Zend_Controller_Router_Route("/rest/0.2/people/filter/reflect", array(
		"controller"=> "api02",
		"action" => "rest",
		"routeController" => "api02action",
		"routeAction" => "reflectpplfilter",
		"routeXslt" =>"people",
		"routeDataType" =>"filter",
		"dc"=>"1",
		"format"=>"xml"
		));
	$apiRoutes["vosfilterreflection"] = new Zend_Controller_Router_Route("/rest/0.2/vos/filter/reflect", array(
		"controller"=> "api02",
		"action" => "rest",
		"routeController" => "api02action",
		"routeAction" => "reflectvosfilter",
		"routeXslt" =>"vos",
		"routeDataType" =>"filter",
		"dc"=>"1",
		"format"=>"xml"
		));
	$apiRoutes["dissemination"] = new Zend_Controller_Router_Route("/rest/0.2/dissemination", array(
		"controller"=> "api02",
		"action" => "rest",
		"routeController" => "api02action",
		"routeAction" => "disseminationlog",
		"routeXslt" => "dissemination",
		"routeDataType" =>"dissemination",
		"dc"=>"1",
		"format"=>"xml"
		));
	$apiRoutes["disseminationentry"] = new Zend_Controller_Router_Route("/rest/0.2/dissemination/:id", array(
		"controller"=> "api02",
		"action" => "rest",
		"routeController" => "api02action",
		"routeAction" => "disseminationlog",
		"routeXslt" => "dissemination",
		"routeDataType" =>"dissemination",
		"dc"=>"1",
		"format"=>"xml"
		), array("id"=>"\d+"));
	$apiRoutes["categories"] = new Zend_Controller_Router_Route("/rest/0.2/categories",array(
		"controller" => "api02", "version" => "0.2",
		"action" => "rest",
		"routeModel" => "Categories",
		"routeXslt" => "applications",
		"routeDataType" =>"category",
		"format" =>"xml"
	));
	foreach($apiRoutes as $rk=>$rv){
	   $front->getRouter()->addRoute($rk."_02",$rv);
	}
}
