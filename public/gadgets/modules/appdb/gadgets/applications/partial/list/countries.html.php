<?php
$regional = $this->country();
?>
<select id="p_search_countries" class="searchdroplist">
    <option value="-1"></option>
<?php foreach($regional as $r){ ?>
    <option value="<?php echo $r->attr("id");?>"><?php echo $r; ?></option>
<?php } ?>
</select>
