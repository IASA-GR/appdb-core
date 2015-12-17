<?php
class Default_Model_DbTable_Row_AppCountries extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_AppCountries extends Default_Model_DbTable_AppCountriesBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_AppCountries';
	protected $_primary = array('id', 'countryid');
}
