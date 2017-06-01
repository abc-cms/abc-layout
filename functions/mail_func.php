<?php

/**
 * функции для работы с почтой
 * TODO
 * добавить в настройки отправку через smtp
 */


/**
 * отправляет письма по шаблону в таблице letter_templates
 * @param string $template - letter_templates.name
 * @param int $language - ID языка
 * @param array $q - массив данных
 * @param string $receiver - получатель
 * @param string $sender - отправитель
 * @param string $reply - ответить
 * @param array $files - массив файлов
 * @return boolean - отправлено или нет
 * @see email
 */
function mailer($template,$language,$q,$receiver=false,$sender=false,$reply=false,$files=false) {
	//echo "SELECT * FROM letter_templates WHERE name='".$template."'";
	if ($letter = mysql_select("SELECT * FROM letter_templates WHERE name='".$template."'",'row')) {
		global $lang,$config,$modules;
		if ($receiver==false) $receiver = $letter['receiver'] ? $letter['receiver'] : $config['receiver'];
		if ($sender==false) $sender = $letter['sender'] ? $letter['sender'] : $config['sender'];
		//print_r($letter);
		ob_start();
		include (ROOT_DIR.'files/letter_templates/'.$letter['id'].'/'.$language.'/subject.php');
		$subject = ob_get_clean();
		ob_start(); // echo to buffer, not screen
		include (ROOT_DIR.'files/letter_templates/'.$letter['id'].'/'.$language.'/text.php');
		$text = ob_get_clean(); // get buffer contentshtml_array('letter_templates/'.$q);
		//echo '<b>'.$subject.'</b><br />'.$text.'<br /><br />';
		if ($letter['template']) {
			if (!function_exists('html_array')) require_once(ROOT_DIR.'functions/html_func.php');
			$text = html_array('letter_templates/'.$letter['template'],$text);
		}
		//echo ($text).'<br />';
		//закоментированна старая функция
		//return email2($sender,$receiver,$subject,$text,$reply,$files);
		return email (
			array(
				'from'=>$sender,
				'to'=>$receiver,
				'subject'=>$subject,
				'text'=>$text,
				'reply'=>$reply,
				'files'=>$files
			)
		);
	}
}


/**
 * функция для отправки письма - 2015.06.29
 * @param $data array - массив данных
 * smtp - массив данных подключения к smtp
 * subject - тема письма
 * from - отправитель
 * to - получатель
 * cc - копия
 * bc - скрытая копия
 * replay - ответить
 * cc - копия
 * bcc - скрытая копия
 * return - если письмо не пришло то куда отправить
 * type- text/html илил text/plain
 * charset - кодировка (UTF-8)
 * text - текст
 * files - прикрелпенные файлы
 * @return bool
 * http://swiftmailer.org/
 */
function email ($data = array()) {//print_r($data);
	require_once(ROOT_DIR.'plugins/swiftmailer/lib/swift_required.php');
	// Create the Transport
	if (!empty($data['smtp'])) {//smtp данные
		$transport = Swift_SmtpTransport::newInstance();
		$transport->setHost($data['smtp']['host'])
		->setPort($data['smtp']['port'])
		->setEncryption($data['smtp']['encryption'])
		->setUsername($data['smtp']['username'])
		->setPassword($data['smtp']['password']);
	} else {
		$transport = Swift_MailTransport::newInstance();
		//$transport = Swift_SendmailTransport::newInstance();
	}
	// Create the Mailer using your created Transport
	$mailer = Swift_Mailer::newInstance($transport);
	// Create a message
	$message = Swift_Message::newInstance($data['subject']); //тема письма
	$message -> setFrom($data['from']); //отправитель
	//получатели
	$to = explode(',',$data['to']); //через запятую разделяем получателей
	foreach ($to as $k=>$v) {
		$receiver = explode(' ',trim($v),2); //через пробел разделяем емейл и имя
		if (empty($receiver[1])) $receiver[1] = $receiver[0];
		$receivers[$receiver[1]] = $receiver[0];
	}
	$message->setTo($receivers); //print_r($receivers);
	if (!empty($data['replay'])) $message->setReplyTo($data['replay']); //ответить
	if (!empty($data['cc'])) $message->setCc($data['cc']); //копия
	if (!empty($data['bcc'])) $message->setBcc($data['bcc']); //скрытая копия
	if (!empty($data['return'])) $message->setReturnPath($data['return']); //если письмо не пришло то куда отправить
	if (empty($data['type'])) $data['type'] = 'text/html'; //тип текста, может быть еще 'text/plain'
	if (empty($data['charset'])) $data['charset'] = 'UTF-8';
	$message -> setBody($data['text'],$data['type'],$data['charset']);
	if (!empty($data['files'])) {
		foreach ($data['files'] as $k=>$v) if (is_file($v)) {
			$message->attach(
				Swift_Attachment::fromPath($v)->setFilename($k)
			);
		}
	}
	// Send the message
	return $mailer->send($message);
}

