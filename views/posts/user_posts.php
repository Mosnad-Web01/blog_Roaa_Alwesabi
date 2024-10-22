<?php
session_start();
include '../includes/db.php';
include '../includes/header.php';

// تأكد من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    echo "<script> Swal.fire({ icon: 'error', title: 'Error', text: 'You must be logged in to view your posts!' }).then(() => { window.location.href = '/Blog2/views/users/login.php'; }); </script>";
    exit;
}

// جلب معرف المستخدم من الجلسة
$user_id = $_SESSION['user_id'];


// جلب المنشورات الخاصة بالمستخدم
$stmt = $pdo->prepare('
    SELECT posts.*, post_status.status 
    FROM posts 
    JOIN post_status ON posts.id = post_status.post_id 
    WHERE posts.author_id = ? AND post_status.status IN ("published", "draft")
');
$stmt->execute([$user_id]);
$posts = $stmt->fetchAll();
?>

<div class="container mx-auto m-0 p-0 bg-gray-100 dark:bg-gray-900 w-full h-screen">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($posts as $post): ?>
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center mb-4">
                    <i class="fas fa-newspaper text-blue-500 dark:text-blue-400 text-2xl mr-2"></i>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($post['title']); ?></h2>
                </div>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    <?php echo htmlspecialchars(substr($post['content'], 0, 100)); ?>...
                </p>
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-4 flex items-center">
                    <i class="fas fa-calendar-alt text-gray-400 dark:text-gray-500 mr-2"></i>
                    <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                </div>
                
                <!-- عرض حالة المنشور مرة واحدة -->
                                <div class="text-sm mb-4">
                    <?php if ($post['status'] === 'published'): ?>
                        <span class="text-gray-700 dark:text-gray-300">Published</span>
                    <?php else: ?>
                        <form method="POST" action="publish.php" class="inline-block">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <button type="submit" class="text-red-500 hover:text-red-700">
                                Draft
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                
               

                <!-- زر Read more -->
                <div class="flex items-center justify-between">
                    <a href="show.php?id=<?php echo $post['id']; ?>" class="text-blue-500 hover:text-blue-700 flex items-center">
                        <i class="fas fa-arrow-right mr-2"></i> Read more
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
