<?=html_sources('footer','highslide_gallery')?>
<?php
$img = get_img('shop_products',$q,'img');
$images = @$q['imgs'];
$shop_parameters = false;
$title = filter_var($q['name'],FILTER_SANITIZE_STRING);
?>
<h1><?=$q['name']?></h1>
<div class="shop_product_text content">
	<div class="gallary">
		<?php if ($q['img']) {?><a title="<?=$title?>" onclick="return hs.expand(this, config1)" href="<?=$img?>"><?php } ?>
		<img src="<?=$img?>" alt="<?=$title?>" />
		<?php if ($q['img']) {?></a><?php } ?>
		<?php if ($images) {
			$n = 0;
			$list = '';
			foreach ($images as $k=>$v) if (@$v['display']==1) {
				$n++;
				$alt2=filter_var(@$v['name'],FILTER_SANITIZE_STRING);
				$title2=filter_var(@$v['title'],FILTER_SANITIZE_STRING);
				$title2 = $title2 ? $title2 : $alt2;
				$path = '/templates/_images/shop_products/'.$v['file'];
				$list.= '<li><a title="'.$title2.'" onclick="return hs.expand(this, {slideshowGroup: 2})" href="'.$path.'"><img src="'.$path.'" alt="'.$alt2.'" title="'.$title2.'" /></a></li>';
			}
			if ($n) {?>
			<div class="carousel">
				<ul><?=$list?></ul>
				<a class="next" href="#" title=""></a>
				<a class="prev" href="#" title=""></a>
			</div>
		<?php }} ?>
	</div>
	<div class="info">

		<?php if ($q['price']>0) {?>
		<div class="price">
			<span><?=$q['price']?></span> <?=i18n('shop|currency',true)?>
			<?php if ($q['price2']>0){?>
			<s><span><?=$q['price2']?></span> <?=i18n('shop|currency',true)?></s>
			<?php } ?>
		</div>
		<?php } ?>

		<a class="js_buy btn btn-default pull-left" data-id="<?=$q['id']?>" data-price="<?=$q['price']?>" href="#" title="<?=i18n('basket|buy')?>"><i class="icon-shopping-cart"></i> <?=i18n('basket|buy')?></a>


		<?=html_array('common/share')?>
		<div class="text"><?=$q['text']?></div>
	</div>
	<div class="clearfix"></div>
	<? /*html_query('shop/review_list',"SELECT * FROM shop_reviews WHERE display=1 AND product=".$q['id']." ORDER BY date DESC",'')?>
	<?=html_array('shop/review_form',$q) */?>
</div>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function () {
	//количетство фото в блоке
	var count = $('.shop_product_text .gallary .carousel ul li').length;
	//ширина одной фото
	var margin = $('.shop_product_text .gallary .carousel ul li:first-child').outerWidth();
	//задаем ширину всего блока
	$('.shop_product_text .gallary ul').width(count*margin);
	//поках стрелочек при ховере
	$('.shop_product_text .gallary .carousel').hover(
		function () {
			$('.shop_product_text .carousel .next, .shop_product_text .carousel .prev').show().css('display','block');
		},
		function () {
			$('.shop_product_text .carousel .next, .shop_product_text .carousel .prev').hide();
		}
	);
	//перемотка
	$('.shop_product_text .gallary .next,.shop_product_text .gallary .prev').click(function(){
		//текущее смещение (отрицательное значение)
		var left = parseInt($('.shop_product_text .gallary ul').css('margin-left'));
		//ширина всего блока
		var width_total = parseInt($('.shop_product_text .gallary ul').width());
		//ширина видимой части
		var width_box = parseInt($('.shop_product_text .gallary .carousel').width());
		//ширина одной фото
		var margin = $('.shop_product_text .gallary .carousel ul li:first-child').outerWidth();
		//console.log(width_total+' '+width_box+' '+left+' '+margin);
		//перемотка вперед
		if ($(this).hasClass('next')) {
			//если ширина видимой части + смещение больше общей ширины то больше не мотаем
			if (width_box+margin-left>width_total) return false;
			left = left-margin;
		}
		//перемотка назад
		else {
			if (left>=0) return false;
			left = left+margin;
		}
		$('.shop_product_text .gallary ul').animate({marginLeft:left+'px'},500);
		return false;
	});
});
</script>
