<?php
class Repository_Model_CommRepoAllowedPlatformCombinationsFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(1);
		$this->_fields[] = 'id';
		$this->_fields[] = 'osId';
		$this->_fields[] = 'archId';
		$this->_fields[] = 'canSupport';
		$this->_fields[] = 'fsPattern';
		$this->_fields[] = 'incRelSupport';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['osId'] = 'integer';
		$this->_fieldTypes['archId'] = 'integer';
		$this->_fieldTypes['canSupport'] = 'string';
		$this->_fieldTypes['fsPattern'] = 'string';
		$this->_fieldTypes['incRelSupport'] = 'string';
		$this->_table = 'comm_repo_allowed_platform_combinations';
	}
}
