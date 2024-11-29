<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Update the current user's last activity
$stmt = $pdo->prepare("UPDATE users SET last_activity = NOW() WHERE user_id = ?");
$stmt->execute([$user_id]);

// Fetch the current user's information
$stmt = $pdo->prepare("
    SELECT *, 
           CASE WHEN TIMESTAMPDIFF(SECOND, last_activity, NOW()) < 300 THEN 'Online' ELSE 'Offline' END AS current_status
    FROM users 
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$current_user = $stmt->fetch();

// Function to get the correct image path
function getImagePath($image) {
    if ($image === '') {
        return 'images/default.png';
    } else {
        return $image;
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Realtime Chat App</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
  <div class="wrapper">
    <section class="users">
      <header>
        <div class="content">
          <img src="<?php echo getImagePath($current_user['image']); ?>" alt="">
          <div class="details">
            <span><?php echo $current_user['first_name'] . " " . $current_user['last_name']; ?></span>
            <p><?php echo $current_user['current_status']; ?></p>
          </div>
        </div>
        <a href="logout.php" class="logout">Logout</a>
      </header>
      <div class="search">
        <span class="text">Select a user to start chat</span>
        <input type="text" placeholder="Enter name to search...">
        <button><i class="fas fa-search"></i></button>
      </div>
      <div class="users-list">
        <?php foreach ($users as $user): ?>
          <a href="chat.php?user_id=<?php echo $user['user_id']; ?>">
            <div class="content">
              <img src="<?php echo getImagePath($user['image']); ?>" alt="">
              <div class="details">
                <span><?php echo $user['first_name'] . " " . $user['last_name']; ?></span>
                <p><?php echo $user['latest_message'] ? htmlspecialchars($user['latest_message']) : "No messages yet"; ?></p>
              </div>
            </div>
            <div class="status-dot <?php echo $user['current_status'] == 'Online' ? 'Online' : 'Offline'; ?>">
              <i class="fas fa-circle"></i>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
  <script src="javascript/users.js"></script>
</body>
</html>