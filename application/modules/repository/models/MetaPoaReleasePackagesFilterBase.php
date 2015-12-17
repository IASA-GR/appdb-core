<?php
class Repository_Model_MetaPoaReleasePackagesFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(1);
		$this->_fields[] = 'id';
		$this->_fields[] = 'poaId';
		$this->_fields[] = 'pkgName';
		$this->_fields[] = 'pkgVersion';
		$this->_fields[] = 'pkgRelease';
		$this->_fields[] = 'pkgArch';
		$this->_fields[] = 'pkgType';
		$this->_fields[] = 'pkgFilename';
		$this->_fields[] = 'pkgDescription';
		$this->_fields[] = 'pkgInstallationSize';
		$this->_fields[] = 'pkgGroup';
		$this->_fields[] = 'pkgRequires';
		$this->_fields[] = 'pkgLicense';
		$this->_fields[] = 'pkgUrl';
		$this->_fields[] = 'pkgGeneral';
		$this->_fields[] = 'pkgMisc';
		$this->_fields[] = 'pkgLevel';
		$this->_fields[] = 'pkgSize';
		$this->_fields[] = 'pkgMd5Sum';
		$this->_fields[] = 'pkgSha1Sum';
		$this->_fields[] = 'pkgSha256Sum';
		$this->_fields[] = 'pkgVersionIndex';
		$this->_fields[] = 'timestampInserted';
		$this->_fields[] = 'insertedBy';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['poaId'] = 'integer';
		$this->_fieldTypes['pkgName'] = 'string';
		$this->_fieldTypes['pkgVersion'] = 'string';
		$this->_fieldTypes['pkgRelease'] = 'string';
		$this->_fieldTypes['pkgArch'] = 'string';
		$this->_fieldTypes['pkgType'] = 'string';
		$this->_fieldTypes['pkgFilename'] = 'string';
		$this->_fieldTypes['pkgDescription'] = 'string';
		$this->_fieldTypes['pkgInstallationSize'] = 'string';
		$this->_fieldTypes['pkgGroup'] = 'string';
		$this->_fieldTypes['pkgRequires'] = 'string';
		$this->_fieldTypes['pkgLicense'] = 'string';
		$this->_fieldTypes['pkgUrl'] = 'string';
		$this->_fieldTypes['pkgGeneral'] = 'string';
		$this->_fieldTypes['pkgMisc'] = 'string';
		$this->_fieldTypes['pkgLevel'] = 'enum';
		$this->_fieldTypes['pkgSize'] = 'bigint';
		$this->_fieldTypes['pkgMd5Sum'] = 'string';
		$this->_fieldTypes['pkgSha1Sum'] = 'string';
		$this->_fieldTypes['pkgSha256Sum'] = 'string';
		$this->_fieldTypes['pkgVersionIndex'] = 'integer';
		$this->_fieldTypes['timestampInserted'] = 'string';
		$this->_fieldTypes['insertedBy'] = 'integer';
		$this->_table = 'meta_poa_release_packages';
	}
}
