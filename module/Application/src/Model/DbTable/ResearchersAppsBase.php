<?php
namespace Application\Model\DbTable;




class ResearchersAppsBase extends AROTable
{
	protected $_name = 'researchers_apps';
	protected $_primary = array('appid', 'researcherid');
	protected $_sequence = false;
}
