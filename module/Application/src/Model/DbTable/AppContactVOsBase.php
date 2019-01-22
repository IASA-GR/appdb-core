<?php
namespace Application\Model\DbTable;




class AppContactVOsBase extends AROTable
{
	protected $_name = 'appcontact_vos';
	protected $_primary = array('appid', 'researcherid', 'void');
	protected $_sequence = false;
}
