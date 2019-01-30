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

class VaProvidersMapper extends VaProvidersMapperBase
{
	public function fetchAll($filter = null, $format = '', $xmldetailed = false) {
		if ($format == '') {
			return parent::fetchAll($filter);
		} else {
			if ($format === 'xml') {
				$func = "va_provider_to_xml";
				if ($xmldetailed) {
					$func .= "_ext";
				}
				$select = $this->getDbTable()->select();
				if ($filter->expr() != "") {
					$select->where($filter->expr());
				}
				$this->getDbTable()->getAdapter()->setFetchMode(Zend_Db::FETCH_OBJ);
				$resultSet = $this->getDbTable()->getAdapter()->query("SELECT $func(id) as va_provider FROM (".$select.") AS T ORDER BY sitename;")->fetchAll();
				$entries = array();
				foreach ($resultSet as $row) {
					$entry = $row->va_provider;
					$entries[] = $entry;
				}
				return $entries;
         	}
		}
	}

}
