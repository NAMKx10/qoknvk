<?php
// src/modules/contracts/add_view.php

// --- 1. جلب البيانات الديناميكية للقوائم ---
// جلب العملاء
$clients_list = $pdo->query("SELECT id, client_name FROM clients WHERE status = 'Active' AND deleted_at IS NULL ORDER BY client_name ASC")->fetchAll(PDO::FETCH_KEY_PAIR);

// جلب الوحدات المتاحة فقط والتي تقع ضمن فروع المستخدم المسموح بها
$units_sql_where = " WHERE u.deleted_at IS NULL AND u.status = 'متاح' ";
$units_params = [];
$units_sql_where .= build_branches_query_condition('p', $units_params);
$units_sql = "
    SELECT u.id, CONCAT(p.property_name, ' - ', u.unit_name) as full_unit_name 
    FROM units u
    JOIN properties p ON u.property_id = p.id
    {$units_sql_where}
    ORDER BY p.property_name, u.unit_name ASC
";
$units_stmt = $pdo->prepare($units_sql);
$units_stmt->execute($units_params);
$units_list = $units_stmt->fetchAll(PDO::FETCH_ASSOC);

// جلب الخيارات من تهيئة المدخلات
$payment_cycles = get_lookup_options($pdo, 'payment_cycle');
$statuses = get_lookup_options($pdo, 'status', true);
?>

<div class="modal-header">
    <h5 class="modal-title">إضافة عقد إيجار جديد</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<form method="POST" action="index.php?page=contracts/handle_add" class="ajax-form">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label required">رقم العقد</label><input type="text" class="form-control" name="contract_number" required></div>
            <div class="col-md-6"><label class="form-label required">العميل</label><select class="form-select select2-init" name="client_id" required data-placeholder="اختر العميل..."><option></option><?php foreach($clients_list as $id => $name):?><option value="<?=$id?>"><?=htmlspecialchars($name)?></option><?php endforeach; ?></select></div>
            <div class="col-12">
                <label class="form-label required">الوحدات المؤجرة</label>
                <select class="form-select select2-init" name="unit_ids[]" multiple required data-placeholder="اختر وحدة واحدة أو أكثر...">
                    <?php foreach($units_list as $unit): ?>
                        <option value="<?= $unit['id'] ?>"><?= htmlspecialchars($unit['full_unit_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6"><label class="form-label required">تاريخ بداية العقد</label><input type="date" class="form-control" name="start_date" required></div>
            <div class="col-md-6"><label class="form-label required">تاريخ نهاية العقد</label><input type="date" class="form-control" name="end_date" required></div>
            <div class="col-md-6"><label class="form-label required">القيمة الإجمالية للعقد</label><input type="number" step="0.01" class="form-control" name="total_amount" required></div>
            <div class="col-md-6"><label class="form-label required">دورة السداد</label><select class="form-select" name="payment_cycle" required><?php foreach($payment_cycles as $cycle):?><option value="<?=htmlspecialchars($cycle)?>"><?=htmlspecialchars($cycle)?></option><?php endforeach; ?></select></div>
            <div class="col-md-6"><label class="form-label">الحالة</label><select class="form-select" name="status"><?php foreach($statuses as $key => $value):?><option value="<?= htmlspecialchars($key) ?>" <?= ($key === 'Active') ? 'selected' : '' ?>><?= htmlspecialchars($value) ?></option><?php endforeach; ?></select></div>
            <div class="col-12"><label class="form-label">ملاحظات</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ العقد</button>
    </div>
</form>