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
 
 class fltTerm {
	public $val;
	public $literal;
	public $field;
	public $table;
	public $excluded;
	public $required;
	public $strict;
	public $numeric;
	public $regex;
	public $fuzzy;
	public $private;
	public $in;

	public function __construct() {
		$this->val = '';
		$this->literal = false;
		$this->field = null;
		$this->table = null;
		$this->excluded = false;
		$this->required = false;
		$this->strict = false;
		$this->regex = false;
		$this->fuzzy = false;
		$this->numeric = null;
		$this->private = false;
	}
}

function explodeFltStr(&$fltstr) {
	$f = $fltstr;
    $r = array();
    $pos1 = 0;  
    $pos2 = 0;  
	
	///////////////////////////////////////////////////////////
	// FIXME: temp fix for obscure bug in dissemination tool //	
	$f = str_replace("”", '"', $f);							 //
	///////////////////////////////////////////////////////////

	// replace spaces inside double quotes with no-break spaces
	// in order to split arguments by spaces later on
	$nbs = " "; // UTF-8 NO-BREAK SPACE;
	$s = $f;
	$instr = false;
	for ($i=0; $i<strlen($s); $i++) {
		if ( substr($s,$i,1) == '"' ) {
			$instr = ! $instr;
			if ( ($instr === false) && (substr($s,$i+1,1) !== " ") && ($i<strlen($s)-1) ) {
				$error = "Syntax error. Expected space sparator or EOL at position ".($i+1).".";
				return $s;
			}
		} elseif ( substr($s,$i,1) == " " ) {
			if ( $instr ) $s = substr($s,0,$i).$nbs.substr($s,$i+1);
		}
	}
	$f = $s;

	$f = preg_replace("/\s+/", " ", $f);
	$r = array_merge($r, explode(" ", $f));
	$a = array();
	foreach ($r as $i) {
		if (!((substr($i,0,1) == '-') || (substr($i,0,1) == '+'))) $a[] = $i;
	}
	foreach($r as $i) {
		if ((substr($i,0,1) == '-') || (substr($i,0,1) == '+')) $a[] = $i;
	}
	$fltstr=trim(implode(" ",$a));
	for($i=0; $i<count($a); $i++) $a[$i] = preg_replace('/"/','',$a[$i]);
	$b = array();
	foreach ($a as $i) if ($i != '') {
		// restore regular spaces in each argument
		$i = str_replace($nbs, " ", $i);
		$x = new fltTerm();
		$predicate = true;
		while ($predicate) {
			if ( substr($i,0,1) === "+" ) {
				$x->required = true;
				$i = substr($i,1);
			} elseif ( substr($i,0,1) === "-" ) {
				$x->excluded = true;
				$i = substr($i,1);
			} elseif ( substr($i,0,1) === ">" ) {
				$x->numeric = "g";
				$i = substr($i,1);
			} elseif ( substr($i,0,1) === "<" ) {
				$x->numeric = "l";
				$i = substr($i,1);
			} elseif ( substr($i,0,1) === "=" ) {
				$x->strict = true;
				$i = substr($i,1);
			} elseif ( substr($i,0,1) === "~" ) {
				$x->regex = true;
				$i = substr($i,1);
			} elseif ( substr($i,0,1) === "&" ) {
				$x->private = true;
				$i = substr($i,1);
			} elseif ( substr($i,0,1) === "$" ) {
				$x->fuzzy = true;
				$i = substr($i,1);
			} elseif ( substr($i,0,1) === "*" ) {
				$x->in = true;
				$i = substr($i,1);
			} else $predicate = false;			
		}
		if ( strpos($i,":") === false ) {
			$x->table = null;
			$x->field = null;
			$x->val = $i;
		} else {
			$xx = explode(":", $i);
			$x->val = $xx[1];
			if ( strpos($xx[0],".") === false ) {
				$x->field = ($xx[0]==''?null:$xx[0]);
			} else {
				$xx = explode(".", $xx[0]);
				$x->table = $xx[0];
				$x->field = ($xx[1]==''?null:$xx[1]);
			}
		}
		$b[] = $x;
	}
	return $b;
}

class FilterParser {
	const NORM_APP = 0;
	const NORM_PPL = 1;
	const NORM_VOS = 2;
	const NORM_DISSEMINATION = 3;
	const NORM_SITE = 4;

