<?php
class Default_Model_DbTable_Row_Applications extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_Applications extends Default_Model_DbTable_ApplicationsBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_Applications';
}
