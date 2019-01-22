<?php
namespace Application\Model\DbTable;




class AppCountries extends AppCountriesBase
{
	protected $_rowClass = 'Application\Model\DbTable\Row\AppCountries';
	protected $_primary = array('id', 'countryid');
}