	public static function fieldsToXML($flds, $base, $n = 0) {
		$a = explode(" ",$flds);
		sort($a);
		if ($n > 0) array_unshift($a, "any:string");
		$s = '';
		foreach ($a as $i) {
			$end = "/>";
			if ($n === 0) {
                switch ($i) {
                    case("site"):
                        $fltstr = "id:string name:string description:string tier:numeric roc:string subgrid:string supports:string";
                        if ( $base === $i ) {
                            $fltstr = $fltstr." country:complex";
                        }
						$end = '>'.FilterParser::fieldsToXML($fltstr, $base, $n+1).'</filter:field>';
                        break;
                    case("dissemination"):
                        $fltstr = "id:numeric date:string subject:string message:string";
                        if ( $base === "dissemination" ) {
                            $fltstr = $fltstr." sender:complex";
                        }
						$end = '>'.FilterParser::fieldsToXML($fltstr, $base, $n+1).'</filter:field>';
                        break;
					case("application"):
						$fltstr = "id:numeric name:string description:string abstract:string registeredon:datetime lastupdated:datetime tool:boolean rating:numeric tag:string validated:boolean owner:numeric addedby:numeric deleted:boolean releasecount:numeric arch:string os:string language:string status:string phonebook:string license:complex published:boolean hypervisor:string osfamily:string imageformat:string metatype:integer sitecount:integer year:integer month:integer day:integer";
						if ( $base === $i ) {
							$fltstr = $fltstr." person:complex country:complex middleware:complex vo:complex discipline:complex category:complex";
						}
						$end = '>'.FilterParser::fieldsToXML($fltstr, $base, $n+1).'</filter:field>';
						break;
					case("sender"):
					case("person"):
						$fltstr = "id:numeric firstname:string lastname:string name:string registeredon:datetime institute:string activated:boolean lastlogin:datetime lastupdated:datetime nodissemination:boolean contact:string role:string roleid:numeric language:string os:string arch:string phonebook:string license:complex accessgroup:complex";
						if ( $base === $i ) {
							$fltstr = $fltstr." country:complex application:complex middleware:complex vo:complex discipline:complex category:complex";
						}
						$end = '>'.FilterParser::fieldsToXML($fltstr, $base, $n+1).'</filter:field>';
                        break;
                    case("category"):
						$end = '>'.FilterParser::fieldsToXML("id:numeric name:string", $base, $n+1).'</filter:field>';
                        break;
					case("discipline"):
						$end = '>'.FilterParser::fieldsToXML("id:numeric name:string", $base, $n+1).'</filter:field>';
						break;
					case("accessgroup"):
						$end = '>'.FilterParser::fieldsToXML("id:numeric name:string payload:string", $base, $n+1).'</filter:field>';
						break;
					case("license"):
						$end = '>'.FilterParser::fieldsToXML("id:numeric name:string title:string group:string", $base, $n+1).'</filter:field>';
						break;
					case("vo"):
						$fltstr = "id:numeric name:string description:string";
						if ( $base === $i ) {
							$fltstr = $fltstr." country:complex application:complex middleware:complex vo:complex discipline:complex category:complex storetype:string phonebook:string scope:string";
						}
						$end = '>'.FilterParser::fieldsToXML($fltstr, $base, $n+1).'</filter:field>';
						break;
					case("country"):
						$end = '>'.FilterParser::fieldsToXML("id:numeric name:string isocode:string", $base, $n+1).'</filter:field>';
						break;
					case("middleware"):
						$end = '>'.FilterParser::fieldsToXML("id:numeric name:string", $base, $n+1).'</filter:field>';
						break;
					case("any"):
						$end = '>'.FilterParser::fieldsToXML("", $base, $n+1).'</filter:field>';
						break;
				}
			} else {
				// HACK
				if ( $i === "xcountry" ) {
					$i = "countryname:string";
				}
			}
			$ii = explode(":", $i);
			if ( count($ii) > 1 ) {
				$name = $ii[0];
				$type = $ii[1];
			} else {
				$name = $i;
				$type = "complex";
			}
			if ( $name != "" ) $s .= '<filter:field level="'.$n.'" name="'.$name.'" type="'.$type.'"'.$end;
		}
		return $s;
	}

