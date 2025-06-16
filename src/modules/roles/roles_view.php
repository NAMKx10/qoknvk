<?php
// src/modules/roles/roles_view.php
$stmt  = $pdo->query("SELECT * FROM roles WHERE deleted_at IS NULL ORDER BY id ASC");
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<h1 class="mb-4"><i class="fas fa-user-shield me-1"></i>إدارة الأدوار</h1>

<button type="button" class="btn btn-success mb-3" 
        data-bs-toggle="modal" 
        data-bs-target="#mainModal" 
        data-bs-url="index.php?page=roles/add&view_only=true" 
        data-bs-title="إضافة دور جديد">
    <i class="fas fa-plus-circle me-1"></i>إضافة دور جديد
</button>

<table class="table table-bordered table-striped text-center align-middle">
  <thead class="table-light">
    <tr>
      <th>#</th><th>اسم الدور</th><th>الوصف</th><th>الإجراءات</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($roles as $row): ?>
      <tr>
        <td><?= $row['id']; ?></td>
        <td><?= htmlspecialchars($row['role_name']); ?></td>
        <td><?= htmlspecialchars($row['description']); ?></td>
        <td>
          <a href="index.php?page=roles/edit&id=<?= $row['id']; ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-edit"></i>
          </a>
          <a href="index.php?page=roles/delete&id=<?= $row['id']; ?>" class="btn btn-sm btn-danger"
             onclick="return confirm('تأكيد الحذف؟');">
            <i class="fas fa-trash"></i>
          </a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
