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

class MyNew {

 protected $_e;

 public function init($e) {
     $this->_e = $e;
 }

    public function __set($name,$value)
    {
        $method = 'set'.$name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid MyNew property '."'".$name."'");
        }
        $this->$method($value);
    }

    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid MyNew property'."'".$name."'");
        }
        return $this->$method();
    }

 public function getSubjectID() {
    return $this->_e['subjectid'];
 }

 public function getSubjectType() {
    return $this->_e['subjecttype'];
 }

 public function getTimestamp() {
     return $this->_e['timestamp'];
 }

 public function getAction() {
    return $this->_e['action'];
 }
}