/**
 * отправка email - старая функция
 * @param string $sender - отправитель
 * @param string $receiver - получатель
 * @param string $subject - тема письма
 * @param string $text - текст письма
 * @param string $reply - кому ответить
 * @param array $files - массив файлов array('название файла'=>'путь к файлу','название файла'=>'путь к файлу')
 * @return bool - отправлено или нет
 *
 */
function email2 ($sender,$receiver,$subject,$text,$reply=false,$files = array()) {
	$subject = '=?UTF-8?B?'.base64_encode(filter_var($subject)).'?=';
	$sitename = $_SERVER['SERVER_NAME'];
	$sitename = '=?UTF-8?B?'.base64_encode(filter_var($sitename, FILTER_SANITIZE_STRING )).'?=';
	//без файлов
	$headers = "MIME-Version: 1.0".PHP_EOL;
	//если письма не доходят то отправителем надо ставить емейл который добавлен на сервере
	$headers.= "From: ".$sitename." <".$sender.">".PHP_EOL;
	$headers.= "Return-path: ".$sender.PHP_EOL;
	if ($reply) $headers.= "Reply-To: ".$reply.PHP_EOL;
	$headers.= "X-Mailer: PHP/".phpversion().PHP_EOL;
	if (!is_array($files) OR count($files)==0) {
		$headers .= "Content-Type: text/html; charset=UTF-8".PHP_EOL;
		$multipart = $text;
	}
	else {
		$boundary = "--".md5(uniqid(time()));
		$headers.="Content-Type: multipart/mixed; boundary=\"".$boundary."\"".PHP_EOL;
		$multipart = "--".$boundary.PHP_EOL;
		$multipart.= "Content-Type: text/html; charset=UTF-8".PHP_EOL;
		$multipart.= "Content-Transfer-Encoding: base64".PHP_EOL.PHP_EOL;
		$text = chunk_split(base64_encode($text)).PHP_EOL.PHP_EOL;
		$multipart.= stripslashes($text);
		//$count = count($files);
		foreach($files as $k=>$v) if (is_file($v)){
			$fp = fopen($v, "r");
			if ($fp) {
				$content = fread($fp, filesize($v));
				$multipart.= "--".$boundary.PHP_EOL;
				$multipart.= 'Content-Type: application/octet-stream'.PHP_EOL;
				$multipart.= 'Content-Transfer-Encoding: base64'.PHP_EOL;
				$multipart.= 'Content-Disposition: attachment; filename="=?UTF-8?B?'.base64_encode(filter_var($k,FILTER_SANITIZE_STRING )).'?="'.PHP_EOL.PHP_EOL;
				$multipart.= chunk_split(base64_encode($content)).PHP_EOL;
			}
			fclose($fp);
		}
		$multipart.= "--".$boundary."--".PHP_EOL;
	}
	$receivers = explode(',',$receiver);
	$return = true;
	foreach ($receivers as $k=>$v) {
		if ($k>0) sleep(1); //делаем паузу перед отправлением второго письма
		$return = mail(trim($v),$subject,$multipart,$headers) ? $return : false;
	}
	//возвращаем false если хотя бы одно письмо не отправлено
	return $return;
}