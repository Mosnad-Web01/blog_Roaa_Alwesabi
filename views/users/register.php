<?php
session_start(); 
include '../includes/db.php'; 
include '../includes/header.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $bio = $_POST['bio'];
    
    // معالجة تحميل صورة الملف الشخصي
    $profile_image = NULL; // قيمة افتراضية
    if ($_FILES['profile_image']['name']) {
        $target_dir = "../uploads/profile_images/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // إنشاء الدليل إذا لم يكن موجودًا
        }
        $image_name = basename($_FILES['profile_image']['name']);
        $target_file = $target_dir . $image_name;
        
        // حفظ الملف في الدليل
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            $profile_image = $image_name;
        } 
    }
    
    // إدخال البيانات في قاعدة البيانات
    $sql = "INSERT INTO users (username, password, name, email, phone_number, bio, profile_image) 
    VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql); 

    try {
        $stmt->execute([$username, $password, $name, $email, $phone_number, $bio, $profile_image]);

        // تأكيد التسجيل
        $_SESSION['success'] = $lang === 'ar' ? "تم إنشاء الحساب بنجاح. ستظل منشوراتك موجودة إذا حذفت حسابك." : "Account created successfully. Your posts will remain if you delete your account.";
        $redirect = true; // تعيين متغير للتحقق من التوجيه لاحقًا
    } catch (PDOException $e) {
        $_SESSION['error'] = $lang === 'ar' ? "فشل في إنشاء الحساب." : "Failed to create account.";
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../../assets/css/output.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title><?php echo $lang === 'ar' ? 'تسجيل حساب جديد' : 'Register Account'; ?></title>
</head>
<body class="bg-gray-100 text-black dark:bg-gray-800 dark:text-white">
    <main class="container mx-auto mt-6">
        <div class="bg-white dark:bg-gray-900 shadow-md rounded p-6 max-w-md mx-auto">
            <h2 class="text-xl font-bold mb-4 text-center"><?php echo $lang === 'ar' ? 'تسجيل حساب جديد' : 'Register Account'; ?></h2>

            <!-- نموذج التسجيل -->
            <form method="POST" enctype="multipart/form-data">
                <input type="text" name="username" placeholder="Username" required class="mb-4 w-full p-2 border border-gray-300 rounded">
                <input type="password" name="password" placeholder="Password" required class="mb-4 w-full p-2 border border-gray-300 rounded">
                <input type="text" name="name" placeholder="Name" required class="mb-4 w-full p-2 border border-gray-300 rounded">
                <input type="email" name="email" placeholder="Email" class="mb-4 w-full p-2 border border-gray-300 rounded">
                <input type="text" name="phone_number" placeholder="Phone Number" class="mb-4 w-full p-2 border border-gray-300 rounded">
                <textarea name="bio" placeholder="Bio" class="mb-4 w-full p-2 border border-gray-300 rounded"></textarea>
                <input type="file" name="profile_image" accept="image/*" class="mb-4 w-full">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded w-full hover:bg-blue-700"><?php echo $lang === 'ar' ? 'تسجيل' : 'Register'; ?></button>
            </form>

            <script>
                <?php if (isset($_SESSION['success'])): ?>
                    Swal.fire({
                        icon: 'success',
                        title: '<?php echo $lang === 'ar' ? 'نجاح' : 'Success'; ?>',
                        text: '<?php echo $_SESSION['success']; ?>',
                    }).then(() => {
                        // التوجيه بعد عرض الرسالة
                        window.location.href = 'login.php'; 
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
