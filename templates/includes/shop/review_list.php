<?php
if ($i==1) {	?>
<h2 style="padding:10px 0 5px"><?=i18n('shop|reviews',true)?></h2>
	<?php
	}
?>
<div class="shop_review_list">
	<div class="review_rating"><div><span style="width:<?=($q['rating']*20)?>%"></span></div></div>
	<div<?=editable('shop_reviews|name|'.$q['id'],'editable_str','review_name')?>><?=$q['name']?></div>
	<div class="review_date"><?=date2($q['date'],'%d.%m.%Y')?></div>
	<div<?=editable('shop_reviews|text|'.$q['id'],'editable_text','review_text')?>><?=$q['text']?></div>
</div>