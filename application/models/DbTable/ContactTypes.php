<?php
class Default_Model_DbTable_Row_ContactTypes extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_ContactTypes extends Default_Model_DbTable_ContactTypesBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_ContactTypes';
}
