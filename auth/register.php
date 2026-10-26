<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role     = $_POST['role'] ?? 'student'; // only admin can elevate? For simplicity we force student

    // Validate
    if (empty($username) || empty($password)) {
        $error = "Username and password required.";
    } else {
        // Hash password
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert using prepared statement
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'student')");
        $stmt->bind_param("ss", $username, $hash);
        if ($stmt->execute()) {
            $success = "Registration successful. <a href='login.php'>Login</a>";
        } else {
            $error = "Username already exists or database error.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
    <h2>Student Registration</h2>
    <?php if (isset($error)) echo "<p style='color:red'>".e($error)."</p>"; ?>
    <?php if (isset($success)) echo "<p style='color:green'>".e($success)."</p>"; ?>
    <form method="post">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login</a></p>
</body>
</html>