

التحديث: ملف التعريف README.md (الإصدار 3.5 - البنية الاحترافية)

# منصة "Namk" لإدارة الأعمال والأصول المتكاملة (الإصدار 3.5)

## 1. فكرة المشروع وهدفه

**منصة "Namk"** هي منصة تطبيقات أعمال سحابية (Cloud Business Application Platform) تم تصميمها لتكون **معيارية، مترابطة، وقابلة للتهيئة**. الهدف الأساسي للمنصة هو توفير "نموذج قياسي" (Blueprint) لإدارة أي كيان في النظام (عقارات، فروع، ملاك، وثائق)، مع إعطاء مدير النظام القدرة على تخصيص تفاصيل هذه الكيانات وسلوكها من لوحة تحكم مركزية دون الحاجة لتعديل الكود.

المنتج النهائي هو نظام قوي وموثوق يتميز بتجربة مستخدم فائقة الوضوح والاتساق، ومبني على أساس تقني متين يسهل صيانته وتطويره.

---

## 2. المبادئ الحاكمة للمشروع

1.  **الهيكلية النظيفة أولًا (Clean Architecture First):** الفصل التام بين المسؤوليات هو أساس كل شيء. نستخدم نمط (Controller-View) لفصل منطق الأعمال عن العرض، ونعزل معالجات AJAX في مجلد `handlers`.
2.  **لا تكرر نفسك (DRY):** أي منطق أو كود يتكرر يتم تحويله فورًا إلى "دالة مساعدة" (في `src/core/functions.php`) أو "مكتبة" (في `src/libraries/`) مركزية.
3.  **التهيئة بدلًا من البرمجة (Configuration over Code):** كل الخيارات والقوائم والسلوكيات قابلة للإدارة من قسم "تهيئة المدخلات".
4.  **الأمان المدمج (Security by Design):** نظام الصلاحيات (`RBAC`) ليس ميزة ثانوية، بل هو جزء أساسي ومدمج في كل إجراء وكل واجهة عبر دالة `has_permission()`.
5.  **الأداء كأولوية (Performance First):** نخطط لتحسين أداء قاعدة البيانات بشكل استباقي عبر استخدام الفهارس (Indexes) وتحسين الاستعلامات.

---

## 3. الهيكلية الفنية المفصلة (The Architecture)

لقد تمت إعادة هيكلة المشروع بالكامل ليتبع نمطًا احترافيًا يفصل بين المسؤوليات، مما يضمن سهولة الصيانة والتطوير.

---
`
/
├── 📁 app/ # قلب التطبيق (المنطق المركزي)
│ ├── 📄 request_handler.php # يوجه كل الطلبات
│ ├── 📄 security.php # جدار الحماية (الجلسات والصلاحيات)
│ └── 📄 view_renderer.php # محرك عرض الواجهات
│
├── 📁 config/ # ملفات الإعدادات
│
├── 📁 handlers/ # ملفات معالجات AJAX لكل موديول
│
├── 📁 on/ # المجلد العام (نقطة الدخول وملفات assets)
│ └── 📄 index.php
│
├── 📁 routes/ # خريطة الموقع والمسارات
│ └── 📄 web.php
│
└── 📁 src/ # الشيفرة المصدرية والواجهات
├── 📁 core/ # الدوال الأساسية
├── 📁 libraries/ # المكتبات المركزية (مثل Database.php)
└── 📁 modules/ # الوحدات الوظيفية (كل موديول يحتوي على controller و view)
`
---


### دورة حياة الطلب (Request Lifecycle) بالتفصيل:
1.  **نقطة الدخول:** كل الطلبات تصل إلى ملف `on/index.php`.
2.  **الأمان أولًا:** يتم استدعاء `app/security.php` الذي يتحقق من جلسة المستخدم ويقوم بتحميل صلاحياته.
3.  **توجيه الطلب:**
    *   **إذا كان الطلب AJAX أو معالجة:** يتم توجيهه إلى `app/request_handler.php` الذي يستدعي المعالج المناسب من `handlers/`.
    *   **إذا كان الطلب عرض صفحة:** يتم استدعاء `app/view_renderer.php` الذي يقوم بتحميل المتحكم المناسب من `src/modules/` (مثل `users_controller.php`)، والذي بدوره يجهز البيانات ثم يستدعي ملف الواجهة (`users_view.php`).
4.  **عرض الواجهة النهائية:** يتم وضع محتوى الصفحة داخل القالب الرئيسي (`templates/layout.php`) وعرضه للمستخدم.

---

## 4. التقنيات والمكتبات المستخدمة

*   **الواجهة الخلفية (Backend):** PHP (أسلوب برمجة إجرائي منظم) مع قاعدة بيانات MySQL/MariaDB.
*   **الواجهة الأمامية (Frontend):**
    *   قالب [**Tabler**](https://tabler.io/): أساس نظام التصميم.
    *   مكتبة [**jQuery**](https://jquery.com/): للتفاعل مع عناصر DOM.
    *   مكتبة [**Select2**](https://select2.org/): لتحسين قوائم الاختيار.
    *   مكتبة [**SweetAlert2**](https://sweetalert2.github.io/): لرسائل التأكيد والتنبيهات.
    *   مكتبة [**ApexCharts**](https://apexcharts.com/): للرسوم البيانية التفاعلية.

---

## 5. خارطة الطريق (Roadmap)

*   **تم إنجازه:**
    *   [✓] بناء الهيكلية الاحترافية وفصل المسؤوليات.
    *   [✓] بناء وتفعيل نظام صلاحيات (RBAC) متكامل.
    *   [✓] بناء نظام تهيئة مدخلات ديناميكي مع "مصمم نماذج".
    *   [✓] تأمين وتوحيد الموديلات الأساسية (المستخدمين، الأدوار، الصلاحيات، الفروع، العقارات، الوحدات).
    *   [✓] بناء لوحة تحكم ذكية وحيوية.
*   **الخطوة التالية المباشرة:** **البدء في بناء وتأمين موديول "العملاء" (`Clients`)** بنفس النموذج القياسي.
*   **الأهداف المستقبلية:**
    *   [ ] استكمال بناء الموديلات التشغيلية (الموردين، العقود).
    *   [ ] بناء "المركز المالي المتقدم".
    *   [ ] بناء صفحات "الملف الشخصي" ذات التبويبات.
    *   [ ] تطبيق تحسينات الأداء (Indexes & Caching).

