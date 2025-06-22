<?php
// src/modules/login/login_view.php (تصميم Tabler الاحترافي)
$site_name = 'نظام إدارة الأملاك'; // يمكنك جلب هذا من قاعدة البيانات لاحقًا
?>
<!doctype html>
<html lang="ar" dir="rtl">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>تسجيل الدخول - <?= htmlspecialchars($site_name) ?></title>
    <link href="./assets/css/tabler.rtl.min.css" rel="stylesheet"/>
    <style>
      body {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        background-color: #f5f7fb;
      }
    </style>
  </head>
  <body class="d-flex flex-column">
    <div class="page page-center">
      <div class="container container-tight py-4">
        <div class="text-center mb-4">
          <a href="." class="navbar-brand navbar-brand-autodark">
            <img src="./assets/static/logo.svg" height="36" alt="">
          </a>
        </div>
        <div class="card card-md">
          <div class="card-body">
            <h2 class="h2 text-center mb-4">تسجيل الدخول إلى حسابك</h2>
            <?php if (isset($_SESSION['login_error'])): ?>
                <div class="alert alert-danger" role="alert">
                    <div class="d-flex">
                        <div><i class="icon ti ti-alert-circle me-2"></i></div>
                        <div><?= $_SESSION['login_error']; unset($_SESSION['login_error']); ?></div>
                    </div>
                </div>
            <?php endif; ?>
            <form action="index.php?page=handle_login" method="post" autocomplete="off">
              <div class="mb-3">
                <label class="form-label">اسم المستخدم</label>
                <input type="text" name="username" class="form-control" placeholder="ادخل اسم المستخدم" required>
              </div>
              <div class="mb-2">
                <label class="form-label">كلمة المرور</label>
                <input type="password" name="password" class="form-control" placeholder="كلمة المرور الخاصة بك" required>
              </div>
              <div class="form-footer">
                <button type="submit" class="btn btn-primary w-100">دخول</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <script src="./assets/js/tabler.min.js" defer></script>
  </body>
</html>