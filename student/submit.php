<?php
require 'db.php';

// Must be logged in as student
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

// CSRF verification
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    die("Invalid CSRF token.");
}

// Retrieve submitted answers (array: question_id => selected_option)
$answers = $_POST['ans'] ?? [];
if (!is_array($answers)) {
    $answers = [];
}

// Calculate score server-side
$score = 0;
$stmt = $conn->prepare("SELECT id, correct FROM questions");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $qid = $row['id'];
    if (isset($answers[$qid]) && $answers[$qid] == $row['correct']) {
        $score++;
    }
}
$stmt->close();

// Record result in database (prepared statement)
$insert = $conn->prepare("INSERT INTO results (user_id, score) VALUES (?, ?)");
$insert->bind_param("ii", $_SESSION['uid'], $score);
$insert->execute();
$insert->close();

// Clear session (force logout after exam)
session_destroy();

// Display score
?>
<!DOCTYPE html>
<html>
<head>
    <title>Exam Submitted</title>
</head>
<body>
    <h2>Exam Completed</h2>
    <p>Your score: <strong><?php echo $score; ?></strong></p>
    <p><a href="login.php">Login again</a></p>
</body>
</html>