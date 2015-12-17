<?php
class Default_Model_DbTable_Row_ExtAuthors extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_ExtAuthors extends Default_Model_DbTable_ExtAuthorsBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_ExtAuthors';
}
