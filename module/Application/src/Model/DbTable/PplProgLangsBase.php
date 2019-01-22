<?php
namespace Application\Model\DbTable;




class PplProgLangsBase extends AROTable
{
	protected $_name = 'pplproglangs';
	protected $_primary = array('researcherid', 'proglangid');
	protected $_sequence = false;
}
