<?php
namespace Application\Model\DbTable;




class AppContactMiddlewaresBase extends AROTable
{
	protected $_name = 'appcontact_middlewares';
	protected $_primary = array('appid', 'researcherid', 'appmiddlewareid');
	protected $_sequence = false;
}