	public static function normalizeFilter($s, $mode, &$error, &$flds = null, $ver = null) {
		if ( is_null($ver) ) {
			$api = Zend_Registry::get("api");
			$ver = $api["latestVersion"];
		}
		switch ($mode) {
			case (FilterParser::NORM_APP):
				$normalizer = new RestAppFilterReflection();
				break;
			case (FilterParser::NORM_PPL):
				$normalizer = new RestPplFilterReflection();
				break;
			case (FilterParser::NORM_VOS):
				$normalizer = new RestVOFilterReflection();
				break;
			case (FilterParser::NORM_DISSEMINATION):
				$normalizer = new RestDisseminationFilterReflection();
				break;
			case (FilterParser::NORM_SITE):
				$normalizer = new RestSiteFilterReflection();
				break;
		}
		$reflection_str = strval($normalizer->get()->finalize());
		$reflection = new DOMDocument();
		$reflection->loadXML($reflection_str, LIBXML_NSCLEAN | LIBXML_COMPACT);
		$help = '';
		$nbs = " "; // UTF-8 NO-BREAK SPACE;
		$ss = '';
		$error = '';
		$s = trim($s);
		// replace escaped double quotes with unicode double quotes
		$s = str_replace('\\"', "”", $s);
		// replace spaces inside double quotes with no-breaking unicode spaces 
		$instr = false;
		for ($i=0; $i<strlen($s); $i++) {
			if ( substr($s,$i,1) == '"' ) {
				$instr = ! $instr;
				if ( ($instr === false) && (substr($s,$i+1,1) !== " ") && ($i<strlen($s)-1) ) {
					$error = "Syntax error. Expected space sparator or EOL at position ".($i+1).".";
					return $s;
				}
			} elseif ( substr($s,$i,1) == " " ) {
				if ( $instr ) $s = substr($s,0,$i).$nbs.substr($s,$i+1);
			}
		}
		if ( $instr ) {
			$error = "Syntax error. Expected closing ` \" ' character before EOL.";
			return $s;
		}
		// replace multiple spaces with single space
		$s = preg_replace('/ +/', ' ', $s);
		// remove single space after ":" which is usually a user mistake
		$s = preg_replace('/: /', ':', $s);
		// split string into arguments per spaces
		$args = explode(" ", $s);
		// analyze each argument
		$argi = 0;
		foreach($args as $s) {
			$op = "";
			$neg = false;
			$required = false;
			$private = false;
			// restore normal spaces
			$s = str_replace($nbs, " ", $s);
			// look for operators (modifiers) at the start of the argument
			$doops = true;
			$ops = '';
			for ($i=0; $i<=strlen($s); $i++) {
				switch (substr($s, $i, 1)) {
					case '-':
						$neg = true;
					case '+':
						$required = true;
						break;
					case '&':
						$private = true;
						break;
					case '$':
					case '=':
					case '~':
					case '<':
					case '>':
					case '*':
						break;
					default:
						$doops = false;
				}
				if ( ! $doops ) {
					$ops = substr($s, 0, $i);
					if ( substr($s, $i) !== false ) {
						$s = substr($s, $i);
					}
					break;
				}
			}
			// now operators, if any, are in the "ops" buffer, the rest of the argument in the "s" buffer 
			// normilize operator ordering
			$ops2 = '';
			if ( (strpos($ops, "-") !== false) /*&& ($argi > 0)*/ ) $ops2 = $ops2."-";	// ignore "+","-" operator at the 1st argument
			if ( (strpos($ops, "+") !== false) /*&& ($argi > 0)*/ && (strpos($ops, "-") === false) ) $ops2 = $ops2."+";	// "-" is stronger than "+"
			if ( (strpos($ops, "<=") !== false) || (strpos($ops, "=<") !== false) ) $ops2 = $ops2."<=";
			elseif ( (strpos($ops, ">=") !== false) || (strpos($ops, "=>") !== false)) $ops2 = $ops2.">=";
			elseif ( (strpos($ops, ">") !== false) ) $ops2 = $ops2.">";
			elseif ( (strpos($ops, "<") !== false) ) $ops2 = $ops2."<";
			elseif ( (strpos($ops, "=") !== false) ) $ops2 = $ops2."=";
			elseif ( (strpos($ops, "*") !== false) ) $ops2 = $ops2."*";
			if ( (strpos($ops, "~") !== false) ) $ops2 = $ops2."~";
			elseif ( (strpos($ops, "$") !== false) ) $ops2 = $ops2."$";
			if ( (strpos($ops, "&") !== false) ) {
				if ( $ops2 !== "&" ) {
					$ops2 = $ops2."&";
				}
			}
			$ops = $ops2;
			// double quotes, if any, should be right after a colon, or at the beginning of the argument
			// return with an error if not
			if ( (strpos($s, '"') !== false) && (strpos($s, ':"') === false) && (strpos($s, '"')>0) ) {
				$error = 'Syntax error. Unexpected character ` " \' at position '.(strlen($ss)+strpos($s, '"')).".";
				return substr($ss." ".$s,1);
			}
			// look for a colon outside of double quotes, and split into property and value if there is one
			$prop = '';
			$p1 = strpos($s, ':');
			$p2 = strpos($s, '"');
			if ( $p2 === false ) $p2 = $p1+1;
			if ( ($p1 < $p2) && ($p2 !== false)) {
				$prop = substr($s, 0, $p1);
				$s = substr($s, $p1+1*($p1!==false));
			}
			// now the property, if any, is in the "prop" buffer, the rest of the argument (i.e. the value) in the "s" buffer
			// look for a dot in the property name, in order to handle references to objects
			$obj = '';
			if ( strpos($prop, ".") !== false ) {
				$obj = substr($prop, 0, strpos($prop, "."));
				$prop = substr($prop, strpos($prop, ".")+1);
			}
			$hackFlagAny = false;
			if ($obj == '') {
				//if ( $prop == '' ) {
				// DISABLE "any.any" as default context; instead, use "SEARCH_TAGET.any"
				if ( false ) {
					$hackFlagAny = true;
					$obj = "any";
					$prop = "any";
				} else {
					if (trim($prop) == "") {
						$prop = "any";
					};
					switch ($mode) {
						case (FilterParser::NORM_APP):
                            switch ($prop) {
								case "person":
								case "discipline":
								case "vo":
								case "country":
								case "middleware":
								case "category":
								case "application":
									$obj = $prop;
									$prop = "any";
									break;
								default:
									$obj = "application";
							}
							break;
						case (FilterParser::NORM_PPL):
							switch ($prop) {
								case "person":
								case "discipline":
								case "vo":
								case "country":
								case "middleware":
								case "category":
								case "application":
									$obj = $prop;
									$prop = "any";
									break;
								default:
									$obj = "person";
							}
							break;
                        case (FilterParser::NORM_VOS):
                            switch ($prop) {
								case "person":
								case "discipline":
								case "vo":
								case "country":
								case "middleware":
								case "category":
								case "application":
                                    $obj = $prop;
                                    $prop = "any";
                                    break;
                                default:
                                    $obj = "vo";
                            }
                            break;
                        case (FilterParser::NORM_DISSEMINATION):
                            switch ($prop) {
								case "sender":
								case "discipline":
								case "vo":
								case "country":
								case "middleware":
								case "category":
								case "application":
								case "dissemination":
                                    $obj = $prop;
                                    $prop = "any";
                                    break;
                                default:
                                    $obj = "dissemination";
                            }
                            break;
                        case (FilterParser::NORM_SITE):
                            switch ($prop) {
								case "country":
								case "discipline":
								case "vo":
								case "middleware":
								case "category":
								case "application":
                                    $obj = $prop;
                                    $prop = "any";
                                    break;
                                default:
                                    $obj = "site";
                            }
                            break;
					}
				}
			}
			if ($prop == $obj) {
				$prop = "any";
			}
			if ( ($obj == "any") && ( $prop != "any" ) ) {
				$error = 'Grammar error. Invalid property ` '.$prop.' \' for specifier ` any \' at keyword '.($argi+1).".";
				return substr($ss." ".$ops.($obj !== "" ? $obj."." : "").$prop.":".$s,1);
			}
			
			//backwards compatibility for "role.id"
			if ( $obj === "role" ) {
				$obj = "person"; 
				if ($prop === "id") {
					$prop = "roleid";
				} else {
					$prop = "role";
				}
			}

			// validate specifiers and properties against API reflection 
			$xp = new DOMXPath($reflection);
			$xpres = $xp->query('//filter:field[@name="'.$obj.'"]');
			$found = false;
			if (!is_null($xpres)) {
				if (count($xpres)>0) $found=true;
			}
			if (!$found) {
				$error = 'Grammar error. Invalid specifier ` '.$obj.' \' at keyword '.($argi+1).".";				
				return substr($ss." ".$ops.($obj !== "" ? $obj."." : "").$prop.":".$s,1);
			} else {
				$found = false;
				$xpres = $xp->query('//filter:field[@name="'.$obj.'"]/filter:field[@name="'.$prop.'"]');
				if (!is_null($xpres)) {
					if ($xpres->length>0) {
						$found=true;
						$isComplex = ($xpres->item(0)->attributes->getNamedItem("type")->nodeValue==="complex"?true:false);
					}
				}
				if (!$found) {
					$error = 'Grammar error. Invalid property ` '.$prop.' \' for specifier ` '.$obj.' \' at keyword '.($argi+1).".";	
					return substr($ss." ".$ops.($obj !== "" ? $obj."." : "").$prop.":".$s,1);
				} else {
					if ( $isComplex ) {
						$obj = $prop;
						$prop = "any";
					}
				}
			}
	
			if ( trim($s) === "" ) $s = '""';
			// append the normalized argument to the output buffer
			if ( $neg ) if (strpos($ops, "-") === false) $ops = "-".$ops;
			// HACK: ommit "any.any"
			if ($hackFlagAny) {
				if ( $ops !== $s ) {
					$ss = $ss." ".$ops.$s;
				} else {
					$ss = $ss." ".$s;
				}
			} else {
				$ss = $ss." ".$ops.($obj !== "" ? $obj."." : "").$prop.":".$s;
			}
			if ( $flds !== null ) {
				$op = str_replace("+", "", $ops);
				$op = str_replace("-", "", $op);
				$flds[] = array("ref" => $obj, "field" => $prop, "value" => $s, "operator" => $op, "required" => $required, "negated" => $neg);
			}
			$argi++;
		}
		// do not return the first char in the "ss" buffer, which should always be a space
		return substr($ss,1);
	}

