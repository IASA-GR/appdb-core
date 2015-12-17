  <?php $v = $this->application;
				$id = $v->attr("id");
				$categories = $v->category();
				$istool = false;
				$contacts = $v->contact();
				$contactCount=count($contacts);
				$pubs = $v->publication();
				$applogo = $v->logo;
				if(is_null($applogo)){
				  if($istool==false){
					$applogo =  "http://".$_SERVER['APPLICATION_UI_HOSTNAME']."/images/app.png";
				  }else{
					  $applogo =  "http://".$_SERVER['APPLICATION_UI_HOSTNAME']."/images/tool.png";
				  }
				}
				$personimg =  "http://".$_SERVER['APPLICATION_UI_HOSTNAME']."/images/person.png";
				$cpubs = 0;
				foreach($pubs as $pp){
				 if(strval($pp->title)===''){
					 continue;
				 }
				 $cpubs = $cpubs+1;
				}
     ?>
<div class="backButton" onmouseover="$(this).parent().addClass('backHover');" onmouseout="$(this).parent().removeClass('backHover');" onclick="gadgets.utils.ui.ApplicationDetails.hide('<?php echo $id; ?>');">
    <img   src="/gadgets/resources/skins/default/images/back.png" width="20px" height="20px" alt="back" title="Back to list"/>
