<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col"><h2 class="page-title">تهيئة مدخلات النظام</h2></div>
            <div class="col-auto ms-auto d-print-none">
                <?php if (has_permission('add_lookup_group')): ?>
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=settings/add_lookup_group&view_only=true">
                    <i class="ti ti-plus me-2"></i>إضافة مجموعة جديدة
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <?php foreach ($grouped_options as $group_key => $group_data): ?>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">
                                    <?= htmlspecialchars($group_data['group_info']['option_value'] ?? $group_key) ?>
                                    <span class="badge bg-secondary-lt ms-2"><?= count($group_data['options'] ?? []) ?></span>
                                </h3>
                                <code class="text-muted d-block mt-1"><?= htmlspecialchars($group_key) ?></code>
                            </div>
                            <div class="card-actions">
                                <div class="dropdown">
                                    <a href="#" class="btn-action dropdown-toggle" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <?php if (has_permission('add_lookup_option')): ?>
                                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=settings/add_lookup_option&group=<?= $group_key ?>&view_only=true"><i class="ti ti-plus dropdown-item-icon"></i> إضافة خيار</a>
                                        <?php endif; ?>
                                        <?php if (has_permission('edit_lookup_group')): ?>
                                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=settings/edit_lookup_group&group=<?= $group_key ?>&view_only=true"><i class="ti ti-edit dropdown-item-icon"></i> تعديل المجموعة</a>
                                        <?php endif; ?>
                                        <?php if (has_permission('delete_lookup_group')): ?>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item text-danger" href="index.php?page=settings/delete_lookup_group&group=<?= $group_key ?>" class="confirm-delete"><i class="ti ti-trash dropdown-item-icon"></i> حذف المجموعة</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion" id="accordion-<?= $group_key ?>">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-<?= $group_key ?>">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $group_key ?>">
                                        عرض / إخفاء الخيارات
                                    </button>
                                </h2>
                                <div id="collapse-<?= $group_key ?>" class="accordion-collapse collapse" data-bs-parent="#accordion-<?= $group_key ?>">
                                    <div class="list-group list-group-flush list-group-hoverable">
                                        <?php if(!empty($group_data['options'])): foreach($group_data['options'] as $option):?>
                                            <div class="list-group-item">
                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <span class="badge" style="background-color:<?=htmlspecialchars($option['bg_color']??'#6c757d')?>; color:<?=htmlspecialchars($option['color']??'#ffffff')?>;"><?=htmlspecialchars($option['option_value'])?></span>
                                                    </div>
                                                    <div class="col text-truncate">
                                                        <span class="text-reset d-block"><?= htmlspecialchars($option['option_value']) ?></span>
                                                        <div class="d-block text-muted text-truncate mt-n1">
                                                            <code><?= htmlspecialchars($option['option_key']) ?></code>
                                                            <?php if($group_key==='documents_type'): $fields_count=count(json_decode($option['custom_fields_schema']??'[]',true)); ?>
                                                                <span class="ms-3 badge bg-blue-lt"><?=$fields_count?> حقل مخصص</span>
                                                            <?php endif;?>
                                                        </div>
                                                    </div>
                                                    <div class="col-auto">
                                                        <div class="btn-list flex-nowrap">
                                                            <?php if (has_permission('edit_lookup_option')): ?>
                                                            <a href="#" class="btn btn-icon" title="تعديل الخيار" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=settings/edit_lookup_option&id=<?=$option['id']?>&view_only=true"><i class="ti ti-edit"></i></a>
                                                            <?php endif; ?>
                                                            <?php if (has_permission('delete_lookup_option')): ?>
                                                            <a href="index.php?page=settings/delete_lookup_option&id=<?= $option['id'] ?>" class="btn btn-outline-danger btn-icon confirm-delete" title="حذف الخيار"><i class="ti ti-trash"></i></a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; else: ?>
                                            <div class="list-group-item"><p class="text-muted text-center p-3 mb-0">لا توجد خيارات في هذه المجموعة بعد.</p></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>