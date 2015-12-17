<?php $a = $this->attr();
$pcount = intval($a["count"]);
if($pcount>0){
$length = intval($a["pagelength"]);
$offset = intval($a["pageoffset"]);
$total_pages = ceil($pcount/$length);
$current_page = ceil($offset/$length + 1);
$prev_page = intval($current_page-1);
$next_page = $current_page+1;
$next_offset =$offset + $length;
$prev_offset = $offset- $length;
$lastoffset = ($total_pages*$length)-$length;
$vname = $this->getViewName();
$vname =$vname;
$jsnext = "gadgets.update({pagelength: $length ,pageoffset: $next_offset},'".$vname.";pager');";
$jsprev = "gadgets.update({pagelength: $length ,pageoffset: $prev_offset},'".$vname.";pager');";
$jsfirst = "gadgets.update({pageoffset: 0},'".$vname.";pager');";
$jslast = "gadgets.update({pageoffset: $lastoffset},'".$vname.";pager');";
?>
<ul class="listpager" >
    <?php if($prev_page==0){?>
        <li class="pagerLink pagerDisabled">Previous</li>
    <?php }else{ ?>
        <li class="pagerLink pagerActive">
            <a onclick="<?php echo $jsprev; ?>">Previous</a>
        </li>
    <?php } ?>
    <li class="pagerInfo">
        <select id="pagerCurrentPage" onchange="gadgets.utils.ui.selectpage(this,<?php echo $length;?>,'<?php echo $vname;?>;pager');">
         <?php for($i=1; $i<=$total_pages; $i=$i+1){?>
            <option value="<?php echo $i; ?>" <?php if($i==$current_page){ echo "selected='true'";} ?>><?php echo $i; ?></option>
        <?php }?>
        </select>
        <span> of </span>
        <span><?php echo $total_pages; ?></span>
    </li>
    <?php if($next_page>$total_pages){?>
        <li class="pagerLink pagerDisabled">Next</li>
    <?php } else { ?>
        <li class="pagerLink pagerActive">
            <a onclick="<?php echo $jsnext;?>" >Next</a>
        </li>
    <?php } ?>    
</ul>
<?php } else { ?>
<ul class="listpager">
    <li class="pagerInfo"><span>No software found</span></li>
</ul>
<?php } ?>
