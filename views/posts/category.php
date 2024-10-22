<?php
include '../includes/db.php';
include '../includes/header.php';

// التأكد من وجود معرف الفئة في الرابط
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script> Swal.fire({ icon: 'error', title: 'Error', text: 'Invalid category ID!' }).then(() => { window.location.href = '/Blog2/views/posts/index.php'; }); </script>";
    exit;
}

// جلب معرف الفئة من الرابط
$category_id = intval($_GET['id']);

// جلب معلومات الفئة
$categoryStmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
$categoryStmt->execute([$category_id]);
$category = $categoryStmt->fetch();

// إذا لم يتم العثور على الفئة
if (!$category) {
    echo "<script> Swal.fire({ icon: 'error', title: 'Error', text: 'Category not found!' }).then(() => { window.location.href = '/Blog2/views/posts/index.php'; }); </script>";
    exit;
}

// جلب المنشورات الخاصة بالفئة
$postsStmt = $pdo->prepare('
    SELECT posts.* 
    FROM posts 
    JOIN post_status ON posts.id = post_status.post_id 
    WHERE post_status.status = ? AND posts.category_id = ?'
);
$postsStmt->execute(['published', $category_id]);
$posts = $postsStmt->fetchAll();
?>

<div class="container mx-auto m-0 p-0 bg-gray-100 dark:bg-gray-900 w-full h-screen">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($category['name']); ?></h1>
        <p class="text-gray-600 dark:text-gray-300"><?php echo htmlspecialchars($category['description']); ?></p>
    </div>

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
