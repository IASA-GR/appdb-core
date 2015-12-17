<?php
class Repository_Model_CommRepoOssFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(1);
		$this->_fields[] = 'id';
		$this->_fields[] = 'name';
		$this->_fields[] = 'displayName';
		$this->_fields[] = 'flavor';
		$this->_fields[] = 'displayFlavor';
		$this->_fields[] = 'artifactType';
		$this->_fields[] = 'acronym';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['name'] = 'tinytext';
		$this->_fieldTypes['displayName'] = 'tinytext';
		$this->_fieldTypes['flavor'] = 'tinytext';
		$this->_fieldTypes['displayFlavor'] = 'string';
		$this->_fieldTypes['artifactType'] = 'string';
		$this->_fieldTypes['acronym'] = 'string';
		$this->_table = 'comm_repo_oss';
	}
}
