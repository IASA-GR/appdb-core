<?php
class Repository_Model_DeletedMetaPoaReleaseFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(1);
		$this->_fields[] = 'deletedBy';
		$this->_fields[] = 'timestampDeleted';
		$this->_fields[] = 'id';
		$this->_fields[] = 'productReleaseId';
		$this->_fields[] = 'displayVersion';
		$this->_fields[] = 'releaseNotes';
		$this->_fields[] = 'changeLog';
		$this->_fields[] = 'poaUrl';
		$this->_fields[] = 'poaCandidateUrl';
		$this->_fields[] = 'poaPath';
		$this->_fields[] = 'targetPlatformCombId';
		$this->_fields[] = 'dMethodCombId';
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
		$this->_fields[] = 'insertedBy';
		$this->_fieldTypes['deletedBy'] = 'integer';
		$this->_fieldTypes['timestampDeleted'] = 'string';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['productReleaseId'] = 'integer';
		$this->_fieldTypes['displayVersion'] = 'string';
		$this->_fieldTypes['releaseNotes'] = 'longtext';
		$this->_fieldTypes['changeLog'] = 'longtext';
		$this->_fieldTypes['poaUrl'] = 'string';
		$this->_fieldTypes['poaCandidateUrl'] = 'string';
		$this->_fieldTypes['poaPath'] = 'string';
		$this->_fieldTypes['targetPlatformCombId'] = 'integer';
		$this->_fieldTypes['dMethodCombId'] = 'integer';
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
		$this->_fieldTypes['insertedBy'] = 'integer';
		$this->_table = 'deleted_meta_poa_release';
	}
}
