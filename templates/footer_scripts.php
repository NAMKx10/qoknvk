<!-- Modal (النافذة المنبثقة الرئيسية) -->
<div class="modal modal-blur fade" id="main-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <!-- المحتوى سيتم حقنه هنا بالكامل عبر AJAX -->
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Tabler Core & Libs -->
<script src="./assets/js/tabler.min.js" defer></script>
<script src="./assets/libs/apexcharts/dist/apexcharts.min.js" defer></script>

<!-- ======================================================= -->
<!-- JavaScript المخصص لنظامنا (يعمل بدون jQuery) -->
<!-- ======================================================= -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // --- 1. منطق تحميل محتوى النوافذ المنبثقة (Modals) ---
    document.body.addEventListener('show.bs.modal', function(e) {
        // نتأكد أننا نتعامل مع النافذة الرئيسية فقط
        if (e.target.id !== 'main-modal') return;
        
        let button = e.relatedTarget;
        if (!button) return;
        
        let url = button.getAttribute('data-bs-url');
        if (!url) return;

        let modalContent = e.target.querySelector('.modal-content');
        let modalTitleEl = document.getElementById('modal-title'); // نفترض وجود هذا ID في النموذج
        let modal = bootstrap.Modal.getInstance(e.target);

        // عرض مؤشر التحميل
        modalContent.innerHTML = '<div class="modal-body p-5 text-center"><div class="spinner-border"></div></div>';

        // طلب المحتوى من الخادم
        fetch(url)
            .then(response => {
                if (!response.ok) { throw new Error('فشل تحميل المحتوى من الخادم.'); }
                return response.text();
            })
            .then(html => {
                modalContent.innerHTML = html;
                // بعد تحميل المحتوى، نجد العنوان من داخل النموذج ونضعه في رأس النافذة
                let newTitle = modalContent.querySelector('.modal-title');
                if(newTitle && modalTitleEl) {
                    modalTitleEl.innerHTML = newTitle.innerHTML;
                }
            })
            .catch(err => {
                modalContent.innerHTML = '<div class="modal-body"><div class="alert alert-danger">' + err.message + '</div></div>';
            });
    });

    // --- 2. منطق إرسال النماذج عبر AJAX ---
    document.body.addEventListener('submit', function(e) {
        if (!e.target.classList.contains('ajax-form')) return;
        
        e.preventDefault();
        const form = e.target;
        const submitButton = form.querySelector('button[type="submit"]');
        const originalButtonHtml = submitButton.innerHTML;

        submitButton.disabled = true;
        submitButton.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> جاري الحفظ...`;
        
        const errorDiv = form.querySelector('#form-error-message');
        if(errorDiv) errorDiv.style.display = 'none';
        
        fetch(form.action, {
            method: form.method,
            body: new FormData(form),
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                // إذا كان هناك خطأ في الخادم (مثل خطأ 500)
                throw new Error(`خطأ في الخادم: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const modalInstance = bootstrap.Modal.getInstance(form.closest('.modal'));
                if (modalInstance) {
                    modalInstance.hide();
                }
                // إضافة إشعار نجاح (باستخدام Tabler's built-in notifications)
                const alertHtml = '<div class="alert alert-success alert-dismissible" role="alert"><div class="d-flex"><div><i class="ti ti-check"></i></div><div><h4 class="alert-title">نجاح</h4><div class="text-muted">' + (data.message || 'تمت العملية بنجاح.') + '</div></div></div><a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a></div>';
                document.getElementById('page-alerts').innerHTML = alertHtml;
                // إعادة تحميل الصفحة بعد فترة قصيرة لرؤية التغييرات
                setTimeout(() => location.reload(), 1500);

            } else {
                if(errorDiv) {
                    errorDiv.textContent = data.message || 'حدث خطأ غير متوقع.';
                    errorDiv.style.display = 'block';
                }
            }
        })
        .catch(error => {
            if(errorDiv) {
                errorDiv.textContent = error.message || 'فشل الاتصال بالخادم.';
                errorDiv.style.display = 'block';
            }
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonHtml;
        });
    });

        // === بداية الإضافة: دالة تأكيد الحذف ===
    document.body.addEventListener('click', function(e) {
        if (e.target.closest('.confirm-delete')) {
            e.preventDefault();
            const url = e.target.closest('.confirm-delete').href;

            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: "سيتم نقل العنصر إلى الأرشيف!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم، قم بالحذف!',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }
    });
    // === نهاية الإضافة ===

    
});
</script>