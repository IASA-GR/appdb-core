<?php
namespace Application\Model\DbTable;




class AppOSesBase extends AROTable
{
	protected $_name = 'app_oses';
	protected $_primary = array('appid', 'osid');
	protected $_sequence = false;
}
