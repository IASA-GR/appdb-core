 <?php
 $search = strtolower((isset(GadgetRequest::GetRequest()->ViewParameters["search"]))?GadgetRequest::GetRequest()->ViewParameters["search"]:"true");
 if($search==="true"){
     $search = true;
 }else{
     $search=false;
 }
function getHierarchyEntry($entry, $data,$depth = 0){
	$id = $entry->attr("id");
	$val = ("".$entry);
	$pid = $entry->attr("parentid");
	$res = array("id"=>$id, "value"=> $val, "parentid"=> $pid, "depth"=>$depth, "children"=>array());
	foreach( $data as $d ){
		$cpid = $d->attr("parentid");
		if( $cpid == $id ){
			$res["children"][] = getHierarchyEntry($d,$data,($depth+1));
		}
	}
	return $res;
}
function getFlatHierarchy($entry){
	$id = $entry["id"];
	$val = $entry["value"];
	$indentval = $val;
	$pid = $entry["parentid"];
	$depth = $entry["depth"];
	$children = $entry["children"];
	$res = array();
	
	if($depth > 0){
		$indent = "";
		for($i=0; $i<$depth; $i+=1){
			$indent.="?";
		}
		$indentval = $indent . $val;
	}
	$res[] = array("id"=>$id, "value"=> $val, "parentid"=> $pid, "depth"=>$depth, "indentvalue"=>$indentval );
	if( is_array($children) && count($children) > 0 ){
		foreach($children as $e){
			if( $e ){
				$newres = getFlatHierarchy($e);
				foreach($newres as $n){
					$res[]= $n;
				}
			}
		}
	}
	return $res;
}
function getHierarchyValues($data){
	$entries = array();
	$res = array();
	foreach($data as $d){
		if( !$d->attr("parentid") ){
			$entries[] = getHierarchyEntry($d, $data);
		}
	}
	foreach($entries as $e){
		$newres = getFlatHierarchy($e);
		foreach($newres as $n){
			$res[]= $n;
		}
	}
	return $res;
}	
 ?>
<table style="table-layout: fixed;width:100%;" cellSpacing="0" cellPadding="0" border="0" align="center" width="100%">
    <tbody>
        <tr align="left" style="width:100%;">
            <td style="width:60px;padding-right:30px;">
                <a href="http://www.egi.eu" target="_blank" style="text-decoration: none;border-style:none;display:block;color:white;overflow:hidden;">
                <img src="http://appdb.egi.eu/images/EGI-logo_small2.png" width="40px" height="20px" alt="www.egi.eu" style="text-decoration: none;border-style: none;color:white;" />
                </a>
            </td>
            <td align="center" style="width:75%;" >
                
                <div style="width:100%;">
                    <center style="width:100%">
                <?php $title = @GadgetRequest::GetRequest()->ViewParameters["title"];
                      if(isset ($title)){
                          echo $title;
                      }else{
                          echo "";
                      }
                      ?>
                        </center>
                </div>
                  
            </td>
            <td align="right" style="min-width:75px;float:right;">
                <?php if($search===true){ ?>
                <a id="searchlink" onclick="gadgets.appdb.applications.toggleSearch();" style="font-size: x-small;text-decoration: none;cursor:pointer;vertical-align: middle;padding-right:5px;">
                    <img src="/gadgets/resources/skins/default/images/search.png" alt="Search" title="Search applications database" width="20px" height="20px" />
                </a>
                <a id="clearquerylink" onclick="gadgets.appdb.applications.revertToBaseQuery();" style="font-size: x-small;text-decoration: none;cursor:pointer;vertical-align: middle;padding-right:5px;display:none;">
                    <img src="/gadgets/resources/skins/default/images/undo.png" alt="Clear" title="Clear search and refresh items" width="20px" height="20px" />
                </a>
                <?php } ?>
                <a id="helplink" onclick="gadgets.appdb.applications.showHelp(this);" style="font-size: x-small;text-decoration: none;cursor:pointer;vertical-align: middle;padding-right:5px;" href="#">
                    <img title="Click for help" alt="Help"  width="20px" height="20px" border="0" src="/gadgets/resources/skins/default/images/help.png"/>
                </a>
            </td>
        </tr>
        <?php
            if($search===true){
        ?>
        <tr class="searchPanel">
            <td colspan="3">
                <table width="80%" align="center" >
                    <tr class="inputrow">
                        <td class="searchfieldcell">
                            Name
                        </td>
                        <td class="searchvaluecell">
                            <input id="searchName" type="text" value="" class="textbox"/>
                        </td>
                        <td class="searchfieldcell">
                            Country
                        </td>
                        <td class="searchvaluecell">
                            <?php $r = $this->internalCall("regional",array(),"countries"); ?>
                        </td>
                    </tr>
                    <tr class="inputrow">
                        <td class="searchfieldcell">
                            Description
                        </td>
                        <td class="searchvaluecell">
                            <input id="searchDescription" type="text" value="" class="textbox"/>
                        </td>
                        <td class="searchfieldcell">
                            Discipline
                        </td>
                        <td class="searchvaluecell">
                            <?php $r = $this->internalCall("disciplines",array(),"disciplines"); ?>
                        </td>
                    </tr>
                    <tr class="inputrow">
                        <td class="searchfieldcell">
                            Abstract
                        </td>
                        <td class="searchvaluecell">
                            <input id="searchAbstract" type="text" value="" class="textbox"/>
                        </td>
                        <td class="searchfieldcell">
							Category
						</td>
						<td class="searchvaluecell">
							<?php $r = $this->internalCall("categories",array(),"categories"); ?>
						</td>
                    </tr>
                    <tr class="inputrow">
                        <td class="searchfieldcell">
                               VO
                        </td>
                        <td class="searchvaluecell">
                            <?php $r = $this->internalCall("vos",array(),"vos"); ?>
                        </td>
						<td class="searchfieldcell">
							Tag
						</td>
						<td class="searchvaluecell">
							<?php $r = $this->internalCall("tags",array(),"tags"); ?>
						</td>
                    </tr>
					<tr class="inputrow">
						
						
					</tr>
                    <tr style="height:5px;"><td colspan="2"></td></tr>
                    <tr class="inputrow" >
                        <td align="left" >
                            <a class="button" onclick="gadgets.appdb.applications.clearSearch()" >clear</a>
                        </td>
                        <td colspan="3">
                            <span style="float:right">
                                <a class="button" onclick="gadgets.appdb.applications.search()" id="seachbutton">search</a>
                                <a class="button" onclick="gadgets.appdb.applications.toggleSearch()" >close</a>
                            </span>
                        </td>
                    </tr>
                     <tr style="height:5px;"><td colspan="2"></td></tr>
                </table>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>