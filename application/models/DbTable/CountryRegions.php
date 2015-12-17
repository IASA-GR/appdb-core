<?php
class Default_Model_DbTable_Row_CountryRegions extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_CountryRegions extends Default_Model_DbTable_CountryRegionsBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_CountryRegions';
	protected $_primary = 'countryid';
}
