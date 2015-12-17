<?php
class Default_Model_DbTable_Row_AppViews extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_AppViews extends Default_Model_DbTable_AppViewsBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_AppViews';
	protected $_primary = 'id';
}
