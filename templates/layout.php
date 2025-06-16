<!doctype html>
<html lang="ar" dir="rtl">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title><?= $page_title ?? 'نظام إدارة الأملاك' ?></title>
    <!-- CSS files -->
    <link href="./assets/css/tabler.rtl.min.css" rel="stylesheet"/>
    <link href="./assets/css/tabler-icons.min.css" rel="stylesheet"/>
    <style>
      body { font-feature-settings: "cv03", "cv04", "cv11"; }
      .navbar-nav .nav-link-title { font-weight: 600 !important; }
    </style>
  </head>
  <body class="layout-fluid">
    <div class="page">
      
      <!-- Navbar -->
      <?php require_once __DIR__ . '/navbar.php'; ?>
      
      <div class="page-wrapper border-top">
        <!-- Page body -->
        <div class="page-body">
          <div class="container-xl">
            <?= $page_content ?? '' ?>
          </div>
        </div>
        <!-- Footer -->
        <footer class="footer footer-transparent d-print-none">
          <div class="container-xl"><div class="row text-center align-items-center flex-row-reverse"><div class="col-12 col-lg-auto mt-3 mt-lg-0"><ul class="list-inline list-inline-dots mb-0"><li class="list-inline-item">Copyright © <?= date('Y') ?> ناجي قاسم.</li></ul></div></div></div>
        </footer>
      </div>
    </div>
    
    <!-- JavaScript لـ Tabler -->
    <script src="./assets/js/tabler.min.js" defer></script>
    
    <!-- JavaScript المخصص لنظامنا -->
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        document.body.addEventListener('show.bs.modal', function(e) {
            let modal = e.target;
            let button = e.relatedTarget;
            if (!button) return;
            let url = button.getAttribute('data-bs-url');
            if (!url) return;
            let modalBody = modal.querySelector('.modal-body');
            modalBody.innerHTML = '<div class="d-flex justify-content-center p-5"><div class="spinner-border" role="status"></div></div>';
            fetch(url)
                .then(response => response.text())
                .then(html => { modalBody.innerHTML = html; })
                .catch(err => { modalBody.innerHTML = '<div class="alert alert-danger">فشل تحميل المحتوى.</div>'; });
        });
    });
    </script>
  </body>
</html>
