<?php
namespace Application\Model\DbTable;




class AppVOsBase extends AROTable
{
	protected $_name = 'app_vos';
	protected $_primary = array('void', 'appid');
	protected $_sequence = false;
}
