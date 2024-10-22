<?php
session_start();
include '../includes/db.php'; // Database connection
include '../includes/header.php'; // Include the header

// التحقق مما إذا كان المستخدم قد سجل الدخول
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = '../users/login.php';</script>";
    exit(); // إنهاء البرنامج بعد التوجيه
}

// جلب الفئات من قاعدة البيانات
$categoryStmt = $pdo->prepare("SELECT id, name FROM categories");
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// جلب معلومات المنشور للتعديل
$post_id = $_GET['id'] ?? null;
if ($post_id) {
    $postStmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $postStmt->execute([$post_id]);
    $post = $postStmt->fetch();
    
    if (!$post) {
        echo "<script>Swal.fire('Error', 'Post not found!', 'error');</script>";
        exit();
    }
} else {
    echo "<script>Swal.fire('Error', 'Invalid post ID!', 'error');</script>";
    exit();
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $tags = $_POST['tags'];
    $language = $_POST['language'];
    $category_id = $_POST['category_id'];

    // تحديث المنشور في جدول 'posts'
    $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, tags = ?, language = ?, category_id = ? WHERE id = ?");
    $stmt->execute([$title, $content, $tags, $language, $category_id, $post_id]);

    // Handle media upload (optional)
    if (isset($_FILES['media']) && $_FILES['media']['error'] == 0) {
        $mediaDir = '../uploads/';
        if (!is_dir($mediaDir)) {
            mkdir($mediaDir, 0777, true); // Create the directory if it doesn't exist
        }

        $mediaPath = $mediaDir . basename($_FILES['media']['name']);
        move_uploaded_file($_FILES['media']['tmp_name'], $mediaPath);

        // إدراج/تحديث الوسائط في الجدول 'media'
        $stmt_media = $pdo->prepare("INSERT INTO media (post_id, file_path, file_type) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE file_path = ?, file_type = ?");
        $mediaType = mime_content_type($_FILES['media']['tmp_name']); // Get media type
        $fileType = '';
        if (strpos($mediaType, 'image') !== false) {
            $fileType = 'image';
        } elseif (strpos($mediaType, 'video') !== false) {
            $fileType = 'video';
        } elseif (strpos($mediaType, 'audio') !== false) {
            $fileType = 'audio';
        }
        $stmt_media->execute([$post_id, $mediaPath, $fileType, $mediaPath, $fileType]);
    }

    echo "<script>Swal.fire('Success', 'Post updated successfully!', 'success').then(() => {
            window.location.href = 'show.php?id=$post_id';
        });</script>";
}
?>

<div class="container mx-auto mt-6">
    <h2 class="text-2xl font-bold mb-4">Edit Post</h2>
    <form action="" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
        <div class="mb-4">
            <label for="title" class="block text-gray-700 dark:text-gray-300">Post Title</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded dark:bg-gray-700 dark:text-white">
        </div>
        <div class="mb-4">
            <label for="content" class="block text-gray-700 dark:text-gray-300">Content</label>
            <textarea id="content" name="content" rows="5" required class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded dark:bg-gray-700 dark:text-white"><?php echo htmlspecialchars($post['content']); ?></textarea>
        </div>
        <div class="mb-4">
            <label for="tags" class="block text-gray-700 dark:text-gray-300">Tags</label>
            <input type="text" id="tags" name="tags" value="<?php echo htmlspecialchars($post['tags']); ?>" class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded dark:bg-gray-700 dark:text-white">
        </div>
        <div class="mb-4">
            <label for="language" class="block text-gray-700 dark:text-gray-300">Language</label>
            <select id="language" name="language" required class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded dark:bg-gray-700 dark:text-white">
                <option value="en" <?php echo ($post['language'] == 'en') ? 'selected' : ''; ?>>English</option>
                <option value="ar" <?php echo ($post['language'] == 'ar') ? 'selected' : ''; ?>>Arabic</option>
            </select>
        </div>
        <div class="mb-4">
            <label for="category_id" class="block text-gray-700 dark:text-gray-300">Category</label>
            <select id="category_id" name="category_id" required class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded dark:bg-gray-700 dark:text-white">
                <option value="" disabled>Select a category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo ($category['id'] == $post['category_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-4">
            <label for="media" class="block text-gray-700 dark:text-gray-300">Media (Image, Video, or Audio)</label>
            <input type="file" id="media" name="media" class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded dark:bg-gray-700 dark:text-white">
        </div>
        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Update Post</button>
        <a href="show.php?id=<?php echo $post_id; ?>" class="bg-gray-300 hover:bg-gray-400 text-black font-bold py-2 px-4 rounded ml-4">Cancel</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
