<?php
// logout.php

// بدء الجلسة
session_start();

// إلغاء كل متغيرات الجلسة
$_SESSION = [];

// تدمير الجلسة وملفات تعريف الارتباط إن وُجدت
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}
session_destroy();

// إعادة التوجيه إلى شاشة الدخول
header("Location: index.php?page=login");
exit();

/*
ملاحظات:
- الكود يُنهي الجلسة بشكل كامل ويزيل جميع متغيرات وتعريفات الجلسة للمستخدم.
- يتأكد من حذف ملفات تعريف الارتباط الخاصة بالجلسة (session cookie) إذا كانت مفعلة.
- بعد إنهاء الجلسة، يعيد التوجيه تلقائياً إلى صفحة الدخول.
- الكود آمن ويفضل استخدامه عند تسجيل الخروج في تطبيقات PHP.
*/
