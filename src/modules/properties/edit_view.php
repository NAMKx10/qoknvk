<?php
if (!isset($_GET['id'])) { header('Location: index.php?page=properties'); exit(); }
$property_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
$stmt->execute([$property_id]);
$property = $stmt->fetch();
// جلب قائمة الفروع النشطة
$branches_stmt = $pdo->query("SELECT id, branch_name FROM branches WHERE status = 'نشط' ORDER BY branch_name ASC");
$branches_list = $branches_stmt->fetchAll();
if (!$property) { header('Location: index.php?page=properties'); exit(); }
// جلب أنواع العقارات ديناميكياً مع فلترة اسم المجموعة
$property_types_stmt = $pdo->prepare("SELECT option_value FROM lookup_options WHERE group_key = ? AND group_key != option_key AND deleted_at IS NULL ORDER BY display_order, option_value ASC");
$property_types_stmt->execute(['property_type']);
$property_types = $property_types_stmt->fetchAll(PDO::FETCH_COLUMN);

?>

<div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
<form method="POST" action="index.php?page=properties/handle_edit_ajax" class="ajax-form">
<input type="hidden" name="id" value="<?php echo $property['id']; ?>">
<div class="row g-3">
<div class="col-sm-6"><label for="property_name" class="form-label">اسم العقار</label><input type="text" class="form-control" id="property_name" name="property_name" value="<?php echo htmlspecialchars($property['property_name']); ?>" required></div>
<div class="col-sm-6">
    <label for="branch_id" class="form-label">الفرع التابع له</label>
    <select class="form-select select2-init" id="branch_id" name="branch_id" required data-placeholder="اختر الفرع...">
        <option></option>
        <?php foreach ($branches_list as $branch): ?>
            <option value="<?php echo $branch['id']; ?>" <?php echo ($property['branch_id'] == $branch['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($branch['branch_name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
<div class="col-sm-6"><label for="property_code" class="form-label">كود العقار</label><input type="text" class="form-control" id="property_code" name="property_code" value="<?php echo htmlspecialchars($property['property_code']); ?>"></div>
<div class="col-sm-6">
<label for="property_type" class="form-label">نوع العقار</label>
<select class="form-select select2-init" id="property_type" name="property_type" required data-placeholder="اختر نوع العقار...">
<option></option>
<?php foreach ($property_types as $type): ?>
    <option value="<?php echo htmlspecialchars($type); ?>" <?php echo ($property['property_type'] == $type) ? 'selected' : ''; ?>>
        <?php echo htmlspecialchars($type); ?>
    </option>
<?php endforeach; ?>
</select>
</div>
<div class="col-sm-6"><label for="ownership_type" class="form-label">نوع التملك</label><select class="form-select" id="ownership_type" name="ownership_type"><option value="ملك" <?php echo ($property['ownership_type'] == 'ملك') ? 'selected' : ''; ?>>ملك</option><option value="استثمار" <?php echo ($property['ownership_type'] == 'استثمار') ? 'selected' : ''; ?>>استثمار</option></select></div>
<div class="col-sm-6"><label for="status" class="form-label">الحالة</label><select class="form-select" id="status" name="status"><option value="نشط" <?php echo ($property['status'] == 'نشط') ? 'selected' : ''; ?>>نشط</option><option value="ملغي" <?php echo ($property['status'] == 'ملغي') ? 'selected' : ''; ?>>ملغي</option><option value="مؤرشف" <?php echo ($property['status'] == 'مؤرشف') ? 'selected' : ''; ?>>مؤرشف</option></select></div>
<div class="col-sm-6"><label for="owner_name" class="form-label">اسم المالك</label><input type="text" class="form-control" id="owner_name" name="owner_name" value="<?php echo htmlspecialchars($property['owner_name']); ?>"></div>
<div class="col-sm-6"><label for="deed_number" class="form-label">رقم الصك</label><input type="text" class="form-control" id="deed_number" name="deed_number" value="<?php echo htmlspecialchars($property['deed_number']); ?>"></div>
<div class="col-sm-6"><label for="property_value" class="form-label">قيمة العقار</label><input type="number" step="0.01" class="form-control" id="property_value" name="property_value" value="<?php echo htmlspecialchars($property['property_value']); ?>"></div>
<div class="col-sm-6"><label for="city" class="form-label">المدينة</label><input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($property['city']); ?>"></div>
<div class="col-sm-6"><label for="district" class="form-label">الحي</label><input type="text" class="form-control" id="district" name="district" value="<?php echo htmlspecialchars($property['district']); ?>"></div>
<div class="col-sm-6"><label for="area" class="form-label">المساحة (م²)</label><input type="number" step="0.01" class="form-control" id="area" name="area" value="<?php echo htmlspecialchars($property['area']); ?>"></div>
<div class="col-sm-6"><label for="floors_count" class="form-label">عدد الأدوار</label><input type="number" class="form-control" id="floors_count" name="floors_count" value="<?php echo htmlspecialchars($property['floors_count']); ?>"></div>
<div class="col-12"><label for="address" class="form-label">العنوان الوطني</label><textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($property['address']); ?></textarea></div>
<div class="col-12"><label for="notes" class="form-label">ملاحظات</label><textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($property['notes']); ?></textarea></div>
</div>
<hr class="my-4">
<div class="d-flex justify-content-end">
<button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">إلغاء</button>
<button type="submit" class="btn btn-primary">حفظ التعديلات</button>
</div>
</form>
