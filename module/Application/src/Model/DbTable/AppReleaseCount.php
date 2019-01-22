<?php
namespace Application\Model\DbTable;




class AppReleaseCount extends AppReleaseCountBase
{
	protected $_rowClass = 'Application\Model\DbTable\Row\AppReleaseCount';
	protected $_primary = array('appid', 'state');
}
