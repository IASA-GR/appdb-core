<?php
namespace Application\Model\DbTable;




class ActorGroupMembers extends ActorGroupMembersBase
{
	protected $_rowClass = 'Application\Model\DbTable\Row\ActorGroupMembers';
	protected $_primary = 'id';
}
