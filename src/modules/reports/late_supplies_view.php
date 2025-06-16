<!DOCTYPE html>
<html lang="ar" dir="rtl"><head><meta charset="UTF-8"><title>تقرير متأخرات التوريد</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><style>@import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap'); body { font-family: 'Tajawal', sans-serif; } .table th { background-color: #f2f2f2; } .late-days { color: #dc3545; font-weight: bold; } @media print { .no-print { display: none; } }</style></head>
<body>
    <div class="container-fluid mt-4">
        <button onclick="window.print()" class="btn btn-secondary float-end no-print">طباعة</button>
        <h2 class="text-center">تقرير متأخرات التوريد (الموردين)</h2>
        <p class="text-center text-muted">المتأخرات حتى تاريخ: <?php echo htmlspecialchars($as_of_date); ?></p><hr>
        <table class="table table-bordered table-hover">
            <thead class="table-dark"><tr><th>المورد</th><th>العقد رقم</th><th>العقار</th><th>تاريخ الاستحقاق</th><th>المبلغ المستحق</th><th>المدفوع</th><th>المتبقي</th><th>أيام التأخير</th></tr></thead>
            <tbody>
                <?php if (empty($late_payments)): ?>
                    <tr><td colspan="8" class="text-center alert alert-success">لا توجد دفعات توريد متأخرة.</td></tr>
                <?php else: ?>
                    <?php foreach ($late_payments as $payment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment['party_name']); ?></td>
                            <td><?php echo htmlspecialchars($payment['contract_number']); ?></td>
                            <td><?php echo htmlspecialchars($payment['property_name']); ?></td>
                            <td><?php echo $payment['due_date']; ?></td>
                            <td><?php echo number_format($payment['amount_due'], 2); ?></td>
                            <td><?php echo number_format($payment['amount_paid'], 2); ?></td>
                            <td><?php echo number_format($payment['remaining_amount'], 2); ?></td>
                            <td class="late-days"><?php echo $payment['days_late']; ?> يوم</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body></html>