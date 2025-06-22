<?php
// src/modules/owners/branches_modal_view.php
if (!isset($_GET['id'])) { die("ID is required."); }
$owner_id = $_GET['id'];

// جلب كل الفروع النشطة
$all_branches = $pdo->query("SELECT id, branch_name FROM branches WHERE status = 'نشط' AND deleted_at IS NULL ORDER BY branch_name ASC")->fetchAll();

// جلب الفروع المرتبطة حالياً بالمالك
$linked_branches_stmt = $pdo->prepare("SELECT branch_id FROM owner_branches WHERE owner_id = ?");
$linked_branches_stmt->execute([$owner_id]);
$linked_branch_ids = $linked_branches_stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<div class="modal-header">
    <h5 class="modal-title">إدارة فروع المالك</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form method="POST" action="index.php?page=owners/handle_update_branches" class="ajax-form">
    <input type="hidden" name="owner_id" value="<?= $owner_id ?>">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <p>حدد الفروع التي ترغب بربط هذا المالك بها.</p>
        <div class="row row-cols-1 row-cols-md-2 g-2">
            <?php foreach($all_branches as $branch): ?>
                <div class="col">
                    <label class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="branches[]" value="<?= $branch['id'] ?>" <?= in_array($branch['id'], $linked_branch_ids) ? 'checked' : '' ?>>
                        <span class="form-check-label"><?= htmlspecialchars($branch['branch_name']) ?></span>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn" data-bs-dismiss="modal">إلغاء</a>
        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
    </div>
</form>