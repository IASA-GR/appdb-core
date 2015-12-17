<?php
class Repository_Model_CommRepoDmethodsFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(1);
		$this->_fields[] = 'id';
		$this->_fields[] = 'name';
		$this->_fields[] = 'label';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['name'] = 'tinytext';
		$this->_fieldTypes['label'] = 'string';
		$this->_table = 'comm_repo_dmethods';
	}
}
