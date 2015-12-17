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
?>
<?php
// PUT YOUR CUSTOM CODE HERE
class Default_Model_AppUrls extends Default_Model_AppUrlsBase
{
	public static function getTitles($notEmpty=false) {
		global $application;
		$db = $application->getBootstrap()->getResource('db');
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$q = "SELECT DISTINCT title FROM app_urls" . (($notEmpty===true)?" WHERE title IS NOT NULL AND title<>''":"");
		$res = $db->query($q);
		$titles = array();
		if ( $res !== null ) {
			foreach($res as $r) {
				$titles[] = $r->title;
			}
		}
		return $titles;
	}
}
