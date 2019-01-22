<?php
namespace Application\Model\DbTable;




class AppTeam extends AppTeamBase
{
	protected $_rowClass = 'Application\Model\DbTable\Row\AppTeam';
	protected $_primary = array('id', 'appid');
}
