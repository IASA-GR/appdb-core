<?php
namespace Application\Model\DbTable;

//use Application\Model\DbTable\Row\StatusesBase;
//use Zend\Db\TableGateway\AbstractTableGateway;

class AROTable extends \Zend\Db\TableGateway\AbstractTableGateway
{
	protected $_name;
	protected $_primary;
	protected $_sequence;

	public function __construct() {
		$this->adapter = \APPDB_ADAPTER();
		$this->table = $this->_name;
		$this->initialize();
	}

	public function getName() {
		return $this->_name;
	}

	public function getPrimary() {
		return $this->_primary;
	}

	public function getSequence() {
		return $this->_sequence;
	}
}
