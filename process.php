<?php
// Validate form inputs
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = $_POST["name"];
  $email = $_POST["email"];
  $password = $_POST["password"];
  $profile_pic = $_FILES["profile_pic"];

  $errors = array();
  
  if (empty($name)) {
    $errors[] = "Name is required";
  }
  
  if (empty($email)) {
    $errors[] = "Email is required";
  } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format";
  }
  
  if (empty($password)) {
    $errors[] = "Password is required";
  }
  
  if ($profile_pic["error"] == UPLOAD_ERR_NO_FILE) {
    $errors[] = "Profile picture is required";
  } else if ($profile_pic["error"] != UPLOAD_ERR_OK) {
    $errors[] = "Error uploading profile picture";
  } else {
    $file_ext = strtolower(pathinfo($profile_pic["name"], PATHINFO_EXTENSION));
    $file_name = uniqid() . '_' . date('YmdHis') . '.' . $file_ext;
    $file_path = "uploads/" . $file_name;
    
    if (!move_uploaded_file($profile_pic["tmp_name"], $file_path)) {
      $errors[] = "Error saving profile picture";
    }
  }
  
  if (empty($errors)) {
    // Save user data to CSV file
    $user_data = array($name, $email, $file_name);
    $user_data_str = implode(",", $user_data) . "\n";
    file_put_contents("users.csv", $user_data_str, FILE_APPEND);
    
    // Start new session and set cookie
    session_start();
    setcookie("username", $name, time() + (86400 * 30), "/");
    
    // Redirect to success page
    header("Location: success.html");
    exit();
  }
}

// If there are errors, display them to the user
if (!empty($errors)) {
  echo "<ul>";
  foreach ($errors as $error) {
    echo "<li>$error</li>";
  }
  echo "</ul>";
}
