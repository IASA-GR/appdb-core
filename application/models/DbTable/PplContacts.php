<?php
class Default_Model_DbTable_Row_PplContacts extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_PplContacts extends Default_Model_DbTable_PplContactsBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_PplContacts';
	protected $_primary = 'id';
}
