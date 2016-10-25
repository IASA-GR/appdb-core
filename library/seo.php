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
?>
