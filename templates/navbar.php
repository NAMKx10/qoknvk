<?php
// تحديد الصفحة الحالية من الرابط (GET)
$current_page = $_GET['page'] ?? 'dashboard';

// تعريف مجموعات الصفحات لتحديد القائمة النشطة
$base_pages = ['branches', 'properties', 'units', 'clients', 'suppliers'];
$contract_pages = ['contracts', 'supply_contracts', 'financial'];
$admin_pages = ['users', 'roles', 'permissions', 'settings/lookups', 'archive', 'about'];

// معرفة إذا كانت المجموعة نشطة بناءً على الصفحة الحالية
$is_base_active = in_array($current_page, $base_pages);
$is_contract_active = in_array($current_page, $contract_pages);
$is_admin_active = in_array($current_page, $admin_pages);
?>

<ul class="navbar-nav">
  <!-- الرئيسية -->
  <li class="nav-item <?= ($current_page === 'dashboard') ? 'active' : '' ?>">
    <a class="nav-link" href="index.php?page=dashboard">
      <span class="nav-link-icon d-md-none d-lg-inline-block">
        <i class="ti ti-home"></i>
      </span>
      <span class="nav-link-title">الرئيسية</span>
    </a>
  </li>

  <!-- القائمة الأساسية -->
  <li class="nav-item dropdown <?= ($is_base_active) ? 'active' : '' ?>">
    <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" role="button" aria-expanded="<?= ($is_base_active) ? 'true' : 'false' ?>">
      <span class="nav-link-icon d-md-none d-lg-inline-block">
        <i class="ti ti-building"></i>
      </span>
      <span class="nav-link-title">الأساسيات</span>
    </a>
    <div class="dropdown-menu">
      <a class="dropdown-item" href="index.php?page=branches">الفروع</a>
      <a class="dropdown-item" href="index.php?page=owners">الملاك</a>
      <a class="dropdown-item" href="index.php?page=properties">العقارات</a>
      <a class="dropdown-item" href="index.php?page=units">الوحدات</a>
      <a class="dropdown-item" href="index.php?page=clients">العملاء</a>
      <a class="dropdown-item" href="index.php?page=suppliers">الموردين</a>
    </div>
  </li>
  
  <!-- العقود -->
  <li class="nav-item dropdown <?= ($is_contract_active) ? 'active' : '' ?>">
    <a class="nav-link dropdown-toggle" href="#navbar-contracts" data-bs-toggle="dropdown" role="button" aria-expanded="<?= ($is_contract_active) ? 'true' : 'false' ?>">
      <span class="nav-link-icon d-md-none d-lg-inline-block">
        <i class="ti ti-file-text"></i>
      </span>
      <span class="nav-link-title">العقود</span>
    </a>
    <div class="dropdown-menu">
      <a class="dropdown-item" href="index.php?page=contracts">عقود الإيجار</a>
      <a class="dropdown-item" href="index.php?page=supply_contracts">عقود التوريد</a>
    </div>
  </li>
  
  <!-- الإدارة -->
  <li class="nav-item dropdown <?= ($is_admin_active) ? 'active' : '' ?>">
    <a class="nav-link dropdown-toggle" href="#navbar-admin" data-bs-toggle="dropdown" role="button" aria-expanded="<?= ($is_admin_active) ? 'true' : 'false' ?>">
      <span class="nav-link-icon d-md-none d-lg-inline-block">
        <i class="ti ti-settings"></i>
      </span>
      <span class="nav-link-title">الإدارة</span>
    </a>
    <div class="dropdown-menu">
      <a class="dropdown-item" href="index.php?page=documents">الوثائق</a> 
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

<?php
/*
ملاحظات:
- هذا الشيفرة تقوم بتفعيل عناصر القائمة المناسبة بناءً على الصفحة الحالية.
- تم ترتيب وتعليق كل جزء لسهولة القراءة والصيانة.
- يمكن إضافة أو تعديل الصفحات بسهولة بتعديل المصفوفات أعلى الملف.
- لم يتم حذف أو تعديل أي منطق أصلي، فقط ترتيب وتعليق.
*/
?>