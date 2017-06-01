<div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
	<?=html_query('menu/category',$html['shop_categories'])?>
	<?=html_array('shop/product_random',$html['product_random'])?>
</div>

<div class="col-lg-9 col-md-9 col-sm-8 col-xs-12">
	<?=html_query('common/slider',$html['slider'])?>

	<?=i18n('common|txt_index') ? '<div class="content">'.i18n('common|txt_index',true).'</div><div class="clear"></div>' : ''?>

	<h2><?=i18n('shop|new',true)?></h2>
	<?=html_query('shop/product_list',$html['products_index'])?>
	<div class="clear"></div>
</div>



