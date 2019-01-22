<?php
namespace Application\Model\DbTable;

class StatusesBase extends AROTable 
{
	protected $_name = 'statuses';
	protected $_primary = 'id';
	protected $_sequence = false;
}
