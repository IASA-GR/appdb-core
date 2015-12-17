<?php
class Default_Model_DbTable_Row_AppReleaseCount extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_AppReleaseCount extends Default_Model_DbTable_AppReleaseCountBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_AppReleaseCount';
	protected $_primary = array('appid', 'state');
}
