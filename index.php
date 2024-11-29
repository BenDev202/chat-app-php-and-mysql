<?php
session_start();
require_once 'config.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "Email already exists.";
        } else {
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
                // Insert user into database
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, image) VALUES (?, ?, ?, ?, ?)");
                
                try {
                    $stmt->execute([$first_name, $last_name, $email, $hashed_password, $image]);
                    $_SESSION['user_id'] = $pdo->lastInsertId();
                    $success = "Signup successful! Redirecting to chat...";
                    header("Location: user.php");
                } catch (PDOException $e) {
                    $error = "Error: " . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Signup - Realtime Chat App</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
  <div class="wrapper">
    <section class="form signup">
      <header>Realtime Chat App</header>
      <form action="" method="POST" enctype="multipart/form-data">
        <?php if (!empty($error)): ?>
          <div class="error-text" style=" color: #721c24; background: #f8d7da; padding: 8px 10px; text-align: center; border-radius: 5px; margin-bottom: 10px; border: 1px solid #f5c6cb;"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
          <div class="success-text" style=" color: #00c476; background: hsl(156, 100%, 38%, 15%); padding: 8px 10px; text-align: center; border-radius: 5px; margin-bottom: 10px; border: 1px solid #93f3cc;"><?php echo $success; ?></div>
        <?php endif; ?>
        <div class="name-details">
          <div class="field input">
            <label>First name</label>
            <input type="text" name="first_name" placeholder="First name" required>
          </div>
          <div class="field input">
            <label>Last name</label>
            <input type="text" name="last_name" placeholder="Last name" required>
          </div>
        </div>
        <div class="field input">
          <label>Email Address</label>
          <input type="email" name="email" placeholder="Enter your email" required>
        </div>
        <div class="field input">
          <label>Password</label>
          <input type="password" name="password" placeholder="Enter new password" required>
          <i class="fas fa-eye"></i>
        </div>
        <div class="field image">
          <label>Select image</label>
          <input type="file" name="image" accept="image/x-png,image/gif,image/jpeg,image/jpg">
        </div>
        <div class="field button">
          <input type="submit" value="Continue to Chat">
        </div>
      </form>
      <div class="link">Already signed up? <a href="login.php">Login now</a></div>
    </section>
  </div>
  <script src="javascript/pass-show-hide.js"></script>
  <script src="javascript/signup.js"></script>
</body>
</html>