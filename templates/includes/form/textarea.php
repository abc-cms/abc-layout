<div class="form-group form_textarea <?=isset($q['class']) ? $q['class'] : ''?>">
	<?php
	if (isset($q['caption'])) {
		?>
	<label><?=$q['caption']?></label>
		<?php
	}
	?>
	<textarea name="<?=$q['name']?>" class="form-control<?=isset($q['attr']) ? $q['attr'] : ''?>"><?=isset($q['value']) ? $q['value'] : ''?></textarea>
</div>
