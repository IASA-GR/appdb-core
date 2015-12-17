<?php
class Default_Model_DbTable_Row_VaProviders extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_VaProviders extends Default_Model_DbTable_VaProvidersBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_VaProviders';
	protected $_primary = 'id';
}
