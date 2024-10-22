<?php
// تأكد من أن المستخدم مسجل دخوله ولديه الصلاحية لحذف التعليق
session_start();
include '../includes/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment_id = $_POST['comment_id'];
    // تحقق من أن المستخدم هو صاحب التعليق أو مسؤول
    if ($_SESSION['user_id'] == $comment_owner_id || $_SESSION['role'] == 'admin') {
        // هنا قم بتنفيذ عملية الحذف من قاعدة البيانات
        $stmt = $pdo->prepare('DELETE FROM comments WHERE id = ?');
        $stmt->execute([$comment_id]);
        echo 'تم حذف التعليق';
    } else {
        echo 'ليس لديك صلاحية حذف هذا التعليق.';
    }
}
