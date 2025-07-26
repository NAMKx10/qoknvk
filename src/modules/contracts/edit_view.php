<?php
// src/modules/contracts/edit_view.php

if (!isset($_GET['id'])) { die("ID is required."); }
$contract_id = $_GET['id'];

// جلب بيانات العقد
$stmt = $pdo->prepare("SELECT * FROM contracts_rental WHERE id = ?");
$stmt->execute([$contract_id]);
$contract = $stmt->fetch();
if (!$contract) { die("Contract not found."); }

// جلب الوحدات المرتبطة حاليًا بالعقد
$current_units_stmt = $pdo->prepare("SELECT unit_id FROM contract_units WHERE contract_id = ?");
$current_units_stmt->execute([$contract_id]);
$current_unit_ids = $current_units_stmt->fetchAll(PDO::FETCH_COLUMN);

// --- جلب البيانات الديناميكية للقوائم ---
$clients_list = $pdo->query("SELECT id, client_name FROM clients WHERE status = 'Active' AND deleted_at IS NULL ORDER BY client_name ASC")->fetchAll(PDO::FETCH_KEY_PAIR);
$units_sql_where = " WHERE u.deleted_at IS NULL AND (u.status = 'متاح' OR u.id IN (".implode(',', $current_unit_ids ?: [0]).")) "; // السماح بعرض الوحدات المتاحة + الوحدات المرتبطة بالعقد الحالي
$units_params = [];
$units_sql_where .= build_branches_query_condition('p', $units_params);
$units_sql = "SELECT u.id, CONCAT(p.property_name, ' - ', u.unit_name) as full_unit_name FROM units u JOIN properties p ON u.property_id = p.id {$units_sql_where} ORDER BY p.property_name, u.unit_name ASC";
$units_stmt = $pdo->prepare($units_sql);
$units_stmt->execute($units_params);
$units_list = $units_stmt->fetchAll(PDO::FETCH_ASSOC);

$payment_cycles = get_lookup_options($pdo, 'payment_cycle');
$statuses = get_lookup_options($pdo, 'status', true);
?>

<div class="modal-header">
    <h5 class="modal-title">تعديل عقد الإيجار رقم: <?= htmlspecialchars($contract['contract_number']) ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form method="POST" action="index.php?page=contracts/handle_edit" class="ajax-form">
    <input type="hidden" name="id" value="<?= $contract['id'] ?>">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label required">رقم العقد</label><input type="text" class="form-control" name="contract_number" value="<?= htmlspecialchars($contract['contract_number']) ?>" required></div>
            <div class="col-md-6"><label class="form-label required">العميل</label><select class="form-select select2-init" name="client_id" required data-placeholder="اختر العميل..."><option></option><?php foreach($clients_list as $id => $name):?><option value="<?=$id?>" <?= ($contract['client_id'] == $id) ? 'selected' : '' ?>><?=htmlspecialchars($name)?></option><?php endforeach; ?></select></div>
            <div class="col-12">
                <label class="form-label required">الوحدات المؤجرة</label>
                <select class="form-select select2-init" name="unit_ids[]" multiple required data-placeholder="اختر وحدة واحدة أو أكثر...">
                    <?php foreach($units_list as $unit): ?>
                        <option value="<?= $unit['id'] ?>" <?= in_array($unit['id'], $current_unit_ids) ? 'selected' : '' ?>><?= htmlspecialchars($unit['full_unit_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6"><label class="form-label required">تاريخ البداية</label><input type="date" class="form-control" name="start_date" value="<?= htmlspecialchars($contract['start_date']) ?>" required></div>
            <div class="col-md-6"><label class="form-label required">تاريخ النهاية</label><input type="date" class="form-control" name="end_date" value="<?= htmlspecialchars($contract['end_date']) ?>" required></div>
            <div class="col-md-6"><label class="form-label required">القيمة الإجمالية</label><input type="number" step="0.01" class="form-control" name="total_amount" value="<?= htmlspecialchars($contract['total_amount']) ?>" required></div>
            <div class="col-md-6"><label class="form-label required">دورة السداد</label><select class="form-select" name="payment_cycle" required><?php foreach($payment_cycles as $cycle):?><option value="<?=htmlspecialchars($cycle)?>" <?= ($contract['payment_cycle'] == $cycle) ? 'selected' : '' ?>><?=htmlspecialchars($cycle)?></option><?php endforeach; ?></select></div>
            <div class="col-md-6"><label class="form-label">الحالة</label><select class="form-select" name="status"><?php foreach($statuses as $key => $value):?><option value="<?= htmlspecialchars($key) ?>" <?= ($contract['status'] == $key) ? 'selected' : '' ?>><?= htmlspecialchars($value) ?></option><?php endforeach; ?></select></div>
            <div class="col-12"><label class="form-label">ملاحظات</label><textarea class="form-control" name="notes" rows="2"><?= htmlspecialchars($contract['notes']) ?></textarea></div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
    </div>
</form>