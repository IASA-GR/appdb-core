<?php
class Default_Model_DbTable_Row_UserRequests extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_UserRequests extends Default_Model_DbTable_UserRequestsBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_UserRequests';
}
