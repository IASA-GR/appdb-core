<?php
// PLEASE DO NOT EDIT THIS FILE
// IT IS AUTOMATOCALLY GENERATED BY THE MODELLER
// AND ANY CHANGES WILL BE OVERWRITTEN
class Repository_Model_CommRepoDmethodsBase {
	protected $_items;
	protected $_mapper;
	protected $_filter;
	protected $_format;

	public function __construct($filter = null)
	{
		$this->_items = null;
		if ( $filter === null ) {
			$this->_filter = new Repository_Model_CommRepoDmethodsFilter();
		} else {
			$this->_filter = $filter;
		}
	}

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
				throw new Exception("Invalid CommRepoDmethods property: '$name'");
		}
		$this->$method($value);
	}

	public function __get($name)
	{
		$method = 'get' . $name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
				throw new Exception("Invalid CommRepoDmethods property: '$name'");
		}
		return $this->$method();
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
				$this->setMapper(new Repository_Model_CommRepoDmethodsMapper());
		}
		return $this->_mapper;
	}

	public function getFilter()
	{
		return $this->_filter;
	}

	public function setFilter($value)
	{
		$this->_filter=$value;
		return $this;
	}

	public function add($item)
	{
		$this->getMapper()->save($item);
		$this->_items[] = $item;
	}

	public function remove($index)
	{
		if ( is_object($index) ) {
			$this->getMapper()->delete($index);
			$i=0;
			foreach($this->_items as $item) {
				if ( $item == $index ) {
					unset($this->_items[$i]);
					break;
				}
				$i++;
			}
		} else {
			if ( isset($this->items[$index]) ) {
				$this->getMapper()->delete($this->items[$index]);
				unset($this->_items[$index]);
			}
		}
		return $this;
	}

	public function save()
	{
		foreach($this->_items as $item) {
			$item->save();
		}
		return $this;
	}

	public function setFormat($value) {
		$this->_format = $value;
		return $this;
	}

	public function getFormat() {
		return $this->_format;
	}

	public function getItems()
	{
		if ($this->_items === null) $this->refresh();
		return $this->_items;
	}

	public function item($id)
	{
		if ($this->_items === null) $this->refresh();
		foreach ($this->_items as $item)
			if ($item->id == $id) return $item;
		return null;
	}

	public function refresh($format = '')
	{
		$this->_items = $this->getMapper()->fetchAll($this->_filter, $format);
		return $this;
	}

	public function count()
	{
		if ( $this->_items === null ) {
			return $this->getMapper()->count($this->_filter);
		} else {
			return count($this->_items);
		};
	}

	public function toXML()
	{
		$XML = "<CommRepoDmethods>
";
		foreach ($this->_items as $item) {
			if ( ! ($item === null) ) $XML .= $item->toXML();
		}
		$XML .= "</CommRepoDmethods>
";
		return $XML;
	}
}
