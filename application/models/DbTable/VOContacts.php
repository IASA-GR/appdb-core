<?php
class Default_Model_DbTable_Row_VOContacts extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_VOContacts extends Default_Model_DbTable_VOContactsBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_VOContacts';
	protected $_primary = array('void', 'researcherid', 'role');
}
