<?php
// ===============================================
//  صفحة الطالب — بيفتحها من الموبايل بعد ما يعمل Scan للـ QR
// ===============================================
require_once __DIR__ . '/db.php';

$errors = [];
// الصفحة بتبعت بـ JavaScript (fetch) عشان تشتغل حتى جوه معاينة الكاميرا
$isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
$name = $_COOKIE['wall_name'] ?? '';   // 📘 الدرس 1 — ?? قيمة افتراضية لو مفيش
$body = '';

// 📘 الدرس 4 — Form Handling: الفورم اتبعتت بـ POST؟
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $body = trim($_POST['body'] ?? '');

    // 📘 الدرس 4 — Server-side Validation (مش بنثق في المتصفح)
    if ($name === '') {
        $errors['name'] = 'الاسم مطلوب';
    } elseif (mb_strlen($name) > 30) {
        $errors['name'] = 'الاسم طويل… 30 حرف بالكتير';
    }

    if ($body === '') {
        $errors['body'] = 'الإجابة مطلوبة';
    } elseif (mb_strlen($body) > 200) {
        $errors['body'] = 'الإجابة طويلة… 200 حرف بالكتير';
    }

    // مفيش أخطاء؟ نحفظ في الداتابيز
    if (empty($errors)) {
        $color = rand(0, 5);   // 📘 الدرس 3 — built-in function

        // 📘 الدرس 5 — CREATE (INSERT) بـ prepared statement عشان نمنع SQL Injection
        $stmt = mysqli_prepare(db(), "INSERT INTO messages (name, body, color) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssi", $name, $body, $color);
        mysqli_stmt_execute($stmt);

        setcookie('wall_name', $name, time() + 60 * 60 * 24 * 30);
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => true]);
            exit;
        }
        header('Location: index.php?sent=1');   // عشان الـ refresh ميبعتش نفس الإجابة تاني
        exit;
    }

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'errors' => $errors], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$sent = isset($_GET['sent']);   // 📘 الدرس 4 — $_GET من الـ URL
$question = get_question();
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
  <meta name="theme-color" content="#F2ECE1">
  <title><?= e(CLASS_NAME) ?></title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body class="page phone">
  <main class="panel">
    <p class="kicker"><?= e(CLASS_NAME) ?></p>
    <h1 class="q" dir="auto"><?= e($question) ?></h1>

    <?php if ($sent): ?>
      <div class="sent">
        <div class="tick">✓</div>
        <h2>وصلت!</h2>
        <p>بص على الشاشة 👀</p>
        <a class="btn ghost" href="index.php">ابعت إجابة تانية</a>
      </div>
    <?php else: ?>
      <form id="f" method="post" action="index.php" novalidate>
        <label for="name">اسمك</label>
        <input id="name" name="name" type="text" dir="auto" maxlength="30" required
               value="<?= e($name) ?>" class="<?= isset($errors['name']) ? 'invalid' : '' ?>" autocomplete="given-name">
        <?php if (isset($errors['name'])): ?><p class="error"><?= $errors['name'] ?></p><?php endif; ?>

        <label for="body">إجابتك</label>
        <textarea id="body" name="body" dir="auto" maxlength="200" rows="4" required
                  class="<?= isset($errors['body']) ? 'invalid' : '' ?>"><?= e($body) ?></textarea>
        <div class="counter"><span id="count"><?= mb_strlen($body) ?></span>/200</div>
        <?php if (isset($errors['body'])): ?><p class="error"><?= $errors['body'] ?></p><?php endif; ?>

        <p class="error" id="netErr" hidden></p>
        <button class="btn big" type="submit" id="go">ابعت للشاشة ←</button>
      </form>
    <?php endif; ?>
  </main>

  <script>
    // عدّاد الحروف (Client-side: للراحة بس، الحماية الحقيقية في PHP فوق)
    const box = document.getElementById('body');
    if (box) box.addEventListener('input', () => {
      document.getElementById('count').textContent = box.value.length;
    });

    // الإرسال بـ fetch: بيشتغل في أي متصفح، وبيقول للطالب لو في مشكلة
    const form = document.getElementById('f');
    if (form && window.fetch) form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = document.getElementById('go');
      const net = document.getElementById('netErr');
      form.querySelectorAll('p.error:not(#netErr)').forEach(p => p.remove());
      form.querySelectorAll('.invalid').forEach(el => el.classList.remove('invalid'));
      net.hidden = true;
      btn.disabled = true; btn.textContent = 'بيبعت…';
      const ctrl = new AbortController();
      const timer = setTimeout(() => ctrl.abort(), 8000);
      try {
        const res = await fetch('index.php', {
          method: 'POST', body: new FormData(form),
          headers: { 'X-Requested-With': 'fetch' }, signal: ctrl.signal, credentials: 'same-origin'
        });
        const data = await res.json();
        if (data.ok) {
          document.querySelector('main').querySelector('form').outerHTML =
            '<div class="sent"><div class="tick">✓</div><h2>وصلت!</h2><p>بص على الشاشة 👀</p>' +
            '<a class="btn ghost" href="index.php">ابعت إجابة تانية</a></div>';
          return;
        }
        for (const [field, msg] of Object.entries(data.errors || {})) {
          const input = document.getElementById(field);
          input.classList.add('invalid');
          const p = document.createElement('p');
          p.className = 'error'; p.textContent = msg;
          (field === 'body' ? document.querySelector('.counter') : input).after(p);
        }
      } catch (err) {
        net.textContent = 'الموبايل مش قادر يوصل للجهاز… اتأكد إنك على نفس الـ Wi-Fi وجرّب تاني';
        net.hidden = false;
      } finally {
        clearTimeout(timer);
        btn.disabled = false; btn.textContent = 'ابعت للشاشة ←';
      }
    });
  </script>
</body>
</html>
