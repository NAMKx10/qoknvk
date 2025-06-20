<?php
// src/modules/documents/documents_view.php (الإصدار المطور)

// --- (جديد) جلب "قاموس" لترجمة أنواع الوثائق ---
$types_stmt = $pdo->query("SELECT option_key, option_value FROM lookup_options WHERE group_key = 'documents_type'");
$document_type_map = $types_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

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
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($documents)): ?>
                            <tr><td colspan="5" class="text-center p-4">لم تتم إضافة أي وثائق بعد.</td></tr>
                        <?php else: foreach($documents as $doc): ?>
                        <tr>
                            <td>
                                <?php
                                    // (مُحسَّن) استخدام القاموس لعرض الاسم العربي
                                    $type_key = $doc['document_type'];
                                    echo htmlspecialchars($document_type_map[$type_key] ?? $type_key);
                                ?>
                            </td>
                            <td><?= htmlspecialchars($doc['document_number']) ?></td>
                            <td><span class="badge bg-success-lt"><?= htmlspecialchars($doc['status']) ?></span></td>
                            <td><?= htmlspecialchars($doc['expiry_date']) ?></td>
                            <td class="text-end">
                                <a href="#" class="btn">تعديل</a>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>