</div>
<div class="applicationDetails">
    <table style="padding:0px;height:100%;">
        <tbody>
            <tr style="height: 100%;">
                <td  class="detailsBack" onmouseover="gadgets.utils.ui.ApplicationDetails.backOver(this);" onmouseout="gadgets.utils.ui.ApplicationDetails.backOut(this);" onclick="gadgets.utils.ui.ApplicationDetails.hide('<?php echo $id; ?>');" title="Back to list">
                    
                </td>
                <td class="detailsRight" style="vertical-align: top;">
                    <table style="position:relative;display:block;top:0px;" >
                        <thead>
                            <tr>
                                <td  colspan="2"></td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr valign="top">
                                <td align="left" valign="top" >
                                     <a href="<?php echo $v->permalink; ?>" target="_blank" >
                                            <div class="listcolimg">
                                                <img src="<?php echo$applogo; ?>" alt="logo"  />
                                            </div>
                                        </a>
                                </td>
                                <td >
                                    <table >
                                        <tbody>
                                            <tr>
                                                <td class="fieldcell">
                                                    Name:
                                                </td>
                                                <td class="fieldvalue">
                                                    <?php echo $v->name; ?>
                                                    <span style="padding-left:5px;">(<a target="_blank" href="<?php echo $v->permalink; ?>" class="linkButton"  title="Click to open the software details on the AppDB portal, in a seperate window ">AppDB permalink</a>)</span>
                                                </td>
                                                </tr>
                                            <tr>
                                                <td class="fieldcell">
                                                    Category:
                                                </td>
                                                <td class="fieldvalue">
													<?php $vds = $v->category();$vdscount = count($vds);
													for($i=0; $i<$vdscount; $i+=1){
														$vdstr = ''.$vds[$i];
														if(strlen($vdstr) > 25){
															echo "<span title='".$vdstr."'>";
															echo substr($vdstr, 0, 22) . "...";

														} else {
															echo "<span>" . $vdstr;
														}
														echo "</span> ";
														if($i<$vdscount-1){ echo " <b> | </b>" ; }
														}?>
                                                </td>
                                            </tr>
                                            <?php if($istool == false) { ?>
											<tr>
												<td class="fieldcell">
													Discipline:
												</td>
												<td class="valuecell">
												   <?php $vds = $v->discipline();$vdscount = count($vds);
													for($i=0; $i<$vdscount; $i+=1){
														$vdstr = ''.$vds[$i];
														if(strlen($vdstr) > 25){
															echo "<span title='".$vdstr."'>";
															echo substr($vdstr, 0, 22) . "...";

														} else {
															echo "<span>" . $vdstr;
														}
														echo "</span> ";
														if($i<$vdscount-1){ echo " <b> | </b>" ; }
														}?>
												</td>
											</tr>
											<?php } ?>
											<?php $subd = $v->subdiscipline(); $subdcount = count($subd);
											if($istool==false && $subdcount>0 && strlen($subd[0])>0){?>
											<tr>
												<td class="fieldcell">
													Subdiscipline:
												</td>
												<td class="valuecell">
													 <?php
													for($i=0; $i<$subdcount; $i+=1){
														$subdstr = ''.$subd[$i];
														if(strlen($subdstr) > 25){
															echo "<span title='".$subdstr."'>";
															echo substr($subdstr, 0, 22) . "...";

														} else {
															echo "<span>" . $subdstr;
														}
														echo "</span> ";
														if($i<$subdcount-1){ echo " <b> | </b>" ; }
														}?>
												</td>
											</tr>
											<?php } ?>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr style="padding:0px;margin:0px;"><td colspan="2" style="padding:5px;"></td></tr>
                            <tr>
                                <td class="fieldcell"></td>
                                <td class="fieldvalue" >
                                    <div style="overflow:auto;">
                                        <ul  style="list-style: none;padding:0px;margin: 0px;vertical-align: top;float:left">
                                            <?php
                                                $links = $v->url();
                                                foreach($links as $l){
                                                    $url = "".$l;
                                                    $urltype = $l->attr("type");
                                                    if($url==="http://")continue;
                                                    ?>
                                                    <li style="float:left;padding-right:10px;">
                                                            <span><a href="<?php echo $url; ?>" target="_blank" class="hidetext"><?php echo $urltype;?> </a></span>
                                                    </li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <tr ><td colspan="2" style="padding:15px;"><a class="toggleButton" onclick="gadgets.utils.ui.ApplicationDetails.toggleAbstract(<?php echo $id; ?>)"><span style="width:100%">Abstract</span></a></td></tr>
                            <tr >
                                <td colspan="2" class="fieldvalue" style="padding-left:15px;padding-right:5px;">
                                    <div id="appDetailAbstract_<?php echo $id; ?>" style="width:100%;height: 100px;overflow:hidden;" onmouseover="$(this).css('overflow','auto');" onmouseout="$(this).css('overflow','hidden');">
                                        <div style="width:100%">
                                            <?php echo $v->abstract; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr><td colspan="2" ><div style="padding:15px;"> </div></td></tr>
                            <tr>
                                <td class="fieldcell"  style="padding-left:15px;">
                                    Country:
                                </td>
                                <td class="fieldvalue" style="width:100%">
                                    <div style="width:100%;overflow:auto;">
                                        <?php $countries = $v->country();
                                        foreach($countries as $counts){ ?>
                                            <span style="display:inline-block;padding-right: 3px;">
                                                <?php $iso2 = $counts->attr("isocode");
                                                                if(empty($iso2)==false){
                                                                $double2 = explode("/", $iso2);
                                                                if(count($double2)>1){
                                                                    $iso2 = strtolower($double2[0]).".png";
                                                                    $doubleiso2 = strtolower($double2[1]).".png";
                                                                }else{
                                                                    $iso2 = strtolower($iso2).".png";
                                                                }
                                                          ?>
                                                    <img src="http://appdb.egi.eu/images/flags/<?php echo $iso2; ?>" alt="<?php echo $iso2; ?>" width="22" height="16"/>
                                                    <?php if(isset($doubleiso2)){?><img alt="" src="http://appdb.egi.eu/images/flags/<?php echo $doubleiso2; ?>"  width="22" height="16" style="display:inline;padding-left:3px;"/><?php $doubleiso2=null;$double2=array();} ?>
                                                    <?php }?>
                                               
                                                    <?php echo $counts; ?>
                                                
                                            </span>
                                        <?php }?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="fieldcell"  style="padding-left:15px;"   >
                                    VOs:
                                </td>
                                <td class="fielvalue">
                                    <div style="width:100%;overflow:auto;">
                                        <?php $vos = $v->vo();
                                        foreach($vos as $vo){?>
                                            <span ><a href="http://appdb.egi.eu/?p=<?php echo base64_encode("/vo/details?id=".$vo->attr("name"));?>" target="_blank" class="linkButton"><?php echo $vo->attr("name"); ?></a></span>
                                       <?php }?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="fieldcell"  style="padding-left:15px;">
                                    Middlewares:
                                </td>
                                <td class="fieldvalue">
                                    <div style="width:100%;overflow:auto;">
                                        <?php $mids = $v->middleware();
                                        foreach($mids as $mw){?>
                                            <span class="linkButton"><?php echo $mw; ?></span>
                                       <?php }?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="fieldcell"  style="padding-left:15px;">
                                    Status:
                                </td>
                                <td class="fieldvalue">
                                    <?php echo $v->status; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="fieldcell" style="padding-left:15px">
                                    Added On:
                                </td>
                                <td class="fieldvalue">
                                    <?php $ao = $v->addedOn;
                                        if(isset($ao)){
                                           $ao = explode("T",$ao);
                                           echo $ao[0] . " by " ?><a target="_blank" class="linkButton" href="<?php echo $v->owner->permalink; ?>"><?php echo $v->owner->firstname . " " . $v->owner->lastname; ?></a><?php ;
                                        }else{
                                            echo "-";
                                        }
                                    ?>
                                </td>
                            </tr>
                             <tr><td colspan="2" style="padding:15px;"><a class="toggleButton" onclick="gadgets.utils.ui.ApplicationDetails.toggleContacts(<?php echo $id; ?>);"><span style="width:100%">Contacts<?php echo ' ['. $contactCount .']';?></span></a></td></tr>
                            <tr>
                                <td class="fieldvalue" colspan="2" style="padding-left:15px;">
                                    <?php
                                        if($contactCount==0){?>
                                            <span><i>The software has no contacts</i></span>
                                    <?php }else{?>
                                            <div id="appDetailContact_<?php echo $id; ?>" style="height: 72px;overflow:hidden;" onmouseover="$(this).css('overflow','auto');" onmouseout="$(this).css('overflow','hidden');">
                                                 <?php
                                                        foreach($contacts as $cnt){
                                                            $cdata = array();
                                                            $cdata["name"] = $cnt->firstname . " " .$cnt->lastname;
                                                            $cdata["institute"] = "".$cnt->institute;
                                                            if($cnt->role!==null){
                                                                $cdata["role"] ="". $cnt->role->attr("type");
                                                            }
                                                            $cdata["permalink"] = "".$cnt->permalink;
                                                            $cdata["image"] = "".((is_null($cnt->image))?$personimg:$cnt->image);
                                                             $cdata["country"] ="". $cnt->country;
                                                             $cou = $cnt->country;
                                                             $iso = $cou->attr("isocode");
                                                             $double = array();
                                                             $fullname = $cdata["name"];
                                                             if(strlen($fullname)>18){
                                                                 $fullname=substr($fullname, 0, 17).'...';
                                                             }
                                                             if(strlen($cdata["institute"])>23){
                                                                 $cdata["institute"]=substr($cdata["institute"], 0, 22).'...';
                                                             }
                                                             if(empty($iso)==false){
                                                                $double = explode("/", $iso);
                                                                if(count($double)>1){
                                                                    $iso = strtolower($double[0]).".png";
                                                                    $doubleiso = strtolower($double[1]).".png";
                                                                }else{
                                                                    $iso = strtolower($iso).".png";
                                                                }
                                                             }
                                                     ?>
                                                        <div  class="appContact">
                                                            <table cellspacing="0px" cellpadding="0px">
                                                            <tbody>
                                                              <tr>
                                                                  <td class="listleftcol" style="width:40px;padding:0px;" align="center" valign="top">
                                                                    <a href="<?php echo $cdata["permalink"];?>" target="_blank" >
                                                                    <div class="listcolimg">
                                                                        <img src="<?php echo $cdata["image"];?>" style="width:30px;height:30px;" alt="image"  />
                                                                    </div>
                                                                    </a>
                                                                </td>
                                                                <td class="contactlistrightcol">
                                                                    <table  cellpadding="0" cellspacing="2px" style="height: 80px">
                                                                    <tbody>
                                                                        <tr style="height:8px;line-height: 8px;" align="center" valign="top" >
                                                                            <td style="font-weight: bold;height:8px;white-space:nowrap;vertical-align: top;">
                                                                               <div style="width:100%;white-space: nowrap;padding: 3px;margin:0px;">
                                                                                    <a href="<?php echo $cdata["permalink"];?>" target="_blank" class="hidetext">
                                                                                    <?php echo $fullname; ?>
                                                                                    </a>
                                                                                    <?php if(empty($iso)==false){?>
                                                                                                <span style="padding:5px;">/</span><img src="http://appdb.egi.eu/images/flags/<?php echo $iso; ?>"  style="vertical-align: middle;width:11px;height:8px;display:inline;padding-left:3px;" alt=""/>
                                                                                                <?php if(isset($doubleiso)){?><span style="padding:2px;"></span><img alt="" src="http://appdb.egi.eu/images/flags/<?php echo $doubleiso; ?>"  style="vertical-align: middle;width:11px;height:8px;display:inline;padding-left:3px;"/><?php $doubleiso=null;$double=array();} ?>
                                                                                   <?php } ?>
                                                                               </div>
                                                                                <?php if(isset($cdata["institute"])){?>
                                                                                <div style="font-style: italic;font-weight: normal;width:100%;white-space: nowrap;padding: 3px;margin:0px;">
                                                                                    <?php echo $cdata["institute"]; ?>
                                                                                </div>
                                                                                <?php } ?>
                                                                                <?php if(isset($cdata["role"])) {?>
                                                                                <div style="font-style: italic;font-weight: normal;width:100%;white-space: nowrap;padding: 3px;margin:0px;"><?php echo $cdata["role"]; ?></div>
                                                                        <?php }?>
                                                                                </td>
                                                                        </tr>
                                                                    </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                        </div>
                                                <?php } ?>
                                            </div>
                                    <?php } ?>
                                </td>
                            </tr>
                            <tr><td colspan="2" style="padding:15px;"><a class="toggleButton" onclick="gadgets.utils.ui.ApplicationDetails.togglePub(<?php echo $id; ?>);"><span style="width:100%">Publications<?php echo ' ['.$cpubs.']';?></span></a></tr>
                            <tr >
                                <td class="fieldvalue" colspan="2" style="padding-left:15px;">
                                    <div id="appDetailPub_<?php echo $id; ?>" style="overflow:visible;width:100%;" >
                                    <?php
                                    if($cpubs>0){ ?>
                                        <ul class="list" >
                                        <?php $c=0; foreach($pubs as $p){ if(strval($p->title)===''){continue;} $c = $c+1;?>
                                            <li class="publicationRow <?php echo (($c & 1)==1)?"odd":""; ?>">
                                                 <div  class="appPublication">
                                                <table class="listtable" cellspacing="0px" cellpadding="0px" >
                                                <tbody>
                                                  <tr>
                                                    <td class="listleftcol" style="width:40px;padding:0px;" align="center" valign="top">
                                                        <a href="<?php echo $p->url;?>" target="_blank" >
                                                        <div class="listcolimg">
                                                            <img src="/gadgets/resources/skins/default/images/book.png" style="width:30px;height:30px;" alt="image"  />
                                                        </div>
                                                        </a>
                                                    </td>
                                                    <td class="publistrightcol">
                                                            <table  cellpadding="0" cellspacing="2px" style="width:100%">
                                                            <tbody>
                                                                <tr>
                                                                    <td valign="top" colspan="2">
                                                                        <a href="<?php echo $p->url;?>" target="_blank" class="linkButton" >
                                                                            <span><?php echo $p->title; ?>
                                                                                <span><i>
                                                                                <?php
                                                                                    $titleout = "";
                                                                                    if ((intval($p->startPage.'')==0) && (intval($p->endPage.'')==0)){ } else {
                                                                                        $titleout .= 'pg:'. $p->startPage;
                                                                                        if(intval($p->endPage.'')>0) {
                                                                                            $titleout .=  "-" .$p->endPage;
                                                                                        }
                                                                                    }
                                                                                    if(strval($p->volume)!==''){
                                                                                        if($titleout!==""){
                                                                                            $titleout .= ",";
                                                                                        }
                                                                                        $titleout .= 'vol:'.$p->volume;
                                                                                    }
                                                                                    if($titleout!==""){
                                                                                        echo "(".$titleout.")";
                                                                                    }
                                                                                ?>
                                                                                </i></span>
                                                                            </span>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <?php if(strval($p->conference)!==''){ ?>
                                                                <tr>
                                                                    <td class="fieldcell">Conference: </td>
                                                                    <td class="fieldvalue"><i><?php echo $p->conference; ?></i></td>
                                                                </tr>
                                                                <?php } ?>
                                                                <?php if(strval($p->proceedings)){ ?>
                                                                <tr>
                                                                    <td class="fieldcell">Proccedings: </td>
                                                                    <td class="fieldvalue"><i><?php echo $p->proceedings; ?></i></td>
                                                                </tr>
                                                                <?php } ?>
                                                                <?php if(strval($p->isbn)!=='') { ?>
                                                                <tr>
                                                                    <td class="fieldcell">ISBN: </td>
                                                                    <td class="fieldvalue"><i><?php echo $p->isbn;?></i></td>
                                                                </tr>
                                                             <?php } ?>

                                                             <?php if(strval($p->publisher)!==''){ ?>
                                                                <tr>
                                                                    <td class="fieldcell">Publisher: </td>
                                                                    <td class="fieldvalue"><?php echo $p->publisher; ?></td>
                                                                </tr>
                                                             <?php } ?>
                                                            </tbody>
                                                            </table>
                                                      </td>
                                                  </tr>
                                                </table>
                                             </div>
                                            </li>
                                        <?php }?>
                                        <?php if($c==0){?><span><i>The software has no publications</i></span><?php } ?>
                                        </ul>
                                        </div>
                                    <?php }else { ?><span><i>The software has no publications</i></span><?php } ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>

        </tbody>
    </table>

</div>