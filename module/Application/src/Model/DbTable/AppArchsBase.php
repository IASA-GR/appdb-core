<?php
namespace Application\Model\DbTable;




class AppArchsBase extends AROTable
{
	protected $_name = 'app_archs';
	protected $_primary = array('appid', 'archid');
	protected $_sequence = false;
}
