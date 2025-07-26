<!-- src/modules/suppliers/suppliers_view.php (النسخة المطورة والمؤمنة بالكامل) -->

<div class="card">
    <div class="card-body">
        <!-- 1. صف العنوان والأزرار -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div><h2 class="page-title mb-0">إدارة الموردين</h2></div>
            <div class="btn-list">
                <button onclick="window.print();" class="btn btn-outline-secondary d-print-none"><i class="ti ti-printer me-2"></i>طباعة</button>
                <a href="#" class="btn"><i class="ti ti-table-plus me-2"></i>إدخال متعدد</a>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"><i class="ti ti-checkup-list me-2"></i>إجراءات متعددة</button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="#" onclick="submitBatchForm('soft_delete')"><i class="ti ti-trash dropdown-item-icon"></i>نقل المحدد للأرشيف</a>
                    </div>
                </div>
                <?php if (has_permission('add_supplier')): ?>
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=suppliers/add&view_only=true">
                    <i class="ti ti-plus me-2"></i>إضافة مورد
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- 2. بطاقات الإحصائيات -->
        <div class="row row-cards mb-4">
            <div class="col-md-3"><div class="card bg-primary text-primary-fg"><div class="card-stamp"><div class="card-stamp-icon bg-white text-primary"><i class="ti ti-truck-delivery"></i></div></div><div class="card-body"><h3 class="card-title m-0">إجمالي الموردين</h3><p class="h1 mt-1 mb-0"><?= $stats['total_suppliers'] ?? 0 ?></p></div></div></div>
            <div class="col-md-3"><div class="card bg-success text-success-fg"><div class="card-stamp"><div class="card-stamp-icon bg-white text-success"><i class="ti ti-user-check"></i></div></div><div class="card-body"><h3 class="card-title m-0">الموردون النشطون</h3><p class="h1 mt-1 mb-0"><?= $stats['active_suppliers'] ?? 0 ?></p></div></div></div>
            <div class="col-md-3"><div class="card bg-info text-info-fg"><div class="card-stamp"><div class="card-stamp-icon bg-white text-info"><i class="ti ti-building-skyscraper"></i></div></div><div class="card-body"><h3 class="card-title m-0">موردون (منشأة)</h3><p class="h1 mt-1 mb-0"><?= $stats['companies'] ?? 0 ?></p></div></div></div>
            <div class="col-md-3"><div class="card bg-warning text-warning-fg"><div class="card-stamp"><div class="card-stamp-icon bg-white text-warning"><i class="ti ti-user-circle"></i></div></div><div class="card-body"><h3 class="card-title m-0">موردون (فرد)</h3><p class="h1 mt-1 mb-0"><?= $stats['individuals'] ?? 0 ?></p></div></div></div>
        </div>

        <!-- 3. قسم الفلترة -->
        <form action="index.php" method="GET"><input type="hidden" name="page" value="suppliers">
            <div class="row g-3">
                <div class="col-md-3"><input type="search" name="q" class="form-control" placeholder="بحث بالاسم، الكود، السجل..." value="<?= htmlspecialchars($filter_q ?? '') ?>"></div>
                <div class="col-md-3"><select class="form-select select2-init" name="branch_id"><option value="">كل الفروع</option><?php foreach($branches_for_filter as $branch):?><option value="<?=$branch['id']?>" <?= ($filter_branch_id == $branch['id'])?'selected':''?>><?=htmlspecialchars($branch['branch_name'])?></option><?php endforeach;?></select></div>
                <div class="col-md-2"><select class="form-select" name="type"><option value="">كل الأنواع</option><?php foreach($supplier_types_for_filter as $type):?><option value="<?=$type?>" <?= ($filter_type == $type)?'selected':''?>><?=htmlspecialchars($type)?></option><?php endforeach;?></select></div>
                <div class="col-md-2"><select class="form-select" name="status"><option value="">كل الحالات</option><?php foreach($statuses_for_filter as $key => $value):?><option value="<?= $key ?>" <?= ($filter_status == $key) ? 'selected' : '' ?>><?=htmlspecialchars($value)?></option><?php endforeach;?></select></div>
                <div class="col-md-2 d-flex"><button type="submit" class="btn btn-primary w-100">بحث</button><a href="index.php?page=suppliers" class="btn btn-ghost-secondary ms-2" title="إعادة تعيين"><i class="ti ti-refresh"></i></a></div>
            </div>
        </form>
    </div>
</div>

