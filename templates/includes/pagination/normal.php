<?php

/*
 * стандартный пагинатор, используется в функции html_query() в качестве второго значение (через проблем) в первом параметре
 */

//массив возможных количеств выдачи записей
$count_array = array('10'=>'10');
//$count_array = array('10'=>'10','2'=>'20','30'=>'30','all'=>i18n('common|pagination_count_all'));
$count_first = key($count_array);
//номер страницы с записями
$n = (isset($_GET['n']) && $_GET['n']>=1) ? intval($_GET['n']) : 1;
//количество записей на страницу
$c = (isset($_GET['c']) && array_key_exists($_GET['c'],$count_array)) ? intval($_GET['c']) : key($count_array);
//полное количество записей
if (isset($m[2])) $num_rows = $m[2];
else $num_rows = mysql_select($query,'num_rows',$cache);

//КОД ПАГИНАТОРА ***************************************************************
if ($num_rows>0 && $c>0) {
	//если количество переданное через урл больше реального количества то сравнивается
	if ($c>$num_rows) $c = $num_rows;
	//количество страниц пагинатора
	$quantity = ceil($num_rows/$c);

	//количество ссылок
	$lc = 7;

	//страниц меньше или равно $lc
	if ($quantity <= $lc) {
		for ($i=1; $i<=$quantity; $i++) $list[] = array($i,$i);

	//если страниц пагинатора больше $lc, так как пагинатор расчитан только на $lc ссылок
	} else {
		//активная в начале  [1][2][3][4][5][..][100], если она не замыкает группу (5)
		if ($n < ($e = $lc - 2)) {
			for ($i=1; $i<=$e; $i++) $list[] = array($i,$i);			//$lc-2 первых ссылок
			$list[] = array(ceil(($quantity + $e)/2),0);				//[..]
			$list[] = array($quantity,$quantity);						//последняя ссылка

		//активная в коце [1][..][96][97][98][99][100], если она не начинает группу (96)
		} elseif ($n > ($s = $quantity - $lc + 2 + 1)) {
			$list[] = array(1,1);										//первая ссылка
			$list[] = array(ceil(($s + 1)/2),0);						//[..]
			for ($i = $s; $i<=$quantity; $i++) $list[] = array($i,$i);	//$lc-2 последних ссылок

		//активная в середине [1][..][49][50][51][..][100]
		} else {
			$s = $n - ceil(($lc - 4 - 1)/2);
			$e = $n + floor(($lc - 4 - 1)/2);

			$list[] = array(1,1);										//первая ссылка
			$list[] = array((ceil(($s + 1)/2)),0);						//[..]
			for ($i = $s; $i<=$e; $i++) $list[] = array ($i,$i);		//$lc-4 средних ссылок
			$list[] = array(ceil(($quantity + $e)/2),0);				//[..]
			$list[] = array($quantity,$quantity);						//последняя ссылка
		}
	}
}

//HTML *************************************************************************
$get = $_GET;
unset($get['u'],$get['n'],$get['c']);
$url = $url_c = http_build_query($get);
//урл для страниц
if ($url) $url = $url.'&amp;';
//если уазано количество то сохранять при постраничной
if ($c!=$count_first) $url.= 'c='.$c.'&amp;';
//урл для количества
if ($url_c) $url_c = $url_c.'&amp;';
$clear =  '/';
foreach ($u as $k=>$v) if ($v) $clear.=$v.'/';
//если есть пагинатор
if (isset($list) && count($list)>1) {
	$pagination = '<div class="pagination__normal">';
	$pagination .= '<ul class="pagination">';
	if ($n==1) $pagination.= '<li class="disabled"><span class="button">'.i18n('common|pagination_prev').'</span></li>';
	else $pagination.= '<li><a class="button" href="'.(($url=='' AND $n==2) ? $clear : '?'.$url.'n='.($n-1)).'">'.i18n('common|pagination_prev').'</a></li>';
	foreach ($list as $k=>$v) {
		$name = $v[1]==0 ? '...' : $v[0];
		if ($v[0]==$n) $pagination.= '<li class="current"><span class="button">'.$name.'</span></li>';
		else $pagination.= '<li><a class="button" href="'.(($url=='' AND $v[1]==1) ? $clear : '?'.$url.'n='.$v[0]).'">'.$name.'</a></li>';
	}
	if ($n==$quantity) $pagination.= '<li class="disabled"><span class="button">'.i18n('common|pagination_next').'</span></li>';
	else $pagination.= '<li><a class="button" href="?'.$url.'n='.($n+1).'">'.i18n('common|pagination_next').'</a></li>';
	$pagination.= '</ul>';
	if (count($count_array)>1) {
		$pagination .= '<ul class="pagination pagination_count">';
		foreach ($count_array as $k=>$v) {
			if ($c==$k) $pagination.= '<li class="current"><span class="button">'.$v.'</span></li>';
			else $pagination.= '<li><a class="button" href="'.(($url_c=='' AND $count_first==$k) ? $clear : '?'.$url_c.'c='.$k).'">'.$v.'</a></li>';
		}
		$pagination.= '</ul>';
	}
	$pagination.= '</div>';
	$pagination = '{content}'.$pagination;
//если нет результата
} else $pagination = '{content}';

//QUERY ************************************************************************
//если не показать все записи то добавляем лимит
if ($c>0) {
	$begin = $n * $c - $c;
	if (is_string($query)) {
		$query .= ' LIMIT ' . $begin . ',' . $c;
	}
}
