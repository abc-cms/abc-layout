<?php

/**
 * функции для работы с языками
 */

/**
 * создание массива $lang
 * @param int|string $str - ИД или урл языка
 * @param string $type - урл или ИД [url|id]
 * @return array - массив данных
 */
function lang($str=false,$type='id') {
	global $config;
	$lang=false;
	//если язык не по умолчанию
	if ($str!=false) {
		$where = $type=='id' ? "id = ".intval($str) : "url = '".mysql_res($str)."'";
		$lang = mysql_select("SELECT * FROM languages WHERE ".$where." LIMIT 1",'row',60*60);
	}
	//язык должен быть всегда
	if ($lang==false) {
		$lang = mysql_select("SELECT * FROM languages WHERE display=1 ORDER BY rank DESC LIMIT 1", 'row', 60 * 60);
	}
	//i для основного языка будет пустым, а для второстепенного числом
	$lang['i'] = $lang['id']!=1 ? $lang['id'] : '';
	return $lang;//return array_merge($lang,unserialize($lang['dictionary']));
}

/**
 * выбирает слово из словаря по ключу, оборачивает в блок для редактирования
 * данные берутся из /files/languages/{ID}/dictionary/ {ID} - ИД текущего языка
 * @param string $str - ключ слова
 * @param string|array $editable - быстрое редактировение(str|text) или массив значений для замены
 * @return string - слово
 */
function i18n ($str,$editable=false) {
	global $lang;
	if (empty($lang)) $lang = lang();
	//функции авторизации
	require_once(ROOT_DIR.'functions/auth_func.php');
	$data = explode('|',$str);
	if (!isset($lang[$data[0]])) {
		if (file_exists(ROOT_DIR.'/files/languages/'.$lang['id'].'/dictionary/'.$data[0].'.php')) require (ROOT_DIR.'/files/languages/'.$lang['id'].'/dictionary/'.$data[0].'.php');
		else trigger_error('dictionary '.$str, E_USER_DEPRECATED);
	}
	//если есть права на быстрое редактирование и передали в гет $_GET['i18n'] то вместо словаря показываем просто ключи (отладка)
	if (isset($_GET['i18n']) && access('editable dictionary')) {
		return str_replace('%s', $str, '{%s}');
	}
	else {
		//если $editable массив то нужно сделать замену {i} на значения массива
		if (is_array($editable)) {
			return (isset($lang[$data[0]][$data[1]])) ? template($lang[$data[0]][$data[1]],$editable) : '';
		}
		//активировать быстрое редактирование
		elseif ($editable != false && access('editable dictionary')) {
			//функции для работы нтмл кодом - editable
			require_once(ROOT_DIR.'functions/html_func.php');
			if ($editable==true) $editable = 'str';
			$string = isset($lang[$data[0]][$data[1]]) ? $lang[$data[0]][$data[1]] : '';
			return '<span'.editable('dictionary|'.$str,$editable).'>'.$string.'</span>';
		}
		//просто вывести значение словаря
		else return (isset($lang[$data[0]][$data[1]])) ? $lang[$data[0]][$data[1]] : '';
	}
}

/**
 * выбирает слово из словаря по ключу - только для админпанели
 * @param string $str - ключ слова
 * @return string - слово
 */
function a18n ($str) {
	global $a18n;
	return (isset($a18n[$str])) ? $a18n[$str] : $str;
}
