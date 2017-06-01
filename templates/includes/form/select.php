<div class="form-group form_select <?=isset($q['class']) ? $q['class'] : ''?>">
	<?php
	if (isset($q['caption'])) {
		?>
	<label><?=$q['caption']?></label>
		<?php
	}
	?>
	<select class="form-control <?=isset($q['attr']) ? $q['attr'] : ''?>" name="<?=$q['name']?>" ><?=$q['select']?></select>
</div>