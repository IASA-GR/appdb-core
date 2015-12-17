<?php
class Default_Model_DbTable_Row_ActorGroupMembers extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_ActorGroupMembers extends Default_Model_DbTable_ActorGroupMembersBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_ActorGroupMembers';
	protected $_primary = 'id';
}
