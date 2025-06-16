<?php
// هذا الكود يتطلب أن يكون ملف index.php قد قام بتضمين database.php و functions.php
if (!isset($_GET['id'])) { die("Client ID is required."); }
$client_id = $_GET['id'];

// --- تعديل: جلب نوع الفرع أيضًا ---
$stmt = $pdo->prepare("
    SELECT b.branch_name, b.branch_type 
    FROM branches b 
    JOIN client_branches cb ON b.id = cb.branch_id 
    WHERE cb.client_id = ? 
    ORDER BY b.branch_name
");
$stmt->execute([$client_id]);
$branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- === بداية التنسيق الجديد === -->
<style>
    .branch-list-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #eee;
    }
    .branch-list-item:last-child {
        border-bottom: none;
    }
    .branch-icon {
        font-size: 1.2rem;
        color: #6c757d; /* لون رمادي ثانوي */
        margin-left: 1rem; /* استخدام margin-left للغة العربية */
    }
</style>

<div class="list-group list-group-flush">
    <?php if (empty($branches)): ?>
        <p class="text-muted p-3">هذا العميل غير مرتبط بأي فرع.</p>
    <?php else: ?>
        <?php foreach ($branches as $branch): ?>
            <div class="branch-list-item">
                <!-- اختيار أيقونة بناءً على نوع الفرع -->
                <?php if ($branch['branch_type'] == 'منشأة'): ?>
                    <i class="fas fa-building branch-icon"></i>
                <?php else: ?>
                    <i class="fas fa-user branch-icon"></i>
                <?php endif; ?>
                <span><?php echo htmlspecialchars($branch['branch_name']); ?></span>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<!-- === نهاية التنسيق الجديد === -->