<?php
require 'db.php';

// Only admin allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied.");
}

$csrf_token = generate_csrf_token();
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $message = "Invalid CSRF token.";
    } else {
        // Validate inputs
        $question = trim($_POST['q'] ?? '');
        $opt1     = trim($_POST['o1'] ?? '');
        $opt2     = trim($_POST['o2'] ?? '');
        $opt3     = trim($_POST['o3'] ?? '');
        $opt4     = trim($_POST['o4'] ?? '');
        $correct  = filter_input(INPUT_POST, 'ans', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 4]]);

        if (empty($question) || empty($opt1) || empty($opt2) || empty($opt3) || empty($opt4) || !$correct) {
            $message = "All fields are required and correct answer must be 1-4.";
        } else {
            // Insert using prepared statement
            $stmt = $conn->prepare("INSERT INTO questions (question, opt1, opt2, opt3, opt4, correct) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssi", $question, $opt1, $opt2, $opt3, $opt4, $correct);
            if ($stmt->execute()) {
                $message = "Question added successfully!";
            } else {
                $message = "Database error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch existing questions for display
$questions = $conn->query("SELECT id, question, opt1, opt2, opt3, opt4, correct FROM questions ORDER BY id");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Admin – Add Question</h2>
    <?php if ($message): ?>
        <p style="color:green"><?php echo e($message); ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="text" name="q" placeholder="Question text" required><br>
        <input type="text" name="o1" placeholder="Option 1" required><br>
        <input type="text" name="o2" placeholder="Option 2" required><br>
        <input type="text" name="o3" placeholder="Option 3" required><br>
        <input type="text" name="o4" placeholder="Option 4" required><br>
        <input type="number" name="ans" placeholder="Correct option (1-4)" min="1" max="4" required><br>
        <button type="submit">Add Question</button>
    </form>

    <h3>Existing Questions</h3>
    <?php if ($questions->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Question</th>
                <th>Opt1</th>
                <th>Opt2</th>
                <th>Opt3</th>
                <th>Opt4</th>
                <th>Correct</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $questions->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo e($row['question']); ?></td>
                <td><?php echo e($row['opt1']); ?></td>
                <td><?php echo e($row['opt2']); ?></td>
                <td><?php echo e($row['opt3']); ?></td>
                <td><?php echo e($row['opt4']); ?></td>
                <td><?php echo $row['correct']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No questions yet.</p>
    <?php endif; ?>

    <p><a href="logout.php">Logout</a></p>
</body>
</html>