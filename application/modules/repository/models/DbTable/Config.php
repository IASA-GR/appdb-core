<?php
class Repository_Model_DbTable_Row_Config extends Zend_Db_Table_Row_Abstract
{


}

class Repository_Model_DbTable_Config extends Repository_Model_DbTable_ConfigBase
{
	protected $_rowClass = 'Repository_Model_DbTable_Row_Config';
}
