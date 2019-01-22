<?php
namespace Application\Model\DbTable;




class Permissions extends PermissionsBase
{
	protected $_rowClass = 'Application\Model\DbTable\Row\Permissions';
	protected $_primary = 'id';
}
