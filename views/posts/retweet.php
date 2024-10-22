<?php
session_start();
include '../includes/db.php';

$post_id = $_POST['post_id'];
$user_id = $_SESSION['user_id'];

// Check if the user has already retweeted the post
$stmt = $pdo->prepare('SELECT * FROM retweets WHERE post_id = ? AND user_id = ?');
$stmt->execute([$post_id, $user_id]);
$retweet = $stmt->fetch();

if ($retweet) {
    // Remove retweet
    $stmt = $pdo->prepare('DELETE FROM retweets WHERE post_id = ? AND user_id = ?');
    $stmt->execute([$post_id, $user_id]);
} else {
    // Retweet the post
    $stmt = $pdo->prepare('INSERT INTO retweets (post_id, user_id) VALUES (?, ?)');
    $stmt->execute([$post_id, $user_id]);
}

// Fetch updated retweets count
$stmt = $pdo->prepare('SELECT COUNT(*) FROM retweets WHERE post_id = ?');
$stmt->execute([$post_id]);
$retweets_count = $stmt->fetchColumn();

echo json_encode(['success' => true, 'retweets_count' => $retweets_count]);
