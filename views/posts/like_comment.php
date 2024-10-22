<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $comment_id = $data['comment_id'];
    $type = $data['type'];
    $user_id = $_SESSION['user_id'];

    // تأكد من وجود المستخدم
    if ($user_id) {
        // تحقق مما إذا كان المستخدم قد قام بإعجاب هذا التعليق بالفعل
        $stmt = $pdo->prepare('SELECT * FROM likes WHERE comment_id = ? AND user_id = ?');
        $stmt->execute([$comment_id, $user_id]);
        $like = $stmt->fetch();

        if ($like) {
            // إذا كان المستخدم قد قام بإعجاب التعليق بالفعل، قم بتحديثه
            if ($like['type'] === $type) {
                echo json_encode(['success' => false, 'message' => 'You already ' . $type . 'd this comment.']);
                exit;
            } else {
                // إذا كان الإعجاب مختلفًا، قم بتحديثه
                $stmt = $pdo->prepare('UPDATE likes SET type = ? WHERE id = ?');
                $stmt->execute([$type, $like['id']]);
            }
        } else {
            // إذا لم يكن هناك إعجاب سابق، أضف إعجاب جديد
            $stmt = $pdo->prepare('INSERT INTO likes (comment_id, user_id, type) VALUES (?, ?, ?)');
            $stmt->execute([$comment_id, $user_id, $type]);
        }

        // احصل على عدد الإعجابات وعدم الإعجابات
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM likes WHERE comment_id = ? AND type = "like"');
        $stmt->execute([$comment_id]);
        $likes_count = $stmt->fetchColumn();

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM likes WHERE comment_id = ? AND type = "dislike"');
        $stmt->execute([$comment_id]);
        $dislikes_count = $stmt->fetchColumn();

        echo json_encode(['success' => true, 'likes_count' => $likes_count, 'dislikes_count' => $dislikes_count]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    }
}
