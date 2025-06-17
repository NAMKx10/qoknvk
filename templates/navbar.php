<?php
$current_page = $_GET['page'] ?? 'dashboard';
// تعريف المجموعات لتحديد القائمة النشطة
$base_pages = ['branches', 'properties', 'units', 'clients', 'suppliers'];
$contract_pages = ['contracts', 'supply_contracts', 'financial'];
$admin_pages = ['users', 'roles', 'permissions', 'settings/lookups', 'archive', 'about'];

$is_base_active = in_array($current_page, $base_pages);
$is_contract_active = in_array($current_page, $contract_pages);
$is_admin_active = in_array($current_page, $admin_pages);
?>

<ul class="navbar-nav">
  <li class="nav-item <?= ($current_page === 'dashboard') ? 'active' : '' ?>">
    <a class="nav-link" href="index.php?page=dashboard"><span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-home"></i></span><span class="nav-link-title">الرئيسية</span></a>
  </li>
  <li class="nav-item dropdown <?= ($is_base_active) ? 'active' : '' ?>">
    <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" role="button" aria-expanded="<?= ($is_base_active) ? 'true' : 'false' ?>"><span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-building-community"></i></span><span class="nav-link-title">الإدارة الأساسية</span></a>
    <div class="dropdown-menu">
      <a class="dropdown-item" href="index.php?page=branches">الفروع</a>
      <a class="dropdown-item" href="index.php?page=properties">العقارات</a>
      <a class="dropdown-item" href="index.php?page=units">الوحدات</a>
      <a class="dropdown-item" href="index.php?page=clients">العملاء</a>
      <a class="dropdown-item" href="index.php?page=suppliers">الموردين</a>
    </div>
  </li>
  <li class="nav-item dropdown <?= ($is_contract_active) ? 'active' : '' ?>">
    <a class="nav-link dropdown-toggle" href="#navbar-contracts" data-bs-toggle="dropdown" role="button" aria-expanded="<?= ($is_contract_active) ? 'true' : 'false' ?>"><span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-file-invoice"></i></span><span class="nav-link-title">العقود والمالية</span></a>
    <div class="dropdown-menu">
      <a class="dropdown-item" href="index.php?page=contracts">عقود الإيجار</a>
      <a class="dropdown-item" href="index.php?page=supply_contracts">عقود التوريد</a>
    </div>
  </li>
  <li class="nav-item dropdown <?= ($is_admin_active) ? 'active' : '' ?>">
    <a class="nav-link dropdown-toggle" href="#navbar-admin" data-bs-toggle="dropdown" role="button" aria-expanded="<?= ($is_admin_active) ? 'true' : 'false' ?>"><span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-settings"></i></span><span class="nav-link-title">إدارة النظام</span></a>
    <div class="dropdown-menu">
      <a class="dropdown-item" href="index.php?page=users">المستخدمين</a>
      <a class="dropdown-item" href="index.php?page=roles">الأدوار</a>
      <a class="dropdown-item" href="index.php?page=permissions">الصلاحيات</a>
      <a class="dropdown-item" href="index.php?page=settings/lookups">تهيئة المدخلات</a>
      <div class="dropdown-divider"></div>
      <a class="dropdown-item" href="index.php?page=archive">الأرشيف</a>
      <a class="dropdown-item" href="index.php?page=about">حول النظام</a>
    </div>
  </li>
</ul>
