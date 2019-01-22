<?php
namespace Application\Model\DbTable;




class LinkStatuses extends LinkStatusesBase
{
	protected $_rowClass = 'Application\Model\DbTable\Row\LinkStatuses';
	protected $_primary = array('name','url');
}
