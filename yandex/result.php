<?php
require_once __DIR__ . '/config.db.php'; // подключаем базу данных (у меня это goDB [http://godb.ru])
$secret = "secret_key"; // секретный ключ yandex money
$percent = 4; // процент, на который мы увеличиваем размер платежа (комиссия на плечах покупателя)
$comission = 0.5; // процент, который вычитает yandex с получателя

// получаем значение переменных из массива $_POST от yandex'a
function get_filter_input_post($var) {
    return filter_input(0,$var);
}

$y_codepro = get_filter_input_post('codepro');
$y_label = (int) get_filter_input_post('label'); // метка платежа, сюда мы ранее занесли ID платежа, когда формировали форму оплаты
$y_sha1_hash = get_filter_input_post('sha1_hash');
$y_notification_type = get_filter_input_post('notification_type');
$y_amount = (float) get_filter_input_post('amount'); // сумма, которая была получена продавцом
$y_operation_id = get_filter_input_post('operation_id');
$y_datetime = get_filter_input_post('datetime');
$y_sender = get_filter_input_post('sender'); // кошелек отправителя (покупателя)

// если оплата с протекцией, то отфутболиваем
if($y_codepro != 'false') {
	header("HTTP/1.1 001 Bad Request");
	exit;
}

$cost = $db->query('SELECT money FROM add_balance WHERE id=?i',array($y_label),'el'); // сумма пополнения, указанная покупателем

// если это прямой платеж с кошелька, то процент равен 4
//if($y_notification_type == 'p2p-incoming') {
//	$percent = 4;
//}

// если это платеж с карты, то процент равен 5
if($y_notification_type == 'card-incoming') {
	$percent = 5;
        $comission = 2;
}

$withdraw_amount = $cost+($cost/100)*$percent; // сумма, которую должен был оплатить покупатель (можно еще сравнить с 'withdraw_amount' параметром, который тоже посылает обработчику yandex)
$amount = round($withdraw_amount-($withdraw_amount/100)*$comission,2); // получаем сумму, которая должна была поступить на наш кошелек

// если полученная нами сумма не равна сумме из пришедшей $_POST переменной, то отфутболиваем
if($y_amount != $amount) {
	header("HTTP/1.1 002 Bad Request");
	exit;
}

// создаем sha1 хэш из пришедших нам параметров и нашего секретного ключа
$str = "{$y_notification_type}&{$y_operation_id}&{$y_amount}&643&{$y_datetime}&{$y_sender}&{$y_codepro}&{$secret}&{$y_label}";
$sha1_hash = sha1($str);

// если пришедший в переменной $_POST хэш не равен нашему, то отфутболиваем
if ($sha1_hash != $y_sha1_hash) {
  header("HTTP/1.1 003 Bad Request");
  exit;
}

// платеж прошел нормально
// здесь какой то код
header("HTTP/1.1 200 OK");
exit;