<?php
// student.php
require_once 'config.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: index.php");
    exit;
}
$student_ref_id = $_SESSION['user']['ref_id'];
// fetch student info
$stmt = $mysqli->prepare("SELECT * FROM student WHERE id = ?");
$stmt->bind_param('i', $student_ref_id); $stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
if (!$student) die("Student record not found.");

// handle password change
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_pwd'])) {
    $old = $_POST['old_password']; $new = $_POST['new_password']; $confirm = $_POST['confirm_password'];
    if ($new !== $confirm) $msg = "New passwords do not match.";
    else {
        // verify current password
        $stmt = $mysqli->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param('i', $_SESSION['user']['id']); $stmt->execute();
        $pw = $stmt->get_result()->fetch_assoc()['password'];
        if (!password_verify($old, $pw)) $msg = "Old password incorrect.";
        else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $u = $mysqli->prepare("UPDATE users SET password = ? WHERE id = ?");
            $u->bind_param('si', $hash, $_SESSION['user']['id']); $u->execute();
            $msg = "Password changed.";
        }
    }
}

// fetch marks for this student
$stm = $mysqli->prepare("SELECT e.*, t.name as teacher_name FROM exam e LEFT JOIN teacher t ON e.teacher_id = t.id WHERE e.student_id = ?");
$stm->bind_param('i', $student_ref_id); $stm->execute();
$exams = $stm->get_result()->fetch_all(MYSQLI_ASSOC);

// compute cgpa from exams (for all 6 courses take average; if course missing, skip)
$gpas = [];
foreach($exams as $e) {
    if ($e['gpa'] !== null) $gpas[] = (float)$e['gpa'];
}
$cgpa = compute_cgpa($gpas);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Student Dashboard - <?php echo htmlspecialchars($student['name']); ?></title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;background:#f7fafc;padding:20px}
    .wrap{max-width:900px;margin:0 auto}
    header{display:flex;justify-content:space-between;align-items:center}
    table{width:100%;border-collapse:collapse;margin:12px 0;background:#fff}
    th,td{padding:8px;border:1px solid #e6e6e6}
    .card{background:#fff;padding:12px;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,.04);margin-top:12px}
    input{padding:8px;margin:6px 0;width:100%}
    button{padding:8px 12px;background:#2b6cb0;color:#fff;border:none;border-radius:6px;cursor:pointer}
    .msg{padding:8px;background:#e6fffa;border:1px solid #b2f5ea;margin:8px 0}
  </style>
</head>
<body>
  <div class="wrap">
    <header>
      <h1>Welcome, <?php echo htmlspecialchars($student['name']); ?></h1>
      <div>
        <a href="logout.php">Logout</a>
      </div>
    </header>

    <?php if ($msg): ?><div class="msg"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>

    <div class="card">
      <h3>Your Marks</h3>
      <table>
        <thead><tr><th>Course</th><th>Teacher</th><th>Total</th><th>Grade</th><th>GPA</th><th>Updated</th></tr></thead>
        <tbody>
          <?php
          // display each predefined course even if missing
          $all_courses = ['RE'=>'Requirement Engineering','TPE'=>'Tools & Practice','SS'=>'Software Security','ADB'=>'Advanced Database','WEB'=>'Web Development','ASM'=>'Assemble Language'];
          // index exams by course for quick lookup
          $byCourse = [];
          foreach($exams as $e) $byCourse[$e['course_code']] = $e;
          foreach($all_courses as $code => $name):
              $e = $byCourse[$code] ?? null;
          ?>
            <tr>
              <td><?php echo "$code - $name"; ?></td>
              <td><?php echo $e ? htmlspecialchars($e['teacher_name']) : '-'; ?></td>
              <td><?php echo $e ? $e['total'] : '-'; ?></td>
              <td><?php echo $e ? $e['grade'] : '-'; ?></td>
              <td><?php echo $e ? $e['gpa'] : '-'; ?></td>
              <td><?php echo $e ? $e['updated_at'] : '-'; ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p><strong>Your CGPA (average across submitted courses):</strong> <?php echo $cgpa; ?></p>
    </div>

    <div class="card">
      <h3>Change Password</h3>
      <form method="post">
        <input type="password" name="old_password" placeholder="Old password" required>
        <input type="password" name="new_password" placeholder="New password" required>
        <input type="password" name="confirm_password" placeholder="Confirm new password" required>
        <button type="submit" name="change_pwd">Change Password</button>
      </form>
    </div>
  </div>
</body>
</html>
