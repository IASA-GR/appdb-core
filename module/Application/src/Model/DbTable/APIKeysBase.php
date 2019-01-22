<?php
namespace Application\Model\DbTable;




class APIKeysBase extends AROTable
{
	protected $_name = 'apikeys';
	protected $_primary = 'id';
	protected $_sequence = true;
}
