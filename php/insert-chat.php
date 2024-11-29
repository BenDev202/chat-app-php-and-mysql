<?php
session_start();
if(isset($_SESSION['user_id'])){
    include_once "../config.php";
    $sender_id = $_POST['outgoing_id'];
    $receiver_id = $_POST['incoming_id'];
    $message = $_POST['message'];
    if(!empty($message)){
        $sql = "INSERT INTO messages (sender_id, receiver_id, message)
                VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sender_id, $receiver_id, $message]);
    }
}else{
    header("location: ../login.php");
}