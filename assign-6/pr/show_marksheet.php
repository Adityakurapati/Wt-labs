<?php
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

// Function to get marks for a student
function getMarks($student_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT subjects.name, marks.mse_marks, marks.ese_marks 
                            FROM marks 
                            JOIN subjects ON marks.subject_id = subjects.id 
                            WHERE marks.student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to calculate grade
function calculateGrade($total_marks) {
    if ($total_marks >= 90) return 'S';
    elseif ($total_marks >= 80) return 'A';
    elseif ($total_marks >= 70) return 'B';
    elseif ($total_marks >= 60) return 'C';
    elseif ($total_marks >= 50) return 'D';
    else return 'F';
}

$students = getStudents();

// Handle form submission
$selected_student_id = isset($_POST['student']) ? $_POST['student'] : null;
$student_marks = $selected_student_id ? getMarks($selected_student_id) : null;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIT Marksheet Results</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #1a1a1a;
            color: #ffffff;
            transition: background-color 0.3s ease;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2 {
            color: #00bcd4;
            text-align: center;
            animation: fadeIn 1s ease-out;
        }
        .student-select {
            margin-bottom: 20px;
            text-align: center;
        }
        select {
            padding: 10px;
            border: none;
            border-radius: 4px;
            background-color: #3a3a3a;
            color: #ffffff;
            transition: background-color 0.3s ease;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            animation: slideIn 0.5s ease-out;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #444;
        }
        th {
            background-color: #2c2c2c;
            color: #00bcd4;
        }
        tr:nth-child(even) {
            background-color: #2c2c2c;
        }
        tr:hover {
            background-color: #3a3a3a;
        }
        .result {
            background-color: #2c2c2c;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1s ease-out;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #00bcd4;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .back-link:hover {
            color: #008ba3;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>VIT Marksheet Results</h1>

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
        <h2>Marksheet</h2>
        <table>
            <tr>
                <th>Subject</th>
                <th>MSE Marks (30%)</th>
                <th>ESE Marks (70%)</th>
                <th>Total Marks</th>
                <th>Grade</th>
            </tr>
            <?php 
            $total_marks = 0;
            $subject_count = 0;
            foreach ($student_marks as $mark): 
                $mse_contribution = $mark['mse_marks'];
                $ese_contribution = $mark['ese_marks'];
                $subject_total = $mse_contribution + $ese_contribution;
                $total_marks += $subject_total;
                $subject_count++;
            ?>
            <tr>
                <td><?php echo htmlspecialchars($mark['name']); ?></td>
                <td><?php echo htmlspecialchars($mark['mse_marks']); ?></td>
                <td><?php echo htmlspecialchars($mark['ese_marks']); ?></td>
                <td><?php echo number_format($subject_total, 2); ?></td>
                <td><?php echo calculateGrade($subject_total); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <?php 
        $average_marks = $total_marks / $subject_count;
        $gpa = ($average_marks / 100) * 10;
        ?>

        <div class="result">
            <h2>Semester Result</h2>
            <p>Total Marks: <?php echo number_format($total_marks, 2); ?></p>
            <p>Average Marks: <?php echo number_format($average_marks, 2); ?></p>
            <p>GPA: <?php echo number_format($gpa, 2); ?></p>
            <p>Overall Grade: <?php echo calculateGrade($average_marks); ?></p>
        </div>

        <?php elseif ($selected_student_id): ?>
        <p>No marks found for this student.</p>
        <?php endif; ?>

        <a href="marksheet.php" class="back-link">Back to Add Marks</a>
    </div>

    <script>
        document.querySelector('select').addEventListener('change', function() {
            this.form.style.animation = 'none';
            this.form.offsetHeight; // Trigger reflow
            this.form.style.animation = 'fadeIn 0.5s ease-out';
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>