<?php
namespace Application\Model\DbTable;




class AppManualCountriesBase extends AROTable
{
	protected $_name = 'appmanualcountries';
	protected $_primary = array('appid', 'countryid');
	protected $_sequence = false;
}
