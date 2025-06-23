
<?php
/**
 * routes/web.php
 * 
 * خريطة النظام - القائمة البيضاء للصفحات المسموح بها.
 * يحتوي هذا الملف على جميع المسارات التي يمكن للمستخدم الوصول إليها،
 * بالإضافة إلى عنوان كل صفحة ومسار ملف العرض (View) الخاص بها.
 */

return [

    'dashboard'         => ['path' => 'dashboard/dashboard_view.php', 'title' => 'لوحة التحكم'],
    'login'             => ['path' => 'login/login_view.php', 'title' => 'تسجيل الدخول'],
    'about'             => ['path' => 'about/about_view.php', 'title' => 'حول النظام'],
    // الإدارة الأساسية
    'owners'            => ['path' => 'owners/owners_view.php', 'title' => 'إدارة الملاك'],
    'owners/add'        => ['path' => 'owners/add_view.php', 'title' => 'إضافة مالك'],
    'owners/edit'       => ['path' => 'owners/edit_view.php', 'title' => 'تعديل مالك'],
    'owners/branches_modal' => ['path' => 'owners/branches_modal_view.php', 'title' => 'إدارة فروع المالك'],
    'branches'          => ['path' => 'branches/branches_view.php', 'title' => 'إدارة الفروع'],
    'branches/add'      => ['path' => 'branches/add_view.php', 'title' => 'إضافة فرع'],
    'branches/edit'     => ['path' => 'branches/edit_view.php', 'title' => 'تعديل فرع'],
    'properties'        => ['path' => 'properties/properties_view.php', 'title' => 'إدارة العقارات'],
    'properties/add'    => ['path' => 'properties/add_view.php', 'title' => 'إضافة عقار'],
    'properties/edit'   => ['path' => 'properties/edit_view.php', 'title' => 'تعديل عقار'],
    'units'             => ['path' => 'units/units_view.php', 'title' => 'إدارة الوحدات'],
    'units/add'         => ['path' => 'units/add_view.php', 'title' => 'إضافة وحدة'],
    'clients'           => ['path' => 'clients/clients_view.php', 'title' => 'إدارة العملاء'],
    'clients/add'       => ['path' => 'clients/add_view.php', 'title' => 'إضافة عميل'],
    'suppliers'         => ['path' => 'suppliers/suppliers_view.php', 'title' => 'إدارة الموردين'],
    'suppliers/add'     => ['path' => 'suppliers/add_view.php', 'title' => 'إضافة مورد'],
    // العقود والمالية
    'contracts'         => ['path' => 'contracts/contracts_view.php', 'title' => 'عقود الإيجار'],
    'contracts/view'    => ['path' => 'contracts/view_view.php', 'title' => 'تفاصيل العقد'],
    'supply_contracts'  => ['path' => 'supply_contracts/supply_contracts_view.php', 'title' => 'عقود التوريد'],
    'supply_contracts/view' => ['path' => 'supply_contracts/view_view.php', 'title' => 'تفاصيل العقد'],
    // إدارة النظام
    'documents'         => ['path' => 'documents/documents_view.php', 'title' => 'إدارة الوثائق'],
    'documents/add'     => ['path' => 'documents/add_view.php', 'title' => 'إضافة وثيقة'],
    'documents/edit'    => ['path' => 'documents/edit_view.php', 'title' => 'تعديل وثيقة'],
    'documents/get_custom_fields_schema_ajax' => ['path' => ''], // معالج فقط
    'documents/get_entities_for_linking_ajax' => ['path' => ''],
    'documents/get_linked_entities_ajax'      => ['path' => ''],
    'documents/add_link_ajax'                 => ['path' => ''],
    'documents/delete_link_ajax'              => ['path' => ''],
    'users'             => ['path' => 'users/users_view.php', 'title' => 'إدارة المستخدمين'],
    'users/add'         => ['path' => 'users/add_view.php', 'title' => 'إضافة مستخدم'],
    'users/edit'        => ['path' => 'users/edit_view.php', 'title' => 'تعديل مستخدم'],
    'users/delete'      => ['path' => '', 'title' => 'حذف مستخدم'], // معالجة فقط
    'roles'             => ['path' => 'roles/roles_view.php', 'title' => 'إدارة الأدوار'],
    'roles/add'         => ['path' => 'roles/add_view.php', 'title' => 'إضافة دور'],
    'roles/edit'        => ['path' => 'roles/edit_view.php', 'title' => 'تعديل الصلاحيات'],
    'roles/edit_role'   => ['path' => 'roles/edit_role_view.php', 'title' => 'تعديل الدور'],
    'permissions'       => ['path' => 'permissions/permissions_view.php', 'title' => 'إدارة الصلاحيات'],
    'permissions/add_group'     => ['path' => 'permissions/add_group_view.php', 'title' => 'إضافة مجموعة'],
    'permissions/edit_group'    => ['path' => 'permissions/edit_group_view.php', 'title' => 'تعديل مجموعة'],
    'permissions/delete_group'  => ['path' => ''], // معالجة فقط
    'permissions/add'           => ['path' => 'permissions/add_view.php', 'title' => 'إضافة صلاحية'],
    'permissions/edit'          => ['path' => 'permissions/edit_view.php', 'title' => 'تعديل صلاحية'],
    'permissions/delete'        => ['path' => ''], // معالجة فقط
    'archive'           => ['path' => 'archive/archive_view.php', 'title' => 'الأرشيف'],
    'settings/lookups'              => ['path' => 'settings/lookups_view.php', 'title' => 'تهيئة المدخلات'],
    'settings/add_lookup_group'     => ['path' => 'settings/add_lookup_group_view.php', 'title' => 'إضافة مجموعة'],
    'settings/edit_lookup_group'    => ['path' => 'settings/edit_lookup_group_view.php', 'title' => 'تعديل مجموعة'],
    'settings/add_lookup_option'    => ['path' => 'settings/add_lookup_option_view.php', 'title' => 'إضافة خيار'],
    'settings/edit_lookup_option'   => ['path' => 'settings/edit_lookup_option_view.php', 'title' => 'تعديل خيار'],
];

?>