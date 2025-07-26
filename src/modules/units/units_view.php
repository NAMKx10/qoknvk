<!-- src/modules/units/units_view.php (النسخة النهائية المطابقة للنموذج القياسي) -->

<div class="card">
    <div class="card-body">
        <!-- 1. صف العنوان والأزرار -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div><h2 class="page-title mb-0">إدارة الوحدات</h2></div>
            <div class="btn-list">
                <button onclick="window.print();" class="btn btn-outline-secondary d-print-none"><i class="ti ti-printer me-2"></i>طباعة</button>
                <a href="#" class="btn"><i class="ti ti-table-plus me-2"></i>إدخال متعدد</a>
                <a href="#" class="btn"><i class="ti ti-upload me-2"></i>إجراءات متعددة</a>
                <?php if (has_permission('add_unit')): ?>
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=units/add&view_only=true">
                    <i class="ti ti-plus me-2"></i>إضافة وحدة
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- 2. بطاقات الإحصائيات -->
        <div class="row row-cards mb-4">
            <div class="col-md-3"><div class="card bg-primary text-primary-fg"><div class="card-stamp"><div class="card-stamp-icon bg-white text-primary"><i class="ti ti-door"></i></div></div><div class="card-body"><h3 class="card-title m-0">إجمالي الوحدات</h3><p class="h1 mt-1 mb-0"><?= $stats['total_units'] ?? 0 ?></p></div></div></div>
            <div class="col-md-3"><div class="card bg-success text-success-fg"><div class="card-stamp"><div class="card-stamp-icon bg-white text-success"><i class="ti ti-building-cottage"></i></div></div><div class="card-body"><h3 class="card-title m-0">الوحدات المؤجرة</h3><p class="h1 mt-1 mb-0"><?= $stats['rented_units'] ?? 0 ?></p></div></div></div>
            <div class="col-md-3"><div class="card bg-info text-info-fg"><div class="card-stamp"><div class="card-stamp-icon bg-white text-info"><i class="ti ti-key"></i></div></div><div class="card-body"><h3 class="card-title m-0">الوحدات المتاحة</h3><p class="h1 mt-1 mb-0"><?= $stats['available_units'] ?? 0 ?></p></div></div></div>
            <div class="col-md-3"><div class="card bg-azure text-azure-fg"><div class="card-stamp"><div class="card-stamp-icon bg-white text-azure"><i class="ti ti-ruler-2"></i></div></div><div class="card-body"><h3 class="card-title m-0">إجمالي المساحة</h3><p class="h1 mt-1 mb-0"><?= number_format($stats['total_area'] ?? 0, 2) ?> <small>م²</small></p></div></div></div>
        </div>

        <!-- 3. قسم الفلترة -->
        <form action="index.php" method="GET"><input type="hidden" name="page" value="units">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">بحث شامل</label><input type="search" name="q" class="form-control" placeholder="ابحث باسم الوحدة أو كودها..." value="<?= htmlspecialchars($filter_q ?? '') ?>"></div>
                <div class="col-md-3"><label class="form-label">العقار</label><select class="form-select select2-init" name="property_id"><option value="">كل العقارات</option><?php foreach($properties_for_filter as $id => $name):?><option value="<?=$id?>" <?= ($filter_property_id == $id)?'selected':''?>><?=htmlspecialchars($name)?></option><?php endforeach;?></select></div>
                <div class="col-md-2"><label class="form-label">النوع</label><select class="form-select select2-init" name="type"><option value="">كل الأنواع</option><?php foreach($unit_types_for_filter as $type):?><option value="<?=$type?>" <?= ($filter_type == $type)?'selected':''?>><?=htmlspecialchars($type)?></option><?php endforeach;?></select></div>
                <div class="col-md-1"><label class="form-label">الحالة</label><select class="form-select select2-init" name="status"><option value="">كل الحالات</option><?php foreach($statuses_for_filter as $key => $value):?><option value="<?= $key ?>" <?= ($filter_status == $key) ? 'selected' : '' ?>><?=htmlspecialchars($value)?></option><?php endforeach;?></select></div>
                <div class="col-md-2 d-flex align-items-end"><button type="submit" class="btn btn-primary w-100">بحث</button><a href="index.php?page=units" class="btn btn-ghost-secondary ms-2" title="إعادة تعيين"><i class="ti ti-refresh"></i></a></div>
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
                    <th>م</th>
                    <th>رقم الوحدة</th>
                    <th>النوع</th>
                    <th>المساحة</th>
                    <th>اسم الوحدة</th>
                    <th>كود الوحدة</th>
                    <th>العقار</th>
                    <th>كود الفرع</th>
                    <th>الحالة</th>
                    <th>اسم المستأجر</th>
                    <th>ملاحظات</th>
                    <th class="w-1">الاجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($units)): ?>
                    <tr><td colspan="13" class="text-center p-4">لا توجد وحدات.</td></tr>
                <?php else: $row_counter = $offset + 1; foreach($units as $unit): ?>
                <tr>
                    <td><input class="form-check-input m-0 align-middle" type="checkbox" name="row_id[]" value="<?= $unit['id'] ?>"></td>
                    <td><span class="text-muted"><?= $row_counter++ ?></span></td>
                    <td><span class="fw-bold"><?= htmlspecialchars($unit['unit_name']) ?></span></td>
                    <td><?= htmlspecialchars($unit['unit_type']) ?></td>
                    <td><?= htmlspecialchars($unit['area']) ?> م²</td>
                    <td><?= htmlspecialchars($unit['unit_name']) ?></td>
                    <td><span class="text-muted"><?= htmlspecialchars($unit['unit_code'] ?? 'N/A') ?></span></td>
                    <td><a href="index.php?page=properties&q=<?= urlencode($unit['property_name']) ?>"><?= htmlspecialchars($unit['property_name']) ?></a></td>
                    <td><span class="text-muted"><?= htmlspecialchars($unit['branch_code'] ?? 'N/A') ?></span></td>
                    <td>
                        <span class="badge" style="background-color: <?= htmlspecialchars($unit['status_color'] ?? '#6c757d') ?>; color: #fff;">
                            <?= htmlspecialchars($unit['status_name'] ?? $unit['status']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($unit['tenant_name'] ?? '—') ?></td>
                    <td><?php if (!empty($unit['notes'])): ?><i class="ti ti-info-circle text-primary" data-bs-toggle="tooltip" title="<?= htmlspecialchars($unit['notes']) ?>"></i><?php endif; ?></td>
                    <td class="text-end">
                        <div class="btn-list flex-nowrap">
                            <a href="print.php?template=unit_profile_print&id=<?= $unit['id'] ?>" class="btn btn-icon btn-outline-secondary" target="_blank" title="طباعة ملف الوحدة"><i class="ti ti-printer"></i></a>
                            <?php if (has_permission('edit_unit')): ?><a href="#" class="btn btn-icon btn-outline-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=units/edit&id=<?= $unit['id'] ?>&view_only=true" title="تعديل"><i class="ti ti-edit"></i></a><?php endif; ?>
                            <?php if (has_permission('delete_unit')): ?><a href="index.php?page=units/delete&id=<?= $unit['id'] ?>" class="btn btn-icon btn-outline-danger confirm-delete" title="حذف"><i class="ti ti-trash"></i></a><?php endif; ?>    
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex align-items-center">
        <div class="text-muted">عرض <div class="mx-2 d-inline-block"><form id="limit-form-<?=uniqid()?>" class="d-inline-block" action="index.php" method="GET"><input type="hidden" name="page" value="units"><?php foreach($_GET as $key => $val){ if($key != 'limit' && $key != 'p') echo "<input type='hidden' name='$key' value='".htmlspecialchars($val)."' />"; }?><select name="limit" class="form-select form-select-sm" onchange="this.form.submit()"><?php foreach($records_per_page_options as $option):?><option value="<?=$option?>" <?= ($limit == $option) ? 'selected' : '' ?>><?=$option?></option><?php endforeach; ?></select></form></div> سجلات</div>
        <div class="m-auto"><?php render_smart_pagination($current_page, $total_pages, $_GET); ?></div>
    </div>
</div>