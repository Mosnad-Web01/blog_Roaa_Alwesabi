<?php
session_start();
include '../includes/db.php'; 

// التحقق مما إذا تم تمرير معرف المنشور
if (isset($_GET['post_id'])) {
    $post_id = $_GET['post_id'];

    // جلب التعليقات المرتبطة بالمنشور
    $stmt = $pdo->prepare('SELECT * FROM comments WHERE post_id = ? ORDER BY created_at DESC');
    $stmt->execute([$post_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // التحقق من تعيين اللغة في الجلسة، وتعيين اللغة الافتراضية إذا لم تكن موجودة
    $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'ar';


    // عرض التعليقات
    foreach ($comments as $comment) {
        echo '
        <div class="bg-white shadow-md p-4 mt-2 rounded-lg dark:bg-gray-700 flex justify-between items-start transition duration-300 ease-in-out hover:shadow-lg">
            <div class="flex-1">
                <p class="text-gray-800 dark:text-gray-200">' . htmlspecialchars($comment['content']) . '</p>
                <p class="text-xs text-gray-500">' . $comment['created_at'] . '</p>
            </div>';

        // إضافة أزرار التعديل والحذف إذا كان المستخدم صاحب التعليق أو المدير
if ($_SESSION['user_id'] == $comment['user_id'] || $_SESSION['role'] == 'admin') {
    echo '
    <div class="flex items-center space-x-2">
        <button class="text-blue-500 hover:underline" onclick="document.getElementById(\'editModal' . $comment['id'] . '\').classList.remove(\'hidden\');">
            <i class="fas fa-edit"></i>
        </button>
        <a href="?delete_comment_id=' . $comment['id'] . '" class="text-red-500 hover:underline">
            <i class="fas fa-trash"></i>
        </a>
    </div>';
}

// تأكد من أن النموذج الخاص بالتعديل يكون مخفيًا في البداية
echo '
<div id="editModal' . $comment['id'] . '" class="hidden fixed z-50 inset-0 bg-gray-600 bg-opacity-50 flex justify-center items-center">
    <div class="bg-white rounded p-4 dark:bg-gray-800">
        <h4 class="text-xl">' . ($lang === 'ar' ? 'تعديل التعليق' : 'Edit Comment') . '</h4>
        <form action="" method="POST">
            <input type="hidden" name="edit_comment_id" value="' . $comment['id'] . '">
            <textarea name="edit_comment_content" rows="2" class="w-full border border-gray-300 p-2 rounded dark:border-gray-600 dark:bg-gray-700 dark:text-white" required>' . htmlspecialchars($comment['content']) . '</textarea>
            <button type="submit" class="bg-blue-500 text-white px-4 py-1 mt-2 rounded">' . ($lang === 'ar' ? 'تحديث التعليق' : 'Update Comment') . '</button>
            <button type="button" class="text-gray-500 hover:underline mt-2" onclick="document.getElementById(\'editModal' . $comment['id'] . '\').classList.add(\'hidden\');">' . ($lang === 'ar' ? 'إلغاء' : 'Cancel') . '</button>
        </form>
    </div>
</div>';

    }
} else {
    echo 'No post ID provided.';
}
?>
