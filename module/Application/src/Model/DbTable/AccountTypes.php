<?php
namespace Application\Model\DbTable;




class AccountTypes extends AccountTypesBase
{
	protected $_rowClass = 'Application\Model\DbTable\Row\AccountTypes';
	protected $_primary = 'id';
}
