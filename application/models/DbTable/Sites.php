<?php
class Default_Model_DbTable_Row_Sites extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_Sites extends Default_Model_DbTable_SitesBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_Sites';
}
