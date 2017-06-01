<?php

/**
 * функции для работы со строками
 */

/**
 * генерация ключевых слов
 * @param string $str - html код с текстом
 * @return string - ключевые слова через запятую
 * @version v1.2.4
 * v1.2.4 - добавил чтобы всегда возвращало string (раньше могло null вернуть)
 */
function keywords($str) {
	$keywords = '';
	if (strlen($str)>0) {
		$str = preg_replace("/&[\w]+;/", ' ',$str);	//замена символов типа &nbsp; на пробел
		$str = mb_strtolower(trim(strip_tags($str)),'UTF-8');
		$str = preg_replace('~[^-їієа-яa-z0-9 ]+~u', ' ', $str);
		$token = strtok($str, ' ');
		$array = array();
		while ($token) {
			$token = trim($token);
			if (strlen($token)>=4) {
				if (!isset($array[$token])) $array[$token]=0;
				$array[$token]++;
			}
			$token = strtok(' ');
		}
		if (count($array)>0) {
			arsort ($array);
			foreach ($array as $key=>$value) {
				if (strlen($keywords.', '.$key)>255) break;
				$keywords.= ', '.$key;
			}
			return substr($keywords, 2);
		}
	}
	return '';
}

/**
 * генерирует описание из текста
 * @param string $str - html код с текстом
 * @return string текст около 255 символов длиной
 */
function description($str) {
	$description = '';
	$str = preg_replace("/&[\w]+;/", ' ',$str);	//замена символов типа &nbsp; на пробел
	$str = trim(strip_tags($str));
	$token = strtok($str, ' ');
	while ($token) {
		$token = trim($token);
		if ($token!='') {
			if (strlen($description.' '.$token)>255) break;
			$description.= trim($token).' ';
		}
		$token = strtok(' ');
	}
	return trim($description);
}

/**
 * преобразование кирилицы в транслит
 * @param string $str - строка текста, обычно название
 * @return string - транлит
 */
function trunslit($str){
	$str = mb_strtolower(trim(strip_tags($str)),'UTF-8');
	$str = str_replace(
		array('a','o','u','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ь','ы','ъ','э','ю','я','і','ї','є'),
		array('a','o','u','a','b','v','g','d','e','e','zh','z','i','i','k','l','m','n','o','p','r','s','t','u','f','h','ts','ch','sh','shch','','y','','e','yu','ya','i','yi','e'),
		$str
	);
	$str = preg_replace('~[^-a-z0-9_.]+~u', '-', $str);	//удаление лишних символов
	$str = preg_replace('~[-]+~u','-',$str);			//удаление лишних -
	$str = trim($str,'-');								//обрезка по краям -
	$str = trim($str,'.');
	return $str;
}

//зaмена функции strtolower
if (!function_exists('mb_strtolower')) {
	function mb_strtolower($str,$enc = 'UTF-8') {
		$large = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','A','A','A','A','A','A','?','C','E','E','E','E','I','I','I','I','?','N','O','O','O','O','O','O','U','U','U','U','Y','А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','Є');
		$small = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','a','a','a','a','a','a','?','c','e','e','e','e','i','i','i','i','?','n','o','o','o','o','o','o','u','u','u','u','y','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я','є');
		return str_replace($large,$small,$str);
	}
}
//оставлена для совместимости
function strtolower_utf8($str){
	return mb_strtolower($str,'UTF-8');
}

/**
 * корректировка часового пояса
 * @param $date - дата
 * @param int $time_zone - временной пояс
 * @return string - дата с учетом временного пояса
 */
function time_zone($date,$time_zone = false) {
	if ($time_zone==false) {
		if (access('user auth')==false) return $date;
		global $user;
		$time_zone = $user['time_zone'];
	}
	if ($time_zone==4) return $date;
	return strftime('%Y-%m-%d %H:%M:%S',(strtotime($date)+($time_zone-4)*60*60));
}

/**
 * доставляет нули к числу
 * @param int $number - число
 * @param int $n - количество цифр в числе
 * @return string - число с нулями - 00067
 */
function zerofill($number,$n = 7) {
	return str_pad($number,$n,'0',STR_PAD_LEFT);
}

