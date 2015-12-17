<?php
class Default_Model_DbTable_Row_PplViews extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_PplViews extends Default_Model_DbTable_PplViewsBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_PplViews';
	protected $_primary = 'id';
}
