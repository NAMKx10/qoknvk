<?php
// src/modules/settings/lookups_view.php (الإصدار الاحترافي مع قائمة جانبية)

// --- 1. جلب وتجميع الخيارات ---
$stmt = $pdo->query("SELECT * FROM lookup_options WHERE deleted_at IS NULL ORDER BY group_key, display_order, id");
$options = $stmt->fetchAll(PDO::FETCH_ASSOC);
$grouped_options = [];
foreach ($options as $option) {
    if ($option['group_key'] === $option['option_key']) {
        $grouped_options[$option['group_key']]['display_name'] = $option['option_value'];
    } else {
        $grouped_options[$option['group_key']]['options'][] = $option;
    }
}
?>

<!-- =============================================== -->
<!-- HTML: واجهة تهيئة المدخلات (التصميم الاحترافي)    -->
<!-- =============================================== -->

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">تهيئة مدخلات النظام</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=settings/add_lookup_group&view_only=true">
                    <i class="ti ti-plus me-2"></i>إضافة مجموعة جديدة
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row gx-lg-4">
            
            <!-- العمود الأول: قائمة التنقل -->
            <div class="col-lg-3" style="position: sticky; top: 1rem">
                <div class="list-group list-group-transparent mb-3">
                    <?php foreach ($grouped_options as $group_key => $group_data): ?>
                        <a class="list-group-item list-group-item-action" href="#settings-<?= htmlspecialchars($group_key) ?>">
                            <?= htmlspecialchars($group_data['display_name'] ?? $group_key) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- العمود الثاني: المحتوى والبطاقات -->
            <div class="col-lg-9">
                <div class="alert alert-info" role="alert">
                    <div class="text-muted">من هنا يمكنك التحكم في كل القوائم والخيارات والحالات وألوانها في النظام.</div>
                </div>

                <?php foreach ($grouped_options as $group_key => $group_data): ?>
                    <div class="card card-lg mb-3" id="settings-<?= htmlspecialchars($group_key) ?>">
                        <div class="card-header">
                            <h3 class="card-title">
                                <?= htmlspecialchars($group_data['display_name'] ?? $group_key) ?>
                                <code class="ms-2 text-muted small">(<?= htmlspecialchars($group_key) ?>)</code>
                            </h3>
                            <div class="card-actions">
                                <a href="#" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=settings/edit_lookup_group&group=<?= $group_key ?>&view_only=true">تعديل المجموعة</a>
                                 <a href="index.php?page=settings/delete_lookup_group&group=<?= $group_key ?>" class="btn btn-outline-danger confirm-delete-group">حذف المجموعة</a>
                                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=settings/add_lookup_option&group=<?= $group_key ?>&view_only=true"><i class="ti ti-plus me-1"></i> إضافة خيار</a>
                                <a href="index.php?page=settings/delete_lookup_option&id=<?= $option['id'] ?>" class="btn confirm-delete" title="أرشفة"><i class="ti ti-trash"></i></a>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table card-table table-vcenter table-hover">
                                <!-- نفس محتوى الجدول السابق... -->
                                <thead>
                                    <tr>
                                        <th style="width: 20%;">المعاينة</th>
                                        <th>القيمة المعروضة</th>
                                        <th>المفتاح البرمجي</th>
                                        <?php if($group_key === 'document_type'): ?><th>الحقول المخصصة</th><?php endif; ?>
                                        <th class="w-1"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(!empty($group_data['options'])): foreach ($group_data['options'] as $option): ?>
                                        <tr>
                                            <td><span class="badge" style="background-color:<?= htmlspecialchars($option['bg_color'] ?? '#6c757d') ?>; color:<?= htmlspecialchars($option['color'] ?? '#ffffff') ?>;"><?= htmlspecialchars($option['option_value']) ?></span></td>
                                            <td><?= htmlspecialchars($option['option_value']) ?></td>
                                            <td><code><?= htmlspecialchars($option['option_key']) ?></code></td>
                                            <?php if($group_key === 'document_type'): $custom_fields_count = count(json_decode($option['custom_fields_schema'] ?? '[]', true)); ?>
                                            <td><span class="badge bg-blue-lt"><?= $custom_fields_count ?> حقل</span></td>
                                            <?php endif; ?>
                                            <td class="text-end">
                                                <a href="#" class="btn" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=settings/edit_lookup_option&id=<?= $option['id'] ?>&view_only=true">تعديل</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; else: ?>
                                        <tr><td colspan="5" class="text-center text-muted p-3">لا توجد خيارات في هذه المجموعة بعد.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>