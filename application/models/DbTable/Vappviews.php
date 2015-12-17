<?php
class Default_Model_DbTable_Row_Vappviews extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_Vappviews extends Default_Model_DbTable_VappviewsBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_Vappviews';
	protected $_primary = array('vmiinstanceid', 'vappversionid');
}
