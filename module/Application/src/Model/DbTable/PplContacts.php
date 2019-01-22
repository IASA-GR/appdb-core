<?php
namespace Application\Model\DbTable;




class PplContacts extends PplContactsBase
{
	protected $_rowClass = 'Application\Model\DbTable\Row\PplContacts';
	protected $_primary = 'id';
}
