<?php
// ===============================================
//  wall.php — الشاشة اللي على البروجيكتور
// ===============================================
require_once __DIR__ . '/db.php';
$question = get_question();
$url = student_url();
$isLocalOnly = is_loopback(parse_url($url, PHP_URL_HOST) ?? '');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e(CLASS_NAME) ?></title>
  <link rel="stylesheet" href="assets/style.css">
  <script src="assets/qrcode.js"></script>
</head>
<body class="wall">
  <header class="wall-head">
    <div class="q-area">
      <p class="kicker"><?= e(CLASS_NAME) ?> · LIVE <span class="dot" id="dot"></span></p>
      <h1 id="question" dir="auto"><?= e($question) ?></h1>
    </div>
    <aside class="join card">
      <div id="qr" class="qr"></div>
      <div class="join-txt">
        <b>Scan to join</b>
        <span class="ar">امسح الكود وابعت إجابتك</span>
        <code id="url"><?= e($url) ?></code>
        <?php if ($isLocalOnly): ?>
          <small class="warn">⚠ العنوان ده بيشتغل على جهازك بس — اكتبي الـ IP في config.php</small>
        <?php endif; ?>
      </div>
      <div class="count"><span id="count">0</span><small>answers</small></div>
    </aside>
  </header>

  <main id="board" class="board"></main>
  <div id="empty" class="empty">
    <b>Waiting for the first answer…</b>
    <span class="ar">مستنيين أول إجابة</span>
  </div>

  <nav class="tools">
    <button id="soundBtn" title="Sound">🔇</button>
    <button id="fsBtn" title="Fullscreen">⛶</button>
    <a href="admin.php" title="Admin">⚙</a>
  </nav>

<script>
// ---------- QR ----------
const STUDENT_URL = <?= json_encode($url) ?>;
const qr = qrcode(0, 'M');
qr.addData(STUDENT_URL);
qr.make();
document.getElementById('qr').innerHTML = qr.createSvgTag({ cellSize: 6, margin: 0, scalable: true });

// ---------- cards ----------
const board = document.getElementById('board');
const shown = new Map();          // id → element
let first = true;

function makeCard(m, delay) {
  const card = document.createElement('article');
  card.className = 'note c' + (m.color % 6);
  const tilt = ((m.id * 37) % 7) - 3;               // ميلان ثابت لكل كارت
  card.style.setProperty('--r', (tilt * 0.8) + 'deg');
  card.style.animationDelay = delay + 'ms';
  const len = [...m.body].length;
  card.dataset.size = len < 40 ? 'l' : len < 110 ? 'm' : 's';

  const p = document.createElement('p');
  p.dir = 'auto';
  p.textContent = m.body;                            // textContent = آمن ضد XSS
  const who = document.createElement('footer');
  who.dir = 'auto';
  who.textContent = '— ' + m.name;
  card.append(p, who);
  return card;
}

async function poll() {
  try {
    const res = await fetch('api.php', { cache: 'no-store' });
    const data = await res.json();
    if (data.error) throw new Error(data.message);
    document.getElementById('dot').classList.remove('off');

    const q = document.getElementById('question');
    if (q.textContent !== data.question) {
      q.textContent = data.question;
      q.classList.remove('flash'); void q.offsetWidth; q.classList.add('flash');
    }
    document.getElementById('count').textContent = data.count;

    // اللي اتمسح من صفحة التحكم يختفي من الشاشة
    const ids = new Set(data.messages.map(m => m.id));
    for (const [id, el] of shown) {
      if (!ids.has(id)) { el.classList.add('gone'); setTimeout(() => el.remove(), 400); shown.delete(id); }
    }
    // الجديد يظهر أول واحد
    let added = 0;
    data.messages.forEach((m) => {
      if (shown.has(m.id)) return;
      const card = makeCard(m, first ? added * 60 : 0);
      board.prepend(card);
      shown.set(m.id, card);
      added++;
    });
    if (added && !first) blip();
    first = false;
    document.getElementById('empty').style.display = shown.size ? 'none' : 'flex';
  } catch (err) {
    document.getElementById('dot').classList.add('off');
  }
}
poll();
setInterval(poll, 2000);

// ---------- sound ----------
let audio = null;
document.getElementById('soundBtn').onclick = (e) => {
  audio = audio ? null : new (window.AudioContext || window.webkitAudioContext)();
  e.currentTarget.textContent = audio ? '🔊' : '🔇';
  if (audio) blip();
};
function blip() {
  if (!audio) return;
  const o = audio.createOscillator(), g = audio.createGain(), t = audio.currentTime;
  o.type = 'sine';
  o.frequency.setValueAtTime(660, t);
  o.frequency.exponentialRampToValueAtTime(1320, t + 0.08);
  g.gain.setValueAtTime(0.0001, t);
  g.gain.exponentialRampToValueAtTime(0.25, t + 0.02);
  g.gain.exponentialRampToValueAtTime(0.0001, t + 0.25);
  o.connect(g).connect(audio.destination);
  o.start(t); o.stop(t + 0.3);
}

// ---------- fullscreen ----------
document.getElementById('fsBtn').onclick = () => {
  if (document.fullscreenElement) document.exitFullscreen();
  else document.documentElement.requestFullscreen();
};
</script>
</body>
</html>
