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

class VOMembersMapper extends VOMembersMapperBase
{
	public function fetchAll($filter = null, $format = '', $xmldetailed = false) {
		if ( $format === "xml" ) {
			$ores = parent::fetchAll(null);
			$ids = array();
			foreach ($ores as $i) {
				$ids[] = $i->void;
			}
			$res = new VOs();
			$res->filter->id->in($ids);
			if ($filter !== null) {
				$res->filter->chain($filter, "AND");
				$res->filter->limit = $filter->limit;
				$res->filter->offset = $filter->offset;
			}
			$res->filter->orderBy("name");
			$res->refresh("xml", $xmldetailed);
			$ret = $res->items;
			for ($ic = 0; $ic < count($ret); $ic++) {
				$i = $ret[$ic];
				$ii = '<e xmlns:discipline="' . RestAPIHelper::XMLNS_DISCIPLINE() . '" xmlns:vo="' . RestAPIHelper::XMLNS_VO() . "\">$i</e>";
				$x = new SimpleXMLElement($ii);
				$x = $x->xpath("//vo:vo");
				$x = $x[0];
				$xid = strval($x->attributes()->id);
				foreach ($ores as $j) {
					if ($j->void == $xid) {
						$i = str_replace("<vo:vo ", "<vo:vo relation=\"member\" member_since=\"". $j->memberSince . "\" ", $i);
						$ret[$ic] = $i;
						break;
					}
				}
			}
			return $ret;
		} else {
			return parent::fetchAll($filter, $format, $xmldetailed);
		}
	}

}
