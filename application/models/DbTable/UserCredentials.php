<?php
class Default_Model_DbTable_Row_UserCredentials extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_UserCredentials extends Default_Model_DbTable_UserCredentialsBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_UserCredentials';
}
