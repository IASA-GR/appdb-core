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
?>
