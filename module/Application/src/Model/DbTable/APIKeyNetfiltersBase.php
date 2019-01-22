<?php
namespace Application\Model\DbTable;




class APIKeyNetfiltersBase extends AROTable
{
	protected $_name = 'apikey_netfilters';
	protected $_primary = array('keyid', 'netfilter');
	protected $_sequence = false;
}
