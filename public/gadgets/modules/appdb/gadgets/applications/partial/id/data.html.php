    <?php $v = $this->application;
                 $id = $v->attr("id");
                 $istool = $v->tool;
                 $istool = (isset($istool)?($istool==='0'?false:true):false);
                 $personimg =  "http://".$_SERVER['APPLICATION_UI_HOSTNAME']."/images/person.png";
     ?>

<table><tbody>
<tr  id="appurllist_<?php echo $id; ?>" style="display:none">
    <td>
        <div style="height:120px;overflow:auto;">
            <ul class="applicationUrlList">
                <li>
                    <a href="<?php echo $v->permalink; ?>" target="_blank"  class="hidetext">
                                <span style="width:100%"><?php echo "Software permanent link";?></span>
                    </a>
                </li>
                <?php
                    $links = $v->url();
                    foreach($links as $l){
                        $url = "".$l;
                        $urltype = $l->attr("type");
                        if($url==="http://")continue;
                        ?>
                        <li>
                            <a href="<?php echo $url; ?>" target="_blank" class="hidetext">
                                <span style="width:100%"><?php echo $urltype;?></span>
                            </a>
                        </li>
                <?php } ?>
            </ul>
        </div>
    </td>
</tr>
<tr id="appcontacts_<?php echo $id;?>" style="display:none;">
    <td >
        <?php
            $contacts = $v->contact();
            if(count($contacts)===0){?>
                <span><h3>The software has no contacts</h3></span>
        <?php } else { ?>
                <div style="height:120px;overflow:auto;">
        <table class="appContactsList">
            <tbody>
                <tr >
                    <td class="appContactsListrow" >
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
                             $fullname = $cdata["name"];
                             if(strlen($fullname)>18){
                                 $fullname=substr($fullname, 0, 17).'...';
                             }
                             if(strlen($cdata["institute"])>23){
                                 $cdata["institute"]=substr($cdata["institute"], 0, 22).'...';
                             }
                             $cou = $cnt->country;
                             $iso = $cou->attr("isocode");
                             $double = array();
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
                        <div class="appContact">
                            <table cellspacing="0px" cellpadding="0px">
                            <tbody>
                              <tr>
                                <td class="listleftcol" style="width:40px;padding:0px;" align="center">
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
                                                                <span style="padding:5px;">/</span><img src="http://appdb.egi.eu/images/flags/<?php echo $iso; ?>"  style="vertical-align: middle;width:11px;height:8px;display:inline;padding-left:3px;"/>
                                                                <?php if(isset($doubleiso)){?><span style="padding:2px;"></span><img src="http://appdb.egi.eu/images/flags/<?php echo $doubleiso; ?>"  style="vertical-align: middle;width:11px;height:8px;display:inline;padding-left:3px;"/><?php $doubleiso=null;$double=array();} ?>
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
                        <?php }?>
                        <?php }?>
                    </td>
                 </tr>
            </tbody>
        </table>
            </div>
    </td>
</tr>
<tr id="appcountries_<?php echo $id;?>" style="display:none">
    <td class="applicationCountries">
        <div style="height:120px;overflow:auto;">
        <table>
            <tbody>
        <?php $countries = $v->country();
        foreach($countries as $counts){ ?>
            <tr>
                <td width="22px"><?php $iso2 = $counts->attr("isocode");
                          if(empty($iso2)==false){
                            $iso2 = strtolower($iso2).".png";
                          ?>
                    <img src="http://appdb.egi.eu/images/flags/<?php echo $iso2; ?>" alt="<?php echo $iso2; ?>" width="22" height="16"/>
                    <?php }?>
                </td>
                <td>
                    <?php echo $counts; ?>
                </td>
            </tr>
        <?php }?>
            </tbody>
        </table>
        </div>
    </td>
</tr>
<tr id="appvos_<?php echo $id;?>" style="display:none">
    <td class="applicationVos">
            <div style="height:120px;overflow:auto;padding-left:10px;">
            <?php $vos = $v->vo();
            foreach($vos as $vo){?>
                <div ><a href="http://appdb.egi.eu/?p=<?php echo base64_encode("/vo/details?id=".$vo->attr("name"));?>" target="_blank" class="linkButton"><?php echo $vo->attr("name"); ?></a></div>
           <?php }?>
            </div>
    </td>
</tr>
</tbody></table>