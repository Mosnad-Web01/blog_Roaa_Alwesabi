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

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $tags = $_POST['tags'];
    $language = $_POST['language'];
    $category_id = $_POST['category_id'];
    $author_id = $_SESSION['user_id']; // Assuming the user is logged in

    // التحقق مما إذا كان الزر الذي تم النقر عليه هو "Save as Draft"
    $isDraft = isset($_POST['save_draft']);

    // Insert the post into the 'posts' table
    $stmt = $pdo->prepare("INSERT INTO posts (title, content, author_id, tags, language, category_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $content, $author_id, $tags, $language, $category_id]);

    // الحصول على معرف المنشور الذي تم إنشاؤه
    $post_id = $pdo->lastInsertId();

    // إدراج الحالة بناءً على ما إذا كان المنشور كمسودة أم منشور
    $status = $isDraft ? 'draft' : 'published';
    $stmt_status = $pdo->prepare("INSERT INTO post_status (post_id, status) VALUES (?, ?)");
    $stmt_status->execute([$post_id, $status]);

    // Handle media upload
    if (isset($_FILES['media']) && $_FILES['media']['error'][0] == 0) {
        $mediaDir = '../uploads/';
        if (!is_dir($mediaDir)) {
            mkdir($mediaDir, 0777, true); // Create the directory if it doesn't exist
        }

        // Loop through each file
        foreach ($_FILES['media']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['media']['error'][$key] == 0) {
                $mediaPath = $mediaDir . basename($_FILES['media']['name'][$key]);
                $mediaType = mime_content_type($tmp_name); // Get media type
                move_uploaded_file($tmp_name, $mediaPath);

                // Determine media type (image, video, audio)
                $fileType = '';
                if (strpos($mediaType, 'image') !== false) {
                    $fileType = 'image';
                } elseif (strpos($mediaType, 'video') !== false) {
                    $fileType = 'video';
                } elseif (strpos($mediaType, 'audio') !== false) {
                    $fileType = 'audio';
                }

                // Insert the media into the 'media' table
                $stmt_media = $pdo->prepare("INSERT INTO media (post_id, file_path, file_type) VALUES (?, ?, ?)");
                $stmt_media->execute([$post_id, $mediaPath, $fileType]);
            }
        }
    }

    // توجيه المستخدم بناءً على الحالة
    if ($isDraft) {
        echo "<script>window.location.href = '../posts/index.php';</script>"; // إذا كان كمسودة، انتقل إلى قائمة المنشورات
    } else {
        echo "<script>window.location.href = '../posts/show.php?id=$post_id';</script>"; // إذا تم نشره، انتقل إلى عرض المنشور الجديد
    }
    exit();
}
?>

<div class="container mx-auto mt-6">
    <h2 class="text-2xl font-bold mb-4">Create New Post</h2>
    <form action="" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
        <div class="mb-4">
            <label for="title" class="block text-gray-700 dark:text-gray-300">Post Title</label>
            <input type="text" id="title" name="title" required class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded dark:bg-gray-700 dark:text-white">
        </div>
        <div class="mb-4">
            <label for="content" class="block text-gray-700 dark:text-gray-300">Content</label>
            <textarea id="content" name="content" rows="5" required class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded dark:bg-gray-700 dark:text-white"></textarea>
        </div>
        <div class="mb-4">
            <label for="tags" class="block text-gray-700 dark:text-gray-300">Tags</label>
            <input type="text" id="tags" name="tags" class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded dark:bg-gray-700 dark:text-white">
        </div>
        <div class="mb-4">
            <label for="language" class="block text-gray-700 dark:text-gray-300">Language</label>
            <select id="language" name="language" required class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded dark:bg-gray-700 dark:text-white">
                <option value="ar">Arabic</option>
                <option value="en">English</option>
            </select>
        </div>
        <div class="mb-4">
            <label for="category_id" class="block text-gray-700 dark:text-gray-300">Category</label>
            <select id="category_id" name="category_id" required class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded dark:bg-gray-700 dark:text-white">
                <option value="" disabled selected>Select a category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-4">
            <label for="media" class="block text-gray-700 dark:text-gray-300">Media (Image, Video, or Audio)</label>
            <input type="file" id="media" name="media[]" multiple class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded dark:bg-gray-700 dark:text-white">
        </div>
        <button type="submit" name="save" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Create Post</button>
        <button type="submit" name="save_draft" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded ml-4">Save as Draft</button>
        <a href="../posts/index.php" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded ml-4">Cancel</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
