<?php
    $data = $this->vo();
?>
<select id="p_search_vos" class="searchdroplist">
    <option value="-1"></option>
<?php foreach($data as $r){ ?>
    <option value="<?php echo $r->attr("id");?>"><?php echo $r->attr("name"); ?></option>
<?php } ?>
</select>