<?php
class Default_Model_DbTable_Row_EntityRelations extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_EntityRelations extends Default_Model_DbTable_EntityRelationsBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_EntityRelations';
	protected $_primary = 'id';
}
