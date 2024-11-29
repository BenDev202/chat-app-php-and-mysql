<?php 
session_start();
if(isset($_SESSION['user_id'])){
    include_once "../config.php";
    $outgoing_id = $_POST['outgoing_id'];
    $incoming_id = $_POST['incoming_id'];
    $output = "";
    $sql = "SELECT * FROM messages 
            LEFT JOIN users ON users.user_id = messages.sender_id
            WHERE (sender_id = ? AND receiver_id = ?)
            OR (sender_id = ? AND receiver_id = ?) 
            ORDER BY message_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$outgoing_id, $incoming_id, $incoming_id, $outgoing_id]);
    $messages = $stmt->fetchAll();
    if(count($messages) > 0){
        foreach($messages as $message){
            if($message['sender_id'] === $outgoing_id){
                $output .= '<div class="chat outgoing">
                            <div class="details">
                                <p>'. htmlspecialchars($message['message']) .'</p>
                            </div>
                            </div>';
            }else{
                $output .= '<div class="chat incoming">
                            <img src="'.$message['image'].'" alt="">
                            <div class="details">
                                <p>'. htmlspecialchars($message['message']) .'</p>
                            </div>
                            </div>';
            }
        }
    }else{
        $output .= '<div class="text">No messages are available. Once you send message they will appear here.</div>';
    }
    echo $output;
}else{
    header("location: ../login.php");
}