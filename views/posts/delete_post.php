<?php
session_start();
require_once '../includes/db.php'; // تأكد من مسار ملف db.php الصحيح

// جلب معرف المنشور
$post_id = $_GET['id'] ?? null;

// التحقق مما إذا كان معرف المنشور موجودًا
if (!$post_id) {
    echo "<script>Swal.fire('Error', 'Post ID not provided!', 'error');</script>";
    exit;
}

// جلب المنشور من قاعدة البيانات
$stmt = $pdo->prepare('SELECT author_id FROM posts WHERE id = ?');
$stmt->execute([$post_id]);
$post = $stmt->fetch();

// التحقق مما إذا كان المنشور موجودًا
if (!$post) {
    echo "<script>Swal.fire('Error', 'Post not found!', 'error');</script>";
    exit;
}

// التحقق مما إذا كان المستخدم هو مؤلف المنشور أو مسؤول
if ($_SESSION['user_id'] != $post['author_id'] && $_SESSION['role'] != 'admin') {
    echo "<script>Swal.fire('Error', 'You are not authorized to delete this post!', 'error');</script>";
    exit;
}

// حذف المنشور من قاعدة البيانات
$stmt = $pdo->prepare('DELETE FROM posts WHERE id = ?');
$stmt->execute([$post_id]);

echo "<script>
    Swal.fire('Success', 'Post deleted successfully!', 'success')
    .then(() => {
        window.location.href = 'index.php'; // إعادة توجيه المستخدم إلى الصفحة الرئيسية بعد الحذف
    });
</script>";
?>
