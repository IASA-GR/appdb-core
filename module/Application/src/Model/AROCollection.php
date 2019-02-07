<?php
/**
 * Copyright (C) 2015 IASA - Institute of Accelerating Systems and Applications (http://www.iasa.gr)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and 
 * limitations under the License.
 */
?>
<?php
namespace Application\Model;

class AROCollection {
	protected $_items;
	protected $_mapper;
	protected $_filter;
	protected $_format;
	protected $_basename;
	protected $_baseitemname;

	public function __construct($filter = null)
	{
		$this->_items = null;
		if (! is_null($filter)) {
			$this->_filter = $filter;
		}
	}
	
	public function __destruct() {
		if (is_array($this->_items)) {
			for ($i = count($this->_items) - 1; $i >= 0; --$i) {
				unset($this->_items[$i]);
			}
		}
		unset($this->_items);
		unset($this->_mapper);
		unset($this->_filter);
	}

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
				throw new \Exception("Invalid " . $this->_basename . " property: '$name'");
		}
		$this->$method($value);
	}

	public function __get($name)
	{
		$method = 'get' . $name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
				throw new \Exception("Invalid " . $this->_basename . " property: '$name'");
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
			$type = "Application\\Model\\" . $this->_basename . "Mapper";
			$this->setMapper(new $type);
		}
		return $this->_mapper;
	}

	public function getFilter()
	{
		if (null === $this->_filter) {
			$type = "Application\\Model\\" . $this->_basename . "Filter";
			$this->setFilter(new $type);
		}
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
				if ( $item === $index ) {
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
		$XML = "<" . $this->_basename . ">\n";
		foreach ($this->_items as $item) {
			if (! is_null($item)) $XML .= $item->toXML();
		}
		$XML .= "</" . $this->_basename . ">\n";
		return $XML;
	}
}
