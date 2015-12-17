<?php
class Default_Model_DbTable_Row_Hypervisors extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_Hypervisors extends Default_Model_DbTable_HypervisorsBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_Hypervisors';
	protected $_primary = 'id';
}
