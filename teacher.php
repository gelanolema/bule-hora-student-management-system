<?php
session_start();
// IMPORTANT: Ensure this path is correct and config.php initializes $conn (mysqli connection)
include("../includes/config.php"); 

// --- Configuration Checks ---
// 1. Check if $conn is a valid object
if (!isset($conn) || $conn->connect_error) {
    die("Database Connection Error: " . (isset($conn) ? $conn->connect_error : "Config file missing or failed to initialize \$conn."));
}

// Ensure teacher is logged in
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'] ?? '', ['teacher','admin'])) {
    header("Location: ../index.php");
    exit();
}

// Resolve teacher id from common session keys
$teacher_id = 0;
if (!empty($_SESSION['user_id'])) $teacher_id = intval($_SESSION['user_id']);
elseif (!empty($_SESSION['teacher_id'])) $teacher_id = intval($_SESSION['teacher_id']);
elseif (!empty($_SESSION['id'])) $teacher_id = intval($_SESSION['id']); // fallback

if ($teacher_id <= 0) {
    die("Session Error: User ID is missing. Please check your login session setup.");
}

/**
 * Helper: check if teacher assigned to course_code (uses teacher_course table)
 */
function teacher_owns_course($conn, $teacher_id, $course_code) {
    $course_code = mysqli_real_escape_string($conn, trim($course_code));
    $sql = "SELECT id FROM teacher_course WHERE teacher_id = ? AND course_code = ? LIMIT 1";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("is", $teacher_id, $course_code);
        $stmt->execute();
        $stmt->store_result();
        $ok = ($stmt->num_rows === 1);
        $stmt->close();
        return $ok;
    }
    return false;
}

/**
 * Calculate grade and gpa from total score.
 */
function calculate_grade_gpa($total) {
    $t = floatval($total);
    if ($t >= 90) return ['A', 4.0];
    if ($t >= 85) return ['A-', 3.7];
    if ($t >= 80) return ['B+', 3.3];
    if ($t >= 75) return ['B', 3.0];
    if ($t >= 70) return ['B-', 2.7];
    if ($t >= 65) return ['C+', 2.3];
    if ($t >= 60) return ['C', 2.0];
    if ($t >= 50) return ['D', 1.0];
    return ['F', 0.0];
}

$message = "";

/* ---------------- ADD RECORD ---------------- */
if (isset($_POST['add_record'])) {
    $student_id = intval($_POST['student_id'] ?? 0);
    $course_code = trim($_POST['course_code'] ?? '');
    // numeric components: allow float
    $quiz = is_numeric($_POST['quiz']) ? floatval($_POST['quiz']) : 0;
    $test = is_numeric($_POST['test']) ? floatval($_POST['test']) : 0;
    $assignment = is_numeric($_POST['assignment']) ? floatval($_POST['assignment']) : 0;
    $project = is_numeric($_POST['project']) ? floatval($_POST['project']) : 0;
    $mid = is_numeric($_POST['mid']) ? floatval($_POST['mid']) : 0;
    $final = is_numeric($_POST['final']) ? floatval($_POST['final']) : 0;

    if ($student_id <= 0 || $course_code === '') {
        $message = "Please choose a student and course.";
    } else {
        if (!teacher_owns_course($conn, $teacher_id, $course_code)) {
            $message = "Permission denied: you are not assigned to this course.";
        } else {
            // compute total, grade, gpa
            $total = $quiz + $test + $assignment + $project + $mid + $final;
            list($grade, $gpa) = calculate_grade_gpa($total);

            $sql = "INSERT INTO exam (student_id, teacher_id, course_code, quiz, test, assignment, project, mid, final, total, grade, gpa) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("iissddddddsd",
                    $student_id,
                    $teacher_id,
                    $course_code,
                    $quiz,
                    $test,
                    $assignment,
                    $project,
                    $mid,
                    $final,
                    $total,
                    $grade,
                    $gpa
                );
                if ($stmt->execute()) {
                    $message = "Record added successfully.";
                } else {
                    if ($conn->errno == 1062) {
                        $message = "Error: A record for this student and course already exists. Please use the 'Edit' function to update it.";
                    } else {
                         $message = "DB error: " . $stmt->error;
                    }
                }
                $stmt->close();
            } else {
                $message = "DB prepare error (Add Record): " . $conn->error;
            }
        }
    }
}

