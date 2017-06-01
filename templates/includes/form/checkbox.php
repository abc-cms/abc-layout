<div class="form-group checkbox form-checkbox <?=@$q['class']?>">
	<?php
	$checked = (isset($q['value']) AND $q['value']==1) ? ' checked="checked" ' : '';
	if (isset($q['caption'])) {
		?>
	<div class="label"><?=$q['caption']?></div>
		<?php
		}
	?>
	<div class="data">
		<label>
			<input name="<?=$q['name']?>" <?=isset($q['attr']) ? $q['attr'] : ''?> type="checkbox" value="1"<?=$checked?>/>
			<span><?=isset($q['units']) ? $q['units'] : ''?></span>
		</label>
	</div>
	<div class="clear"></div>
</div>
