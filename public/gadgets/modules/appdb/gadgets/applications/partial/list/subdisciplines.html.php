<?php
$data = $this->subdiscipline();
?>
<select id="p_search_subdesciplines" class="searchdroplist">
    <option value="-1"></option>
<?php foreach($data as $r){ ?>
    <option value="<?php echo $r->attr("id");?>"><?php echo $r; ?></option>
<?php } ?>
</select>