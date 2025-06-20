<!-- Modal -->
<div class="modal modal-blur fade" id="main-modal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content"></div></div></div>

<!-- External JS & CSS Libraries -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.rtl.min.css" />

<!-- Custom JS Application Logic -->
<script>
$(document).ready(function() {
    
    // --- دالة مركزية لتفعيل Select2 ---
    function initializeSelect2(selector) {
        $(selector).each(function() {
            var $this = $(this);
            if (!$this.data('select2')) { // منع إعادة التفعيل
                var parentDropdown = $this.closest('.modal').length ? $this.closest('.modal') : $(document.body);
                $this.select2({
                    theme: "bootstrap-5",
                    dir: "rtl",
                    placeholder: $this.data('placeholder') || "اختر...",
                    dropdownParent: parentDropdown,
                    width: 'style' // يجعل العرض يتناسب مع العنصر الأصلي
                });
            }
        });
    }

    // 1. تفعيل Select2 في الصفحة الرئيسية عند التحميل
    initializeSelect2('.select2-init');

    // 2. تفعيل Select2 داخل النوافذ المنبثقة بعد فتحها
    $('#main-modal').on('shown.bs.modal', function () {
        initializeSelect2($(this).find('.select2-init'));
    });

    // 3. منطق تحميل محتوى النوافذ المنبثقة
    $('#main-modal').on('show.bs.modal', function(e) {
        var button = $(e.relatedTarget);
        var url = button.data('bs-url');
        if (url) {
            var modal = $(this);
            var modalContent = modal.find('.modal-content');
            modalContent.html('<div class="modal-body p-5 text-center"><div class="spinner-border"></div></div>');
            $.get(url, function(data) {
                modalContent.html(data);
                // إعادة تفعيل Select2 للمحتوى الجديد
                initializeSelect2(modal.find('.select2-init'));
            }).fail(function() {
                modalContent.html('<div class="modal-body"><div class="alert alert-danger">فشل تحميل المحتوى.</div></div>');
            });
        }
    });

    // 4. منطق إرسال النماذج عبر AJAX
    $('body').on('submit', '.ajax-form', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        var originalButtonHtml = submitButton.html();
        submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> جاري الحفظ...');

        $.ajax({
            type: form.attr('method'), url: form.attr('action'), data: form.serialize(), dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#main-modal').modal('hide');
                    setTimeout(() => location.reload(), 500);
                } else {
                    form.find('#form-error-message').text(response.message || 'حدث خطأ.').show();
                }
            },
            error: function() { form.find('#form-error-message').text('فشل الاتصال بالخادم.').show(); },
            complete: function() { submitButton.prop('disabled', false).html(originalButtonHtml); }
        });
    });

    // 5. منطق تأكيد الحذف
    $('body').on('click', '.confirm-delete', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        Swal.fire({
            title: 'هل أنت متأكد؟', text: "سيتم نقل العنصر إلى الأرشيف!", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6',
            confirmButtonText: 'نعم، قم بالحذف!', cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) { window.location.href = url; }
        });
    });

    // 6. منطق تحديد كل مربعات الاختيار
    window.toggleAllCheckboxes = function(source) {
        $('input[name="row_id[]"]').prop('checked', source.checked);
    }

        // 5. منطق تأكيد الحذف
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

        // --- (جديد) منطق أزرار الصعود والنزول ---
    const $scrollTopBtn = $('#scroll-to-top-btn');
    const $scrollBottomBtn = $('#scroll-to-bottom-btn');

    // إظهار وإخفاء زر الصعود
    $(window).on('scroll', function() {
        if ($(this).scrollTop() > 300) {
            $scrollTopBtn.fadeIn();
        } else {
            $scrollTopBtn.fadeOut();
        }
    });

    // حدث النقر لزر الصعود
    $scrollTopBtn.on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({ scrollTop: 0 }, 'smooth');
    });

    // حدث النقر لزر النزول
    $scrollBottomBtn.on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({ scrollTop: $(document).height() }, 'smooth');
    });

});
</script>