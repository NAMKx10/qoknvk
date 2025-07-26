<!-- src/modules/contracts/contracts_view.php (النسخة النهائية مع دمج الأعمدة والنافذة المنبثقة) -->

<div class="card">
    <div class="card-body">
        <!-- (لا تغيير في هذا الجزء) -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div><h2 class="page-title mb-0">إدارة عقود الإيجار</h2></div>
            <div class="btn-list">
                <a href="#" class="btn"><i class="ti ti-table-plus me-2"></i>إدخال متعدد</a>
                <div class="btn-group"><button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"><i class="ti ti-checkup-list me-2"></i>إجراءات متعددة</button><div class="dropdown-menu"><a class="dropdown-item" href="#" onclick="submitBatchForm('soft_delete')"><i class="ti ti-trash dropdown-item-icon"></i>نقل المحدد للأرشيف</a></div></div>
                <?php if (has_permission('add_contract')): ?><a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=contracts/add&view_only=true"><i class="ti ti-plus me-2"></i>إضافة عقد</a><?php endif; ?>
            </div>
        </div>
        <div class="table-responsive mb-4 border rounded">
            <table class="table table-vcenter card-table table-striped"><thead class="bg-light-lt"><tr><th class="text-center"><i class="ti ti-file-text"></i> عدد العقود</th><th class="text-center"><i class="ti ti-door"></i> الوحدات المؤجرة</th><th class="text-center"><i class="ti ti-ruler-2"></i> المساحة المؤجرة</th><th class="text-center"><i class="ti ti-cash"></i> قيمة العقود النشطة</th><th class="text-center"><i class="ti ti-users"></i> عدد العملاء</th></tr></thead><tbody><tr><td><div><span class="badge bg-primary-lt me-2">الإجمالي:</span> <?= $stats['total_contracts'] ?? 0 ?></div><div><span class="badge bg-success-lt me-2">النشطة:</span> <?= $stats['active_contracts'] ?? 0 ?></div><div><span class="badge bg-warning-lt me-2">المنتهية:</span> <?= $stats['expired_contracts'] ?? 0 ?></div><div><span class="badge bg-secondary-lt me-2">المسودة:</span> <?= $stats['draft_contracts'] ?? 0 ?></div></td><td class="text-center align-middle fs-2 fw-bold"><?= $stats['active_rented_units'] ?? 0 ?></td><td class="text-center align-middle fs-2 fw-bold"><?= number_format($stats['active_rented_area'] ?? 0, 0) ?> م²</td><td class="text-center align-middle fs-2 fw-bold"><?= number_format($stats['active_contracts_value'] ?? 0, 2) ?></td><td><div><span class="badge bg-primary-lt me-2">الإجمالي:</span> <?= $stats['total_clients'] ?? 0 ?></div><div><span class="badge bg-info-lt me-2">منشآت:</span> <?= $stats['company_clients'] ?? 0 ?></div><div><span class="badge bg-warning-lt me-2">أفراد:</span> <?= $stats['individual_clients'] ?? 0 ?></div></td></tr></tbody></table>
        </div>
        <form action="index.php" method="GET"><input type="hidden" name="page" value="contracts"><div class="row g-3"><div class="col-md-3"><input type="search" name="q" class="form-control" placeholder="بحث برقم العقد أو اسم العميل..." value="<?= htmlspecialchars($filter_q ?? '') ?>"></div><div class="col-md-3"><select class="form-select select2-init" name="client_id" data-placeholder="كل العملاء"><option value=""></option><?php foreach($clients_for_filter as $id => $name):?><option value="<?=$id?>" <?= ($filter_client_id == $id)?'selected':''?>><?=htmlspecialchars($name)?></option><?php endforeach;?></select></div><div class="col-md-3"><select class="form-select select2-init" name="property_id" data-placeholder="كل العقارات"><option value=""></option><?php foreach($properties_for_filter as $id => $name):?><option value="<?=$id?>" <?= ($filter_property_id == $id)?'selected':''?>><?=htmlspecialchars($name)?></option><?php endforeach;?></select></div><div class="col-md-2"><select class="form-select" name="status"><option value="">كل الحالات</option><?php foreach($statuses_for_filter as $key => $value):?><option value="<?= $key ?>" <?= ($filter_status == $key) ? 'selected' : '' ?>><?=htmlspecialchars($value)?></option><?php endforeach;?></select></div><div class="col-md-1 d-flex"><button type="submit" class="btn btn-primary w-100">بحث</button><a href="index.php?page=contracts" class="btn btn-ghost-secondary ms-2" title="إعادة تعيين"><i class="ti ti-refresh"></i></a></div></div></form>
    </div>
