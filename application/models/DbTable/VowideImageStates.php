<?php
class Default_Model_DbTable_Row_VowideImageStates extends Zend_Db_Table_Row_Abstract
{


}

class Default_Model_DbTable_VowideImageStates extends Default_Model_DbTable_VowideImageStatesBase
{
	protected $_rowClass = 'Default_Model_DbTable_Row_VowideImageStates';
	protected $_primary = 'id';
}
