<?php
class Default_Model_DbTable_Row_Authors extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_Authors extends Default_Model_DbTable_AuthorsBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_Authors';
	protected $_primary = 'id';
}
