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
class Default_Model_AggregateNewsMapper extends Default_Model_AggregateNewsMapperBase
{
	private function joins(&$select, $filter) {
		if ( is_array($filter->joins) ) {
			$select->from("aggregate_news");
			if (in_array("applications", $filter->joins) || in_array("categories", $filter->joins)) {
				$select->joinLeft('applications', 'applications.id = aggregate_news.subjectid AND applications.deleted IS FALSE AND applications.moderated IS FALSE', array());
				if (in_array("vos", $filter->joins)) {
					$select->joinLeft('app_vos', 'app_vos.appid = applications.id', array());
					$select->joinLeft('vos', 'app_vos.void = vos.id AND vos.deleted IS FALSE', array());
				}
				if (in_array("countries", $filter->joins) || in_array("appcountries", $filter->joins)) {
					$select->joinLeft('appcountries', 'appcountries.appid = applications.id', array());
					$select->joinLeft('countries','countries.id = appcountries.id', array());
				}
				if (in_array("middlewares", $filter->joins) || in_array("app_middlewares", $filter->joins)) {
					$select->joinLeft('app_middlewares','applications.id = app_middlewares.appid', array());
					$select->joinLeft('middlewares','middlewares.id = app_middlewares.middlewareid', array());
				}
				if (in_array("researchers", $filter->joins) || in_array("positiontypes", $filter->joins) || in_array("contacts", $filter->joins)) {
					$select->joinLeft('researchers_apps','researchers_apps.appid = applications.id', array());
					$select->joinLeft('researchers','researchers.id = researchers_apps.researcherid AND researchers.deleted IS FALSE', array());
				}
				if (in_array("positiontypes", $filter->joins)) $select->joinLeft('positiontypes','positiontypes.id = researchers.positiontypeid', array());
				if (in_array("contacts", $filter->joins)) $select->joinLeft('contacts','contacts.researcherid = researchers.id', array());
				if (in_array("categories", $filter->joins)) $select->joinLeft('categories','categories.id = ANY(applications.categoryid)', array());
				if (in_array("disciplines", $filter->joins)) $select->joinLeft("disciplines", "disciplines.id = ANY(applications.disciplineid)", array());
				if (in_array("statuses", $filter->joins)) $select->joinLeft('statuses','statuses.id = applications.statusid', array());
				if (in_array("licenses", $filter->joins)) {
					$select->joinLeft('app_licenses','app_licenses.appid = applications.id', array());
					$select->joinLeft('licenses','licenses.id = app_licenses.licenseid', array());
				}
				if (in_array("proglangs", $filter->joins)) {
					$select->joinLeft("appproglangs","appproglangs.appid = applications.id", array());
					$select->joinLeft("proglangs","proglangs.id = appproglangs.proglangid", array());
				}

				if ( in_array("vapp_versions", $filter->joins) || in_array("vmiflavours", $filter->joins) || in_array("oses", $filter->joins) || in_array("os_families", $filter->joins) || in_array("archs", $filter->joins) || in_array("hypervisors", $filter->joins) ) {
					$select->joinLeft('vapplications', 'vapplications.appid = applications.id', array());
					$select->joinLeft('vapp_versions', 'vapp_versions.vappid = vapplications.id AND vapp_versions.published AND vapp_versions.enabled AND NOT vapp_versions.archived', array());
				}
				if ( in_array("vmiflavours", $filter->joins) || in_array("oses", $filter->joins) || in_array("os_families", $filter->joins) || in_array("archs", $filter->joins) || in_array("hypervisors", $filter->joins) ) {
					$select->joinLeft('vmis', 'vmis.vappid = vapplications.id', array());
					$select->joinLeft('vmiflavours', 'vmiflavours.vmiid= vmis.id', array());
				}
				if ( in_array("oses", $filter->joins) || in_array("os_families", $filter->joins) ) {
					$select->joinLeft('oses', 'vmiflavours.osid = oses.id', array());
					if ( in_array("os_families", $filter->joins) ) {
						$select->joinLeft('os_families', 'os_families.id = oses.os_family_id', array());
					}
				}
				if ( in_array("hypervisors", $filter->joins) ) {
					$select->joinLeft('hypervisors', 'hypervisors.name::TEXT = ANY(vmiflavours.hypervisors::TEXT[])', array());
				}
				if ( in_array("archs", $filter->joins) ) {
					$select->joinLeft('archs', 'vmiflavours.archid = archs.id', array());
				}
				if ( in_array("app_release_count", $filter->joins) ) {
					$select->joinLeft('app_release_count', 'app_release_count.appid = applications.id', array());
				}

				$select->where("aggregate_news.subjecttype = 'app'");
			} elseif (in_array("researchers", $filter->joins)) {
				$select->joinLeft('researchers','researchers.id = aggregate_news.subjectid AND researchers.deleted IS FALSE', array());
				$select->joinLeft('countries','countries.id = researchers.countryid', array());
				$select->joinLeft('positiontypes','positiontypes.id = researchers.positiontypeid', array());
				$select->joinLeft('contacts','contacts.researcherid = researchers.id', array());
				$select->joinLeft('researchers_apps', 'researchers_apps.researcherid = researchers.id', array());
				$select->joinLeft('applications', 'applications.id = researchers_apps.appid AND applications.deleted IS FALSE AND applications.moderated IS FALSE', array());
				$select->joinLeft('app_vos', 'app_vos.appid = applications.id', array());
				$select->joinLeft('vos', 'vos.id = app_vos.void AND vos.deleted IS FALSE', array());
				$select->joinLeft('disciplines', 'disciplines.id = ANY(applications.disciplineid)', array());
			}
		}
	}

	public function fetchAll($filter = null, $format = '') {
		$doParent = true;
		if ( $filter !== null ) {
			if ($filter->expr() != '') {
				$doParent = false;
				$select = $this->getDbTable()->select()->distinct();
				$this->joins($select, $filter);
				$select->where($filter->expr());
				$select->limit($filter->limit, $filter->offset);
				$select->order($filter->orderBy);
				error_log("".$select);
				$resultSet = $this->getDbTable()->fetchAll($select);
				$entries = array();
				foreach ($resultSet as $row) {
					$entry = new Default_Model_AggregateNewsEntry();
					$this->populate($entry,$row);
					$entries[] = $entry;
				}
				return $entries;
			}
		} 
		if ( $doParent ) return parent::fetchAll($filter);
	}

}
