<?php
class Default_Model_DbTable_Row_Contacts extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_Contacts extends Default_Model_DbTable_ContactsBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_Contacts';
}
