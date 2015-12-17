<?php
class Default_Model_DbTable_Row_Actors extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_Actors extends Default_Model_DbTable_ActorsBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_Actors';
	protected $_primary = array("id", "type");
}
