<div class="form-group form_file <?=isset($q['class']) ? $q['class'] : ''?>">
	<?php
		if (isset($q['caption'])) {
		?>
	<label><?=$q['caption']?></label>
		<?php
		}
	?>
	<input <?=isset($q['attr']) ? $q['attr'] : ''?> name="<?=$q['name']?>" type="file" />
</div>