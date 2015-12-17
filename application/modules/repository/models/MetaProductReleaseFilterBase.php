<?php
class Repository_Model_MetaProductReleaseFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->_fields[] = 'id';
		$this->_fields[] = 'currentStateId';
		$this->_fields[] = 'displayVersion';
		$this->_fields[] = 'parent_id';
		$this->_fields[] = 'appDBId';
		$this->_fields[] = 'repoAreaId';
		$this->_fields[] = 'description';
		$this->_fields[] = 'technologyProvider';
		$this->_fields[] = 'technologyProviderShortName';
		$this->_fields[] = 'ISODate';
		$this->_fields[] = 'incremental';
		$this->_fields[] = 'majorVersion';
		$this->_fields[] = 'minorVersion';
		$this->_fields[] = 'updateVersion';
		$this->_fields[] = 'revisionVersion';
		$this->_fields[] = 'releaseNotes';
		$this->_fields[] = 'changeLog';
		$this->_fields[] = 'installationNotes';
		$this->_fields[] = 'repositoryURL';
		$this->_fields[] = 'releaseXML';
		$this->_fields[] = 'qualityCriteriaVerificationReport';
		$this->_fields[] = 'stageRolloutReport';
		$this->_fields[] = 'additionalDetails';
		$this->_fields[] = 'deleted';
		$this->_fields[] = 'extraFld1';
		$this->_fields[] = 'extraFld2';
		$this->_fields[] = 'extraFld3';
		$this->_fields[] = 'extraFld4';
		$this->_fields[] = 'extraFld5';
		$this->_fields[] = 'timestampInserted';
		$this->_fields[] = 'timestampLastUpdated';
		$this->_fields[] = 'timestampLastStateChange';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['currentStateId'] = 'integer';
		$this->_fieldTypes['displayVersion'] = 'string';
		$this->_fieldTypes['parent_id'] = 'integer';
		$this->_fieldTypes['appDBId'] = 'integer';
		$this->_fieldTypes['repoAreaId'] = 'integer';
		$this->_fieldTypes['description'] = 'longtext';
		$this->_fieldTypes['technologyProvider'] = 'string';
		$this->_fieldTypes['technologyProviderShortName'] = 'string';
		$this->_fieldTypes['contact'] = 'string';
		$this->_fieldTypes['technicalContact'] = 'string';
		$this->_fieldTypes['ISODate'] = 'varchar';
		$this->_fieldTypes['incremental'] = 'boolean';
		$this->_fieldTypes['majorVersion'] = 'integer';
		$this->_fieldTypes['minorVersion'] = 'integer';
		$this->_fieldTypes['updateVersion'] = 'integer';
		$this->_fieldTypes['revisionVersion'] = 'integer';
		$this->_fieldTypes['releaseNotes'] = 'longtext';
		$this->_fieldTypes['changeLog'] = 'longtext';
		$this->_fieldTypes['installationNotes'] = 'longtext';
		$this->_fieldTypes['repositoryURL'] = 'string';
		$this->_fieldTypes['releaseXML'] = 'longtext';
		$this->_fieldTypes['qualityCriteriaVerificationReport'] = 'string';
		$this->_fieldTypes['stageRolloutReport'] = 'string';
		$this->_fieldTypes['additionalDetails'] = 'string';
		$this->_fieldTypes['deleted'] = 'enum';
		$this->_fieldTypes['extraFld1'] = 'string';
		$this->_fieldTypes['extraFld2'] = 'string';
		$this->_fieldTypes['extraFld3'] = 'string';
		$this->_fieldTypes['extraFld4'] = 'string';
		$this->_fieldTypes['extraFld5'] = 'string';
		$this->_fieldTypes['timestampInserted'] = 'string';
		$this->_fieldTypes['timestampLastUpdated'] = 'string';
		$this->_fieldTypes['timestampLastStateChange'] = 'string';
		$this->_table = 'meta_product_release';
	}
}
