<?php

$page['title'] = 'Главная';

//рандомный товар
$html['product_random'] = $html['shop_products'][0];

//три товара на главной
$html['products_index'] = array();
for ($i=0; $i<3; $i++) {
	$html['products_index'][] = $html['shop_products'][$i];
}


