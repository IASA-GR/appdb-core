<?php
namespace Application\Model\DbTable;




class AccessTokenNetfiltersBase extends AROTable
{
	protected $_name = 'access_token_netfilters';
	protected $_primary = array('tokenid', 'netfilter');
	protected $_sequence = false;
}
