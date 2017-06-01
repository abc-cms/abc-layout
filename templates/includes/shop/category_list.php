<?php
if ($config['multilingual']) $q['name'] = $q['name'.$lang['i']];
$img = $q['img'] ? '/files/shop_categories/'.$q['id'].'/img/p-'.$q['img'] : '/'.$config['style'].'/images/no_img.svg';
$url = get_url('shop_category',$q);
$title = filter_var($q['name'],FILTER_SANITIZE_STRING);
if ($i==1) echo '<div class="inner_container"><div class="row">';
?>
<div class="shop_category_list col-xs-6 col-sm-4 col-md-3">
	<div class="border">
		<div class="img" ><a href="<?=$url?>" style="background-image:url('<?=$img?>')" title="<?=$title?>"><img src="<?=$img?>" /></a></div>
		<div class="name"><a  href="<?=$url?>" title="<?=$title?>"><?=$q['name']?></a></div>
	</div>
</div>
<?php
if ($i==$num_rows) echo '</div></div>';