<?php
// src/modules/suppliers/branches_modal_view.php (النسخة المطورة)

if (!isset($_GET['id'])) { die("Supplier ID is required."); }
$supplier_id = $_GET['id'];

$stmt = $pdo->prepare("
    SELECT b.branch_name, b.branch_type 
    FROM branches b 
    JOIN supplier_branches sb ON b.id = sb.branch_id 
    WHERE sb.supplier_id = ? 
    ORDER BY b.branch_name
");
$stmt->execute([$supplier_id]);
$branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="modal-header">
    <h5 class="modal-title">الفروع المرتبطة بالمورد</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body p-0">
    <div class="list-group list-group-flush">
        <?php if (empty($branches)): ?>
            <div class="p-4 text-center text-muted">هذا المورد غير مرتبط بأي فرع.</div>
        <?php else: ?>
            <?php foreach ($branches as $branch): ?>
                <div class="list-group-item">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="text-muted">
                                <?php if ($branch['branch_type'] == 'منشأة'): ?>
                                    <i class="ti ti-building-skyscraper"></i>
                                <?php else: ?>
                                    <i class="ti ti-user-circle"></i>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="col">
                            <div class="text-body d-block"><?= htmlspecialchars($branch['branch_name']); ?></div>
                            <div class="text-muted d-block mt-n1"><?= htmlspecialchars($branch['branch_type']); ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>