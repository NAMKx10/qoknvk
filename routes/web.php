
<?php
/**
 * routes/web.php
 * 
 * خريطة النظام - القائمة البيضاء للصفحات المسموح بها.
 * يحتوي هذا الملف على جميع المسارات التي يمكن للمستخدم الوصول إليها،
 * بالإضافة إلى عنوان كل صفحة ومسار ملف العرض (View) الخاص بها.
 */

return [

    'dashboard' => ['path' => 'dashboard/dashboard_controller.php', 'title' => 'لوحة التحكم'],
    'login'             => ['path' => 'login/login_view.php', 'title' => 'تسجيل الدخول'],
    'about'             => ['path' => 'about/about_view.php', 'title' => 'حول النظام'],
    // الإدارة الأساسية
    'owners'            => ['path' => 'owners/owners_view.php', 'title' => 'إدارة الملاك'],
    'owners/add'        => ['path' => 'owners/add_view.php', 'title' => 'إضافة مالك'],
    'owners/edit'       => ['path' => 'owners/edit_view.php', 'title' => 'تعديل مالك'],
    'owners/branches_modal' => ['path' => 'owners/branches_modal_view.php', 'title' => 'إدارة فروع المالك'],
    'branches' => ['path' => 'branches/branches_controller.php', 'title' => 'إدارة الفروع'],
    'branches/add'      => ['path' => 'branches/add_view.php', 'title' => 'إضافة فرع'],
    'branches/edit'     => ['path' => 'branches/edit_view.php', 'title' => 'تعديل فرع'],
    'properties' => ['path' => 'properties/properties_controller.php', 'title' => 'إدارة العقارات'],
    'properties/add'    => ['path' => 'properties/add_view.php', 'title' => 'إضافة عقار'],
    'properties/edit'   => ['path' => 'properties/edit_view.php', 'title' => 'تعديل عقار'],
    'properties/batch_edit'        => ['path' => 'properties/properties_batch_edit_controller.php', 'title' => 'تعديل جماعي للعقارات'],
    'properties/handle_batch_edit' => ['path' => ''],
    'properties/batch_add'         => ['path' => 'properties/properties_batch_add_controller.php', 'title' => 'إدخال متعدد للعقارات'],
    'properties/handle_batch_add'  => ['path' => ''], // معالج فقط

    'units'             => ['path' => 'units/units_controller.php', 'title' => 'إدارة الوحدات'],
    'units/add'         => ['path' => 'units/add_view.php', 'title' => 'إضافة وحدة'],
    'units/edit'        => ['path' => 'units/edit_view.php', 'title' => 'تعديل وحدة'],
    'clients'           => ['path' => 'clients/clients_controller.php', 'title' => 'إدارة العملاء'],
    'clients/add'       => ['path' => 'clients/add_view.php', 'title' => 'إضافة عميل'],
    'clients/edit'      => ['path' => 'clients/edit_view.php', 'title' => 'تعديل عميل'],
    'clients/branches_modal' => ['path' => 'clients/branches_modal_view.php', 'title' => 'الفروع المرتبطة بالعميل'],
    'suppliers'         => ['path' => 'suppliers/suppliers_controller.php', 'title' => 'إدارة الموردين'],
    'suppliers/add'     => ['path' => 'suppliers/add_view.php', 'title' => 'إضافة مورد'],
    'suppliers/edit'    => ['path' => 'suppliers/edit_view.php', 'title' => 'تعديل مورد'],
    'suppliers/branches_modal' => ['path' => 'suppliers/branches_modal_view.php', 'title' => 'الفروع المرتبطة بالمورد'],   
    // العقود والمالية
    'contracts'         => ['path' => 'contracts/contracts_controller.php', 'title' => 'عقود الإيجار'],
    'contracts/add'     => ['path' => 'contracts/add_view.php', 'title' => 'إضافة عقد إيجار'],
    'contracts/edit'    => ['path' => 'contracts/edit_view.php', 'title' => 'تعديل عقد إيجار'],
'contracts/units_modal' => ['path' => 'contracts/units_modal_view.php', 'title' => 'الوحدات المرتبطة بالعقد'], // <-- السطر الجديد
    'contracts/view'    => ['path' => 'contracts/view_view.php', 'title' => 'تفاصيل العقد'],
    'contracts/delete'  => ['path' => '', 'title' => 'حذف عقد'],
    'supply_contracts'       => ['path' => 'supply_contracts/supply_contracts_view.php', 'title' => 'عقود التوريد'],
    'supply_contracts/view'  => ['path' => 'supply_contracts/view_view.php', 'title' => 'تفاصيل العقد'],
    // إدارة النظام
    'documents'              => ['path' => 'documents/documents_view.php', 'title' => 'إدارة الوثائق'],
    'documents/add'          => ['path' => 'documents/add_view.php', 'title' => 'إضافة وثيقة'],
    'documents/edit'         => ['path' => 'documents/edit_view.php', 'title' => 'تعديل وثيقة'],
    'documents/get_custom_fields_schema_ajax' => ['path' => ''], // معالج فقط
    'documents/get_entities_for_linking_ajax' => ['path' => ''],
    'documents/get_linked_entities_ajax'      => ['path' => ''],
    'documents/add_link_ajax'                 => ['path' => ''],
    'documents/delete_link_ajax'              => ['path' => ''],
    'documents/get_type_config_ajax' => ['path' => ''], // معالج فقط
    'users' => ['path' => 'users/users_controller.php', 'title' => 'إدارة المستخدمين'],
    'users/add'         => ['path' => 'users/add_view.php', 'title' => 'إضافة مستخدم'],
    'users/edit'        => ['path' => 'users/edit_view.php', 'title' => 'تعديل مستخدم'],
    'users/delete'      => ['path' => '', 'title' => 'حذف مستخدم'], // معالجة فقط
    'roles' => ['path' => 'roles/roles_controller.php', 'title' => 'إدارة الأدوار'],
    'roles/add'         => ['path' => 'roles/add_view.php', 'title' => 'إضافة دور'],
    'roles/edit'        => ['path' => 'roles/edit_view.php', 'title' => 'تعديل الصلاحيات'],
    'roles/edit_role'   => ['path' => 'roles/edit_role_view.php', 'title' => 'تعديل الدور'],
    'permissions' => ['path' => 'permissions/permissions_controller.php', 'title' => 'إدارة الصلاحيات'],
    'permissions/add_group'     => ['path' => 'permissions/add_group_view.php', 'title' => 'إضافة مجموعة'],
    'permissions/edit_group'    => ['path' => 'permissions/edit_group_view.php', 'title' => 'تعديل مجموعة'],
    'permissions/delete_group'  => ['path' => ''], // معالجة فقط
    'permissions/add'           => ['path' => 'permissions/add_view.php', 'title' => 'إضافة صلاحية'],
    'permissions/edit'          => ['path' => 'permissions/edit_view.php', 'title' => 'تعديل صلاحية'],
    'permissions/delete'        => ['path' => ''], // معالجة فقط
    'archive'           => ['path' => 'archive/archive_view.php', 'title' => 'الأرشيف'],
    'settings'                  => ['path' => 'settings/settings_controller.php', 'title' => 'الإعدادات العامة'],
    'settings/handle_update'    => ['path' => '', 'title' => 'حفظ الإعدادات'],
    'settings/lookups' => ['path' => 'settings/lookups_controller.php', 'title' => 'تهيئة المدخلات'],
    'settings/add_lookup_group'     => ['path' => 'settings/add_lookup_group_view.php', 'title' => 'إضافة مجموعة'],
    'settings/edit_lookup_group'    => ['path' => 'settings/edit_lookup_group_view.php', 'title' => 'تعديل مجموعة'],
    'settings/add_lookup_option'    => ['path' => 'settings/add_lookup_option_view.php', 'title' => 'إضافة خيار'],
    'settings/edit_lookup_option'   => ['path' => 'settings/edit_lookup_option_view.php', 'title' => 'تعديل خيار'],
];

?>