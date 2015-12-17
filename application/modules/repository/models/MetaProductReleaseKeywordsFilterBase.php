<?php
class Repository_Model_MetaProductReleaseKeywordsFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(1);
		$this->_fields[] = 'id';
		$this->_fields[] = 'releaseId';
		$this->_fields[] = 'keyword';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['releaseId'] = 'integer';
		$this->_fieldTypes['keyword'] = 'string';
		$this->_table = 'meta_product_release_keywords';
	}
}
