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

require_once('email_configuration.php');
require_once('email_service.php');

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
?>
