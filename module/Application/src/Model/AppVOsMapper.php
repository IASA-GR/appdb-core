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

class AppVOsMapper extends AppVOsMapperBase
{
	public function save(AROItem $value)
	{
		$tbl = new DbTable\AppVOsManual(); 
		$data = array();
		if ( ! isnull($value->getVoID()) ) $data['void'] = $value->getVoID();
		if ( ! isnull($value->getAppID()) ) $data['appid'] = $value->getAppID();


		$q1 = array('void = ?', 'appid = ?');
		$q2 = array($value->void, $value->appid);
		$select = $tbl->getSql()->select();
		for ($i=0; $i < count($q1); $i++) {
			$select->where($q1[$i],$q2[$i]);
		}
		$new_entry = (count(db()->query(SQL2STR($this, $select), array())->toArray()) == 0);
		if ( $new_entry ) {
			$tbl->insert($data);
		} else {
			$s = array();
			for ($i=0; $i < count($q1); $i++) {
				$s[]=$tbl->getAdapter()->quoteInto($q1[$i],$q2[$i]);
			}
			$tbl->update($data, $s);
		}
	}

	public function delete(AppVO $value)
	{
		$tbl = new DbTable\AppVOsManual(); 
		$q1 = array('void = ?', 'appid = ?');
		$q2 = array($value->void, $value->appid);
		$s = array();
		for ($i=0; $i < count($q1); $i++) {
			$s[]=$tbl->getAdapter()->quoteInto($q1[$i],$q2[$i]);
		}
		$tbl->delete($s);
	}


}
