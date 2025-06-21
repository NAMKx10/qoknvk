<?php
// src/modules/documents/documents_view.php (الإصدار المصحح للألوان والأزرار)

// --- جلب قاموس أنواع الوثائق لترجمة المفتاح إلى اسم ---
$types_stmt = $pdo->query("SELECT option_key, option_value FROM lookup_options WHERE group_key = 'documents_type'");
$document_type_map = $types_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// --- جلب قاموس الحالات وألوانها ---
// --- (جديد ومبسط) جلب قاموس الحالات ---
$status_map_stmt = $pdo->query("SELECT option_key, option_value, bg_color, color FROM lookup_options WHERE group_key = 'status'");
$status_map = $status_map_stmt->fetchAll(PDO::FETCH_KEY_PAIR | PDO::FETCH_GROUP);

// --- جلب كل الوثائق للعرض ---
$stmt = $pdo->query("SELECT * FROM documents WHERE deleted_at IS NULL ORDER BY id DESC");
$documents = $stmt->fetchAll();
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col"><h2 class="page-title">إدارة الوثائق</h2></div>
            <div class="col-auto ms-auto d-print-none">
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=documents/add&view_only=true">
                    <i class="ti ti-plus me-2"></i>إضافة وثيقة جديدة
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="table-responsive">
                <table class="table card-table table-vcenter text-nowrap">
                    <thead>
                        <tr>
                            <th>نوع الوثيقة</th>
                            <th>رقم الوثيقة</th>
                            <th>الحالة</th>
                            <th>تاريخ الانتهاء</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($documents)): ?>
                            <tr><td colspan="5" class="text-center p-4">لم تتم إضافة أي وثائق بعد.</td></tr>
                        <?php else: foreach($documents as $doc): ?>
                        <tr>
                            <td><?= htmlspecialchars($document_type_map[$doc['document_type']] ?? $doc['document_type']) ?></td>
                            <td><?= htmlspecialchars($doc['document_number']) ?></td>
                            <td>
                                <?php 
                                    $status_key = $doc['status'];
                                    $status_name = $status_map[$status_key]['value'] ?? $status_key;
                                    
                                    // (مُصحَّح) التحقق من وجود اللون قبل استخدامه
                                    $bg_color = !empty($status_map[$status_key]['bg_color']) ? $status_map[$status_key]['bg_color'] : '#6c757d';
                                    $color = !empty($status_map[$status_key]['color']) ? $status_map[$status_key]['color'] : '#ffffff';
                                ?>
                                <span class="badge" style="background-color: <?= $bg_color ?>; color: <?= $color ?>;"><?= htmlspecialchars($status_name) ?></span>
                            </td>
                            <td><?= htmlspecialchars($doc['expiry_date']) ?></td>
                            <td class="text-end">
                                <!-- (مُصحَّح) إعادة حاوية الأزرار -->
                                <div class="btn-list flex-nowrap">
                                    <a href="#" class="btn" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=documents/edit&id=<?= $doc['id'] ?>&view_only=true">
                                        تعديل
                                    </a>
                                    <a href="index.php?page=documents/delete&id=<?= $doc['id'] ?>" class="btn btn-outline-danger btn-icon confirm-delete" title="حذف الوثيقة">
                                        <i class="ti ti-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>