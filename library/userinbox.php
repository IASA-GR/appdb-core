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
class UserInbox{
	private static function getEnvelopStart($apiversion = '1.0'){
		return  '<appdb:appdb xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:appdb="http://appdb.egi.eu/api/1.0/appdb" xmlns:application="http://appdb.egi.eu/api/1.0/application" xmlns:discipline="http://appdb.egi.eu/api/1.0/discipline" xmlns:category="http://appdb.egi.eu/api/1.0/category" xmlns:dissemination="http://appdb.egi.eu/api/1.0/dissemination" '.
				'xmlns:person="http://appdb.egi.eu/api/' . $apiversion . '/person" '.
				'xmlns:permission="http://appdb.egi.eu/api/' . $apiversion . '/permission" '.
				'xmlns:privilege="http://appdb.egi.eu/api/' . $apiversion . '/privilege" '.
				'xmlns:user="http://appdb.egi.eu/api/' . $apiversion . '/user" '.
				'xmlns:virtualization="http://appdb.egi.eu/api/' . $apiversion . '/virtualization" '.
				'xmlns:message="http://appdb.egi.eu/api/' . $apiversion . '/message" '.
				'datatype="virtualization" version="' . $apiversion . '">';
	}
	private static function getEnvelopEnd(){
		return '</appdb:appdb>';
	}
	private static function getModel($uid,$flt=array()){
		$msgs = new Default_Model_Messages();
		$length = ( (isset($flt["length"]) && is_numeric($flt["length"]) )?intval($flt["length"]):10 );
		$offset = ( (isset($flt["offset"]) && is_numeric($flt["offset"]) )?intval($flt["offset"]):0 );
		$order = strtolower(trim(( (isset($flt["order"]) && is_string($flt["order"]) )?strval($flt["order"]):"sendon" )));
		$orderop = strtolower(trim(( (isset($flt["orderop"]) && is_string($flt["orderop"]) )?strval($flt["orderop"]):"ASC" )));
		$folder = strtolower(trim(( (isset($flt["folder"]) && is_string($flt["folder"]) )?strval($flt["folder"]):"all" )));
		$from = ( (isset($flt["from"]) && is_numeric($flt["from"]) )?intval($flt["from"]):null );
		$to = ( (isset($flt["to"]) && is_numeric($flt["to"]) )?intval($flt["to"]):null );
		$unread = (isset($flt["unread"])?true:false);
		$id = ( ( isset($flt["id"]) && is_numeric($flt["id"]) )?intval($flt["id"]):null );
		if( $id !== null ){
			$msgs->filter->id->equals($id);
		}else{
			switch($folder){
				case "inbox":
					$f1 = new Default_Model_MessagesFilter();
					$f2 = new Default_Model_MessagesFilter();
					$f3 = new Default_Model_MessagesFilter();

					$f1->receiverid->equals($uid);
					$f2->senderid->equals($from);
					$f3->isread->equals(false);

					$msgs->filter->chain($f1, "AND");
					if( $from !== null ){
						error_log("from $from");
						$msgs->filter->chain($f2, "AND");
					}
					if( $unread === true ){
						$msgs->filter->chain($f3, "AND");
					}

					break;
				case "outbox":
					$msgs->filter->senderid->equals($uid);
					if( $to !== null ){
						$msgs->filter->chain($msgs->filter->receiverid->equals($to), "AND");
					}
					break;
				default:
					$msgs->filter->senderid->equals($uid)->or($msgs->filter->receiverid->equals($uid));
					break;
			}
		}
		
		$msgs->filter->limit = $length;
		$msgs->filter->offset = $offset;
		
		return $msgs;
	}
	public static function getMessages($uid,$flt=array()){
		$envelop_start = self::getEnvelopStart();
		$envelop_end = self::getEnvelopEnd();
		$folder = strtolower(trim(( (isset($flt["folder"]) && is_string($flt["folder"]) )?strval($flt["folder"]):null )));
		$action = strtolower(trim(( (isset($flt["action"]) && is_string($flt["action"]) )?strval($flt["action"]):'fetch' )));
		$onlycount = ($action==="count")?true:false;
		if( $uid === null ){
			return $envelop_start . "<person:messages count='0' length='0' offset='0' folder='".$folder."'></person:messages>" . $envelop_end;
		}
		
		$msgs = self::getModel($uid,$flt);
		$msgscount = count($msgs->items);
		$res = $envelop_start;
		$res .= "<person:messages count='".$msgscount."' length='".$msgs->filter->limit."' offset='".$msgs->filter->offset."' orderby='".$msgs->filter->orderBy."'>";
		if( $msgscount === 0 || $onlycount ){
			$res .= "</person:messages>" . $envelop_end;
			return $res;
		}
		$senders = array();
		for($i=0; $i<$msgscount; $i+=1){
			$item = $msgs->items[$i];
			$isread = ( ($item->isread===true)?"true":"false" );
			$dir = "unknown";
			$personid = -1;
			$msg = "<person:message id='" . $item->id . "' isread='" . $isread  . "' sendon='" . $item->senton . "' ";
			if( $uid == $item->receiverid ){
				$msg .= "folder='inbox' >";
				$dir = "from";
				$personid = $item->senderid;
				if( isset($senders[$personid]) == false ){
					$s = $item->getSender();
					$senders[$personid] = " id='" . $s->id . "' cname='" . $s->cname . "' firstname='" . $s->firstname . "' lastname='" . $s->lastname . "' ";
				}
			}else if($uid == $item->senderid){
				$msg .= "folder='outbox' >";
				$dir = "to";
				$personid = $item->receiverid;
				if( isset($senders[$personid]) == false ){
					$s = $item->getReceiver();
					$senders[$personid] = " id='" . $s->id . "' cname='" . $s->cname . "' firstname='" . $s->firstname . "' lastname='" . $s->lastname . "' ";
				}
			}else{
				$msg .= "folder='unknown' ></person:message>";
				$res .= $msg;
				continue;
			}
			
			$msg .= "<message:headers><message:" . $dir . " " . $senders[$personid] . "></message:" . $dir . "></message:headers>";
			$msg .= "<message:content>" . $item->msg . "</message:content>";
			$msg .= "</person:message>";
			$res .= $msg;
		}
		
		$res .= "</person:messages>" . $envelop_end;
		return $res;
	}
}
?>
