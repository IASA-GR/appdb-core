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
// PUT YOUR CUSTOM CODE HERE
namespace Application\Model;

class VA extends VABase
{
	public function getVersions(){
		$versions = new VAversions();
		$versions->filter->vappid->equals($this->id);
		if( count( $versions->items ) > 0 ){
			return $versions->items;
		}
		return null;
	}
	
	public function getLatestVersion(){
		$versions = new VAversions();
		$versions->filter->vappid->equals($this->id)->and($versions->filter->published->equals(true)->and($versions->filter->archived->equals(false)->and($versions->filter->enabled->equals(true))));
		if( count( $versions->items ) > 0 ){
			return $versions->items[0];
		}
		return null;
	}
	
	public function getArchivedVersions(){
		$versions = new VAversions();
		$versions->filter->vappid->equals($this->id)->and($versions->filter->published->equals(true)->and($versions->filter->archived->equals(true)));
		$versions->filter->orderby("archivedon DESC");
		if( count( $versions->items ) > 0 ){
			return $versions->items;
		}
		return null;
	}
	
	public function getUnpublishedVersion(){
		$versions = new VAversions();
		$versions->filter->vappid->equals($this->id)->and($versions->filter->published->equals(false)->and($versions->filter->archived->equals(false)));
		if( count( $versions->items ) > 0 ){
			return $versions->items[0];
		}
		return null;
	}
}
