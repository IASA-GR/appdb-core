<?php
    $data = $this->tag();
?>
<select id="p_search_tags" class="searchdroplist">
    <option value="-1"></option>
<?php foreach($data as $r){ ?>
    <option value="<?php echo ''.$r;?>"><?php echo ''.$r; ?></option>
<?php } ?>
</select>