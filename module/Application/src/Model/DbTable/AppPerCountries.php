<?php
namespace Application\Model\DbTable;




class AppPerCountries extends AppPerCountriesBase
{
	protected $_rowClass = 'Application\Model\DbTable\Row\AppPerCountries';
	protected $_primary = 'id';
}
