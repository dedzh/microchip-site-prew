<?php
/**
 * Copyright (c) 2026 Темирова Ангелина. All rights reserved.
 * Viewing and familiarization only. Any other use is prohibited.
 * Contact: temirovaangelina00@gmail.com
 */

header('Content-Type: application/json');
// Импортируем классы PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Функция для логирования
function debug_log($message)
{
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    file_put_contents($logDir . '/debug.log', date('Y-m-d H:i:s') . ' ' . $message . PHP_EOL, FILE_APPEND);
}
debug_log('Запрос получен. POST: ' . print_r($_POST, true));

// Секретный ключ reCAPTCHA для домена (замените на настоящий)
$recaptchaSecret = '1234566789'; // тестовый, нужно заменить!

// Получаем данные из POST
$name = trim($_POST['name'] ?? '');
$company = trim($_POST['company'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$messageText = trim($_POST['message'] ?? '');
$captcha = $_POST['g-recaptcha-response'] ?? '';

// Проверка обязательных полей
if (empty($name) || empty($phone) || empty($email) || empty($messageText)) {
    echo json_encode(['success' => false, 'error' => 'Заполните все обязательные поля']);
    exit;
}

// Проверка капчи
if (empty($captcha)) {
    echo json_encode(['success' => false, 'error' => 'Пожалуйста, подтвердите, что вы не робот']);
    exit;
}

// Верификация капчи через Google
$url = '';
$data = [
    'secret' => $recaptchaSecret,
    'response' => $captcha,
    'remoteip' => $_SERVER['REMOTE_ADDR']
];

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);
if ($result === false) {
    debug_log('Ошибка соединения с Google reCAPTCHA');
    echo json_encode(['success' => false, 'error' => 'Ошибка проверки капчи. Попробуйте позже.']);
    exit;
}

$responseKeys = json_decode($result, true);
if (intval($responseKeys['success']) !== 1) {
    debug_log('Капча не пройдена: ' . print_r($responseKeys, true));
    echo json_encode(['success' => false, 'error' => 'Капча не пройдена. Попробуйте ещё раз.']);
    exit;
}

// Сохранение заявки в файл
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
$logEntry = date('Y-m-d H:i:s') . " | Имя: $name | Компания: $company | Телефон: $phone | Email: $email | Сообщение: " . str_replace("\n", " ", $messageText) . PHP_EOL;
file_put_contents($logDir . '/requests.log', $logEntry, FILE_APPEND | LOCK_EX);

// Отправка на почту через PHPMailer
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;

    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $mail = new PHPMailer(true);
        try {
            // Настройки SMTP поставить реальную почту 
            $mail->isSMTP();
            $mail->Host       = 'mail.hosting.reg.ru';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'infoexample@mail.ru';
            $mail->Password   = "password";
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('infoexample@mail.ru', 'Ромашка');
            $mail->addAddress('infoexample@mail.ru');

            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Новая заявка с сайта';
            $mail->Body    = "Имя: $name\nКомпания: $company\nТелефон: $phone\nEmail: $email\nСообщение:\n$messageText";
            $mail->isHTML(false);

            if ($mail->send()) {
                debug_log('✓ Письмо успешно отправлено');
            } else {
                debug_log('✗ Ошибка отправки: ' . $mail->ErrorInfo);
                file_put_contents($logDir . '/mail_errors.log', date('Y-m-d H:i:s') . ' ' . $mail->ErrorInfo . PHP_EOL, FILE_APPEND);
            }
        } catch (Exception $e) {
            debug_log("Исключение PHPMailer: {$e->getMessage()}");
            file_put_contents($logDir . '/mail_errors.log', date('Y-m-d H:i:s') . ' ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
        }
    } else {
        debug_log('Классы PHPMailer не найдены, хотя vendor/autoload.php существует');
    }
} else {
    debug_log('vendor/autoload.php не найден. Установите PHPMailer через Composer.');
}
echo json_encode(['success' => true, 'message' => 'Заявка принята. Менеджер свяжется с вами в течение 15 минут.']);