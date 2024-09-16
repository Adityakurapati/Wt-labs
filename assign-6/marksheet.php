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

// Handle form submission to add marks
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['student_id'])) {
    $student_id = $_POST['student_id'];
    $subjects = getSubjects();

    // Loop through subjects and save MSE and ESE marks
    foreach ($subjects as $subject) {
        $subject_id = $subject['id'];
        $mse_marks = $_POST['mse_marks'][$subject_id];
        $ese_marks = $_POST['ese_marks'][$subject_id];

        // Insert or update marks for the student
        $stmt = $conn->prepare("INSERT INTO marks (student_id, subject_id, mse_marks, ese_marks) 
                                VALUES (?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE mse_marks = ?, ese_marks = ?");
        $stmt->bind_param("iiiiii", $student_id, $subject_id, $mse_marks, $ese_marks, $mse_marks, $ese_marks);

        if (!$stmt->execute()) {
            die("Error inserting marks: " . $stmt->error);
        }
    }

    echo "<p>Marks added successfully for student ID: $student_id</p>";
}

// Get all students and subjects
$students = getStudents();
$subjects = getSubjects();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Marks</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #0D1B2A;
            color: white;
            padding: 20px;
        }

        h1, h2 {
            color: #E0E1DD;
            text-align: center;
            margin-bottom: 30px;
        }

        form {
            margin: 0 auto;
            max-width: 700px;
            background-color: #1B263B;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        label {
            display: block;
            margin: 15px 0 5px;
            color: #E0E1DD;
            font-weight: 500;
        }

        input[type="number"],
        select {
            width: 100%;
            padding: 12px;
            margin-top: 5px;
            border-radius: 6px;
            border: 1px solid #4C566A;
            background-color: #14213D;
            color: #E0E1DD;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        input[type="number"]:focus,
        select:focus {
            border-color: #FF8C32;
            outline: none;
        }

        select {
            cursor: pointer;
        }

        input[type="submit"] {
            margin-top: 30px;
            padding: 15px;
            background-color: #FF8C32;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #FF6700;
            transform: translateY(-2px);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #0D1B2A;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #23395B;
        }

        th {
            background-color: #1B263B;
            color: #FF8C32;
        }

        td {
            background-color: #14213D;
            color: #E0E1DD;
        }

        tr:nth-child(even) td {
            background-color: #1B263B;
        }

        input[type="number"] {
            transition: background-color 0.3s ease;
        }

        input[type="number"]:hover {
            background-color: #162C47;
        }

        /* Add a subtle animation to form */
        form {
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Tooltip */
        .tooltip {
            position: relative;
            display: inline-block;
            cursor: pointer;
            color: #FF8C32;
        }

        .tooltip .tooltiptext {
            visibility: hidden;
            width: 120px;
            background-color: #1B263B;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            margin-left: -60px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }
    </style>
</head>
<body>
    <h1>Add Marks for Students</h1>
    
    <form method="post">
        <label for="student_id">Select Student:</label>
        <select name="student_id" id="student_id" required>
            <option value="">-- Select a Student --</option>
            <?php foreach ($students as $student): ?>
                <option value="<?php echo htmlspecialchars($student['id']); ?>">
                    <?php echo htmlspecialchars($student['name'] . ' (' . $student['roll_number'] . ')'); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <h2>Enter Marks:</h2>
        <table>
            <tr>
                <th>Subject</th>
                <th>MSE Marks <span class="tooltip">ⓘ<span class="tooltiptext">Max: 50</span></span></th>
                <th>ESE Marks <span class="tooltip">ⓘ<span class="tooltiptext">Max: 100</span></span></th>
            </tr>
            <?php foreach ($subjects as $subject): ?>
            <tr>
                <td><?php echo htmlspecialchars($subject['name']); ?></td>
                <td><input type="number" name="mse_marks[<?php echo $subject['id']; ?>]" min="0" max="50" required></td>
                <td><input type="number" name="ese_marks[<?php echo $subject['id']; ?>]" min="0" max="100" required></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <input type="submit" value="Add Marks">
    </form>
</body>
</html>


<?php
$conn->close();
?>