/* ---------------- EDIT RECORD ---------------- */
if (isset($_POST['edit_record'])) {
    $exam_id = intval($_POST['exam_id'] ?? 0);
    $quiz = is_numeric($_POST['quiz']) ? floatval($_POST['quiz']) : 0;
    $test = is_numeric($_POST['test']) ? floatval($_POST['test']) : 0;
    $assignment = is_numeric($_POST['assignment']) ? floatval($_POST['assignment']) : 0;
    $project = is_numeric($_POST['project']) ? floatval($_POST['project']) : 0;
    $mid = is_numeric($_POST['mid']) ? floatval($_POST['mid']) : 0;
    $final = is_numeric($_POST['final']) ? floatval($_POST['final']) : 0;

    if ($exam_id <= 0) {
        $message = "Invalid record id.";
    } else {
        // fetch course_code from exam to check ownership
        $r = $conn->prepare("SELECT course_code FROM exam WHERE id = ? LIMIT 1");
        if ($r) {
            $r->bind_param("i", $exam_id);
            $r->execute();
            $res = $r->get_result();
            if ($row = $res->fetch_assoc()) {
                $course_code = $row['course_code'];
                
                // Permission check
                if (!teacher_owns_course($conn, $teacher_id, $course_code)) {
                    $message = "Permission denied: You are not assigned to manage this course.";
                } else {
                    $total = $quiz + $test + $assignment + $project + $mid + $final;
                    list($grade, $gpa) = calculate_grade_gpa($total);

                    $u = $conn->prepare("UPDATE exam SET quiz=?, test=?, assignment=?, project=?, mid=?, final=?, total=?, grade=?, gpa=? WHERE id=?");
                    if ($u) {
                        $u->bind_param("dddddddsdi",
                            $quiz, $test, $assignment, $project, $mid, $final, $total, $grade, $gpa, $exam_id
                        );
                        if ($u->execute()) {
                            $message = "Record updated successfully.";
                        } else {
                            $message = "Update error: " . $u->error;
                        }
                        $u->close();
                    } else {
                        $message = "DB update prepare error: " . $conn->error;
                    }
                }
            } else {
                $message = "Record not found.";
            }
            $r->close();
        } else {
            $message = "DB error (Edit Fetch): " . $conn->error;
        }
    }
}

/* ---------------- DELETE RECORD ---------------- */
if (isset($_POST['delete_record'])) {
    $exam_id = intval($_POST['exam_id'] ?? 0);
    if ($exam_id > 0) {
        $r = $conn->prepare("SELECT course_code FROM exam WHERE id = ? LIMIT 1");
        if ($r) {
            $r->bind_param("i", $exam_id);
            $r->execute();
            $res = $r->get_result();
            if ($row = $res->fetch_assoc()) {
                $course_code = $row['course_code'];
                
                // Permission check
                if (!teacher_owns_course($conn, $teacher_id, $course_code)) {
                    $message = "Permission denied: You are not assigned to manage this course.";
                } else {
                    $d = $conn->prepare("DELETE FROM exam WHERE id = ?");
                    if ($d) {
                        $d->bind_param("i", $exam_id);
                        $d->execute();
                        $message = "Record deleted.";
                        $d->close();
                    } else {
                        $message = "DB delete prepare error: " . $conn->error;
                    }
                }
            } else {
                $message = "Record not found.";
            }
            $r->close();
        } else {
            $message = "DB error (Delete Fetch): " . $conn->error;
        }
    }
}

/* ---------------- Fetch assigned courses ---------------- */
$assigned_courses = [];
$caq = $conn->prepare("SELECT tc.course_code, c.course_name 
                      FROM teacher_course tc
                      LEFT JOIN course c ON tc.course_code = c.course_code
                      WHERE tc.teacher_id = ? 
                      ORDER BY tc.course_code");
if ($caq) {
    $caq->bind_param("i", $teacher_id);
    if (!$caq->execute()) {
         $message .= "<br>Error executing course fetch: " . $caq->error;
    }
    $cr = $caq->get_result();
    while ($row = $cr->fetch_assoc()) {
        $assigned_courses[$row['course_code']] = !empty($row['course_name']) ? $row['course_name'] : $row['course_code']; 
    }
    $caq->close();
} else {
     $message .= "<br>DB Prepare Error (Course Fetch): " . $conn->error;
}


/* ---------------- Fetch marks (exams) for assigned courses ---------------- */
$marks = [];
if (!empty($assigned_courses)) {
    $course_codes = array_keys($assigned_courses);
    
    // Build IN clause safely
    $escaped = array_map(function($c) use ($conn){ return "'" . mysqli_real_escape_string($conn, $c) . "'"; }, $course_codes);
    $in = implode(",", $escaped);
    
    $q = "SELECT e.id as exam_id, e.student_id, e.course_code, e.quiz, e.test, e.assignment, e.project, e.mid, e.final, e.total, e.grade, e.gpa, s.name as student_name
            FROM exam e
            LEFT JOIN student s ON e.student_id = s.id
            WHERE e.course_code IN ($in) 
            ORDER BY e.course_code, s.name";
            
    $res = mysqli_query($conn, $q);
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) $marks[] = $r;
    } else {
        $message .= "<br>Error fetching marks: " . mysqli_error($conn);
    }
}

