<?php
namespace Application\Model\DbTable;




class AppAutoCountries extends AppAutoCountriesBase
{
	protected $_rowClass = 'Application\Model\DbTable\Row\AppAutoCountries';
	protected $_primary = array('id', 'countryid');
}
