<?php
class Default_Model_DbTable_Row_AppPerCountries extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_AppPerCountries extends Default_Model_DbTable_AppPerCountriesBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_AppPerCountries';
	protected $_primary = 'id';
}
