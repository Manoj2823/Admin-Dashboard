<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include("connection.php");

$errors = [];

if (isset($_POST['register'])) {
    $username         = trim($_POST['username'] ?? '');
    $email            = trim($_POST['email']    ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($username) || strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = "Username must be between 3 and 50 characters.";
    } elseif (!preg_match("/^[a-zA-Z0-9_]+$/", $username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores.";
    }
    
    // email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    // password
    if (empty($password) || strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $check = $stmt->get_result();

        if ($check->num_rows > 0) {
            $errors[] = "Email is already registered!";
        } else {
            $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("sss", $username, $email, $hashed_password);

            if ($insert_stmt->execute()) {
                header("Location: login.php");
                exit();
            } else {
                $errors[] = "Registration failed. Please try again later.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create an Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { 
            background: #1a1a2e; /* Matches login.php */
            font-family: 'Segoe UI', sans-serif;
        }
        .register-card {
            border-radius: 16px; 
            border: none;
            box-shadow: 0 8px 32px rgba(0,0,0,0.35);
        }
        .register-card .card-header {
            background: linear-gradient(135deg, #198754, #20c997); /* Unique green gradient for registration */
            border-radius: 16px 16px 0 0; 
            padding: 28px 24px 20px;
            text-align: center; 
            color: #fff; 
            border: none;
        }
        .register-card .card-header .avatar-icon {
            width: 60px; height: 60px; 
            background: rgba(255,255,255,0.2);
            border-radius: 50%; display: flex; 
            align-items: center; justify-content: center; 
            margin: 0 auto 12px; font-size: 1.7rem;
        }
        .form-control {
            border-radius: 8px; border: 1px solid #dde2ef;
            padding: 10px 14px; font-size: 0.93rem;
        }
        .form-control:focus {
            border-color: #20c997;
            box-shadow: 0 0 0 3px rgba(32, 201, 151, 0.15);
        }
        .input-group-text {
            background: #f8f9ff; border-color: #dde2ef; color: #888;
            border-radius: 8px 0 0 8px;
        }
        .input-group .form-control { border-radius: 0 8px 8px 0; }
        .btn-register {
            background: linear-gradient(135deg, #198754, #20c997);
            border: none; border-radius: 8px; padding: 12px;
            font-weight: 600; letter-spacing: 0.4px;
            transition: all 0.3s ease;
        }
        .btn-register:hover { 
            opacity: 0.9; 
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(32, 201, 151, 0.3);
        }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center min-vh-100 py-4">

<div class="card register-card" style="width:400px;">
    <div class="card-header">
        <div class="avatar-icon"><i class="bi bi-person-plus-fill"></i></div>
        <h5 class="mb-0 fw-bold">Create an Account</h5>
        <small class="opacity-75">Join us today. It's free!</small>
    </div>
    
    <div class="card-body p-4 p-md-5">

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger d-flex gap-2 py-2" style="border-radius:8px;font-size:0.88rem;">
                <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $error) echo "<li>" . htmlspecialchars($error) . "</li>"; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold small text-muted">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Choose a username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small text-muted">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="you@example.com" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small text-muted">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Create a password" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold small text-muted">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Repeat your password" required>
                </div>
            </div>

            <button name="register" type="submit" class="btn btn-register btn-primary w-100 text-white">
                <i class="bi bi-check2-circle me-1"></i> Register Now
            </button>
        </form>

        <p class="text-center mt-4 mb-0 small text-muted">
            Already have an account? <a href="login.php" class="text-success fw-semibold text-decoration-none">Sign In</a>
        </p>
    </div>
</div>

</body>
</html>