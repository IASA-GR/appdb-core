<?php
class Default_Model_DbTable_Row_Permissions extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_Permissions extends Default_Model_DbTable_PermissionsBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_Permissions';
	protected $_primary = 'id';
}
