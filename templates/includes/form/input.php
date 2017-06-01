<div class="form-group form_input <?=isset($q['class']) ? $q['class'] : ''?>">
	<?php
		if (isset($q['caption'])) {
		?>
	<label for="<?=$q['name']?>"><?=$q['caption']?></label>
		<?php
	}
	?>
	<input name="<?=$q['name']?>" value="<?=isset($q['value']) ? $q['value'] : ''?>" class="form-control <?=isset($q['attr']) ? $q['attr'] : ''?>" />
</div>