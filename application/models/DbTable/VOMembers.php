<?php
class Default_Model_DbTable_Row_VOMembers extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_VOMembers extends Default_Model_DbTable_VOMembersBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_VOMembers';
	protected $_primary = array('void', 'researcherid');
}
