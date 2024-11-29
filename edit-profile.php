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

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
      $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
      $filename = $_FILES["image"]["name"];
      $filetype = $_FILES["image"]["type"];
      $filesize = $_FILES["image"]["size"];
  
      $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
      if (!array_key_exists($ext, $allowed)) {
          $error = "Please select a valid file format.";
      } else {
          $maxsize = 5 * 1024 * 1024; // 5MB
          if ($filesize > $maxsize) {
              $error = "File size is larger than the allowed limit.";
          } else {
              // Create uploads directory if it doesn't exist
              if (!file_exists('uploads')) {
                  mkdir('uploads', 0777, true);
              }
  
              // Generate a unique filename
              $new_filename = "uploads/" . uniqid() . "." . $ext;
  
              if (move_uploaded_file($_FILES["image"]["tmp_name"], $new_filename)) {
                  $image = $new_filename;
              } else {
                  $error = "Failed to upload image. Error: " . error_get_last()['message'];
              }
          }
      }
  } else {
      $image = $user['image'];
  }

    if (empty($error)) {
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, image = ? WHERE user_id = ?");
        try {
            $stmt->execute([$first_name, $last_name, $email, $image, $user_id]);
            $success = "Profile updated successfully!";
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Profile - Realtime Chat App</title>
  <link rel="stylesheet" href="./style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
  <div class="wrapper">
    <section class="form edit-profile">
      <header>Edit Profile</header>
      <form action="" method="POST" enctype="multipart/form-data">
        <?php if (!empty($error)): ?>
          <div class="error-text" style=" color: #721c24; background: #f8d7da; padding: 8px 10px; text-align: center; border-radius: 5px; margin-bottom: 10px; border: 1px solid #f5c6cb;"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
          <div class="success-text" style=" color: #00c476; background: hsl(156, 100%, 38%, 15%); padding: 8px 10px; text-align: center; border-radius: 5px; margin-bottom: 10px; border: 1px solid #93f3cc;"><?php echo $success; ?></div>
        <?php endif; ?>
        <div class="field input">
          <label>First Name</label>
          <input type="text" name="first_name" value="<?php echo $user['first_name']; ?>" required>
        </div>
        <div class="field input">
          <label>Last Name</label>
          <input type="text" name="last_name" value="<?php echo $user['last_name']; ?>" required>
        </div>
        <div class="field input">
          <label>Email Address</label>
          <input type="email" name="email" value="<?php echo $user['email']; ?>" required>
        </div>
        <div class="field image">
          <label>Profile Image</label>
          <input type="file" name="image" accept="image/x-png,image/gif,image/jpeg,image/jpg">
        </div>
        <div class="field button">
          <input type="submit" value="Update Profile">
        </div>
      </form>
      <div class="link">
        <a href="profile.php">Back to Profile</a>
      </div>
    </section>
  </div>
</body>
</html>