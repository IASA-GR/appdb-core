<?php
class Repository_Model_MetaPoaDocLinksFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(1);
		$this->_fields[] = 'id';
		$this->_fields[] = 'poaId';
		$this->_fields[] = 'documentationLink';
		$this->_fields[] = 'documentationLinkType';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['poaId'] = 'integer';
		$this->_fieldTypes['documentationLink'] = 'string';
		$this->_fieldTypes['documentationLinkType'] = 'integer';
		$this->_table = 'meta_poa_docLinks';
	}
}
