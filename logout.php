<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("UPDATE users SET status = 'Offline' WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    session_unset();
    session_destroy();
}
header("Location: login.php");
exit();
?>