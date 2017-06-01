<?php
$img = get_img('shop_products',$q,'img');
$title = filter_var($q['name'],FILTER_SANITIZE_STRING);
$alt = $q['img'] ? 'p-'.$q['img'] : i18n('common|wrd_no_photo');
$url = get_url('shop_product',$q);
if ($i==1) echo '<div class="inner_container"><div class="row">';
?>
<div class="shop_product_list col-xs-12 col-sm-6 col-md-4">
	<div class="border clearfix">
		<div class="img"><div><a href="<?=$url?>"" title="<?=$title?>"><img width="100%" src="<?=$img?>" alt="<?=$title?>" /></a></div></div>
		<a class="name" href="<?=$url?>" title="<?=$title?>"><?=$q['name']?></a>
		<?php if ($q['price']>0) {?>
		<div class="price">
			<span<?=editable('shop_products|price|'.$q['id'])?>><?=$q['price']?></span> <?=i18n('shop|currency',true)?>
			<?php if ($q['price2']>0) {?>
			<s><span<?=editable('shop_products|price2|'.$q['id'])?>><?=$q['price2']?></span> <?=i18n('shop|currency',true)?></s>
			<?php } ?>
		</div>
		<?php } ?>
		<?php if (isset($modules['basket']) AND $q['price']>0) {?>
		<a class="js_buy btn btn-default pull-left" data-id="<?=$q['id']?>" data-price="<?=$q['price']?>" href="#" title="<?=i18n('basket|buy')?>"><i class="icon-shopping-cart"></i> <?=i18n('basket|buy')?></a>
		<?php } ?>
		<a class="btn btn-primary pull-right" href="<?=$url?>" title="<?=i18n('common|wrd_more')?>"><?=i18n('common|wrd_more')?></a>
	</div>
</div>
<?php
if (fmod($i,3)==0) echo '<div class="clearfix visible-md"></div>';
if (fmod($i,2)==0) echo '<div class="clearfix visible-sm"></div>';
if ($i==$num_rows) echo '</div></div>';