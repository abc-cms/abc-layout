<?php

//рандомный товар
$page['product_random'] = $page['shop_products'][0];

//три товара на главной
$page['products_index'] = array();
for ($i=0; $i<3; $i++) {
	$page['products_index'][] = $page['shop_products'][$i];
}