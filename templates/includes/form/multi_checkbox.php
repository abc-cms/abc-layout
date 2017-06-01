<div class="form-group form_multi_checkbox clearfix <?=@$q['class']?>">
	<?php
	if (isset($q['caption'])) {
		?>
	<label><?=$q['caption']?></label>
		<?php
	}
	?>
	<div class="data">
		<?php
		$data = is_array($q['data']) ? $q['data'] : mysql_select($q['data'],'array');
		$value = @$q['value']!='' ? explode(',',$q['value']) : array();
		foreach ($data as $k=>$v) {
			$checked = in_array($k,$value) ? ' checked="checked" ' : '';
			?>
		<label class="checkbox-inline">
			<input name="" type="checkbox" value="<?=$k?>"<?=$checked?>/> <?=$v?>
		</label>
			<?php
		}
		?>
	</div>
	<input name="<?=$q['name']?>" type="hidden" value="<?=$q['value']?>">
</div>

