<?php
// ===============================================
//  api.php — الشاشة بتسأله كل ثانيتين: "في إجابات جديدة؟"
//  بيرجّع JSON مش HTML
// ===============================================
define('IS_API', true);
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// 📘 الدرس 5 — READ (SELECT): آخر 60 إجابة
$result = mysqli_query(db(), "SELECT id, name, body, color FROM messages ORDER BY id DESC LIMIT 60");

// 📘 الدرس 2 + 3 — while loop بتملأ array
$messages = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['id'] = (int) $row['id'];
    $row['color'] = (int) $row['color'];
    $messages[] = $row;
}

$count = mysqli_fetch_row(mysqli_query(db(), "SELECT COUNT(*) FROM messages"))[0];

echo json_encode([
    'question' => get_question(),
    'count'    => (int) $count,
    'messages' => array_reverse($messages),   // الأقدم الأول
], JSON_UNESCAPED_UNICODE);
