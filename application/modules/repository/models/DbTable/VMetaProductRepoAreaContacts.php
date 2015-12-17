<?php
class Repository_Model_DbTable_Row_VMetaProductRepoAreaContacts extends Zend_Db_Table_Row_Abstract
{


}

class Repository_Model_DbTable_VMetaProductRepoAreaContacts extends Repository_Model_DbTable_VMetaProductRepoAreaContactsBase
{
	protected $_rowClass = 'Repository_Model_DbTable_Row_VMetaProductRepoAreaContacts';
    protected $_primary = 'pseudoId';
}
