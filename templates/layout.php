<!doctype html>
<html lang="ar" dir="rtl">
  <head>
    <meta charset="utf-8"/><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/><meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title><?= htmlspecialchars($page_title ?? 'نظام إدارة الأملاك') ?></title>
    <!-- CSS files -->
    <link href="./assets/css/tabler.rtl.min.css" rel="stylesheet"/>
    <link href="./assets/css/tabler-icons.min.css" rel="stylesheet"/>
    <style> body { font-feature-settings: "cv03", "cv04", "cv11"; } .navbar-nav .nav-link-title { font-weight: 600 !important; } </style>
  </head>
  <body class="layout-fluid">
    <div class="page">
      
      <!-- BEGIN: Header -->
      <header class="navbar navbar-expand-md d-print-none" >
        <div class="container-xl">
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
            <span class="navbar-toggler-icon"></span>
          </button>
          
          <!-- ١. الشعار على اليمين -->
          <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
            <a href="index.php?page=dashboard">
              <img src="./assets/static/logo-white.svg" width="110" height="32" alt="Tabler" class="navbar-brand-image">
            </a>
          </h1>

          <!-- ٢. أيقونات الإجراءات على اليسار -->
          <div class="navbar-nav flex-row order-md-last">
            <div class="d-none d-md-flex">
              <a href="?theme=dark" class="nav-link px-0 hide-theme-dark" title="تفعيل الوضع الليلي"><i class="ti ti-moon"></i></a>
              <a href="?theme=light" class="nav-link px-0 hide-theme-light" title="تفعيل الوضع النهاري"><i class="ti ti-sun"></i></a>
            </div>
            <div class="nav-item dropdown">
              <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                <span class="avatar avatar-sm" style="background-image: url(./assets/static/avatars/default-user.svg)"></span>
                <div class="d-none d-xl-block ps-2">
                  <div><?= htmlspecialchars($_SESSION['username'] ?? 'المستخدم') ?></div>
                  <div class="mt-1 small text-muted">مدير النظام</div>
                </div>
              </a>
              <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <a href="#" class="dropdown-item">تسجيل الخروج</a>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- BEGIN: Main Navigation Bar -->
      <div class="navbar-expand-md">
        <div class="collapse navbar-collapse" id="navbar-menu">
          <div class="navbar navbar-light">
            <div class="container-xl">
                <?php require_once __DIR__ . '/navbar.php'; // استدعاء القائمة هنا ?>
            </div>
          </div>
        </div>
      </div>
      <!-- END: Main Navigation Bar -->

      <div class="page-wrapper">
        <div class="page-body">
          <div class="container-xl">
            <div id="page-alerts"></div>
            <?= $page_content ?? '' ?>
          </div>
        </div>
        <footer class="footer footer-transparent d-print-none">
          <div class="container-xl"><div class="row text-center align-items-center flex-row-reverse"><div class="col-12 col-lg-auto mt-3 mt-lg-0"><ul class="list-inline list-inline-dots mb-0"><li class="list-inline-item">Copyright © <?= date('Y') ?> ناجي قاسم.</li></ul></div></div></div>
        </footer>
      </div>
    </div>
    
    <!-- jQuery (يجب أن يكون هنا أولاً) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Tabler Core & Libs (يأتي بعد jQuery) -->
    <script src="./assets/js/tabler.min.js" defer></script>
    <script src="./assets/libs/apexcharts/dist/apexcharts.min.js" defer></script>
    
    <!-- ملف السكربتات المخصص يأتي أخيرًا -->


    <?php require_once __DIR__ . '/footer_scripts.php'; ?>
  </body>
</html>