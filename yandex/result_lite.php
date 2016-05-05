<?php
$secret = "secret_key"; // секретный ключ yandex money

$y_codepro = filter_input(0,'codepro');
$y_label = (int) filter_input(0,'label'); // метка платежа, сюда мы ранее занесли ID платежа, когда формировали форму оплаты
$y_sha1_hash = filter_input(0,'sha1_hash');
$y_notification_type = filter_input(0,'notification_type');
$y_amount = (float) filter_input(0,'amount'); // сумма, которая была получена продавцом
$y_operation_id = filter_input(0,'operation_id');
$y_datetime = filter_input(0,'datetime');
$y_sender = filter_input(0,'sender'); // кошелек отправителя (покупателя)
$y_currency = '643';

// если оплата с протекцией, то отфутболиваем
if($y_codepro != 'false') { exit; }

// создаем sha1 хэш из пришедших нам параметров и нашего секретного ключа
$sha1_hash = sha1("{$y_notification_type}&{$y_operation_id}&{$y_amount}&{$y_currency}&{$y_datetime}&{$y_sender}&{$y_codepro}&{$secret}&{$y_label}");

// если пришедший в переменной $_POST хэш не равен нашему, то отфутболиваем
if ($sha1_hash != $y_sha1_hash) { exit; }

// платеж прошел нормально
// здесь какой то код