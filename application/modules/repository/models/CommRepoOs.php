<?php
// PUT YOUR CUSTOM CODE HERE
class Repository_Model_CommRepoOs extends Repository_Model_CommRepoOsBase
{
	private $_dmethods;
	public function getDeployMethods(){
		if ($this->_dmethods === null) {
			$r = new Repository_Model_CommRepoAllowedOsDmethodCombinations();
			$r->filter->osId->equals($this->id);
			$this->_dmethods = $r->items;
		}
		return $this->_dmethods;
	}
}
