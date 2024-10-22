<?php
session_start();
include '../includes/db.php';

// تأكد من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    echo "<script> Swal.fire({ icon: 'error', title: 'Error', text: 'You must be logged in to perform this action!' }).then(() => { window.location.href = '/Blog2/views/users/login.php'; }); </script>";
    exit;
}

// تحقق من أن معرف المنشور موجود في الطلب
if (isset($_POST['post_id'])) {
    $post_id = $_POST['post_id'];
    
    // تحديث حالة المنشور إلى "published"
    $stmt = $pdo->prepare('UPDATE post_status SET status = "published" WHERE post_id = ?');
    if ($stmt->execute([$post_id])) {
        echo "<script> Swal.fire({ icon: 'success', title: 'Success', text: 'The post has been published!' }).then(() => { window.location.href = '/Blog2/views/users/your_posts.php'; }); </script>";
    } else {
        echo "<script> Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to publish the post. Please try again.' }).then(() => { window.location.href = '/Blog2/views/users/your_posts.php'; }); </script>";
    }
} else {
    echo "<script> Swal.fire({ icon: 'error', title: 'Error', text: 'Invalid request!' }).then(() => { window.location.href = '/Blog2/views/users/your_posts.php'; }); </script>";
}
?>
