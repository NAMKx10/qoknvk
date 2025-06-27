<?php
// src/modules/properties/properties_batch_edit_view.php
// (الواجهة الجديدة للتعديل المتعدد بأسلوب Excel)
?>
<!-- 1. تضمين ملفات مكتبة Handsontable -->
<script src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css" rel="stylesheet">

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col"><h2 class="page-title">تعديل جماعي للعقارات</h2></div>
            <div class="col-auto ms-auto d-print-none"><a href="index.php?page=properties" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-2"></i>العودة</a></div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-body">
                <p class="text-muted">يمكنك تعديل أي قيمة مباشرة في الجدول. سيتم حفظ كل التغييرات عند الضغط على الزر.</p>
                <!-- 2. ✨ هذا هو الـ div الفارغ الذي سيتحول إلى جدول Excel ✨ -->
                <div id="batch-edit-grid"></div>
            </div>
            <div class="card-footer text-end">
                <button id="save-edit-btn" class="btn btn-primary"><i class="ti ti-device-floppy me-2"></i>حفظ كل التعديلات</button>
            </div>
        </div>
    </div>
</div>

<!-- 3. ✨ كود JavaScript الذي يقوم بكل العمل ✨ -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. قراءة البيانات التي جهزها المتحكم
        const propertiesData = JSON.parse('<?= $properties_json ?>');
        const branchesData = JSON.parse('<?= $branches_json ?>');
        const propertyTypesData = JSON.parse('<?= $property_types_json ?>');
        const ownershipTypesData = JSON.parse('<?= $ownership_types_json ?>');
        const statusesData = JSON.parse('<?= $statuses_json ?>');
        
        const container = document.getElementById('batch-edit-grid');
        const saveButton = document.getElementById('save-edit-btn');
        const branchSource = Object.entries(branchesData).map(([id, name]) => ({ id: id, label: name }));

        // 2. ✨ هنا الإصلاح: نقوم بترجمة البيانات قبل عرضها في الجدول ✨
        const propertiesForGrid = propertiesData.map(p => {
            return {
                ...p, // نأخذ كل بيانات العقار كما هي
                // ونقوم بترجمة قيمة الفرع والحالة إلى النص المقروء
                branch_id: branchesData[p.branch_id] || p.branch_id,
                status: statusesData[p.status] || p.status 
            };
        });
        
        // 3. تهيئة الجدول مع البيانات المترجمة
        const hot = new Handsontable(container, {
            data: propertiesForGrid, // ✨ نستخدم البيانات المترجمة هنا ✨
            language: 'ar-SA',
            colHeaders: ['اسم العقار', 'كود العقار', 'الفرع', 'نوع العقار', 'التملك', 'الحالة', 'المالك', 'رقم الصك', 'المدينة', 'الحي', 'المساحة', 'القيمة', 'الملاحظات'],
            columns: [
                 { data: 'property_name' }, { data: 'property_code' },
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
                { data: 'status', type: 'dropdown', source: Object.values(statusesData) }, // قائمة الخيارات تظل باللغة العربية
                { data: 'owner_name' }, { data: 'deed_number' }, { data: 'city' }, { data: 'district' },
                { data: 'area', type: 'numeric' }, { data: 'property_value', type: 'numeric' }, { data: 'notes' }
            ],
            minSpareRows: 0, height: 'auto', stretchH: 'all', licenseKey: 'non-commercial-and-evaluation',

            // 4. ✨ نقوم بالترجمة العكسية قبل الحفظ ✨
            beforeChange: (changes, source) => {
                 if (source === 'loadData') return;
                 changes.forEach(([row, prop, oldValue, newValue]) => {
                    const rowData = hot.getSourceDataAtRow(row);
                    if (prop === 'branch_id') {
                        const selectedBranch = branchSource.find(b => b.label === newValue);
                        if (selectedBranch) { rowData.branch_id = selectedBranch.id; }
                    } else if (prop === 'status') {
                        const statusKey = Object.keys(statusesData).find(key => statusesData[key] === newValue);
                        if (statusKey) { rowData.status = statusKey; }
                    }
                });
            }
        });

        // 5. منطق الحفظ (يبقى كما هو)
        saveButton.addEventListener('click', function() {
             saveButton.disabled = true;
             saveButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> جاري الحفظ...';

             // نحصل على كل البيانات من الجدول بعد التعديل
             const dataToSave = hot.getSourceData();

             fetch('index.php?page=properties/handle_batch_edit', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ property: dataToSave })
             })
             .then(res => res.json()).then(result => {
                if (result.success) {
                    Swal.fire('تم الحفظ!', result.message, 'success').then(() => window.location.href = 'index.php?page=properties');
                } else {
                    Swal.fire('خطأ!', result.message, 'error');
                }
             }).catch(err => Swal.fire('خطأ اتصال!', 'فشل الاتصال بالخادم', 'error'))
             .finally(() => {
                saveButton.disabled = false;
                saveButton.innerHTML = '<i class="ti ti-device-floppy me-2"></i>حفظ كل التعديلات';
             });
        });
    });
</script>
