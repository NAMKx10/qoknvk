<!doctype html>
<html lang="ar" dir="rtl">
  <head>
    <meta charset="utf-8"/><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/><meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title><?= htmlspecialchars($page_title ?? 'نظام إدارة الأملاك') ?></title>
    <link href="./assets/css/tabler.rtl.min.css" rel="stylesheet"/>
    <link href="./assets/css/tabler-icons.min.css" rel="stylesheet"/>
    <style> body { font-feature-settings: "cv03", "cv04", "cv11"; } .navbar-nav .nav-link-title { font-weight: 600 !important; } </style>
  </head>
  <body class="layout-fluid">
    <div class="page">
      <?php require_once __DIR__ . '/navbar.php'; ?>
      <div class="page-wrapper border-top">
        <div class="page-body">
          <div class="container-xl"><?= $page_content ?? '' ?></div>
        </div>
        <footer class="footer footer-transparent d-print-none">
          <div class="container-xl"><div class="row text-center align-items-center flex-row-reverse"><div class="col-12 col-lg-auto mt-3 mt-lg-0"><ul class="list-inline list-inline-dots mb-0"><li class="list-inline-item">Copyright © <?= date('Y') ?> ناجي قاسم.</li></ul></div></div></div>
        </footer>
      </div>
    </div>
    <!-- Modal -->
    <div class="modal modal-blur fade" id="main-modal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <!-- المحتوى سيتم حقنه هنا بالكامل -->
        </div>
      </div>
    </div>
    <script src="./assets/js/tabler.min.js" defer></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        document.body.addEventListener('show.bs.modal', function(e) {
            if (e.target.id !== 'main-modal') return;
            let button = e.relatedTarget;
            if (!button) return;
            let url = button.getAttribute('data-bs-url');
            if (!url) return;
            let modalContent = e.target.querySelector('.modal-content');
            modalContent.innerHTML = '<div class="modal-body p-5 text-center"><div class="spinner-border"></div></div>';
            fetch(url).then(response => response.text()).then(html => { modalContent.innerHTML = html; });
        });
    });
    </script>
  </body>
</html>
