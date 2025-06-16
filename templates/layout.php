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
      <header class="navbar navbar-expand-md navbar-light d-print-none">
        <div class="container-xl"><?php require_once __DIR__ . '/navbar.php'; ?></div>
      </header>
      
      <div class="page-wrapper border-top">
        <div class="page-body">
          <div class="container-xl"><?= $page_content ?? '' ?></div>
        </div>
        <footer class="footer footer-transparent d-print-none">
          <div class="container-xl"><div class="row text-center align-items-center flex-row-reverse"><div class="col-12 col-lg-auto mt-3 mt-lg-0"><ul class="list-inline list-inline-dots mb-0"><li class="list-inline-item">Copyright © <?= date('Y') ?> ناجي قاسم.</li></ul></div></div></div>
        </footer>
      </div>
    </div>

    <!-- JavaScript الأساسي لـ Tabler -->
    <script src="./assets/js/tabler.min.js" defer></script>

    <!-- JavaScript المخصص لنظامنا (بدون jQuery) -->
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // --- منطق تحميل محتوى النوافذ المنبثقة (Modals) ---
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
                .then(html => {
                    modalBody.innerHTML = html;
                })
                .catch(err => {
                    modalBody.innerHTML = '<div class="alert alert-danger">فشل تحميل المحتوى.</div>';
                });
        });

        // --- منطق إرسال النماذج عبر AJAX ---
        document.body.addEventListener('submit', function(e) {
            if (!e.target.classList.contains('ajax-form')) return;
            
            e.preventDefault();
            let form = e.target;
            let submitButton = form.querySelector('button[type="submit"]');
            let originalButtonHtml = submitButton.innerHTML;

            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> جاري الحفظ...';

            fetch(form.action, {
                method: form.method,
                body: new FormData(form),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // لا يمكننا إغلاق الـ modal بسهولة بدون المرور على الكائن، لذا سنقوم فقط بإعادة التحميل
                    location.reload();
                } else {
                    let errorDiv = form.querySelector('#form-error-message');
                    if(errorDiv) {
                        errorDiv.textContent = data.message || 'حدث خطأ غير متوقع.';
                        errorDiv.style.display = 'block';
                    }
                }
            })
            .catch(err => {
                let errorDiv = form.querySelector('#form-error-message');
                if(errorDiv) {
                    errorDiv.textContent = 'فشل الاتصال بالخادم.';
                    errorDiv.style.display = 'block';
                }
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonHtml;
            });
        });
    });
    </script>
  </body>
</html>