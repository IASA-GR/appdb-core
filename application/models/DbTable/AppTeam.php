<?php
class Default_Model_DbTable_Row_AppTeam extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_AppTeam extends Default_Model_DbTable_AppTeamBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_AppTeam';
	protected $_primary = array('id', 'appid');
}
