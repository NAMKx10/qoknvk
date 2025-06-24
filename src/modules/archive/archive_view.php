<?php
// src/modules/archive/archive_view.php (الإصدار النهائي مع الإجراءات الجماعية)

// --- 1. تعريف الجداول القابلة للأرشفة ---
$tables_map = [
    'properties'        => ['display' => 'العقارات',           'name_col' => 'property_name'],
    'units'             => ['display' => 'الوحدات',            'name_col' => 'unit_name'],
    'clients'           => ['display' => 'العملاء',            'name_col' => 'client_name'],
    'suppliers'         => ['display' => 'الموردين',           'name_col' => 'supplier_name'],
    'contracts_rental'  => ['display' => 'عقود الإيجار',          'name_col' => 'contract_number'],
    'contracts_supply'  => ['display' => 'عقود التوريد',        'name_col' => 'contract_number'],
    'documents'         => ['display' => 'الوثائق',              'name_col' => 'document_name'], // <-- هذا هو السطر الجديد
    'users'             => ['display' => 'المستخدمون',         'name_col' => 'full_name'],
    'roles'             => ['display' => 'الأدوار',             'name_col' => 'role_name'],
    'lookup_options'    => ['display' => 'خيارات الإعدادات',   'name_col' => 'option_value'],
    'permission_groups' => ['display' => 'مجموعات الصلاحيات', 'name_col' => 'group_name'],
    'permissions'       => ['display' => 'الصلاحيات',           'name_col' => 'description'],

];

// --- 2. جلب البيانات المؤرشفة ---
$archived_items = [];
foreach ($tables_map as $table => $details) {
    $name_column = $details['name_col'];
    $stmt = $pdo->query("SELECT id, `{$name_column}` as name, deleted_at FROM `{$table}` WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($items) {
        $archived_items[$table] = $items;
    }
}
?>

<div class="page-header d-print-none">
    <div class="container-xl"><h2 class="page-title">الأرشيف (سلة المحذوفات)</h2></div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="alert alert-warning" role="alert"><div class="d-flex"><div><i class="icon ti ti-alert-triangle me-2"></i></div><div><h4 class="alert-title">تحذير!</h4><div class="text-muted">الحذف النهائي سيزيل العنصر من قاعدة البيانات بشكل دائم ولا يمكن التراجع عنه.</div></div></div></div>

        <?php if (empty($archived_items)): ?>
            <div class="card"><div class="card-body text-center text-muted">الأرشيف فارغ حالياً.</div></div>
        <?php else: ?>
            <div class="accordion" id="archiveAccordion">
                <?php foreach ($archived_items as $table => $items): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-<?= $table ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $table ?>">
    <span class="me-auto">
        <?= htmlspecialchars($tables_map[$table]['display']) ?>
        <span class="badge bg-secondary-lt ms-2"><?= count($items) ?></span>
    </span>
</button>
                        </h2>
                        <div id="collapse-<?= $table ?>" class="accordion-collapse collapse" data-bs-parent="#archiveAccordion">
                            <div class="accordion-body">
                                <form method="POST" action="index.php?page=archive/batch_action">
                                    <input type="hidden" name="table" value="<?= $table ?>">
                                    <div class="d-flex gap-2 mb-3">
                                        <select name="action" class="form-select" style="width: auto;">
                                            <option value="">-- إجراء جماعي --</option>
                                            <option value="restore">استعادة المحدد</option>
                                            <option value="force_delete">حذف نهائي للمحدد</option>
                                        </select>
                                        <button type="submit" class="btn btn-primary">تنفيذ</button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table card-table table-vcenter text-nowrap">
                                            <thead>
                                                <tr>
                                                    <th class="w-1"><input class="form-check-input m-0 align-middle" type="checkbox" onchange="$(this).closest('table').find('.item-checkbox').prop('checked', this.checked);"></th>
                                                    <th>الاسم/الرقم</th>
                                                    <th>تاريخ الحذف</th>
                                                    <th class="w-1"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($items as $item): ?>
                                                <tr>
                                                    <td><input class="form-check-input m-0 align-middle item-checkbox" type="checkbox" name="ids[]" value="<?= $item['id'] ?>"></td>
                                                    <td><?= htmlspecialchars($item['name'] ?: "ID: " . $item['id']) ?></td>
                                                    <td><span class="text-muted"><?= $item['deleted_at'] ?></span></td>
                                                    <td class="text-end">
                                                        <a href="index.php?page=archive/restore&table=<?= $table ?>&id=<?= $item['id'] ?>" class="btn btn-sm btn-ghost-success">استعادة</a>
                                                        <a href="index.php?page=archive/force_delete&table=<?= $table ?>&id=<?= $item['id'] ?>" class="btn btn-sm btn-ghost-danger confirm-force-delete">حذف</a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>