<?php
session_start();
include '../includes/db.php'; // Database connection
include '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Fetch user data from the database
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $pdo->prepare($sql); 
    $stmt->bindParam(1, $username);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Logged in successfully!',
                    }).then(() => {
                        window.location.href = '../users/profile.php';
                    });
                  </script>";
        } else {
            echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Invalid password.',
                    });
                  </script>";
        }
    } else {
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'User not found.',
                });
              </script>";
    }
    $stmt = null; // Close statement
}
?>

<div class="flex items-center justify-center min-h-screen bg-gray-100 dark:bg-gray-800">
    <div class="bg-white dark:bg-gray-700 p-8 rounded-lg shadow-lg w-full max-w-sm">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-700 dark:text-gray-300">Login</h2>
        <form method="POST" class="space-y-4">
            <div>
                <label for="username" class="block text-gray-700 dark:text-gray-300">Username</label>
                <input type="text" name="username" id="username" placeholder="Username" required class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded dark:bg-gray-600 dark:text-white">
            </div>
            <div>
                <label for="password" class="block text-gray-700 dark:text-gray-300">Password</label>
                <input type="password" name="password" id="password" placeholder="Password" required class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded dark:bg-gray-600 dark:text-white">
            </div>
            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 rounded">Login</button>
        </form>
        <p class="mt-4 text-center text-gray-600 dark:text-gray-400">
            Don't have an account? <a href="register.php" class="text-blue-500 hover:underline">Sign up</a>
        </p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
