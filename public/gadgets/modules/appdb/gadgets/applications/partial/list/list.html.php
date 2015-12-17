
<ul class="list">
    <?php
    $c = 0;
    $id = -1;
    $a = $this->application();
    foreach($a as $v){
        $c = $c+1;
        $id = $v->attr("id");
		$istool = $v->attr("tool");
		$istool = ($istool=='true')?true:false;
        $abs = "".$v->abstract;
        $applogo = $v->logo;
        if(is_null($applogo)){
		  if($istool==false){
			$applogo =  "http://".$_SERVER['APPLICATION_UI_HOSTNAME']."/images/app.png";
		  }else{
			  $applogo =  "http://".$_SERVER['APPLICATION_UI_HOSTNAME']."/images/tool.png";
		  }
		}
    ?>
    <li class="listrow <?php echo (($c & 1)==1)?"odd":""; ?>">
        <div class="listitemseperator" />
        <table class="listtable" style="table-layout: fixed" cellSpacing="0" cellPadding="0" border="0">
        <tbody>
            <tr>
                <td class="listleftcol"  align="center">
                    <a href="<?php echo $v->permalink; ?>" target="_blank" >
                        <div class="listcolimg">
                            <img src="<?php echo $applogo; ?>" alt="logo"  />
                        </div>
                    </a>
                </td>
                <td class="listrightcol" >
                    <table id="appdisplay_<?php echo $id; ?>">
                        <tr  id="appmain_<?php echo $id; ?>"  >
                            <td>
                                <table border="0">
                                    <tr>
                                        <td class="fieldcell">
                                            Name
                                        </td>
                                        <td class="valuecell">
                                            <a target="_blank" href="<?php echo $v->permalink; ?>" class="linkButton" ><?php echo (strval($v->name)==""?"-":$v->name); ?></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fieldcell">
                                            Description
                                        </td>
                                        <td class="valuecell">
                                            <?php echo $v->description; ?>
                                        </td>
                                    </tr>
                                    <tr >
                                        <td class="fieldcell">
                                            Abstract
                                        </td>
                                        <td class="valuecell" valign="baseline" >
                                           <?php
                                                        $abslen = strlen($abs);
                                                        if($abslen<200){
                                                            echo $abs;
                                                        } else {
                                                            echo substr($abs,0,200); ?>
                                                    <a onclick="gadgets.utils.ui.showtext(this,'<?php echo $id; ?>');" class="hidetext"  style="vertical-align: baseline;">
                                                        <img src="/gadgets/resources/skins/default/images/more.png" alt="more"  />
                                                    </a>
                                                    <?php }?>
                                        </td>
                                    </tr>
									<?php if($istool == false) { ?>
                                    <tr>
                                        <td class="fieldcell">
                                            Discipline
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
									<?php $subd= $v->subdiscipline(); $subdcount = count($subd);
                                     if ($istool == false && $subdcount>0  && strlen(''.$subd[0])>0){ ?>
									<tr>
										<td class="fieldcell">
											Subdiscipline
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
									</tr><?php } ?>
                                </table>
                            </td>
                        </tr>
                        <tr id="appabstract_<?php echo $id; ?>" style="display:none;">
                            <td>
                                <div style="height:120px;overflow:auto;">
                                    <span class="fieldcell">Abstract: </span><?php echo $abs; ?>
                                </div>
                            </td>
                        </tr>
                        <tr  id="appurllist_<?php echo $id; ?>" style="display:none;">
                           <td>
                               <center>
                                   <div>loading...</div>
                                   <div>
                                       <img src="/gadgets/resources/images/ajax-loader.gif" alt="loading" />
                                   </div>
                               </center>
                           </td>
                        </tr>
                        <tr id="appcontacts_<?php echo $id;?>" style="display:none;">
                           <td>
                               <center>
                                   <div>loading...</div>
                                   <div>
                                       <img src="/gadgets/resources/images/ajax-loader.gif" alt="loading" />
                                   </div>
                               </center>
                           </td>
                        </tr>
                        <tr id="appcountries_<?php echo $id;?>" style="display:none;">
                           <td >
                               <center>
                                   <div>loading...</div>
                                   <div>
                                       <img src="/gadgets/resources/images/ajax-loader.gif" alt="http://www.egi.eu"/>
                                   </div>
                               </center>
                           </td>
                        </tr>
                        <tr id="appvos_<?php echo $id;?>" style="display:none;">
                           <td style="vertical-align: middle">
                               <center>
                                   <div>loading...</div>
                                   <div>
                                       <img src="/gadgets/resources/images/ajax-loader.gif" alt="loading" />
                                   </div>
                               </center>
                           </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr style="padding-top:10px;">
                <td >
                </td>
                <td align="center">
                    <table style="table-layout: fixed;padding-left:5px;padding-right:5px;" width="auto">
                        <tr><td><div style="height:5px;padding: 0PX;margin:0px;"></div></td></tr>
                        <tr class="itemfooter" id="itemfooter_<?php echo $id; ?>">
                            <td class="itemfooterlink itemselected" id="tab_main"><a onclick="gadgets.utils.ui.showmain('<?php echo $id; ?>')"><span>main</span></a></td>
                            <td class="itemfooterlink" id="tab_abstract"><a onclick="gadgets.utils.ui.showabstract('<?php echo $id; ?>')"><span>abstract</span></a></td>
                            <td class="itemfooterlink" id="tab_links"><a onclick="gadgets.utils.ui.showlinks('<?php echo $id; ?>')"><span>material</span></a></td>
                            <td class="itemfooterlink" id="tab_contacts"><a onclick="gadgets.utils.ui.showcontacts('<?php echo $id; ?>')"><span>contacts</span></a></td>
                            <td class="itemfooterlink" id="tab_countries"><a onclick="gadgets.utils.ui.showcountries('<?php echo $id; ?>')"><span>countries</span></a></td>
                            <td class="itemfooterlink" id="tab_vos"><a onclick="gadgets.utils.ui.showvos('<?php echo $id; ?>')"><span>vos</span></a></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    </li>
    <?php }?>
</ul>
