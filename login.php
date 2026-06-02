<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include("connection.php");

if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    

    // Fetch user by email only first
    $stmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        $inputPassword = trim($_POST['password'] ?? '');
        $storedHash    = $row['password'];

        // Support both bcrypt (new registrations) and MD5 (legacy admin seeded via SQL)
        $valid = password_verify($inputPassword, $storedHash)
               || (strlen($storedHash) === 32 && md5($inputPassword) === $storedHash);

        if ($valid) {
            $_SESSION['user'] = $row['username'];
            $_SESSION['role'] = $row['role'] ?? 'user';

            if ($_SESSION['role'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: user.php");
            }
            exit();
        } else {
            $error = "Invalid email or password!";
        }
    } else {
        $error = "Invalid email or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #1a1a2e; }
        .login-card {
            border-radius: 16px; border: none;
            box-shadow: 0 8px 32px rgba(0,0,0,0.35);
        }
        .login-card .card-header {
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            border-radius: 16px 16px 0 0; padding: 28px 24px 20px;
            text-align: center; color: #fff; border: none;
        }
        .login-card .card-header .avatar-icon {
            width: 60px; height: 60px; background: rgba(255,255,255,0.2);
            border-radius: 50%; display: flex; align-items: center;
            justify-content: center; margin: 0 auto 12px; font-size: 1.7rem;
        }
        .form-control {
            border-radius: 8px; border: 1px solid #dde2ef;
            padding: 10px 14px; font-size: 0.93rem;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13,110,253,0.15);
        }
        .input-group-text {
            background: #f8f9ff; border-color: #dde2ef; color: #888;
            border-radius: 8px 0 0 8px;
        }
        .input-group .form-control { border-radius: 0 8px 8px 0; }
        .btn-login {
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            border: none; border-radius: 8px; padding: 10px;
            font-weight: 600; letter-spacing: 0.4px;
            transition: opacity 0.2s;
        }
        .btn-login:hover { opacity: 0.88; }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center vh-100">

<div class="card login-card" style="width:380px;">
    <div class="card-header">
        <div class="avatar-icon"><i class="bi bi-shield-lock-fill"></i></div>
        <h5 class="mb-0 fw-bold">Welcome Back</h5>
        <small class="opacity-75">Sign in to your account</small>
    </div>
    <div class="card-body p-4">

        <?php if (isset($error)): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 py-2" style="border-radius:8px;font-size:0.88rem;">
                <i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold small text-muted">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold small text-muted">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Enter password" required>
                    <button type="button" class="btn btn-outline-secondary" style="border-radius:0 8px 8px 0;border-color:#dde2ef;"
                        onclick="togglePass()">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>
            <button name="login" type="submit" class="btn btn-login btn-primary w-100 text-white">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
            </button>
        </form>

        <p class="text-center mt-3 mb-0 small text-muted">
            Don't have an account? <a href="registration.php" class="text-primary fw-semibold">Register</a>
        </p>
    </div>
</div>

<script>
function togglePass() {
    const input = document.getElementById('passwordInput');
    const icon  = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>
</body>
</html>
