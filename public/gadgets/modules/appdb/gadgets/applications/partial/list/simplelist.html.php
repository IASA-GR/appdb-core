
<ul class="list" style="width:auto">
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
    <li class="listrow <?php echo (($c & 1)==1)?"odd":""; ?>" >
        <div class="listitemseperator" ></div>
        <table class="listtable" style="table-layout: fixed;width:100%;padding-right: 5px" cellSpacing="0" cellPadding="0" border="0">
        <tbody>
            <tr>
                <td class="listleftcol"  align="center">
                    <div class="listcolimg">
                        <a href="<?php echo $v->permalink; ?>" target="_blank" >
                                <img src="<?php echo $applogo; ?>" alt="logo"  />
                        </a>
                    </div>
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
                                            <a class="linkButton"  target="_blank"  onclick="gadgets.utils.ui.ApplicationDetails.load('<?php echo $id; ?>')"  ><?php echo (strval($v->name)==""?"-":$v->name); ?></a>
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
									<?php if($istool==false) { ?>
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
									<?php $subd = $v->subdiscipline(); $subdcount = count($subd);
									if($istool==false && $subdcount>0 && strlen($subd[0])>0){?>
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
									</tr>
									<?php } ?>
                                    <tr >
                                        <td class="fieldcell">
                                            Abstract
                                        </td>
                                        <td class="valuecell" valign="baseline" >
                                           <?php
                                                        $abslen = strlen($abs);
                                                        if($abslen<100){
                                                            echo strip_tags($abs);
                                                        } else {
                                                            echo substr(strip_tags($abs),0,100); ?><span>...</span>
                                                    <?php }?>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr >
                <td>
                </td>
                <td align="right">
                    <table style="table-layout: fixed;float:right;width:70px">
                        <tr align="right" >
                            <td>    
                                    <table class="linkButton" >
                                        <tr>
                                            <td>
                                                <a class="linkButton"  onclick="gadgets.utils.ui.ApplicationDetails.load('<?php echo $id; ?>')" style="vertical-align: top;float:right;" title="Click to view application's details">
                                                    <img src="/gadgets/resources/skins/default/images/details.png" width="18" height="18" alt=""  title="Click to view application's details"/>
                                                </a>
                                            </td>
                                            <td>
                                                 <a class="linkButton"  onclick="gadgets.utils.ui.ApplicationDetails.load('<?php echo $id; ?>')" style="vertical-align: top;float:right;" title="Click to view application's details">details</a>
                                            </td>
                                        </tr>
                                    </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    </li>
    <?php }?>
</ul>