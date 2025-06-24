<?php
if (!isset($_GET['id'])) { die("ID required."); }
$branch_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM branches WHERE id = ?");
$stmt->execute([$branch_id]);
$branch = $stmt->fetch();
if (!$branch) { die("Branch not found."); }

// --- جلب البيانات من الإعدادات ---
$branch_types = $pdo->query("SELECT option_value FROM lookup_options WHERE group_key = 'entity_type' AND option_key != 'entity_type' AND deleted_at IS NULL")->fetchAll(PDO::FETCH_COLUMN);
$statuses_map_stmt = $pdo->query("SELECT option_key, option_value, bg_color, color FROM lookup_options WHERE group_key = 'status' AND option_key != 'status'");
$statuses_map = [];
foreach($statuses_map_stmt->fetchAll(PDO::FETCH_ASSOC) as $status) { $statuses_map[$status['option_key']] = $status; }
?>
<div class="modal-header">
    <h5 class="modal-title">تعديل الفرع: <?= htmlspecialchars($branch['branch_name']) ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form method="POST" action="index.php?page=branches/handle_edit" class="ajax-form">
    <input type="hidden" name="id" value="<?= $branch['id'] ?>">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label required">اسم الفرع</label><input type="text" class="form-control" name="branch_name" value="<?= htmlspecialchars($branch['branch_name']) ?>" required></div>
            <div class="col-md-6"><label class="form-label">كود الفرع</label><input type="text" class="form-control" name="branch_code" value="<?= htmlspecialchars($branch['branch_code']) ?>"></div>
            <div class="col-md-6"><label class="form-label">نوع الكيان</label><select class="form-select select2-init" name="branch_type"><?php foreach($branch_types as $type):?><option value="<?= htmlspecialchars($type) ?>" <?= ($branch['branch_type'] == $type)?'selected':'' ?>><?= htmlspecialchars($type) ?></option><?php endforeach;?></select></div>
            <div class="col-md-6"><label class="form-label">رقم السجل</label><input type="text" class="form-control" name="registration_number" value="<?= htmlspecialchars($branch['registration_number']) ?>"></div>
            <div class="col-md-6"><label class="form-label">الرقم الضريبي</label><input type="text" class="form-control" name="tax_number" value="<?= htmlspecialchars($branch['tax_number']) ?>"></div>
            <div class="col-md-6"><label class="form-label">الجوال</label><input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($branch['phone']) ?>"></div>
            <div class="col-md-6"><label class="form-label">البريد الإلكتروني</label><input type="email" class="form-control" name="email" value="<?= htmlspecialchars($branch['email']) ?>"></div>
            <div class="col-md-6"><label class="form-label">الحالة</label><select class="form-select" name="status"><?php foreach($statuses_map as $key => $s):?><option value="<?= htmlspecialchars($key) ?>" <?= ($branch['status'] == $key)?'selected':'' ?>><?= htmlspecialchars($s['option_value']) ?></option><?php endforeach;?></select></div>
            <div class="col-12"><label class="form-label">العنوان</label><textarea class="form-control" name="address" rows="2"><?= htmlspecialchars($branch['address']) ?></textarea></div>
            <div class="col-12"><label class="form-label">ملاحظات</label><textarea class="form-control" name="notes" rows="2"><?= htmlspecialchars($branch['notes']) ?></textarea></div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn" data-bs-dismiss="modal">إلغاء</a>
        <button type="submit" class="btn btn-primary ms-auto">حفظ التعديلات</button>
    </div>
</form>