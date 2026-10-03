<?php
// 📘 الدرس 4 — require_once: بنحمّل الإعدادات مرة واحدة بس
require_once __DIR__ . '/config.php';

// 📘 الدرس 5 — الاتصال بـ MySQL
// static: الاتصال بيتعمل مرة واحدة ويتعاد استخدامه
function db()
{
    static $conn = null;
    if ($conn === null) {
        mysqli_report(MYSQLI_REPORT_OFF);
        $conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if (!$conn) {
            show_db_error(mysqli_connect_error());
        }
        mysqli_set_charset($conn, 'utf8mb4');
    }
    return $conn;
}

// 📘 الدرس 4 — الأمان: أي حاجة كتبها المستخدم بتعدّي على htmlspecialchars قبل ما تتعرض
function e($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

// 📘 الدرس 3 — function بترجّع قيمة
function get_question()
{
    $result = mysqli_query(db(), "SELECT question FROM settings WHERE id = 1");
    $row = $result ? mysqli_fetch_assoc($result) : null;
    return $row ? $row['question'] : '';
}

// العنوان اللي الطلاب هيفتحوه من موبايلاتهم (بيتحط في الـ QR)
function student_url()
{
    if (PUBLIC_URL !== '') {
        return PUBLIC_URL;
    }
    $host = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
    if (is_loopback($host)) {
        $lan = gethostbyname(gethostname());
        if ($lan && $lan !== gethostname()) {
            $host = $lan;
        }
    }
    if (strpos($host, ':') !== false) {
        $host = '[' . $host . ']';
    }
    $port = (int) ($_SERVER['SERVER_PORT'] ?? 80);
    $portPart = ($port === 80) ? '' : ':' . $port;
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    return 'http://' . $host . $portPart . $dir . '/';
}

function is_loopback($host)
{
    $host = trim($host, '[]');
    return $host === '::1' || strpos($host, '127.') === 0 || $host === 'localhost';
}

function show_db_error($message)
{
    http_response_code(500);
    if (defined('IS_API')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'database', 'message' => $message]);
        exit;
    }
    echo '<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<link rel="stylesheet" href="assets/style.css"><title>' . e(CLASS_NAME) . '</title></head>'
        . '<body class="page"><main class="panel narrow">'
        . '<p class="kicker">DATABASE</p><h1>مش قادر أوصل لقاعدة البيانات</h1>'
        . '<p>اتأكدي إن <b>MySQL</b> شغال من XAMPP Control Panel، وبعدين افتحي '
        . '<a href="setup.php">setup.php</a> مرة واحدة عشان يجهّز الجداول.</p>'
        . '<pre class="err" dir="ltr">' . e($message) . '</pre></main></body></html>';
    exit;
}
