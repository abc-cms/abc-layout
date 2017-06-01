<?php

/**
 * общие функции
 */

//обрезание обратных слешев в $_REQUEST данных
function stripslashes_smart($post) {
	if (get_magic_quotes_gpc()) {
		if (is_array($post)) {
			foreach ($post as $k=>$v) {
				$q[$k] = stripslashes_smart($v);
			}
		}
		else $q = stripslashes($post);
	}
	else $q = $post;
	return $q;
}

//создание урл из $_GET
function build_query($key = '') {
	$get = $_GET;
	if ($key) {
		$array = explode(',',$key);
		foreach ($array as $k=>$v) unset($get[$v]);
	}
	return http_build_query($get);
}

//создание файла лога в папке logs
/**
 * @param $file - название файла в папке /logs/
 * @param $string - строка или массив данных который будут записаны в лог
 * @param bool $debug - в значении true логи будут писываться только если $config['debug'] = true
 */
function log_add($file,$string,$debug=false) {
	global $config;
	//логи с пометкой дебаг не создаются при выключеном $config['debug']
	if ($debug==false OR $config['debug'] == true) {
		if (!is_dir(ROOT_DIR . 'logs')) mkdir(ROOT_DIR . 'logs');
		$fp = fopen(ROOT_DIR . 'logs/' . $file, 'a');
		//если в лог передан массив то делаем из него строку
		if (is_array($string)) {
			$content = '';
			foreach ($string as $k=>$v) {
				if (is_array($v)) $content.= $k.':'.serialize($v)."\t";
				else $content.= $k.':'.$v."\t";
			}
			$string = $content;
		}
		fwrite($fp, $string . PHP_EOL);
		fclose($fp);
	}
}

//получить ИП
function get_ip(){
	$ip = '';
	if(!empty($_SERVER['HTTP_X_REAL_IP'])) {//check ip from share internet
		$ip = $_SERVER['HTTP_X_REAL_IP'];
	}
	elseif(!empty($_SERVER['HTTP_CLIENT_IP'])) { //check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	}
	elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { //to check ip is pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

/**
 * Transpose input arrays and save input keys.
 * 
 * Example inputs: 
 * 
 * <code>
 * <input name="name[]" value="Alex"><br>
 * <input name="post[]" value="Actor"><br>
 * <input name="email[]" value="alex_actor@mail.dev"><br>
 * </code>
 * 
 * input as
 * 
 * <code>
 * [
 *  'name' => ['Alex', 'Born', 'Cindal'],
 *  'post' => ['Actor', 'Banker', 'Conductor'],
 *  'email' => ['alex_actor@mail.dev', 'born_banker@mail.dev', 'cindal_conductor.dev']   
 * ];
 * </code>
 * output as 
 * <code>
 * [
 *  0 => [
 *      'name'  => 'Alex',
 *      'post'  => 'Actor',
 *      'email' => 'alex_actor@mail.dev'
 *  ],
 *       1 => [
 *           'name'  => 'Born',
 *           'post'  => 'Banker',
 *           'email' => 'born_banker@mail.dev'
 *       ],
 *       2 => [
 *           'name'  => 'Cindal',
 *           'post'  => 'Conductor',
 *           'email' => 'cindal_conductor.dev'
 *       ],
 *   ];
 * </code>
 * 
 * @param array $inputArray
 * @return array
 */
function transposeArray(array $inputArray){
	$outputArray = array();
	foreach ($inputArray as $dataKey=>$dataValues) {
		foreach ($dataValues as $k=>$v) {
			$outputArray[$k][$dataKey] = $v;
		}
	}
	return $outputArray;
}

/**
 * Get value from config and return default value if empty.
 * Ex. config('mysql_server', 'localhost');
 * Or config('mysql.server', 'localhost') for get multidimensional array value
 * 
 * @global array $config
 * @param string $key
 * @param mixed $default
 * @return mixed
 * добавлана v.1.1.21
 */
function config($key, $default = NULL) {
	global $config;

	if(strpos($key, '.')) 
	{
	    $array = $config;            
	    foreach (explode('.', $key) as $segment) {                
		if (isset($array[$segment])) {
		    $array = $array[$segment];
		} else {
		    return $default;
		}
	    }

	    return $array;
	} 
	else 
	{
	    return (isset($config[$key])) ? $config[$key] : $default;
	}                                
}

/*
 * Функция для сжатия нтмл кода
 * @param $body - простой нтмл код
 * @return mixed - сжатый нтмл код
 * @version v1.2.11
 * v.1.1.8 - добавлена
 * v.1.2.11 - полностью обновлена
*/
function html_minify ($body) {
	//remove redundant (white-space) characters
	$replace = array(
		//remove tabs before and after HTML tags
		'/\>[^\S ]+/s'   => '>',
		'/[^\S ]+\</s'   => '<',
		//shorten multiple whitespace sequences; keep new-line characters because they matter in JS!!!
		'/([\t ])+/s'  => ' ',
		//remove leading and trailing spaces
		'/^([\t ])+/m' => '',
		'/([\t ])+$/m' => '',
		// remove JS line comments (simple only); do NOT remove lines containing URL (e.g. 'src="http://server.com/"')!!!
		'~//[a-zA-Z0-9 ]+$~m' => '',
		//remove empty lines (sequence of line-end and white-space characters)
		'/[\r\n]+([\t ]?[\r\n]+)+/s'  => "\n",
		//remove empty lines (between HTML tags); cannot remove just any line-end characters because in inline JS they can matter!
		'/\>[\r\n\t]+\</s'    => '><',
		//все пробелы между тегами нельзя удалять
		'/\>[ ]+\</s'    => '> <',
		//remove "empty" lines containing only JS's block end character; join with next line (e.g. "}\n}\n</script>" --> "}}</script>"
		'/}[\r\n\t ]+/s'  => '}',
		'/}[\r\n\t ]+,[\r\n\t ]+/s'  => '},',
		//remove new-line after JS's function or condition start; join with next line
		'/\)[\r\n\t ]?{[\r\n\t ]+/s'  => '){',
		'/,[\r\n\t ]?{[\r\n\t ]+/s'  => ',{',
		//remove new-line after JS's line end (only most obvious and safe cases)
		'/\),[\r\n\t ]+/s'  => '),',
		//remove quotes from HTML attributes that does not contain spaces; keep quotes around URLs!
		'~([\r\n\t ])?([a-zA-Z0-9]+)="([a-zA-Z0-9_/\\-]+)"([\r\n\t ])?~s' => '$1$2=$3$4', //$1 and $4 insert first white-space character found before/after attribute
	);
	$body = preg_replace(array_keys($replace), array_values($replace), $body);

	//remove optional ending tags (see http://www.w3.org/TR/html5/syntax.html#syntax-tag-omission )
	$remove = array(
		'</option>', '</li>', '</dt>', '</dd>', '</tr>', '</th>', '</td>'
	);
	$body = str_ireplace($remove, '', $body);
	return $body;
}

/**
 * функция для тестирования скриптов, выводить в удобочитаемом виде информацию
 * @param $data - массив значений для вывода на экран
 * @param bool $die - опция умирать или нет
 * @version v1.1.30
 * v.1.1.30 - добавлена
 */
function dd($data,$die=false) {
	echo '<pre>';
	print_r($data);
	echo '<pre>';
	if ($die) die();
}