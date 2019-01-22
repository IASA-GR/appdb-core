<?php
namespace Application\Model\DbTable;




class CountryRegions extends CountryRegionsBase
{
	protected $_rowClass = 'Application\Model\DbTable\Row\CountryRegions';
	protected $_primary = 'countryid';
}
