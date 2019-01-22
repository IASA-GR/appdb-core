<?php
namespace Application\Model\DbTable;




class Vappviews extends VappviewsBase
{
	protected $_rowClass = 'Application\Model\DbTable\Row\Vappviews';
	protected $_primary = array('vmiinstanceid', 'vappversionid');
}
