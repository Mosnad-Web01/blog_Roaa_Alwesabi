<?php
session_start();

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: /Blog2/views/users/login.php");
    exit();
}
?>
