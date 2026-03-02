<?php
<<<<<<< HEAD
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'path/to/PHPMailer/src/Exception.php';  // Correcting the path
require 'path/to/PHPMailer/src/PHPMailer.php';
require 'path/to/PHPMailer/src/SMTP.php';

$recaptchaResponse = $_POST['g-recaptcha-response'];  // Ensuring correct parameter name

$mail = new PHPMailer(true);
try {
    //Server settings
    $mail->SMTPDebug = 0;                    // Enable verbose debug output
    $mail->isSMTP();                           // Set mailer to use SMTP
    $mail->Host       = 'smtp.example.com';      // Specify main and backup SMTP servers
    $mail->SMTPAuth   = true;                  // Enable SMTP authentication
    $mail->Username   = 'your_email@example.com'; // SMTP username
    $mail->Password   = 'your_password';      // SMTP password
    $mail->SMTPSecure = 'tls';                 // Enable TLS encryption, `ssl` also accepted
    $mail->Port       = 587;                   // TCP port to connect to

    //Recipients
    $mail->setFrom('from@example.com', 'Mailer');
    $mail->addAddress('recipient@example.com', 'Joe User'); // Add a recipient

    // Content
    $mail->isHTML(true);                      // Set email format to HTML
    $mail->Subject = 'Here is the subject';
    $mail->Body    = 'This is the HTML message body <b>in bold!</b>';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;  // Proper error handling
}
?>
=======
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

$recaptchaSecret = '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe';


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
$url = 'https://www.google.com/recaptcha/api/siteverify';
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
    if (!$isLocalhost) {
        echo json_encode(['success' => false, 'error' => 'Ошибка проверки капчи. Попробуйте позже.']);
        exit;
    }
}


$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
$logEntry = date('Y-m-d H:i:s') . " | Имя: $name | Компания: $company | Телефон: $phone | Email: $email | Сообщение: " . str_replace("\n", " ", $messageText) . PHP_EOL;
file_put_contents($logDir . '/requests.log', $logEntry, FILE_APPEND | LOCK_EX);


$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;


    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $mail = new PHPMailer(true);
        try {
            // Настройки SMTP 
            $mail->isSMTP();
            $mail->Host       = 'mail.hosting.reg.ru';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'info@macrochip.ru';
            $mail->Password   = 'Ksenya198210_10'; //засунь это в константы в файлах
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // Отправитель и получатель
            $mail->setFrom('info@macrochip.ru', 'Макрочип');
            $mail->addAddress('info@macrochip.ru');

            // Контент
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Новая заявка с сайта Макрочип';
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
>>>>>>> 519b5e1 (update)