	private static function filterImplode($f,$type = "application"){
		if($f->field !== null){
		   if($f->table === null){
				if(in_array($f->field, array("tag","tags","keyword"))){
					$f->table=$type;
					$f->field="keywords";
				}else if(in_array($f->field, array("vo","country","middleware","discipline","application"))){
					$f->table = $f->field;
					$f->field = "name";
				}else if($f->field === "person"){
					$f->table = $f->field;
					$f->field = "any";
				} else if ($f->field === "role"){
					$f->table = $f->field;
					$f->field = "description";
				} else {
					$f->table = $type;
				}
			}
		}
		$s =  ( $f->table === null )?"":$f->table .".";
		$s .= ( $f->field === null )?"":$f->field.":";
		$s .= ( $f->val === null )?"":( ( $f->table === null && $f->field === null )?'"'.$f->val.'"':$f->val );
		$pref = "";
		if($f->required){
		   $pref = "+";
		}else if($f->excluded){
		   $pref = "-";
		}
		if($f->strict){
		   $pref .= "=";
		}
		if($f->numeric !== null){
		   switch($f->numeric){
		   case "g":
			   $pref .= ">";
			   break;
		   case "l":
			   $pref .= "<";
			   break;
		   }
		}
		if($f->regex){
		   $pref .= "~";
		}
		if($f->fuzzy){
			$pref .= "$";
		}
		if($f->private){
			$pref .= "&";
		}
		$s = $pref . $s;
		return $s;
	}

	public static function filterNormalization($fltstr,&$flthash=0){
		error_log("WARNING: this function is deprecated, please use FilterParser::normalizeFilter instead");
		if ( $fltstr == '' ) {
			return $fltstr;
		}
		$fltarray = explodeFltStr($fltstr);
		
		$strar = array();
		$tmp = null;

		foreach($fltarray as $f){
			$tmp = self::filterImplode($f);
			$flthash = $flthash + hexdec(md5($tmp));
			$strar[] = $tmp;
		}
		return implode(" ", $strar);
	}

	public static function getApplications($fltstr, $isfuzzy=false) {
		$f = new Default_Model_ApplicationsFilter();
		FilterParser::buildFilter($f, $fltstr, $isfuzzy);
		return $f;
	}

	public static function getPeople($fltstr, $isfuzzy=false) {
		$f = new Default_Model_ResearchersFilter();
		FilterParser::buildFilter($f, $fltstr, $isfuzzy);
		return $f;
	}

	public static function getVOs($fltstr, $isfuzzy=false) {
		$f = new Default_Model_VOsFilter();
		FilterParser::buildFilter($f, $fltstr, $isfuzzy);
		return $f;
    }

	public static function getDissemination($fltstr, $isfuzzy=false) {
		$f = new Default_Model_DisseminationFilter();
		FilterParser::buildFilter($f, $fltstr, $isfuzzy);
		return $f;
	}

	public static function getSites($fltstr, $isfuzzy=false) {
		$f = new Default_Model_SitesFilter();
		FilterParser::buildFilter($f, $fltstr, $isfuzzy);
		return $f;
	}

	public static function buildFilter(&$filter, $fltstr, $isfuzzy=false){
		$fltstr = trim($fltstr);
		if ( substr($fltstr, 0, 1) === "|" ) {
			$fltstr = trim(substr($fltstr, 1));
		}
		if ( substr($fltstr, -1, 1) === "|" ) {
			$fltstr = trim(substr($fltstr, 0, -1));
		}
		$fltstr_parts = explode(" | ", $fltstr);
		$exprs = array();
		foreach($fltstr_parts as $fltstr_part) {
			if ( trim($fltstr_part) != '' ) {
				$filter->_expr = '';
				FilterParser::__buildFilter($filter, $fltstr_part, $isfuzzy);
				$exprs[] = $filter->expr();
			}
		}
		if (count($exprs) === 0) {
			debug_log("buildFilter: null expression");
		} elseif (count($exprs) === 1 ) {
			$filter->_expr = $exprs[0];
			$filter->fltstr = $fltstr_parts[0];
		} else {
			$filter->_expr = $exprs;
			$filter->fltstr = $fltstr_parts;
		}
	}

