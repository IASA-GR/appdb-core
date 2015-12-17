<?php
class Default_Model_DbTable_Row_AccountTypes extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_AccountTypes extends Default_Model_DbTable_AccountTypesBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_AccountTypes';
	protected $_primary = 'id';
}
