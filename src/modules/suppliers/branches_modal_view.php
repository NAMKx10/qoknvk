<?php
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
<style>.branch-list-item{display:flex;align-items:center;padding:0.75rem 1rem;border-bottom:1px solid #eee;}.branch-list-item:last-child{border-bottom:none;}.branch-icon{font-size:1.2rem;color:#6c757d;margin-left:1rem;}</style>
<div class="list-group list-group-flush">
    <?php if (empty($branches)): ?><p class="text-muted p-3">هذا المورد غير مرتبط بأي فرع.</p><?php else: foreach ($branches as $branch): ?>
    <div class="branch-list-item">
        <?php if ($branch['branch_type'] == 'منشأة'): ?><i class="fas fa-building branch-icon"></i><?php else: ?><i class="fas fa-user branch-icon"></i><?php endif; ?>
        <span><?php echo htmlspecialchars($branch['branch_name']); ?></span>
    </div><?php endforeach; endif; ?>
</div>