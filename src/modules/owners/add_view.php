<?php
$statuses_map = $pdo->query("SELECT option_key, option_value FROM lookup_options WHERE group_key = 'status' AND option_key != 'status' AND deleted_at IS NULL")->fetchAll(PDO::FETCH_KEY_PAIR);
// === جلب البيانات الديناميكية ===

// 1. جلب أنواع الملاك من الإعدادات (باستخدام group_key الصحيح: entity_type)
$owner_types = $pdo->query("SELECT option_value FROM lookup_options WHERE group_key = 'entity_type' AND option_key != 'entity_type' AND deleted_at IS NULL")->fetchAll(PDO::FETCH_COLUMN);

// 2. جلب قائمة الفروع النشطة
$branches_list = $pdo->query("SELECT id, branch_name FROM branches WHERE status = 'Active' AND deleted_at IS NULL ORDER BY branch_name ASC")->fetchAll();
?>

<div class="modal-header">
    <h5 class="modal-title">إضافة مالك جديد</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<form method="POST" action="index.php?page=owners/handle_add" class="ajax-form">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label required">اسم المالك</label><input type="text" class="form-control" name="owner_name" required></div>
            
            <div class="col-md-6">
                <label class="form-label required">نوع المالك</label>
                <select class="form-select" name="owner_type" required>
                    <!-- يتم الآن بناء الخيارات ديناميكيًا من قاعدة البيانات -->
                    <?php foreach($owner_types as $type): ?>
                        <option value="<?=htmlspecialchars($type)?>"><?=htmlspecialchars($type)?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-6"><label class="form-label">كود المالك</label><input type="text" class="form-control" name="owner_code"></div>
            <div class="col-md-6"><label class="form-label">رقم الهوية/السجل</label><input type="text" class="form-control" name="id_number"></div>
            <div class="col-md-6"><label class="form-label">الجوال</label><input type="text" class="form-control" name="mobile"></div>
<div class="col-md-6"><label class="form-label">البريد الإلكتروني</label><input type="email" class="form-control" name="email"></div>

<!-- القسم الجديد والموحد -->
<div class="col-md-6">
    <label class="form-label">الحالة</label>
    <select class="form-select" name="status">
        <?php
            // استعلام جلب الحالات
            $statuses_map = $pdo->query("SELECT option_key, option_value FROM lookup_options WHERE group_key = 'status' AND option_key != 'status' AND deleted_at IS NULL")->fetchAll(PDO::FETCH_KEY_PAIR);
            foreach($statuses_map as $key => $name):
        ?>
            <option value="<?= htmlspecialchars($key) ?>" <?= ($key == 'Active') ? 'selected' : '' ?>>
                <?= htmlspecialchars($name) ?>
            </option>
        <?php endforeach;?>
    </select>
</div>

<div class="col-12">
    <label class="form-label">الفروع المرتبطة</label>
    <select class="form-select select2-init" name="branches[]" multiple>
        <?php foreach($branches_list as $b):?>
            <option value="<?=$b['id']?>"><?=htmlspecialchars($b['branch_name'])?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="col-12"><label class="form-label">ملاحظات</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
        </div>
    </div>
    <div class="modal-footer">
    <a href="#" class="btn" data-bs-dismiss="modal">إلغاء</a>
    <button type="submit" class="btn btn-primary">حفظ المالك</button>
</div>
</form>