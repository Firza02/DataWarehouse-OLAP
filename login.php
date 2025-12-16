<?php
session_start();
require 'koneksi.php';

$error = "";

// Jika sudah login → langsung lempar ke home
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}

// Proses form submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === "" || $password === "") {
        $error = "Username dan password wajib diisi.";
    } else {
        // Ambil user level eksekutif
        $sql  = "SELECT user_id, username, full_name, role, password 
                 FROM user 
                 WHERE username = ? AND role = 'executive'
                 LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            // Untuk tugas: cek plain text
            if ($password === $row['password']) {
                $_SESSION['user_id']   = $row['user_id'];
                $_SESSION['username']  = $row['username'];
                $_SESSION['full_name'] = $row['full_name'];
                $_SESSION['role']      = $row['role'];

                header("Location: home.php");
                exit;
            } else {
                $error = "Password salah.";
            }
        } else {
            $error = "User tidak ditemukan atau bukan level eksekutif.";
        }

        mysqli_stmt_close($stmt);
    }
}
?>
<!doctype html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://fonts.googleapis.com/css?family=Roboto:300,400&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="fonts/icomoon/style.css">
  <link rel="stylesheet" href="css/owl.carousel.min.css">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <!-- Style -->
  <link rel="stylesheet" href="css/style.css">

  <title>Login Eksekutif - UASDWO</title>
</head>

<body>

  <div class="content">
    <div class="container">
      <div class="row">

        <div class="col-md-6">
          <img src="images/store.jpg" alt="Image" class="img-fluid">
        </div>

        <div class="col-md-6 contents">
          <div class="row justify-content-center">
            <div class="col-md-8">

              <div class="mb-4">
                <h1>LOGIN</h1>
                <p class="mb-4">Dashboard UASDWO (Eksekutif)</p>
              </div>

              <?php if ($error !== ""): ?>
                <div class="alert alert-danger py-2">
                  <?php echo htmlspecialchars($error); ?>
                </div>
              <?php endif; ?>

              <form action="login.php" method="post">
                <div class="form-group first">
                  <label for="username">Username</label>
                  <input type="text" class="form-control" id="username" name="username" required>
                </div>

                <div class="form-group last mb-4">
                  <label for="password">Password</label>
                  <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <input type="submit" value="Login" class="btn btn-block btn-dark">
              </form>

            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script src="js/jquery-3.3.1.min.js"></script>
  <script src="js/popper.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/main.js"></script>
</body>

</html>
