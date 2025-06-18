<?php
// جلب كل البيانات اللازمة للنموذج
if (!isset($_GET['id'])) { die("Property ID is required."); }
$property_id = $_GET['id'];

// جلب بيانات العقار المحدد
$stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
$stmt->execute([$property_id]);
$property = $stmt->fetch();
if (!$property) { die("Property not found."); }

// جلب قائمة الفروع للاختيار
$branches_list = $pdo->query("SELECT id, branch_name FROM branches WHERE status = 'نشط' ORDER BY branch_name ASC")->fetchAll();

// جلب أنواع العقارات من جدول الإعدادات
$property_types = $pdo->query("SELECT option_value FROM lookup_options WHERE group_key = 'property_type' AND deleted_at IS NULL")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="modal-header">
    <h5 class="modal-title">تعديل العقار: <?= htmlspecialchars($property['property_name']) ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form method="POST" action="index.php?page=properties/handle_edit" class="ajax-form">
    <input type="hidden" name="id" value="<?= $property['id'] ?>">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label required">اسم العقار</label>
                <input type="text" class="form-control" name="property_name" value="<?= htmlspecialchars($property['property_name']) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">الفرع التابع له</label>
                <select class="form-select" name="branch_id">
                    <option value="">اختر الفرع...</option>
                    <?php foreach($branches_list as $b):?>
                        <option value="<?=$b['id']?>" <?= ($property['branch_id'] == $b['id'])?'selected':'' ?>><?=htmlspecialchars($b['branch_name'])?></option>
                    <?php endforeach;?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">كود العقار</label>
                <input type="text" class="form-control" name="property_code" value="<?= htmlspecialchars($property['property_code']) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">نوع العقار</label>
                <select class="form-select" name="property_type">
                    <option value="">اختر النوع...</option>
                    <?php foreach($property_types as $pt):?>
                        <option value="<?=$pt?>" <?= ($property['property_type'] == $pt)?'selected':'' ?>><?=htmlspecialchars($pt)?></option>
                    <?php endforeach;?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">نوع التملك</label>
                <select class="form-select" name="ownership_type">
                    <option value="ملك" <?= ($property['ownership_type'] == 'ملك')?'selected':'' ?>>ملك</option>
                    <option value="استثمار" <?= ($property['ownership_type'] == 'استثمار')?'selected':'' ?>>استثمار</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">الحالة</label>
                <select class="form-select" name="status">
                    <option value="نشط" <?= ($property['status'] == 'نشط')?'selected':'' ?>>نشط</option>
                    <option value="ملغي" <?= ($property['status'] == 'ملغي')?'selected':'' ?>>ملغي</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">اسم المالك</label>
                <input type="text" class="form-control" name="owner_name" value="<?= htmlspecialchars($property['owner_name']) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">رقم الصك</label>
                <input type="text" class="form-control" name="deed_number" value="<?= htmlspecialchars($property['deed_number']) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">قيمة العقار</label>
                <input type="number" class="form-control" name="property_value" value="<?= htmlspecialchars($property['property_value']) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">الحي</label>
                <input type="text" class="form-control" name="district" value="<?= htmlspecialchars($property['district']) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">المدينة</label>
                <input type="text" class="form-control" name="city" value="<?= htmlspecialchars($property['city']) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">المساحة (م²)</label>
                <input type="number" class="form-control" name="area" value="<?= htmlspecialchars($property['area']) ?>">
            </div>
            <div class="col-lg-12">
                <label class="form-label">ملاحظات</label>
                <textarea class="form-control" name="notes" rows="3"><?= htmlspecialchars($property['notes']) ?></textarea>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn" data-bs-dismiss="modal">إلغاء</a>
        <button type="submit" class="btn btn-primary ms-auto">حفظ التعديلات</button>
    </div>
</form>