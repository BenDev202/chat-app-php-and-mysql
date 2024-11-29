<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    exit();
}

$user_id = $_SESSION['user_id'];
$searchTerm = $_POST['searchTerm'];

// Function to get the correct image path
function getImagePath($imageName) {
    if ($imageName === 'default.png') {
        return 'images/' . $imageName;
    } else {
        return $imageName;
    }
}

$stmt = $pdo->prepare("
    SELECT u.*, 
           CASE WHEN m.sender_id = ? THEN CONCAT('You: ', LEFT(m.message, 20)) ELSE LEFT(m.message, 20) END AS latest_message,
           CASE WHEN m.max_created_at IS NULL THEN 0 ELSE 1 END AS has_message
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
    WHERE u.user_id != ? AND (u.first_name LIKE ? OR u.last_name LIKE ?)
    ORDER BY has_message DESC, m.max_created_at DESC, u.status DESC
");
$stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, "%$searchTerm%", "%$searchTerm%"]);
$users = $stmt->fetchAll();

$output = "";
if (count($users) > 0) {
    foreach ($users as $user) {
        $output .= '<a href="chat.php?user_id=' . $user['user_id'] . '">
                      <div class="content">
                        <img src="' . getImagePath($user['image']) . '" alt="">
                        <div class="details">
                          <span>' . $user['first_name'] . ' ' . $user['last_name'] . '</span>
                          <p>' . ($user['latest_message'] ? htmlspecialchars($user['latest_message']) : "No messages yet") . '</p>
                        </div>
                      </div>
                      <div class="status-dot ' . ($user['status'] == 'Online' ? 'Online' : 'Offline') . '">
                        <i class="fas fa-circle"></i>
                      </div>
                    </a>';
    }
} else {
    $output .= "No users found related to your search term";
}
echo $output;