<?php
namespace Application\Model\DbTable;




class AppVOsManualBase extends AROTable
{
	protected $_name = '__app_vos';
	protected $_primary = array('void', 'appid');
	protected $_sequence = false;
}
