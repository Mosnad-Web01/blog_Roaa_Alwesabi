<?php
include '../includes/db.php';
include '../includes/header.php';

// جلب الفئات من قاعدة البيانات
$categoryStmt = $pdo->prepare('SELECT * FROM categories WHERE language = ?');
$categoryStmt->execute(['ar']);
$categories = $categoryStmt->fetchAll();

// جلب المنشورات بناءً على الفئة المختارة
$posts = [];
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $selectedCategory = $_GET['category'];

    // جلب المنشورات المرتبطة بالفئة المحددة
    $postsStmt = $pdo->prepare('
        SELECT posts.* 
        FROM posts 
        JOIN post_status ON posts.id = post_status.post_id 
        JOIN categories ON posts.category_id = categories.id 
        WHERE post_status.status = ? AND categories.name = ?'
    );
    $postsStmt->execute(['published', $selectedCategory]);
    $posts = $postsStmt->fetchAll();
}
?>

<div class="container mx-auto m-0 p-0 bg-gray-100 dark:bg-gray-900 w-full h-screen">
    

    <!-- قسم لاستطلاع اهتمام الزوار -->
    <div class="bg-gray-200 dark:bg-gray-800 p-4 rounded-lg mb-8">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">هل لديك اهتمام معين؟</h3>
        <form method="GET" action="filter.php" class="flex space-x-4">
            <select name="category" class="p-2 rounded bg-white dark:bg-gray-700">
                <option value="">اختر فئة</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category['name']); ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="bg-blue-500 dark:bg-blue-700 text-white font-bold py-2 px-4 rounded">استكشف</button>
        </form>
    </div>

    <!-- عرض المنشورات التي تم اختيارها -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (count($posts) > 0): ?>
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
        <?php else: ?>
            <div class="col-span-3 text-center">
                <p class="text-gray-600 dark:text-gray-300">لا توجد منشورات في هذه الفئة حتى الآن.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="mt-8 text-center">
        <a href="index.php" class="bg-blue-500 dark:bg-blue-700 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 dark:hover:bg-blue-500 transition">عودة إلى الصفحة الرئيسية</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
