<?php
session_start();
include '../includes/db.php'; // Database connection
include '../includes/header.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /users/login.php');
    exit;
}

// Handle account deletion
if (isset($_POST['confirm_delete'])) {
    $user_id = $_SESSION['user_id'];

    // Keep the user's posts, but delete the account
    $deleteQuery = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $deleteQuery->execute([$user_id]);

    session_destroy();
    header('Location: /users/register.php');
    exit;
}
?>

<main class="container mx-auto mt-6">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded p-6 max-w-md mx-auto">
        <h2 class="text-xl font-bold mb-4 text-center"><?php echo $lang === 'ar' ? 'تأكيد حذف الحساب' : 'Confirm Account Deletion'; ?></h2>
        <p><?php echo $lang === 'ar' ? 'هل أنت متأكد أنك تريد حذف حسابك؟ هذه العملية لا يمكن التراجع عنها.' : 'Are you sure you want to delete your account? This action cannot be undone.'; ?></p>

        <form method="post" id="deleteAccountForm">
            <div class="flex justify-between mt-6">
                <button type="button" id="confirmDelete" class="text-red-500 hover:text-red-700 flex items-center space-x-2">
                    <i class="fas fa-trash"></i>
                    <span><?php echo $lang === 'ar' ? 'نعم، احذف حسابي' : 'Yes, delete my account'; ?></span>
                </button>

                <a href="profile.php" class="text-gray-500 hover:text-gray-700 flex items-center space-x-2">
                    <i class="fas fa-arrow-left"></i>
                    <span><?php echo $lang === 'ar' ? 'إلغاء' : 'Cancel'; ?></span>
                </a>
            </div>
        </form>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('confirmDelete').addEventListener('click', function () {
        Swal.fire({
            title: '<?php echo $lang === 'ar' ? 'تأكيد الحذف' : 'Confirm Deletion'; ?>',
            text: '<?php echo $lang === 'ar' ? 'هل أنت متأكد أنك تريد حذف حسابك؟' : 'Are you sure you want to delete your account?'; ?>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<?php echo $lang === 'ar' ? 'نعم، احذف حسابي' : 'Yes, delete my account'; ?>',
            cancelButtonText: '<?php echo $lang === 'ar' ? 'إلغاء' : 'Cancel'; ?>'
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit the form if confirmed
                document.getElementById('deleteAccountForm').submit();
            }
        });
    });
</script>

<?php include '../includes/footer.php'; ?>