	public static function __buildFilter(&$filter, $fltstr, $isfuzzy=false){
		$fltstr_orig = $fltstr;
		$globalPrivate = ((substr(trim($fltstr), 0, 2) === "& ") || (trim($fltstr) === "&"));
		switch(get_class($filter)) {
		case "Default_Model_ApplicationsFilter":
			$defaultTable = "application";
			break;
		case "Default_Model_ResearchersFilter":
			$defaultTable = "person";
			break;
		case "Default_Model_VOsFilter":
			$defaultTable = "vo";
            break;
        case "Default_Model_DisseminationFilter":
            $defaultTable = "dissemination";
            break;
        case "Default_Model_SitesFilter":
            $defaultTable = "site";
            break;
		default:
			return false;
		}
		if ( $fltstr != '' ) {
			$fltarray = explodeFltStr($fltstr);
			foreach($fltarray as $fltstr) {
				$chainOp = "OR";
				$innerChainOp = "OR";
				if ($fltstr->numeric !== null) { // there is an explicit numeric comparison operator present
					$expOp = $fltstr->numeric;
					if ($fltstr->strict) {
						$expOp = $expOp."e";
					} else {
						$expOp = $expOp."t";
					}
				} else { // auto comparison, no explicit numeric comparison operator present
					// following line is for backwards compatibility
					// "isfuzzy" argument has been obsoleted
					if ($isfuzzy) $expOp = "soundsLike"; else $expOp = "ilike";
					if ($fltstr->strict) {
						$expOp = "ilike";
						$strict = true;
					} else {
						$strict = false;
					}
					if ($fltstr->regex) {
						$expOp = "matches";
					}
					if ($fltstr->fuzzy) {
						$expOp = "soundsLike";
					}
					if ($fltstr->in) {
						$expOp = "in";
					}
				}
				$private = $fltstr->private || $globalPrivate;
				if ($fltstr->required) {
					$chainOp = "AND";
				} elseif ($fltstr->excluded) {
					$expOp = "not".$expOp;
					$innerChainOp = "AND";
					$chainOp = "AND";
				}
				if ( ($fltstr->field === "any") && ($fltstr->table === "any") ) $fltstr->field = null;
				if ($fltstr->field === null) {
					if (($expOp === 'like') || ($expOp === 'ilike') || ($expOp === 'notlike') || ($expOp === 'notilike')) {
						$fltstr = str_replace("%",'\\\\%',$fltstr->val);
						$fltstr = str_replace("_",'\\\\_',$fltstr);
						if ( ! $strict ) {
							$fltstr = "%".$fltstr."%";
						}
					} else $fltstr = $fltstr->val;
					$ff = array();
					$f = new Default_Model_ApplicationsFilter();
					$f->any->$expOp($fltstr);
					$ff[] = $f;
					if ( $defaultTable !== "site") {
						$f = new Default_Model_LicensesFilter();
						$f->any->$expOp($fltstr);
						$ff[] = $f;
					}
					$f = new Default_Model_VOsFilter();
					$f->any->$expOp($fltstr);
					$ff[] = $f;
					$f = new Default_Model_MiddlewaresFilter();
					$f->any->$expOp($fltstr);
					$ff[] = $f;
					$f = new Default_Model_CountriesFilter();
					$f->any->$expOp($fltstr);
					$ff[] = $f;
					if ( ! $private || $defaultTable === "application" ) {
						$f = new Default_Model_AppCountriesFilter();
						$f->any->$expOp($fltstr);
						$ff[] = $f;
					}
					if ( $defaultTable !== "site") {
						$f = new Default_Model_StatusesFilter();
						$f->any->$expOp($fltstr);
						$ff[] = $f;
					}
					$f = new Default_Model_ArchsFilter();
					$f->any->$expOp($fltstr);
					$ff[] = $f;
					$f = new Default_Model_OSesFilter();
					$f->any->$expOp($fltstr);
					$ff[] = $f;
					$f = new Default_Model_ProgLangsFilter();
					$f->any->$expOp($fltstr);
					$ff[] = $f;
					$f = new Default_Model_DisciplinesFilter();
					$f->any->$expOp($fltstr);
					$ff[] = $f;
					if ( $defaultTable !== "site") {
						$f = new Default_Model_ResearchersFilter();
						$f->any->$expOp($fltstr);
						$ff[] = $f;
					}
					$f = new Default_Model_CategoriesFilter();
					$f->any->$expOp($fltstr);
					$ff[] = $f;
					if ( $defaultTable !== "site") {
						$f = new Default_Model_PositionTypesFilter();
						$f->any->$expOp($fltstr);
						$ff[] = $f;
					}
					if ( $defaultTable !== "site") {
						$f = new Default_Model_ContactsFilter();
						$f->any->$expOp($fltstr);
						$ff[] = $f;
					}
					if ( (! $private || $defaultTable === "application") && ($defaultTable !== "vo") && ($defaultTable !== "site") && ($defaultTable !== "person") ) {
						$f = new Default_Model_HypervisorsFilter();
						$f->name->$expOp($fltstr);
						$ff[] = $f;
					}
					if ( (! $private || $defaultTable === "application" ) && ($defaultTable !== "vo") && ($defaultTable !== "site") && ($defaultTable !== "person") ) {
						$f = new Default_Model_VMIflavoursFilter();
						$f->format->$expOp($fltstr);
						$ff[] = $f;
					}
					if ( /*(! $private && $defaultTable === "application") ||*/ $defaultTable === "site" ) {
						$f = new Default_Model_SitesFilter();
						$f->any->$expOp($fltstr);
						$ff[] = $f;
					}
					$innerFilter = null;
					foreach($ff as $f) {
						if ( $innerFilter === null ) {
							$innerFilter = $f;
						} else {
							$innerFilter = $innerFilter->chain($f, $innerChainOp, $private);
						}
					}
					$filter = $filter->chain($innerFilter, $chainOp, $private);
				} else {
					$countryHack = false;
					$f1 = null;
					$val = $fltstr->val;
					$fld = $fltstr->field;
					if ( $fld === "phonebook" ) {
						$expOp = "equals";
					}
					if (($expOp === 'like') || ($expOp === 'ilike') || ($expOp === 'notlike') || ($expOp === 'notilike')) {
						$val = str_replace("%",'\\\\%',$val);
						$val = str_replace("_",'\\\\_',$val);
						if ( ! $strict ) {
							$val = "%".$val."%";
						}
					}
					if ( $fltstr->table == "any" ) $fltstr->table = null;
					if ($fltstr->table === null) {
						$tbl = $defaultTable;
					} else {
						$tbl = $fltstr->table;
					}
					//complex search fields are mapped to tables
					if ( in_array($fld, array("sender","application","person","vo","country","middleware","discipline","category")) ) {
						$tbl = $fld;
						$fld = "any";
					}
					if ( $tbl === "role" ) {
						$tbl = "person";
						$fld = "role";
					}
					if ( $tbl === "application" && $fld === "countryname" ) {
						$tbl = "country";
						$fld = "name";
						$countryHack = true;
                    }
                    $interval = Zend_Registry::get("app");
                    $interval = $interval["invalid"];
					switch($tbl) {
					case "site":
						switch($fld) {
						case "id":
						case "description":
						case "tier":
						case "subgrid":
						case "roc":
							break;
						case "name":
							break;
						case "phonebook":
							$f1 = false;
							$f2 = new Default_Model_SitesFilter();
							if ( (strtolower($val) >= "a") && (strtolower($val) <= "z") ) {
								$val = strtolower($val);
								if (strtolower($val) < "z") {
									$f2->name->ge($val, false)->and($f2->name->lt("chr(ascii('$val')+1)", false, false));
								} else {
									$f2->name->ge($val, false)->and($f2->name->lt("'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz'", false, false));
								}
							} elseif ( $val === "0-9" ) {
								$f2->name->ge("'0'", false, false)->and($f2->name->le("'9'", false, false));
							} elseif ( $val === "#" ) {
								$fld = "name";
								$f2->$fld->matches("^[^A-Za-z0-9].+");
							} else {
								continue;
							}							
							$filter->chain($f2, $chainOp, $private);
							break;
						case "supports":
							$f1 = false;
							$f2 = new Default_Model_SitesFilter();
							if (is_numeric(trim(str_replace("%", '', $val)))) {
								$supports = trim(str_replace("%", '', $val));
							} else {
								switch (strtolower($supports)) {
								case "occi":
									$supports = 1;
									break;
								default:
									$supports = 0;
									break;
								}
							}
							switch (intval($supports)) {
							case 1:
								$f2->id->in("(SELECT site_supports('occi'))",false,false);
								break;
							case 0:
							default:
								$f2->id->notin("(SELECT site_supports('occi'))",false,false);
								break;
							}
							$filter->chain($f2, $chainOp, $private);
							break;
						case "hasinstances":
							$f1 = false;
							$f2 = new Default_Model_SitesFilter();
							if (is_numeric(trim(str_replace("%", '', $val)))) {
								$siteinstances = trim(str_replace("%", '', $val));
							} else {
								switch (strtolower($siteinstances)) {
								case "occi":
									$siteinstances = 1;
									break;
								default:
									$siteinstances = 0;
									break;
								}
							}
							switch (intval($siteinstances)) {
							case 1:
								$f2->id->in("(SELECT site_instances('occi'))",false,false);
								break;
							case 0:
							default:
								$f2->id->notin("(SELECT site_instances('occi'))",false,false);
								break;
							}
							$filter->chain($f2, $chainOp, $private);
							break;
						}
						if ($f1 === null) $f1 = new Default_Model_SitesFilter();
						break;
					case "application":
						switch($fld) {
						case "sitecount":
							$fld = "(SELECT vappliance_site_count(###THETABLE###.id))";
							break;
						case "published":
							$val = str_replace("%", "", $val);
							if (filter_var($val, FILTER_VALIDATE_BOOLEAN)) {
								$f1 = false;
								$f2 = new Default_Model_VAversionsFilter();
								$f2->published->equals("true")->and($f2->archived->equals(false))->and($f2->enabled->equals(true));
								$filter->chain($f2, $chainOp, $private);
							} else {
								$f1 = new Default_Model_VAversionsFilter();
							}
							break;
						case "relatedto":
							$f1 = false;
							$f2 = new Default_Model_ApplicationsFilter();
							$val = str_replace("%", "", $val);
							$f2->id->in("(SELECT (app).id FROM related_apps(" . $val . "))", false, false);
							$filter->chain($f2, $chainOp, $private);
							break;
						case "phonebook":
							$f1 = false;
							$f2 = new Default_Model_ApplicationsFilter();
							if ( (strtolower($val) >= "a") && (strtolower($val) <= "z") ) {
								$val = strtolower($val);
								if (strtolower($val) < "z") {
									$f2->name->ge($val, false)->and($f2->name->lt("chr(ascii('$val')+1)", false, false));
								} else {
									$f2->name->ge($val, false)->and($f2->name->lt("'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz'", false, false));
								}
							} elseif ( $val === "0-9" ) {
								$f2->name->ge("'0'", false, false)->and($f2->name->le("'9'", false, false));
							} elseif ( $val === "#" ) {
								$fld = "SUBSTRING(applications.name, 1, 1)";
								$f3 = new Default_Model_ApplicationsFilter();
								// NOTE: max name length is 50 characters
								$f2->$fld->lt("'a'", false, false)->or($f2->$fld->gt("'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz'", false, false));
								$f3->$fld->lt("'0'", false, false)->or($f3->$fld->gt("'99999999999999999999999999999999999999999999999999'", false, false));
								$f2->chain($f3, "AND", $private);
							} else {
								continue;
							}							
							$filter->chain($f2, $chainOp, $private);
							break;
						case "status":
							if (is_numeric(trim(str_replace("%", '', $val)))) {
								$fld = "id";
							} else {
								$fld = "name";
							}
							$tbl = "statuses";
							$f1 = new Default_Model_StatusesFilter();
							break;
						case "osfamily":
							if (is_numeric(trim(str_replace("%", '', $val)))) {
								$fld = "id";
							} else {
								$fld = "name";
							}
							$tbl = "os_families";
							$f1 = new Default_Model_OSFamiliesFilter();
							break;
						case "imageformat":
							$tbl = "vmiflavours";
							$fld = "format";
							$f1 = new Default_Model_VMIflavoursFilter();
							break;
						case "hypervisor":
							if (is_numeric(trim(str_replace("%", '', $val)))) {
								$fld = "id";
							} else {
								$fld = "name";
							}
							$tbl = "hypervisors";
							$f1 = new Default_Model_HypervisorsFilter();
							break;
						case "os":
							if (is_numeric(trim(str_replace("%", '', $val)))) {
								$fld = "id";
							} else {
								$fld = "name";
							}
							$tbl = "oses";
							$f1 = new Default_Model_OSesFilter();
							break;
						case "arch":
							if (is_numeric(trim(str_replace("%", '', $val)))) {
								$fld = "id";
							} else {
								$fld = "name";
							}
							$tbl = "archs";
							$f1 = new Default_Model_ArchsFilter();
							break;
						case "language":
							if (is_numeric(trim(str_replace("%", '', $val)))) {
								$fld = "id";
							} else {
								$fld = "name";
							}
							$tbl = "proglangs";
							$f1 = new Default_Model_ProgLangsFilter();
							break;
						case "releasecount":
							$fld="relcount";
							$f1 = new Default_Model_AppReleaseCountFilter();
							break;
						case "validated":
							if (preg_match('/^[0-9]+ (month|year)s{0,1}$/', trim(str_replace('%', '', $val)))) {
								$fld = "lastupdated BETWEEN NOW() - INTERVAL '" . str_replace('%', '', $val) . "' AND NOW()";
								$val = "true";
							} else {
								$fld = "lastupdated BETWEEN NOW() - INTERVAL '".$interval."' AND NOW()";
							};
							break;
						case "year":
							$fld="EXTRACT(YEAR FROM dateadded)";
							break;
						case "month":
							$fld="EXTRACT(MONTH FROM dateadded)";
							break;
						case "day":
							$fld="EXTRACT(DAY FROM dateadded)";
							break;
						case "registeredon":
						case "date":
							$fld = "dateadded";
							break;
						case "tag":
						case "tags":
						case "keyword":
							$fld = "keywords";
							break;
						case "metatype":
							$f1 = new Default_Model_ApplicationsFilter();
							$f1->metatype->numequals($val);
							break;
						}
						if ($f1 === null) $f1 = new Default_Model_ApplicationsFilter();
						break;
					case "vo":
						$f1 = null;
						switch ($fld) {
						case "storetype":
							$f1 = false;
							$f2 = new Default_Model_ApplicationsFilter();
							if (is_numeric(trim(str_replace("%", '', $val)))) {
								$storetype = trim(str_replace("%", '', $val));
							} else {
								switch (strtolower($storetype)) {
								case "application":
									$storetype = 1;
									break;
								case "virtual appliance":
									$storetype = 2;
									break;
								case "software appliance":
									$storetype = 3;
									break;
								default:
									$storetype = 0;
									break;
								}
							}
							switch ($storetype) {
							case 1:
								$f2->metatype->numequals(0);
								break;
							case 2:
								$f2->metatype->numequals(1);
								break;
							case 3:
								$f2->metatype->numequals(2);
								break;
							default:
								$f2 = false;
								break;
							}
							if ($f2 !== false) {
								$filter->chain($f2, $chainOp, $private);
							}
							break;
						case "phonebook":
							$f1 = false;
							$f2 = new Default_Model_VOsFilter();
							if ( (strtolower($val) >= "a") && (strtolower($val) <= "z") ) {
								$val = strtolower($val);
								if (strtolower($val) < "z") {
									$f2->name->ge($val, false)->and($f2->name->lt("chr(ascii('$val')+1)", false, false));
								} else {
									$f2->name->ge($val, false)->and($f2->name->lt("'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz'", false, false));
								}
							} elseif ( $val === "0-9" ) {
								$f2->name->ge("'0'", false, false)->and($f2->name->le("'9'", false, false));
							} elseif ( $val === "#" ) {
								$fld = "name";
								$f2->$fld->matches("^[^A-Za-z0-9].+");
							} else {
								continue;
							}							
							$filter->chain($f2, $chainOp, $private);
						}
						if ($f1 === null) $f1 = new Default_Model_VOsFilter();
						break;
					case "country":
						if ( ! $private || $defaultTable === "application" ) {
							$f1 = false;
							$f2 = new Default_Model_CountriesFilter();
							$f2->$fld->$expOp($val);
							$f3 = new Default_Model_AppCountriesFilter();
							$f3->$fld->$expOp($val);
							$filter->chain($f2->chain($f3, "OR", $private), $chainOp, $private);
						} else {
							$f1 = new Default_Model_CountriesFilter();
						}
					case "countryxxx":
						if (( $defaultTable === "site" ) || ( $defaultTable === "person" )) {
							if ( $countryHack ) {
								$f1 = new Default_Model_CountriesFilter();
								$filter->chain($f1,$chainOp);
								$f1 = new Default_Model_AppCountriesFilter();
							} else {
								$f1 = new Default_Model_CountriesFilter();
							}
						} else {
							$f1 = new Default_Model_AppCountriesFilter();
						}
						break;
					case "middleware":
						$f1 = new Default_Model_MiddlewaresFilter();
						break;
					case "discipline":
						$f1 = new Default_Model_DisciplinesFilter();
						break;
					case "category":
						$f1 = new Default_Model_CategoriesFilter();
						break;
					case "accessgroup":
						switch($fld) {
							case "name":
							case "id":
								$f2 = new Default_Model_ActorGroupsFilter();
								$f2->$fld->$expOp($val);
								$filter->chain($f2, $chainOp, $private);
								break;
							case "payload":
								$f2 = new Default_Model_ActorGroupMembersFilter();
								$f2->$fld->$expOp($val);
								$filter->chain($f2, $chainOp, $private);
								break;
						}
						$f1 = false;
						break;
					case "license":
						switch ($fld) {
							case "id":
								$f1 = new Default_Model_LicensesFilter();
								break;
							default:
								$f2 = new Default_Model_LicensesFilter();
								$f2->$fld->$expOp($val);
								$f3 = new Default_Model_AppLicensesFilter();
								$f3->$fld->$expOp($val);
								$filter->chain($f2->chain($f3, "OR", $private), $chainOp, $private);
								$f1 = false;
						}
						break;
                    case "sender":
					case "person":
						switch($fld) {
						case "phonebook":
							$f1 = false;
							$f2 = new Default_Model_ResearchersFilter();
							if ( (strtolower($val) >= "a") && (strtolower($val) <= "z") ) {
								$val = strtolower($val);
								if (strtolower($val) < "z") {
									$f2->name->ge($val, false)->and($f2->name->lt("chr(ascii('$val')+1)", false, false));
								} else {
									$f2->name->ge($val, false)->and($f2->name->lt("'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz'", false, false));
								}
							} elseif ( $val === "0-9" ) {
								$f2->name->ge("'0'", false, false)->and($f2->name->le("'9'", false, false));
							} elseif ( $val === "#" ) {
								$fld = "name";
								$f2->$fld->matches("^[^A-Za-z0-9].+");
							} else {
								continue;
							}							
							$filter->chain($f2, $chainOp, $private);
							break;
						case "language":
							if (is_numeric(trim(str_replace("%", '', $val)))) {
								$fld = "id";
							} else {
								$fld = "name";
							}
							$tbl = "proglangs";
							$f1 = new Default_Model_ProgLangsFilter();
							break;
						case "os":
							if (is_numeric(trim(str_replace("%", '', $val)))) {
								$fld = "id";
							} else {
								$fld = "name";
							}
							$tbl = "oses";
							$f1 = new Default_Model_OSesFilter();
							break;
						case "arch":
							if (is_numeric(trim(str_replace("%", '', $val)))) {
								$fld = "id";
							} else {
								$fld = "name";
							}
							$tbl = "archs";
							$f1 = new Default_Model_ArchsFilter();
							break;
						case "year":
							$fld="EXTRACT(YEAR FROM dateinclusion)";
							break;
						case "month":
							$fld="EXTRACT(MONTH FROM dateinclusion)";
							break;
						case "day":
							$fld="EXTRACT(DAY FROM dateinclusion)";
							break;
						case "registeredon":
						case "date":
							$fld = "dateinclusion";
							break;
						case "institute":
							$fld = "institution";
							break;
						case "role":
							$f1 = new Default_Model_PositionTypesFilter();
							$fld = "any";
							break;
						case "roleid":
							$f1 = new Default_Model_PositionTypesFilter();
							$fld = "id";
							break;
						case "contact":
							$f1 = new Default_Model_ContactsFilter();
							$fld = "data";
							break;
						}
						if ($f1 === null) $f1 = new Default_Model_ResearchersFilter();
						break;
					}
					if (($f1 !== null) && ($f1 !== false)) {
						if ( isset($strict) && $strict && is_numeric($val) && (($f1->_fieldTypes[$fld] === "integer") || ($f1->_fieldTypes[$fld] === "float")) && (($expOp === 'like') || ($expOp === 'ilike') || ($expOp === 'notlike') || ($expOp === 'notilike')) ) {
							if (($expOp === "notlike") || ($expOp === "notilike")) {
								$expOp = "notnumequals";
							} else {
								$expOp = "numequals";
							}
						}
						if ((($expOp === "in") || ($expOp === "notin")) && ($val === "va_categories()")) {
							$f1->$fld->$expOp("(SELECT $val)", true, false);
						} else {
							$f1->$fld->$expOp($val);
						}
						$filter->chain($f1, $chainOp, $private);
					}
				}
			}
		}
		$filter->fltstr = $fltstr_orig;
		return true;
	}
}

