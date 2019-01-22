<?php
namespace Application\Model\DbTable;




class VOContacts extends VOContactsBase
{
	protected $_rowClass = 'Application\Model\DbTable\Row\VOContacts';
	protected $_primary = array('void', 'researcherid', 'role');
}
