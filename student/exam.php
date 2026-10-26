<?php
require 'db.php';

// Only authenticated students
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

// Fetch all questions
$questions = $conn->query("SELECT id, question, opt1, opt2, opt3, opt4 FROM questions ORDER BY id");
$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Online Exam</title>
    <style>
        .question { margin-bottom: 20px; }
        .options label { display: block; margin-left: 20px; }
    </style>
</head>
<body>
    <h2>Examination</h2>
    <p>Welcome, <?php echo e($_SESSION['user']); ?>! Answer all questions.</p>
    <form method="post" action="submit.php">
        <!-- CSRF Protection -->
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

        <?php $i = 1; while ($row = $questions->fetch_assoc()): ?>
        <div class="question">
            <p><strong><?php echo $i++; ?>.</strong> <?php echo e($row['question']); ?></p>
            <div class="options">
                <?php for ($j = 1; $j <= 4; $j++): ?>
                <label>
                    <input type="radio" name="ans[<?php echo $row['id']; ?>]" value="<?php echo $j; ?>" required>
                    <?php echo e($row["opt$j"]); ?>
                </label>
                <?php endfor; ?>
            </div>
        </div>
        <?php endwhile; ?>

        <button type="submit">Submit Exam</button>
    </form>

    <!-- Optional anti-cheat JavaScript -->
    <script>
        // Disable right-click
        document.addEventListener('contextmenu', e => e.preventDefault());

        // Detect tab/window switch
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                alert('Warning: Tab switching is not allowed and may be recorded!');
                // Optionally log this event via AJAX
            }
        });
    </script>
</body>
</html>