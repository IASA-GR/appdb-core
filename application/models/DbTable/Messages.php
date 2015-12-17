<?php
class Default_Model_DbTable_Row_Messages extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_Messages extends Default_Model_DbTable_MessagesBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_Messages';
}
