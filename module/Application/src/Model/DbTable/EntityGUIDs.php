<?php
namespace Application\Model\DbTable;




class EntityGUIDs extends EntityGUIDsBase
{
	protected $_rowClass = 'Application\Model\DbTable\Row\EntityGUIDs';
	protected $_primary = 'guid';
}
