<?php
$lang = 'ar'; // القيمة الافتراضية

if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
}
$dir = $lang === 'ar' ? 'rtl' : 'ltr';

// تحقق من ملفات تعريف الارتباط لتحديد الوضع الليلي /api/ blogs /api/blogs/1
$darkMode = isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'true'; 
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../../assets/css/output.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title><?php echo $lang === 'ar' ? 'مدونة بسيطة' : 'Simple Blog'; ?></title>
    <style>
        /* زيادة المسافة بين النص والأيقونة في حالة اللغة العربية */
        .lang-icon-ar i {
            margin-left: 8px;
        }

        /* تصميم القائمة المنسدلة */
        .navbar {
            display: none; /* إخفاء القائمة بشكل افتراضي */
        }
        html[dir="rtl"] .navbar a {
                margin-right: 0;
                margin-left: 20px; /* مسافة على اليسار بدلًا من اليمين في حالة rtl */
            }
        /* عرض القائمة عند تفعيل الفئة */
        .navbar.open {
            display: flex;
            flex-direction: column; /* عرض العناصر في عمود */
        }

        @media (min-width: 768px) {
            .navbar {
                display: flex; /* عرض القائمة بشكل طبيعي على الشاشات الكبيرة */
                flex-direction: row; /* عرض العناصر في صف */
            }
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-black  dark:text-gray-200 <?php echo $darkMode ? 'dark' : ''; ?>">
    <header class="bg-blue-900 text-white p-4 flex justify-between items-center dark:bg-gray-800">
        <h1 class="text-center text-2xl"><?php echo $lang === 'ar' ? 'مدونتي ' : 'My Simple Blog'; ?></h1>

        <div class="relative">
            <!-- زر القائمة المنسدلة -->
            <button id="navbarToggle" class="text-white md:hidden">
                <i class="fas fa-bars"></i>
            </button>
            <nav class="navbar" id="navbar">
                <a href="/Blog2/views/posts/index.php" class="nav-link hover:text-gray-300"><?php echo $lang === 'ar' ? 'الرئيسية' : 'Posts'; ?></a>
                <a href="/Blog2/views/users/login.php" class="nav-link hover:text-gray-300"><?php echo $lang === 'ar' ? 'تسجيل الدخول' : 'Login'; ?></a>
                <a href="/Blog2/views/users/profile.php" class="nav-link hover:text-gray-300"><?php echo $lang === 'ar' ? 'الصفحة الشخصية' : 'Profile'; ?></a>
            </nav>

        </div>

        <div class="flex items-center space-x-4">
            <!-- القائمة المنسدلة لتغيير اللغة -->
            <div class="relative">
                <button class="text-white flex items-center space-x-2 <?php echo $lang === 'ar' ? 'lang-icon-ar' : ''; ?>" id="langDropdown">
                    <i class="fas fa-language"></i>
                    <span><?php echo $lang === 'ar' ? 'العربية' : 'Language'; ?></span>
                </button>
                <div id="langOptions" class="hidden absolute right-0 mt-2 w-32 bg-white text-black rounded shadow-lg dark:bg-gray-700 dark:text-white">
                    <a href="?lang=ar" class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-600">العربية</a>
                    <a href="?lang=en" class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-600">English</a>
                </div>
            </div>

            <!-- زر الوضع الليلي -->
            <button id="toggleDarkMode" class="text-white">
                <i class="fas <?php echo $darkMode ? 'fa-sun' : 'fa-moon'; ?>"></i>
            </button>
        </div>
    </header>

    <main class="container mx-auto mt-6 dark:bg-gray-800 dark:text-white">
        <!-- محتوى الصفحة -->
    </main>

    <script>
        // تبديل القائمة المنسدلة للغة
        const langDropdown = document.getElementById('langDropdown');
        const langOptions = document.getElementById('langOptions');
        
        langDropdown.addEventListener('click', () => {
            langOptions.classList.toggle('hidden');
        });

        // تبديل الوضع الليلي
        const toggleDarkMode = document.getElementById('toggleDarkMode');
        toggleDarkMode.addEventListener('click', () => {
            document.body.classList.toggle('dark'); // إضافة أو إزالة فئة dark
            
            // تحديث ملفات تعريف الارتباط
            const isDark = document.body.classList.contains('dark');
            document.cookie = `darkMode=${isDark}; path=/;`;
            
            // تحديث أيقونة الشمس/القمر
            toggleDarkMode.querySelector('i').classList.toggle('fa-sun');
            toggleDarkMode.querySelector('i').classList.toggle('fa-moon');
        });

        // إعادة تعيين حالة الوضع الليلي عند تحميل الصفحة
        if (document.cookie.split(';').some((item) => item.trim().startsWith('darkMode=true'))) {
            document.body.classList.add('dark');
        }

        // وظيفة لفتح وإغلاق القائمة المنسدلة
        const navbarToggle = document.getElementById('navbarToggle');
        const navbar = document.getElementById('navbar');

        navbarToggle.addEventListener('click', () => {
            navbar.classList.toggle('open'); // تبديل الفئة open للقائمة
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>
</html>
