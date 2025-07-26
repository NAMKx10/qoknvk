<?php
// src/modules/contracts/view_view.php (النسخة المطورة بالكامل)

if (!isset($_GET['id'])) { header('Location: index.php?page=contracts'); exit(); }
$contract_id = (int)$_GET['id'];

// 1. جلب بيانات العقد الأساسية مع اسم العميل
$stmt_contract = $pdo->prepare("SELECT cr.*, c.client_name FROM contracts_rental cr JOIN clients c ON cr.client_id = c.id WHERE cr.id = ? AND cr.deleted_at IS NULL");
$stmt_contract->execute([$contract_id]);
$contract = $stmt_contract->fetch();
if (!$contract) { header('Location: index.php?page=contracts'); exit(); }

// 2. جلب الوحدات المرتبطة بالعقد
$stmt_units = $pdo->prepare("
    SELECT u.unit_name, u.unit_type, u.area 
    FROM units u 
    JOIN contract_units cu ON u.id = cu.unit_id 
    WHERE cu.contract_id = ?
");
$stmt_units->execute([$contract_id]);
$units = $stmt_units->fetchAll();
$total_area = array_sum(array_column($units, 'area'));

// 3. جلب جدول الدفعات
$payments_stmt = $pdo->prepare("SELECT * FROM payment_schedules WHERE contract_type = 'rental' AND contract_id = ? ORDER BY due_date ASC");
$payments_stmt->execute([$contract_id]);
$payments = $payments_stmt->fetchAll();

// 4. جلب خريطة الحالات
$statuses_map_stmt = $pdo->query("SELECT option_key, option_value, bg_color, color FROM lookup_options WHERE group_key = 'status'");
$statuses_map = [];
foreach($statuses_map_stmt->fetchAll(PDO::FETCH_ASSOC) as $status) {
    $statuses_map[$status['option_key']] = $status;
}
$payment_statuses = [];
foreach($pdo->query("SELECT option_key, option_value, bg_color, color FROM lookup_options WHERE group_key = 'payment_status'")->fetchAll(PDO::FETCH_ASSOC) as $s) {
    $payment_statuses[$s['option_key']] = $s;
}

?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">تفاصيل العقد رقم: <?= htmlspecialchars($contract['contract_number']); ?></h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="index.php?page=contracts" class="btn"><i class="ti ti-arrow-left me-2"></i>العودة للقائمة</a>
                    <a href="#" class="btn btn-outline-secondary"><i class="ti ti-file-invoice me-2"></i>كشف حساب الدفعات</a>
                    <?php if (has_permission('edit_contract')): ?>
                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=contracts/edit&id=<?= $contract['id'] ?>&view_only=true"><i class="ti ti-edit me-2"></i>تعديل</a>
                    <?php endif; ?>
                    <?php if (has_permission('delete_contract')): ?>
                    <a href="index.php?page=contracts/delete&id=<?= $contract['id'] ?>" class="btn btn-outline-danger confirm-delete"><i class="ti ti-trash me-2"></i>حذف</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row g-4">
            <!-- العمود الأيمن: تفاصيل العقد والوحدات -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header"><h3 class="card-title">المعلومات الأساسية للعقد</h3></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3"><strong>العميل:</strong> <?= htmlspecialchars($contract['client_name']) ?></div>
                            <div class="col-md-6 mb-3"><strong>رقم العقد:</strong> <?= htmlspecialchars($contract['contract_number']) ?></div>
                            <div class="col-md-6 mb-3"><strong>تاريخ البداية:</strong> <?= htmlspecialchars($contract['start_date']) ?></div>
                            <div class="col-md-6 mb-3"><strong>تاريخ النهاية:</strong> <?= htmlspecialchars($contract['end_date']) ?></div>
                            <div class="col-md-6 mb-3"><strong>مدة العقد:</strong> <?php $start = new DateTime($contract['start_date']); $end = new DateTime($contract['end_date']); $interval = $start->diff($end); echo $interval->format('%y سنة, %m شهر, %d يوم'); ?></div>
                            <div class="col-md-6 mb-3"><strong>الحالة:</strong> <span class="badge" style="background-color: <?= $statuses_map[$contract['status']]['bg_color'] ?? '#6c757d' ?>; color: <?= $statuses_map[$contract['status']]['color'] ?? '#fff' ?>;"><?= htmlspecialchars($statuses_map[$contract['status']]['option_value'] ?? $contract['status']) ?></span></div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header"><h3 class="card-title">الوحدات المؤجرة (<?= count($units) ?>)</h3></div>
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter">
                            <thead><tr><th>اسم الوحدة</th><th>النوع</th><th>المساحة (م²)</th></tr></thead>
                            <tbody>
                                <?php foreach($units as $unit): ?>
                                <tr><td><?= htmlspecialchars($unit['unit_name']) ?></td><td><?= htmlspecialchars($unit['unit_type']) ?></td><td><?= number_format($unit['area'], 2) ?></td></tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot><tr class="bg-light"><td colspan="2" class="fw-bold">الإجمالي</td><td class="fw-bold"><?= number_format($total_area, 2) ?> م²</td></tr></tfoot>
                        </table>
                    </div>
                </div>
                
                <?php if(!empty($contract['notes'])): ?>
                <div class="card">
                    <div class="card-header"><h3 class="card-title">ملاحظات العقد</h3></div>
                    <div class="card-body"><p class="card-text"><?= nl2br(htmlspecialchars($contract['notes'])) ?></p></div>
                </div>
                <?php endif; ?>
            </div>

            <!-- العمود الأيسر: جدول الدفعات -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">جدول الدفعات</h3></div>
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter">
                            <thead><tr><th>#</th><th>تاريخ الاستحقاق</th><th>المبلغ</th><th>الحالة</th></tr></thead>
                            <tbody>
                                <?php foreach ($payments as $index => $payment): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= $payment['due_date'] ?></td>
                                    <td><?= formatCurrency($payment['amount_due']) ?></td>
                                    <td><span class="badge bg-<?= ($payment['status'] == 'مدفوع بالكامل') ? 'success' : 'warning' ?>-lt"><?= $payment['status'] ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>