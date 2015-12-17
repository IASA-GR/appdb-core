<?php
class Default_Model_DbTable_Row_APIKeys extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_APIKeys extends Default_Model_DbTable_APIKeysBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_APIKeys';
}
