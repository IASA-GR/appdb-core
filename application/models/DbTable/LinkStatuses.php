<?php
class Default_Model_DbTable_Row_LinkStatuses extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_LinkStatuses extends Default_Model_DbTable_LinkStatusesBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_LinkStatuses';
	protected $_primary = array('name','url');
}
