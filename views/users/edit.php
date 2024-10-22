<?php
session_start();

include '../includes/db.php'; // الاتصال بقاعدة البيانات
include '../includes/header.php'; 

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone_number = $_POST['phone_number'];
        $bio = $_POST['bio'];
        $password = $_POST['password']; // إضافة حقل كلمة المرور
        
        // معالجة تحديث صورة الملف الشخصي
        $profile_image = NULL; // قيمة افتراضية
        if ($_FILES['profile_image']['name']) {
            $target_dir = "../uploads/profile_images/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $image_name = basename($_FILES['profile_image']['name']);
            $target_file = $target_dir . $image_name;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                $profile_image = $image_name;
            } 
        }

        // تشفير كلمة المرور إذا تم إدخالها
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET name = ?, email = ?, phone_number = ?, bio = ?, profile_image = ?, password = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $params = [$name, $email, $phone_number, $bio, $profile_image, $hashed_password, $user_id];
        } else {
            $sql = "UPDATE users SET name = ?, email = ?, phone_number = ?, bio = ?, profile_image = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $params = [$name, $email, $phone_number, $bio, $profile_image, $user_id];
        }
        
        try {
            $stmt->execute($params);
            $_SESSION['success'] = $lang === 'ar' ? "تم تحديث الحساب بنجاح." : "Account updated successfully.";
            $redirect = true; // تعيين متغير للتحقق من التوجيه لاحقًا
        } catch (PDOException $e) {
            $_SESSION['error'] = $lang === 'ar' ? "فشل في تحديث الحساب." : "Failed to update account.";
        }
    }

    // جلب بيانات المستخدم لملء النموذج
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../../assets/css/output.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title><?php echo $lang === 'ar' ? 'تعديل الملف الشخصي' : 'Edit Profile'; ?></title>
</head>
<body class="bg-gray-100 text-black dark:bg-gray-800 dark:text-white">
    <main class="container mx-auto mt-6">
        <div class="bg-white dark:bg-gray-900 shadow-md rounded p-6 max-w-md mx-auto">
            <h2 class="text-xl font-bold mb-4 text-center"><?php echo $lang === 'ar' ? 'تعديل الملف الشخصي' : 'Edit Profile'; ?></h2>

            <!-- نموذج تعديل البيانات -->
            <form method="POST" enctype="multipart/form-data">
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required class="mb-4 w-full p-2 border border-gray-300 rounded">
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="mb-4 w-full p-2 border border-gray-300 rounded">
                <input type="text" name="phone_number" value="<?= htmlspecialchars($user['phone_number']) ?>" class="mb-4 w-full p-2 border border-gray-300 rounded">
                <textarea name="bio" class="mb-4 w-full p-2 border border-gray-300 rounded"><?= htmlspecialchars($user['bio']) ?></textarea>
                <input type="file" name="profile_image" accept="image/*" class="mb-4 w-full">
                <input type="password" name="password" placeholder="<?php echo $lang === 'ar' ? 'كلمة المرور الجديدة' : 'New Password'; ?>" class="mb-4 w-full p-2 border border-gray-300 rounded">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded w-full hover:bg-blue-700"><?php echo $lang === 'ar' ? 'تحديث' : 'Update'; ?></button>
            </form>

            <script>
                <?php if (isset($_SESSION['success'])): ?>
                    Swal.fire({
                        icon: 'success',
                        title: '<?php echo $lang === 'ar' ? 'نجاح' : 'Success'; ?>',
                        text: '<?php echo $_SESSION['success']; ?>',
                    }).then(() => {
                        // التوجيه بعد عرض الرسالة
                        window.location.href = 'profile.php'; 
                    });
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    Swal.fire({
                        icon: 'error',
                        title: '<?php echo $lang === 'ar' ? 'خطأ' : 'Error'; ?>',
                        text: '<?php echo $_SESSION['error']; ?>',
                    });
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
            </script>
        </div>
    </main>
</body>
</html>

<?php include '../includes/footer.php'; ?>
