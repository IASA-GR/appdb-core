<?php
namespace Application\Model\DbTable;




class PendingAccountsBase extends AROTable
{
	protected $_name = 'pending_accounts';
	protected $_primary = 'id';
	protected $_sequence = true;
}
