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
namespace Application\Model;

class AggregateNewsMapper extends AggregateNewsMapperBase
{
	private function joins(&$select, $filter) {
		if ( is_array($filter->joins) ) {
			$select->from("aggregate_news");
			if (in_array("applications", $filter->joins) || in_array("categories", $filter->joins)) {
				$select->join('applications', 'applications.id = aggregate_news.subjectid AND applications.deleted IS FALSE AND applications.moderated IS FALSE', array(), 'left');
				if (in_array("vos", $filter->joins)) {
					$select->join('app_vos', 'app_vos.appid = applications.id', array(), 'left');
					$select->join('vos', 'app_vos.void = vos.id AND vos.deleted IS FALSE', array(), 'left');
				}
				if (in_array("countries", $filter->joins) || in_array("appcountries", $filter->joins)) {
					$select->join('appcountries', 'appcountries.appid = applications.id', array(), 'left');
					$select->join('countries','countries.id = appcountries.id', array(), 'left');
				}
				if (in_array("middlewares", $filter->joins) || in_array("app_middlewares", $filter->joins)) {
					$select->join('app_middlewares','applications.id = app_middlewares.appid', array(), 'left');
					$select->join('middlewares','middlewares.id = app_middlewares.middlewareid', array(), 'left');
				}
				if (in_array("researchers", $filter->joins) || in_array("positiontypes", $filter->joins) || in_array("contacts", $filter->joins)) {
					$select->join('researchers_apps','researchers_apps.appid = applications.id', array(), 'left');
					$select->join('researchers','researchers.id = researchers_apps.researcherid AND researchers.deleted IS FALSE', array(), 'left');
				}
				if (in_array("positiontypes", $filter->joins)) $select->join('positiontypes','positiontypes.id = researchers.positiontypeid', array(), 'left');
				if (in_array("contacts", $filter->joins)) $select->join('contacts','contacts.researcherid = researchers.id', array(), 'left');
				if (in_array("categories", $filter->joins)) $select->join('categories','categories.id = ANY(applications.categoryid)', array(), 'left');
				if (in_array("disciplines", $filter->joins)) $select->join("disciplines", "disciplines.id = ANY(applications.disciplineid)", array(), 'left');
				if (in_array("statuses", $filter->joins)) $select->join('statuses','statuses.id = applications.statusid', array(), 'left');
				if (in_array("licenses", $filter->joins)) {
					$select->join('app_licenses','app_licenses.appid = applications.id', array(), 'left');
					$select->join('licenses','licenses.id = app_licenses.licenseid', array(), 'left');
				}
				if (in_array("proglangs", $filter->joins)) {
					$select->join("appproglangs","appproglangs.appid = applications.id", array(), 'left');
					$select->join("proglangs","proglangs.id = appproglangs.proglangid", array(), 'left');
				}

				if ( in_array("vapp_versions", $filter->joins) || in_array("vmiflavours", $filter->joins) || in_array("oses", $filter->joins) || in_array("os_families", $filter->joins) || in_array("archs", $filter->joins) || in_array("hypervisors", $filter->joins) ) {
					$select->join('vapplications', 'vapplications.appid = applications.id', array(), 'left');
					$select->join('vapp_versions', 'vapp_versions.vappid = vapplications.id AND vapp_versions.published AND vapp_versions.enabled AND NOT vapp_versions.archived', array(), 'left');
				}
				if ( in_array("vmiflavours", $filter->joins) || in_array("oses", $filter->joins) || in_array("os_families", $filter->joins) || in_array("archs", $filter->joins) || in_array("hypervisors", $filter->joins) ) {
					$select->join('vmis', 'vmis.vappid = vapplications.id', array(), 'left');
					$select->join('vmiflavours', 'vmiflavours.vmiid= vmis.id', array(), 'left');
				}
				if ( in_array("oses", $filter->joins) || in_array("os_families", $filter->joins) ) {
					$select->join('oses', 'vmiflavours.osid = oses.id', array(), 'left');
					if ( in_array("os_families", $filter->joins) ) {
						$select->join('os_families', 'os_families.id = oses.os_family_id', array(), 'left');
					}
				}
				if ( in_array("hypervisors", $filter->joins) ) {
					$select->join('hypervisors', 'hypervisors.name::TEXT = ANY(vmiflavours.hypervisors::TEXT[])', array(), 'left');
				}
				if ( in_array("archs", $filter->joins) ) {
					$select->join('archs', 'vmiflavours.archid = archs.id', array(), 'left');
				}
				if ( in_array("app_release_count", $filter->joins) ) {
					$select->join('app_release_count', 'app_release_count.appid = applications.id', array(), 'left');
				}

				$select->where("aggregate_news.subjecttype = 'app'");
			} elseif (in_array("researchers", $filter->joins)) {
				$select->join('researchers','researchers.id = aggregate_news.subjectid AND researchers.deleted IS FALSE', array(), 'left');
				$select->join('countries','countries.id = researchers.countryid', array(), 'left');
				$select->join('positiontypes','positiontypes.id = researchers.positiontypeid', array(), 'left');
				$select->join('contacts','contacts.researcherid = researchers.id', array(), 'left');
				$select->join('researchers_apps', 'researchers_apps.researcherid = researchers.id', array(), 'left');
				$select->join('applications', 'applications.id = researchers_apps.appid AND applications.deleted IS FALSE AND applications.moderated IS FALSE', array(), 'left');
				$select->join('app_vos', 'app_vos.appid = applications.id', array(), 'left');
				$select->join('vos', 'vos.id = app_vos.void AND vos.deleted IS FALSE', array(), 'left');
				$select->join('disciplines', 'disciplines.id = ANY(applications.disciplineid)', array(), 'left');
			}
		}
	}

	public function fetchAll($filter = null, $format = '') {
		$doParent = true;
		if (! is_null($filter)) {
			if ($filter->expr() != '') {
				$doParent = false;
				$select = $this->getDbTable()->getSql()->select();
				$select->quantifier('DISTINCT');
				$this->joins($select, $filter);
				$select->where($filter->expr());
				if (! is_null($filter->$limit)) $select->limit($filter->limit);
				if (! is_null($filter->$offset)) $select->offset($filter->offset);
				if (! is_null($filter->$orderBy)) $select->order($filter->orderBy);
				$resultSet = db()->query(SQL2STR($this, $select), array())->toArray();
				$entries = array();
				foreach ($resultSet as $row) {
					$entry = new AggregateNewsEntry();
					$this->populate($entry,$row);
					$entries[] = $entry;
				}
				return $entries;
			}
		} 
		if ( $doParent ) return parent::fetchAll($filter);
	}

}
