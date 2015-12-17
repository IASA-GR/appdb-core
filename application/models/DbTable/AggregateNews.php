<?php
class Default_Model_DbTable_Row_AggregateNews extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_AggregateNews extends Default_Model_DbTable_AggregateNewsBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_AggregateNews';
	protected $_primary = 'id';
}
