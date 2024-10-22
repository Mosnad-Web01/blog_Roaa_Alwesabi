<?php
session_start(); // بدء جلسة جديدة أو استئناف الجلسة الحالية

// تحقق مما إذا كان المستخدم مسجلاً للدخول
if (!isset($_SESSION['user_id'])) {
    // إذا لم يكن المستخدم مسجلاً، قم بتوجيهه إلى صفحة تسجيل الدخول
    echo '<script>window.location.href = "../users/login.php";</script>';
    exit;
}

include '../includes/db.php';
include '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $language = trim($_POST['language']); 

    // تحقق من القيم المدخلة
    if (!empty($name) && !empty($language)) {
        // إضافة فئة جديدة إلى قاعدة البيانات
        $stmt = $pdo->prepare('INSERT INTO categories (name, description, language) VALUES (?, ?, ?)');
        $stmt->execute([$name, $description, $language]);
        header('Location: index.php');
        exit;
    }
}
?>

<div class="container mx-auto m-0 p-0 bg-gray-100 dark:bg-gray-900 w-full h-screen">
    <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-md">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">إنشاء فئة جديدة</h2>
        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-gray-700 dark:text-gray-300 mb-2" for="name">اسم الفئة</label>
                <input type="text" name="name" id="name" class="border rounded w-full p-2" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 dark:text-gray-300 mb-2" for="description">وصف الفئة</label>
                <textarea name="description" id="description" class="border rounded w-full p-2" rows="4"></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 dark:text-gray-300 mb-2" for="language">اللغة</label>
                <select name="language" id="language" class="border rounded w-full p-2" required>
                    <option value="ar">العربية</option>
                    <option value="en">الإنجليزية</option>
                   
                </select>
            </div>
            <button type="submit" class="bg-blue-500 dark:bg-blue-700 text-white font-bold py-2 px-4 rounded">إضافة فئة</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
