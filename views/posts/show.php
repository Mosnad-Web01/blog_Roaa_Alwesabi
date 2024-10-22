<?php
session_start();
include '../includes/db.php';
include '../includes/header.php';

$id = $_GET['id'] ?? null;

if ($id) {
    // Fetch the post along with category, media, and author info
    $stmt = $pdo->prepare('
        SELECT p.*, c.name AS category_name, u.username, u.profile_image
        FROM posts p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN users u ON p.author_id = u.id
        WHERE p.id = ?'
    );
    $stmt->execute([$id]);
    $post = $stmt->fetch();

    // Fetch media related to the post
    $stmt_media = $pdo->prepare('SELECT file_path FROM media WHERE post_id = ?');
    $stmt_media->execute([$id]);
    $media = $stmt_media->fetchAll(PDO::FETCH_ASSOC);

    // Fetch comments with user data and like/dislike counts
    $stmt = $pdo->prepare('
        SELECT c.*, u.username, u.profile_image, 
               (SELECT COUNT(*) FROM likes WHERE comment_id = c.id AND type = "like") AS likes_count,
               (SELECT COUNT(*) FROM likes WHERE comment_id = c.id AND type = "dislike") AS dislikes_count
        FROM comments c
        LEFT JOIN users u ON c.user_id = u.id
        WHERE c.post_id = ?'
    );
    $stmt->execute([$id]);
    $comments = $stmt->fetchAll();

    // Fetch likes count for the post
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM likes WHERE post_id = ? AND type = "like"');
    $stmt->execute([$id]);
    $likes_count = $stmt->fetchColumn();

    // Fetch retweets count for the post
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM retweets WHERE post_id = ?');
    $stmt->execute([$id]);
    $retweets_count = $stmt->fetchColumn();
} else {
    echo "<script>Swal.fire('Error', 'Post not found!', 'error');</script>";
    exit;
}

// Handle post deletion
if (isset($_GET['delete_post']) && ($_SESSION['user_id'] == $post['author_id'] || $_SESSION['role'] == 'admin')) {
    $stmt = $pdo->prepare('DELETE FROM posts WHERE id = ?');
    $stmt->execute([$id]);
    echo "<script>
            Swal.fire('Success', 'Post deleted successfully!', 'success').then(() => {
                window.location.href = 'index.php'; // Redirect to main page after deletion
            });
          </script>";
    exit;
}

// Handle comment deletion
if (isset($_GET['delete_comment_id']) && ($_SESSION['user_id'] == $post['author_id'] || $_SESSION['role'] == 'admin')) {
    $comment_id = $_GET['delete_comment_id'];
    $stmt = $pdo->prepare('DELETE FROM comments WHERE id = ?');
    $stmt->execute([$comment_id]);
    echo "<script>
            Swal.fire('Success', 'Comment deleted successfully!', 'success').then(() => {
                window.location.href = 'show.php?id=$id'; // Redirect back to post after deletion
            });
          </script>";
    exit;
}

// Handle new comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content'])) {
    $comment_content = trim($_POST['comment_content']);
    if (!empty($comment_content) && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        $stmt = $pdo->prepare('INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)');
        $stmt->execute([$id, $user_id, $comment_content]);

        // Redirect after success
        echo "<script>
                Swal.fire('Success', 'Comment added successfully!', 'success').then(() => {
                    window.location.href = 'show.php?id=$id';
                });
              </script>";
    } else {
        echo "<script>Swal.fire('Error', 'Comment cannot be empty!', 'error');</script>";
    }
}

// Handle comment edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_content']) && isset($_POST['comment_id'])) {
    $new_content = trim($_POST['new_content']);
    $comment_id = $_POST['comment_id'];

    if (!empty($new_content)) {
        $stmt = $pdo->prepare('UPDATE comments SET content = ? WHERE id = ?');
        $stmt->execute([$new_content, $comment_id]);
        echo "<script>Swal.fire('Success', 'Comment updated successfully!', 'success').then(() => {
                window.location.href = 'show.php?id=$id'; // Redirect back to post after update
            });
          </script>";
    } else {
        echo "<script>Swal.fire('Error', 'Comment cannot be empty!', 'error');</script>";
    }
}
?>

