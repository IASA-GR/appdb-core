<?php
namespace Application\Model\DbTable;




class AppContactOtherItemsBase extends AROTable
{
	protected $_name = 'appcontact_otheritems';
	protected $_primary = array('appid', 'researcherid', 'item');
	protected $_sequence = false;
}
