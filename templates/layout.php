<!doctype html>
<html lang="ar" dir="rtl">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title><?= htmlspecialchars($page_title ?? 'نظام إدارة الأملاك') ?></title>
    <!-- CSS files -->
    <link href="./assets/css/tabler.rtl.min.css" rel="stylesheet"/>
    <link href="./assets/css/tabler-icons.min.css" rel="stylesheet"/>
    <style> body { font-feature-settings: "cv03", "cv04", "cv11"; } .navbar-nav .nav-link-title { font-weight: 600 !important; } </style>
  </head>
  <body class="layout-fluid">
    <div class="page">
      <!-- Navbar -->
      <?php require_once __DIR__ . '/navbar.php'; ?>
      
      <div class="page-wrapper border-top">
        <div class="page-body">
          <div class="container-xl">
            <?= $page_content ?? '' ?>
          </div>
        </div>
        <footer class="footer footer-transparent d-print-none">
          <div class="container-xl"><div class="row text-center align-items-center flex-row-reverse"><div class="col-12 col-lg-auto mt-3 mt-lg-0"><ul class="list-inline list-inline-dots mb-0"><li class="list-inline-item">Copyright © <?= date('Y') ?> ناجي قاسم.</li></ul></div></div></div>
        </footer>
      </div>
    </div>

    <!-- Modal (النافذة المنبثقة الرئيسية) -->
    <div class="modal modal-blur fade" id="main-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <!-- المحتوى سيتم حقنه هنا بالكامل عبر AJAX -->
            </div>
        </div>
    </div>

    <!-- JavaScript لـ Tabler -->
    <script src="./assets/js/tabler.min.js" defer></script>
    <!-- JavaScript المخصص لنظامنا -->
    <script>
document.addEventListener("DOMContentLoaded", function() {
    
    // هذا الكود يستمع لحدث فتح أي نافذة منبثقة في الصفحة
    document.body.addEventListener('show.bs.modal', function(e) {
        
        // نحدد النافذة والزر الذي فتحها
        let modal = e.target;
        let button = e.relatedTarget;
        
        // نتأكد أن الزر لديه رابط لجلب المحتوى
        if (!button) return;
        let url = button.getAttribute('data-bs-url');
        if (!url) return;

        // نجد المكان الذي سنضع فيه المحتوى داخل النافذة
        let modalBody = modal.querySelector('.modal-body');
        
        // نعرض مؤشر تحميل
        modalBody.innerHTML = '<div class="d-flex justify-content-center p-5"><div class="spinner-border" role="status"></div></div>';

        // نستخدم fetch لجلب المحتوى من الرابط
        fetch(url)
            .then(response => {
                // نتأكد أن الطلب نجح
                if (!response.ok) {
                    throw new Error('فشل تحميل المحتوى');
                }
                return response.text();
            })
            .then(html => {
                // نضع المحتوى الذي تم جلبه داخل جسم النافذة
                modalBody.innerHTML = html;
            })
            .catch(err => {
                // في حالة الفشل، نعرض رسالة خطأ
                modalBody.innerHTML = '<div class="alert alert-danger">فشل الاتصال بالخادم أو الصفحة غير موجودة.</div>';
            });
    });

    // لاحقًا، سنضيف كود إرسال النماذج هنا...

});
</script>
  </body>
</html>
