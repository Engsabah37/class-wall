<?php
// افتحي الصفحة دي مرة واحدة بس: بتعمل الداتابيز والجداول لوحدها
require_once __DIR__ . '/config.php';
mysqli_report(MYSQLI_REPORT_OFF);

$steps = [];
$ok = true;

$conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS);
if (!$conn) {
    $ok = false;
    $steps[] = ['bad', 'مش قادر أوصل لـ MySQL — شغّليه من XAMPP Control Panel', mysqli_connect_error()];
} else {
    mysqli_set_charset($conn, 'utf8mb4');

    // 📘 الدرس 5 — أوامر SQL في array، وبنلف عليها بـ foreach
    $queries = [
        'إنشاء قاعدة البيانات ' . DB_NAME =>
            "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci",
        'اختيار قاعدة البيانات' =>
            "USE `" . DB_NAME . "`",
        'جدول الإعدادات settings' =>
            "CREATE TABLE IF NOT EXISTS settings (
                id INT PRIMARY KEY,
                question VARCHAR(200) NOT NULL
            )",
        'السؤال الافتراضي' =>
            "INSERT IGNORE INTO settings (id, question) VALUES (1, 'What did you learn today?')",
        'جدول الإجابات messages' =>
            "CREATE TABLE IF NOT EXISTS messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(30) NOT NULL,
                body VARCHAR(200) NOT NULL,
                color TINYINT NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
    ];

    foreach ($queries as $label => $sql) {
        if (mysqli_query($conn, $sql)) {
            $steps[] = ['ok', $label, ''];
        } else {
            $ok = false;
            $steps[] = ['bad', $label, mysqli_error($conn)];
            break;
        }
    }
}
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Setup</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body class="page">
  <main class="panel narrow">
    <p class="kicker">SETUP</p>
    <h1><?= $ok ? 'كله جاهز ✓' : 'في مشكلة' ?></h1>
    <ul class="steps">
      <?php foreach ($steps as [$state, $label, $detail]): ?>
        <li class="<?= $state ?>">
          <span class="mark"><?= $state === 'ok' ? '✓' : '✗' ?></span>
          <span><?= htmlspecialchars($label) ?></span>
          <?php if ($detail): ?><code dir="ltr"><?= htmlspecialchars($detail) ?></code><?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
    <?php if ($ok): ?>
      <div class="links">
        <a class="btn" href="wall.php">افتحي الشاشة على البروجيكتور ←</a>
        <a class="btn ghost" href="admin.php">صفحة التحكم</a>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>
