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
class Default_Model_ApplicationsFilter extends Default_Model_ApplicationsFilterBase {
	public function __construct() {
		parent::__construct();
        $interval = Zend_Registry::get("app");
        $interval = $interval["invalid"];
		$this->_fields[] = 'rank';
		$this->_fields[] = 'EXTRACT(YEAR FROM dateadded)';
		$this->_fields[] = 'EXTRACT(MONTH FROM dateadded)';
		$this->_fields[] = 'EXTRACT(DAY FROM dateadded)';
        $this->_fields[] = "lastupdated BETWEEN NOW\(\) - INTERVAL '[0-9]+ (year|month)s{0,1}' AND NOW\(\)";
        $this->_fields[] = "lastupdated BETWEEN NOW() - INTERVAL '".$interval."' AND NOW()";
        $this->_fields[] = "SUBSTRING(applications.name, 1, 1)";
		$this->_fields[] = "relatedto";
		$this->_fields[] = "(SELECT vappliance_site_count(###THETABLE###.id))";
        $this->_fields[] = "any.any";
		$this->_fieldTypes['rank'] = 'integer';
		$this->_fieldTypes['EXTRACT(YEAR FROM dateadded)'] = 'integer';
		$this->_fieldTypes['EXTRACT(MONTH FROM dateadded)'] = 'integer';
		$this->_fieldTypes['EXTRACT(DAY FROM dateadded)'] = 'integer';
        $this->_fieldTypes['keywords'] = 'string[]';
        $this->_fieldTypes["lastupdated BETWEEN NOW() - INTERVAL '".$interval."' AND NOW()"] = 'boolean';
		$this->_fieldTypes["SUBSTRING(applications.name, 1, 1)"] = 'string';
		$this->_fieldTypes["(SELECT vappliance_site_count(###THETABLE###.id))"] = 'integer';
        $this->_fieldTypes["any.any"] = 'string';
        $this->_fieldTypes["relatedto"] = 'string';
        $this->_fieldTypes["lastupdated BETWEEN NOW\(\) - INTERVAL '[0-9]+ (year|month)s{0,1}' AND NOW\(\)"] = 'string';
	}
}
