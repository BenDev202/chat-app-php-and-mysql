<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    exit();
}

$user_id = $_SESSION['user_id'];

// Function to get the correct image path
function getImagePath($imageName) {
    if ($imageName === '') {
        return 'images/' . $imageName;
    } else {
        return $imageName;
    }
}

// Fetch all users except the current user with their latest messages and current status
$stmt = $pdo->prepare("
    SELECT u.*, 
           CASE WHEN m.sender_id = ? THEN CONCAT('You: ', LEFT(m.message, 20)) ELSE LEFT(m.message, 20) END AS latest_message,
           CASE WHEN m.max_created_at IS NULL THEN 0 ELSE 1 END AS has_message,
           CASE WHEN TIMESTAMPDIFF(SECOND, u.last_activity, NOW()) < 300 THEN 'Online' ELSE 'Offline' END AS current_status
    FROM users u
    LEFT JOIN (
        SELECT 
            CASE 
                WHEN sender_id = ? THEN receiver_id 
                ELSE sender_id 
            END AS user_id,
            message,
            sender_id,
            MAX(created_at) as max_created_at
        FROM messages
        WHERE sender_id = ? OR receiver_id = ?
        GROUP BY user_id
    ) m ON u.user_id = m.user_id
    WHERE u.user_id != ?
    ORDER BY has_message DESC, m.max_created_at DESC, current_status DESC
");
$stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id]);
$users = $stmt->fetchAll();

$output = "";
foreach ($users as $user) {
    $output .= '<a href="chat.php?user_id=' . $user['user_id'] . '">
                  <div class="content">
                    <img src="' . getImagePath($user['image']) . '" alt="">
                    <div class="details">
                      <span>' . $user['first_name'] . ' ' . $user['last_name'] . '</span>
                      <p>' . ($user['latest_message'] ? htmlspecialchars($user['latest_message']) : "No messages yet") . '</p>
                    </div>
                  </div>
                  <div class="status-dot ' . ($user['current_status'] == 'Online' ? 'Online' : 'Offline') . '">
                    <i class="fas fa-circle"></i>
                  </div>
                </a>';
}
echo $output;