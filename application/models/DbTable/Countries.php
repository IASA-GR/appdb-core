<?php
class Default_Model_DbTable_Row_Countries extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_Countries extends Default_Model_DbTable_CountriesBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_Countries';
}
