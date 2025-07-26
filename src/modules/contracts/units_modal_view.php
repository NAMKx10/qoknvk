<?php
// src/modules/contracts/units_modal_view.php

if (!isset($_GET['id'])) { die("Contract ID is required."); }
$contract_id = (int)$_GET['id'];

$stmt = $pdo->prepare("
    SELECT p.property_name, u.unit_name, u.unit_type, u.area
    FROM units u
    JOIN properties p ON u.property_id = p.id
    JOIN contract_units cu ON u.id = cu.unit_id
    WHERE cu.contract_id = ?
    ORDER BY p.property_name, u.unit_name
");
$stmt->execute([$contract_id]);
$units = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_units = count($units);
$total_area = array_sum(array_column($units, 'area'));
?>

<div class="modal-header">
    <h5 class="modal-title">الوحدات المرتبطة بالعقد (<?= $total_units ?>)</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body p-0">
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-striped">
            <thead>
                <tr>
                    <th>العقار</th>
                    <th>اسم الوحدة</th>
                    <th>نوع الوحدة</th>
                    <th>المساحة (م²)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($units as $unit): ?>
                <tr>
                    <td><?= htmlspecialchars($unit['property_name']) ?></td>
                    <td><?= htmlspecialchars($unit['unit_name']) ?></td>
                    <td><?= htmlspecialchars($unit['unit_type']) ?></td>
                    <td><?= number_format($unit['area'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="bg-light">
                    <td colspan="3" class="fw-bold">الإجمالي</td>
                    <td class="fw-bold"><?= number_format($total_area, 2) ?> م²</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>