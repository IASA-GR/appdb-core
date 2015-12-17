<?php
class Repository_Model_MetaContactsFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(1);
		$this->_fields[] = 'id';
		$this->_fields[] = 'assocId';
		$this->_fields[] = 'assocEntity';
		$this->_fields[] = 'externalId';
		$this->_fields[] = 'contactTypeId';
		$this->_fields[] = 'firstname';
		$this->_fields[] = 'lastname';
		$this->_fields[] = 'email';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['assocId'] = 'integer';
		$this->_fieldTypes['assocEntity'] = 'string';
		$this->_fieldTypes['externalId'] = 'integer';
		$this->_fieldTypes['contactTypeId'] = 'integer';
		$this->_fieldTypes['firstname'] = 'string';
		$this->_fieldTypes['lastname'] = 'string';
		$this->_fieldTypes['email'] = 'varchar';
		$this->_table = 'meta_contacts';
	}
}
