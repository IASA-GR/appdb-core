<?php
    $data = $this->category();
	$flat_data = getHierarchyValues($data);
?>
<select id="p_search_categories" class="searchdroplist">
    <option value="-1"></option>
<?php foreach($flat_data as $r){?>
    <option value="<?php echo $r["id"];?>" data-parentid="<?php echo $r["parentid"]; ?>"><?php echo $r["indentvalue"]; ?></option>
<?php } ?>
</select>