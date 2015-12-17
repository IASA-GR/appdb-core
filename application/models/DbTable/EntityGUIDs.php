<?php
class Default_Model_DbTable_Row_EntityGUIDs extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_EntityGUIDs extends Default_Model_DbTable_EntityGUIDsBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_EntityGUIDs';
	protected $_primary = 'guid';
}
