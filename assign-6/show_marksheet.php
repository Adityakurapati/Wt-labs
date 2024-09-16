<?php
// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "marksheet_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get all students
function getStudents() {
    global $conn;
    $result = $conn->query("SELECT * FROM students");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to get all subjects
function getSubjects() {
    global $conn;
    $result = $conn->query("SELECT * FROM subjects");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to get marks for a student
function getMarks($student_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT subjects.name, marks.mse_marks, marks.ese_marks 
                            FROM marks 
                            JOIN subjects ON marks.subject_id = subjects.id 
                            WHERE marks.student_id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $student_id);
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to calculate GPA and Grades
function calculateGPA($marks) {
    $total_marks = 0;
    $total_subjects = count($marks);
    $grade = '';
    $grade_points = 0;
    
    foreach ($marks as $mark) {
        $total = $mark['mse_marks'] + $mark['ese_marks']; // Total of MSE and ESE marks
        
        // Assign grade and grade points based on the total marks
        if ($total >= 90) {
            $grade_points += 4.0;
            $grade = 'A+';
        } elseif ($total >= 80) {
            $grade_points += 3.7;
            $grade = 'A';
        } elseif ($total >= 70) {
            $grade_points += 3.3;
            $grade = 'B+';
        } elseif ($total >= 60) {
            $grade_points += 3.0;
            $grade = 'B';
        } elseif ($total >= 50) {
            $grade_points += 2.7;
            $grade = 'C+';
        } elseif ($total >= 40) {
            $grade_points += 2.3;
            $grade = 'C';
        } else {
            $grade_points += 0;
            $grade = 'F';  // Fail
        }

        $total_marks += $total;
    }

    // Calculate GPA
    $gpa = $grade_points / $total_subjects;
    
    return ['gpa' => round($gpa, 2), 'total_marks' => $total_marks, 'grade' => $grade];
}

// Get all students and subjects
$students = getStudents();
$subjects = getSubjects();

// Handle form submission
$selected_student_id = isset($_POST['student']) ? $_POST['student'] : null;
$student_marks = $selected_student_id ? getMarks($selected_student_id) : null;
$gpa_details = $student_marks ? calculateGPA($student_marks) : null;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marksheet System</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #1a1a2e;
            color: #fff;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h1, h2 {
            color: #f1f1f1;
            text-align: center;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            max-width: 800px;
            margin-bottom: 20px;
            background-color: #16213e;
            color: #fff;
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            border: 1px solid #333;
            padding: 12px 20px;
            text-align: left;
        }
        th {
            background-color: #0f3460;
        }
        tr:nth-child(even) {
            background-color: #1a2a6c;
        }
        select, input {
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            width: 100%;
            max-width: 300px;
            background-color: #0f3460;
            color: #fff;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        select:hover, input:hover {
            background-color: #16213e;
        }
        input[type="submit"] {
            cursor: pointer;
            background-color: #e94560;
        }
        input[type="submit"]:hover {
            background-color: #f25d75;
        }
        .student-select {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .container {
            width: 100%;
            max-width: 800px;
        }
        .no-marks {
            color: #f2f2f2;
            font-size: 18px;
            text-align: center;
        }
        .gpa-section {
            margin-top: 20px;
            text-align: center;
            background-color: #16213e;
            padding: 20px;
            border-radius: 10px;
        }
        .gpa-section p {
            margin: 5px 0;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Marksheet System</h1>

        <h2>Students</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Roll Number</th>
            </tr>
            <?php foreach ($students as $student): ?>
            <tr>
                <td><?php echo htmlspecialchars($student['id']); ?></td>
                <td><?php echo htmlspecialchars($student['name']); ?></td>
                <td><?php echo htmlspecialchars($student['roll_number']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <h2>Student Marks</h2>
        <form method="post" class="student-select">
            <label for="student">Select a student: </label>
            <select id="student" name="student" onchange="this.form.submit()">
                <option value="">Select a student</option>
                <?php foreach ($students as $student): ?>
                <option value="<?php echo htmlspecialchars($student['id']); ?>" <?php echo $selected_student_id == $student['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($student['name'] . ' (' . $student['roll_number'] . ')'); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if ($student_marks): ?>
        <table>
            <tr>
                <th>Subject</th>
                <th>MSE Marks</th>
                <th>ESE Marks</th>
            </tr>
            <?php foreach ($student_marks as $mark): ?>
            <tr>
                <td><?php echo htmlspecialchars($mark['name']); ?></td>
                <td><?php echo htmlspecialchars($mark['mse_marks']); ?></td>
                <td><?php echo htmlspecialchars($mark['ese_marks']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <div class="gpa-section">
            <h2>Final Result</h2>
            <p><strong>Total Marks: </strong><?php echo htmlspecialchars($gpa_details['total_marks']); ?></p>
            <p><strong>Final GPA: </strong><?php echo htmlspecialchars($gpa_details['gpa']); ?></p>
            <p><strong>Grade: </strong><?php echo htmlspecialchars($gpa_details['grade']); ?></p>
        </div>
        <?php elseif ($selected_student_id): ?>
        <p class="no-marks">No marks found for this student.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
