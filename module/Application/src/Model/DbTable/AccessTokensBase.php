<?php
namespace Application\Model\DbTable;




class AccessTokensBase extends AROTable
{
	protected $_name = 'access_tokens';
	protected $_primary = 'id';
	protected $_sequence = true;
}
