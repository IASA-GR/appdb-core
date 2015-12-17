<?php
class Repository_Model_CommRepoRepositoriesFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(1);
		$this->_fields[] = 'id';
		$this->_fields[] = 'name';
		$this->_fields[] = 'base_path';
		$this->_fields[] = 'base_url';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['name'] = 'varchar';
		$this->_fieldTypes['base_path'] = 'varchar';
		$this->_fieldTypes['base_url'] = 'varchar';
		$this->_table = 'comm_repo_repositories';
	}
}
