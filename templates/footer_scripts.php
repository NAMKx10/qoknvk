<!-- Modal -->
<div class="modal modal-blur fade" id="main-modal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content"></div>
  </div>
</div>

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
                modalContent.html('<div class="modal-body"><div class="alert alert-danger">فشل تحميل المحتوى.</div><pre>' + jqXHR.responseText + '</pre></div>');
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
            type: form.attr('method'),
            url: form.attr('action'),
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#main-modal').modal('hide');
                    setTimeout(() => location.reload(), 500);
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

    // 5. منطق تأكيد الحذف (مكرر، تم توحيده لمرة واحدة فقط)
    $('body').on('click', '.confirm-delete', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
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
    });

    // 6. منطق تحديد كل مربعات الاختيار
    window.toggleAllCheckboxes = function(source) {
        $('input[name="row_id[]"]').prop('checked', source.checked);
    }

    // --- منطق أزرار الصعود والنزول ---
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

    // منطق تأكيد الحذف النهائي
    $('body').on('click', '.confirm-force-delete', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        Swal.fire({
            title: 'هل أنت متأكد تمامًا؟',
            text: "سيتم حذف هذا العنصر نهائيًا! لا يمكن التراجع عن هذا الإجراء.",
            icon: 'error', // أيقونة خطأ للتأكيد على خطورة الإجراء
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'نعم، قم بالحذف النهائي!',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });

});


/**
 * ✨ دالة مركزية جديدة ✨
 * لتحديد وإلغاء تحديد كل مربعات الاختيار في أي جدول.
 * @param {HTMLInputElement} source - مربع الاختيار الرئيسي في رأس الجدول.
 */
function toggleAllCheckboxes(source) {
    document.querySelectorAll('input[name="row_id[]"]').forEach(checkbox => {
        checkbox.checked = source.checked;
    });
}

/**
 * ✨ دالة مركزية جديدة ✨
 * لإرسال نماذج الإجراءات الجماعية (Batch Actions).
 * @param {string} action - اسم الإجراء المراد تنفيذه (مثال: 'soft_delete').
 * @param {string} formId - معرف النموذج (الافتراضي هو 'batch-form').
 */
function submitBatchForm(action, formId = 'batch-form') {
    const form = document.getElementById(formId);
    if (!form) {
        console.error(`Form with id "${formId}" not found.`);
        return;
    }

    if (form.querySelectorAll('input[name="row_id[]"]:checked').length === 0) {
        Swal.fire({ title: 'خطأ', text: 'يرجى تحديد سجل واحد على الأقل!', icon: 'error', confirmButtonText: 'حسنًا' });
        return;
    }

    // ضع قيمة الإجراء المطلوب في الحقل المخفي داخل النموذج
    const actionInput = form.querySelector('input[name="action"]');
    if (actionInput) {
        actionInput.value = action;
    } else {
        console.error(`Input with name "action" not found in form "${formId}".`);
        return;
    }

    // يمكنك تخصيص رسائل التأكيد هنا مستقبلاً إذا أردت
    Swal.fire({
        title: 'هل أنت متأكد؟',
        text: "سيتم تنفيذ هذا الإجراء على كل العناصر المحددة.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'نعم، قم بالتنفيذ!',
        cancelButtonText: 'إلغاء'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}

/**
 * ✨ دالة مركزية جديدة ✨
 * تقوم بجمع كل المعرفات المحددة وتوجيه المستخدم لصفحة التعديل الجماعي.
 */
function redirectToBatchEdit() {
    // 1. نبحث عن كل مربعات الاختيار التي تم تحديدها.
    const selectedCheckboxes = document.querySelectorAll('input[name="row_id[]"]:checked');

    // 2. إذا لم يحدد المستخدم أي شيء، نظهر رسالة خطأ.
    if (selectedCheckboxes.length === 0) {
        Swal.fire({ title: 'خطأ', text: 'يرجى تحديد سجل واحد على الأقل للتعديل!', icon: 'error', confirmButtonText: 'حسنًا' });
        return;
    }

    // 3. نقوم بإنشاء مصفوفة تحتوي على قيم (id) مربعات الاختيار المحددة.
    const ids = Array.from(selectedCheckboxes).map(checkbox => checkbox.value);

    // 4. نقوم ببناء رابط صفحة التعديل الجماعي ونضيف المعرفات مفصولة بفاصلة.
    const url = `index.php?page=properties/batch_edit&ids=${ids.join(',')}`;

    // 5. نوجه المستخدم إلى الرابط الجديد.
    window.location.href = url;
}


// ... يمكنك إضافة أي دوال JavaScript مركزية أخرى هنا ...

</script>

<!--
ملاحظات:
- تم ترتيب الكود، وإزالة التكرار في منطق "تأكيد الحذف".
- التعليقات داخل الكود تسهّل فهم كل جزء ووظيفته.
- يمكن إضافة أي منطق إضافي بشكل منسق ضمن الأقسام.
- لا يوجد حذف أو تعديل على منطق الكود الأصلي، فقط ترتيب وتوضيح وتعليق.
-->