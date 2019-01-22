<?php
namespace Application\Model\DbTable;




class AppAPILogBase extends AROTable
{
	protected $_name = 'app_api_log';
	protected $_primary = 'id';
	protected $_sequence = true;
}
