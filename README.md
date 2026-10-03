# Class Wall — شاشة الفصل التفاعلية

الطلاب بيعملوا Scan للـ QR من موبايلاتهم، ويكتبوا إجاباتهم، فتظهر على البروجيكتور في نفس اللحظة.
التطبيق كله مكتوب بـ PHP و MySQL، وبيستخدم كل اللي اتشرح في Unit 19.

## التشغيل (5 دقايق)

1. انسخي فولدر `class-wall` كله جوه `C:\xampp\htdocs\`
2. من XAMPP Control Panel شغّلي **Apache** و **MySQL**
3. افتحي `http://localhost/class-wall/setup.php` **مرة واحدة بس**. هيعمل الداتابيز والجداول لوحده
4. افتحي `http://localhost/class-wall/wall.php` على البروجيكتور، ودوسي ⛶ عشان تبقى Full screen
5. الطلاب يعملوا Scan للـ QR اللي على الشاشة

صفحة التحكم: `http://localhost/class-wall/admin.php` (الرقم السري الافتراضي `1234`، غيّريه من `config.php`).
منها تقدري تغيّري السؤال، وتمسحي إجابة، أو تمسحي الشاشة كلها.

## لازم تجرّبي من موبايلك قبل الحصة

- **الموبايلات لازم تكون على نفس الـ Wi-Fi اللي عليه جهازك.**
- **لو الموبايل مش بيفتح الصفحة،** فالسبب غالباً Windows Firewall. افتحي
  *Windows Security ← Firewall ← Allow an app through firewall* وعلّمي على **Apache HTTP Server** في خانة Private.
  واتأكدي إن شبكة الـ Wi-Fi متسجلة على الجهاز **Private** مش Public.
- **لو الـ QR طالع بعنوان غلط،** أو على الشاشة مكتوب تحذير أحمر:
  افتحي `cmd` واكتبي `ipconfig`، وخدي الرقم اللي جنب **IPv4 Address** (زي `192.168.1.15`).
  وبعدين اكتبيه في `config.php`:
  `define('PUBLIC_URL', 'http://192.168.1.15/class-wall/');`
- **لو Wi-Fi المدرسة بيمنع الأجهزة تشوف بعض،** اعملي Hotspot من موبايلك، ووصّلي عليه اللابتوب والطلاب.
- **من غير موبايلات خالص؟** الطلاب يفتحوا نفس العنوان من أجهزة المعمل.

## خريطة الكود ← الدروس

| الملف | بيعمل إيه | الدروس اللي فيه |
| --- | --- | --- |
| `config.php` | الإعدادات (constants) | 1: Constants |
| `db.php` | الاتصال بالداتابيز + functions مساعدة | 3: Functions · 4: require_once و htmlspecialchars · 5: mysqli_connect |
| `setup.php` | بيعمل الداتابيز والجداول | 3: Arrays · 2: foreach · 5: CREATE TABLE |
| `index.php` | صفحة الطالب (الفورم) | 4: POST و GET و Validation · 5: INSERT بـ prepared statement |
| `api.php` | بيرجّع الإجابات JSON للشاشة | 5: SELECT · 2: while · 3: arrays |
| `wall.php` | الشاشة اللي على البروجيكتور | PHP + JavaScript بيسأل api.php كل ثانيتين |
| `admin.php` | صفحة التحكم | 2: switch...case · 5: UPDATE و DELETE · Sessions |

كل سطر مهم في الكود عليه تعليق `📘 الدرس X` بيقول هو تبع أنهي درس.

## أفكار للحصة

- **أول الحصة:** «Describe PHP in one word»، وكده الكل يشارك في أول دقيقة.
- **آخر الحصة:** «What is still confusing?»، وده سؤال خروج جاهز يتعرض قدامك على طول.
- **تحدّي للطلاب الشاطرين:** يفتحوا `index.php` ويحاولوا يبعتوا `<script>`. هيلاقوه اتعرض كنص عادي، واسأليهم ليه.
- **مشروع للكارت الأحمر:** يضيفوا زرار Like لكل إجابة، يعني عمود `likes` و UPDATE.
