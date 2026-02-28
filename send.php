<?php
// send.php - обработчик формы обратной связи
header('Content-Type: application/json');

// Функция для логирования (поможет при отладке)
function debug_log($message)
{
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    file_put_contents($logDir . '/debug.log', date('Y-m-d H:i:s') . ' ' . $message . PHP_EOL, FILE_APPEND);
}
debug_log('Запрос получен. POST: ' . print_r($_POST, true));

// Определяем, локальный ли это сервер
$isLocalhost = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || $_SERVER['REMOTE_ADDR'] === '127.0.0.1');

// --- НАСТРОЙКИ reCAPTCHA ---
if ($isLocalhost) {
    // Тестовый ключ Google (всегда пропускает)
    $recaptchaSecret = '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe';
} else {
    // ЗАМЕНИТЕ НА ВАШ РЕАЛЬНЫЙ СЕКРЕТНЫЙ КЛЮЧ (из консоли Google)
    $recaptchaSecret = '6LcOnnksAAAAAKv4e...'; // вставьте сюда ваш секретный ключ
}

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

// Верификация капчи через Google (используем file_get_contents с контекстом, но можно и cURL)
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
    // На localhost просто пропускаем
} else {
    $responseKeys = json_decode($result, true);
    if (intval($responseKeys['success']) !== 1) {
        debug_log('Капча не пройдена: ' . print_r($responseKeys, true));
        echo json_encode(['success' => false, 'error' => 'Капча не пройдена. Попробуйте ещё раз.']);
        exit;
    }
}

// ========== СОХРАНЕНИЕ ЗАЯВКИ В ФАЙЛ (ВСЕГДА) ==========
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
$logEntry = date('Y-m-d H:i:s') . " | Имя: $name | Компания: $company | Телефон: $phone | Email: $email | Сообщение: " . str_replace("\n", " ", $messageText) . PHP_EOL;
file_put_contents($logDir . '/requests.log', $logEntry, FILE_APPEND | LOCK_EX);

// ========== (ОПЦИОНАЛЬНО) ОТПРАВКА НА ПОЧТУ ==========
// Если вы хотите отправлять письма, раскомментируйте этот блок и настройте SMTP.
// Для продакшена рекомендуется использовать внешний SMTP (например, Gmail или Яндекс).
// ВАЖНО: не храните пароль в коде – используйте переменные окружения или конфиг вне public_html.


// Подключаем PHPMailer (укажите правильный путь к папке PHPMailer)
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
    // Настройки SMTP (пример для Gmail)
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'temirovaangelina00@gmail.com'; // ваш Gmail
    $mail->Password   = 'lsjy nljg gpzs pbzz';        // НЕ обычный пароль, а пароль приложения!
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // или 'ssl' для порта 465
    $mail->Port       = 587;

    // Отправитель и получатель
    $mail->setFrom('temirovaangelina00@gmail.com', 'dedushkazh'); // должен быть тот же, что в Username
    $mail->addAddress('temirovaangelina00@gmail.com'); // на какой email отправлять

    // Контент
    $mail->CharSet = 'UTF-8';
    $mail->Subject = 'Новая заявка с сайта Макрочип';
    $mail->Body    = "Имя: $name\nКомпания: $company\nТелефон: $phone\nEmail: $email\nСообщение:\n$messageText";

    $mail->send();
    debug_log('Письмо успешно отправлено через SMTP');
} catch (Exception $e) {
    debug_log("Ошибка отправки: {$mail->ErrorInfo}");
    file_put_contents($logDir . '/mail_errors.log', date('Y-m-d H:i:s') . ' ' . $mail->ErrorInfo . PHP_EOL, FILE_APPEND);
    // Не выводим ошибку пользователю, чтобы не раскрывать детали
}


// ========== ОТВЕТ ПОЛЬЗОВАТЕЛЮ ==========
echo json_encode(['success' => true, 'message' => 'Заявка принята. Менеджер свяжется с вами в течение 15 минут.']);
