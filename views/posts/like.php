<?php
session_start();
include '../includes/db.php';

$post_id = $_POST['post_id'];
$user_id = $_SESSION['user_id'];

// Check if the user has already liked the post
$stmt = $pdo->prepare('SELECT * FROM likes WHERE post_id = ? AND user_id = ?');
$stmt->execute([$post_id, $user_id]);
$like = $stmt->fetch();

if ($like) {
    // Unlike the post
    $stmt = $pdo->prepare('DELETE FROM likes WHERE post_id = ? AND user_id = ?');
    $stmt->execute([$post_id, $user_id]);
} else {
    // Like the post
    $stmt = $pdo->prepare('INSERT INTO likes (post_id, user_id, type) VALUES (?, ?, "like")');
    $stmt->execute([$post_id, $user_id]);
}

// Fetch updated likes count
$stmt = $pdo->prepare('SELECT COUNT(*) FROM likes WHERE post_id = ? AND type = "like"');
$stmt->execute([$post_id]);
$likes_count = $stmt->fetchColumn();

echo json_encode(['success' => true, 'likes_count' => $likes_count]);
