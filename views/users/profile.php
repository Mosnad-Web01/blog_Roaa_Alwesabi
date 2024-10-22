<?php
session_start();
include '../includes/db.php'; // الاتصال بقاعدة البيانات
include '../includes/header.php';

// تأكد من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    echo "<script> Swal.fire({ icon: 'error', title: 'Error', text: 'You must be logged in to view this page!' }).then(() => { window.location.href = '/Blog2/views/users/login.php'; }); </script>";
    exit;
}

// جلب تفاصيل المستخدم
$user_id = $_SESSION['user_id'];
$query = $pdo->prepare("SELECT * FROM users WHERE id = ?"); 
$query->execute([$user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<script> Swal.fire({ icon: 'error', title: 'Error', text: 'User not found!' }); </script>";
    exit;
}

// معالجة تسجيل الخروج
if (isset($_POST['logout'])) {
    session_destroy();
    echo "<script> Swal.fire({ icon: 'success', title: 'Logged Out', text: 'You have successfully logged out.' }).then(() => { window.location.href = '/Blog2/views/users/login.php'; }); </script>";
    exit;
}

// معالجة حذف الحساب
if (isset($_POST['delete_account'])) {
    // الاحتفاظ بمنشورات المستخدم، ولكن حذف الحساب
    $deleteQuery = $pdo->prepare("DELETE FROM users WHERE id = ?"); // تغيير $conn إلى $pdo
    $deleteQuery->execute([$user_id]);

    session_destroy();
    echo "<script> Swal.fire({ icon: 'success', title: 'Account Deleted', text: 'Your account has been deleted.' }).then(() => { window.location.href = '/Blog2/views/users/register.php'; }); </script>";
    exit;
}
?>

<main class="container mx-auto mt-6">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded p-6 max-w-md mx-auto">
        <h2 class="text-xl font-bold mb-4 text-center"><?php echo $lang === 'ar' ? 'معلومات الحساب' : 'Profile Information'; ?></h2>

        <div class="mb-4">
            <label class="block text-gray-700 dark:text-gray-300"><?php echo $lang === 'ar' ? 'اسم المستخدم:' : 'Username:'; ?></label>
            <p class="bg-gray-100 dark:bg-gray-700 p-2 rounded"><?php echo htmlspecialchars($user['username']); ?></p>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 dark:text-gray-300"><?php echo $lang === 'ar' ? 'الاسم:' : 'Name:'; ?></label>
            <p class="bg-gray-100 dark:bg-gray-700 p-2 rounded"><?php echo htmlspecialchars($user['name']); ?></p>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 dark:text-gray-300"><?php echo $lang === 'ar' ? 'البريد الإلكتروني:' : 'Email:'; ?></label>
            <p class="bg-gray-100 dark:bg-gray-700 p-2 rounded"><?php echo htmlspecialchars($user['email']); ?></p>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 dark:text-gray-300"><?php echo $lang === 'ar' ? 'رقم الهاتف:' : 'Phone Number:'; ?></label>
            <p class="bg-gray-100 dark:bg-gray-700 p-2 rounded"><?php echo htmlspecialchars($user['phone_number']); ?></p>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 dark:text-gray-300"><?php echo $lang === 'ar' ? 'السيرة الذاتية:' : 'Bio:'; ?></label>
            <p class="bg-gray-100 dark:bg-gray-700 p-2 rounded"><?php echo htmlspecialchars($user['bio']); ?></p>
        </div>

        <?php if ($user['profile_image']) : ?>
            <div class="mb-4">
                <label class="block text-gray-700 dark:text-gray-300"><?php echo $lang === 'ar' ? 'صورة الملف الشخصي:' : 'Profile Image:'; ?></label>
                <img src="../uploads/profile_images/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image" class="w-24 h-24 object-cover rounded-full border-2 border-gray-300">
            </div>
        <?php endif; ?>

        <div class="flex justify-between mt-6">
             <!-- زر منشوراتك -->
            <a href="../posts/user_posts.php" class="text-blue-500 hover:text-blue-700 flex items-center space-x-2">
                <i class="fas fa-file-alt"></i>
                <span><?php echo $lang === 'ar' ? 'منشوراتك' : 'Your Posts'; ?></span>
            </a>
            <a href="edit.php" class="text-blue-500 hover:text-blue-700 flex items-center space-x-2">
                <i class="fas fa-edit"></i>
                <span><?php echo $lang === 'ar' ? 'تعديل الملف الشخصي' : 'Edit Profile'; ?></span>
            </a>
            
            <form method="POST" action="" onsubmit="return confirm('<?php echo $lang === 'ar' ? 'هل أنت متأكد أنك تريد تسجيل الخروج؟' : 'Are you sure you want to log out?'; ?>');">
                <button type="submit" name="logout" class="text-gray-500 hover:text-gray-700 flex items-center space-x-2">
                    <i class="fas fa-sign-out-alt"></i>
                    <span><?php echo $lang === 'ar' ? 'تسجيل الخروج' : 'Logout'; ?></span>
                </button>
            </form>

            <form method="POST" action="" onsubmit="return confirm('<?php echo $lang === 'ar' ? 'هل أنت متأكد أنك تريد حذف حسابك؟' : 'Are you sure you want to delete your account?'; ?>');">
                <button type="submit" name="delete_account" class="text-red-500 hover:text-red-700 flex items-center space-x-2">
                    <i class="fas fa-trash"></i>
                    <span><?php echo $lang === 'ar' ? 'حذف الحساب' : 'Delete Account'; ?></span>
                </button>
            </form>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
