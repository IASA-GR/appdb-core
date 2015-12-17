<?php
class Repository_Model_CommRepoAllowedOsDmethodCombinationsFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(1);
		$this->_fields[] = 'id';
		$this->_fields[] = 'osId';
		$this->_fields[] = 'dMethodId';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['osId'] = 'integer';
		$this->_fieldTypes['dMethodId'] = 'integer';
		$this->_table = 'comm_repo_allowed_os_dmethod_combinations';
	}
}
