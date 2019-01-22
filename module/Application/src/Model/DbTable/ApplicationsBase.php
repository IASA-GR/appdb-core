<?php
namespace Application\Model\DbTable;



class ApplicationsBase extends AROTable 
{
	protected $_name = 'applications';
	protected $_primary = 'id';
	protected $_sequence = true;
}
