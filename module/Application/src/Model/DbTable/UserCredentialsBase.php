<?php
namespace Application\Model\DbTable;




class UserCredentialsBase extends AROTable
{
	protected $_name = 'user_credentials';
	protected $_primary = 'id';
	protected $_sequence = true;
}
