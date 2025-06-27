<?php
// src/modules/properties/properties_batch_add_view.php
// (الواجهة الجديدة للإدخال المتعدد بأسلوب Excel)
?>
<!-- 1. تضمين ملفات مكتبة Handsontable -->
<script src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css" rel="stylesheet">

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col"><h2 class="page-title">إدخال متعدد للعقارات</h2></div>
            <div class="col-auto ms-auto d-print-none"><a href="index.php?page=properties" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-2"></i>العودة</a></div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-body">
                <p class="text-muted">
                    يمكنك لصق البيانات مباشرة من ملف Excel. تأكد من أن ترتيب الأعمدة في ملفك يطابق ترتيبها في الجدول أدناه.
                </p>
                <!-- 2. ✨ هذا هو الـ div الفارغ الذي سيتحول إلى جدول Excel ✨ -->
                <div id="batch-add-grid"></div>
            </div>
            <div class="card-footer text-end">
                <button id="save-data-btn" class="btn btn-primary"><i class="ti ti-device-floppy me-2"></i>حفظ السجلات الجديدة</button>
            </div>
        </div>
    </div>
</div>

<!-- 3. ✨ كود JavaScript الذي يقوم بكل العمل ✨ -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // 3a. قراءة البيانات بصيغة JSON التي جهزها المتحكم
        const branchesData = JSON.parse('<?= $branches_json ?>');
        const propertyTypesData = JSON.parse('<?= $property_types_json ?>');
        const ownershipTypesData = JSON.parse('<?= $ownership_types_json ?>');
        const statusesData = JSON.parse('<?= $statuses_json ?>');

        const container = document.getElementById('batch-add-grid');
        const saveButton = document.getElementById('save-data-btn');

        // 3b. تجهيز بيانات القوائم المنسدلة للمكتبة
        const branchSource = Object.entries(branchesData).map(([id, name]) => ({ id: id, label: name }));

        // 3c. تهيئة الجدول مع الأعمدة الجديدة
        const hot = new Handsontable(container, {
            data: Array.from({ length: <?= $num_rows ?> }, () => ({})), // إنشاء صفوف فارغة
            language: 'ar-SA',
            colHeaders: ['اسم العقار *', 'الفرع *', 'نوع العقار', 'التملك', 'الحالة', 'المالك', 'رقم الصك', 'المدينة', 'الحي', 'المساحة', 'القيمة', 'الملاحظات'],
            columns: [
                { data: 'property_name' },
                {
                    data: 'branch_id', type: 'autocomplete',
                    source: function (query, process) {
                        const matching = branchSource.filter(b => b.label.toLowerCase().includes(query.toLowerCase()));
                        process(matching.map(b => b.label));
                    },
                    allowInvalid: false
                },
                { data: 'property_type', type: 'dropdown', source: propertyTypesData },
                { data: 'ownership_type', type: 'dropdown', source: ownershipTypesData },
                { data: 'status', type: 'dropdown', source: Object.values(statusesData) }, // حفظ المفتاح الإنجليزي
                { data: 'owner_name' },
                { data: 'deed_number' },
                { data: 'city' },
                { data: 'district' },
                { data: 'area', type: 'numeric' },
                { data: 'property_value', type: 'numeric', numericFormat: { pattern: '0,0.00' } },
                { data: 'notes' }
            ],
            minSpareRows: 1, height: 'auto', stretchH: 'all', licenseKey: 'non-commercial-and-evaluation',
            
            // 3d. معالجة البيانات قبل الحفظ لتصحيح الـ ID
            beforeChange: (changes, source) => {
    if (source === 'loadData') {
        return; // لا تطبق أي تغييرات عند تحميل البيانات أول مرة
    }
    changes.forEach(([row, prop, oldValue, newValue]) => {
        const rowData = hot.getSourceDataAtRow(row);
        
        // ✨ تصحيح معرف الفرع ✨
        if (prop === 'branch_id') {
            const selectedBranch = branchSource.find(b => b.label === newValue);
            if (selectedBranch) {
                rowData.branch_id = selectedBranch.id;
            }
        }
        // ✨ تصحيح قيمة الحالة ✨
        else if (prop === 'status') {
            // نبحث عن المفتاح الإنجليزي المقابل للاسم العربي الذي اختاره المستخدم
            const statusKey = Object.keys(statusesData).find(key => statusesData[key] === newValue);
            if (statusKey) {
                rowData.status = statusKey;
            }
        }
    });
},
        });

// 3e. ✨ منطق الحفظ الفعلي عند الضغط على الزر ✨
saveButton.addEventListener('click', function() {
    saveButton.disabled = true;
    saveButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> جاري الحفظ...';

    // نقوم بتصفية الصفوف التي لم يقم المستخدم بإدخال اسم عقار لها
    const dataToSave = hot.getSourceData().filter(row => row.property_name && row.property_name.trim() !== '' && row.branch_id);
    
    if (dataToSave.length === 0) {
         Swal.fire('بيانات ناقصة!', 'يرجى إدخال اسم عقار واختيار فرع واحد على الأقل.', 'warning');
         saveButton.disabled = false;
         saveButton.innerHTML = '<i class="ti ti-device-floppy me-2"></i>حفظ السجلات الجديدة';
         return;
    }

    // إرسال البيانات إلى المعالج الخلفي
    fetch('index.php?page=properties/handle_batch_add', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ property: dataToSave })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            Swal.fire('تم الحفظ بنجاح!', result.message, 'success')
                .then(() => window.location.href = 'index.php?page=properties');
        } else {
            Swal.fire('خطأ في الحفظ!', result.message || 'حدث خطأ غير متوقع.', 'error');
        }
    })
    .catch(() => Swal.fire('خطأ في الاتصال!', 'فشل الاتصال بالخادم.', 'error'))
    .finally(() => {
         saveButton.disabled = false;
         saveButton.innerHTML = '<i class="ti ti-device-floppy me-2"></i>حفظ السجلات الجديدة';
    });
});
    });
</script>