<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Client Information</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <style>
    form {
      background: linear-gradient(to right, #007bff, #00b8d4);
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .form-control:focus {
      border-color: #007bff;
      box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    .btn-primary {
      background-color: #007bff;
      border-color: #007bff;
    }
    .btn-primary:hover {
      background-color: #0056b3;
      border-color: #004a8a;
    }
  </style>
</head>
<body class="container m-4">
  <h1>Client Information</h1>
  <div class="row">
    <div class="col-md-6">
      <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST["admin"])) {
          $email = isset($_POST["email"]) ? $_POST["email"] : "";
          $password = isset($_POST["password"]) ? $_POST["password"] : "";

          // Store data in Replit database
          $curl = curl_init();
          curl_setopt_array($curl, array(
            CURLOPT_URL => getenv('REPLIT_DB_URL') . '/' . urlencode($email),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => urlencode($password)
          ));
          $response = curl_exec($curl);
          curl_close($curl);

          echo "<div class='alert alert-success'>Form submitted successfully!</div>";
        }
      ?>
      <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <fieldset>
          <legend>Client Information</legend>
          <div class="container">
            <label for="email" class="form-label mt-4">Email address</label>
            <input type="email" class="form-control" id="email" name="email" aria-describedby="emailHelp" placeholder="Enter email" required>
            <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
          </div>
          <div>
            <label for="password" class="form-label mt-4">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" autocomplete="off" required>
          </div>
          <button type="submit" class="m-4 btn btn-primary">Submit</button>
        </fieldset>
      </form>
    </div>
    <div class="col-md-6">
      <?php
        // Check if admin button was clicked
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["admin"])) {
          $admin_password = isset($_POST["admin_password"]) ? $_POST["admin_password"] : "";
          if ($admin_password == "admin24") {
            // Retrieve data from Replit database
            $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => getenv('REPLIT_DB_URL') . '?prefix=',
              CURLOPT_RETURNTRANSFER => true
            ));
            $response = curl_exec($curl);
            curl_close($curl);

            // Parse and display user data
            $data = json_decode($response, true);
            if ($data) {
              echo "<h2>User Data:</h2><ul>";
              foreach ($data as $email => $password) {
                echo "<li>Email: " . htmlspecialchars($email) . " - Password: " . htmlspecialchars($password) . "</li>";
              }
              echo "</ul>";
            } else {
              echo "<div class='alert alert-info'>No data found.</div>";
            }
          } else {
            echo "<div class='alert alert-danger'>Invalid admin password.</div>";
          }
        }
      ?>
      <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <fieldset>
          <legend>Admin</legend>
          <div class="container">
            <label for="admin_password" class="form-label mt-4">Admin Password</label>
            <input type="password" class="form-control" id="admin_password" name="admin_password" placeholder="Admin Password" autocomplete="off" required>
          </div>
          <button type="submit" class="m-4 btn btn-danger" name="admin">View User Data</button>
        </fieldset>
      </form>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>
