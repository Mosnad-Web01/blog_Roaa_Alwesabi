<?php
include '../includes/db.php';
include '../includes/header.php';

// إعداد المتغيرات للصفحات
$limit = 9; // عدد المنشورات أو المواضيع لكل صفحة
$postPage = isset($_GET['post_page']) ? (int)$_GET['post_page'] : 1; // رقم الصفحة الحالية للمنشورات
$postOffset = ($postPage - 1) * $limit; // تعيين قيمة الـ OFFSET للمنشورات

$categoryPage = isset($_GET['category_page']) ? (int)$_GET['category_page'] : 1; // رقم الصفحة الحالية للمواضيع
$categoryOffset = ($categoryPage - 1) * $limit; // تعيين قيمة الـ OFFSET للمواضيع

// جلب المنشورات المنشورة
$stmt = $pdo->prepare('
    SELECT posts.* 
    FROM posts 
    JOIN post_status ON posts.id = post_status.post_id 
    WHERE post_status.status = ? 
    LIMIT ? OFFSET ?
');
$stmt->execute(['published', $limit, $postOffset]);
$posts = $stmt->fetchAll();

// جلب الفئات المتاحة
$categoriesStmt = $pdo->prepare('SELECT * FROM categories WHERE language = "ar" LIMIT ? OFFSET ?');
$categoriesStmt->execute([$limit, $categoryOffset]);
$categories = $categoriesStmt->fetchAll();
?>

<div class="container mx-auto m-0 p-0 bg-gray-100 dark:bg-gray-900 w-full h-screen">
    <!-- المنشورات -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($posts as $post): ?>
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center mb-4">
                    <i class="fas fa-newspaper text-blue-500 dark:text-blue-400 text-2xl mr-2"></i>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($post['title']); ?></h2>
                </div>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    <i class="fas fa-align-left text-gray-400 dark:text-gray-500 mr-1"></i>
                    <?php echo htmlspecialchars(substr($post['content'], 0, 100)); ?>...
                </p>
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-4 flex items-center">
                    <i class="fas fa-calendar-alt text-gray-400 dark:text-gray-500 mr-2"></i>
                    Posted on: <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                </div>
                <a href="show.php?id=<?php echo $post['id']; ?>" class="text-blue-500 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 flex items-center">
                    <i class="fas fa-arrow-right mr-2"></i> Read more
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- أزرار التنقل بين صفحات المنشورات -->
    <div class="mt-8 flex justify-between">
        <?php if ($postPage > 1): ?>
            <a href="?post_page=<?php echo $postPage - 1; ?>" class="bg-gray-500 text-white py-2 px-4 rounded">السابق</a>
        <?php else: ?>
            <span class="bg-gray-300 text-gray-700 py-2 px-4 rounded cursor-not-allowed">السابق</span>
        <?php endif; ?>

        <?php if (count($posts) === $limit): ?>
            <a href="?post_page=<?php echo $postPage + 1; ?>" class="bg-gray-500 text-white py-2 px-4 rounded">التالي</a>
        <?php else: ?>
            <span class="bg-gray-300 text-gray-700 py-2 px-4 rounded cursor-not-allowed">التالي</span>
        <?php endif; ?>
    </div>

    <!-- زر لنشر منشور جديد -->
    <div class="mt-8 text-center">
        <a href="create.php" class="bg-blue-500 dark:bg-blue-700 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 dark:hover:bg-blue-500 transition">New Post</a>
    </div>

    <div class="bg-gray-200 dark:bg-gray-800 p-4 rounded-lg mt-8 mb-8">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">استكشف مواضيعنا:</h3>

        <!-- شبكة عرض الفئات -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($categories as $category): ?>
                <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6 hover:shadow-xl transform hover:scale-105 transition-transform duration-300">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700 dark:text-gray-300 flex items-center">
                            <i class="fas fa-folder-open text-yellow-500 mr-2"></i>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </span>
                        <a href="category.php?id=<?php echo $category['id']; ?>" class="text-blue-500 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300">استكشف</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- أزرار التنقل بين صفحات الفئات -->
        <div class="mt-8 flex justify-between">
            <?php if ($categoryPage > 1): ?>
                <a href="?category_page=<?php echo $categoryPage - 1; ?>" class="bg-gray-500 text-white py-2 px-4 rounded">السابق</a>
            <?php else: ?>
                <span class="bg-gray-300 text-gray-700 py-2 px-4 rounded cursor-not-allowed">السابق</span>
            <?php endif; ?>

            <?php if (count($categories) === $limit): ?>
                <a href="?category_page=<?php echo $categoryPage + 1; ?>" class="bg-gray-500 text-white py-2 px-4 rounded">التالي</a>
            <?php else: ?>
                <span class="bg-gray-300 text-gray-700 py-2 px-4 rounded cursor-not-allowed">التالي</span>
            <?php endif; ?>
        </div>

        <div class="mt-4 text-center">
            <a href="create_category.php" class="bg-blue-500 dark:bg-blue-700 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 dark:hover:bg-blue-500 transition">إنشاء فئة جديدة</a>
        </div>
        
        
        <!-- الدعوة للتسجيل -->
        <div class="text-center mt-8">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">هل أنت جديد هنا؟</h3>
            <p class="mb-4 text-gray-600 dark:text-gray-400">انضم إلينا للاستفادة من المحتوى الحصري والمشاركة في المناقشات.</p>
            <a href="../users/register.php" class="bg-blue-500 dark:bg-blue-700 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 dark:hover:bg-blue-500 transition">سجل الآن</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