/* ---------------- Fetch students for add form ---------------- */
$students = [];
$sr = mysqli_query($conn, "SELECT id, name FROM student ORDER BY name");
if($sr) {
    while ($s = mysqli_fetch_assoc($sr)) $students[] = $s;
} else {
    $message .= "<br>Error fetching students: " . mysqli_error($conn);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Teacher Dashboard</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
/* ==================================== */
/* ========== DESIGN OVERHAUL ========= */
/* ==================================== */

/* --- Variables and Global Reset --- */
:root {
    --primary-color:rgb(141, 186, 245); /* Google Blue */
    --primary-dark:rgb(106, 165, 252);
    --primary-light: #e8f0fe;
    --text-dark: #202124;
    --text-medium: #5f6368;
    --background-light:rgb(113, 98, 250);
    --success-color: #34a853;
    --danger-color: #ea4335;
    --card-shadow: 0 1px 3px rgba(60,64,67,0.1), 0 1px 2px rgba(60,64,67,0.1);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Roboto', sans-serif;
    background: var(--background-light);
    color: var(--text-dark);
    line-height: 1.6;
}

/* --- Navigation Bar --- */
.nav {
    background: var(--primary-color);
    color: white;
    padding: 16px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 18px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.nav-title {
    font-weight: 500;
}

.nav a {
    color: white;
    text-decoration: none;
    background: var(--primary-dark);
    padding: 8px 14px;
    border-radius: 4px;
    font-weight: 400;
    transition: background-color 0.3s;
    font-size: 14px;
}
.nav a:hover {
    background: #073b9e;
}

/* --- Container and Cards --- */
.container {
    padding: 30px 24px;
    max-width: 1300px;
    margin: auto;
}

.card {
    background: white;
    padding: 24px;
    border-radius: 8px;
    box-shadow: var(--card-shadow);
    margin-bottom: 25px;
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(60,64,67,0.15);
}

h2 {
    margin-bottom: 20px;
    color: var(--primary-dark);
    font-size: 22px;
    font-weight: 500;
    border-bottom: 2px solid var(--primary-light);
    padding-bottom: 8px;
}

/* --- Forms and Inputs --- */
.row {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
}

.col {
    flex: 1;
    min-width: 0; /* Ensures proper collapsing on smaller screens */
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-size: 14px;
    font-weight: 500;
    color: var(--text-medium);
}

input, select {
    padding: 10px 12px;
    border-radius: 4px;
    border: 1px solid #dadce0;
    width: 100%;
    transition: border-color 0.2s, box-shadow 0.2s;
    font-size: 14px;
}

input:focus, select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 1px var(--primary-color);
    outline: none;
}

/* --- Buttons --- */
.btn {
    background: var(--primary-color);
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: background-color 0.3s, box-shadow 0.3s;
    display: inline-block;
    text-align: center;
}
.btn.small {
    padding: 5px 10px;
    font-size: 12px;
}

.btn:hover {
    background: var(--primary-dark);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.danger {
    background: var(--danger-color) !important;
}
.danger:hover {
    background: #c32720 !important;
}

/* --- Messages --- */
.message-box {
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 25px;
    font-weight: 500;
    border-left: 5px solid;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.success {
    border-left-color: var(--success-color); 
    color: #106f3a;
    background-color: #f6fffa;
}

.error {
    border-left-color: var(--danger-color); 
    color: #990000;
    background-color: #fff6f6;
}

/* --- Table Styles --- */
.table-responsive {
    overflow-x: auto;
}

table {
    min-width: 900px;
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 10px;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
}

th {
    background: var(--primary-color);
    color: #fff;
    padding: 12px 10px;
    text-align: left;
    font-weight: 500;
    font-size: 13px;
    text-transform: uppercase;
}
th:first-child { border-top-left-radius: 8px; }
th:last-child { border-top-right-radius: 8px; }

td {
    padding: 12px 10px;
    border-bottom: 1px solid #f1f1f1;
    font-size: 14px;
    vertical-align: middle;
}

tr:last-child td {
    border-bottom: none;
}

tr:nth-child(even) {
    background: #f7f9fa;
}

tr:hover {
    background: #eef0ff;
}

/* Specific Grade Highlighting */
td:nth-child(10), td:nth-child(11) { /* Total and Grade columns */
    font-weight: 500;
    color: var(--primary-dark);
}

/* --- Action Cell and Edit Form --- */
.action-cell {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 100px; /* Ensures buttons have space */
}

details {
    display: block;
    margin-top: 4px;
}
details summary {
    list-style: none; /* Hide default triangle */
    cursor: pointer;
    background: #fafafa;
    border: 1px solid #dadce0;
    padding: 6px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    color: var(--primary-color);
    transition: background-color 0.2s;
    user-select: none;
    display: inline-block;
}
details summary:hover {
    background: #f1f1f1;
}

.inline-form-group {
    margin-top: 10px;
    padding: 10px;
    border: 1px dashed #ccc;
    border-radius: 4px;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: flex-start;
}
.inline-form-group input {
    width: 70px;
    padding: 5px;
    font-size: 11px;
}
.inline-form-group button {
    margin-top: 8px;
    width: 100%;
}

/* --- Assigned Courses List --- */
.assigned-courses-list {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    list-style: none;
    padding: 0;
}
.assigned-courses-list li {
    background: var(--primary-light);
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    color: var(--primary-dark);
    border: 1px solid var(--primary-color);
}


/* --- Media Queries (Responsiveness) --- */
@media (max-width: 900px) {
    .row {
        flex-direction: column;
        gap: 0;
    }
    .col {
        width: 100%;
    }
    .nav {
        flex-direction: column;
        gap: 10px;
        font-size: 16px;
    }
    .container {
        padding: 20px 15px;
    }
    h2 {
        font-size: 20px;
    }
}
</style>

</head>
<body>
<div class="nav">
    <div class="nav-title">👨‍🏫 Teacher Dashboard - Welcome mr. <?php echo htmlspecialchars($_SESSION['username']); ?></div>
    <div>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <?php 
    if($message) {
        // Simple heuristic to color the message box
        $msg_class = (strpos($message, 'Error') !== false || strpos($message, 'DB') !== false || strpos($message, 'denied') !== false) ? 'error' : 'success';
        echo "<div class='message-box {$msg_class}'>".nl2br(htmlspecialchars($message))."</div>";
    } 
    ?>

    <div class="card">
        <h2>📚 Your Assigned Courses</h2>
        <?php
        if(empty($assigned_courses)){
            echo "<p>No courses assigned. Please check the <code>teacher_course</code> table for your teacher ID: **{$teacher_id}**.</p>";
        } else {
            // Updated structure for cleaner list display
            echo "<ul class='assigned-courses-list'>";
            foreach($assigned_courses as $code => $name) {
                echo "<li><strong>".htmlspecialchars($code)."</strong>: ".htmlspecialchars($name)."</li>";
            }
            echo "</ul>";
        }
        ?>
    </div>

    <div class="card">
        <h2>📝 Add New Exam Record</h2>
        <form method="post">
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="student_id">Student Name</label>
                        <select id="student_id" name="student_id" required>
                            <option value=""> Select Student </option>
                            <?php foreach($students as $s) echo "<option value='".intval($s['id'])."'>".htmlspecialchars($s['name'])." (ID: ".intval($s['id']).")</option>"; ?>
                        </select>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="course_code">Course Code / Name</label>
                        <select id="course_code" name="course_code" required>
                            <option value=""> Select Course </option>
                            <?php 
                            // Display logic (PHP code unchanged from previous working version)
                            foreach($assigned_courses as $code => $name) {
                                $display_text = htmlspecialchars($code) . " - " . htmlspecialchars($name);
                                echo "<option value='".htmlspecialchars($code)."'>{$display_text}</option>";
                            } 
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <div style="margin-top:12px;">
                <label style="font-weight:600; display:block; margin-bottom: 8px;">Enter Marks (Scores up to 100)</label>
                <div class="row">
                    <div class="col"><div class="form-group"><label>Quiz</label><input type="number" step="0.01" name="quiz" value="0" min="0" max="5" required></div></div>
                    <div class="col"><div class="form-group"><label>Test</label><input type="number" step="0.01" name="test" value="0" min="0" max="5" required></div></div>
                    <div class="col"><div class="form-group"><label>Assignment</label><input type="number" step="0.01" name="assignment" value="0" min="0" max="10" required></div></div>
                </div>

                <div class="row" style="margin-top:-8px;">
                    <div class="col"><div class="form-group"><label>Project</label><input type="number" step="0.01" name="project" value="0" min="0" max="20" required></div></div>
                    <div class="col"><div class="form-group"><label>Midterm</label><input type="number" step="0.01" name="mid" value="0" min="0" max="20" required></div></div>
                    <div class="col"><div class="form-group"><label>Final</label><input type="number" step="0.01" name="final" value="0" min="0" max="40" required></div></div>
                </div>
            </div>

            <div style="margin-top:15px; text-align: right;">
                <button class="btn" type="submit" name="add_record">➕ Add Record</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>📊 Student Exam Records</h2>
        <div class="table-responsive">
            <?php
            if(empty($marks)){
                echo "<p>No exam records have been added yet for your assigned courses.</p>";
            } else {
                echo "<table><thead><tr><th>#</th><th>Student Name</th><th>Course Code</th><th>Quiz</th><th>Test</th><th>Assign</th><th>Proj</th><th>Mid</th><th>Final</th><th>Total</th><th>Grade</th><th>GPA</th><th>Action</th></tr></thead><tbody>";
                $i=1;
                foreach($marks as $m){
                    $exam_id = intval($m['exam_id']);
                    $student_name = htmlspecialchars($m['student_name'] ?: ("ID ".$m['student_id']));
                    echo "<tr>
                        <td>{$i}</td>
                        <td>{$student_name}</td>
                        <td>".htmlspecialchars($m['course_code'])."</td>
                        <td>".htmlspecialchars($m['quiz'])."</td>
                        <td>".htmlspecialchars($m['test'])."</td>
                        <td>".htmlspecialchars($m['assignment'])."</td>
                        <td>".htmlspecialchars($m['project'])."</td>
                        <td>".htmlspecialchars($m['mid'])."</td>
                        <td>".htmlspecialchars($m['final'])."</td>
                        <td>".number_format((float)$m['total'], 2)."</td>
                        <td>".htmlspecialchars($m['grade'])."</td>
                        <td>".number_format((float)$m['gpa'], 2)."</td>
                        <td class='action-cell'>
                            <details>
                              <summary>✏️ Edit Scores</summary>
                              <form method='post'>
                                <input type='hidden' name='exam_id' value='{$exam_id}'>
                                <div class='inline-form-group'>
                                    <input type='number' step='0.01' name='quiz' value='".htmlspecialchars($m['quiz'])."' placeholder='Quiz' min='0' max='100'>
                                    <input type='number' step='0.01' name='test' value='".htmlspecialchars($m['test'])."' placeholder='Test' min='0' max='100'>
                                    <input type='number' step='0.01' name='assignment' value='".htmlspecialchars($m['assignment'])."' placeholder='Assign' min='0' max='100'>
                                    <input type='number' step='0.01' name='project' value='".htmlspecialchars($m['project'])."' placeholder='Proj' min='0' max='100'>
                                    <input type='number' step='0.01' name='mid' value='".htmlspecialchars($m['mid'])."' placeholder='Mid' min='0' max='100'>
                                    <input type='number' step='0.01' name='final' value='".htmlspecialchars($m['final'])."' placeholder='Final' min='0' max='100'>
                                    <button class='btn small' type='submit' name='edit_record'>Save Update</button>
                                </div>
                              </form>
                            </details>

                            <form method='post' onsubmit=\"return confirm('Are you sure you want to permanently delete this record for {$student_name}?');\">
                                <input type='hidden' name='exam_id' value='{$exam_id}'>
                                <button class='btn danger small' type='submit' name='delete_record'>🗑️ Delete</button>
                            </form>
                        </td>
                    </tr>";
                    $i++;
                }
                echo "</tbody></table>";
            }
            ?>
        </div>
    </div>
</div>

</body>
</html>