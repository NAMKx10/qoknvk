<!-- Modal -->
<div class="modal modal-blur fade" id="main-modal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content"></div></div></div>

<!-- SweetAlert2 (موجود بالفعل من الخطوات السابقة) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- JavaScript المخصص باستخدام jQuery -->
<script>
$(document).ready(function() {
    
    // --- 1. منطق تحميل محتوى النوافذ المنبثقة (Modals) ---
    $('body').on('show.bs.modal', '#main-modal', function(e) {
        var button = $(e.relatedTarget);
        var url = button.data('bs-url');
        var title = button.data('bs-title');
        
        var modal = $(this);
        modal.find('.modal-content').html('<div class="modal-body p-5 text-center"><div class="spinner-border"></div></div>');
        
        // جلب المحتوى باستخدام jQuery.get
        $.get(url, function(data) {
            modal.find('.modal-content').html(data);
            // بعد التحميل، قم بتحديث العنوان إذا كان موجودًا في الزر
            if (title) {
                modal.find('.modal-title').text(title);
            }
        }).fail(function() {
            modal.find('.modal-content').html('<div class="modal-body"><div class="alert alert-danger">فشل تحميل المحتوى.</div></div>');
        });
    });

    // --- 2. منطق إرسال النماذج عبر AJAX ---
    $('body').on('submit', '.ajax-form', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        var originalButtonHtml = submitButton.html();

        submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> جاري الحفظ...');

        $.ajax({
            type: form.attr('method'),
            url: form.attr('action'),
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#main-modal').modal('hide');
                    setTimeout(() => location.reload(), 500); // إعادة تحميل الصفحة بعد نصف ثانية
                } else {
                    form.find('#form-error-message').text(response.message || 'حدث خطأ.').show();
                }
            },
            error: function() {
                form.find('#form-error-message').text('فشل الاتصال بالخادم.').show();
            },
            complete: function() {
                submitButton.prop('disabled', false).html(originalButtonHtml);
            }
        });
    });

    // --- 3. منطق تأكيد الحذف ---
    $('body').on('click', '.confirm-delete', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: "سيتم نقل هذا العنصر إلى الأرشيف!",
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
    });

    // --- 4. تفعيل مربعات الاختيار ---
    window.toggleAllCheckboxes = function(source) {
        $('input[name="row_id[]"]').prop('checked', source.checked);
    }

});
</script>