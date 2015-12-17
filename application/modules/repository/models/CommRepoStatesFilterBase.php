<?php
class Repository_Model_CommRepoStatesFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(1);
		$this->_fields[] = 'id';
		$this->_fields[] = 'name';
		$this->_fields[] = 'repository_id';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['name'] = 'varchar';
		$this->_fieldTypes['repository_id'] = 'integer';
		$this->_table = 'comm_repo_states';
	}
}
