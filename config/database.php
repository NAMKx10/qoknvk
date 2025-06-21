<?php
// إعدادات قاعدة البيانات الخاصة بك
define('DB_HOST', 'localhost');
define('DB_USER', '0'); // <-- قم بتغيير هذه بعد الاختبار إلى اسم المستخدم الجديد!
define('DB_PASS', '0'); // <-- قم بتغيير هذه بعد الاختبار إلى كلمة المرور الجديدة!
define('DB_NAME', '0'); // <-- قم بتغيير هذه بعد الاختبار إلى اسم قاعدة البيانات الجديدة!
define('DB_CHARSET', 'utf8mb4');

// إنشاء اتصال PDO (أكثر أماناً)
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    // في بيئة الإنتاج، لا تعرض تفاصيل الخطأ للمستخدم
    // يمكنك تسجيل الخطأ في ملف logs بدلاً من ذلك
    die('فشل الاتصال بقاعدة البيانات. يرجى المحاولة لاحقاً.');
}

// تحميل الإعدادات من قاعدة البيانات لتكون متاحة في كل مكان
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>