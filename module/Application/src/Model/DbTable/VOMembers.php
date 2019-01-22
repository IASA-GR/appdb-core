<?php
namespace Application\Model\DbTable;




class VOMembers extends VOMembersBase
{
	protected $_rowClass = 'Application\Model\DbTable\Row\VOMembers';
	protected $_primary = array('void', 'researcherid');
}