<!-- Post Container -->
<div class="container mx-auto p-6 bg-white shadow-md rounded-lg mt-6 dark:bg-gray-800">
<div class="tweet-header flex items-center mb-4">
    <!-- Profile Image -->
    <?php if ($post['profile_image']): ?>
    <img src="../uploads/profile_images/<?php echo htmlspecialchars($post['profile_image']); ?>" alt="Profile Image" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;">


    <?php endif; ?>

    <!-- Username -->
    <div class="ml-3">
        <span class="font-bold text-lg text-black dark:text-white"><?php echo htmlspecialchars($post['username']); ?></span>
        <span class="text-gray-500 dark:text-gray-100">@<?php echo htmlspecialchars($post['username']); ?></span>
    </div>
</div>

<!-- Post Title -->


    <!-- Post Title -->
    <h2 class="text-2xl font-bold mb-4 text-black dark:text-white"><?php echo htmlspecialchars($post['title']); ?></h2>
    
    <!-- Post Content -->
    <p class="mb-4 text-gray-800 dark:text-gray-300"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>

    <!-- Post Category -->
    <p class="mb-4 text-gray-800 dark:text-gray-300"><strong>Category:</strong> <?php echo htmlspecialchars($post['category_name']); ?></p>

    <!-- Post Tags -->
    <p class="mb-4 text-gray-800 dark:text-gray-300"><strong>Tags:</strong> <?php echo htmlspecialchars($post['tags']); ?></p>

   <!-- Display Media -->
<div class="mb-4">
    <h3 class="text-lg text-gray-800 dark:text-gray-300 font-semibold">Media</h3>
    <div class="flex flex-wrap gap-4">
        <?php foreach ($media as $item): ?>
            <a href="<?php echo htmlspecialchars($item['file_path']); ?>" target="_blank" download>
                <img src="<?php echo htmlspecialchars($item['file_path']); ?>" alt="Media" style="width: 30%; height: auto; border-radius: 20%; object-fit: cover; transition: transform 0.2s ease-in-out;" onmouseover="this.style.transform='scale(1.1)';" onmouseout="this.style.transform='scale(1)';">
            </a>
        <?php endforeach; ?>
    </div>
