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
class Default_Model_SitesFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'id';
		$this->_fields[] = 'name';
		$this->_fields[] = 'shortname';
		$this->_fields[] = 'officialname';
		$this->_fields[] = 'description';
		$this->_fields[] = 'portalurl';
		$this->_fields[] = 'homeurl';
		$this->_fields[] = 'contactemail';
		$this->_fields[] = 'contacttel';
		$this->_fields[] = 'alarmemail';
		$this->_fields[] = 'csirtemail';
		$this->_fields[] = 'giisurl';
		$this->_fields[] = 'countryid';
		$this->_fields[] = 'countrycode';
		$this->_fields[] = 'countryname';
		$this->_fields[] = 'regionid';
		$this->_fields[] = 'regionname';
		$this->_fields[] = 'tier';
		$this->_fields[] = 'subgrid';
		$this->_fields[] = 'roc';
		$this->_fields[] = 'productioninfrastructure';
		$this->_fields[] = 'certificationstatus';
		$this->_fields[] = 'timezone';
		$this->_fields[] = 'latitude';
		$this->_fields[] = 'longitude';
		$this->_fields[] = 'domainname';
		$this->_fields[] = 'ip';
		$this->_fields[] = 'guid';
		$this->_fields[] = 'datasource';
		$this->_fields[] = 'createdon';
		$this->_fields[] = 'createdby';
		$this->_fields[] = 'updatedon';
		$this->_fields[] = 'updatedby';
		$this->_fields[] = 'deleted';
		$this->_fields[] = 'deletedon';
		$this->_fields[] = 'deletedby';
		$this->_fieldTypes['id'] = 'string';
		$this->_fieldTypes['name'] = 'string';
		$this->_fieldTypes['shortname'] = 'string';
		$this->_fieldTypes['officialname'] = 'string';
		$this->_fieldTypes['description'] = 'string';
		$this->_fieldTypes['portalurl'] = 'string';
		$this->_fieldTypes['homeurl'] = 'string';
		$this->_fieldTypes['contactemail'] = 'string';
		$this->_fieldTypes['contacttel'] = 'string';
		$this->_fieldTypes['alarmemail'] = 'string';
		$this->_fieldTypes['csirtemail'] = 'string';
		$this->_fieldTypes['giisurl'] = 'string';
		$this->_fieldTypes['countryid'] = 'integer';
		$this->_fieldTypes['countrycode'] = 'string';
		$this->_fieldTypes['countryname'] = 'string';
		$this->_fieldTypes['regionid'] = 'integer';
		$this->_fieldTypes['regionname'] = 'string';
		$this->_fieldTypes['tier'] = 'string';
		$this->_fieldTypes['subgrid'] = 'string';
		$this->_fieldTypes['roc'] = 'string';
		$this->_fieldTypes['productioninfrastructure'] = 'string';
		$this->_fieldTypes['certificationstatus'] = 'string';
		$this->_fieldTypes['timezone'] = 'string';
		$this->_fieldTypes['latitude'] = 'string';
		$this->_fieldTypes['longitude'] = 'string';
		$this->_fieldTypes['domainname'] = 'string';
		$this->_fieldTypes['ip'] = 'string';
		$this->_fieldTypes['guid'] = 'uuid';
		$this->_fieldTypes['datasource'] = 'string';
		$this->_fieldTypes['createdon'] = 'string';
		$this->_fieldTypes['createdby'] = 'string';
		$this->_fieldTypes['updatedon'] = 'string';
		$this->_fieldTypes['updatedby'] = 'string';
		$this->_fieldTypes['deleted'] = 'boolean';
		$this->_fieldTypes['deletedon'] = 'string';
		$this->_fieldTypes['deletedby'] = 'string';
		$this->_table = 'sites';
	}
}
