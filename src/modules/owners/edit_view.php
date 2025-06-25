<?php
// src/modules/owners/edit_view.php (النسخة الموحدة مع ربط الفروع)

if (!isset($_GET['id'])) { die("ID is required."); }
$owner_id = $_GET['id'];

// جلب بيانات المالك
$stmt = $pdo->prepare("SELECT * FROM owners WHERE id = ?");
$stmt->execute([$owner_id]);
$owner = $stmt->fetch();
if (!$owner) { die("Owner not found."); }

// --- جلب البيانات الديناميكية ---
// 1. جلب أنواع الملاك من إعدادات "نوع الكيان"
$owner_types = $pdo->query("SELECT option_value FROM lookup_options WHERE group_key = 'entity_type' AND option_key != 'entity_type' AND deleted_at IS NULL")->fetchAll(PDO::FETCH_COLUMN);

// 2. جلب الحالات
$statuses_map = $pdo->query("SELECT option_key, option_value FROM lookup_options WHERE group_key = 'status' AND option_key != 'status' AND deleted_at IS NULL")->fetchAll(PDO::FETCH_KEY_PAIR);

// 3. جلب كل الفروع النشطة
$branches_list = $pdo->query("SELECT id, branch_name FROM branches WHERE status = 'Active' AND deleted_at IS NULL ORDER BY branch_name ASC")->fetchAll();

// 4. جلب الفروع المرتبطة حاليًا بهذا المالك
$current_branches_stmt = $pdo->prepare("SELECT branch_id FROM owner_branches WHERE owner_id = ?");
$current_branches_stmt->execute([$owner_id]);
$current_branch_ids = $current_branches_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="modal-header">
    <h5 class="modal-title">تعديل المالك: <?= htmlspecialchars($owner['owner_name']) ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form method="POST" action="index.php?page=owners/handle_edit" class="ajax-form">
    <input type="hidden" name="id" value="<?= $owner['id'] ?>">
    <div class="modal-body">
         <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label required">اسم المالك</label><input type="text" class="form-control" name="owner_name" value="<?= htmlspecialchars($owner['owner_name']) ?>" required></div>
            <div class="col-md-6"><label class="form-label required">نوع المالك</label><select class="form-select" name="owner_type" required><?php foreach($owner_types as $type):?><option value="<?=htmlspecialchars($type)?>" <?= ($owner['owner_type'] == $type) ? 'selected' : '' ?>><?=htmlspecialchars($type)?></option><?php endforeach; ?></select></div>
            <div class="col-md-6"><label class="form-label">كود المالك</label><input type="text" class="form-control" name="owner_code" value="<?= htmlspecialchars($owner['owner_code']) ?>"></div>
            <div class="col-md-6"><label class="form-label">رقم الهوية/السجل</label><input type="text" class="form-control" name="id_number" value="<?= htmlspecialchars($owner['id_number']) ?>"></div>
            <div class="col-md-6"><label class="form-label">الجوال</label><input type="text" class="form-control" name="mobile" value="<?= htmlspecialchars($owner['mobile']) ?>"></div>
            <div class="col-md-6"><label class="form-label">البريد الإلكتروني</label><input type="email" class="form-control" name="email" value="<?= htmlspecialchars($owner['email']) ?>"></div>
            <div class="col-md-6"><label class="form-label">الحالة</label><select class="form-select" name="status"><?php foreach($statuses_map as $key => $name):?><option value="<?= htmlspecialchars($key) ?>" <?= ($owner['status'] == $key) ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option><?php endforeach;?></select></div>
            
            <div class="col-12">
                <label class="form-label">الفروع المرتبطة</label>
                <select class="form-select select2-init" name="branches[]" multiple>
                    <?php foreach ($branches_list as $branch): ?>
                        <option value="<?= $branch['id'] ?>" <?= in_array($branch['id'], $current_branch_ids) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($branch['branch_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-12"><label class="form-label">ملاحظات</label><textarea class="form-control" name="notes" rows="2"><?= htmlspecialchars($owner['notes']) ?></textarea></div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn" data-bs-dismiss="modal">إلغاء</a>
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
    </div>
</form>