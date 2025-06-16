<?php
if (!isset($_GET['id'])) { die("ID is required."); }
$client_id = $_GET['id'];

// جلب بيانات العميل الأساسية
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->execute([$client_id]);
$client = $stmt->fetch();
if (!$client) { die("Client not found."); }

// === بداية الإضافة ===
// جلب قائمة الفروع النشطة للاختيار
$branches_stmt = $pdo->query("SELECT id, branch_name FROM branches WHERE status = 'نشط' ORDER BY branch_name ASC");
$branches_list = $branches_stmt->fetchAll();

// جلب الفروع المرتبطة حاليًا بهذا العميل لتحديدها
$current_branches_stmt = $pdo->prepare("SELECT branch_id FROM client_branches WHERE client_id = ?");
$current_branches_stmt->execute([$client_id]);
$current_branch_ids = $current_branches_stmt->fetchAll(PDO::FETCH_COLUMN);
// === نهاية الإضافة ===

?>

<div id="form-error-message" class="alert alert-danger" style="display:none;"></div>

<form method="POST" action="index.php?page=clients/handle_edit_ajax" class="ajax-form">
    <input type="hidden" name="id" value="<?php echo $client['id']; ?>">
    <div class="row g-3">
        <div class="col-sm-6">
            <label for="client_name" class="form-label">اسم العميل/المنشأة</label>
            <input type="text" class="form-control" id="client_name" name="client_name" value="<?php echo htmlspecialchars($client['client_name']); ?>" required>
        </div>
        <div class="col-sm-6">
            <label for="client_type" class="form-label">نوع العميل</label>
            <select class="form-select" id="client_type" name="client_type">
                <option value="فرد" <?php echo ($client['client_type'] == 'فرد') ? 'selected' : ''; ?>>فرد</option>
                <option value="منشأة" <?php echo ($client['client_type'] == 'منشأة') ? 'selected' : ''; ?>>منشأة</option>
            </select>
        </div>
        <div class="col-sm-6">
            <label for="id_number" class="form-label">رقم الهوية/السجل</label>
            <input type="text" class="form-control" id="id_number" name="id_number" value="<?php echo htmlspecialchars($client['id_number']); ?>">
        </div>
        <div class="col-sm-6">
            <label for="tax_number" class="form-label">الرقم الضريبي</label>
            <input type="text" class="form-control" id="tax_number" name="tax_number" value="<?php echo htmlspecialchars($client['tax_number'] ?? ''); ?>">
        </div>
        <div class="col-sm-6">
            <label for="mobile" class="form-label">الجوال</label>
            <input type="text" class="form-control" id="mobile" name="mobile" value="<?php echo htmlspecialchars($client['mobile']); ?>">
        </div>
        <div class="col-sm-6">
            <label for="email" class="form-label">البريد الإلكتروني</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($client['email']); ?>">
        </div>
        <div class="col-sm-6">
            <label for="representative_name" class="form-label">اسم الممثل (للمنشآت)</label>
            <input type="text" class="form-control" id="representative_name" name="representative_name" value="<?php echo htmlspecialchars($client['representative_name']); ?>">
        </div>
        <div class="col-sm-6">
            <label for="status" class="form-label">الحالة</label>
            <select class="form-select" id="status" name="status">
                <option value="نشط" <?php echo ($client['status'] == 'نشط') ? 'selected' : ''; ?>>نشط</option>
                <option value="ملغي" <?php echo ($client['status'] == 'ملغي') ? 'selected' : ''; ?>>ملغي</option>
            </select>
        </div>
        
        <!-- === بداية الإضافة: قائمة الفروع === -->
        <div class="col-12">
            <label for="branches" class="form-label">الفروع المرتبطة (اختياري)</label>
            <select class="form-select select2-init" id="branches" name="branches[]" multiple data-placeholder="اختر فرعًا أو أكثر...">
                <?php foreach ($branches_list as $branch): ?>
                    <option value="<?php echo $branch['id']; ?>" <?php echo in_array($branch['id'], $current_branch_ids) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($branch['branch_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <!-- === نهاية الإضافة === -->

        <div class="col-12">
            <label for="address" class="form-label">العنوان الوطني</label>
            <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($client['address']); ?></textarea>
        </div>
         <div class="col-12">
            <label for="notes" class="form-label">ملاحظات</label>
            <textarea class="form-control" id="notes" name="notes" rows="2"><?php echo htmlspecialchars($client['notes'] ?? ''); ?></textarea>
        </div>

    </div>
    <hr class="my-4">
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
    </div>
</form>