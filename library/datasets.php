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

class Datasets{
	public static function nameAvailability($name, $datasetid=0){
		$dts = new Default_Model_Datasets();
		$f1 = new Default_Model_DatasetsFilter();
		$f1->name->ilike(trim($name));
		$dts->filter->chain($f1, 'AND');
		if( $datasetid > 0 ){
			$f2 = new Default_Model_DatasetsFilter();
			$f2->id->notequals($datasetid);
			$dts->filter->chain($f2, 'AND');
		}
		if( count($dts->items) > 0 ){
			$dt = $dts->items[0];
			return array( "id"=>$dt->id,"guid"=>$dt->guid, "name" => $dt->name, "description"=> $dt->description);
		}
		return true;
	}
}