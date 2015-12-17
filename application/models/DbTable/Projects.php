<?php
class Default_Model_DbTable_Row_Projects extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_Projects extends Default_Model_DbTable_ProjectsBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_Projects';
}
