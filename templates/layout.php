<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // --- 1. منطق تحميل محتوى النوافذ المنبثقة (Modals) ---
    document.body.addEventListener('show.bs.modal', function(e) {
        if (e.target.id !== 'main-modal') return;
        let button = e.relatedTarget;
        if (!button) return;
        let url = button.getAttribute('data-bs-url');
        if (!url) return;
        let modalContent = e.target.querySelector('.modal-content');
        modalContent.innerHTML = '<div class="modal-body p-5 text-center"><div class="spinner-border"></div></div>';
        fetch(url)
            .then(response => response.ok ? response.text() : Promise.reject('فشل تحميل المحتوى'))
            .then(html => { modalContent.innerHTML = html; })
            .catch(err => { modalContent.innerHTML = '<div class="modal-body"><div class="alert alert-danger">' + err + '</div></div>'; });
    });

    // === بداية الإضافة: منطق إرسال النماذج عبر AJAX ===
    document.body.addEventListener('submit', function(e) {
        // نتأكد أن النموذج الذي تم إرساله يحمل كلاس .ajax-form
        if (!e.target.classList.contains('ajax-form')) return;
        
        e.preventDefault(); // منع الإرسال التقليدي
        let form = e.target;
        let submitButton = form.querySelector('button[type="submit"]');
        let originalButtonHtml = submitButton.innerHTML;

        // تعطيل الزر وعرض مؤشر التحميل
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> جاري الحفظ...';

        fetch(form.action, {
            method: form.method,
            body: new FormData(form),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // إغلاق النافذة وإعادة تحميل الصفحة لعرض البيانات الجديدة
                const modalInstance = bootstrap.Modal.getInstance(form.closest('.modal'));
                if (modalInstance) {
                    modalInstance.hide();
                }
                location.reload();
            } else {
                // عرض رسالة الخطأ من الخادم
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
            // إعادة الزر إلى حالته الأصلية في كل الحالات
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonHtml;
        });
    });
    // === نهاية الإضافة ===

});
</script>
