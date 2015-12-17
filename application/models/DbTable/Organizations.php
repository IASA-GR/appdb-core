<?php
class Default_Model_DbTable_Row_Organizations extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_Organizations extends Default_Model_DbTable_OrganizationsBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_Organizations';
}
