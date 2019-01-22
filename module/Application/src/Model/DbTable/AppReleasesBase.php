<?php
namespace Application\Model\DbTable;




class AppReleasesBase extends AROTable
{
	protected $_name = 'app_releases';
	protected $_primary = 'id';
	protected $_sequence = true;
}
