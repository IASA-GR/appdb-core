<?php
class Default_Model_DbTable_Row_UserAccounts extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_UserAccounts extends Default_Model_DbTable_UserAccountsBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_UserAccounts';
}
