<!-- src/modules/branches/branches_view.php (النسخة الكاملة والمؤمنة) -->

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div><h2 class="page-title mb-0">إدارة الفروع والكيانات</h2></div>
            <div class="btn-list">
                <button onclick="window.print();" class="btn btn-outline-secondary"><i class="ti ti-printer me-2"></i>طباعة</button>
                <a href="#" class="btn"><i class="ti ti-table-plus me-2"></i>إدخال متعدد</a>
                <a href="#" class="btn"><i class="ti ti-upload me-2"></i>إجراءات متعددة</a>
                <?php if (has_permission('add_branch')): ?>
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=branches/add&view_only=true"><i class="ti ti-plus me-2"></i>إضافة فرع جديد</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="row row-cards mb-4">
            <div class="col-md-3"><div class="card bg-primary text-primary-fg"><div class="card-stamp"><div class="card-stamp-icon bg-white text-primary"><i class="ti ti-building-community"></i></div></div><div class="card-body"><h3 class="card-title m-0">إجمالي الفروع</h3><p class="h1 mt-1 mb-0"><?= $stats['total'] ?? 0 ?></p></div></div></div>
            <div class="col-md-3"><div class="card bg-success text-success-fg"><div class="card-stamp"><div class="card-stamp-icon bg-white text-success"><i class="ti ti-circle-check"></i></div></div><div class="card-body"><h3 class="card-title m-0">الفروع النشطة</h3><p class="h1 mt-1 mb-0"><?= $stats['active'] ?? 0 ?></p></div></div></div>
            <div class="col-md-3"><div class="card bg-info text-info-fg"><div class="card-stamp"><div class="card-stamp-icon bg-white text-info"><i class="ti ti-building-skyscraper"></i></div></div><div class="card-body"><h3 class="card-title m-0">كيانات (منشأة)</h3><p class="h1 mt-1 mb-0"><?= $stats['companies'] ?? 0 ?></p></div></div></div>
            <div class="col-md-3"><div class="card bg-warning text-warning-fg"><div class="card-stamp"><div class="card-stamp-icon bg-white text-warning"><i class="ti ti-user-circle"></i></div></div><div class="card-body"><h3 class="card-title m-0">كيانات (فرد)</h3><p class="h1 mt-1 mb-0"><?= $stats['individuals'] ?? 0 ?></p></div></div></div>
        </div>

        <form action="index.php" method="GET"><input type="hidden" name="page" value="branches">
            <div class="row g-3">
                <div class="col-md-6"><input type="search" name="q" class="form-control" placeholder="ابحث بالاسم، الكود، السجل..." value="<?= htmlspecialchars($filter_q ?? '') ?>"></div>
                <div class="col-md-2"><select class="form-select" name="type"><option value="">كل الأنواع</option><?php foreach($branch_types_filter as $type):?><option value="<?= htmlspecialchars($type) ?>" <?= ($filter_type == $type)?'selected':''?>><?= htmlspecialchars($type) ?></option><?php endforeach;?></select></div>
                <div class="col-md-2"><select class="form-select" name="status"><option value="">كل الحالات</option><?php foreach($statuses_filter as $key => $status):?><option value="<?= htmlspecialchars($key) ?>" <?= ($filter_status == $key)?'selected':''?>><?= htmlspecialchars($status['name']) ?></option><?php endforeach;?></select></div>
                <div class="col-md-2 d-flex align-items-end"><button type="submit" class="btn btn-primary w-100">بحث</button><a href="index.php?page=branches" class="btn btn-ghost-secondary ms-2" title="إعادة تعيين"><i class="ti ti-refresh"></i></a></div>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="table-responsive">
        <table class="table card-table table-vcenter text-nowrap table-hover table-selectable">
            <thead>
                <tr>
                    <th class="w-1"><input class="form-check-input m-0 align-middle" type="checkbox" onchange="toggleAllCheckboxes(this)"></th>
                    <th>م</th><th>الفرع/الشركة</th><th>البيانات الرئيسية</th><th>معلومات التواصل</th><th>نوع الكيان</th><th>الحالة</th><th>العقارات/الوحدات</th><th>ملاحظات</th><th class="w-1"></th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($branches)): ?><tr><td colspan="10" class="text-center p-4">لا توجد نتائج.</td></tr><?php else: foreach ($branches as $branch): ?>
                <tr>
                    <td><input class="form-check-input m-0 align-middle" type="checkbox" name="row_id[]" value="<?= $branch['id'] ?>"></td>
                    <td><span class="text-muted"><?= $branch['id'] ?></span></td>
                    <td><div class="fw-bold"><?= htmlspecialchars($branch['branch_name']) ?></div><div class="text-muted">كود: <?= htmlspecialchars($branch['branch_code'] ?? 'N/A') ?></div></td>
                    <td><div class="fw-bold">السجل: <?= htmlspecialchars($branch['registration_number'] ?? '—') ?></div><div class="text-muted">الضريبي: <?= htmlspecialchars($branch['tax_number'] ?? '—') ?></div></td>
                    <td><div class="fw-bold">الجوال: <?= htmlspecialchars($branch['phone'] ?? '—') ?></div><div class="text-muted" title="<?= htmlspecialchars($branch['email']) ?>">الإيميل: <?= htmlspecialchars($branch['email'] ? substr($branch['email'], 0, 20).'...' : '—') ?></div></td>
                    <td><?= htmlspecialchars($branch['branch_type']) ?></td>
                    <td><span class="badge" style="background-color: <?= htmlspecialchars($statuses_map[$branch['status']]['bg_color'] ?? '#6c757d') ?>; color: #fff;"><?= htmlspecialchars($statuses_map[$branch['status']]['name'] ?? $branch['status']) ?></span></td>
                    <td><div class="fw-bold">العقارات: <?= $branch['properties_count'] ?></div><div class="text-muted">الوحدات: <?= $branch['units_count'] ?></div></td>
                    <td><?php if (!empty($branch['notes'])): ?><i class="ti ti-info-circle text-primary" data-bs-toggle="tooltip" title="<?= htmlspecialchars($branch['notes']) ?>"></i><?php endif; ?></td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-1">
                            <a href="print.php?template=branch_profile_print&id=<?= $branch['id'] ?>" class="btn btn-icon btn-outline-secondary" target="_blank" data-bs-toggle="tooltip" title="طباعة ملف الفرع"><i class="ti ti-printer"></i></a>
                            <?php if (has_permission('edit_branch')): ?><a href="#" class="btn btn-icon btn-outline-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=branches/edit&id=<?= $branch['id'] ?>&view_only=true" title="تعديل"><i class="ti ti-edit"></i></a><?php endif; ?>
                            <?php if (has_permission('delete_branch')): ?><a href="index.php?page=branches/delete&id=<?= $branch['id'] ?>" class="btn btn-icon btn-outline-danger confirm-delete" title="حذف (أرشفة)"><i class="ti ti-trash"></i></a><?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex align-items-center">
        <div class="text-muted">عرض <div class="mx-2 d-inline-block"><form class="d-inline-block" id="limit-form" action="index.php" method="GET"><input type="hidden" name="page" value="branches"><input type="hidden" name="q" value="<?= htmlspecialchars($filter_q ?? '') ?>"><input type="hidden" name="type" value="<?= htmlspecialchars($filter_type ?? '') ?>"><input type="hidden" name="status" value="<?= htmlspecialchars($filter_status ?? '') ?>"><select name="limit" class="form-select form-select-sm" onchange="this.form.submit();"><?php foreach($records_per_page_options as $option): ?><option value="<?= $option ?>" <?= ($limit == $option) ? 'selected' : '' ?>><?= $option ?></option><?php endforeach; ?></select></form></div> سجلات</div>
        <div class="m-auto"><?php render_smart_pagination($current_page, $total_pages, $_GET); ?></div>
        <p class="m-0 text-muted">من أصل <?= $total_records ?> سجل</p>
    </div>
</div>