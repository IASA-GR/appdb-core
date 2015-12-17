<?php
class Default_Model_DbTable_Row_AppCategories extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_AppCategories extends Default_Model_DbTable_AppCategoriesBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_AppCategories';
}
