<?php
class Repository_Model_MetaProductRepoAreasFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(1);
		$this->_fields[] = 'id';
		$this->_fields[] = 'name';
		$this->_fields[] = 'swId';
		$this->_fields[] = 'swName';
		$this->_fields[] = 'description';
		$this->_fields[] = 'installationNotes';
		$this->_fields[] = 'additionalDetails';
		$this->_fields[] = 'yumRepofileId';
		$this->_fields[] = 'aptRepofileId';
		$this->_fields[] = 'knownIssues';
		$this->_fields[] = 'timestampInserted';
		$this->_fields[] = 'timestampLastUpdated';
		$this->_fields[] = 'timestampLastProductionBuild';
		$this->_fields[] = 'insertedBy';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['name'] = 'string';
		$this->_fieldTypes['swId'] = 'integer';
		$this->_fieldTypes['swName'] = 'string';
		$this->_fieldTypes['description'] = 'string';
		$this->_fieldTypes['installationNotes'] = 'string';
		$this->_fieldTypes['additionalDetails'] = 'string';
		$this->_fieldTypes['yumRepofileId'] = 'integer';
		$this->_fieldTypes['aptRepofileId'] = 'integer';
		$this->_fieldTypes['knownIssues'] = 'string';
		$this->_fieldTypes['timestampInserted'] = 'string';
		$this->_fieldTypes['timestampLastUpdated'] = 'string';
		$this->_fieldTypes['timestampLastProductionBuild'] = 'string';
		$this->_fieldTypes['insertedBy'] = 'integer';
		$this->_table = 'meta_product_repo_area';
	}
}
