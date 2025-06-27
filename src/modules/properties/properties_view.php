<?php
// ملف العرض النظيف - لا يحتوي على أي منطق، فقط يعرض المتغيرات
?>
    
    <!-- 1. البطاقة العلوية الرئيسية (تحتوي على كل شيء) -->
    <div class="card mb-4">
        <div class="card-body">
            <!-- 1a. صف العنوان والأزرار (مع استعادة الأزرار المفقودة) -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="page-title mb-0">إدارة العقارات</h2>
                </div>
                <div class="btn-list">
                    <button onclick="window.print();" class="btn btn-outline-secondary"><i class="ti ti-printer me-2"></i>طباعة</button>
                    <a href="index.php?page=properties/batch_add" class="btn"><i class="ti ti-table-plus me-2"></i>إدخال متعدد</a>

                    <!-- ✨ هنا التغيير: تحويل الزر إلى قائمة منسدلة للإجراءات ✨ -->
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="ti ti-checkup-list me-2"></i>إجراءات متعددة
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#" onclick="submitBatchForm('soft_delete')"> <i class="ti ti-trash dropdown-item-icon"></i>نقل المحدد للأرشيف</a>
                            <a class="dropdown-item" href="#" onclick="redirectToBatchEdit()"><i class="ti ti-edit dropdown-item-icon"></i>تعديل المحدد</a>
                        </div>
                    </div>
                    
                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=properties/add&view_only=true" data-bs-title="إضافة عقار جديد"><i class="ti ti-plus me-2"></i>إضافة عقار</a>
                </div>
            </div>

        <!-- 1b. صف بطاقات الإحصائيات (نسخة طبق الأصل) -->
        <div class="row row-cards mb-4">
            <div class="col-md-6 col-lg-3"><div class="card bg-primary text-primary-fg"><div class="card-stamp"><div class="card-stamp-icon bg-white text-primary"><i class="ti ti-building-arch"></i></div></div><div class="card-body"><h3 class="card-title m-0">إجمالي العقارات</h3><p class="h1 mt-1 mb-0"><?= $total_records ?? 0 ?></p></div></div></div>
            <div class="col-md-6 col-lg-3"><div class="card bg-green text-green-fg"><div class="card-stamp"><div class="card-stamp-icon bg-white text-green"><i class="ti ti-door"></i></div></div><div class="card-body"><h3 class="card-title m-0">إجمالي الوحدات</h3><p class="h1 mt-1 mb-0"><?= $stats['total_units'] ?? 0 ?></p></div></div></div>
            <div class="col-md-6 col-lg-3"><div class="card bg-warning text-warning-fg"><div class="card-stamp"><div class="card-stamp-icon bg-white text-warning"><i class="ti ti-currency-real"></i></div></div><div class="card-body"><h3 class="card-title m-0">إجمالي قيمة العقارات</h3><p class="h1 mt-1 mb-0"><?= number_format($stats['total_value'] ?? 0, 0) ?></p></div></div></div>
            <div class="col-md-6 col-lg-3"><div class="card bg-azure text-azure-fg"><div class="card-stamp"><div class="card-stamp-icon bg-white text-azure"><i class="ti ti-ruler-measure"></i></div></div><div class="card-body"><h3 class="card-title m-0">إجمالي المساحة</h3><p class="h1 mt-1 mb-0"><?= number_format($stats['total_area'] ?? 0, 2) ?> <small>م²</small></p></div></div></div>
        </div>

        <!-- 1c. صف الفلترة والبحث (نسخة طبق الأصل) -->
        <form action="index.php" method="GET">
            <input type="hidden" name="page" value="properties">
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label">بحث شامل</label><input type="search" name="q" class="form-control" placeholder="ابحث..." value="<?= htmlspecialchars($filter_q ?? '') ?>"></div>
                <div class="col-md-2"><label class="form-label">الفرع</label><select class="form-select select2-init" name="branch_id"><option value="">الكل</option><?php foreach($branches_for_filter as $branch):?><option value="<?=$branch['id']?>" <?= ($filter_branch_id == $branch['id']) ? 'selected' : '' ?>><?=htmlspecialchars($branch['branch_name'])?></option><?php endforeach;?></select></div>
                <div class="col-md-2"><label class="form-label">النوع</label><select class="form-select select2-init" name="type"><option value="">الكل</option><?php foreach($property_types_for_filter as $type):?><option value="<?=$type?>" <?= ($filter_type == $type) ? 'selected' : '' ?>><?=htmlspecialchars($type)?></option><?php endforeach;?></select></div>
                <div class="col-md-2"><label class="form-label">التملك</label><select class="form-select" name="ownership"><option value="">الكل</option><option value="ملك">ملك</option><option value="استثمار">استثمار</option></select></div>
                <div class="col-md-1"><label class="form-label">الحالة</label><select class="form-select" name="status"><option value="">الكل</option><?php foreach($statuses_for_filter as $key => $value):?><option value="<?= $key ?>" <?= ($filter_status == $key) ? 'selected' : '' ?>><?=htmlspecialchars($value)?></option><?php endforeach;?></select></div>
                <div class="col-md-2"><label class="form-label"> </label><div class="btn-list"><button type="submit" class="btn btn-primary">تطبيق</button><a href="index.php?page=properties" class="btn" title="إعادة تعيين"><i class="ti ti-refresh"></i></a></div></div>
            </div>
        </form>
    </div>
