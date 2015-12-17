<?php
class Repository_Model_VMetaProductRepoAreaContactsFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(1);
		$this->_fields[] = 'pseudoId';
		$this->_fields[] = 'externalId';
		$this->_fields[] = 'contactTypeId';
		$this->_fields[] = 'firstname';
		$this->_fields[] = 'lastname';
		$this->_fields[] = 'email';
		$this->_fields[] = 'repoareaid';
		$this->_fieldTypes['pseudoId'] = 'string';
		$this->_fieldTypes['externalId'] = 'integer';
		$this->_fieldTypes['contactTypeId'] = 'integer';
		$this->_fieldTypes['firstname'] = 'string';
		$this->_fieldTypes['lastname'] = 'string';
		$this->_fieldTypes['email'] = 'string';
		$this->_fieldTypes['repoareaid'] = 'integer';
		$this->_table = 'v_meta_product_repo_area_contacts';
	}
}
