<?php
class Default_Model_DbTable_Row_AppAutoCountries extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_AppAutoCountries extends Default_Model_DbTable_AppAutoCountriesBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_AppAutoCountries';
	protected $_primary = array('id', 'countryid');
}
