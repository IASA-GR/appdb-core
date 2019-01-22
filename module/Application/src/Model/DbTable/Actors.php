<?php
namespace Application\Model\DbTable;




class Actors extends ActorsBase
{
	protected $_rowClass = 'Application\Model\DbTable\Row\Actors';
	protected $_primary = array("id", "type");
}
