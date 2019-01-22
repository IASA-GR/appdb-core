<?php
namespace Application\Model\DbTable;




class AggregateNews extends AggregateNewsBase
{
	protected $_rowClass = 'Application\Model\DbTable\Row\AggregateNews';
	protected $_primary = 'id';
}
