<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile - Realtime Chat App</title>
  <link rel="stylesheet" href="./style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
  <div class="wrapper">
    <section class="users">
      <header>
        <div class="content">
          <img src="<?php echo $user['image']; ?>" alt="">
          <div class="details">
            <span><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></span>
            <p><?php echo $user['status']; ?></p>
          </div>
        </div>
        <a href="logout.php" class="logout">Logout</a>
      </header>
      <div class="profile-info">
        <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
        <p><strong>Joined:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
      </div>
      <div class="profile-actions">
        <a href="user.php" class="button-link" style="color: #efefef;">Chat with Users</a>
        <a href="edit-profile.php" class="button-link" style="color: #efefef;">Edit Profile</a>
      </div>
    </section>
  </div>
</body>
</html>