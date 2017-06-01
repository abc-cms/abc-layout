<?php

$page['name'] = 'Страница категории';

$html['module'] = 'shop_category';

$html['product_list'] = html_query('shop/product_list normal 100',$html['shop_products']);

$html['filter'] = '';