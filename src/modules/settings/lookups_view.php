<?php
$stmt = $pdo->query("SELECT * FROM lookup_options WHERE deleted_at IS NULL ORDER BY group_key, display_order, id");
$options = $stmt->fetchAll();
$grouped_options = [];
foreach ($options as $option) {
    if ($option['group_key'] !== $option['option_key']) {
        $grouped_options[$option['group_key']]['options'][] = $option;
    } else {
        $grouped_options[$option['group_key']]['display_name'] = $option['option_value'];
    }
}
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-cogs ms-2"></i>تهيئة مدخلات النظام</h1>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#mainModal" data-bs-url="index.php?page=settings/add_lookup_group&view_only=true" data-bs-title="إضافة مجموعة جديدة">
            <i class="fas fa-plus-circle ms-1"></i>إضافة مجموعة جديدة
        </button>
    </div>
</div>
<div class="alert alert-info"><i class="fas fa-info-circle ms-2"></i>من هنا يمكنك التحكم في كل القوائم والخيارات والحالات وألوانها في النظام.</div>

<?php foreach ($grouped_options as $group_key => $group_data): ?>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <?php echo htmlspecialchars($group_data['display_name'] ?? $group_key); ?>
                <code class="text-muted small">(<?php echo htmlspecialchars($group_key); ?>)</code>
                <button type="button" class="btn btn-sm btn-outline-secondary p-1 lh-1" data-bs-toggle="modal" data-bs-target="#mainModal" data-bs-url="index.php?page=settings/edit_lookup_group&group=<?php echo $group_key; ?>&view_only=true" data-bs-title="تعديل المجموعة" title="تعديل المجموعة"><i class="fas fa-pen fa-sm"></i></button>
            </h5>
            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#mainModal" data-bs-url="index.php?page=settings/add_lookup_option&group=<?php echo $group_key; ?>&view_only=true" data-bs-title="إضافة خيار جديد إلى <?php echo $group_key; ?>">
                <i class="fas fa-plus ms-1"></i> إضافة خيار
            </button>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 20%;">المعاينة</th>
                        <th>القيمة المعروضة</th>
                        <th>المفتاح</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($group_data['options'])): ?>
                        <?php foreach ($group_data['options'] as $option): ?>
                            <tr>
                                <td><span class="badge" style="background-color:<?php echo htmlspecialchars($option['bg_color'] ?? '#6c757d'); ?>; color:<?php echo htmlspecialchars($option['color'] ?? '#ffffff'); ?>; font-size: 0.85em;"><?php echo htmlspecialchars($option['option_value']); ?></span></td>
                                <td><?php echo htmlspecialchars($option['option_value']); ?></td>
                                <td><code><?php echo htmlspecialchars($option['option_key']); ?></code></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#mainModal" data-bs-url="index.php?page=settings/edit_lookup_option&id=<?php echo $option['id']; ?>&view_only=true" data-bs-title="تعديل الخيار" title="تعديل"><i class="fas fa-edit"></i></button>
                                    <a href="index.php?page=settings/delete&id=<?php echo $option['id']; ?>" class="btn btn-sm btn-danger" title="أرشفة" onclick="return confirm('سيتم نقل هذا الخيار إلى الأرشيف. هل أنت متأكد؟');"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center text-muted p-3">لا توجد خيارات في هذه المجموعة بعد.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endforeach; ?>
```</details>

**بعد تحديث هذين الملفين، يجب أن يعود النظام للعمل بشكل كامل ومستقر، مع تفعيل كل ميزات صفحة الإعدادات الجديدة.**

أنا في انتظار تأكيدك للنجاح.