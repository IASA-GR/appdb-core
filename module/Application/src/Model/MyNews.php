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

class MyNews {

 protected $_news;
 public $limit;
 public $offset;
 public $filter;
 public $from;
 public $to;
 public $event;

 public function __construct($filter = null)
    {
		if ( $this->filter === null ) {
			$this->filter = new Filter();
			$this->filter->_table = 'news';
		} else {
			$this->filter = $filter;
		}
    }
  
 public function count() {
     return count($this->_news);
 }

 public function refresh() {
    /*
    Query to cluster news by type in time (i.e. do not flood with the same news entry if repeated closely in time)

    1) Define vector space V(news,R) with elements FROM table "news".
    2) Define tensor product W = V x V to be the normed v.s. with ||x|| = t(x1)-t(x2), where x in W, x1,x2 in V, and t the "timespace"
    dimension of V.
    3) Take E=(x,d) to be the 2-tuple of all diagonal elements of subspace W\P_t1(W)P_t2(W) and their norm, where P_t is the projection
    operator in the t dimesion.
    4) Define S={(x,d)|x in E and d < 90000} (90000 is circa one day)
    5) Select all rows from table "news" where their id is not the id of the second tensor product element of any element in S
	 */
	$l = '';
	if ( $this->limit != '' ) $l .= "LIMIT ".$this->limit;
	if ( $this->offset != '' ) $l .= "OFFSET ".$this->offset;
	if ($this->filter->expr() != '') {
	    $where = "AND (".$this->filter->expr().")";
	} else {
	    $where = '';
	}
	$where .= " AND action <> 'delete') AND ( CASE WHEN action = 'update' THEN subjecttype<>'doc' ELSE true END)";
	if ( ( $this->from != '') || ( $this->to != '' ) ) {
	   if ( $this->from != '' ) $from = "'".date("Y-m-d H:i:s", strtotime($this->from))."'"; else $from = '0';
	   if ( $this->to != '' ) $to = "'".date("Y-m-d H:i:s", strtotime($this->to))."'"; else $to = 'EXTRACT(EPOCH FROM NOW())';
	   $where.=" AND (timestamp BETWEEN $from AND $to)";
	}
	if ( $this->event != '' ) $where.=" AND ( action = '".$this->event."')";
	$query = 'SELECT * FROM newsviews as news WHERE (ID NOT IN (SELECT i2 FROM (SELECT DISTINCT t1 as timestamp, si1 as subjectID, st1 as subjectType, a1 as action, i1, i2 FROM (SELECT EXTRACT(EPOCH FROM t1)-EXTRACT(EPOCH FROM t2) as dt,si1,st1,a1,t1,t2,i1,i2 FROM (SELECT n1.timestamp as t1, n1.subjectID as si1, n1.subjectType as st1, n1.action as a1, n1.ID as i1, n2.timestamp as t2, n2.subjectID as si2, n2.subjectType as st2, n2.action as a2, n2.ID as i2 FROM newsviews as n1,newsviews as n2) AS T1 WHERE si1=si2 AND st1=st2 AND a1=a2 AND EXTRACT(EPOCH FROM t1)-EXTRACT(EPOCH FROM t2)>0) AS T2 WHERE dt < 90000 ) AS T3) '.$where.' ORDER BY timestamp DESC '.$l;
	$this->_news = db()->query($query, array())->toArray();
    return $this;
 }

 public function items($i) {
     $e = new MyNew();
     $e->init($this->_news[$i]);
     return $e;
 }

}
