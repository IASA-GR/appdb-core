 <?php 
class Repository_Model_DbTable_ZendDBTable extends Zend_Db_Table_Abstract {
   protected $_use_adapter = 'repository';

   public function __construct($config = null) {
		   if (isset($this->_use_adapter)) {
				   $config = $this->_use_adapter;
		   }
		   return parent::__construct($config);
   }
}
