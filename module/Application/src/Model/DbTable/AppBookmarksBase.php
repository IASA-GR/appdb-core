<?php
namespace Application\Model\DbTable;




class AppBookmarksBase extends AROTable
{
	protected $_name = 'appbookmarks';
	protected $_primary = array('appid', 'researcherid');
	protected $_sequence = false;
}
