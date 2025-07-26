<?php
// src/modules/clients/edit_view.php (النسخة المحدثة مع كود العميل)
if (!isset($_GET['id'])) { die("ID is required."); }
$client_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->execute([$client_id]);
$client = $stmt->fetch();
if (!$client) { die("Client not found."); }
$branches_list = $pdo->query("SELECT id, branch_name FROM branches WHERE status = 'Active' AND deleted_at IS NULL ORDER BY branch_name ASC")->fetchAll();
$current_branch_ids = $pdo->prepare("SELECT branch_id FROM client_branches WHERE client_id = ?");
$current_branch_ids->execute([$client_id]);
$current_branch_ids = $current_branch_ids->fetchAll(PDO::FETCH_COLUMN);
$client_types = get_lookup_options($pdo, 'entity_type');
$statuses = get_lookup_options($pdo, 'status', true);
?>
<div class="modal-header"><h5 class="modal-title">تعديل العميل: <?= htmlspecialchars($client['client_name']) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
<form method="POST" action="index.php?page=clients/handle_edit" class="ajax-form">
    <input type="hidden" name="id" value="<?= $client['id']; ?>">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label required">اسم العميل</label><input type="text" class="form-control" name="client_name" value="<?= htmlspecialchars($client['client_name']); ?>" required></div>
            <div class="col-md-6"><label class="form-label">كود العميل</label><input type="text" class="form-control" name="client_code" value="<?= htmlspecialchars($client['client_code'] ?? ''); ?>"></div>
            <div class="col-md-6"><label class="form-label required">نوع العميل</label><select class="form-select" name="client_type" required><?php foreach($client_types as $type): ?><option value="<?= htmlspecialchars($type) ?>" <?= ($client['client_type'] == $type) ? 'selected' : ''; ?>><?= htmlspecialchars($type) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-6"><label class="form-label">رقم الهوية/السجل</label><input type="text" class="form-control" name="id_number" value="<?= htmlspecialchars($client['id_number']); ?>"></div>
            <div class="col-md-6"><label class="form-label">الرقم الضريبي</label><input type="text" class="form-control" name="tax_number" value="<?= htmlspecialchars($client['tax_number'] ?? ''); ?>"></div>
            <div class="col-md-6"><label class="form-label">الجوال</label><input type="text" class="form-control" name="mobile" value="<?= htmlspecialchars($client['mobile']); ?>"></div>
            <div class="col-md-6"><label class="form-label">البريد الإلكتروني</label><input type="email" class="form-control" name="email" value="<?= htmlspecialchars($client['email']); ?>"></div>
            <div class="col-md-6"><label class="form-label">اسم الممثل</label><input type="text" class="form-control" name="representative_name" value="<?= htmlspecialchars($client['representative_name']); ?>"></div>
            <div class="col-md-6"><label class="form-label">الحالة</label><select class="form-select" name="status"><?php foreach($statuses as $key => $value): ?><option value="<?= htmlspecialchars($key) ?>" <?= ($client['status'] == $key) ? 'selected' : ''; ?>><?= htmlspecialchars($value) ?></option><?php endforeach; ?></select></div>
            <div class="col-12"><label class="form-label">الفروع المرتبطة</label><select class="form-select select2-init" name="branches[]" multiple data-placeholder="اختر فرعًا أو أكثر..."><?php foreach ($branches_list as $branch): ?><option value="<?= $branch['id']; ?>" <?= in_array($branch['id'], $current_branch_ids) ? 'selected' : ''; ?>><?= htmlspecialchars($branch['branch_name']); ?></option><?php endforeach; ?></select></div>
            <div class="col-12"><label class="form-label">العنوان</label><textarea class="form-control" name="address" rows="2"><?= htmlspecialchars($client['address']); ?></textarea></div>
            <div class="col-12"><label class="form-label">ملاحظات</label><textarea class="form-control" name="notes" rows="2"><?= htmlspecialchars($client['notes'] ?? ''); ?></textarea></div>
        </div>
    </div>
    <div class="modal-footer"><button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button><button type="submit" class="btn btn-primary">حفظ التعديلات</button></div>
</form>