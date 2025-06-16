<?php
// ... (منطق جلب البيانات يبقى كما هو) ...
$archived_items = [];
$tables_map = [
    'properties' => ['display' => 'العقارات', 'name_col' => 'property_name'],
    'units' => ['display' => 'الوحدات', 'name_col' => 'unit_name'],
    'clients' => ['display' => 'العملاء', 'name_col' => 'client_name'],
    'suppliers' => ['display' => 'الموردين', 'name_col' => 'supplier_name'],
    'contracts_rental' => ['display' => 'عقود الإيجار', 'name_col' => 'contract_number'],
    'contracts_supply' => ['display' => 'عقود التوريد', 'name_col' => 'contract_number'],
    'lookup_options' => ['display' => 'خيارات الإعدادات', 'name_col' => 'option_value'],
    'users' => ['display'=>'المستخدمون', 'name_col'=>'full_name'],
    'roles' => ['display' => 'الأدوار', 'name_col' => 'role_name'],
    'permission_groups' => ['display' => 'مجموعات الصلاحيات', 'name_col' => 'group_name'], // السطر الجديد
    'permissions' => ['display' => 'الصلاحيات', 'name_col' => 'permission_key']

];
foreach ($tables_map as $table => $details) {
    $name_column = $details['name_col'];
    $stmt = $pdo->query("SELECT id, `{$name_column}` as name, deleted_at FROM `{$table}` WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($items) {
        $archived_items[$table] = $items;
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-archive ms-2"></i>الأرشيف (سلة المحذوفات)</h1>
</div>

<div class="alert alert-warning">
    <i class="fas fa-info-circle ms-2"></i>
    هذه الصفحة تعرض كل العناصر التي تم "حذفها" (أرشفتها). يمكنك استعادتها لتعود للظهور في النظام، أو حذفها نهائياً.
</div>

<?php if (empty($archived_items)): ?>
    <div class="alert alert-success text-center">الأرشيف فارغ حالياً.</div>
<?php else: ?>
    <div class="accordion" id="archiveAccordion">
        <?php foreach ($archived_items as $table => $items): ?>
             <div class="accordion-item">
                <h2 class="accordion-header" id="heading-<?php echo $table; ?>">
                    <!-- /// تم تعديل هذا الزر بالكامل /// -->
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $table; ?>">
                       <div class="d-flex w-100 justify-content-between align-items-center">
                           <span><strong>قسم: <?php echo htmlspecialchars($tables_map[$table]['display']); ?></strong><span class="badge bg-secondary me-5">(<?php echo count($items); ?>) عنصر</span></span>
                           </div>
                    </button>
                </h2>
                <div id="collapse-<?php echo $table; ?>" class="accordion-collapse collapse" data-bs-parent="#archiveAccordion">
                    <div class="accordion-body">
                        <form method="POST" action="index.php?page=archive/batch_action">
                            <input type="hidden" name="table" value="<?php echo $table; ?>">
                            <div class="mb-3 d-flex gap-2">
                                <select name="action" class="form-select form-select-sm" style="width: 200px;">
                                    <option value="">-- اختر إجراء جماعي --</option>
                                    <option value="restore">استعادة المحدد</option>
                                    <option value="force_delete">حذف نهائي للمحدد</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">تنفيذ</button>
                            </div>
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 30px;"><input type="checkbox" class="form-check-input" onclick="$('input[name*=\'ids\']').prop('checked', this.checked);"></th>
                                        <th>الاسم/الرقم</th>
                                        <th>تاريخ الحذف</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($items as $item): ?>
                                    <tr>
                                        <td><input type="checkbox" class="form-check-input" name="ids[]" value="<?php echo $item['id']; ?>"></td>
                                        <td><?php echo htmlspecialchars($item['name'] ?: "رقم تسلسلي " . $item['id']); ?></td>
                                        <td><?php echo $item['deleted_at']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>