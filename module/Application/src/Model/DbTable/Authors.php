<?php
namespace Application\Model\DbTable;




class Authors extends AuthorsBase
{
	protected $_rowClass = 'Application\Model\DbTable\Row\Authors';
	protected $_primary = 'id';
}