<form method="POST" action="index.php?page=suppliers/batch_action" id="batch-form">
    <input type="hidden" name="action" id="batch-action-input">
    <div class="card mt-4">
        <div class="table-responsive">
            <table class="table card-table table-vcenter text-nowrap table-hover table-selectable">
                <thead>
                    <tr>
                        <th class="w-1"><input class="form-check-input m-0 align-middle" type="checkbox" onchange="toggleAllCheckboxes(this)"></th>
                        <th>م</th>
                        <th>المورد</th>
                        <th>الخدمة / السجل</th>
                        <th>الفروع</th>
                        <th>العقود</th>
                        <th>الحالة</th>
                        <th>ملاحظات</th>
                        <th class="w-1">الاجراءات</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($suppliers)): ?><tr><td colspan="9" class="text-center p-4">لا توجد نتائج.</td></tr><?php else: $row_counter = $offset + 1; foreach ($suppliers as $supplier): ?>
                    <tr>
                        <td><input class="form-check-input m-0 align-middle" type="checkbox" name="row_id[]" value="<?= $supplier['id'] ?>"></td>
                        <td><span class="text-muted"><?= $row_counter++ ?></span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="avatar me-2"><?= mb_substr($supplier['supplier_name'], 0, 2) ?></span>
                                <div>
                                    <div class="fw-bold"><?= htmlspecialchars($supplier['supplier_name']) ?></div>
                                    <div class="text-muted">كود: <?= htmlspecialchars($supplier['supplier_code'] ?? 'N/A') ?> • <?= htmlspecialchars($supplier['supplier_type'] ?? '—') ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div><?= htmlspecialchars($supplier['service_type'] ?? '—') ?></div>
                            <div class="text-muted">سجل: <?= htmlspecialchars($supplier['registration_number'] ?? '—') ?></div>
                        </td>
                        <td>
                            <?php if ($supplier['branch_count'] > 0): ?>
                                <a href="#" class="badge bg-blue-lt" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=suppliers/branches_modal&id=<?= $supplier['id'] ?>&view_only=true">
                                    <?= $supplier['branch_count'] ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-green-lt"><?= $supplier['contracts_count'] ?></span></td>
                        <td>
                            <?php $status_key = $supplier['status']; $status_info = $statuses_map[$status_key] ?? null; ?>
                            <span class="badge" style="background-color: <?= htmlspecialchars($status_info['bg_color'] ?? '#6c757d') ?>; color: #fff;">
                                <?= htmlspecialchars($status_info['name'] ?? $status_key) ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($supplier['notes'])): ?>
                                <i class="ti ti-info-circle text-primary" data-bs-toggle="tooltip" title="<?= htmlspecialchars($supplier['notes']) ?>"></i>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="#" class="btn btn-icon btn-outline-secondary" title="طباعة كشف الحساب"><i class="ti ti-file-invoice"></i></a>
                                <a href="print.php?template=supplier_profile_print&id=<?= $supplier['id'] ?>" class="btn btn-icon btn-outline-secondary" target="_blank" title="طباعة ملف المورد"><i class="ti ti-printer"></i></a>
                                <?php if (has_permission('edit_supplier')): ?>
                                <a href="#" class="btn btn-icon btn-outline-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=suppliers/edit&id=<?= $supplier['id'] ?>&view_only=true" title="تعديل"><i class="ti ti-edit"></i></a>
                                <?php endif; ?>
                                <?php if (has_permission('delete_supplier')): ?>
                                <a href="index.php?page=suppliers/delete&id=<?= $supplier['id'] ?>" class="btn btn-icon btn-outline-danger confirm-delete" title="حذف المورد"><i class="ti ti-trash"></i></a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex align-items-center">
            <div class="text-muted">عرض <div class="mx-2 d-inline-block"><form id="limit-form" class="d-inline-block" action="index.php" method="GET"><input type="hidden" name="page" value="suppliers"><?php foreach($_GET as $key => $val){ if($key != 'limit' && $key != 'p') echo "<input type='hidden' name='$key' value='".htmlspecialchars($val)."' />"; }?><select name="limit" class="form-select form-select-sm" onchange="this.form.submit()"><?php foreach($records_per_page_options as $option):?><option value="<?=$option?>" <?= ($limit == $option) ? 'selected' : '' ?>><?=$option?></option><?php endforeach; ?></select></form></div> سجلات</div>
            <div class="m-auto"><?php render_smart_pagination($current_page, $total_pages, $_GET); ?></div>
        </div>
    </div>
</form>