function validateFilterActionHelper($flt, $type, $ver = null) {
	if ( is_null($ver) ) {
		$api = Zend_Registry::get("api");
		$ver = $api["latestVersion"];
	}
	$origFlt = $flt;
	$error = '';
	$stype = '';
	$flds = array();
	$flt = FilterParser::normalizeFilter($flt, $type, $error, $flds, $ver);
	switch ($type) {
		case FilterParser::NORM_APP:
			$stype="application";
			break;
		case FilterParser::NORM_PPL:
			$stype="person";
			break;
		case FilterParser::NORM_VOS:
			$stype="vo";
			break;
		case FilterParser::NORM_DISSEMINATION:
			$stype="dissemination";
			break;
		case FilterParser::NORM_SITE:
			$stype="site";
			break;
	}
	$s = '<'.$stype.':filter xmlns:filter="http://appdb.egi.eu/api/'.$ver.'/filter" xmlns:'.$stype.'="http://appdb.egi.eu/api/'.$ver.'/'.$stype.'">';
	if ( $error != '' ) {
		$s .= '<filter:error>';
		$s .= base64_encode($error);
		$s .= '</filter:error>';
	}
	$s .= '<filter:originalForm>';
	$s .= base64_encode($origFlt);
	$s .= '</filter:originalForm>';
	$s .= '<filter:normalForm>';
	$s .= base64_encode($flt);
	$s .= '</filter:normalForm>';
	$ref = array();
	foreach ($flds as $f) {
		if ( $f["value"] != "" ) {
			if (! isset($ref[$f["ref"]]) ) $ref[$f["ref"]] = '<filter:field level="0" name="'.$f["ref"].'">';
			$ref[$f["ref"]] .= '<filter:field level="1" name="'.$f["field"].'" '.
				($f["operator"] == '' ? '' : 'operator="'.htmlentities($f["operator"]).'" ').
				($f["required"] == false ? '' : 'required="true" ').
				($f["negated"] == false ? '' : 'negated="true" ').
				'>'.base64_encode($f["value"]).'</filter:field>';
		}
	}
	foreach ($ref as $r) {
		$r .= '</filter:field>';
		$s .= $r;
	}
	$s .= '</'.$stype.':filter>';

	return $s;
}
?>