/**
 * конвертация даты
 * @param datetime $date - время
 * @param string $type - формат даты
 * @return string - отформатировання дана
 */
function date2($date,$type='%d.%m.%y') {
	//названия месяцев $m = strftime('%m',$date);
	//i18n('calendar|month_'.$m) полное название месяца
	//i18n('calendar|month2_'.$m) родительный падеж
	//i18n('calendar|m_'.$m) сокращенное название месяца
	//названия дней $d = strftime('%d',strtotime($date));
	//i18n('calendar|day_'.$d) полное название дня
	//i18n('calendar|d_'.$d) сокращенное название дня
	//полная дата, месяц в родительном падеже
	if ($type=='d month y') {
		if (is_string($date)) $date = strtotime($date);
		$d = strftime('%d',$date);
		$m = strftime('%m',$date);
		$y = strftime('%Y',$date);
		return $d.' '.i18n('calendar|month2_'.$m).' '.$y;
	}
	//в зависимости от прошедшего времени показывает три варианта
	//12:52 | 5 июля | 2011 г.
	elseif ($type=='smart') {
		//если в прошлом году 2011 г.
		if ($date<date('Y')) return date2($date,'%Y ').i18n('calendar|y');
		//если в этом году 5 июля
		elseif 	($date<date('Y-m-d')) {
			$d = strftime('%d',strtotime($date));
			$m = strftime('%m',strtotime($date));
			return $d.' '.i18n('calendar|month2_'.$m);
		}
		// если сегодня 12:52
		else return date2($date,'%H:%M');
	}
	else return strftime($type,strtotime($date));
}

/**
 * обрезание текста
 * @param $text нтмл - код с текстом
 * @param int $lenght - длина результирующего текста
 * @param string $strip_tags - какие теги оставляем
 * @param string $end - постфикс если обрезали строку
 * @return string - обрезанная строка
 * @version v1.1.32
 * v1.1.32 - замена iconv на mb
 */
function about($text,$lenght = 1000,$strip_tags = '<br><img>',$end = '..') {
	$text  = strip_tags($text,$strip_tags);
	if (strlen($text)>$lenght) {
		$text = mb_substr($text,0,$lenght,"UTF-8");
		$text.= $end;
	}
	return $text;
}

/**
 * функция читабельности текста
 * @param string $text - текст
 * @return string - оформленный текст
 */
function readability($text) {
	//замена - на &mdash
	$text = str_replace(" - ", "&nbsp;&mdash; ", $text);
	//замена пробелов на &nbsp возле кортких слов (1 и 2 буквы)
	$text = preg_replace('/(^|\s+)([0-9A-Za-zA-Zа-яЇї]{1,2})\s+/ui', '$1$2&nbsp;', $text);
	return $text;
}

/**
 * аналог explode только возвращает не массив а нужный елемент
 * @param string $delimiter - разделитель
 * @param string $str - строка для разделения
 * @param int $number -
 * @param int $count
 * @return mixed
 */
function explode2($delimiter,$str,$number = 1,$count = 2) {
	$array = explode($delimiter,$str,$count);
	$n = $number-1;
	if (isset($array[$n])) return $array[$n];
}

/**
 * множественное число слова
 * @param int $number - число
 * @param string $str1 - строка один ...
 * @param string $str2 - строка два ...
 * @param string $str5 - строка пять ...
 * @return string
 */
function plural($number, $str1, $str2, $str5){
	return $number % 10 == 1 && $number % 100 != 11 ? $str1 : ($number % 10 >= 2 && $number % 10 <= 4 && ($number % 100 < 10 || $number % 100 >= 20) ? $str2 : $str5);
}

function setUrlParams($url, $params = array())  {
	$urlp = parse_url($url);
	if(!count($params) && (!is_array($urlp))) return $url;
	else {
		parse_str(@$urlp['query'],$urlparams);
		foreach ($params as $k=>$v) {
			if ($v!==false) $urlparams[$k] = $v;
			else unset($urlparams[$k]);
		}
		$query = http_build_query($urlparams);
		return "{$urlp['path']}?".$query;
	}
}
