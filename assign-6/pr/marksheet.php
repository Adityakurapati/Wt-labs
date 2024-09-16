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

// Function to add a student
function addStudent($name, $roll_number) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO students (name, roll_number) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $roll_number);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Function to add marks
function addMarks($student_id, $subject_id, $mse_marks, $ese_marks) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO marks (student_id, subject_id, mse_marks, ese_marks) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iidd", $student_id, $subject_id, $mse_marks, $ese_marks);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Function to get subjects
function getSubjects() {
    global $conn;
    $result = $conn->query("SELECT id, name FROM subjects");
    return $result->fetch_all(MYSQLI_ASSOC);
}

$message = '';

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_student'])) {
        $name = $_POST['student_name'];
        $roll_number = $_POST['roll_number'];
        if (addStudent($name, $roll_number)) {
            $message = "Student added successfully.";
        } else {
            $message = "Error adding student.";
        }
    } elseif (isset($_POST['add_marks'])) {
        $student_id = $_POST['student_id'];
        $success = true;
        foreach ($_POST['marks'] as $subject_id => $marks) {
            $mse_marks = $marks['mse'];
            $ese_marks = $marks['ese'];
            if (!addMarks($student_id, $subject_id, $mse_marks, $ese_marks)) {
                $success = false;
                break;
            }
        }
        if ($success) {
            $message = "Marks added successfully.";
        } else {
            $message = "Error adding marks.";
        }
    }
}

// Fetch students and subjects for dropdowns
$students = $conn->query("SELECT id, name, roll_number FROM students");
$subjects = getSubjects();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIT Marksheet System</title>
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
            form {
                background-color: #2c2c2c;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 20px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                animation: slideIn 0.5s ease-out;
            }
            input, select {
                width: 100%;
                padding: 10px;
                margin-bottom: 10px;
                border: none;
                border-radius: 4px;
                background-color: #3a3a3a;
                color: #ffffff;
                transition: background-color 0.3s ease;
            }
            input[type="submit"] {
                background-color: #00bcd4;
                color: #ffffff;
                cursor: pointer;
                transition: background-color 0.3s ease;
            }
            input[type="submit"]:hover {
                background-color: #008ba3;
            }
            .message {
                background-color: #4caf50;
                color: #ffffff;
                padding: 10px;
                margin-bottom: 20px;
                border-radius: 4px;
                text-align: center;
                animation: fadeIn 0.5s ease-out;
            }
            .view-link {
                display: block;
                text-align: center;
                margin-top: 20px;
                color: #00bcd4;
                text-decoration: none;
                transition: color 0.3s ease;
            }
            .view-link:hover {
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
        <h1>VIT Marksheet System</h1>

        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <h2>Add Student</h2>
        <form method="post" id="addStudentForm">
            <input type="text" name="student_name" placeholder="Student Name" required>
            <input type="text" name="roll_number" placeholder="Roll Number" required>
            <input type="submit" name="add_student" value="Add Student">
        </form>

        <h2>Add Marks</h2>
        <form method="post" id="addMarksForm">
            <select name="student_id" required>
                <option value="">Select Student</option>
                <?php while ($row = $students->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>"><?php echo $row['name'] . ' (' . $row['roll_number'] . ')'; ?></option>
                <?php endwhile; ?>
            </select>
            
            <?php foreach ($subjects as $subject): ?>
                <h3><?php echo htmlspecialchars($subject['name']); ?></h3>
                <input type="number" name="marks[<?php echo $subject['id']; ?>][mse]" placeholder="MSE Marks (out of 30)" min="0" max="30" step="0.1" required>
                <input type="number" name="marks[<?php echo $subject['id']; ?>][ese]" placeholder="ESE Marks (out of 70)" min="0" max="70" step="0.1" required>
            <?php endforeach; ?>

            <input type="submit" name="add_marks" value="Add Marks">
        </form>

        <a href="show_marksheet.php" class="view-link">View Marksheet</a>
    </div>

    
        <script>
            document.getElementById('addStudentForm').addEventListener('submit', function(e) {
                e.preventDefault();
                this.style.animation = 'none';
                this.offsetHeight; // Trigger reflow
                this.style.animation = 'slideIn 0.5s ease-out';
                this.submit();
            });
    
            document.getElementById('addMarksForm').addEventListener('submit', function(e) {
                e.preventDefault();
                this.style.animation = 'none';
                this.offsetHeight; // Trigger reflow
                this.style.animation = 'slideIn 0.5s ease-out';
                this.submit();
            });
        </script>
    </body>
    </html>
    
    <?php
    $conn->close();
    ?>