</div>

<form method="POST" action="index.php?page=contracts/batch_action" id="batch-form">
    <input type="hidden" name="action" id="batch-action-input">
    <div class="card mt-4">
        <div class="table-responsive">
            <table class="table card-table table-vcenter text-nowrap table-hover table-selectable">
                <thead>
                    <tr>
                        <th class="w-1"><input class="form-check-input m-0 align-middle" type="checkbox" onchange="toggleAllCheckboxes(this)"></th>
                        <th>م</th>
                        <th>رقم العقد</th>
                        <th>العميل</th>
                        <th>العقار والوحدات</th>
                        <th>التواريخ</th>
                        <th>مدة العقد</th>
                        <th>القيمة / الدفعة</th>
                        <th>الحالة</th>
                        <th class="w-1">الاجراءات</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($contracts)): ?><tr><td colspan="10" class="text-center p-4">لا توجد نتائج.</td></tr><?php else: $row_counter = $offset + 1; foreach ($contracts as $contract): ?>
                    <tr>
                        <td><input class="form-check-input m-0 align-middle" type="checkbox" name="row_id[]" value="<?= $contract['id'] ?>"></td>
                        <td><span class="text-muted"><?= $row_counter++ ?></span></td>
                        <td><span class="text-muted"><?= htmlspecialchars($contract['contract_number']) ?></span></td>
                        <td><?= htmlspecialchars($contract['client_name']) ?></td>
                        <td>
                            <?php if ($contract['units_count'] > 1): ?>
                                <a href="#" class="badge bg-blue-lt" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=contracts/units_modal&id=<?= $contract['id'] ?>&view_only=true">
                                    <?= $contract['units_count'] ?> وحدات
                                </a>
                                <div class="text-muted"><?= htmlspecialchars($contract['property_names'] ?? '—') ?></div>
                            <?php else: ?>
                                <div class="fw-bold"><?= htmlspecialchars($contract['unit_details'] ?? '—') ?></div>
                                <div class="text-muted"><?= htmlspecialchars($contract['property_names'] ?? '—') ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($contract['start_date']) ?></div>
                            <div class="text-muted"><?= htmlspecialchars($contract['end_date']) ?></div>
                        </td>
                        <td>
                            <?= calculate_contract_duration($contract['start_date'], $contract['end_date']) ?>
                            <div class="text-muted"><?= htmlspecialchars($contract['payment_cycle']) ?></div>
                        </td>
                        <td>
                            <div class="fw-bold"><?= number_format($contract['total_amount'], 2) ?></div>
                            <div class="text-muted"><?= number_format($contract['payment_amount'], 2) ?></div>
                        </td>
                        <td>
                            <?php $status_key = $contract['status']; $status_info = $statuses_map[$status_key] ?? null; ?>
                            <span class="badge" style="background-color: <?= htmlspecialchars($status_info['bg_color'] ?? '#6c757d') ?>; color: #fff;">
                                <?= htmlspecialchars($status_info['name'] ?? $status_key) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-list flex-nowrap">
                                <a href="index.php?page=contracts/view&id=<?= $contract['id'] ?>" class="btn">عرض</a>
                                <?php if (has_permission('edit_contract')): ?>
                                <a href="#" class="btn btn-icon btn-outline-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=contracts/edit&id=<?= $contract['id'] ?>&view_only=true" title="تعديل"><i class="ti ti-edit"></i></a>
                                <?php endif; ?>
                                <?php if (has_permission('delete_contract')): ?>
                                <a href="index.php?page=contracts/delete&id=<?= $contract['id'] ?>" class="btn btn-icon btn-outline-danger confirm-delete" title="حذف العقد"><i class="ti ti-trash"></i></a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex align-items-center">
            <p class="m-0 text-muted">عرض <span><?= count($contracts) ?></span> من <span><?= $total_records ?></span> سجل</p>
            <div class="m-auto"><?php render_smart_pagination($current_page, $total_pages, $_GET); ?></div>
        </div>
    </div>
</form>