<?php
class Repository_Model_DbTable_Row_MetaContacts extends Zend_Db_Table_Row_Abstract
{


}

class Repository_Model_DbTable_MetaContacts extends Repository_Model_DbTable_MetaContactsBase
{
	protected $_rowClass = 'Repository_Model_DbTable_Row_MetaContacts';
}
