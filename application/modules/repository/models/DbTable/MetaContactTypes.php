<?php
class Repository_Model_DbTable_Row_MetaContactTypes extends Zend_Db_Table_Row_Abstract
{


}

class Repository_Model_DbTable_MetaContactTypes extends Repository_Model_DbTable_MetaContactTypesBase
{
	protected $_rowClass = 'Repository_Model_DbTable_Row_MetaContactTypes';
}
