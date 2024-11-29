<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$receiver_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

if (!$receiver_id) {
    header("Location: user.php");
    exit();
}

// Update the current user's last activity
$stmt = $pdo->prepare("UPDATE users SET last_activity = NOW() WHERE user_id = ?");
$stmt->execute([$user_id]);

// Fetch the receiver's information
$stmt = $pdo->prepare("
    SELECT *, 
           CASE WHEN TIMESTAMPDIFF(SECOND, last_activity, NOW()) < 300 THEN 'Online' ELSE 'Offline' END AS current_status
    FROM users 
    WHERE user_id = ?
");
$stmt->execute([$receiver_id]);
$receiver = $stmt->fetch();

if (!$receiver) {
    header("Location: user.php");
    exit();
}

// Function to get the correct image path
function getImagePath($image) {
    if ($image === '') {
        return 'images/default.png';
    } else {
        return $image;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chat with <?php echo htmlspecialchars($receiver['first_name'] . ' ' . $receiver['last_name']); ?> - Realtime Chat App</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
  <div class="wrapper">
    <section class="chat-area">
      <header>
        <a href="user.php" class="back-icon"><i class="fas fa-arrow-left"></i></a>
        <img src="<?php echo getImagePath($receiver['image']); ?>" alt="">
        <div class="details">
          <span><?php echo htmlspecialchars($receiver['first_name'] . ' ' . $receiver['last_name']); ?></span>
          <p><?php echo $receiver['current_status']; ?></p>
        </div>
      </header>
      <div class="chat-box">
        <!-- Messages will be loaded here dynamically -->
      </div>
      <form action="#" class="typing-area">
        <input type="text" name="outgoing_id" value="<?php echo $user_id; ?>" hidden>
        <input type="text" name="incoming_id" value="<?php echo $receiver_id; ?>" hidden>
        <input type="text" name="message" class="input-field" placeholder="Type a message here..." autocomplete="off">
        <button><i class="fab fa-telegram-plane"></i></button>
      </form>
    </section>
  </div>
  <script src="javascript/chat.js"></script>
</body>
</html>