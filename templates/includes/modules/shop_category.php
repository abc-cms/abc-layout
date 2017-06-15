<div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
	<?=html_query('menu/category',$page['shop_categories'])?>
	<?=$html['filter']?>
</div>

<div class="col-lg-9 col-md-9 col-sm-8 col-xs-12">
	<h1><?=$page['name']?></h1>
	<?=$html['product_list']?>
</div>

