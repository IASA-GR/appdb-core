<?php
namespace Application\Model\DbTable;




class AppProgLangsBase extends AROTable
{
	protected $_name = 'appproglangs';
	protected $_primary = array('appid', 'proglangid');
	protected $_sequence = false;
}
