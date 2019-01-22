<?php
namespace Application\Model\DbTable;




class AppContactItems extends AppContactItemsBase
{
	protected $_rowClass = 'Application\Model\DbTable\Row\AppContactItems';
	protected $_primary = array('appid','researcherid','itemid','itemtype');
}
