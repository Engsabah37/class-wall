<?php
// ===============================================
//  admin.php — صفحة التحكم بتاعتك (محمية بالرقم السري)
// ===============================================
session_start();
require_once __DIR__ . '/db.php';

$notice = '';

// تسجيل الدخول
if (isset($_POST['pin'])) {
    if ($_POST['pin'] === ADMIN_PIN) {
        $_SESSION['wall_admin'] = true;
        header('Location: admin.php');
        exit;
    }
    $notice = 'الرقم السري غلط';
}
if (isset($_GET['logout'])) {
    unset($_SESSION['wall_admin']);
    header('Location: admin.php');
    exit;
}
$logged = !empty($_SESSION['wall_admin']);

if ($logged && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $conn = db();

    // 📘 الدرس 2 — switch...case
    switch ($_POST['action']) {
        case 'question':
            // 📘 الدرس 5 — UPDATE
            $q = trim($_POST['question'] ?? '');
            if ($q !== '' && mb_strlen($q) <= 200) {
                $stmt = mysqli_prepare($conn, "UPDATE settings SET question = ? WHERE id = 1");
                mysqli_stmt_bind_param($stmt, "s", $q);
                mysqli_stmt_execute($stmt);
            }
            break;

        case 'delete':
            // 📘 الدرس 5 — DELETE بـ WHERE (من غيرها كل الإجابات تتمسح!)
            $id = (int) ($_POST['id'] ?? 0);
            $stmt = mysqli_prepare($conn, "DELETE FROM messages WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            break;

        case 'clear':
            mysqli_query($conn, "DELETE FROM messages");   // هنا قاصدين نمسح الكل
            break;
    }
    header('Location: admin.php');   // عشان الـ refresh ميكررش الأمر
    exit;
}

$messages = [];
if ($logged) {
    $result = mysqli_query(db(), "SELECT id, name, body, created_at FROM messages ORDER BY id DESC");
    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = $row;
    }
}

// أسئلة جاهزة للحصص — 📘 الدرس 3: indexed array
$presets = [
    'What did you learn today?',
    'What is still confusing?',
    'Describe PHP in one word',
    'GET or POST for a login form? Why?',
    'What will this print? echo 5 + 5 . "5";',
    'Write one rule for naming variables',
];
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin · <?= e(CLASS_NAME) ?></title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body class="page">
<?php if (!$logged): ?>
  <main class="panel narrow">
    <p class="kicker">ADMIN</p>
    <h1>صفحة التحكم</h1>
    <form method="post">
      <label for="pin">الرقم السري</label>
      <input id="pin" name="pin" type="password" inputmode="numeric" dir="ltr" autofocus>
      <?php if ($notice): ?><p class="error"><?= e($notice) ?></p><?php endif; ?>
      <button class="btn" type="submit">دخول</button>
    </form>
  </main>
<?php else: ?>
  <main class="panel wide">
    <div class="row-between">
      <div><p class="kicker">ADMIN</p><h1>صفحة التحكم</h1></div>
      <div class="links">
        <a class="btn" href="wall.php" target="_blank">الشاشة ↗</a>
        <a class="btn ghost" href="index.php" target="_blank">صفحة الطالب ↗</a>
        <a class="btn ghost" href="?logout=1">خروج</a>
      </div>
    </div>

    <section class="block">
      <h2>السؤال اللي على الشاشة</h2>
      <form method="post" class="inline">
        <input type="hidden" name="action" value="question">
        <input name="question" dir="auto" maxlength="200" value="<?= e(get_question()) ?>">
        <button class="btn" type="submit">غيّر</button>
      </form>
      <div class="presets">
        <?php foreach ($presets as $p): ?>
          <form method="post">
            <input type="hidden" name="action" value="question">
            <input type="hidden" name="question" value="<?= e($p) ?>">
            <button class="chip" type="submit" dir="ltr"><?= e($p) ?></button>
          </form>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="block">
      <div class="row-between">
        <h2>الإجابات (<?= count($messages) ?>)</h2>
        <form method="post" onsubmit="return confirm('متأكدة؟ كل الإجابات هتتمسح');">
          <input type="hidden" name="action" value="clear">
          <button class="btn danger" type="submit">امسحي الشاشة كلها</button>
        </form>
      </div>
      <?php if (!$messages): ?>
        <p class="muted">مفيش إجابات لسه.</p>
      <?php else: ?>
        <table class="list">
          <tr><th>#</th><th>الاسم</th><th>الإجابة</th><th>الوقت</th><th></th></tr>
          <?php foreach ($messages as $m): ?>
            <tr>
              <td class="muted"><?= (int) $m['id'] ?></td>
              <td dir="auto"><?= e($m['name']) ?></td>
              <td dir="auto"><?= e($m['body']) ?></td>
              <td class="muted" dir="ltr"><?= e(date('H:i', strtotime($m['created_at']))) ?></td>
              <td>
                <form method="post">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                  <button class="x" type="submit" title="امسح">✕</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>
    </section>
  </main>
<?php endif; ?>
</body>
</html>
