<?php
namespace Application\Model\DbTable;




class VOMSesBase extends AROTable
{
	protected $_name = 'vomses';
	protected $_primary = array('void', 'hostname');
	protected $_sequence = false;
}
