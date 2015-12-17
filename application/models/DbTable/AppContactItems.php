<?php
class Default_Model_DbTable_Row_AppContactItems extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_AppContactItems extends Default_Model_DbTable_AppContactItemsBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_AppContactItems';
	protected $_primary = array('appid','researcherid','itemid','itemtype');
}