</div>

<!-- ✨ هنا يبدأ النموذج الخاص ببيانات الجدول فقط ✨ -->
<form method="POST" action="index.php?page=properties/batch_action" id="batch-form">
    <!-- حقل مخفي لتمرير نوع الإجراء -->
    <input type="hidden" name="action" id="batch-action-input">

<!-- بطاقة جدول البيانات الرئيسية (نسخة طبق الأصل) -->
<div class="card mt-4">
    <div class="table-responsive">
        <table class="table card-table table-vcenter text-nowrap table-hover table-selectable">
            <thead>
                <tr>
                    <th class="w-1"><input class="form-check-input m-0 align-middle" type="checkbox" onchange="toggleAllCheckboxes(this)"></th>
                    <th>م</th><th>صورة</th><th>العقار</th><th>المالك / الصك</th><th>قيمة العقار</th><th>الوحدات</th><th>الحالة</th><th>ملاحظات</th><th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($properties)): ?><tr><td colspan="10" class="text-center">لا توجد نتائج.</td></tr><?php else: $row_counter = $offset + 1; foreach($properties as $property): ?>
                <tr>
                    <td><input class="form-check-input m-0 align-middle" type="checkbox" name="row_id[]" value="<?= $property['id'] ?>"></td>
                    <td><span class="text-muted"><?= $row_counter++ ?></span></td>
                    <td><span class="avatar" style="background-image: url(./assets/static/properties/default.jpg)"></span></td>
                    
                    <td>
                        <div class="fw-bold"><?= htmlspecialchars($property['property_name']) ?></div>
                        <div class="text-muted" style="font-size: 0.9em;">
                            <?= htmlspecialchars($property['branch_code'] ?? 'بدون فرع') ?> •
                            <?= htmlspecialchars($property['property_type'] ?? 'بدون نوع') ?> •
                            <?= htmlspecialchars($property['ownership_type'] ?? '') ?> •
                            <?= number_format($property['area'] ?? 0, 0) ?> م² •
                            <?= htmlspecialchars($property['city'] ?? '') ?>
                        </div>
                    </td>

                    <td>
                        <div><?= htmlspecialchars($property['owner_name'] ?? '—') ?></div>
                        <div class="text-muted" style="font-size: 0.9em;">صك: <?= htmlspecialchars($property['deed_number'] ?? '—') ?></div>
                    </td>
                    <td><?= number_format($property['property_value'] ?? 0, 2) ?></td>

                    <td class="text-center"><?= $property['units_count'] ?></td>
                    <td>
                        <?php 
                            $status_key = $property['status'];
                            $status_name = $statuses_map[$status_key]['name'] ?? $status_key;
                            $status_color = $statuses_map[$status_key]['bg_color'] ?? '#6c757d';
                        ?>
                        <span class="badge" style="background-color: <?= htmlspecialchars($status_color) ?>; color: #fff;">
                            <?= htmlspecialchars($status_name) ?>
                        </span>
                    </td>
                    <td><?php if (!empty($property['notes'])): ?><i class="ti ti-info-circle text-primary" data-bs-toggle="tooltip" title="<?= htmlspecialchars($property['notes']) ?>"></i><?php endif; ?></td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-1">
                            <a href="print.php?template=property_profile_print&id=<?= $property['id'] ?>" class="btn btn-icon btn-outline-secondary" target="_blank" title="طباعة"><i class="ti ti-printer"></i></a>
                            <a href="#" class="btn btn-icon btn-outline-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=properties/edit&id=<?= $property['id'] ?>&view_only=true" title="تعديل"><i class="ti ti-edit"></i></a>
                            <a href="index.php?page=properties/delete&id=<?= $property['id'] ?>" class="btn btn-icon btn-outline-danger confirm-delete" title="حذف"><i class="ti ti-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex align-items-center">
         <div class="text-muted">عرض <div class="mx-2 d-inline-block"><form id="limit-form-<?=uniqid()?>" action="index.php" method="GET" class="d-inline-block"><input type="hidden" name="page" value="properties"><?php foreach($_GET as $key => $val){ if($key != 'limit' && $key != 'p') echo "<input type='hidden' name='$key' value='".htmlspecialchars($val)."' />"; }?><select name="limit" class="form-select form-select-sm" onchange="this.form.submit()"><?php foreach($records_per_page_options as $option): ?><option value="<?=$option?>" <?= ($limit == $option) ? 'selected' : '' ?>><?=$option?></option><?php endforeach; ?></select></form></div>سجلات</div>
        <?php render_smart_pagination($current_page, $total_pages, $_GET); ?>
    </div>
</div>
</form>
