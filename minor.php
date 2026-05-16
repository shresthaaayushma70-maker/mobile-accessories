<?php

session_start();

// Check if already logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: dashboard.php");
    exit;
}

require_once "config.php";

$username = $password = "";
$err = "";
$login_type = isset($_POST['login_type']) ? $_POST['login_type'] : 'user'; // default to user login

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = htmlspecialchars(trim($_POST['password']));
    $login_type = isset($_POST['login_type']) ? $_POST['login_type'] : 'user';

    if (empty($username) || empty($password)) {
        $err = "Please enter username and password";
    } else {
        // Simple query - just get the user
        $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) == 1) {
                $user = mysqli_fetch_assoc($result);
                
                // Check password
                if ($password === $user['password']) {
                    // Set default role if not set
                    $user_role = isset($user['role']) && !empty($user['role']) ? $user['role'] : 'user';
                    
                    // Set session
                    $_SESSION["username"] = $user['username'];
                    $_SESSION["user_id"] = $user['id'];
                    $_SESSION["role"] = $user_role;
                    $_SESSION["loggedin"] = true;
                    
                    // Redirect based on role (ignore login_type tab selection)
                    if ($user_role === 'admin') {
                        header("Location: admin_dashboard.php");
                        exit;
                    } else {
                        header("Location: user_dashboard.php");
                        exit;
                    }
                } else {
                    $err = "Incorrect password. Please try again.";
                }
            } else {
                $err = "No account found with that username.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $err = "Database error. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Bazario Mobile Accessories</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="BAZARIO_STYLES.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-header">
                <div class="logo-circle">
                    <i class="fas fa-shopping-bag login-icon"></i>
                </div>
                <h2 class="brand-title">BAZARIO</h2>
                <p class="brand-subtitle">Mobile Accessories Management System</p>
                <div class="header-divider"></div>
            </div>
        
        <?php if (!empty($err)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Error!</strong> <?php echo $err; ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>
        
        <!-- Toggle Buttons -->
        <div class="box-toggle-row" style="display:flex; gap:12px; justify-content:center; margin-bottom:18px;">
            <button id="user-toggle" class="btn btn-outline-primary box-toggle active" onclick="switchBox('user');">User Account</button>
            <button id="admin-toggle" class="btn btn-outline-danger box-toggle" onclick="switchBox('admin');">Admin Account</button>
        </div>

        <!-- User Box -->
        <div class="account-box user-box" id="user-box" style="display: block;">
            <div class="box-header text-center">
                <div class="box-logo user-logo"><i class="fas fa-user"></i></div>
                <h4>Sign in to your User Account</h4>
                <p class="box-sub">Access your orders, profile and more.</p>
            </div>
            <form action="" method="post" class="login-form">
                <input type="hidden" name="login_type" value="user">
                <div class="form-group">
                    <label for="username-user">Username</label>
                    <input type="text" class="form-control" name="username" id="username-user" placeholder="Enter your username" required>
                </div>
                <div class="form-group">
                    <label for="password-user">Password</label>
                    <input type="password" class="form-control" name="password" id="password-user" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn btn-login btn-block btn-primary">Login as User</button>
            </form>
        </div>

        <!-- Admin Box -->
        <div class="account-box admin-box" id="admin-box" style="display: none;">
            <div class="box-header text-center">
                <div class="box-logo admin-logo"><i class="fas fa-user-shield"></i></div>
                <h4>Admin Dashboard Login</h4>
                <p class="box-sub">Enter admin credentials to manage the store.</p>
            </div>
            <form action="" method="post" class="login-form">
                <input type="hidden" name="login_type" value="admin">
                <div class="form-group">
                    <label for="username-admin">Username</label>
                    <input type="text" class="form-control" name="username" id="username-admin" placeholder="Enter admin username" required>
                </div>
                <div class="form-group">
                    <label for="password-admin">Password</label>
                    <input type="password" class="form-control" name="password" id="password-admin" placeholder="Enter admin password" required>
                </div>
                <button type="submit" class="btn btn-login btn-block btn-danger">Login as Admin</button>
            </form>
        </div>
    </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Initialize boxes on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializeBoxes();
        });

        function initializeBoxes() {
            // Default: show user box
            document.getElementById('user-box').style.display = 'block';
            document.getElementById('admin-box').style.display = 'none';
            document.getElementById('user-toggle').classList.add('active');
            document.getElementById('admin-toggle').classList.remove('active');
        }

        function switchBox(type) {
            // Hide both
            document.getElementById('user-box').style.display = 'none';
            document.getElementById('admin-box').style.display = 'none';
            // Remove active from toggles
            document.getElementById('user-toggle').classList.remove('active');
            document.getElementById('admin-toggle').classList.remove('active');

            if (type === 'user') {
                document.getElementById('user-box').style.display = 'block';
                document.getElementById('user-toggle').classList.add('active');
                document.getElementById('username-user').focus();
            } else {
                document.getElementById('admin-box').style.display = 'block';
                document.getElementById('admin-toggle').classList.add('active');
                document.getElementById('username-admin').focus();
            }
        }
    </script>
</body>
<?php mysqli_close($conn); ?>
</html>