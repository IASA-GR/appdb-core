<?php
class Repository_Model_ConfigFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(1);
		$this->_fields[] = 'id';
		$this->_fields[] = 'name';
		$this->_fields[] = 'value';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['name'] = 'varchar';
		$this->_fieldTypes['value'] = 'string';
		$this->_table = 'config';
	}
}
