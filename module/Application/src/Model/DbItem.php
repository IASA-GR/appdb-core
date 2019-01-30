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

interface IDbMapper {
}

class DbMapper implements Default_ModelIDbMapper {

}

interface IDbItem {
    public function save();
    public function remove();
    public function serialize($recursive=false);
    public function getMapper();
    public function getParent();
    public function getObjectName();
    public function getParentObjectName();
}

abstract class DbItem implements IDbItem {
    protected $_parent;

    abstract public function serialize($recursive=false);
    abstract public function getObjectName();
    abstract public function getParentObjectName();
    abstract protected function properties();

    public function __construct($parent = null) {
        if ( !is_null($parent) ) {
            $this->_parent = $parent;
        } else {
            $parent = "".$this->getParentObjectName();
            $this->_parent = new $parent();
        } 
    }

    public function save() {
        $this->getMapper()->save($this);
    }

    public function remove() {
        $this->getMapper()->remove($this);
    }

    public function getMapper()
    {
        return $this->getParent()->getMapper();
    }

    public function getParent() {
        return $this->_parent;
    }
}

interface IDbItemCollection {
    public function getMapper();
    public function refresh();
    public function item($id);
    public function getItems();
    public function count();
    public function save();
    public function getFilter();
    public function setFilter();
    public function add($item);
    public function remove($index);
    public function setFormat($format);
    public function getFormat();
    public function serialize();
    public function getObjectName();
}

abstract class DbItemCollection implements IIDbItemCollection {
    protected $_mapper;
    protected $_filter;

    abstract public function getObjectName();

    public function __construct() {
        $mapper = "".$this->getObjectName()."Mapper" ;
        $this->_mapper = new $mapper();
    }

    public function getMapper() {
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


    public function refresh()
    {
        $this->_items = $this->getMapper()->fetchAll($this->_filter);
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

    public function serialize()
    {
        $XML = "<".$this->getObjectName().">";
        foreach ($this->_items as $item) {
            if ( ! ($item === null) ) $XML .= $item->serialize();
        }
        $XML .= "</".$this->getObjectName().">";
        return $XML;
    }

}
?>
