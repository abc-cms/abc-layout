<div class="row form-group form_input2 clearfix <?=isset($q['class']) ? $q['class'] : ''?>">
	<?php
	if (isset($q['caption'])) {
		?>
	<div class="col-xs-12 form_caption"><?=$q['caption']?></div>
		<?php
	}
	$data = explode('-',@$q['value'])
	?>
	<div class="col-xs-2">
		<input name="" class="form_input2_1 form-control text-right <?=isset($q['attr'][0]) ? $q['attr'][0] : ''?>" value="<?=(isset($data[0]) AND $data[0]>0) ? $data[0] : ''?>"/>
	</div>
	<div class="pull-left" style="padding:5px 0 0">&ndash;</div>
	<div class="col-xs-2">
		<input name="" class="form_input2_2 form-control text-right <?=isset($q['attr'][0]) ? $q['attr'][0] : ''?>" value="<?=@$data[1]>0 ? $data[1] : ''?>" />
	</div>
	<input name="<?=@$q['name']?>" type="hidden" value="<?=@$q['value']?>">
</div>