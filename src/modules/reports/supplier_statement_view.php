<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>كشف حساب: <?php echo htmlspecialchars($supplier_info['supplier_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap');
        body { font-family: 'Tajawal', sans-serif; }
        .table th { background-color: #f2f2f2; }
        .debit { color: #198754; font-weight: 500; } /* مدفوع */
        .credit { color: #dc3545; font-weight: 500; } /* مستحق */
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="container mt-4">
        <button onclick="window.print()" class="btn btn-secondary float-end no-print">طباعة</button>
        <h2 class="text-center">كشف حساب مورد</h2>
        <hr>
        <div class="row mb-3">
            <div class="col"><strong>المورد:</strong> <?php echo htmlspecialchars($supplier_info['supplier_name']); ?></div>
            <div class="col text-end"><strong>التاريخ:</strong> <?php echo date('Y-m-d'); ?></div>
        </div>
        <div class="row mb-3">
            <div class="col"><strong>الفترة:</strong> من <?php echo htmlspecialchars($_POST['start_date'] ?: 'البداية'); ?> إلى <?php echo htmlspecialchars($_POST['end_date'] ?: 'النهاية'); ?></div>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>البيان</th>
                    <th>مستحق للمورد (دائن)</th>
                    <th>مدفوع للمورد (مدين)</th>
                    <th>الرصيد</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $balance = $opening_balance; 
                    $total_credit = 0; // مستحق
                    $total_debit = 0;  // مدفوع
                ?>
                <?php if ($show_opening_balance): ?>
                <tr class="table-light">
                    <td colspan="4"><strong>رصيد افتتاحي...</strong></td>
                    <td><strong><?php echo number_format($opening_balance, 2); ?></strong></td>
                </tr>
                <?php endif; ?>

                <?php foreach ($statement_data as $row): ?>
                    <?php 
                        $credit = ($row['type'] === 'due') ? $row['amount'] : 0;
                        $debit = ($row['type'] === 'paid') ? $row['amount'] : 0;
                        $balance += ($credit - $debit);
                        $total_credit += $credit;
                        $total_debit += $debit;
                    ?>
                    <tr>
                        <td><?php echo $row['transaction_date']; ?></td>
                        <td><?php echo htmlspecialchars($row['description'] ?? ''); ?></td>
                        <td class="credit"><?php echo ($credit > 0) ? number_format($credit, 2) : '-'; ?></td>
                        <td class="debit"><?php echo ($debit > 0) ? number_format($debit, 2) : '-'; ?></td>
                        <td><?php echo number_format($balance, 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="table-light fw-bold">
                    <td colspan="2" class="text-start">الإجمالي</td>
                    <td class="credit"><?php echo number_format($total_credit, 2); ?></td>
                    <td class="debit"><?php echo number_format($total_debit, 2); ?></td>
                    <td>-</td>
                </tr>
                <tr class="fw-bold fs-5 table-dark">
                    <td colspan="4" class="text-start">الرصيد النهائي (مستحق للمورد)</td>
                    <td><?php echo number_format($balance, 2); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>
</html>