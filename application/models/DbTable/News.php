<?php
class Default_Model_DbTable_Row_News extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_News extends Default_Model_DbTable_NewsBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_News';
}