</div>


    <!-- Post Interactions -->
    <div class="flex justify-between text-gray-500 dark:text-gray-300 text-sm">
        <span>
            <button id="retweet-button" class="text-blue-500 cursor-pointer" onclick="retweet(<?php echo $id; ?>)">
                <i class="fas fa-retweet"></i> 
            </button>
            <?php echo $retweets_count; ?> Retweets
        </span>
        <span>
            <button id="like-button" class="text-red-500 cursor-pointer" onclick="likePost(<?php echo $id; ?>)">
                <i class="fas fa-heart"></i> 
            </button>
            <?php echo $likes_count; ?> Likes
        </span>
        
       <!-- Edit and Delete Post (if user owns it or is admin) -->
        <?php if ((isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['author_id']) || 
                    (isset($_SESSION['role']) && $_SESSION['role'] == 'admin')): ?>
            <span>
                <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="text-blue-500">
                    <i class="fas fa-edit"></i>
                </a>
                <a href="show.php?id=<?php echo $id; ?>&delete_post=1" class="text-red-500" onclick="return confirm('Are you sure you want to delete this post?');">
                    <i class="fas fa-trash-alt"></i>
                </a>
            </span>
        <?php endif; ?>

    </div>

    <!-- Comments Section -->
    <div class="mt-6">
        <h3 class="text-xl text-gray-800 dark:text-gray-300 font-semibold mb-4">Comments</h3>
        <?php foreach ($comments as $comment): ?>
            <div class="mb-6 bg-gray-100 p-4 rounded-lg dark:bg-gray-700">
                <div class="flex items-center mb-2">
                    <!-- Comment User Image -->
                    <?php if ($comment['profile_image']): ?>
                    <img src="../uploads/profile_images/<?php echo htmlspecialchars($comment['profile_image']); ?>" alt="Profile Image" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;">
                    <?php endif; ?>
                    <!-- Comment Username -->
                    <span class="font-bold dark:text-gray-300"><?php echo htmlspecialchars($comment['username']); ?></span>
                </div>
                <!-- Comment Content -->
                <p class="mb-2 comment-content-<?php echo $comment['id']; ?>"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                <div class="flex justify-between items-center">
                    <!-- Likes and Dislikes -->
                    <div>
                        <button onclick="likeComment(<?php echo $comment['id']; ?>)" class="text-red-500">
                            <i class="fas fa-heart"></i> Like (<?php echo $comment['likes_count']; ?>)
                        </button>
                        <button onclick="dislikeComment(<?php echo $comment['id']; ?>)" class="text-blue-500">
                            <i class="fas fa-thumbs-down"></i> Dislike (<?php echo $comment['dislikes_count']; ?>)
                        </button>
                    </div>
                    <!-- Edit and Delete Comment (if user owns it or is admin) -->
                    <?php if ((isset($_SESSION['user_id']) && $_SESSION['user_id'] == $comment['user_id']) || 
            (isset($_SESSION['role']) && $_SESSION['role'] == 'admin')): ?>
                    <div>
                        <button class="text-blue-500 edit-button" onclick="toggleEdit(<?php echo $comment['id']; ?>)">تعديل</button>
                        <a href="#" class="text-red-500 delete-button" data-comment-id="<?php echo $comment['id']; ?>" onclick="deleteComment(<?php echo $comment['id']; ?>)"><i class="fas fa-trash-alt"></i></a>
                        <form action="show.php?id=<?php echo $id; ?>" method="POST" class="inline-block edit-form-<?php echo $comment['id']; ?>" style="display:none;">
                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                            <textarea name="new_content" class="border rounded p-2"><?php echo htmlspecialchars($comment['content']); ?></textarea>
                            <button type="submit" class="text-blue-500">تعديل</button>
                        </form>
                    </div>
                <?php endif; ?>

                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Add Comment Form -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <form action="show.php?id=<?php echo $id; ?>" method="POST" class="mt-6">
            <textarea name="comment_content" class="w-full p-2 border rounded-lg dark:bg-gray-600 dark:text-white" placeholder="Add a comment..."></textarea>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 mt-2 rounded-lg">Submit</button>
        </form>
    <?php else: ?>
        <p class="mt-4">Please <a href="login.php" class="text-blue-500">log in</a> to comment.</p>
    <?php endif; ?>
</div>

<script type="text/javascript">
    function likePost(postId) {
        fetch('like.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ post_id: postId, type: 'like' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the likes count after like
                document.querySelector('#like-button').nextSibling.nodeValue = ' ' + data.likes_count + ' Likes';
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function retweet(postId) {
        fetch('retweet.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ post_id: postId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the retweets count after retweet
                document.querySelector('#retweet-button').nextSibling.nodeValue = ' ' + data.retweets_count + ' Retweets';
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function likeComment(commentId) {
        fetch('like_comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ comment_id: commentId, type: 'like' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the likes count for the comment
                const likesButton = document.querySelector(`.comment-content-${commentId}`).nextElementSibling.querySelector('button:nth-child(1)');
                likesButton.innerText = `Like (${data.likes_count})`;
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function dislikeComment(commentId) {
        fetch('like_comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ comment_id: commentId, type: 'dislike' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the dislikes count for the comment
                const dislikesButton = document.querySelector(`.comment-content-${commentId}`).nextElementSibling.querySelector('button:nth-child(2)');
                dislikesButton.innerText = `Dislike (${data.dislikes_count})`;
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function toggleEdit(commentId) {
        const editForm = document.querySelector(`.edit-form-${commentId}`);
        editForm.style.display = editForm.style.display === 'none' || editForm.style.display === '' ? 'inline-block' : 'none';
    }
</script>
<script>
function deleteComment(commentId) {
    if (confirm('هل أنت متأكد أنك تريد حذف هذا التعليق؟')) {
        // طلب الحذف باستخدام AJAX
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'delete_comment.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                // إذا تم الحذف بنجاح، قم بإزالة التعليق من الصفحة
                document.getElementById('comment-' + commentId).remove();
            }
        };
        xhr.send('comment_id=' + commentId);
    }
}
</script>

<?php include '../includes/footer.php'; ?>